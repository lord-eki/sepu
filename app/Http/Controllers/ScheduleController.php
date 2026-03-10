<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\MemberDividend;
use App\Models\Dividend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    // ─────────────────────────────────────────────
    //  MONTHLY DEPOSIT SCHEDULE
    // ─────────────────────────────────────────────

    /**
     * Show monthly deposit schedule — all active members with their
     * share-deposit account details pre-populated for batch submission.
     */
    public function monthlyDeposit(Request $request)
    {
        $month     = $request->month ?? Carbon::now()->format('Y-m');
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate   = Carbon::parse($month)->endOfMonth();

        // All active members with their share_deposits accounts
        $members = Member::with([
            'user',
            'accounts' => fn($q) => $q->where('account_type', 'share_deposits')->where('is_active', true),
        ])
            ->where('membership_status', 'active')
            ->orderBy('first_name')
            ->get();

        // Deposits already made this month
        $existingDeposits = Transaction::with('account')
            ->where('transaction_type', 'deposit')
            ->where('status', 'completed')
            ->whereBetween('processed_at', [$startDate, $endDate])
            ->whereIn('member_id', $members->pluck('id'))
            ->get()
            ->keyBy('member_id');          // one per member is enough for status check

        // Build rows for the front-end table
        $rows = $members->map(function ($member) use ($existingDeposits) {
            $account = $member->accounts->first();
            $existing = $existingDeposits->get($member->id);

           
            $avgDeposit = 0;
            if ($account) {
                $avg = Transaction::where('member_id', $member->id)
                    ->where('account_id', $account->id)
                    ->where('transaction_type', 'deposit')
                    ->where('status', 'completed')
                    ->where('processed_at', '>=', Carbon::now()->subMonths(6))
                    ->avg('amount');
                $avgDeposit = round($avg ?? 0, 2);
            }

            return [
                'member_id'        => $member->id,
                'membership_id'    => $member->membership_id,
                'member_name'      => $member->first_name . ' ' . $member->last_name,
                'phone'            => $member->user->phone ?? null,
                'account_id'       => $account?->id,
                'account_number'   => $account?->account_number,
                'account_balance'  => (float) ($account?->balance ?? 0),
                'suggested_amount' => $avgDeposit,
                'amount'           => $avgDeposit,   // editable default
                'deposited_amount' => $existing ? (float) $existing->amount : 0,
                'payment_method'   => 'cash',
                'status'           => $existing ? 'deposited' : 'pending',
                'payment_date'     => $existing?->processed_at?->format('Y-m-d'),
            ];
        })->values();

        $summary = [
            'total_members'    => $members->count(),
            'deposited_count'  => $rows->where('status', 'deposited')->count(),
            'pending_count'    => $rows->where('status', 'pending')->count(),
            'total_expected'   => round($rows->sum('suggested_amount'), 2),
            'total_deposited'  => round($rows->sum('deposited_amount'), 2),
            'collection_rate'  => $members->count() > 0
                ? round(($rows->where('status', 'deposited')->count() / $members->count()) * 100, 2)
                : 0,
        ];

        return Inertia::render('Admin/Schedule/MonthlyDeposit', [
            'rows'    => $rows,
            'summary' => $summary,
            'month'   => $month,
        ]);
    }

    /**
     * Batch-submit monthly deposits.
     * Expects: { month, payment_method, entries: [{ member_id, account_id, amount }] }
     */
    public function submitMonthlyDeposits(Request $request)
    {
        $request->validate([
            'month'                   => 'required|string',
            'entries'                 => 'required|array|min:1',
            'entries.*.member_id'     => 'required|exists:members,id',
            'entries.*.account_id'    => 'required|exists:accounts,id',
            'entries.*.amount'        => 'required|numeric|min:1',
            'entries.*.payment_method'=> 'required|in:cash,mobile_money,bank_transfer,cheque',
        ]);

        $processedBy = Auth::id();
        $now         = now();
        $created     = 0;
        $errors      = [];

        DB::beginTransaction();
        try {
            foreach ($request->entries as $entry) {
                $account = Account::with('member')->findOrFail($entry['account_id']);

                // Generate unique transaction ID
                $txId = 'DEP-' . strtoupper(uniqid());

                Transaction::create([
                    'transaction_id'   => $txId,
                    'account_id'       => $account->id,
                    'member_id'        => $entry['member_id'],
                    'transaction_type' => 'deposit',
                    'amount'           => $entry['amount'],
                    'balance_before'   => $account->balance,
                    'balance_after'    => $account->balance + $entry['amount'],
                    'description'      => 'Monthly share deposit — ' . Carbon::parse($request->month)->format('F Y'),
                    'payment_method'   => $entry['payment_method'],
                    'status'           => 'completed',
                    'processed_by'     => $processedBy,
                    'processed_at'     => $now,
                ]);

                // Update account balance
                $account->increment('balance', $entry['amount']);
                $account->increment('available_balance', $entry['amount']);
                $account->update(['last_transaction_at' => $now]);

                $created++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['submit' => 'Failed to process deposits: ' . $e->getMessage()]);
        }

        return back()->with('success', "Successfully posted {$created} deposit(s).");
    }



    /**
     * Show loan repayment schedule — members with due/overdue repayments,
     * pre-populated with their loan & repayment details for batch submission.
     */
    public function loanRepayment(Request $request)
    {
        $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate   = $request->end_date   ?? Carbon::now()->endOfMonth()->toDateString();
        $status    = $request->status;

        $query = LoanRepayment::with([
            'loan.member.user',
            'loan.loanProduct',
            'loan.member.accounts' => fn($q) => $q->where('account_type', 'share_deposits'),
        ])
            ->whereBetween('due_date', [$startDate, $endDate])
            ->whereIn('status', ['pending', 'overdue', 'partial'])
            ->orderBy('due_date', 'asc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($request->filled('member_id')) {
            $query->whereHas('loan', fn($q) => $q->where('member_id', $request->member_id));
        }

        $allRepayments = $query->get();

        // Shape rows for the front-end
        $rows = $allRepayments->map(function ($repayment) {
            $loan   = $repayment->loan;
            $member = $loan->member;

            return [
                'repayment_id'       => $repayment->id,
                'due_date'           => $repayment->due_date->format('Y-m-d'),
                'loan_id'            => $loan->id,
                'loan_number'        => $loan->loan_number,
                'member_id'          => $member->id,
                'membership_id'      => $member->membership_id,
                'member_name'        => $member->first_name . ' ' . $member->last_name,
                'phone'              => $member->user->phone ?? null,
                'loan_product'       => $loan->loanProduct->name ?? 'N/A',
                'expected_amount'    => (float) $repayment->expected_amount,
                'principal_amount'   => (float) $repayment->principal_amount,
                'interest_amount'    => (float) $repayment->interest_amount,
                'penalty_amount'     => (float) $repayment->penalty_amount,
                'paid_amount'        => (float) $repayment->paid_amount,
                'outstanding_amount' => (float) $repayment->outstanding_amount,
                'amount'             => (float) $repayment->outstanding_amount, // editable default
                'payment_method'     => 'cash',
                'status'             => $repayment->status,
                'days_late'          => $repayment->days_late ?? 0,
                'outstanding_balance'=> (float) $loan->outstanding_balance,
            ];
        })->values();

        $summary = [
            'total_repayments'  => $rows->count(),
            'total_expected'    => round($rows->sum('expected_amount'), 2),
            'total_outstanding' => round($rows->sum('outstanding_amount'), 2),
            'total_paid'        => round($rows->sum('paid_amount'), 2),
            'overdue_count'     => $rows->where('status', 'overdue')->count(),
            'overdue_amount'    => round($rows->where('status', 'overdue')->sum('outstanding_amount'), 2),
        ];

        return Inertia::render('Admin/Schedule/LoanRepayment', [
            'rows'      => $rows,
            'summary'   => $summary,
            'filters'   => $request->only(['start_date', 'end_date', 'status', 'member_id']),
            'startDate' => $startDate,
            'endDate'   => $endDate,
        ]);
    }

    /**
     * Batch-submit loan repayments.
     * Expects: { entries: [{ repayment_id, loan_id, member_id, amount, payment_method }] }
     */
    public function submitLoanRepayments(Request $request)
    {
        $request->validate([
            'entries'                    => 'required|array|min:1',
            'entries.*.repayment_id'     => 'required|exists:loan_repayments,id',
            'entries.*.loan_id'          => 'required|exists:loans,id',
            'entries.*.member_id'        => 'required|exists:members,id',
            'entries.*.amount'           => 'required|numeric|min:1',
            'entries.*.payment_method'   => 'required|in:cash,mobile_money,bank_transfer,cheque',
        ]);

        $processedBy = Auth::id();
        $now         = now();
        $created     = 0;

        DB::beginTransaction();
        try {
            foreach ($request->entries as $entry) {
                $repayment = LoanRepayment::with('loan')->findOrFail($entry['repayment_id']);
                $loan      = $repayment->loan;
                $amount    = (float) $entry['amount'];

                // Determine how much goes to principal, interest, penalty
                $penaltyPaid    = min($amount, (float) $repayment->penalty_amount);
                $remaining      = $amount - $penaltyPaid;
                $interestPaid   = min($remaining, (float) $repayment->interest_amount);
                $remaining     -= $interestPaid;
                $principalPaid  = min($remaining, (float) $repayment->principal_amount);

                $newPaid        = (float) $repayment->paid_amount + $amount;
                $newOutstanding = max(0, (float) $repayment->outstanding_amount - $amount);

                $newStatus = 'paid';
                if ($newOutstanding > 0) {
                    $newStatus = 'partial';
                }

                // Get member share_deposits account for transaction record
                $account = Account::where('member_id', $entry['member_id'])
                    ->where('account_type', 'share_deposits')
                    ->first();

                $txId = 'RPY-' . strtoupper(uniqid());

                $transaction = Transaction::create([
                    'transaction_id'   => $txId,
                    'account_id'       => $account?->id ?? $loan->id, // fallback
                    'member_id'        => $entry['member_id'],
                    'transaction_type' => 'loan_repayment',
                    'amount'           => $amount,
                    'balance_before'   => $account?->balance ?? 0,
                    'balance_after'    => $account?->balance ?? 0,
                    'description'      => "Loan repayment — {$loan->loan_number}",
                    'reference_number' => $loan->loan_number,
                    'payment_method'   => $entry['payment_method'],
                    'status'           => 'completed',
                    'processed_by'     => $processedBy,
                    'processed_at'     => $now,
                ]);

                // Update repayment record
                $repayment->update([
                    'paid_amount'        => $newPaid,
                    'outstanding_amount' => $newOutstanding,
                    'status'             => $newStatus,
                    'payment_date'       => $now->toDateString(),
                    'transaction_id'     => $transaction->id,
                    'days_late'          => max(0, now()->diffInDays($repayment->due_date, false) * -1),
                ]);

                // Update loan balances
                $loan->decrement('outstanding_balance', $amount);
                $loan->decrement('principal_balance', $principalPaid);
                $loan->decrement('interest_balance', $interestPaid);
                if ($penaltyPaid > 0) {
                    $loan->decrement('penalty_balance', $penaltyPaid);
                }

                // Check if loan is fully paid
                $loan->refresh();
                if ($loan->outstanding_balance <= 0) {
                    $loan->update(['status' => 'closed', 'outstanding_balance' => 0]);
                }

                $created++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['submit' => 'Failed to process repayments: ' . $e->getMessage()]);
        }

        return back()->with('success', "Successfully posted {$created} repayment(s).");
    }



    public function loanDisbursement(Request $request)
    {
        $query = Loan::with(['member.user', 'loanProduct', 'approvedBy'])
            ->where('status', 'approved')
            ->orderBy('approval_date', 'asc');

        if ($request->filled('date_from')) {
            $query->whereDate('approval_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('approval_date', '<=', $request->date_to);
        }
        if ($request->filled('loan_product_id')) {
            $query->where('loan_product_id', $request->loan_product_id);
        }
        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        $loans = $query->paginate(20);

        return Inertia::render('Admin/Schedule/LoanDisbursement', [
            'loans'   => $loans,
            'filters' => $request->only(['date_from', 'date_to', 'loan_product_id', 'member_id']),
        ]);
    }



    public function dividendPayment(Request $request)
    {
        $year     = $request->year ?? Carbon::now()->year;
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
                'message'         => 'No approved dividend found for year ' . $year,
            ]);
        }

        $query = MemberDividend::with(['member.user', 'transaction', 'dividend'])
            ->where('dividend_id', $dividend->id)
            ->orderBy('dividend_amount', 'desc');

        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('member_id')) $query->where('member_id', $request->member_id);

        $memberDividends    = $query->paginate(50);
        $allMemberDividends = $query->get();

        $summary = [
            'total_members'     => $allMemberDividends->count(),
            'total_dividends'   => $allMemberDividends->sum('dividend_amount'),
            'paid_count'        => $allMemberDividends->where('status', 'paid')->count(),
            'paid_amount'       => $allMemberDividends->where('status', 'paid')->sum('dividend_amount'),
            'pending_count'     => $allMemberDividends->where('status', 'pending')->count(),
            'pending_amount'    => $allMemberDividends->where('status', 'pending')->sum('dividend_amount'),
            'payment_progress'  => $allMemberDividends->count() > 0
                ? round(($allMemberDividends->where('status', 'paid')->count() / $allMemberDividends->count()) * 100, 2)
                : 0,
        ];

        return Inertia::render('Admin/Schedule/DividendPayment', [
            'dividend'        => $dividend,
            'memberDividends' => $memberDividends,
            'summary'         => $summary,
            'year'            => $year,
            'filters'         => $request->only(['status', 'member_id']),
        ]);
    }

    public function exportLoanDisbursement(Request $request)
    {
        $loans    = Loan::with(['member.user', 'loanProduct'])->where('status', 'approved')->orderBy('approval_date')->get();
        $filename = 'loan_disbursement_schedule_' . now()->format('Y_m_d') . '.csv';

        return response()->stream(function () use ($loans) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Loan Number', 'Member Name', 'Membership ID', 'Loan Product', 'Approved Amount', 'Processing Fee', 'Insurance Fee', 'Net Disbursement', 'Approval Date', 'Term (Months)']);
            foreach ($loans as $loan) {
                fputcsv($file, [$loan->loan_number, $loan->member->first_name . ' ' . $loan->member->last_name, $loan->member->membership_id, $loan->loanProduct->name, $loan->approved_amount, $loan->processing_fee, $loan->insurance_fee, $loan->approved_amount - $loan->processing_fee - $loan->insurance_fee, $loan->approval_date?->format('Y-m-d'), $loan->term_months]);
            }
            fclose($file);
        }, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$filename}\""]);
    }
}