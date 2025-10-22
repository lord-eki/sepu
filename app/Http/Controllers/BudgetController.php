<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\PaymentVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Carbon\Carbon;

class BudgetController extends Controller
{
    /**
     * Display a listing of budgets.
     */
    public function index(Request $request)
    {
        $query = Budget::query()->with(['creator', 'approver']);

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by year
        if ($request->has('year') && $request->year != '') {
            $query->where('budget_year', $request->year);
        }

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('budget_year', 'like', '%' . $search . '%');
            });
        }

        // Sort by created_at descending by default
        $query->orderBy('budget_year', 'desc')
              ->orderBy('created_at', 'desc');

        $budgets = $query->paginate(15);

        // Get budget statistics
        $stats = [
            'total_budgets' => Budget::count(),
            'active_budgets' => Budget::where('status', 'active')->count(),
            'draft_budgets' => Budget::where('status', 'draft')->count(),
            'total_budget_amount' => Budget::where('status', 'active')->sum('total_budget'),
            'available_years' => Budget::distinct()->pluck('budget_year')->sort()->values(),
        ];

        return Inertia::render('Shared/Budgets/Index', [
            'budgets' => $budgets,
            'stats' => $stats,
            'filters' => $request->only(['status', 'year', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new budget.
     */
    public function create()
    {
        $currentYear = Carbon::now()->year;
        $nextYear = $currentYear + 1;
        
        // Check if budget for next year already exists
        $existingBudget = Budget::where('budget_year', $nextYear)->first();
        
        return Inertia::render('Shared/Budgets/Create', [
            'suggested_year' => $nextYear,
            'existing_budget' => $existingBudget ? true : false,
            'budget_categories' => $this->getBudgetCategories(),
        ]);
    }

    /**
     * Store a newly created budget in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'budget_year' => [
                'required',
                'integer',
                'min:' . (Carbon::now()->year),
                'max:' . (Carbon::now()->year + 5),
                Rule::unique('budgets', 'budget_year'),
            ],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_budget' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'budget_items' => 'required|array|min:1',
            'budget_items.*.category' => 'required|string|max:255',
            'budget_items.*.item_name' => 'required|string|max:255',
            'budget_items.*.description' => 'nullable|string',
            'budget_items.*.budgeted_amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Validate that budget items total equals total budget
            $itemsTotal = collect($request->budget_items)->sum('budgeted_amount');
            if (abs($itemsTotal - $request->total_budget) > 0.01) {
                throw new \Exception('Budget items total must equal the total budget amount.');
            }

            // Create the budget
            $budget = Budget::create([
                'budget_year' => $request->budget_year,
                'title' => $request->title,
                'description' => $request->description,
                'total_budget' => $request->total_budget,
                'status' => 'draft',
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'created_by' => Auth::id(),
            ]);

            // Create budget items
            foreach ($request->budget_items as $item) {
                BudgetItem::create([
                    'budget_id' => $budget->id,
                    'category' => $item['category'],
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'budgeted_amount' => $item['budgeted_amount'],
                    'spent_amount' => 0,
                    'remaining_amount' => $item['budgeted_amount'],
                ]);
            }

            DB::commit();

            return redirect()->route('budgets.show', $budget)
                           ->with('success', 'Budget created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget creation failed: ' . $e->getMessage());
            
            return back()->withErrors(['error' => 'Failed to create budget: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * Display the specified budget.
     */
    public function show(Budget $budget)
    {
        $budget->load([
            'creator',
            'approver',
            'budgetItems' => function ($query) {
                $query->orderBy('category')->orderBy('item_name');
            }
        ]);

        // Calculate budget utilization
        $utilization = $this->calculateBudgetUtilization($budget);

        // Get recent vouchers for this budget
        $recentVouchers = PaymentVoucher::whereHas('budgetItem', function ($query) use ($budget) {
            $query->where('budget_id', $budget->id);
        })->with(['budgetItem', 'creator'])
          ->orderBy('created_at', 'desc')
          ->take(10)
          ->get();

        return Inertia::render('Shared/Budgets/Show', [
            'budget' => $budget,
            'utilization' => $utilization,
            'recent_vouchers' => $recentVouchers,
            'can_approve' => $this->canApproveBudget($budget),
            'can_close' => $this->canCloseBudget($budget),
            'can_activate' => $this->canActivateBudget($budget),
            'can_edit' => $this->canEditBudget($budget),
        ]);
    }

    /**
     * Show the form for editing the specified budget.
     */
    public function edit(Budget $budget)
    {
        // Only allow editing draft budgets
        if ($budget->status !== 'draft') {
            return redirect()->route('budgets.show', $budget)
                           ->with('error', 'Only draft budgets can be edited.');
        }

        $budget->load('budgetItems');

        return Inertia::render('Shared/Budgets/Edit', [
            'budget' => $budget,
            'budget_categories' => $this->getBudgetCategories(),
        ]);
    }

    /**
     * Update the specified budget in storage.
     */
    public function update(Request $request, Budget $budget)
    {
        // Only allow updating draft budgets
        if ($budget->status !== 'draft') {
            return redirect()->route('budgets.show', $budget)
                           ->with('error', 'Only draft budgets can be updated.');
        }

        $request->validate([
            'budget_year' => [
                'required',
                'integer',
                'min:' . (Carbon::now()->year),
                'max:' . (Carbon::now()->year + 5),
                Rule::unique('budgets', 'budget_year')->ignore($budget->id),
            ],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_budget' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'budget_items' => 'required|array|min:1',
            'budget_items.*.category' => 'required|string|max:255',
            'budget_items.*.item_name' => 'required|string|max:255',
            'budget_items.*.description' => 'nullable|string',
            'budget_items.*.budgeted_amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Validate that budget items total equals total budget
            $itemsTotal = collect($request->budget_items)->sum('budgeted_amount');
            if (abs($itemsTotal - $request->total_budget) > 0.01) {
                throw new \Exception('Budget items total must equal the total budget amount.');
            }

            // Update the budget
            $budget->update([
                'budget_year' => $request->budget_year,
                'title' => $request->title,
                'description' => $request->description,
                'total_budget' => $request->total_budget,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

            // Delete existing budget items
            $budget->budgetItems()->delete();

            // Create new budget items
            foreach ($request->budget_items as $item) {
                BudgetItem::create([
                    'budget_id' => $budget->id,
                    'category' => $item['category'],
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'budgeted_amount' => $item['budgeted_amount'],
                    'spent_amount' => 0,
                    'remaining_amount' => $item['budgeted_amount'],
                ]);
            }

            DB::commit();

            return redirect()->route('budgets.show', $budget)
                           ->with('success', 'Budget updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget update failed: ' . $e->getMessage());
            
            return back()->withErrors(['error' => 'Failed to update budget: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * Remove the specified budget from storage.
     */
    public function destroy(Budget $budget)
    {
        // Only allow deleting draft budgets
        if ($budget->status !== 'draft') {
            return redirect()->route('budgets.index')
                           ->with('error', 'Only draft budgets can be deleted.');
        }

        try {
            DB::beginTransaction();

            // Delete budget items first
            $budget->budgetItems()->delete();
            
            // Delete the budget
            $budget->delete();

            DB::commit();

            return redirect()->route('budgets.index')
                           ->with('success', 'Budget deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget deletion failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to delete budget: ' . $e->getMessage());
        }
    }

    /**
     * Approve the specified budget.
     */
    public function approve(Budget $budget)
    {
        if ($budget->status !== 'draft') {
            return redirect()->route('budgets.show', $budget)
                           ->with('error', 'Only draft budgets can be approved.');
        }

        if (!$this->canApproveBudget($budget)) {
            return redirect()->route('budgets.show', $budget)
                           ->with('error', 'You do not have permission to approve this budget.');
        }

        try {
            $budget->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approval_date' => Carbon::now(),
            ]);

            return redirect()->route('budgets.show', $budget)
                           ->with('success', 'Budget approved successfully.');

        } catch (\Exception $e) {
            Log::error('Budget approval failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to approve budget: ' . $e->getMessage());
        }
    }

    /**
     * Activate the specified budget.
     */
    public function activate(Budget $budget)
    {
        if ($budget->status !== 'approved') {
            return redirect()->route('budgets.show', $budget)
                           ->with('error', 'Only approved budgets can be activated.');
        }

        if (!$this->canActivateBudget($budget)) {
            return redirect()->route('budgets.show', $budget)
                           ->with('error', 'You do not have permission to activate this budget.');
        }

        try {
            DB::beginTransaction();

            // Deactivate other active budgets for the same year
            Budget::where('budget_year', $budget->budget_year)
                  ->where('status', 'active')
                  ->update(['status' => 'closed']);

            // Activate this budget
            $budget->update([
                'status' => 'active',
            ]);

            DB::commit();

            return redirect()->route('budgets.show', $budget)
                           ->with('success', 'Budget activated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget activation failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to activate budget: ' . $e->getMessage());
        }
    }

    /**
     * Close the specified budget.
     */
    public function close(Budget $budget)
    {
        if ($budget->status !== 'active') {
            return redirect()->route('budgets.show', $budget)
                           ->with('error', 'Only active budgets can be closed.');
        }

        if (!$this->canCloseBudget($budget)) {
            return redirect()->route('budgets.show', $budget)
                           ->with('error', 'You do not have permission to close this budget.');
        }

        try {
            $budget->update([
                'status' => 'closed',
            ]);

            return redirect()->route('budgets.show', $budget)
                           ->with('success', 'Budget closed successfully.');

        } catch (\Exception $e) {
            Log::error('Budget closure failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to close budget: ' . $e->getMessage());
        }
    }

    /**
     * Display budget items for the specified budget.
     */
    public function items(Budget $budget)
    {
        $budget->load(['budgetItems' => function ($query) {
            $query->orderBy('category')->orderBy('item_name');
        }]);

        // Group items by category
        $itemsByCategory = $budget->budgetItems->groupBy('category');

        return Inertia::render('Shared/Budgets/Items', [
            'budget' => $budget,
            'items_by_category' => $itemsByCategory,
            'can_edit' => $this->canEditBudget($budget),
        ]);
    }

    /**
     * Store a new budget item.
     */
    public function storeItem(Request $request, Budget $budget)
    {
        if (!$this->canEditBudget($budget)) {
            return response()->json(['error' => 'You do not have permission to add items to this budget.'], 403);
        }

        $request->validate([
            'category' => 'required|string|max:255',
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'budgeted_amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Create the budget item
            $item = BudgetItem::create([
                'budget_id' => $budget->id,
                'category' => $request->category,
                'item_name' => $request->item_name,
                'description' => $request->description,
                'budgeted_amount' => $request->budgeted_amount,
                'spent_amount' => 0,
                'remaining_amount' => $request->budgeted_amount,
            ]);

            // Update budget total
            $budget->update([
                'total_budget' => $budget->total_budget + $request->budgeted_amount,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Budget item added successfully.',
                'item' => $item,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget item creation failed: ' . $e->getMessage());
            
            return response()->json(['error' => 'Failed to add budget item: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified budget item.
     */
    public function updateItem(Request $request, Budget $budget, BudgetItem $item)
    {
        if ($item->budget_id !== $budget->id) {
            return response()->json(['error' => 'Budget item does not belong to this budget.'], 404);
        }

        if (!$this->canEditBudget($budget)) {
            return response()->json(['error' => 'You do not have permission to edit this budget item.'], 403);
        }

        $request->validate([
            'category' => 'required|string|max:255',
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'budgeted_amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $oldAmount = $item->budgeted_amount;
            $newAmount = $request->budgeted_amount;
            $difference = $newAmount - $oldAmount;

            // Update the budget item
            $item->update([
                'category' => $request->category,
                'item_name' => $request->item_name,
                'description' => $request->description,
                'budgeted_amount' => $newAmount,
                'remaining_amount' => $newAmount - $item->spent_amount,
            ]);

            // Update budget total
            $budget->update([
                'total_budget' => $budget->total_budget + $difference,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Budget item updated successfully.',
                'item' => $item,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget item update failed: ' . $e->getMessage());
            
            return response()->json(['error' => 'Failed to update budget item: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified budget item.
     */
    public function destroyItem(Budget $budget, BudgetItem $item)
    {
        if ($item->budget_id !== $budget->id) {
            return response()->json(['error' => 'Budget item does not belong to this budget.'], 404);
        }

        if (!$this->canEditBudget($budget)) {
            return response()->json(['error' => 'You do not have permission to delete this budget item.'], 403);
        }

        if ($item->spent_amount > 0) {
            return response()->json(['error' => 'Cannot delete budget item that has been spent against.'], 400);
        }

        try {
            DB::beginTransaction();

            // Update budget total
            $budget->update([
                'total_budget' => $budget->total_budget - $item->budgeted_amount,
            ]);

            // Delete the budget item
            $item->delete();

            DB::commit();

            return response()->json([
                'message' => 'Budget item deleted successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget item deletion failed: ' . $e->getMessage());
            
            return response()->json(['error' => 'Failed to delete budget item: ' . $e->getMessage()], 500);
        }
    }



    public function updateUtilization(Request $request, BudgetItem $budgetItem)
{
    $validated = $request->validate([
        'amount' => 'required|numeric|min:0',
        'description' => 'nullable|string|max:255',
    ]);

    $budgetItem->spent_amount += $validated['amount'];
    $budgetItem->save();

    // Optional: Log utilization in a separate table (history)
    // BudgetUtilization::create([
    //     'budget_item_id' => $budgetItem->id,
    //     'amount' => $validated['amount'],
    //     'description' => $validated['description'],
    // ]);

    return redirect()->back()->with('success', 'Utilization updated successfully!');
}



    /**
     * Display budget variance analysis.
     */
    public function variance(Budget $budget)
    {
        $budget->load(['budgetItems' => function ($query) {
            $query->orderBy('category')->orderBy('item_name');
        }]);

        $varianceData = [];
        $totalBudgeted = 0;
        $totalSpent = 0;

        foreach ($budget->budgetItems as $item) {
            $variance = $item->budgeted_amount - $item->spent_amount;
            $variancePercentage = $item->budgeted_amount > 0 
                ? (($variance / $item->budgeted_amount) * 100) 
                : 0;

            $varianceData[] = [
                'category' => $item->category,
                'item_name' => $item->item_name,
                'budgeted_amount' => $item->budgeted_amount,
                'spent_amount' => $item->spent_amount,
                'variance' => $variance,
                'variance_percentage' => $variancePercentage,
                'status' => $this->getVarianceStatus($variancePercentage),
            ];

            $totalBudgeted += $item->budgeted_amount;
            $totalSpent += $item->spent_amount;
        }

        $totalVariance = $totalBudgeted - $totalSpent;
        $totalVariancePercentage = $totalBudgeted > 0 
            ? (($totalVariance / $totalBudgeted) * 100) 
            : 0;

        return Inertia::render('Shared/Budgets/Variance', [
            'budget' => $budget,
            'variance_data' => $varianceData,
            'totals' => [
                'budgeted' => $totalBudgeted,
                'spent' => $totalSpent,
                'variance' => $totalVariance,
                'variance_percentage' => $totalVariancePercentage,
            ],
        ]);
    }

    /**
     * Display budget utilization analysis.
     */
    public function utilization(Budget $budget)
    {
        $utilization = $this->calculateBudgetUtilization($budget);
        
        // Get spending trends (monthly breakdown)
        $spendingTrends = $this->getSpendingTrends($budget);
        
        // Get category-wise utilization
        $categoryUtilization = $this->getCategoryUtilization($budget);

        return Inertia::render('Shared/Budgets/Utilization', [
            'budget' => $budget,
            'utilization' => $utilization,
            'spending_trends' => $spendingTrends,
            'category_utilization' => $categoryUtilization,
        ]);
    }

    /**
     * Calculate budget utilization metrics.
     */
    private function calculateBudgetUtilization(Budget $budget)
    {
        $totalBudgeted = $budget->total_budget;
        $totalSpent = $budget->budgetItems->sum('spent_amount');
        $totalRemaining = $totalBudgeted - $totalSpent;
        
        $utilizationPercentage = $totalBudgeted > 0 
            ? (($totalSpent / $totalBudgeted) * 100) 
            : 0;

        return [
            'total_budgeted' => $totalBudgeted,
            'total_spent' => $totalSpent,
            'total_remaining' => $totalRemaining,
            'utilization_percentage' => $utilizationPercentage,
            'items_count' => $budget->budgetItems->count(),
            'categories_count' => $budget->budgetItems->pluck('category')->unique()->count(),
        ];
    }

    /**
     * Get spending trends for the budget.
     */
    private function getSpendingTrends(Budget $budget)
    {
        return PaymentVoucher::whereHas('budgetItem', function ($query) use ($budget) {
            $query->where('budget_id', $budget->id);
        })->where('status', 'paid')
          ->selectRaw('DATE_FORMAT(payment_date, "%Y-%m") as month, SUM(amount) as total_spent')
          ->groupBy('month')
          ->orderBy('month')
          ->get();
    }

    /**
     * Get category-wise utilization.
     */
    private function getCategoryUtilization(Budget $budget)
    {
        return $budget->budgetItems
            ->groupBy('category')
            ->map(function ($items, $category) {
                $budgeted = $items->sum('budgeted_amount');
                $spent = $items->sum('spent_amount');
                $remaining = $budgeted - $spent;
                $utilization = $budgeted > 0 ? (($spent / $budgeted) * 100) : 0;

                return [
                    'category' => $category,
                    'budgeted' => $budgeted,
                    'spent' => $spent,
                    'remaining' => $remaining,
                    'utilization_percentage' => $utilization,
                    'items_count' => $items->count(),
                ];
            })
            ->values();
    }

    /**
     * Get predefined budget categories.
     */
    private function getBudgetCategories()
    {
        return [
            'Administrative Expenses',
            'Staff Costs',
            'Marketing & Communication',
            'Technology & Equipment',
            'Office Supplies',
            'Training & Development',
            'Legal & Professional',
            'Insurance',
            'Utilities',
            'Travel & Transport',
            'Member Services',
            'Loan Provisions',
            'Other Expenses',
        ];
    }

    /**
     * Get variance status based on percentage.
     */
    private function getVarianceStatus($variancePercentage)
    {
        if ($variancePercentage > 10) {
            return 'under_budget';
        } elseif ($variancePercentage < -10) {
            return 'over_budget';
        } else {
            return 'on_track';
        }
    }

    /**
     * Check if user can approve budget.
     */
    private function canApproveBudget(Budget $budget)
    {
        $user = Auth::user();
        
        // Cannot approve own budget
        if ($budget->created_by === $user->id) {
            return false;
        }

        // Only management and admin can approve
        return in_array($user->role, ['management', 'admin']);
    }

    /**
     * Check if user can activate budget.
     */
    private function canActivateBudget(Budget $budget)
    {
        $user = Auth::user();
        
        // Only management and admin can activate
        return in_array($user->role, ['management', 'admin']);
    }

    /**
     * Check if user can close budget.
     */
    private function canCloseBudget(Budget $budget)
    {
        $user = Auth::user();
        
        // Only management and admin can close
        return in_array($user->role, ['management', 'admin']);
    }

    /**
     * Check if user can edit budget.
     */
    private function canEditBudget(Budget $budget)
    {
        $user = Auth::user();
        
        // Only draft budgets can be edited
        if ($budget->status !== 'draft') {
            return false;
        }

        // Creator can edit, or management/admin
        return $budget->created_by === $user->id || in_array($user->role, ['management', 'admin']);
    }
}