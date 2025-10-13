<?php

namespace App\Http\Controllers;

use App\Models\Dividend;
use App\Models\Member;
use App\Models\MemberDividend;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Carbon\Carbon;

class DividendController extends Controller
{
    /**
     * Display a listing of dividends.
     */
    public function index(Request $request)
    {
        $query = Dividend::with(['calculatedBy', 'approvedBy'])
            ->orderBy('dividend_year', 'desc');

        // Apply filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('year') && $request->year) {
            $query->where('dividend_year', $request->year);
        }

        $dividends = $query->paginate(15);

        // Get available years for filter
        $availableYears = Dividend::distinct()
            ->orderBy('dividend_year', 'desc')
            ->pluck('dividend_year');

        return Inertia::render('Shared/Dividends/Index', [
            'dividends' => $dividends,
            'availableYears' => $availableYears,
            'filters' => $request->only(['status', 'year']),
            'stats' => $this->getDividendStats()
        ]);
    }

    /**
     * Show the form for creating a new dividend.
     */
    public function create()
    {
        $currentYear = now()->year;
        $previousYear = $currentYear - 1;

        // Check if dividend already exists for current/previous year
        $existingDividend = Dividend::where('dividend_year', $currentYear)
            ->orWhere('dividend_year', $previousYear)
            ->first();

        // Get financial data for calculation
        $financialData = $this->getFinancialDataForYear($previousYear);

        return Inertia::render('Shared/Dividends/Create', [
            'suggestedYear' => $currentYear,
            'previousYear' => $previousYear,
            'existingDividend' => $existingDividend,
            'financialData' => $financialData,
            'totalShares' => $this->getTotalShares($previousYear),
            'activeMembers' => Member::where('membership_status', 'active')->count()
        ]);
    }

    /**
     * Store a newly created dividend in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dividend_year' => [
                'required',
                'integer',
                'min:2000',
                'max:' . (now()->year + 1),
                Rule::unique('dividends', 'dividend_year')
            ],
            'total_profit' => 'required|numeric|min:0',
            'dividend_rate' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            // Calculate total dividends
            $totalShares = $this->getTotalShares($validated['dividend_year']);
            $totalDividends = ($totalShares * $validated['dividend_rate']) / 100;

            // Create dividend record
            $dividend = Dividend::create([
                'dividend_year' => $validated['dividend_year'],
                'total_profit' => $validated['total_profit'],
                'dividend_rate' => $validated['dividend_rate'],
                'total_dividends' => $totalDividends,
                'status' => 'calculated',
                'calculation_date' => now(),
                'calculated_by' => Auth::id(),
                'notes' => $validated['notes']
            ]);

            // Generate member dividends
            $this->generateMemberDividends($dividend);

            DB::commit();

            return redirect()->route('dividends.show', $dividend)
                ->with('success', 'Dividend calculated successfully for year ' . $validated['dividend_year']);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Dividend creation failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Failed to calculate dividend. Please try again.'
            ])->withInput();
        }
    }

    /**
     * Display the specified dividend.
     */
    public function show(Dividend $dividend)
    {
        $dividend->load(['calculatedBy', 'approvedBy']);

        // Get member dividends with pagination
        $memberDividends = MemberDividend::with(['member', 'transaction'])
            ->where('dividend_id', $dividend->id)
            ->orderBy('dividend_amount', 'desc')
            ->paginate(20);

        // Get summary statistics
        $stats = [
            'total_members' => MemberDividend::where('dividend_id', $dividend->id)->count(),
            'total_paid' => MemberDividend::where('dividend_id', $dividend->id)
                ->where('status', 'paid')
                ->sum('dividend_amount'),
            'total_pending' => MemberDividend::where('dividend_id', $dividend->id)
                ->where('status', 'pending')
                ->sum('dividend_amount'),
            'members_paid' => MemberDividend::where('dividend_id', $dividend->id)
                ->where('status', 'paid')
                ->count(),
            'members_pending' => MemberDividend::where('dividend_id', $dividend->id)
                ->where('status', 'pending')
                ->count()
        ];

        return Inertia::render('Shared/Dividends/Show', [
            'dividend' => $dividend,
            'memberDividends' => $memberDividends,
            'stats' => $stats,
            'canApprove' => $this->canApprove($dividend),
            'canDistribute' => $this->canDistribute($dividend)
        ]);
    }

    /**
     * Show the form for editing the specified dividend.
     */
    public function edit(Dividend $dividend)
    {
        // Only allow editing if dividend is in calculated status
        if ($dividend->status !== 'calculated') {
            return redirect()->route('dividends.show', $dividend)
                ->with('error', 'Only calculated dividends can be edited.');
        }

        $financialData = $this->getFinancialDataForYear($dividend->dividend_year);

        return Inertia::render('Shared/Dividends/Edit', [
            'dividend' => $dividend,
            'financialData' => $financialData,
            'totalShares' => $this->getTotalShares($dividend->dividend_year)
        ]);
    }

    /**
     * Update the specified dividend in storage.
     */
    public function update(Request $request, Dividend $dividend)
    {
        // Only allow updating if dividend is in calculated status
        if ($dividend->status !== 'calculated') {
            return redirect()->route('dividends.show', $dividend)
                ->with('error', 'Only calculated dividends can be updated.');
        }

        $validated = $request->validate([
            'total_profit' => 'required|numeric|min:0',
            'dividend_rate' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            // Recalculate total dividends
            $totalShares = $this->getTotalShares($dividend->dividend_year);
            $totalDividends = ($totalShares * $validated['dividend_rate']) / 100;

            // Update dividend record
            $dividend->update([
                'total_profit' => $validated['total_profit'],
                'dividend_rate' => $validated['dividend_rate'],
                'total_dividends' => $totalDividends,
                'notes' => $validated['notes']
            ]);

            // Regenerate member dividends
            MemberDividend::where('dividend_id', $dividend->id)->delete();
            $this->generateMemberDividends($dividend);

            DB::commit();

            return redirect()->route('dividends.show', $dividend)
                ->with('success', 'Dividend updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Dividend update failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Failed to update dividend. Please try again.'
            ])->withInput();
        }
    }

    /**
     * Remove the specified dividend from storage.
     */
    public function destroy(Dividend $dividend)
    {
        // Only allow deletion if dividend is in calculated status
        if ($dividend->status !== 'calculated') {
            return redirect()->route('dividends.index')
                ->with('error', 'Only calculated dividends can be deleted.');
        }

        try {
            DB::beginTransaction();

            // Delete member dividends first
            MemberDividend::where('dividend_id', $dividend->id)->delete();

            // Delete dividend
            $dividend->delete();

            DB::commit();

            return redirect()->route('dividends.index')
                ->with('success', 'Dividend deleted successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Dividend deletion failed: ' . $e->getMessage());
            
            return redirect()->route('dividends.index')
                ->with('error', 'Failed to delete dividend. Please try again.');
        }
    }

    /**
     * Calculate dividends for a specific year.
     */
    public function calculate(Request $request, $year)
    {
        $validated = $request->validate([
            'total_profit' => 'required|numeric|min:0',
            'dividend_rate' => 'required|numeric|min:0|max:100'
        ]);

        try {
            // Check if dividend already exists for this year
            $existingDividend = Dividend::where('dividend_year', $year)->first();
            if ($existingDividend) {
                return response()->json([
                    'error' => 'Dividend already exists for year ' . $year
                ], 422);
            }

            $totalShares = $this->getTotalShares($year);
            $totalDividends = ($totalShares * $validated['dividend_rate']) / 100;

            // Get member breakdown
            $memberBreakdown = $this->calculateMemberDividends($year, $validated['dividend_rate']);

            return response()->json([
                'total_shares' => $totalShares,
                'total_dividends' => $totalDividends,
                'member_count' => count($memberBreakdown),
                'member_breakdown' => $memberBreakdown,
                'average_dividend' => count($memberBreakdown) > 0 ? $totalDividends / count($memberBreakdown) : 0
            ]);

        } catch (\Exception $e) {
            Log::error('Dividend calculation failed: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Failed to calculate dividends. Please try again.'
            ], 500);
        }
    }

    /**
     * Approve a dividend.
     */
    public function approve(Request $request, Dividend $dividend)
    {
        if ($dividend->status !== 'calculated') {
            return redirect()->route('dividends.show', $dividend)
                ->with('error', 'Only calculated dividends can be approved.');
        }

        $validated = $request->validate([
            'approval_notes' => 'nullable|string|max:1000'
        ]);

        try {
            $dividend->update([
                'status' => 'approved',
                'approval_date' => now(),
                'approved_by' => Auth::id(),
                'approval_notes' => $validated['approval_notes']
            ]);

            return redirect()->route('dividends.show', $dividend)
                ->with('success', 'Dividend approved successfully.');

        } catch (\Exception $e) {
            Log::error('Dividend approval failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to approve dividend. Please try again.');
        }
    }

    /**
     * Distribute dividends to members.
     */
    public function distribute(Request $request, Dividend $dividend)
    {
        if ($dividend->status !== 'approved') {
            return redirect()->route('dividends.show', $dividend)
                ->with('error', 'Only approved dividends can be distributed.');
        }

        try {
            DB::beginTransaction();

            $memberDividends = MemberDividend::with('member')
                ->where('dividend_id', $dividend->id)
                ->where('status', 'pending')
                ->get();

            $distributedCount = 0;
            $failedCount = 0;

            foreach ($memberDividends as $memberDividend) {
                try {
                    // Find member's shares account
                    $sharesAccount = Account::where('member_id', $memberDividend->member_id)
                        ->where('account_type', 'shares')
                        ->where('is_active', true)
                        ->first();

                    if (!$sharesAccount) {
                        $failedCount++;
                        continue;
                    }

                    // Create dividend transaction
                    $transaction = Transaction::create([
                        'transaction_id' => $this->generateTransactionId(),
                        'account_id' => $sharesAccount->id,
                        'member_id' => $memberDividend->member_id,
                        'transaction_type' => 'dividend_payment',
                        'amount' => $memberDividend->dividend_amount,
                        'balance_before' => $sharesAccount->balance,
                        'balance_after' => $sharesAccount->balance + $memberDividend->dividend_amount,
                        'description' => "Dividend payment for year {$dividend->dividend_year}",
                        'reference_number' => "DIV-{$dividend->dividend_year}-{$memberDividend->member->membership_id}",
                        'payment_method' => 'system_transfer',
                        'status' => 'completed',
                        'processed_by' => Auth::id(),
                        'processed_at' => now()
                    ]);

                    // Update account balance
                    $sharesAccount->update([
                        'balance' => $sharesAccount->balance + $memberDividend->dividend_amount,
                        'available_balance' => $sharesAccount->available_balance + $memberDividend->dividend_amount,
                        'last_transaction_at' => now()
                    ]);

                    // Update member dividend status
                    $memberDividend->update([
                        'status' => 'paid',
                        'payment_date' => now(),
                        'transaction_id' => $transaction->id
                    ]);

                    $distributedCount++;

                } catch (\Exception $e) {
                    Log::error("Failed to distribute dividend to member {$memberDividend->member_id}: " . $e->getMessage());
                    $failedCount++;
                }
            }

            // Update dividend status if all distributed
            if ($failedCount === 0) {
                $dividend->update([
                    'status' => 'distributed',
                    'distribution_date' => now()
                ]);
            }

            DB::commit();

            $message = "Dividend distribution completed. {$distributedCount} members paid successfully.";
            if ($failedCount > 0) {
                $message .= " {$failedCount} payments failed.";
            }

            return redirect()->route('dividends.show', $dividend)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Dividend distribution failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to distribute dividends. Please try again.');
        }
    }

    /**
     * Reverse dividend distribution.
     */
    public function reverse(Request $request, Dividend $dividend)
    {
        if ($dividend->status !== 'distributed') {
            return redirect()->route('dividends.show', $dividend)
                ->with('error', 'Only distributed dividends can be reversed.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $memberDividends = MemberDividend::with(['member', 'transaction'])
                ->where('dividend_id', $dividend->id)
                ->where('status', 'paid')
                ->get();

            $reversedCount = 0;
            $failedCount = 0;

            foreach ($memberDividends as $memberDividend) {
                try {
                    if ($memberDividend->transaction) {
                        $transaction = $memberDividend->transaction;
                        $account = Account::find($transaction->account_id);

                        // Create reversal transaction
                        $reversalTransaction = Transaction::create([
                            'transaction_id' => $this->generateTransactionId(),
                            'account_id' => $account->id,
                            'member_id' => $memberDividend->member_id,
                            'transaction_type' => 'dividend_reversal',
                            'amount' => -$memberDividend->dividend_amount,
                            'balance_before' => $account->balance,
                            'balance_after' => $account->balance - $memberDividend->dividend_amount,
                            'description' => "Dividend reversal for year {$dividend->dividend_year} - {$validated['reason']}",
                            'reference_number' => "DIV-REV-{$dividend->dividend_year}-{$memberDividend->member->membership_id}",
                            'payment_method' => 'system_transfer',
                            'status' => 'completed',
                            'processed_by' => Auth::id(),
                            'processed_at' => now()
                        ]);

                        // Update account balance
                        $account->update([
                            'balance' => $account->balance - $memberDividend->dividend_amount,
                            'available_balance' => $account->available_balance - $memberDividend->dividend_amount,
                            'last_transaction_at' => now()
                        ]);

                        // Update member dividend status
                        $memberDividend->update([
                            'status' => 'pending',
                            'payment_date' => null,
                            'transaction_id' => null
                        ]);

                        $reversedCount++;
                    }

                } catch (\Exception $e) {
                    Log::error("Failed to reverse dividend for member {$memberDividend->member_id}: " . $e->getMessage());
                    $failedCount++;
                }
            }

            // Update dividend status
            $dividend->update([
                'status' => 'approved',
                'distribution_date' => null,
                'reversal_reason' => $validated['reason'],
                'reversed_by' => Auth::id(),
                'reversed_at' => now()
            ]);

            DB::commit();

            $message = "Dividend reversal completed. {$reversedCount} payments reversed successfully.";
            if ($failedCount > 0) {
                $message .= " {$failedCount} reversals failed.";
            }

            return redirect()->route('dividends.show', $dividend)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Dividend reversal failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to reverse dividends. Please try again.');
        }
    }

    /**
     * Show dividend members.
     */
    public function members(Dividend $dividend)
    {
        $memberDividends = MemberDividend::with(['member', 'transaction'])
            ->where('dividend_id', $dividend->id)
            ->orderBy('dividend_amount', 'desc')
            ->paginate(50);

        return Inertia::render('Dividends/Members', [
            'dividend' => $dividend,
            'memberDividends' => $memberDividends
        ]);
    }

    /**
     * Show member dividend details.
     */
    public function memberDetails(Dividend $dividend, Member $member)
    {
        $memberDividend = MemberDividend::with(['member', 'transaction'])
            ->where('dividend_id', $dividend->id)
            ->where('member_id', $member->id)
            ->firstOrFail();

        // Get member's share transactions for the dividend year
        $shareTransactions = Transaction::with('account')
            ->where('member_id', $member->id)
            ->whereHas('account', function ($query) {
                $query->where('account_type', 'shares');
            })
            ->whereYear('created_at', $dividend->dividend_year)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Dividends/MemberDetails', [
            'dividend' => $dividend,
            'memberDividend' => $memberDividend,
            'shareTransactions' => $shareTransactions
        ]);
    }

    /**
     * Pay individual member dividend.
     */
    public function payMemberDividend(Request $request, Dividend $dividend, Member $member)
    {
        if ($dividend->status !== 'approved') {
            return back()->with('error', 'Dividend must be approved before individual payments.');
        }

        $memberDividend = MemberDividend::where('dividend_id', $dividend->id)
            ->where('member_id', $member->id)
            ->first();

        if (!$memberDividend || $memberDividend->status === 'paid') {
            return back()->with('error', 'Member dividend not found or already paid.');
        }

        try {
            DB::beginTransaction();

            // Find member's shares account
            $sharesAccount = Account::where('member_id', $member->id)
                ->where('account_type', 'shares')
                ->where('is_active', true)
                ->first();

            if (!$sharesAccount) {
                return back()->with('error', 'Member does not have an active shares account.');
            }

            // Create dividend transaction
            $transaction = Transaction::create([
                'transaction_id' => $this->generateTransactionId(),
                'account_id' => $sharesAccount->id,
                'member_id' => $member->id,
                'transaction_type' => 'dividend_payment',
                'amount' => $memberDividend->dividend_amount,
                'balance_before' => $sharesAccount->balance,
                'balance_after' => $sharesAccount->balance + $memberDividend->dividend_amount,
                'description' => "Dividend payment for year {$dividend->dividend_year}",
                'reference_number' => "DIV-{$dividend->dividend_year}-{$member->membership_id}",
                'payment_method' => 'system_transfer',
                'status' => 'completed',
                'processed_by' => Auth::id(),
                'processed_at' => now()
            ]);

            // Update account balance
            $sharesAccount->update([
                'balance' => $sharesAccount->balance + $memberDividend->dividend_amount,
                'available_balance' => $sharesAccount->available_balance + $memberDividend->dividend_amount,
                'last_transaction_at' => now()
            ]);

            // Update member dividend status
            $memberDividend->update([
                'status' => 'paid',
                'payment_date' => now(),
                'transaction_id' => $transaction->id
            ]);

            DB::commit();

            return back()->with('success', 'Member dividend paid successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Member dividend payment failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to pay member dividend. Please try again.');
        }
    }

    /**
     * Generate dividend report.
     */
    public function report(Dividend $dividend)
    {
        $memberDividends = MemberDividend::with('member')
            ->where('dividend_id', $dividend->id)
            ->orderBy('dividend_amount', 'desc')
            ->get();

        $stats = [
            'total_members' => $memberDividends->count(),
            'total_dividends' => $memberDividends->sum('dividend_amount'),
            'average_dividend' => $memberDividends->avg('dividend_amount'),
            'highest_dividend' => $memberDividends->max('dividend_amount'),
            'lowest_dividend' => $memberDividends->min('dividend_amount'),
            'paid_count' => $memberDividends->where('status', 'paid')->count(),
            'pending_count' => $memberDividends->where('status', 'pending')->count(),
            'paid_amount' => $memberDividends->where('status', 'paid')->sum('dividend_amount'),
            'pending_amount' => $memberDividends->where('status', 'pending')->sum('dividend_amount')
        ];

        return Inertia::render('Dividends/Report', [
            'dividend' => $dividend,
            'memberDividends' => $memberDividends,
            'stats' => $stats
        ]);
    }

    /**
     * Show dividend history analytics.
     */
    public function history(Request $request)
    {
        $years = $request->get('years', 5);
        
        $dividends = Dividend::with(['calculatedBy', 'approvedBy'])
            ->orderBy('dividend_year', 'desc')
            ->limit($years)
            ->get();

        $analytics = [
            'yearly_trends' => $this->getYearlyTrends($dividends),
            'rate_trends' => $this->getRateTrends($dividends),
            'member_participation' => $this->getMemberParticipation($dividends),
            'profit_vs_dividends' => $this->getProfitVsDividends($dividends)
        ];

        return Inertia::render('Dividends/Analytics/History', [
            'dividends' => $dividends,
            'analytics' => $analytics,
            'years' => $years
        ]);
    }

    /**
     * Show dividend projections.
     */
    public function projections(Request $request)
    {
        $currentYear = now()->year;
        $projectionYears = $request->get('projection_years', 3);

        // Get historical data for projections
        $historicalData = Dividend::orderBy('dividend_year', 'desc')
            ->limit(5)
            ->get();

        $projections = $this->calculateProjections($historicalData, $projectionYears);

        return Inertia::render('Dividends/Analytics/Projections', [
            'projections' => $projections,
            'historicalData' => $historicalData,
            'currentYear' => $currentYear,
            'projectionYears' => $projectionYears
        ]);
    }

    /**
     * Calculate dividend projections.
     */
    public function calculateDividendProjection(Request $request)
    {
        $validated = $request->validate([
            'projected_profit' => 'required|numeric|min:0',
            'projected_rate' => 'required|numeric|min:0|max:100',
            'year' => 'required|integer|min:2000'
        ]);

        try {
            $totalShares = $this->getTotalShares($validated['year']);
            $totalDividends = ($totalShares * $validated['projected_rate']) / 100;
            $memberCount = Member::where('membership_status', 'active')->count();
            $averageDividend = $memberCount > 0 ? $totalDividends / $memberCount : 0;

            return response()->json([
                'total_shares' => $totalShares,
                'total_dividends' => $totalDividends,
                'member_count' => $memberCount,
                'average_dividend' => $averageDividend,
                'dividend_to_profit_ratio' => $validated['projected_profit'] > 0 ? 
                    ($totalDividends / $validated['projected_profit']) * 100 : 0
            ]);

        } catch (\Exception $e) {
            Log::error('Dividend projection calculation failed: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Failed to calculate projection. Please try again.'
            ], 500);
        }
    }

    // Private helper methods

    /**
     * Get dividend statistics.
     */
    private function getDividendStats()
    {
        $currentYear = now()->year;
        
        return [
            'total_dividends' => Dividend::count(),
            'current_year_dividend' => Dividend::where('dividend_year', $currentYear)->first(),
            'last_year_dividend' => Dividend::where('dividend_year', $currentYear - 1)->first(),
            'total_distributed' => Dividend::where('status', 'distributed')->sum('total_dividends'),
            'pending_approval' => Dividend::where('status', 'calculated')->count(),
            'approved_pending_distribution' => Dividend::where('status', 'approved')->count()
        ];
    }

    /**
     * Get financial data for a specific year.
     */
    private function getFinancialDataForYear($year)
    {
        // This would typically come from your financial records
        // For now, returning sample structure
        return [
            'total_income' => 0,
            'total_expenses' => 0,
            'net_profit' => 0,
            'loan_interest_income' => 0,
            'other_income' => 0,
            'operational_expenses' => 0,
            'provisions' => 0
        ];
    }

    /**
     * Get total shares for a specific year.
     */
    private function getTotalShares($year)
    {
        return Account::where('account_type', 'shares')
            ->where('is_active', true)
            ->whereHas('member', function ($query) {
                $query->where('membership_status', 'active');
            })
            ->whereYear('created_at', '<=', $year)
            ->sum('balance');
    }

    /**
     * Generate member dividends for a dividend record.
     */
    private function generateMemberDividends(Dividend $dividend)
    {
        $memberDividends = $this->calculateMemberDividends($dividend->dividend_year, $dividend->dividend_rate);

        foreach ($memberDividends as $memberDividend) {
            MemberDividend::create([
                'dividend_id' => $dividend->id,
                'member_id' => $memberDividend['member_id'],
                'shares_balance' => $memberDividend['shares_balance'],
                'dividend_amount' => $memberDividend['dividend_amount'],
                'status' => 'pending'
            ]);
        }
    }

   /**
     * Calculate member dividends.
     */
    private function calculateMemberDividends($year, $dividendRate)
    {
        $memberDividends = [];

        $membersWithShares = Member::with(['accounts' => function ($query) {
            $query->where('account_type', 'shares')
                  ->where('is_active', true);
        }])
        ->where('membership_status', 'active')
        ->whereHas('accounts', function ($query) {
            $query->where('account_type', 'shares')
                  ->where('is_active', true)
                  ->where('balance', '>', 0);
        })
        ->get();

        foreach ($membersWithShares as $member) {
            $sharesAccount = $member->accounts->first();
            
            if ($sharesAccount) {
                // Calculate dividend amount based on shares balance and dividend rate
                $dividendAmount = ($sharesAccount->balance * $dividendRate) / 100;
                
                // Round to 2 decimal places for currency
                $dividendAmount = round($dividendAmount, 2);
                
                $memberDividends[] = [
                    'member_id' => $member->id,
                    'member_name' => $member->first_name . ' ' . $member->last_name,
                    'membership_id' => $member->membership_id,
                    'shares_balance' => $sharesAccount->balance,
                    'dividend_amount' => $dividendAmount,
                    'dividend_rate' => $dividendRate
                ];
            }
        }

        return $memberDividends;
    }

    /**
     * Get yearly dividend trends.
     */
    private function getYearlyTrends($dividends)
    {
        return $dividends->map(function ($dividend) {
            return [
                'year' => $dividend->dividend_year,
                'total_dividends' => $dividend->total_dividends,
                'total_profit' => $dividend->total_profit,
                'dividend_rate' => $dividend->dividend_rate,
                'member_count' => MemberDividend::where('dividend_id', $dividend->id)->count(),
                'average_dividend' => $dividend->total_dividends > 0 ? 
                    $dividend->total_dividends / MemberDividend::where('dividend_id', $dividend->id)->count() : 0
            ];
        });
    }

    /**
     * Get dividend rate trends.
     */
    private function getRateTrends($dividends)
    {
        return $dividends->map(function ($dividend) {
            return [
                'year' => $dividend->dividend_year,
                'rate' => $dividend->dividend_rate,
                'profit_margin' => $dividend->total_profit > 0 ? 
                    ($dividend->total_dividends / $dividend->total_profit) * 100 : 0
            ];
        });
    }

    /**
     * Get member participation in dividends.
     */
    private function getMemberParticipation($dividends)
    {
        return $dividends->map(function ($dividend) {
            $totalMembers = Member::where('membership_status', 'active')
                ->whereYear('created_at', '<=', $dividend->dividend_year)
                ->count();
            
            $participatingMembers = MemberDividend::where('dividend_id', $dividend->id)->count();
            
            return [
                'year' => $dividend->dividend_year,
                'total_members' => $totalMembers,
                'participating_members' => $participatingMembers,
                'participation_rate' => $totalMembers > 0 ? 
                    ($participatingMembers / $totalMembers) * 100 : 0
            ];
        });
    }

    /**
     * Get profit vs dividends comparison.
     */
    private function getProfitVsDividends($dividends)
    {
        return $dividends->map(function ($dividend) {
            return [
                'year' => $dividend->dividend_year,
                'profit' => $dividend->total_profit,
                'dividends' => $dividend->total_dividends,
                'retained_earnings' => $dividend->total_profit - $dividend->total_dividends,
                'dividend_payout_ratio' => $dividend->total_profit > 0 ? 
                    ($dividend->total_dividends / $dividend->total_profit) * 100 : 0
            ];
        });
    }

    /**
     * Calculate dividend projections based on historical data.
     */
    private function calculateProjections($historicalData, $projectionYears)
    {
        $projections = [];
        $currentYear = now()->year;

        if ($historicalData->count() < 2) {
            // Not enough historical data for meaningful projections
            return $projections;
        }

        // Calculate average growth rates
        $profitGrowthRates = [];
        $dividendRateChanges = [];
        $memberGrowthRates = [];

        $historicalArray = $historicalData->toArray();
        
        for ($i = 0; $i < count($historicalArray) - 1; $i++) {
            $current = $historicalArray[$i];
            $previous = $historicalArray[$i + 1];

            // Profit growth rate
            if ($previous['total_profit'] > 0) {
                $profitGrowthRates[] = (($current['total_profit'] - $previous['total_profit']) / $previous['total_profit']) * 100;
            }

            // Dividend rate change
            $dividendRateChanges[] = $current['dividend_rate'] - $previous['dividend_rate'];
        }

        $avgProfitGrowth = count($profitGrowthRates) > 0 ? array_sum($profitGrowthRates) / count($profitGrowthRates) : 5;
        $avgDividendRateChange = count($dividendRateChanges) > 0 ? array_sum($dividendRateChanges) / count($dividendRateChanges) : 0;

        // Get current member count and calculate growth
        $currentMembers = Member::where('membership_status', 'active')->count();
        $avgMemberGrowth = 10; // Default 10% growth assumption

        // Generate projections
        $lastDividend = $historicalData->first();
        $baseProfit = $lastDividend->total_profit;
        $baseDividendRate = $lastDividend->dividend_rate;

        for ($i = 1; $i <= $projectionYears; $i++) {
            $projectionYear = $currentYear + $i;
            
            // Project profit with growth rate
            $projectedProfit = $baseProfit * pow(1 + ($avgProfitGrowth / 100), $i);
            
            // Project dividend rate (with some constraints)
            $projectedDividendRate = max(0, min(25, $baseDividendRate + ($avgDividendRateChange * $i)));
            
            // Project member count
            $projectedMembers = $currentMembers * pow(1 + ($avgMemberGrowth / 100), $i);
            
            // Estimate total shares (assuming average share balance growth)
            $avgSharesPerMember = $this->getTotalShares($currentYear) / $currentMembers;
            $projectedTotalShares = $projectedMembers * $avgSharesPerMember * pow(1.05, $i); // 5% annual growth in shares
            
            // Calculate projected dividends
            $projectedTotalDividends = ($projectedTotalShares * $projectedDividendRate) / 100;
            $projectedAvgDividend = $projectedMembers > 0 ? $projectedTotalDividends / $projectedMembers : 0;

            $projections[] = [
                'year' => $projectionYear,
                'projected_profit' => round($projectedProfit, 2),
                'projected_dividend_rate' => round($projectedDividendRate, 2),
                'projected_total_shares' => round($projectedTotalShares, 2),
                'projected_total_dividends' => round($projectedTotalDividends, 2),
                'projected_member_count' => round($projectedMembers),
                'projected_avg_dividend' => round($projectedAvgDividend, 2),
                'dividend_payout_ratio' => $projectedProfit > 0 ? 
                    round(($projectedTotalDividends / $projectedProfit) * 100, 2) : 0,
                'confidence_level' => $this->calculateConfidenceLevel($i, count($historicalData))
            ];
        }

        return $projections;
    }

    /**
     * Calculate confidence level for projections.
     */
    private function calculateConfidenceLevel($yearOffset, $historicalDataPoints)
    {
        // Base confidence starts at 80% and decreases with distance and limited historical data
        $baseConfidence = 80;
        $yearDecay = 10; // Decrease by 10% per year
        $dataPointBonus = min(20, $historicalDataPoints * 2); // Bonus for more historical data

        $confidence = $baseConfidence - ($yearOffset * $yearDecay) + $dataPointBonus;
        
        return max(20, min(90, $confidence)); // Keep between 20% and 90%
    }

    /**
     * Generate a unique transaction ID.
     */
    private function generateTransactionId()
    {
        do {
            $transactionId = 'TXN-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        } while (Transaction::where('transaction_id', $transactionId)->exists());

        return $transactionId;
    }

    /**
     * Check if user can approve dividend.
     */
    private function canApprove($dividend)
    {
        return $dividend->status === 'calculated' && 
               Auth::user()->role === 'admin' && 
               $dividend->calculated_by !== Auth::id();
    }

    /**
     * Check if user can distribute dividend.
     */
    private function canDistribute($dividend)
    {
        return $dividend->status === 'approved' && 
               Auth::user()->role === 'admin';
    }
}