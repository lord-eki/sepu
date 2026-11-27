<?php

namespace App\Http\Controllers;

use App\Http\Requests\RunLoanRepaymentsRequest;
use App\Http\Requests\RunMonthlyDepositsRequest;
use App\Models\Account;
use App\Models\Dividend;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\MemberDividend;
use App\Models\MemberDepositCommitment;
use App\Models\MemberFinanceConfig;
use App\Models\ScheduleExecutionLog;
use App\Models\Transaction;
use App\Services\Loans\LoanService;
use App\Services\Schedule\ScheduleRunService;
use App\Support\ErrorMessage;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class ScheduleController extends Controller
{


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
            'recentLogs'   => $recentLogs,
            'currentMonth' => Carbon::now()->format('Y-m'),
            'currentYear'  => Carbon::now()->year,
        ]);
    }


    // Shows the monthly deposit run with the members still pending for the chosen month.
    public function monthlyDeposit(Request $request, ScheduleRunService $runner)
    {
        $month = $request->month ?? Carbon::now()->format('Y-m');
        $doneMemberIds = $runner->depositMemberIdsDone($month);

        $rows = MemberFinanceConfig::activeContributions()
            ->with(['member', 'contributionAccount'])
            ->whereHas('member', fn ($query) => $query->where('membership_status', 'active'))
            ->orderBy('member_id')
            ->get()
            ->filter(fn ($config) => $config->member && $config->contributionAccount && $config->monthly_contribution > 0)
            ->map(fn ($config) => [
                'config_id' => $config->id,
                'member_id' => $config->member_id,
                'account_id' => $config->contribution_account_id,
                'membership_id' => $config->member->membership_id,
                'member_name' => $config->member->first_name . ' ' . $config->member->last_name,
                'account_number' => $config->contributionAccount->account_number,
                'account_type' => $config->contributionAccount->account_type,
                'account_balance' => (float) $config->contributionAccount->balance,
                'amount' => (float) $config->monthly_contribution,
                'already_deposited_this_month' => $doneMemberIds->has($config->member_id),
            ])
            ->values();

        $pending = $rows->where('already_deposited_this_month', false);

        $summary = [
            'total_eligible' => $rows->count(),
            'already_done' => $rows->count() - $pending->count(),
            'pending' => $pending->count(),
            'total_amount' => round($pending->sum('amount'), 2),
        ];

        return Inertia::render('Admin/Schedule/MonthlyDeposit', [
            'rows' => $rows,
            'summary' => $summary,
            'month' => $month,
            'alreadyRun' => $summary['total_eligible'] > 0 && $summary['pending'] === 0,
        ]);
    }

    // Posts the monthly deposit for each selected member who has not been run yet this month.
    public function runMonthlyDeposits(RunMonthlyDepositsRequest $request, ScheduleRunService $runner)
    {
        $month = $request->validated('month');
        $user = Auth::user();

        $memberIds = collect($request->validated('entries'))
            ->pluck('member_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->mapWithKeys(fn ($id) => [$id => $id]);

        $outcome = $this->runBatch($memberIds, fn ($memberId) => $runner->postMonthlyDeposit($memberId, $month, $user));

        $this->recordRun('monthly_deposits', $month, $outcome);

        return $this->runResponse('schedule.monthly-deposit', $month, 'Monthly deposits posted', 'transactions', $outcome);
    }

  

    // Shows unpaid instalments due by month end that have not been deducted yet this month.
    public function loanRepayment(Request $request, ScheduleRunService $runner)
    {
        $month = $request->month ?? Carbon::now()->format('Y-m');
        $deductions = MemberFinanceConfig::autoLoanDeduct()->pluck('loan_deduction_amount', 'member_id');

        $due = LoanRepayment::with(['loan.member.user', 'loan.loanProduct'])
            ->where('due_date', '<=', Carbon::parse($month)->endOfMonth()->toDateString())
            ->whereIn('status', ['pending', 'overdue', 'partial'])
            ->whereHas('loan', fn ($query) => $query
                ->whereIn('status', Loan::RUNNING_STATUSES)
                ->whereIn('member_id', $deductions->keys()))
            ->orderBy('due_date')
            ->get();

        $doneIds = $runner->repaymentIdsRunForMonth($due->pluck('id'), $month);

        $runCount = $runner->repaymentRunCount($month);

        $rows = $due
            ->reject(fn ($repayment) => in_array($repayment->id, $doneIds, true))
            ->map(fn ($repayment) => $this->repaymentRow($repayment, $deductions->get($repayment->loan->member_id)))
            ->values();

        $summary = [
            'total_repayments' => $rows->count(),
            'already_done' => $runCount,
            'total_expected' => round($rows->sum('expected_amount'), 2),
            'total_deductable' => round($rows->sum('deduct_amount'), 2),
            'total_outstanding' => round($rows->sum('outstanding_amount'), 2),
            'total_principal' => round($rows->sum('principal_amount'), 2),
            'total_interest' => round($rows->sum('interest_amount'), 2),
            'overdue_count' => $rows->where('status', 'overdue')->count(),
        ];

        return Inertia::render('Admin/Schedule/LoanRepayment', [
            'rows' => $rows,
            'summary' => $summary,
            'month' => $month,
            'alreadyRun' => $rows->isEmpty() && $runCount > 0,
        ]);
    }

    // Deducts each selected instalment from member deposits and posts it to the ledger.
    public function runLoanRepayments(RunLoanRepaymentsRequest $request, ScheduleRunService $runner)
    {
        $month = $request->validated('month');
        $user = Auth::user();

        $amounts = collect($request->validated('entries'))
            ->mapWithKeys(fn ($entry) => [(int) $entry['repayment_id'] => (float) $entry['deduct_amount']]);

        $outcome = $this->runBatch($amounts, fn ($amount, $repaymentId) => $runner->postLoanRepayment($repaymentId, $amount, $month, $user));

        $this->recordRun('loan_repayments', $month, $outcome);

        return $this->runResponse('schedule.loan-repayment', $month, 'Loan repayments posted', 'deductions', $outcome);
    }

    // Builds one row of the loan repayment page, capping the suggested deduction at the member's override.
    private function repaymentRow(LoanRepayment $repayment, ?float $override): array
    {
        $outstanding = (float) $repayment->outstanding_amount;

        return [
            'repayment_id' => $repayment->id,
            'due_date' => $repayment->due_date->format('Y-m-d'),
            'loan_id' => $repayment->loan_id,
            'loan_number' => $repayment->loan->loan_number,
            'member_id' => $repayment->loan->member_id,
            'membership_id' => $repayment->loan->member->membership_id,
            'member_name' => $repayment->loan->member->first_name . ' ' . $repayment->loan->member->last_name,
            'loan_product' => $repayment->loan->loanProduct->name ?? 'N/A',
            'principal_amount' => (float) $repayment->principal_amount,
            'interest_amount' => (float) $repayment->interest_amount,
            'penalty_amount' => (float) $repayment->penalty_amount,
            'expected_amount' => (float) $repayment->expected_amount,
            'paid_amount' => (float) $repayment->paid_amount,
            'outstanding_amount' => $outstanding,
            'deduct_amount' => $override ? min($override, $outstanding) : $outstanding,
            'override_set' => $override !== null,
            'outstanding_balance' => (float) $repayment->loan->outstanding_balance,
            'status' => $repayment->status,
            'days_late' => $repayment->days_late ?? 0,
        ];
    }

    // Runs the action on every item and counts what was posted, skipped and failed.
    private function runBatch(Collection $items, Closure $action): array
    {
        $outcome = ['processed' => 0, 'skipped' => 0, 'failed' => 0, 'completed' => 0, 'amount' => 0.0, 'errors' => []];

        foreach ($items as $key => $item) {
            try {
                $result = $action($item, $key);
            } catch (Throwable $e) {
                $outcome['failed']++;
                $outcome['errors'][] = "#{$key}: " . ErrorMessage::from($e);

                continue;
            }

            if ($result['status'] !== 'posted') {
                $outcome['skipped']++;

                continue;
            }

            $outcome['processed']++;
            $outcome['amount'] += $result['amount'];
            $outcome['completed'] += ($result['completed'] ?? false) ? 1 : 0;
        }

        return $outcome;
    }

    // Writes one execution log row for a batch that posted or failed something.
    private function recordRun(string $type, string $month, array $outcome): void
    {
        if ($outcome['processed'] === 0 && $outcome['failed'] === 0) {
            return;
        }

        [$year, $monthNumber] = explode('-', $month);

        ScheduleExecutionLog::create([
            'schedule_type' => $type,
            'processing_month' => (int) $monthNumber,
            'processing_year' => (int) $year,
            'executed_by' => Auth::id(),
            'execution_date' => now(),
            'total_records_processed' => $outcome['processed'],
            'total_records_failed' => $outcome['failed'],
            'total_amount_posted' => $outcome['amount'],
            'status' => $outcome['failed'] === 0 ? 'completed' : ($outcome['processed'] > 0 ? 'partial' : 'failed'),
            'error_log' => $outcome['errors'] ?: null,
        ]);
    }

    // Redirects back to the run page with a summary and any per-item errors.
    private function runResponse(string $route, string $month, string $title, string $noun, array $outcome)
    {
        $message = "{$title}: {$outcome['processed']} {$noun}, KES " . number_format($outcome['amount'], 2) . '.'
            . ($outcome['completed'] ? " {$outcome['completed']} loan(s) fully repaid." : '')
            . ($outcome['skipped'] ? " {$outcome['skipped']} already done for this month." : '')
            . ($outcome['failed'] ? " {$outcome['failed']} failed." : '');

        $redirect = redirect()->route($route, ['month' => $month]);

        if ($outcome['failed'] === 0) {
            return $redirect->with('success', $message);
        }

        return $redirect
            ->with($outcome['processed'] === 0 ? 'error' : 'success', $message)
            ->withErrors(['schedule' => implode(' | ', array_slice($outcome['errors'], 0, 5))]);
    }

    //  Loan disbursements  

    public function loanDisbursement(Request $request)
    {
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
            'id'               => $loan->id,
            'loan_number'      => $loan->loan_number,
            'member_id'        => $loan->member_id,
            'membership_id'    => $loan->member->membership_id,
            'member_name'      => $loan->member->first_name . ' ' . $loan->member->last_name,
            'loan_product'     => $loan->loanProduct->name ?? 'N/A',
            'approved_amount'  => (float) $loan->approved_amount,
            'disbursed_amount' => (float) ($loan->disbursed_amount ?? 0),
            'net_disbursement' => (float) ($loan->approved_amount - $loan->processing_fee - $loan->insurance_fee),
            'processing_fee'   => (float) $loan->processing_fee,
            'insurance_fee'    => (float) $loan->insurance_fee,
            'approval_date'    => $loan->approval_date?->format('Y-m-d'),
            'approved_by'      => $loan->approvedBy?->name,
            'term_months'      => $loan->term_months,
        ]);

        $summary = [
            'total_loans'    => $loans->count(),
            'total_approved' => round($loans->sum('approved_amount'), 2),
            'total_net'      => round($loans->sum('net_disbursement'), 2),
            'total_fees'     => round($loans->sum('processing_fee') + $loans->sum('insurance_fee'), 2),
        ];

        return Inertia::render('Admin/Schedule/LoanDisbursement', [
            'loans'   => $loans,
            'summary' => $summary,
            'filters' => $request->only(['date_from', 'date_to', 'member_id']),
        ]);
    }

    public function runLoanDisbursements(Request $request, LoanService $loans)
    {
        $request->validate([
            'loan_ids'   => 'required|array|min:1',
            'loan_ids.*' => 'required|exists:loans,id',
            'year'       => 'required|integer',
        ]);

        $loanIds     = array_values(array_unique($request->input('loan_ids')));
        $by          = Auth::user();
        $now         = now();
        $processed   = 0;
        $failed      = 0;
        $totalAmount = 0;
        $errors      = [];

        foreach ($loanIds as $loanId) {
            try {
                $result = $loans->disburse((int) $loanId, 'account', null, $by);

                $processed++;
                $totalAmount += $result['net_disbursement'];
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = ['loan_id' => $loanId, 'error' => ErrorMessage::from($e)];
            }
        }

        ScheduleExecutionLog::create([
            'schedule_type'           => 'loan_disbursements',
            'processing_month'        => null,
            'processing_year'         => (int) $request->year,
            'executed_by'             => $by->id,
            'execution_date'          => $now,
            'total_records_processed' => $processed,
            'total_records_failed'    => $failed,
            'total_amount_posted'     => $totalAmount,
            'status'                  => $failed > 0 ? ($processed > 0 ? 'partial' : 'failed') : 'completed',
            'error_log'               => $errors ?: null,
        ]);

        return redirect()->route('schedule.loan-disbursement')
            ->with('success', "Disbursements posted: {$processed} loans, KES " .
                number_format($totalAmount, 2) . '.' . ($failed ? " {$failed} failed." : ''));
    }

    //  DIVIDEND PAYMENTS

    public function dividendPayment(Request $request)
    {
        $year = (int) ($request->year ?? Carbon::now()->year);

        $alreadyRun = ScheduleExecutionLog::alreadyRun('dividend_payments', $year);

        $dividend = Dividend::with(['calculatedBy', 'approvedBy'])
            ->where('dividend_year', $year)
            ->whereIn('status', ['approved', 'distributed'])
            ->first();

        if (! $dividend) {
            return Inertia::render('Admin/Schedule/DividendPayment', [
                'dividend'        => null,
                'memberDividends' => [],
                'summary'         => null,
                'year'            => $year,
                'alreadyRun'      => $alreadyRun,
                'message'         => "No approved dividend found for {$year}. Please calculate and approve a dividend first.",
            ]);
        }

        $eligibleMemberIds = MemberFinanceConfig::dividendEligible()->pluck('member_id');

        $memberDividends = MemberDividend::with(['member.user', 'member.accounts'])
            ->where('dividend_id', $dividend->id)
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->get()
            ->map(function ($md) use ($eligibleMemberIds) {
                $config = $md->member->financeConfig;

                $dividendAccountId = $config?->dividend_account_id
                    ?? null;

                return [
                    'id'                  => $md->id,
                    'member_id'           => $md->member_id,
                    'membership_id'       => $md->member->membership_id,
                    'member_name'         => $md->member->first_name . ' ' . $md->member->last_name,
                    'shares_balance'      => (float) $md->shares_balance,
                    'dividend_amount'     => (float) $md->dividend_amount,
                    'status'              => $md->status,
                    'payment_date'        => $md->payment_date,
                    'eligible'            => $eligibleMemberIds->contains($md->member_id),
                    'dividend_account_id' => $dividendAccountId,
                ];
            })
            ->sortByDesc('dividend_amount')
            ->values();

        $pending = $memberDividends->where('status', 'pending')->where('eligible', true);

        $summary = [
            'total_members'  => $memberDividends->count(),
            'pending_count'  => $pending->count(),
            'pending_amount' => round($pending->sum('dividend_amount'), 2),
            'paid_count'     => $memberDividends->where('status', 'paid')->count(),
            'paid_amount'    => round($memberDividends->where('status', 'paid')->sum('dividend_amount'), 2),
            'ineligible'     => $memberDividends->where('eligible', false)->count(),
            'dividend_rate'  => (float) $dividend->dividend_rate,
        ];

        return Inertia::render('Admin/Schedule/DividendPayment', [
            'dividend' => [
                'id'              => $dividend->id,
                'dividend_year'   => $dividend->dividend_year,
                'dividend_rate'   => (float) $dividend->dividend_rate,
                'total_dividends' => (float) $dividend->total_dividends,
                'status'          => $dividend->status,
                'approval_date'   => $dividend->approval_date?->format('Y-m-d'),
            ],
            'memberDividends' => $memberDividends,
            'summary'         => $summary,
            'year'            => $year,
            'alreadyRun'      => $alreadyRun,
            'filters'         => $request->only(['status']),
        ]);
    }

    public function runDividendPayments(Request $request)
    {
        $request->validate([
            'dividend_id'                      => 'required|exists:dividends,id',
            'year'                             => 'required|integer',
            'distribution_mode'               => 'required|in:payout,reinvest',
            'entries'                          => 'required|array|min:1',
            'entries.*.member_dividend_id'     => 'required|exists:member_dividends,id',
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

                    $member = Member::with(['financeConfig', 'accounts'])
                        ->findOrFail($memberDividend->member_id);

                    $commitment = MemberDepositCommitment::query()
                        ->where('member_id', $member->id)
                        ->where('type', 'dividend')
                        ->where('dividend_mode', 'reinvest')
                        ->where('is_active', true)
                        ->where('effective_from', '<=', now()->toDateString())
                        ->where(function ($query) {
                            $query->whereNull('effective_to')
                                ->orWhere('effective_to', '>=', now()->toDateString());
                        })
                        ->latest('id')
                        ->first();

                    $accountId = $request->distribution_mode === 'reinvest'
                        ? $commitment?->account_id
                        : $member->financeConfig?->dividend_account_id;

                    $account = $member->accounts
                        ->where('id', $accountId)
                        ->where('is_active', true)
                        ->first();

                    if (! $account) {
                        throw new \RuntimeException(
                            $request->distribution_mode === 'reinvest'
                                ? "Member {$member->membership_id} has no active reinvestment deposit account or agreement."
                                : "Member {$member->membership_id} has no configured dividend payout account."
                        );
                    }

                    $requiredAccountType = $request->distribution_mode === 'reinvest'
                        ? 'share_deposits'
                        : 'dividend';

                    if ($account->account_type !== $requiredAccountType) {
                        throw new \RuntimeException(
                            "The selected account for member {$member->membership_id} is not a {$requiredAccountType} account."
                        );
                    }

                    $amount = (float) $memberDividend->dividend_amount;
                    $description = $request->distribution_mode === 'reinvest'
                        ? "Schedule: Dividend reinvestment deposit – {$request->year}"
                        : "Schedule: Dividend payout – {$request->year}";

                    $tx = Transaction::create([
                        'transaction_id'   => 'DIV-SCH-' . strtoupper(uniqid()),
                        'account_id'       => $account->id,
                        'member_id'        => $member->id,
                        'transaction_type' => $request->distribution_mode === 'reinvest'
                            ? 'deposit'
                            : 'dividend_payment',
                        'amount'           => $amount,
                        'balance_before'   => $account->balance,
                        'balance_after'    => $account->balance + $amount,
                        'description'      => $description,
                        'payment_method'   => 'system_transfer',
                        'metadata'         => ['distribution_mode' => $request->distribution_mode],
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
                    $errors[] = ['member_dividend_id' => $entry['member_dividend_id'], 'error' => \App\Support\ErrorMessage::from($e)];
                }
            }

            $dividend = Dividend::find($request->dividend_id);
            $allPaid  = MemberDividend::where('dividend_id', $dividend->id)
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
            return back()->withErrors(['schedule' => 'Failed to run dividend payments: ' . \App\Support\ErrorMessage::from($e)]);
        }

        return redirect()->route('schedule.dividend-payment')
            ->with('success', "Dividend payments posted: {$processed} members, KES " .
                number_format($totalAmount, 2) . '.' . ($failed ? " {$failed} failed." : ''));
    }

    //  EXPORTS

    public function exportLoanDisbursement(Request $request)
    {
        $loans    = Loan::with(['member.user', 'loanProduct'])
            ->where('status', 'approved')
            ->orderBy('approval_date')
            ->get();
        $filename = 'loan_disbursement_schedule_' . now()->format('Y_m_d') . '.csv';

        return response()->stream(function () use ($loans) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Loan Number',
                'Member Name',
                'Membership ID',
                'Loan Product',
                'Approved Amount',
                'Processing Fee',
                'Insurance Fee',
                'Net Disbursement',
                'Approval Date',
                'Term (Months)',
            ]);
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