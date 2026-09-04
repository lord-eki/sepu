<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Dividend;
use App\Models\DividendSetting;
use App\Models\Member;
use App\Models\MemberDividend;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DividendController extends Controller
{
    // -------------------------------------------------------------------------
    // SETTINGS HELPER
    // All rates and fees are stored in a `dividend_settings` table and managed
    // Keys expected:
    //   share_dividend_rate   – e.g. 17   (%)
    //   deposit_interest_rate – e.g. 11   (%)
    //   tax_rate              – e.g.  5   (%)
    //   processing_fee        – e.g. 300  (fixed KES)
    //   excise_duty           – e.g.  60  (fixed KES)
    // -------------------------------------------------------------------------

    /**
     * Load current dividend settings from the database.
     */
    private function getSettings(): array
    {
        $rows = DividendSetting::pluck('value', 'key')->toArray();

        return [
            'share_dividend_rate'   => (float) ($rows['share_dividend_rate']   ?? 17),
            'deposit_interest_rate' => (float) ($rows['deposit_interest_rate'] ?? 11),
            'tax_rate'              => (float) ($rows['tax_rate']               ??  5),
            'processing_fee'        => (float) ($rows['processing_fee']         ?? 300),
            'excise_duty'           => (float) ($rows['excise_duty']            ??  60),
        ];
    }



    /**
     * Get a member's Share Capital balance as at 31 Dec of $year.
     */
    private function getShareCapitalAsAtDec31(int $memberId, int $year): float
    {
        // Use the balance recorded on/before 31-Dec of the target year.
        $account = Account::where('member_id', $memberId)
            ->where('account_type', 'share_capital')
            ->where('is_active', true)
            ->first();

        if (! $account) {
            return 0.0;
        }

        // Sum all transactions up to Dec 31 of the target year
        $balance = Transaction::where('account_id', $account->id)
            ->whereDate('created_at', '<=', "{$year}-12-31")
            ->sum('amount');

        return max(0.0, (float) $balance);
    }

    /**
     * Calculate Share Dividend.
     *
     *   Dividend = Share Capital (by Dec 31) × share_dividend_rate
     */
    private function calcShareDividend(float $shareCapital, float $rate): float
    {
        if ($shareCapital <= 0) {
            return 0.0;
        }

        return $shareCapital * ($rate / 100);
    }

    /**
     *  Calculate monthly deposit interest and sum it.
     *
     * For each month Jan–Dec:
     *   Qualifying Deposit = (Days In Month / 365) × Monthly Balance
     *   Monthly Interest   = Qualifying Deposit × deposit_interest_rate
     *
     * Returns an array with:
     *   total_qualifying_deposits, total_interest, monthly_breakdown
     */
    private function calcDepositInterest(int $memberId, int $year, float $interestRate): array
    {
        $isLeapYear = date('L', strtotime("$year-01-01"));

        $daysInMonth = [
            1 => 31,
            2 => $isLeapYear ? 29 : 28,
            3 => 31,
            4 => 30,
            5 => 31,
            6 => 30,
            7 => 31,
            8 => 31,
            9 => 30,
            10 => 31,
            11 => 30,
            12 => 31,
        ];

        $account = Account::where('member_id', $memberId)
            ->where('account_type', 'share_deposits')
            ->where('is_active', true)
            ->first();

        $totalQualifying = 0.0;
        $totalInterest = 0.0;
        $breakdown = [];

        if (!$account) {
            return [
                'total_qualifying_deposits' => 0,
                'total_interest' => 0,
                'monthly_breakdown' => [],
            ];
        }
        foreach ($daysInMonth as $month => $days) {

            $lastDay = date('Y-m-t', strtotime("$year-$month-01"));

            $balance = (float) Transaction::where('account_id', $account?->id)
                ->whereDate('created_at', '<=', $lastDay)
                ->sum('amount');

            //  no negative balances
            $balance = max(0, $balance);

            if (!$account || $balance <= 0) {
                $breakdown[] = [
                    'month' => $month,
                    'days' => $days,
                    'balance' => 0,
                    'qualifying_deposit' => 0,
                    'monthly_interest' => 0,
                ];
                continue;
            }

            // CORE FORMULA
            $qualifying = round(($days / 365) * $balance, 2);
            $interest   = round($qualifying * ($interestRate / 100), 2);

           $totalQualifying = round($totalQualifying + $qualifying, 2);
            $totalInterest   = round($totalInterest + $interest, 2);

            $breakdown[] = [
                'month' => $month,
                'days' => $days,
                'balance' => $balance,
                'qualifying_deposit' => $qualifying,
                'monthly_interest' => $interest,
            ];
        }

        return [
            'total_qualifying_deposits' => $totalQualifying,
            'total_interest' => $totalInterest,
            'monthly_breakdown' => $breakdown,
        ];
    }

    /**
     * Combine and apply tax + deductions.
     *
     *   Gross        = Share Dividend + Deposit Interest
     *   Tax          = Gross × tax_rate
     *   Net Payable  = Gross - Tax - Processing Fee - Excise Duty
     */
    private function calcNetPayable(
        float $shareDividend,
        float $depositInterest,
        float $taxRate,
        float $processingFee,
        float $exciseDuty
    ): array {

        $gross = $shareDividend + $depositInterest;

        $tax = $gross * ($taxRate / 100);

        $net =  max(0,$gross - $tax - $processingFee - $exciseDuty);

        return [
            'share_dividend' => round($shareDividend, 2),
            'deposit_interest' => round($depositInterest, 2),
            'gross' => round($gross, 2),
            'tax' => round($tax, 2),
            'processing_fee' => $processingFee,
            'excise_duty' => $exciseDuty,
            'net_payable' => round($net, 2),
        ];
    }

    // -------------------------------------------------------------------------
    // CRUD & ACTIONS
    // -------------------------------------------------------------------------

    /**
     * Display a listing of dividends.
     */
    public function index(Request $request)
    {
        $query = Dividend::with(['calculatedBy', 'approvedBy'])
            ->orderBy('dividend_year', 'desc');

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('year') && $request->year) {
            $query->where('dividend_year', $request->year);
        }

        $dividends      = $query->paginate(15);
        $availableYears = Dividend::distinct()->orderBy('dividend_year', 'desc')->pluck('dividend_year');

        return Inertia::render('Shared/Dividends/Index', [
            'dividends'      => $dividends,
            'availableYears' => $availableYears,
            'filters'        => $request->only(['status', 'year']),
            'stats'          => $this->getDividendStats(),
            'settings'       => $this->getSettings(),
        ]);
    }

    /**
     * Show the form for creating a new dividend.
     */
    public function create()
    {
        $currentYear  = now()->year;
        $previousYear = $currentYear - 1;

        $existingDividend = Dividend::where('dividend_year', $currentYear)
            ->orWhere('dividend_year', $previousYear)
            ->first();

        $financialData = $this->getFinancialDataForYear($previousYear);
        $settings      = $this->getSettings();

        $totalShareCapital = Account::where('account_type', 'share_capital')
            ->where('is_active', true)
            ->whereHas('member', fn ($q) => $q->where('membership_status', 'active'))
            ->sum('balance');

        return Inertia::render('Shared/Dividends/Create', [
            'suggestedYear'    => $currentYear,
            'previousYear'     => $previousYear,
            'existingDividend' => $existingDividend,
            'financialData'    => $financialData,
            'totalShareCapital'=> $totalShareCapital,
            'activeMembers'    => Member::where('membership_status', 'active')->count(),
            'settings' => [
                    'share_dividend_rate'   => $settings['share_dividend_rate'],
                    'deposit_interest_rate' => $settings['deposit_interest_rate'],
                    'tax_rate'              => $settings['tax_rate'],
                ],
        ]);
    }

    /**
     * Store a newly created dividend in storage.
     *
     * The dividend year drives all calculations; rates come from settings.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dividend_year' => 'required|integer|min:2000|unique:dividends,dividend_year',
            'notes'         => 'nullable|string|max:2000',
        ]);

        $year     = (int) $validated['dividend_year'];
        $settings = $this->getSettings();

        try {
            DB::beginTransaction();

    
            $memberResults        = $this->computeAllMemberDividends($year, $settings);
            $totalShareDividends  = collect($memberResults)->sum('share_dividend');
            $totalDepositInterest = collect($memberResults)->sum('deposit_interest');
            $totalGross           = collect($memberResults)->sum('gross');
            $totalTax             = collect($memberResults)->sum('tax');
            $totalNet             = collect($memberResults)->sum('net_payable');

            $dividend = Dividend::create([
                'dividend_year'    => $year,
                'total_profit'     => 0, // Set separately if needed
                'dividend_rate'    => $settings['share_dividend_rate'],
                'total_dividends'  => round($totalNet, 2),
                'status'           => 'calculated',
                'calculation_date' => now(),
                'calculated_by'    => Auth::id(),
                'notes'            => json_encode([
                    'user_notes'              => $validated['notes'] ?? null,
                    'settings_snapshot'       => $settings,
                    'total_share_dividends'   => round($totalShareDividends, 2),
                    'total_deposit_interest'  => round($totalDepositInterest, 2),
                    'total_gross'             => round($totalGross, 2),
                    'total_tax'               => round($totalTax, 2),
                    'total_net'               => round($totalNet, 2),
                ]),
            ]);

            // Persist per-member records
            $this->persistMemberDividends($dividend, $memberResults);

            DB::commit();

            return redirect()->route('dividends.show', $dividend)
                ->with('success', "Dividend calculated successfully for year {$year}.");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Dividend creation failed: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Failed: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified dividend.
     */
    public function show(Dividend $dividend)
    {
        $dividend->load(['calculatedBy', 'approvedBy']);
        $metadata = json_decode($dividend->notes, true) ?? [];

        $memberDividends = MemberDividend::with(['member.user', 'transaction'])
            ->where('dividend_id', $dividend->id)
            ->orderBy('dividend_amount', 'desc')
            ->paginate(20);

        // Attach breakdown to each member dividend row
        $memberDividends->getCollection()->transform(function ($md) {
            $md->breakdown = json_decode($md->metadata ?? '{}', true);
            return $md;
        });

        $stats = [
            'total_members'   => MemberDividend::where('dividend_id', $dividend->id)->count(),
            'total_paid'      => MemberDividend::where('dividend_id', $dividend->id)->where('status', 'paid')->sum('dividend_amount'),
            'total_pending'   => MemberDividend::where('dividend_id', $dividend->id)->where('status', 'pending')->sum('dividend_amount'),
            'members_paid'    => MemberDividend::where('dividend_id', $dividend->id)->where('status', 'paid')->count(),
            'members_pending' => MemberDividend::where('dividend_id', $dividend->id)->where('status', 'pending')->count(),
            'settings'        => $metadata['settings_snapshot'] ?? [],
            'totals' => [
                'share_dividends'  => $metadata['total_share_dividends']  ?? 0,
                'deposit_interest' => $metadata['total_deposit_interest'] ?? 0,
                'gross'            => $metadata['total_gross']             ?? 0,
                'tax'              => $metadata['total_tax']               ?? 0,
                'net'              => $metadata['total_net']               ?? 0,
            ],
        ];

        return Inertia::render('Shared/Dividends/Show', [
            'dividend'        => $dividend,
            'metadata'        => $metadata,
            'memberDividends' => $memberDividends,
            'stats'           => $stats,
            'canApprove'      => $this->canApprove($dividend),
            'canDistribute'   => $this->canDistribute($dividend),
        ]);
    }

    /**
     * Show the form for editing the specified dividend.
     */
    public function edit(Dividend $dividend)
    {
        if ($dividend->status !== 'calculated') {
            return redirect()->route('dividends.show', $dividend)
                ->with('error', 'Only calculated dividends can be edited.');
        }

        return Inertia::render('Shared/Dividends/Edit', [
            'dividend'      => $dividend,
            'financialData' => $this->getFinancialDataForYear($dividend->dividend_year),
            'settings'      => $this->getSettings(),
        ]);
    }

    /**
     * Update (recalculate) the specified dividend.
     */
    public function update(Request $request, Dividend $dividend)
    {
        if ($dividend->status !== 'calculated') {
            return redirect()->route('dividends.show', $dividend)
                ->with('error', 'Only calculated dividends can be updated.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:2000',
        ]);

        $year     = (int) $dividend->dividend_year;
        $settings = $this->getSettings();

        try {
            DB::beginTransaction();

            $memberResults        = $this->computeAllMemberDividends($year, $settings);
            $totalShareDividends  = collect($memberResults)->sum('share_dividend');
            $totalDepositInterest = collect($memberResults)->sum('deposit_interest');
            $totalGross           = collect($memberResults)->sum('gross');
            $totalTax             = collect($memberResults)->sum('tax');
            $totalNet             = collect($memberResults)->sum('net_payable');

            $dividend->update([
                'dividend_rate'   => $settings['share_dividend_rate'],
                'total_dividends' => round($totalNet, 2),
                'notes'           => json_encode([
                    'user_notes'             => $validated['notes'] ?? null,
                    'settings_snapshot'      => $settings,
                    'total_share_dividends'  => round($totalShareDividends, 2),
                    'total_deposit_interest' => round($totalDepositInterest, 2),
                    'total_gross'            => round($totalGross, 2),
                    'total_tax'              => round($totalTax, 2),
                    'total_net'              => round($totalNet, 2),
                ]),
            ]);

            MemberDividend::where('dividend_id', $dividend->id)->delete();
            $this->persistMemberDividends($dividend, $memberResults);

            DB::commit();

            return redirect()->route('dividends.show', $dividend)
                ->with('success', 'Dividend recalculated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Dividend update failed: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Failed to update dividend. Please try again.'])->withInput();
        }
    }

    /**
     * Remove the specified dividend from storage.
     */
    public function destroy(Dividend $dividend)
    {
        if ($dividend->status !== 'calculated') {
            return redirect()->route('dividends.index')
                ->with('error', 'Only calculated dividends can be deleted.');
        }

        try {
            DB::beginTransaction();
            MemberDividend::where('dividend_id', $dividend->id)->delete();
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
     * Live preview/calculation endpoint (JSON).
     */
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'dividend_year' => 'required|integer|min:2000',
        ]);

        $year     = (int) $validated['dividend_year'];
        $settings = $this->getSettings();

        try {
            $memberResults = $this->computeAllMemberDividends($year, $settings);

            $summary = [
                'member_count'           => count($memberResults),
                'total_share_dividends'  => round(collect($memberResults)->sum('share_dividend'), 2),
                'total_deposit_interest' => round(collect($memberResults)->sum('deposit_interest'), 2),
                'total_gross'            => round(collect($memberResults)->sum('gross'), 2),
                'total_tax'              => round(collect($memberResults)->sum('tax'), 2),
                'total_processing_fees'  => round(collect($memberResults)->count() * $settings['processing_fee'], 2),
                'total_excise_duties'    => round(collect($memberResults)->count() * $settings['excise_duty'], 2),
                'total_net_payable'      => round(collect($memberResults)->sum('net_payable'), 2),
            ];

            // Return top 100 for preview table
            $preview = array_slice(
                collect($memberResults)
                    ->sortByDesc('net_payable')
                    ->values()
                    ->toArray(),
                0,
                100
            );

            return response()->json([
                'success'  => true,
                'settings' => $settings,
                'summary'  => $summary,
                'preview'  => $preview,
            ]);

        } catch (\Exception $e) {
            Log::error('Dividend preview failed: ' . $e->getMessage());

            return response()->json(['error' => 'Preview failed: ' . $e->getMessage()], 500);
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
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $dividend->update([
                'status'         => 'approved',
                'approval_date'  => now(),
                'approved_by'    => Auth::id(),
                'approval_notes' => $validated['approval_notes'],
            ]);

            return redirect()->route('dividends.show', $dividend)
                ->with('success', 'Dividend approved successfully.');

        } catch (\Exception $e) {
            Log::error('Dividend approval failed: ' . $e->getMessage());

            return back()->with('error', 'Failed to approve dividend. Please try again.');
        }
    }

    /**
     * Distribute dividends – credit net payable to each member's FOSA account.
     *
     * Credit Net to FOSA account.
     */
    public function distribute(Request $request, Dividend $dividend)
    {
        if ($dividend->status !== 'approved') {
            return redirect()->route('dividends.show', $dividend)
                ->with('error', 'Only approved dividends can be distributed.');
        }

        try {
            DB::beginTransaction();

            $memberDividends  = MemberDividend::with('member')
                ->where('dividend_id', $dividend->id)
                ->where('status', 'pending')
                ->get();

            $distributedCount = 0;
            $failedCount      = 0;

            foreach ($memberDividends as $memberDividend) {
                try {
                    // Credit to the member's FOSA account
                    $fosaAccount = Account::where('member_id', $memberDividend->member_id)
                        ->where('account_type', 'fosa')
                        ->where('is_active', true)
                        ->first();

                    if (! $fosaAccount) {
                        Log::warning("No FOSA account for member {$memberDividend->member_id}");
                        $failedCount++;
                        continue;
                    }

                    $netAmount = $memberDividend->dividend_amount;

                    $transaction = Transaction::create([
                        'transaction_id'   => $this->generateTransactionId(),
                        'account_id'       => $fosaAccount->id,
                        'member_id'        => $memberDividend->member_id,
                        'transaction_type' => 'dividend_payment',
                        'amount'           => $netAmount,
                        'balance_before'   => $fosaAccount->balance,
                        'balance_after'    => $fosaAccount->balance + $netAmount,
                        'description'      => "Dividend net credit for year {$dividend->dividend_year}",
                        'reference_number' => "DIV-{$dividend->dividend_year}-{$memberDividend->member->membership_id}",
                        'payment_method'   => 'system_transfer',
                        'status'           => 'completed',
                        'processed_by'     => Auth::id(),
                        'processed_at'     => now(),
                    ]);

                    $fosaAccount->update([
                        'balance'              => $fosaAccount->balance + $netAmount,
                        'available_balance'    => $fosaAccount->available_balance + $netAmount,
                        'last_transaction_at'  => now(),
                    ]);

                    $memberDividend->update([
                        'status'         => 'paid',
                        'payment_date'   => now(),
                        'transaction_id' => $transaction->id,
                    ]);

                    $distributedCount++;

                } catch (\Exception $e) {
                    Log::error("Failed to distribute dividend to member {$memberDividend->member_id}: " . $e->getMessage());
                    $failedCount++;
                }
            }

            if ($failedCount === 0) {
                $dividend->update([
                    'status'            => 'distributed',
                    'distribution_date' => now(),
                ]);
            }

            DB::commit();

            $message = "Dividend distribution completed. {$distributedCount} members paid successfully.";
            if ($failedCount > 0) {
                $message .= " {$failedCount} payments failed.";
            }

            return redirect()->route('dividends.show', $dividend)->with('success', $message);

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
            'reason' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $memberDividends = MemberDividend::with(['member', 'transaction'])
                ->where('dividend_id', $dividend->id)
                ->where('status', 'paid')
                ->get();

            $reversedCount = 0;
            $failedCount   = 0;

            foreach ($memberDividends as $memberDividend) {
                try {
                    if ($memberDividend->transaction) {
                        $transaction = $memberDividend->transaction;
                        $account     = Account::find($transaction->account_id);

                        Transaction::create([
                            'transaction_id'   => $this->generateTransactionId(),
                            'account_id'       => $account->id,
                            'member_id'        => $memberDividend->member_id,
                            'transaction_type' => 'dividend_reversal',
                            'amount'           => -$memberDividend->dividend_amount,
                            'balance_before'   => $account->balance,
                            'balance_after'    => $account->balance - $memberDividend->dividend_amount,
                            'description'      => "Dividend reversal for year {$dividend->dividend_year} – {$validated['reason']}",
                            'reference_number' => "DIV-REV-{$dividend->dividend_year}-{$memberDividend->member->membership_id}",
                            'payment_method'   => 'system_transfer',
                            'status'           => 'completed',
                            'processed_by'     => Auth::id(),
                            'processed_at'     => now(),
                        ]);

                        $account->update([
                            'balance'             => $account->balance - $memberDividend->dividend_amount,
                            'available_balance'   => $account->available_balance - $memberDividend->dividend_amount,
                            'last_transaction_at' => now(),
                        ]);

                        $memberDividend->update([
                            'status'         => 'pending',
                            'payment_date'   => null,
                            'transaction_id' => null,
                        ]);

                        $reversedCount++;
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to reverse dividend for member {$memberDividend->member_id}: " . $e->getMessage());
                    $failedCount++;
                }
            }

            $dividend->update([
                'status'           => 'approved',
                'distribution_date'=> null,
                'reversal_reason'  => $validated['reason'],
                'reversed_by'      => Auth::id(),
                'reversed_at'      => now(),
            ]);

            DB::commit();

            $message = "Dividend reversal completed. {$reversedCount} payments reversed successfully.";
            if ($failedCount > 0) {
                $message .= " {$failedCount} reversals failed.";
            }

            return redirect()->route('dividends.show', $dividend)->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Dividend reversal failed: ' . $e->getMessage());

            return back()->with('error', 'Failed to reverse dividends. Please try again.');
        }
    }

    // -------------------------------------------------------------------------
    // MEMBER-LEVEL ACTIONS
    // -------------------------------------------------------------------------

    public function members(Dividend $dividend)
    {
        $memberDividends = MemberDividend::with(['member', 'transaction'])
            ->where('dividend_id', $dividend->id)
            ->orderBy('dividend_amount', 'desc')
            ->paginate(50);

        return Inertia::render('Dividends/Members', [
            'dividend'        => $dividend,
            'memberDividends' => $memberDividends,
        ]);
    }

    public function memberDetails(Dividend $dividend, Member $member)
    {
        $memberDividend = MemberDividend::with(['member', 'transaction'])
            ->where('dividend_id', $dividend->id)
            ->where('member_id', $member->id)
            ->firstOrFail();

        $breakdown = json_decode($memberDividend->metadata ?? '{}', true);

        $depositTransactions = Transaction::with('account')
            ->where('member_id', $member->id)
            ->whereHas('account', fn ($q) => $q->where('account_type', 'savings'))
            ->whereYear('created_at', $dividend->dividend_year)
            ->orderBy('created_at')
            ->get();

        return Inertia::render('Dividends/MemberDetails', [
            'dividend'            => $dividend,
            'memberDividend'      => $memberDividend,
            'breakdown'           => $breakdown,
            'depositTransactions' => $depositTransactions,
        ]);
    }

    /**
     * Pay an individual member's dividend (credit to FOSA).
     */
    public function payMemberDividend(Request $request, Dividend $dividend, Member $member)
    {
        if ($dividend->status !== 'approved') {
            return back()->with('error', 'Dividend must be approved before individual payments.');
        }

        $memberDividend = MemberDividend::where('dividend_id', $dividend->id)
            ->where('member_id', $member->id)
            ->first();

        if (! $memberDividend || $memberDividend->status === 'paid') {
            return back()->with('error', 'Member dividend not found or already paid.');
        }

        try {
            DB::beginTransaction();

            $fosaAccount = Account::where('member_id', $member->id)
                ->where('account_type', 'fosa')
                ->where('is_active', true)
                ->first();

            if (! $fosaAccount) {
                return back()->with('error', 'Member does not have an active FOSA account.');
            }

            $netAmount = $memberDividend->dividend_amount;

            $transaction = Transaction::create([
                'transaction_id'   => $this->generateTransactionId(),
                'account_id'       => $fosaAccount->id,
                'member_id'        => $member->id,
                'transaction_type' => 'dividend_payment',
                'amount'           => $netAmount,
                'balance_before'   => $fosaAccount->balance,
                'balance_after'    => $fosaAccount->balance + $netAmount,
                'description'      => "Dividend net credit for year {$dividend->dividend_year}",
                'reference_number' => "DIV-{$dividend->dividend_year}-{$member->membership_id}",
                'payment_method'   => 'system_transfer',
                'status'           => 'completed',
                'processed_by'     => Auth::id(),
                'processed_at'     => now(),
            ]);

            $fosaAccount->update([
                'balance'             => $fosaAccount->balance + $netAmount,
                'available_balance'   => $fosaAccount->available_balance + $netAmount,
                'last_transaction_at' => now(),
            ]);

            $memberDividend->update([
                'status'         => 'paid',
                'payment_date'   => now(),
                'transaction_id' => $transaction->id,
            ]);

            DB::commit();

            return back()->with('success', 'Member dividend paid successfully to FOSA account.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Member dividend payment failed: ' . $e->getMessage());

            return back()->with('error', 'Failed to pay member dividend. Please try again.');
        }
    }

    // -------------------------------------------------------------------------
    // REPORTING & ANALYTICS
    // -------------------------------------------------------------------------

    public function report(Dividend $dividend)
    {
        $memberDividends = MemberDividend::with('member')
            ->where('dividend_id', $dividend->id)
            ->orderBy('dividend_amount', 'desc')
            ->get();

        $stats = [
            'total_members'    => $memberDividends->count(),
            'total_dividends'  => $memberDividends->sum('dividend_amount'),
            'average_dividend' => $memberDividends->avg('dividend_amount'),
            'highest_dividend' => $memberDividends->max('dividend_amount'),
            'lowest_dividend'  => $memberDividends->min('dividend_amount'),
            'paid_count'       => $memberDividends->where('status', 'paid')->count(),
            'pending_count'    => $memberDividends->where('status', 'pending')->count(),
            'paid_amount'      => $memberDividends->where('status', 'paid')->sum('dividend_amount'),
            'pending_amount'   => $memberDividends->where('status', 'pending')->sum('dividend_amount'),
        ];

        return Inertia::render('Dividends/Report', [
            'dividend'        => $dividend,
            'memberDividends' => $memberDividends,
            'stats'           => $stats,
        ]);
    }

    public function history(Request $request)
    {
        $years = $request->get('years', 5);

        $dividends = Dividend::with(['calculatedBy', 'approvedBy'])
            ->orderBy('dividend_year', 'desc')
            ->limit($years)
            ->get();

        $analytics = [
            'yearly_trends'        => $this->getYearlyTrends($dividends),
            'rate_trends'          => $this->getRateTrends($dividends),
            'member_participation' => $this->getMemberParticipation($dividends),
            'profit_vs_dividends'  => $this->getProfitVsDividends($dividends),
        ];

        return Inertia::render('Dividends/Analytics/History', [
            'dividends'  => $dividends,
            'analytics'  => $analytics,
            'years'      => $years,
        ]);
    }

    public function projections(Request $request)
    {
        $currentYear     = now()->year;
        $projectionYears = $request->get('projection_years', 3);
        $historicalData  = Dividend::orderBy('dividend_year', 'desc')->limit(5)->get();
        $projections     = $this->calculateProjections($historicalData, $projectionYears);

        return Inertia::render('Dividends/Analytics/Projections', [
            'projections'     => $projections,
            'historicalData'  => $historicalData,
            'currentYear'     => $currentYear,
            'projectionYears' => $projectionYears,
        ]);
    }

    public function calculateDividendProjection(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2000',
        ]);

        $settings    = $this->getSettings();
        $memberCount = Member::where('membership_status', 'active')->count();

        try {
            $results   = $this->computeAllMemberDividends((int) $validated['year'], $settings);
            $totalNet  = collect($results)->sum('net_payable');
            $avgNet    = $memberCount > 0 ? $totalNet / $memberCount : 0;

            return response()->json([
                'member_count'     => $memberCount,
                'total_net'        => round($totalNet, 2),
                'average_net'      => round($avgNet, 2),
                'settings'         => $settings,
            ]);

        } catch (\Exception $e) {
            Log::error('Dividend projection calculation failed: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to calculate projection. Please try again.'], 500);
        }
    }

    // -------------------------------------------------------------------------
    // PRIVATE HELPERS
    // -------------------------------------------------------------------------

    /**
     * Compute dividend figures for every active member for $year.
     *
     * Returns an array of arrays, keyed by member_id, with all breakdown fields.
     */
    private function computeAllMemberDividends(int $year, array $settings): array
    {
        $members = Member::where('membership_status', 'active')
            ->get();
 
        $results = [];
 
        foreach ($members as $member) {
            // Share Capital as at Dec 31 
            $shareCapital = $this->getShareCapitalAsAtDec31($member->id, $year);
 
            // Deposit Interest
            $interestData = $this->calcDepositInterest(
                $member->id,
                $year,
                $settings['deposit_interest_rate']
            );
 
       
            if ($shareCapital <= 0 && $interestData['total_qualifying_deposits'] <= 0) {
                continue;
            }
 
            //Share Dividend (0 if no share capital) 
            $shareDividend = $this->calcShareDividend(
                $shareCapital,
                $settings['share_dividend_rate']
            );
 
            //  Gross → Tax → Net 
            $calc = $this->calcNetPayable(
                $shareDividend,
                $interestData['total_interest'],
                $settings['tax_rate'],
                $settings['processing_fee'],
                $settings['excise_duty']
            );
 
            $results[] = array_merge([
                'member_id'                  => $member->id,
                'member_name'                => $member->first_name . ' ' . $member->last_name,
                'membership_id'              => $member->membership_id,
                'share_capital'              => round($shareCapital, 2),
                'total_qualifying_deposits'  => $interestData['total_qualifying_deposits'],
                'monthly_breakdown'          => $interestData['monthly_breakdown'],
            ], $calc);
        }
 
        return $results;
    }

    /**
     * Persist per-member dividend rows into member_dividends.
     * The dividend_amount stored is the NET PAYABLE (after tax and fees).
     */
    private function persistMemberDividends(Dividend $dividend, array $memberResults): void
    {
        foreach ($memberResults as $result) {
            MemberDividend::create([
                'dividend_id'    => $dividend->id,
                'member_id'      => $result['member_id'],
                'shares_balance' => $result['share_capital'],
                'dividend_amount'=> $result['net_payable'],   // Net payable stored here
                'status'         => 'pending',
                'metadata'       => json_encode([
                    'share_capital'             => $result['share_capital'],
                    'share_dividend'            => $result['share_dividend'],
                    'total_qualifying_deposits' => $result['total_qualifying_deposits'],
                    'deposit_interest'          => $result['deposit_interest'],
                    'gross'                     => $result['gross'],
                    'processing_fee'            => $result['processing_fee'],
                    'excise_duty'               => $result['excise_duty'],
                    'net_payable'               => $result['net_payable'],
                    'monthly_breakdown'         => $result['monthly_breakdown'],
                ]),
            ]);
        }
    }

    private function getDividendStats(): array
    {
        $currentYear = now()->year;

        return [
            'total_dividends'                => Dividend::count(),
            'current_year_dividend'          => Dividend::where('dividend_year', $currentYear)->first(),
            'last_year_dividend'             => Dividend::where('dividend_year', $currentYear - 1)->first(),
            'total_distributed'              => Dividend::where('status', 'distributed')->sum('total_dividends'),
            'pending_approval'               => Dividend::where('status', 'calculated')->count(),
            'approved_pending_distribution'  => Dividend::where('status', 'approved')->count(),
        ];
    }

    private function getFinancialDataForYear($year): array
    {
        return [
            'total_income'        => 0,
            'total_expenses'      => 0,
            'net_profit'          => 0,
            'loan_interest_income'=> 0,
            'other_income'        => 0,
            'operational_expenses'=> 0,
            'provisions'          => 0,
        ];
    }

    private function getYearlyTrends($dividends)
    {
        return $dividends->map(function ($dividend) {
            return [
                'year'             => $dividend->dividend_year,
                'total_dividends'  => $dividend->total_dividends,
                'total_profit'     => $dividend->total_profit,
                'dividend_rate'    => $dividend->dividend_rate,
                'member_count'     => MemberDividend::where('dividend_id', $dividend->id)->count(),
                'average_dividend' => $dividend->total_dividends > 0
                    ? $dividend->total_dividends / max(1, MemberDividend::where('dividend_id', $dividend->id)->count())
                    : 0,
            ];
        });
    }

    private function getRateTrends($dividends)
    {
        return $dividends->map(function ($dividend) {
            return [
                'year'           => $dividend->dividend_year,
                'rate'           => $dividend->dividend_rate,
                'profit_margin'  => $dividend->total_profit > 0
                    ? ($dividend->total_dividends / $dividend->total_profit) * 100
                    : 0,
            ];
        });
    }

    private function getMemberParticipation($dividends)
    {
        return $dividends->map(function ($dividend) {
            $totalMembers        = Member::where('membership_status', 'active')
                ->whereYear('created_at', '<=', $dividend->dividend_year)->count();
            $participatingMembers = MemberDividend::where('dividend_id', $dividend->id)->count();

            return [
                'year'                 => $dividend->dividend_year,
                'total_members'        => $totalMembers,
                'participating_members'=> $participatingMembers,
                'participation_rate'   => $totalMembers > 0
                    ? ($participatingMembers / $totalMembers) * 100
                    : 0,
            ];
        });
    }

    private function getProfitVsDividends($dividends)
    {
        return $dividends->map(function ($dividend) {
            return [
                'year'                  => $dividend->dividend_year,
                'profit'                => $dividend->total_profit,
                'dividends'             => $dividend->total_dividends,
                'retained_earnings'     => $dividend->total_profit - $dividend->total_dividends,
                'dividend_payout_ratio' => $dividend->total_profit > 0
                    ? ($dividend->total_dividends / $dividend->total_profit) * 100
                    : 0,
            ];
        });
    }

    private function calculateProjections($historicalData, $projectionYears): array
    {
        $projections = [];
        $currentYear = now()->year;

        if ($historicalData->count() < 2) {
            return $projections;
        }

        $profitGrowthRates    = [];
        $dividendRateChanges  = [];
        $historicalArray      = $historicalData->toArray();

        for ($i = 0; $i < count($historicalArray) - 1; $i++) {
            $current  = $historicalArray[$i];
            $previous = $historicalArray[$i + 1];

            if ($previous['total_profit'] > 0) {
                $profitGrowthRates[] = (($current['total_profit'] - $previous['total_profit']) / $previous['total_profit']) * 100;
            }
            $dividendRateChanges[] = $current['dividend_rate'] - $previous['dividend_rate'];
        }

        $avgProfitGrowth      = count($profitGrowthRates)   > 0 ? array_sum($profitGrowthRates) / count($profitGrowthRates)     : 5;
        $avgDividendRateChange= count($dividendRateChanges) > 0 ? array_sum($dividendRateChanges) / count($dividendRateChanges) : 0;
        $currentMembers       = Member::where('membership_status', 'active')->count();
        $avgMemberGrowth      = 10;
        $lastDividend         = $historicalData->first();
        $baseProfit           = $lastDividend->total_profit;
        $baseDividendRate     = $lastDividend->dividend_rate;

        for ($i = 1; $i <= $projectionYears; $i++) {
            $projectionYear        = $currentYear + $i;
            $projectedProfit       = $baseProfit * pow(1 + ($avgProfitGrowth / 100), $i);
            $projectedDividendRate = max(0, min(25, $baseDividendRate + ($avgDividendRateChange * $i)));
            $projectedMembers      = $currentMembers * pow(1 + ($avgMemberGrowth / 100), $i);
            $avgSharesPerMember    = $currentMembers > 0
                ? Account::where('account_type', 'share_capital')->where('is_active', true)->sum('balance') / $currentMembers
                : 0;
            $projectedTotalShares  = $projectedMembers * $avgSharesPerMember * pow(1.05, $i);
            $projectedTotalDiv     = ($projectedTotalShares * $projectedDividendRate) / 100;
            $projectedAvgDiv       = $projectedMembers > 0 ? $projectedTotalDiv / $projectedMembers : 0;

            $projections[] = [
                'year'                       => $projectionYear,
                'projected_profit'           => round($projectedProfit, 2),
                'projected_dividend_rate'    => round($projectedDividendRate, 2),
                'projected_total_shares'     => round($projectedTotalShares, 2),
                'projected_total_dividends'  => round($projectedTotalDiv, 2),
                'projected_member_count'     => round($projectedMembers),
                'projected_avg_dividend'     => round($projectedAvgDiv, 2),
                'dividend_payout_ratio'      => $projectedProfit > 0
                    ? round(($projectedTotalDiv / $projectedProfit) * 100, 2)
                    : 0,
                'confidence_level'           => $this->calculateConfidenceLevel($i, $historicalData->count()),
            ];
        }

        return $projections;
    }

    private function calculateConfidenceLevel(int $yearOffset, int $historicalDataPoints): float
    {
        $confidence = 80 - ($yearOffset * 10) + min(20, $historicalDataPoints * 2);
        return max(20, min(90, $confidence));
    }

    private function generateTransactionId(): string
    {
        do {
            $id = 'TXN-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        } while (Transaction::where('transaction_id', $id)->exists());

        return $id;
    }

    protected function canApprove(Dividend $dividend): bool
    {
        return $dividend->status === 'calculated' && Auth::user()->role === 'admin';
    }

    private function canDistribute(Dividend $dividend): bool
    {
        return $dividend->status === 'approved' && Auth::user()->role === 'admin';
    }
}
