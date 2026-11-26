<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetItemRequest;
use App\Http\Requests\StoreBudgetRequest;
use App\Http\Requests\UpdateBudgetItemRequest;
use App\Http\Requests\UpdateBudgetRequest;
use App\Http\Requests\UpdateUtilizationRequest;
use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\ChartOfAccount;
use App\Models\PaymentVoucher;
use App\Support\ErrorMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Carbon\Carbon;

class BudgetController extends Controller
{

    //Index page
    public function index(Request $request)
    {
        $query = Budget::query()->with(['creator', 'approver']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('year')) {
            $query->where('budget_year', $request->year);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('budget_year', 'like', "%{$search}%");
            });
        }

        $query->orderBy('budget_year', 'desc')->orderBy('created_at', 'desc');
        $budgets = $query->paginate(15);

        $stats = [
            'total_budgets'       => Budget::count(),
            'active_budgets'      => Budget::where('status', 'active')->count(),
            'draft_budgets'       => Budget::where('status', 'draft')->count(),
            'total_budget_amount' => Budget::where('status', 'active')->sum('total_budget'),
            'available_years'     => Budget::distinct()->pluck('budget_year')->sort()->values(),
        ];

        return Inertia::render('Shared/Budgets/Index', [
            'budgets' => $budgets,
            'stats'   => $stats,
            'filters' => $request->only(['status', 'year', 'search']),
        ]);
    }


    //Show the create budget form
    public function create()
    {
        $currentYear = Carbon::now()->year;
        $nextYear    = $currentYear + 1;

        $existingBudget = Budget::where('budget_year', $nextYear)->first();

        return Inertia::render('Shared/Budgets/Create', [
            'suggested_year'   => $nextYear,
            'existing_budget'  => $existingBudget ? true : false,
            'budget_accounts'  => $this->getBudgetLineAccounts(),
        ]);
    }

    //Store the new budget
    public function store(StoreBudgetRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $itemsTotal = collect($data->budget_items)->sum('budgeted_amount');
            if (abs($itemsTotal - $data->total_budget) > 0.01) {
                throw new \DomainException('Budget items total must equal the total budget amount.');
            }

            $budget = Budget::create([
                'budget_year'  => $data->budget_year,
                'title'        => $data->title,
                'description'  => $data->description,
                'total_budget' => $data->total_budget,
                'status'       => 'draft',
                'start_date'   => $data->start_date,
                'end_date'     => $data->end_date,
                'created_by'   => Auth::id(),
            ]);

            foreach ($data->budget_items as $item) {
                $account = ChartOfAccount::findOrFail($item['chart_of_account_id']);

                BudgetItem::create([
                    'budget_id'           => $budget->id,
                    'chart_of_account_id' => $account->id,
                    'category'            => $account->account_category,
                    'item_name'           => $account->account_name,
                    'description'         => $item['description'] ?? null,
                    'budgeted_amount'     => $item['budgeted_amount'],
                    'spent_amount'        => 0,
                    'remaining_amount'    => $item['budgeted_amount'],
                ]);
            }

            DB::commit();

            return redirect()->route('budgets.show', $budget)->with('success', 'Budget created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget creation failed: ' . ErrorMessage::from($e));
            return back()->withErrors(['error' => 'Failed to create budget: ' . ErrorMessage::from($e)])->withInput();
        }
    }


    //Show a budget
    public function show(Budget $budget)
    {
        $budget->load(['creator', 'approver', 'budgetItems.chartOfAccount']);
        $utilization = $this->calculateBudgetUtilization($budget);

        $recentVouchers = PaymentVoucher::whereHas('budgetItem', function ($query) use ($budget) {
            $query->where('budget_id', $budget->id);
        })->with(['budgetItem.chartOfAccount', 'creator'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return Inertia::render('Shared/Budgets/Show', [
            'budget'          => $this->formatBudget($budget),
            'utilization'     => $utilization,
            'recent_vouchers' => $recentVouchers,
            'can_approve'     => $this->canApproveBudget($budget),
            'can_close'       => $this->canCloseBudget($budget),
            'can_activate'    => $this->canActivateBudget($budget),
            'can_edit'        => $this->canEditBudget($budget),
            'can_submit'      => $this->canSubmitBudget($budget),
        ]);
    }


    //Edit a budget page
    public function edit(Budget $budget)
    {
        if ($budget->status !== 'draft') {
            return redirect()->route('budgets.show', $budget)
                ->with('error', 'Only draft budgets can be edited.');
        }

        $budget->load('budgetItems.chartOfAccount');

        return Inertia::render('Shared/Budgets/Edit', [
            'budget'          => $this->formatBudget($budget),
            'budget_accounts' => $this->getBudgetLineAccounts(),
        ]);
    }

    //Persist a budget update
    public function update(UpdateBudgetRequest $request, Budget $budget)
    {
        if ($budget->status !== 'draft') {
            return redirect()->route('budgets.show', $budget)
                ->with('error', 'Only draft budgets can be updated.');
        }

        $data =  $request->validated();

        try {
            DB::beginTransaction();

            $itemsTotal = collect($data->budget_items)->sum('budgeted_amount');
            if (abs($itemsTotal - $data->total_budget) > 0.01) {
                throw new \DomainException('Budget items total must equal the total budget amount.');
            }

            $budget->update([
                'budget_year'  => $data->budget_year,
                'title'        => $data->title,
                'description'  => $data->description,
                'total_budget' => $data->total_budget,
                'start_date'   => $data->start_date,
                'end_date'     => $data->end_date,
            ]);

            $budget->budgetItems()->delete();

            foreach ($data->budget_items as $item) {
                $account = ChartOfAccount::findOrFail($item['chart_of_account_id']);

                BudgetItem::create([
                    'budget_id'           => $budget->id,
                    'chart_of_account_id' => $account->id,
                    'category'            => $account->account_category,
                    'item_name'           => $account->account_name,
                    'description'         => $item['description'] ?? null,
                    'budgeted_amount'     => $item['budgeted_amount'],
                    'spent_amount'        => 0,
                    'remaining_amount'    => $item['budgeted_amount'],
                ]);
            }

            DB::commit();

            return redirect()->route('budgets.show', $budget)
                ->with('success', 'Budget updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget update failed: ' . ErrorMessage::from($e));
            return back()->withErrors(['error' => 'Failed to update budget: ' . ErrorMessage::from($e)])->withInput();
        }
    }

    //Delete a budget
    public function destroy(Budget $budget)
    {
        if ($budget->status !== 'draft') {
            return redirect()->route('budgets.index')
                ->with('error', 'Only draft budgets can be deleted.');
        }

        try {
            DB::beginTransaction();
            $budget->budgetItems()->delete();
            $budget->delete();
            DB::commit();

            return redirect()->route('budgets.index')
                ->with('success', 'Budget deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget deletion failed: ' . ErrorMessage::from($e));
            return back()->with('error', 'Failed to delete budget: ' . ErrorMessage::from($e));
        }
    }

    //submitting  a budget
    public function submit(Budget $budget)
    {
        if ($budget->status !== 'draft') {
            return redirect()->route('budgets.show', $budget)
                ->with('error', 'Only draft budgets can be submitted.');
        }

        if (!$this->canSubmitBudget($budget)) {
            return redirect()->route('budgets.show', $budget)
                ->with('error', 'You do not have permission to submit this budget.');
        }

        try {
            $budget->update([
                'status'         => 'pending',
                'submitted_by'   => Auth::id(),
                'submitted_date' => Carbon::now(),
            ]);

            return redirect()->route('budgets.show', $budget)
                ->with('success', 'Budget submitted for approval successfully.');
        } catch (\Exception $e) {
            Log::error('Budget submission failed: ' . ErrorMessage::from($e));
            return back()->with('error', 'Failed to submit budget: ' . ErrorMessage::from($e));
        }
    }

    //Budget Approval
    public function approve(Budget $budget)
    {
        if ($budget->status !== 'pending') {
            return redirect()->route('budgets.show', $budget)
                ->with('error', 'Only pending budgets can be approved.');
        }

        if (!$this->canApproveBudget($budget)) {
            return redirect()->route('budgets.show', $budget)
                ->with('error', 'You do not have permission to approve this budget.');
        }

        try {
            $budget->update([
                'status'        => 'approved',
                'approved_by'   => Auth::id(),
                'approval_date' => Carbon::now(),
            ]);

            return redirect()->route('budgets.show', $budget)
                ->with('success', 'Budget approved successfully.');
        } catch (\Exception $e) {
            Log::error('Budget approval failed: ' . ErrorMessage::from($e));
            return back()->with('error', 'Failed to approve budget: ' . ErrorMessage::from($e));
        }
    }


    //Activate a budget
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

            Budget::where('budget_year', $budget->budget_year)
                ->where('status', 'active')
                ->update(['status' => 'closed']);

            $budget->update(['status' => 'active']);

            DB::commit();

            return redirect()->route('budgets.show', $budget)
                ->with('success', 'Budget activated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget activation failed: ' . ErrorMessage::from($e));
            return back()->with('error', 'Failed to activate budget: ' . ErrorMessage::from($e));
        }
    }


    //Close a budget
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
            $budget->update(['status' => 'closed']);

            return redirect()->route('budgets.show', $budget)
                ->with('success', 'Budget closed successfully.');
        } catch (\Exception $e) {
            Log::error('Budget closure failed: ' . ErrorMessage::from($e));
            return back()->with('error', 'Failed to close budget: ' . ErrorMessage::from($e));
        }
    }


    //Budget items
    public function items(Budget $budget)
    {
        $budget->load(['budgetItems.chartOfAccount']);
        $itemsByCategory = $budget->budgetItems
            ->groupBy(fn($item) => $item->chartOfAccount?->account_type ?? $item->category);

        return Inertia::render('Shared/Budgets/Items', [
            'budget'             => $budget,
            'items_by_category'  => $itemsByCategory,
            'can_edit'           => $this->canEditBudget($budget),
        ]);
    }

    //Store budget Item
    public function storeItem(StoreBudgetItemRequest $request, Budget $budget)
    {
        if (!$this->canEditBudget($budget)) {
            return response()->json(['error' => 'You do not have permission to add items to this budget.'], 403);
        }

        $data = $request->validated();

        try {
            DB::beginTransaction();

            $account = ChartOfAccount::findOrFail($data->chart_of_account_id);

            $item = BudgetItem::create([
                'budget_id'           => $budget->id,
                'chart_of_account_id' => $account->id,
                'category'            => $account->account_category,
                'item_name'           => $account->account_name,
                'description'         => $data->description,
                'budgeted_amount'     => $data->budgeted_amount,
                'spent_amount'        => 0,
                'remaining_amount'    => $data->budgeted_amount,
            ]);

            $budget->increment('total_budget', $data->budgeted_amount);

            DB::commit();

            return response()->json([
                'message' => 'Budget item added successfully.',
                'item'    => $this->formatBudgetItem($item->load('chartOfAccount')),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget item creation failed: ' . ErrorMessage::from($e));
            return response()->json(['error' => 'Failed to add budget item: ' . ErrorMessage::from($e)], 500);
        }
    }

    //Update budget item
    public function updateItem(UpdateBudgetItemRequest $request, Budget $budget, BudgetItem $item)
    {
        if ($item->budget_id !== $budget->id) {
            return response()->json(['error' => 'Budget item does not belong to this budget.'], 404);
        }

        if (!$this->canEditBudget($budget)) {
            return response()->json(['error' => 'You do not have permission to edit this budget item.'], 403);
        }

        $data = $request->validated();

        try {
            DB::beginTransaction();

            $account    = ChartOfAccount::findOrFail($data->chart_of_account_id);
            $oldAmount  = (float) $item->budgeted_amount;
            $newAmount  = (float) $data->budgeted_amount;
            $difference = $newAmount - $oldAmount;

            $item->update([
                'chart_of_account_id' => $account->id,
                'category'            => $account->account_category,
                'item_name'           => $account->account_name,
                'description'         => $data->description,
                'budgeted_amount'     => $newAmount,
                'remaining_amount'    => $newAmount - $item->spent_amount,
            ]);

            $budget->increment('total_budget', $difference);

            DB::commit();

            return response()->json([
                'message' => 'Budget item updated successfully.',
                'item'    => $this->formatBudgetItem($item->load('chartOfAccount')),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget item update failed: ' . ErrorMessage::from($e));
            return response()->json(['error' => 'Failed to update budget item: ' . ErrorMessage::from($e)], 500);
        }
    }


    //Delete a budget item
    public function destroyItem(Budget $budget, BudgetItem $item)
    {
        if ($item->budget_id !== $budget->id) {
            return response()->json(['error' => 'Budget item does not belong to this budget.'], 404);
        }

        if (!$this->canEditBudget($budget)) {
            return response()->json(['error' => 'You do not have permission to delete this budget item.'], 403);
        }

        if ($item->spent_amount > 0) {
            return response()->json(['error' => 'Cannot delete a budget item that has been spent against.'], 400);
        }

        try {
            DB::beginTransaction();
            $budget->decrement('total_budget', $item->budgeted_amount);
            $item->delete();
            DB::commit();

            return response()->json(['message' => 'Budget item deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget item deletion failed: ' . ErrorMessage::from($e));
            return response()->json(['error' => 'Failed to delete budget item: ' . ErrorMessage::from($e)], 500);
        }
    }

    //Update Utilization
    public function updateUtilization(UpdateUtilizationRequest $request, BudgetItem $budgetItem)
    {
        $data = $request->validated();

        $budgetItem->spent_amount     += $data['amount'];
        $budgetItem->remaining_amount  = $budgetItem->budgeted_amount - $budgetItem->spent_amount;
        $budgetItem->save();

        return redirect()->back()->with('success', 'Utilization updated successfully!');
    }


    // Variance report  
    public function variance(Budget $budget)
    {
        $budget->load(['budgetItems.chartOfAccount']);

        $varianceData = [];
        $totalBudgeted = 0;
        $totalSpent    = 0;

        foreach ($budget->budgetItems as $item) {
            $variance           = (float) $item->budgeted_amount - (float) $item->spent_amount;
            $variancePercentage = $item->budgeted_amount > 0
                ? (($variance / $item->budgeted_amount) * 100)
                : 0;

            $varianceData[] = [
                'chart_of_account_id'   => $item->chart_of_account_id,
                'account_code'          => $item->chartOfAccount?->account_code,
                'account_name'          => $item->chartOfAccount?->account_name ?? $item->item_name,
                'account_type'          => $item->chartOfAccount?->account_type,
                'category'              => $item->chartOfAccount?->account_category ?? $item->category,
                'budgeted_amount'       => (float) $item->budgeted_amount,
                'spent_amount'          => (float) $item->spent_amount,
                'variance'              => $variance,
                'variance_percentage'   => round($variancePercentage, 2),
                'status'                => $this->getVarianceStatus($variancePercentage),
            ];

            $totalBudgeted += $item->budgeted_amount;
            $totalSpent    += $item->spent_amount;
        }

        $totalVariance           = $totalBudgeted - $totalSpent;
        $totalVariancePercentage = $totalBudgeted > 0
            ? (($totalVariance / $totalBudgeted) * 100)
            : 0;

        return Inertia::render('Shared/Budgets/Variance', [
            'budget'        => $budget,
            'variance_data' => $varianceData,
            'totals'        => [
                'budgeted'           => $totalBudgeted,
                'spent'              => $totalSpent,
                'variance'           => $totalVariance,
                'variance_percentage' => round($totalVariancePercentage, 2),
            ],
        ]);
    }

    //Utilization report
    public function utilization(Budget $budget)
    {
        $budget->load('budgetItems.chartOfAccount');

        $utilization        = $this->calculateBudgetUtilization($budget);
        $spendingTrends     = $this->getSpendingTrends($budget);
        $categoryUtilization = $this->getCategoryUtilization($budget);

        return Inertia::render('Shared/Budgets/Utilization', [
            'budget'               => $budget,
            'utilization'          => $utilization,
            'spending_trends'      => $spendingTrends,
            'category_utilization' => $categoryUtilization,
        ]);
    }

    //Helper functions 

    private function getBudgetLineAccounts(): array
    {
        return ChartOfAccount::active()
            ->postable()
            ->whereIn('account_type', ['expense', 'revenue'])
            ->with('parentAccount')
            ->orderBy('account_code')
            ->get()
            ->map(fn($a) => [
                'id'            => $a->id,
                'account_code'  => $a->account_code,
                'account_name'  => $a->account_name,
                'account_type'  => $a->account_type,
                'full_path'     => $a->full_path_name,
                'label'         => str_repeat('　', max(0, ($a->level ?? 1) - 1))
                    . $a->account_code . ' – ' . $a->account_name,
            ])
            ->toArray();
    }

    private function formatBudget(Budget $budget): array
    {
        return array_merge($budget->toArray(), [
            'budget_items' => $budget->budgetItems->map(fn($i) => $this->formatBudgetItem($i))->values(),
        ]);
    }

    private function formatBudgetItem(BudgetItem $item): array
    {
        return [
            'id'                  => $item->id,
            'budget_id'           => $item->budget_id,
            'chart_of_account_id' => $item->chart_of_account_id,
            'account_code'        => $item->chartOfAccount?->account_code,
            'account_name'        => $item->chartOfAccount?->account_name ?? $item->item_name,
            'account_type'        => $item->chartOfAccount?->account_type,
            'category'            => $item->chartOfAccount?->account_category ?? $item->category,
            'description'         => $item->description,
            'budgeted_amount'     => (float) $item->budgeted_amount,
            'spent_amount'        => (float) $item->spent_amount,
            'remaining_amount'    => (float) $item->remaining_amount,
        ];
    }

    private function calculateBudgetUtilization(Budget $budget): array
    {
        $totalBudgeted       = (float) $budget->total_budget;
        $totalSpent          = (float) $budget->budgetItems->sum('spent_amount');
        $totalRemaining      = $totalBudgeted - $totalSpent;
        $utilizationPercent  = $totalBudgeted > 0
            ? round(($totalSpent / $totalBudgeted) * 100, 2)
            : 0;

        return [
            'total_budgeted'         => $totalBudgeted,
            'total_spent'            => $totalSpent,
            'total_remaining'        => $totalRemaining,
            'utilization_percentage' => $utilizationPercent,
            'items_count'            => $budget->budgetItems->count(),
            'categories_count'       => $budget->budgetItems
                ->pluck('chartOfAccount.account_type')
                ->filter()
                ->unique()
                ->count(),
        ];
    }

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

    private function getCategoryUtilization(Budget $budget)
    {
        return $budget->budgetItems
            ->groupBy(fn($item) => $item->chartOfAccount?->account_type ?? $item->category)
            ->map(function ($items, $type) {
                $budgeted    = $items->sum('budgeted_amount');
                $spent       = $items->sum('spent_amount');
                $remaining   = $budgeted - $spent;
                $utilization = $budgeted > 0 ? round(($spent / $budgeted) * 100, 2) : 0;

                return [
                    'category'             => $type,
                    'budgeted'             => $budgeted,
                    'spent'                => $spent,
                    'remaining'            => $remaining,
                    'utilization_percentage' => $utilization,
                    'items_count'          => $items->count(),
                    'accounts'             => $items->map(fn($i) => [
                        'account_code' => $i->chartOfAccount?->account_code,
                        'account_name' => $i->chartOfAccount?->account_name ?? $i->item_name,
                        'budgeted'     => (float) $i->budgeted_amount,
                        'spent'        => (float) $i->spent_amount,
                    ])->values(),
                ];
            })
            ->values();
    }

    private function getVarianceStatus(float $variancePercentage): string
    {
        if ($variancePercentage > 10)  return 'under_budget';
        if ($variancePercentage < -10) return 'over_budget';
        return 'on_track';
    }

    private function canApproveBudget(Budget $budget): bool
    {
        $user = Auth::user();
        if ($budget->created_by === $user->id) return false;
        return in_array($user->role, ['management', 'admin']);
    }

    private function canSubmitBudget(Budget $budget): bool
    {
        $user = Auth::user();
        if ($budget->created_by !== $user->id) return false;
        if ($budget->status !== 'draft') return false;
        return true;
    }

    private function canActivateBudget(Budget $budget): bool
    {
        return in_array(Auth::user()->role, ['management', 'admin']);
    }

    private function canCloseBudget(Budget $budget): bool
    {
        return in_array(Auth::user()->role, ['management', 'admin']);
    }

    private function canEditBudget(Budget $budget): bool
    {
        $user = Auth::user();
        if ($budget->status !== 'draft') return false;
        return $budget->created_by === $user->id || in_array($user->role, ['management', 'admin']);
    }
}
