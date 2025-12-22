<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\MemberDividend;
use App\Models\Dividend;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /**
     * Show loan disbursement schedule
     * Loans that are approved but not yet disbursed
     */
    public function loanDisbursement(Request $request)
    {
        $query = Loan::with(['member.user', 'loanProduct', 'approvedBy'])
            ->where('status', 'approved')
            ->orderBy('approval_date', 'asc');

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('approval_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('approval_date', '<=', $request->date_to);
        }

        // Filter by loan product
        if ($request->filled('loan_product_id')) {
            $query->where('loan_product_id', $request->loan_product_id);
        }

        // Filter by member
        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        $loans = $query->paginate(20);

        // Calculate summary statistics
        $summary = [
            'total_loans' => $query->count(),
            'total_amount' => $query->sum('approved_amount'),
            'total_fees' => $query->get()->sum(function($loan) {
                return $loan->processing_fee + $loan->insurance_fee;
            }),
            'total_net_disbursement' => $query->get()->sum(function($loan) {
                return $loan->approved_amount - $loan->processing_fee - $loan->insurance_fee;
            }),
            'oldest_pending' => $query->oldest('approval_date')->first(),
        ];

        // Group by week for planning
        $weeklySchedule = $query->get()->groupBy(function($loan) {
            return Carbon::parse($loan->approval_date)->format('W-Y'); // Week number-Year
        })->map(function($weekLoans) {
            return [
                'week' => Carbon::parse($weekLoans->first()->approval_date)->format('W'),
                'year' => Carbon::parse($weekLoans->first()->approval_date)->format('Y'),
                'start_date' => Carbon::parse($weekLoans->first()->approval_date)->startOfWeek()->format('Y-m-d'),
                'end_date' => Carbon::parse($weekLoans->first()->approval_date)->endOfWeek()->format('Y-m-d'),
                'count' => $weekLoans->count(),
                'total_amount' => $weekLoans->sum('approved_amount'),
                'loans' => $weekLoans,
            ];
        });

        return Inertia::render('Admin/Schedule/LoanDisbursement', [
            'loans' => $loans,
            'summary' => $summary,
            'weeklySchedule' => $weeklySchedule->values(),
            'filters' => $request->only(['date_from', 'date_to', 'loan_product_id', 'member_id']),
        ]);
    }

    /**
     * Show loan repayment schedule
     * All due and upcoming loan repayments
     */
    public function loanRepayment(Request $request)
    {
        $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? Carbon::now()->endOfMonth()->toDateString();

        $query = LoanRepayment::with(['loan.member.user', 'loan.loanProduct', 'transaction'])
            ->whereBetween('due_date', [$startDate, $endDate])
            ->whereIn('status', ['pending', 'overdue', 'partial'])
            ->orderBy('due_date', 'asc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by member
        if ($request->filled('member_id')) {
            $query->whereHas('loan', function($q) use ($request) {
                $q->where('member_id', $request->member_id);
            });
        }

        $repayments = $query->paginate(50);

        // Calculate summary statistics
        $allRepayments = $query->get();
        
        $summary = [
            'total_repayments' => $allRepayments->count(),
            'total_expected' => $allRepayments->sum('expected_amount'),
            'total_outstanding' => $allRepayments->sum('outstanding_amount'),
            'total_paid' => $allRepayments->sum('paid_amount'),
            'overdue_count' => $allRepayments->where('status', 'overdue')->count(),
            'overdue_amount' => $allRepayments->where('status', 'overdue')->sum('outstanding_amount'),
        ];

        // Group by due date for daily planning
        $dailySchedule = $allRepayments->groupBy(function($repayment) {
            return Carbon::parse($repayment->due_date)->format('Y-m-d');
        })->map(function($dayRepayments, $date) {
            return [
                'date' => $date,
                'day_name' => Carbon::parse($date)->format('l'),
                'count' => $dayRepayments->count(),
                'expected_amount' => $dayRepayments->sum('expected_amount'),
                'outstanding_amount' => $dayRepayments->sum('outstanding_amount'),
                'paid_amount' => $dayRepayments->sum('paid_amount'),
                'overdue_count' => $dayRepayments->where('status', 'overdue')->count(),
            ];
        })->values();

        // Get members with multiple repayments due
        $memberSummary = $allRepayments->groupBy('loan.member_id')->map(function($memberRepayments) {
            $member = $memberRepayments->first()->loan->member;
            return [
                'member_id' => $member->id,
                'member_name' => $member->first_name . ' ' . $member->last_name,
                'membership_id' => $member->membership_id,
                'repayment_count' => $memberRepayments->count(),
                'total_due' => $memberRepayments->sum('outstanding_amount'),
            ];
        })->sortByDesc('repayment_count')->values();

        return Inertia::render('Admin/Schedule/LoanRepayment', [
            'repayments' => $repayments,
            'summary' => $summary,
            'dailySchedule' => $dailySchedule,
            'memberSummary' => $memberSummary,
            'filters' => $request->only(['start_date', 'end_date', 'status', 'member_id']),
        ]);
    }

    /**
     * Show monthly deposit schedule
     * Expected deposits from members
     */
    public function monthlyDeposit(Request $request)
    {
        $month = $request->month ?? Carbon::now()->format('Y-m');
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        // Get all active members
        $members = Member::with(['user', 'accounts' => function($q) {
            $q->where('account_type', 'share_deposits');
        }])
        ->where('membership_status', 'active')
        ->get();

        // Get transactions for the period
        $deposits = Transaction::with(['member', 'account'])
            ->where('transaction_type', 'deposit')
            ->where('status', 'completed')
            ->whereBetween('processed_at', [$startDate, $endDate])
            ->get();

        $depositSchedule = $members->map(function($member) use ($deposits) {
            // Get member's share deposits account
            $account = $member->accounts->first();
            
            // Calculate average monthly deposit from last 6 months
            $sixMonthsAgo = Carbon::now()->subMonths(6);
            $historicalDeposits = Transaction::where('member_id', $member->id)
                ->where('account_id', $account?->id)
                ->where('transaction_type', 'deposit')
                ->where('status', 'completed')
                ->where('processed_at', '>=', $sixMonthsAgo)
                ->get();

            $averageDeposit = $historicalDeposits->count() > 0 
                ? $historicalDeposits->avg('amount') 
                : 0;

            // Check if deposited this month
            $currentMonthDeposit = $deposits->where('member_id', $member->id)->first();

            return [
                'member_id' => $member->id,
                'member_name' => $member->first_name . ' ' . $member->last_name,
                'membership_id' => $member->membership_id,
                'account_balance' => $account?->balance ?? 0,
                'expected_amount' => round($averageDeposit, 2),
                'deposited_amount' => $currentMonthDeposit ? $currentMonthDeposit->amount : 0,
                'deposit_date' => $currentMonthDeposit ? $currentMonthDeposit->processed_at->format('Y-m-d') : null,
                'status' => $currentMonthDeposit ? 'deposited' : 'pending',
                'variance' => $currentMonthDeposit 
                    ? round($currentMonthDeposit->amount - $averageDeposit, 2) 
                    : round(-$averageDeposit, 2),
            ];
        });

        // Sort by status pending first and then by member name
        $depositSchedule = $depositSchedule->sortBy([
            ['status', 'desc'],
            ['member_name', 'asc']
        ])->values();

        // Calculate summary
        $summary = [
            'total_members' => $members->count(),
            'deposited_count' => $depositSchedule->where('status', 'deposited')->count(),
            'pending_count' => $depositSchedule->where('status', 'pending')->count(),
            'total_expected' => $depositSchedule->sum('expected_amount'),
            'total_deposited' => $depositSchedule->sum('deposited_amount'),
            'collection_rate' => $members->count() > 0 
                ? round(($depositSchedule->where('status', 'deposited')->count() / $members->count()) * 100, 2) 
                : 0,
        ];

        // Group by week
        $weeklyProgress = $deposits->groupBy(function($deposit) {
            return Carbon::parse($deposit->processed_at)->format('W');
        })->map(function($weekDeposits, $week) {
            return [
                'week' => $week,
                'count' => $weekDeposits->count(),
                'amount' => $weekDeposits->sum('amount'),
            ];
        })->values();

        return Inertia::render('Admin/Schedule/MonthlyDeposit', [
            'deposits' => $depositSchedule,
            'summary' => $summary,
            'weeklyProgress' => $weeklyProgress,
            'month' => $month,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ]);
    }

    /**
     * Show dividend payment schedule
     * Approved dividends pending payment
     */
    public function dividendPayment(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;

        // Get dividend record for the year
        $dividend = Dividend::with(['calculatedBy', 'approvedBy'])
            ->where('dividend_year', $year)
            ->whereIn('status', ['approved', 'distributed'])
            ->first();

        if (!$dividend) {
            return Inertia::render('Admin/Schedule/DividendPayment', [
                'dividend' => null,
                'memberDividends' => [],
                'summary' => null,
                'year' => $year,
                'message' => 'No approved dividend found for year ' . $year,
            ]);
        }

        // Get member dividends
        $query = MemberDividend::with(['member.user', 'transaction', 'dividend'])
            ->where('dividend_id', $dividend->id)
            ->orderBy('dividend_amount', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by member
        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        $memberDividends = $query->paginate(50);

        // Calculate summary
        $allMemberDividends = $query->get();
        
        $summary = [
            'total_members' => $allMemberDividends->count(),
            'total_dividends' => $allMemberDividends->sum('dividend_amount'),
            'paid_count' => $allMemberDividends->where('status', 'paid')->count(),
            'paid_amount' => $allMemberDividends->where('status', 'paid')->sum('dividend_amount'),
            'pending_count' => $allMemberDividends->where('status', 'pending')->count(),
            'pending_amount' => $allMemberDividends->where('status', 'pending')->sum('dividend_amount'),
            'payment_progress' => $allMemberDividends->count() > 0 
                ? round(($allMemberDividends->where('status', 'paid')->count() / $allMemberDividends->count()) * 100, 2) 
                : 0,
        ];

        // Group by amount ranges for batching
        $amountRanges = [
            'under_10k' => $allMemberDividends->where('dividend_amount', '<', 10000),
            '10k_to_50k' => $allMemberDividends->whereBetween('dividend_amount', [10000, 50000]),
            '50k_to_100k' => $allMemberDividends->whereBetween('dividend_amount', [50000, 100000]),
            'over_100k' => $allMemberDividends->where('dividend_amount', '>', 100000),
        ];

        $batchSummary = collect($amountRanges)->map(function($batch, $range) {
            return [
                'range' => $range,
                'count' => $batch->count(),
                'total_amount' => $batch->sum('dividend_amount'),
                'paid_count' => $batch->where('status', 'paid')->count(),
                'pending_count' => $batch->where('status', 'pending')->count(),
            ];
        })->values();

        return Inertia::render('Admin/Schedule/DividendPayment', [
            'dividend' => $dividend,
            'memberDividends' => $memberDividends,
            'summary' => $summary,
            'batchSummary' => $batchSummary,
            'year' => $year,
            'filters' => $request->only(['status', 'member_id']),
        ]);
    }

    /**
     * Export loan disbursement schedule to CSV
     */
    public function exportLoanDisbursement(Request $request)
    {
        $loans = Loan::with(['member.user', 'loanProduct'])
            ->where('status', 'approved')
            ->orderBy('approval_date', 'asc')
            ->get();

        $filename = 'loan_disbursement_schedule_' . now()->format('Y_m_d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($loans) {
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
                    $loan->approval_date ? $loan->approval_date->format('Y-m-d') : '',
                    $loan->term_months,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export loan repayment schedule to CSV
     */
    public function exportLoanRepayment(Request $request)
    {
        $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? Carbon::now()->endOfMonth()->toDateString();

        $repayments = LoanRepayment::with(['loan.member.user', 'loan.loanProduct'])
            ->whereBetween('due_date', [$startDate, $endDate])
            ->whereIn('status', ['pending', 'overdue', 'partial'])
            ->orderBy('due_date', 'asc')
            ->get();

        $filename = 'loan_repayment_schedule_' . now()->format('Y_m_d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($repayments) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Due Date',
                'Loan Number',
                'Member Name',
                'Membership ID',
                'Expected Amount',
                'Principal',
                'Interest',
                'Penalty',
                'Paid Amount',
                'Outstanding',
                'Status',
                'Days Late',
            ]);

            foreach ($repayments as $repayment) {
                fputcsv($file, [
                    $repayment->due_date->format('Y-m-d'),
                    $repayment->loan->loan_number,
                    $repayment->loan->member->first_name . ' ' . $repayment->loan->member->last_name,
                    $repayment->loan->member->membership_id,
                    $repayment->expected_amount,
                    $repayment->principal_amount,
                    $repayment->interest_amount,
                    $repayment->penalty_amount,
                    $repayment->paid_amount,
                    $repayment->outstanding_amount,
                    $repayment->status,
                    $repayment->days_late,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}