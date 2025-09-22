<?php

namespace App\Jobs;

use App\Models\Member;
use App\Models\Loan;
use App\Models\Transaction;
use App\Models\Account;
use App\Services\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class GenerateReportsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reportType;
    protected $startDate;
    protected $endDate;
    protected $userId;

    public function __construct($reportType, $startDate, $endDate, $userId)
    {
        $this->reportType = $reportType;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->userId = $userId;
    }

    public function handle(ReportService $reportService)
    {
        $reportData = [];

        switch ($this->reportType) {
            case 'financial_summary':
                $reportData = $this->generateFinancialSummary();
                break;
            case 'loan_portfolio':
                $reportData = $this->generateLoanPortfolio();
                break;
            case 'member_activity':
                $reportData = $this->generateMemberActivity();
                break;
            case 'transaction_summary':
                $reportData = $this->generateTransactionSummary();
                break;
        }

        // Generate and save report
        $reportService->generateReport($this->reportType, $reportData, $this->userId);
    }

    private function generateFinancialSummary()
    {
        $totalSavings = Account::where('account_type', 'savings')->sum('balance');
        $totalShares = Account::where('account_type', 'shares')->sum('balance');
        $totalDeposits = Account::where('account_type', 'deposits')->sum('balance');
        $totalLoansOutstanding = Loan::whereIn('status', ['disbursed', 'partial_repayment'])->sum('outstanding_balance');
        $totalLoansDisbursed = Loan::where('status', 'disbursed')->sum('disbursed_amount');

        return [
            'total_savings' => $totalSavings,
            'total_shares' => $totalShares,
            'total_deposits' => $totalDeposits,
            'total_loans_outstanding' => $totalLoansOutstanding,
            'total_loans_disbursed' => $totalLoansDisbursed,
            'total_assets' => $totalSavings + $totalShares + $totalDeposits,
            'total_liabilities' => $totalLoansOutstanding
        ];
    }

    private function generateLoanPortfolio()
    {
        $activeLoans = Loan::whereIn('status', ['disbursed', 'partial_repayment'])->count();
        $overdueLoans = Loan::where('days_in_arrears', '>', 0)->count();
        $totalDisbursed = Loan::where('status', 'disbursed')->sum('disbursed_amount');
        $totalRepaid = Loan::sum('disbursed_amount') - Loan::sum('outstanding_balance');

        return [
            'active_loans' => $activeLoans,
            'overdue_loans' => $overdueLoans,
            'total_disbursed' => $totalDisbursed,
            'total_repaid' => $totalRepaid,
            'repayment_rate' => $totalDisbursed > 0 ? ($totalRepaid / $totalDisbursed) * 100 : 0
        ];
    }

    private function generateMemberActivity()
    {
        $totalMembers = Member::where('membership_status', 'active')->count();
        $newMembers = Member::whereBetween('membership_date', [$this->startDate, $this->endDate])->count();
        $activeMembers = Member::whereHas('transactions', function ($query) {
            $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
        })->count();

        return [
            'total_members' => $totalMembers,
            'new_members' => $newMembers,
            'active_members' => $activeMembers,
            'activity_rate' => $totalMembers > 0 ? ($activeMembers / $totalMembers) * 100 : 0
        ];
    }

    private function generateTransactionSummary()
    {
        $transactions = Transaction::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->selectRaw('transaction_type, COUNT(*) as count, SUM(amount) as total_amount')
            ->groupBy('transaction_type')
            ->get();

        return [
            'transaction_breakdown' => $transactions->toArray(),
            'total_transactions' => $transactions->sum('count'),
            'total_amount' => $transactions->sum('total_amount')
        ];
    }
}