<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Dividend;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\MemberDividend;
use App\Models\ScheduleExecutionLog;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    // =========================================================================
    //  SCHEDULE INDEX
    // =========================================================================

    /**
     * Unified schedule landing page.
     * Shows the 4 schedule types and a recent execution log.
     */
    public function index()
    {
        $recentLogs = ScheduleExecutionLog::with('executedBy')
            ->orderByDesc('execution_date')
            ->limit(20)
            ->get()
            ->map(fn($log) => [
                'id'                      => $log->id,
                'schedule_type'           => $log->schedule_type,
                'schedule_type_label'     => $log->schedule_type_label,
                'period_label'            => $log->period_label,
                'execution_date'          => $log->execution_date->format('Y-m-d H:i'),
                'executed_by'             => $log->executedBy?->name,
                'total_records_processed' => $log->total_records_processed,
                'total_records_failed'    => $log->total_records_failed,
                'total_amount_posted'     => (float) $log->total_amount_posted,
                'status'                  => $log->status,
            ]);

        return Inertia::render('Admin/Schedule/Index', [
            'recentLogs'  => $recentLogs,
            'currentMonth'=> Carbon::now()->format('Y-m'),
            'currentYear' => Carbon::now()->year,
        ]);
    }

    // =========================================================================
    //  1. MONTHLY DEPOSITS
    // =========================================================================

    public function monthlyDeposit(Request $request)
    {
        $month = $request->month ?? Carbon::now()->format('Y-m');
        [$year, $mon] = explode('-', $month);

        $alreadyRun = ScheduleExecutionLog::alreadyRun('monthly_deposits', (int)$year, (int)$mon);

        $members = Member::with([
            'user',
            'accounts' => fn($q) => $q->where('account_type', 'share_deposits')->where('is_active', true),
        ])
            ->where('membership_status', 'active')
            ->orderBy('first_name')
            ->get();

        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate   = Carbon::parse($month)->endOfMonth();

        $existingDeposits = Transaction::where('transaction_type', 'deposit')
            ->where('status', 'completed')
            ->whereBetween('processed_at', [$startDate, $endDate])
            ->whereIn('member_id', $members->pluck('id'))
            ->pluck('member_id')
            ->flip();

        $rows = $members->map(function ($member) use ($existingDeposits) {
            $account = $member->accounts->first();
            return [
                'member_id'                   => $member->id,
                'membership_id'               => $member->membership_id,
                'member_name'                 => $member->first_name . ' ' . $member->last_name,
                'account_id'                  => $account?->id,
                'account_number'              => $account?->account_number,
                'account_balance'             => (float) ($account?->balance ?? 0),
                'monthly_contribution_amount' => (float) ($member->monthly_contribution_amount ?? 0),
                'amount'                      => (float) ($member->monthly_contribution_amount ?? 0),
                'already_deposited_this_month'=> $existingDeposits->has($member->id),
            ];
        })
        // Only include members with a configured amount and an account
        ->filter(fn($r) => $r['account_id'] && $r['monthly_contribution_amount'] > 0)
        ->values();

        $summary = [
            'total_eligible'   => $rows->count(),
            'already_done'     => $rows->where('already_deposited_this_month', true)->count(),
            'pending'          => $rows->where('already_deposited_this_month', false)->count(),
            'total_amount'     => round($rows->where('already_deposited_this_month', false)->sum('amount'), 2),
        ];

        return Inertia::render('Admin/Schedule/MonthlyDeposit', [
            'rows'       => $rows,
            'summary'    => $summary,
            'month'      => $month,
            'alreadyRun' => $alreadyRun,
        ]);
    }

    /**
     * PREVIEW – returns the proposed transactions without posting.
     */
    public function previewMonthlyDeposits(Request $request)
    {
        $request->validate([
            'month' => 'required|string|date_format:Y-m',
        ]);

        [$year, $mon] = explode('-', $request->month);

        if (ScheduleExecutionLog::alreadyRun('monthly_deposits', (int)$year, (int)$mon)) {
            return response()->json(['error' => 'This schedule has already been run for the selected period.'], 422);
        }

        $members = Member::with([
            'accounts' => fn($q) => $q->where('account_type', 'share_deposits')->where('is_active', true),
        ])
            ->where('membership_status', 'active')
            ->whereNotNull('monthly_contribution_amount')
            ->where('monthly_contribution_amount', '>', 0)
            ->get();

        $preview = $members->map(function ($member) use ($request) {
            $account = $member->accounts->first();
            return [
                'member_id'    => $member->id,
                'member_name'  => $member->first_name . ' ' . $member->last_name,
                'membership_id'=> $member->membership_id,
                'account_id'   => $account?->id,
                'account_number'=> $account?->account_number,
                'amount'       => (float) $member->monthly_contribution_amount,
                'month'        => $request->month,
                'valid'        => $account !== null,
                'error'        => $account ? null : 'No active share_deposits account',
            ];
        })->values();

        return response()->json([
            'preview'      => $preview,
            'total_amount' => $preview->where('valid', true)->sum('amount'),
            'total_records'=> $preview->where('valid', true)->count(),
            'skipped'      => $preview->where('valid', false)->count(),
        ]);
    }

    /**
     * RUN – post the monthly deposits after user confirmation.
     */
    public function runMonthlyDeposits(Request $request)
    {
        $request->validate([
            'month'   => 'required|string|date_format:Y-m',
            'entries' => 'required|array|min:1',
            'entries.*.member_id'  => 'required|exists:members,id',
            'entries.*.account_id' => 'required|exists:accounts,id',
            'entries.*.amount'     => 'required|numeric|min:1',
        ]);

        [$year, $mon] = explode('-', $request->month);

        if (ScheduleExecutionLog::alreadyRun('monthly_deposits', (int)$year, (int)$mon)) {
            return back()->withErrors(['schedule' => 'This schedule has already been run for the selected period.']);
        }

        $processedBy  = Auth::id();
        $now          = now();
        $monthLabel   = Carbon::parse($request->month)->format('F Y');
        $processed    = 0;
        $failed       = 0;
        $totalAmount  = 0;
        $errors       = [];

        DB::beginTransaction();
        try {
            foreach ($request->entries as $entry) {
                try {
                    $account = Account::findOrFail($entry['account_id']);

                    Transaction::create([
                        'transaction_id'   => 'DEP-SCH-' . strtoupper(uniqid()),
                        'account_id'       => $account->id,
                        'member_id'        => $entry['member_id'],
                        'transaction_type' => 'deposit',
                        'amount'           => $entry['amount'],
                        'balance_before'   => $account->balance,
                        'balance_after'    => $account->balance + $entry['amount'],
                        'description'      => "Schedule: Monthly deposit – {$monthLabel}",
                        'payment_method'   => 'schedule',
                        'status'           => 'completed',
                        'processed_by'     => $processedBy,
                        'processed_at'     => $now,
                    ]);

                    $account->increment('balance', $entry['amount']);
                    $account->increment('available_balance', $entry['amount']);
                    $account->update(['last_transaction_at' => $now]);

                    $processed++;
                    $totalAmount += $entry['amount'];

                } catch (\Throwable $e) {
                    $failed++;
                    $errors[] = [
                        'member_id' => $entry['member_id'],
                        'error'     => $e->getMessage(),
                    ];
                }
            }

            // Record execution log
            ScheduleExecutionLog::create([
                'schedule_type'           => 'monthly_deposits',
                'processing_month'        => (int) $mon,
                'processing_year'         => (int) $year,
                'executed_by'             => $processedBy,
                'execution_date'          => $now,
                'total_records_processed' => $processed,
                'total_records_failed'    => $failed,
                'total_amount_posted'     => $totalAmount,
                'status'                  => $failed > 0 ? ($processed > 0 ? 'partial' : 'failed') : 'completed',
                'error_log'               => $errors ?: null,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['schedule' => 'Failed to run schedule: ' . $e->getMessage()]);
        }

        return redirect()->route('schedule.monthly-deposit')
            ->with('success', "Monthly deposits posted: {$processed} transactions, KES " . number_format($totalAmount, 2) . "." . ($failed ? " {$failed} failed." : ''));
    }

    // =========================================================================
    //  2. LOAN REPAYMENTS
    // =========================================================================

    public function loanRepayment(Request $request)
    {
        $month = $request->month ?? Carbon::now()->format('Y-m');
        [$year, $mon] = explode('-', $month);

        $alreadyRun = ScheduleExecutionLog::alreadyRun('loan_repayments', (int)$year, (int)$mon);

        $startDate = Carbon::parse($month)->startOfMonth()->toDateString();
        $endDate   = Carbon::parse($month)->endOfMonth()->toDateString();

        $repayments = LoanRepayment::with([
            'loan.member.user',
            'loan.loanProduct',
        ])
            ->whereBetween('due_date', [$startDate, $endDate])
            ->whereIn('status', ['pending', 'overdue', 'partial'])
            ->whereHas('loan', fn($q) => $q->where('status', 'active'))
            ->orderBy('due_date')
            ->get();

        $rows = $repayments->map(fn($r) => [
            'repayment_id'       => $r->id,
            'due_date'           => $r->due_date->format('Y-m-d'),
            'loan_id'            => $r->loan_id,
            'loan_number'        => $r->loan->loan_number,
            'member_id'          => $r->loan->member_id,
            'membership_id'      => $r->loan->member->membership_id,
            'member_name'        => $r->loan->member->first_name . ' ' . $r->loan->member->last_name,
            'loan_product'       => $r->loan->loanProduct->name ?? 'N/A',
            'principal_amount'   => (float) $r->principal_amount,
            'interest_amount'    => (float) $r->interest_amount,
            'penalty_amount'     => (float) $r->penalty_amount,
            'expected_amount'    => (float) $r->expected_amount,
            'paid_amount'        => (float) $r->paid_amount,
            'outstanding_amount' => (float) $r->outstanding_amount,
            'outstanding_balance'=> (float) $r->loan->outstanding_balance,
            'status'             => $r->status,
            'days_late'          => $r->days_late,
        ])->values();

        $summary = [
            'total_repayments'    => $rows->count(),
            'total_expected'      => round($rows->sum('expected_amount'), 2),
            'total_outstanding'   => round($rows->sum('outstanding_amount'), 2),
            'total_principal'     => round($rows->sum('principal_amount'), 2),
            'total_interest'      => round($rows->sum('interest_amount'), 2),
            'overdue_count'       => $rows->where('status', 'overdue')->count(),
        ];

        return Inertia::render('Admin/Schedule/LoanRepayment', [
            'rows'       => $rows,
            'summary'    => $summary,
            'month'      => $month,
            'alreadyRun' => $alreadyRun,
        ]);
    }

    /**
     * RUN – post loan repayments after confirmation.
     */
    public function runLoanRepayments(Request $request)
    {
        $request->validate([
            'month'                  => 'required|string|date_format:Y-m',
            'entries'                => 'required|array|min:1',
            'entries.*.repayment_id' => 'required|exists:loan_repayments,id',
            'entries.*.loan_id'      => 'required|exists:loans,id',
            'entries.*.member_id'    => 'required|exists:members,id',
        ]);

        [$year, $mon] = explode('-', $request->month);

        if (ScheduleExecutionLog::alreadyRun('loan_repayments', (int)$year, (int)$mon)) {
            return back()->withErrors(['schedule' => 'This schedule has already been run for the selected period.']);
        }

        $processedBy = Auth::id();
        $now         = now();
        $processed   = 0;
        $failed      = 0;
        $totalAmount = 0;
        $errors      = [];

        DB::beginTransaction();
        try {
            foreach ($request->entries as $entry) {
                try {
                    $repayment = LoanRepayment::with('loan')->findOrFail($entry['repayment_id']);
                    $loan      = $repayment->loan;
                    $amount    = (float) $repayment->outstanding_amount;

                    if ($amount <= 0) continue;

                    // Allocation: penalty first, then interest, then principal
                    $penaltyPaid   = min($amount, (float) $repayment->penalty_amount);
                    $remaining     = $amount - $penaltyPaid;
                    $interestPaid  = min($remaining, (float) $repayment->interest_amount);
                    $remaining    -= $interestPaid;
                    $principalPaid = min($remaining, (float) $repayment->principal_amount);

                    $newPaid        = (float) $repayment->paid_amount + $amount;
                    $newOutstanding = max(0, (float) $repayment->outstanding_amount - $amount);
                    $newStatus      = $newOutstanding > 0 ? 'partial' : 'paid';

                    $tx = Transaction::create([
                        'transaction_id'   => 'RPY-SCH-' . strtoupper(uniqid()),
                        'account_id'       => $loan->member->accounts()
                                                ->where('account_type', 'share_deposits')
                                                ->value('id') ?? $loan->id,
                        'member_id'        => $loan->member_id,
                        'transaction_type' => 'loan_repayment',
                        'amount'           => $amount,
                        'balance_before'   => 0,
                        'balance_after'    => 0,
                        'description'      => "Schedule: Loan repayment – {$loan->loan_number}",
                        'reference_number' => $loan->loan_number,
                        'payment_method'   => 'schedule',
                        'status'           => 'completed',
                        'processed_by'     => $processedBy,
                        'processed_at'     => $now,
                    ]);

                    $repayment->update([
                        'paid_amount'        => $newPaid,
                        'outstanding_amount' => $newOutstanding,
                        'status'             => $newStatus,
                        'payment_date'       => $now->toDateString(),
                        'transaction_id'     => $tx->id,
                    ]);

                    // Update loan balances
                    $loan->decrement('outstanding_balance', $amount);
                    $loan->decrement('principal_balance',   $principalPaid);
                    $loan->decrement('interest_balance',    $interestPaid);
                    if ($penaltyPaid > 0) {
                        $loan->decrement('penalty_balance', $penaltyPaid);
                    }

                    // FRD 5.6 – Mark loan completed when fully paid
                    $loan->refresh();
                    if ($loan->outstanding_balance <= 0) {
                        $loan->update([
                            'status'              => 'completed',
                            'outstanding_balance' => 0,
                        ]);
                    }

                    $processed++;
                    $totalAmount += $amount;

                } catch (\Throwable $e) {
                    $failed++;
                    $errors[] = ['repayment_id' => $entry['repayment_id'], 'error' => $e->getMessage()];
                }
            }

            ScheduleExecutionLog::create([
                'schedule_type'           => 'loan_repayments',
                'processing_month'        => (int) $mon,
                'processing_year'         => (int) $year,
                'executed_by'             => $processedBy,
                'execution_date'          => $now,
                'total_records_processed' => $processed,
                'total_records_failed'    => $failed,
                'total_amount_posted'     => $totalAmount,
                'status'                  => $failed > 0 ? ($processed > 0 ? 'partial' : 'failed') : 'completed',
                'error_log'               => $errors ?: null,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['schedule' => 'Failed to run schedule: ' . $e->getMessage()]);
        }

        return redirect()->route('schedule.loan-repayment')
            ->with('success', "Loan repayments posted: {$processed} transactions, KES " . number_format($totalAmount, 2) . "." . ($failed ? " {$failed} failed." : ''));
    }

    // =========================================================================
    //  3. LOAN DISBURSEMENTS
    // =========================================================================

    public function loanDisbursement(Request $request)
    {
        // FRD 6.2 – Approved loans with pending disbursement
        $query = Loan::with(['member.user', 'loanProduct', 'approvedBy'])
            ->where('status', 'approved')
            ->orderBy('approval_date');

        if ($request->filled('date_from')) {
            $query->whereDate('approval_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('approval_date', '<=', $request->date_to);
        }
        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        $loans = $query->get()->map(fn($loan) => [
            'id'              => $loan->id,
            'loan_number'     => $loan->loan_number,
            'member_id'       => $loan->member_id,
            'membership_id'   => $loan->member->membership_id,
            'member_name'     => $loan->member->first_name . ' ' . $loan->member->last_name,
            'loan_product'    => $loan->loanProduct->name ?? 'N/A',
            'approved_amount' => (float) $loan->approved_amount,
            'disbursed_amount'=> (float) ($loan->disbursed_amount ?? 0),
            'net_disbursement'=> (float) ($loan->approved_amount - $loan->processing_fee - $loan->insurance_fee),
            'processing_fee'  => (float) $loan->processing_fee,
            'insurance_fee'   => (float) $loan->insurance_fee,
            'approval_date'   => $loan->approval_date?->format('Y-m-d'),
            'approved_by'     => $loan->approvedBy?->name,
            'term_months'     => $loan->term_months,
        ]);

        $summary = [
            'total_loans'        => $loans->count(),
            'total_approved'     => round($loans->sum('approved_amount'), 2),
            'total_net'          => round($loans->sum('net_disbursement'), 2),
            'total_fees'         => round($loans->sum('processing_fee') + $loans->sum('insurance_fee'), 2),
        ];

        return Inertia::render('Admin/Schedule/LoanDisbursement', [
            'loans'   => $loans,
            'summary' => $summary,
            'filters' => $request->only(['date_from', 'date_to', 'member_id']),
        ]);
    }

    /**
     * RUN – disburse approved loans after confirmation.
     * FRD 6.4 – Credit member account, update status, enable for repayment schedule.
     */
    public function runLoanDisbursements(Request $request)
    {
        $request->validate([
            'loan_ids'   => 'required|array|min:1',
            'loan_ids.*' => 'required|exists:loans,id',
            'year'       => 'required|integer',
        ]);

        $processedBy = Auth::id();
        $now         = now();
        $processed   = 0;
        $failed      = 0;
        $totalAmount = 0;
        $errors      = [];

        DB::beginTransaction();
        try {
            foreach ($request->loan_ids as $loanId) {
                try {
                    $loan = Loan::with('member.accounts')->findOrFail($loanId);

                    if ($loan->status !== 'approved') {
                        throw new \Exception("Loan {$loan->loan_number} is not in approved status.");
                    }

                    $netAmount = $loan->approved_amount - $loan->processing_fee - $loan->insurance_fee;

                    // Credit member's share_deposits account
                    $account = $loan->member->accounts()
                        ->where('account_type', 'share_deposits')
                        ->where('is_active', true)
                        ->first();

                    if (!$account) {
                        throw new \Exception("No active share_deposits account for member {$loan->member->membership_id}.");
                    }

                    Transaction::create([
                        'transaction_id'   => 'DIS-SCH-' . strtoupper(uniqid()),
                        'account_id'       => $account->id,
                        'member_id'        => $loan->member_id,
                        'transaction_type' => 'loan_disbursement',
                        'amount'           => $netAmount,
                        'balance_before'   => $account->balance,
                        'balance_after'    => $account->balance + $netAmount,
                        'description'      => "Schedule: Loan disbursement – {$loan->loan_number}",
                        'reference_number' => $loan->loan_number,
                        'payment_method'   => 'schedule',
                        'status'           => 'completed',
                        'processed_by'     => $processedBy,
                        'processed_at'     => $now,
                    ]);

                    $account->increment('balance', $netAmount);
                    $account->increment('available_balance', $netAmount);
                    $account->update(['last_transaction_at' => $now]);

                    // FRD 6.4 – Update loan to active
                    $loan->update([
                        'status'            => 'active',
                        'disbursed_amount'  => $loan->approved_amount,
                        'disbursement_date' => $now->toDateString(),
                        'disbursed_by'      => $processedBy,
                        'outstanding_balance' => $loan->total_repayable ?? $loan->approved_amount,
                        'principal_balance'   => $loan->approved_amount,
                    ]);

                    $processed++;
                    $totalAmount += $netAmount;

                } catch (\Throwable $e) {
                    $failed++;
                    $errors[] = ['loan_id' => $loanId, 'error' => $e->getMessage()];
                }
            }

            ScheduleExecutionLog::create([
                'schedule_type'           => 'loan_disbursements',
                'processing_month'        => null, // not month-restricted
                'processing_year'         => (int) $request->year,
                'executed_by'             => $processedBy,
                'execution_date'          => $now,
                'total_records_processed' => $processed,
                'total_records_failed'    => $failed,
                'total_amount_posted'     => $totalAmount,
                'status'                  => $failed > 0 ? ($processed > 0 ? 'partial' : 'failed') : 'completed',
                'error_log'               => $errors ?: null,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['schedule' => 'Failed to run disbursements: ' . $e->getMessage()]);
        }

        return redirect()->route('schedule.loan-disbursement')
            ->with('success', "Disbursements posted: {$processed} loans, KES " . number_format($totalAmount, 2) . "." . ($failed ? " {$failed} failed." : ''));
    }

    // =========================================================================
    //  4. DIVIDEND PAYMENTS
    // =========================================================================

    public function dividendPayment(Request $request)
    {
        $year = (int) ($request->year ?? Carbon::now()->year);

        $alreadyRun = ScheduleExecutionLog::alreadyRun('dividend_payments', $year);

        $dividend = Dividend::with(['calculatedBy', 'approvedBy'])
            ->where('dividend_year', $year)
            ->whereIn('status', ['approved', 'distributed'])
            ->first();

        if (!$dividend) {
            return Inertia::render('Admin/Schedule/DividendPayment', [
                'dividend'        => null,
                'memberDividends' => [],
                'summary'         => null,
                'year'            => $year,
                'alreadyRun'      => $alreadyRun,
                'message'         => "No approved dividend found for {$year}. Please calculate and approve a dividend first.",
            ]);
        }

        $memberDividends = MemberDividend::with(['member.user', 'member.accounts'])
            ->where('dividend_id', $dividend->id)
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->orderByDesc('dividend_amount')
            ->get()
            ->map(fn($md) => [
                'id'              => $md->id,
                'member_id'       => $md->member_id,
                'membership_id'   => $md->member->membership_id,
                'member_name'     => $md->member->first_name . ' ' . $md->member->last_name,
                'shares_balance'  => (float) $md->shares_balance,
                'dividend_amount' => (float) $md->dividend_amount,
                'status'          => $md->status,
                'payment_date'    => $md->payment_date,
                'eligible'        => $md->member->dividend_eligibility ?? true,
                'dividend_account_id' => $md->member->dividend_account_id
                    ?? $md->member->accounts->where('account_type', 'share_deposits')->first()?->id,
            ]);

        $pending = $memberDividends->where('status', 'pending')
                                   ->where('eligible', true);

        $summary = [
            'total_members'   => $memberDividends->count(),
            'pending_count'   => $pending->count(),
            'pending_amount'  => round($pending->sum('dividend_amount'), 2),
            'paid_count'      => $memberDividends->where('status', 'paid')->count(),
            'paid_amount'     => round($memberDividends->where('status', 'paid')->sum('dividend_amount'), 2),
            'ineligible'      => $memberDividends->where('eligible', false)->count(),
            'dividend_rate'   => (float) $dividend->dividend_rate,
        ];

        return Inertia::render('Admin/Schedule/DividendPayment', [
            'dividend'        => [
                'id'            => $dividend->id,
                'dividend_year' => $dividend->dividend_year,
                'dividend_rate' => (float) $dividend->dividend_rate,
                'total_dividends'=> (float) $dividend->total_dividends,
                'status'        => $dividend->status,
                'approval_date' => $dividend->approval_date?->format('Y-m-d'),
            ],
            'memberDividends' => $memberDividends->values(),
            'summary'         => $summary,
            'year'            => $year,
            'alreadyRun'      => $alreadyRun,
            'filters'         => $request->only(['status']),
        ]);
    }

    /**
     * RUN – pay dividends to eligible members after confirmation.
     * FRD 7.5 – Create dividend transaction, credit member account, record in dividend ledger.
     */
    public function runDividendPayments(Request $request)
    {
        $request->validate([
            'dividend_id'              => 'required|exists:dividends,id',
            'year'                     => 'required|integer',
            'entries'                  => 'required|array|min:1',
            'entries.*.member_dividend_id' => 'required|exists:member_dividends,id',
            'entries.*.member_id'      => 'required|exists:members,id',
            'entries.*.account_id'     => 'required|exists:accounts,id',
            'entries.*.dividend_amount'=> 'required|numeric|min:0.01',
        ]);

        if (ScheduleExecutionLog::alreadyRun('dividend_payments', (int) $request->year)) {
            return back()->withErrors(['schedule' => 'Dividends for this year have already been paid.']);
        }

        $processedBy = Auth::id();
        $now         = now();
        $processed   = 0;
        $failed      = 0;
        $totalAmount = 0;
        $errors      = [];

        DB::beginTransaction();
        try {
            foreach ($request->entries as $entry) {
                try {
                    $memberDividend = MemberDividend::findOrFail($entry['member_dividend_id']);

                    if ($memberDividend->status === 'paid') continue;

                    $account = Account::findOrFail($entry['account_id']);
                    $amount  = (float) $entry['dividend_amount'];

                    $tx = Transaction::create([
                        'transaction_id'   => 'DIV-SCH-' . strtoupper(uniqid()),
                        'account_id'       => $account->id,
                        'member_id'        => $entry['member_id'],
                        'transaction_type' => 'dividend',
                        'amount'           => $amount,
                        'balance_before'   => $account->balance,
                        'balance_after'    => $account->balance + $amount,
                        'description'      => "Schedule: Dividend payment – {$request->year}",
                        'payment_method'   => 'schedule',
                        'status'           => 'completed',
                        'processed_by'     => $processedBy,
                        'processed_at'     => $now,
                    ]);

                    $account->increment('balance', $amount);
                    $account->increment('available_balance', $amount);
                    $account->update(['last_transaction_at' => $now]);

                    $memberDividend->update([
                        'status'         => 'paid',
                        'payment_date'   => $now->toDateString(),
                        'transaction_id' => $tx->id,
                    ]);

                    $processed++;
                    $totalAmount += $amount;

                } catch (\Throwable $e) {
                    $failed++;
                    $errors[] = ['member_dividend_id' => $entry['member_dividend_id'], 'error' => $e->getMessage()];
                }
            }

            // Mark dividend as fully distributed if all members paid
            $dividend      = Dividend::find($request->dividend_id);
            $allPaid       = MemberDividend::where('dividend_id', $dividend->id)
                                           ->where('status', '!=', 'paid')
                                           ->doesntExist();
            if ($allPaid) {
                $dividend->update(['status' => 'distributed', 'distribution_date' => $now->toDateString()]);
            }

            ScheduleExecutionLog::create([
                'schedule_type'           => 'dividend_payments',
                'processing_month'        => null,
                'processing_year'         => (int) $request->year,
                'executed_by'             => $processedBy,
                'execution_date'          => $now,
                'total_records_processed' => $processed,
                'total_records_failed'    => $failed,
                'total_amount_posted'     => $totalAmount,
                'status'                  => $failed > 0 ? ($processed > 0 ? 'partial' : 'failed') : 'completed',
                'error_log'               => $errors ?: null,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['schedule' => 'Failed to run dividend payments: ' . $e->getMessage()]);
        }

        return redirect()->route('schedule.dividend-payment')
            ->with('success', "Dividend payments posted: {$processed} members, KES " . number_format($totalAmount, 2) . "." . ($failed ? " {$failed} failed." : ''));
    }

    // =========================================================================
    //  EXPORTS
    // =========================================================================

    public function exportLoanDisbursement(Request $request)
    {
        $loans    = Loan::with(['member.user', 'loanProduct'])
                        ->where('status', 'approved')
                        ->orderBy('approval_date')
                        ->get();
        $filename = 'loan_disbursement_schedule_' . now()->format('Y_m_d') . '.csv';

        return response()->stream(function () use ($loans) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Loan Number','Member Name','Membership ID','Loan Product',
                'Approved Amount','Processing Fee','Insurance Fee','Net Disbursement',
                'Approval Date','Term (Months)']);
            foreach ($loans as $loan) {
                fputcsv($file, [
                    $loan->loan_number,
                    $loan->member->first_name . ' ' . $loan->member->last_name,
                    $loan->member->membership_id,
                    $loan->loanProduct->name,
                    $loan->approved_amount,
                    $loan->processing_fee,
                    $loan->insurance_fee,
                    $loan->approved_amount - $loan->processing_fee - $loan->insurance_fee,
                    $loan->approval_date?->format('Y-m-d'),
                    $loan->term_months,
                ]);
            }
            fclose($file);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}