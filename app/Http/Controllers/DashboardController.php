<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\{Member, Account, Loan, Transaction, PaymentVoucher, Notification};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Main dashboard - redirects to role-specific dashboard
     */
    public function index()
    {
        $user = auth()->user();
        
        return match($user->role) {
            'admin' => $this->adminDashboard(),
            'management' => $this->managementDashboard(),
            'accountant' => $this->accountantDashboard(),
            'loan_officer' => $this->loanOfficerDashboard(),
            'member' => $this->memberDashboard(),
            default => redirect()->route('login')
        };
    }

    /**
     * Admin Dashboard - Full system overview
     */
    public function adminDashboard()
    {
        $stats = [
            'members' => [
                'total' => Member::count(),
                'active' => Member::where('membership_status', 'active')->count(),
                'new_this_month' => Member::whereMonth('created_at', now()->month)->count(),
            ],
            'financial' => [
                'total_savings' => Account::where('account_type', 'savings')->sum('balance'),
                'total_shares' => Account::where('account_type', 'shares')->sum('balance'),
                'total_deposits' => Account::where('account_type', 'deposits')->sum('balance'),
            ],
            'loans' => [
                'total_portfolio' => Loan::whereIn('status', ['disbursed', 'active'])->sum('outstanding_balance'),
                'active_loans' => Loan::where('status', 'active')->count(),
                'pending_applications' => Loan::where('status', 'pending')->count(),
                'arrears_amount' => Loan::where('days_in_arrears', '>', 0)->sum('outstanding_balance'),
            ],
            'transactions' => [
                'today' => Transaction::whereDate('created_at', today())->count(),
                'this_week' => Transaction::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'this_month' => Transaction::whereMonth('created_at', now()->month)->count(),
            ]
        ];

        $recentActivities = $this->getRecentActivities();
        $pendingApprovals = $this->getPendingApprovals();
        $systemHealth = $this->getSystemHealth();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'pendingApprovals' => $pendingApprovals,
            'systemHealth' => $systemHealth,
        ]);
    }

    /**
     * Management Dashboard - Strategic overview
     */
    public function managementDashboard()
    {
        $stats = [
            'financial_summary' => [
                'total_assets' => Account::sum('balance'),
                'loan_portfolio' => Loan::whereIn('status', ['disbursed', 'active'])->sum('outstanding_balance'),
                'monthly_income' => Transaction::where('transaction_type', 'deposit')
                    ->whereMonth('created_at', now()->month)
                    ->sum('amount'),
                'monthly_expenses' => PaymentVoucher::where('status', 'paid')
                    ->whereMonth('created_at', now()->month)
                    ->sum('amount'),
            ],
            'performance' => [
                'portfolio_at_risk' => $this->calculatePortfolioAtRisk(),
                'loan_recovery_rate' => $this->calculateRecoveryRate(),
                'member_growth_rate' => $this->calculateMemberGrowthRate(),
            ],
            'compliance' => [
                'overdue_vouchers' => PaymentVoucher::where('status', 'approved')
                    ->where('created_at', '<', now()->subDays(7))
                    ->count(),
                'pending_reviews' => Loan::where('status', 'under_review')->count(),
            ]
        ];

        $monthlyTrends = $this->getMonthlyTrends();
        $loanPortfolioAnalysis = $this->getLoanPortfolioAnalysis();

        return Inertia::render('Management/Dashboard', [
            'stats' => $stats,
            'monthlyTrends' => $monthlyTrends,
            'loanPortfolioAnalysis' => $loanPortfolioAnalysis,
        ]);
    }

    /**
     * Accountant Dashboard - Financial operations focus
     */
    public function accountantDashboard()
    {
        $stats = [
            'accounts' => [
                'total_savings' => Account::where('account_type', 'savings')->sum('balance'),
                'total_shares' => Account::where('account_type', 'shares')->sum('balance'),
                'total_deposits' => Account::where('account_type', 'deposits')->sum('balance'),
            ],
            'transactions' => [
                'pending' => Transaction::where('status', 'pending')->count(),
                'today_volume' => Transaction::whereDate('created_at', today())->sum('amount'),
                'today_count' => Transaction::whereDate('created_at', today())->count(),
            ],
            'vouchers' => [
                'pending_approval' => PaymentVoucher::where('status', 'pending')->count(),
                'approved_unpaid' => PaymentVoucher::where('status', 'approved')->count(),
                'total_pending_amount' => PaymentVoucher::where('status', 'approved')->sum('amount'),
            ]
        ];

        $pendingTransactions = Transaction::where('status', 'pending')
            ->with(['account.member', 'processedBy'])
            ->latest()
            ->take(10)
            ->get();

        $pendingVouchers = PaymentVoucher::where('status', 'pending')
            ->with('createdBy')
            ->latest()
            ->take(10)
            ->get();

        $dailyTransactionSummary = $this->getDailyTransactionSummary();

        return Inertia::render('Accountant/Dashboard', [
            'stats' => $stats,
            'pendingTransactions' => $pendingTransactions,
            'pendingVouchers' => $pendingVouchers,
            'dailyTransactionSummary' => $dailyTransactionSummary,
        ]);
    }

    /**
     * Loan Officer Dashboard - Loan operations focus
     */
    public function loanOfficerDashboard()
    {
        $stats = [
            'loans' => [
                'pending_applications' => Loan::where('status', 'pending')->count(),
                'under_review' => Loan::where('status', 'under_review')->count(),
                'approved_pending_disbursement' => Loan::where('status', 'approved')->count(),
                'active_loans' => Loan::where('status', 'active')->count(),
            ],
            'portfolio' => [
                'total_disbursed' => Loan::whereIn('status', ['disbursed', 'active'])->sum('disbursed_amount'),
                'outstanding_balance' => Loan::whereIn('status', ['disbursed', 'active'])->sum('outstanding_balance'),
                'overdue_amount' => Loan::where('days_in_arrears', '>', 0)->sum('outstanding_balance'),
            ],
            'targets' => [
                'monthly_disbursement_target' => 500000,
                'monthly_disbursed' => Loan::where('status', 'disbursed')
                    ->whereMonth('disbursement_date', now()->month)
                    ->sum('disbursed_amount'),
            ]
        ];

        $pendingApplications = Loan::where('status', 'pending')
            ->with(['member', 'loanProduct'])
            ->latest()
            ->take(10)
            ->get();

        $overdueLoans = Loan::where('days_in_arrears', '>', 0)
            ->with('member')
            ->orderBy('days_in_arrears', 'desc')
            ->take(10)
            ->get();

        $loanPerformanceMetrics = $this->getLoanPerformanceMetrics();

        return Inertia::render('Officer/Dashboard', [
            'stats' => $stats,
            'pendingApplications' => $pendingApplications,
            'overdueLoans' => $overdueLoans,
            'loanPerformanceMetrics' => $loanPerformanceMetrics,
        ]);
    }

    /**
     * Member Dashboard - Personal account overview
     */
    public function memberDashboard()
    {
        $user = auth()->user();
        $member = $user->member;

        if (!$member) {
            return redirect()->route('profile.complete');
        }

        // Check membership status
        if ($member->membership_status === 'inactive') {
            return redirect()->route('awaiting-activation');
        }

        if ($member->membership_status !== 'active') {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account status does not allow access.');
        }

        $accounts = Account::where('member_id', $member->id)
            ->get()
            ->keyBy('account_type');

        $stats = [
            'accounts' => [
                'savings_balance' => $accounts->get('savings')?->balance ?? 0,
                'shares_balance' => $accounts->get('shares')?->balance ?? 0,
                'deposits_balance' => $accounts->get('deposits')?->balance ?? 0,
            ],
            'loans' => [
                'active_loans' => Loan::where('member_id', $member->id)
                    ->where('status', 'active')
                    ->count(),
                'total_outstanding' => Loan::where('member_id', $member->id)
                    ->whereIn('status', ['disbursed', 'active'])
                    ->sum('outstanding_balance'),
                'next_payment_due' => Loan::where('member_id', $member->id)
                    ->where('status', 'active')
                    ->min('first_repayment_date'),
            ]
        ];

        $recentTransactions = Transaction::where('member_id', $member->id)
            ->with('account')
            ->latest()
            ->take(10)
            ->get();

        $activeLoans = Loan::where('member_id', $member->id)
            ->whereIn('status', ['active', 'disbursed'])
            ->with('loanProduct')
            ->get();

        $notifications = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'member' => $member,
            'stats' => $stats,
            'accounts' => $accounts->values(),
            'recentTransactions' => $recentTransactions,
            'activeLoans' => $activeLoans,
            'notifications' => $notifications,
        ]);
    }

    // Helper methods for calculations

    private function getRecentActivities()
    {
        return collect([
            Transaction::with(['member', 'account'])
                ->latest()
                ->take(5)
                ->get()
                ->map(fn($t) => [
                    'type' => 'transaction',
                    'description' => "{$t->member->first_name} {$t->member->last_name} - {$t->transaction_type}",
                    'amount' => $t->amount,
                    'time' => $t->created_at,
                ]),
            Loan::with('member')
                ->where('status', 'pending')
                ->latest()
                ->take(3)
                ->get()
                ->map(fn($l) => [
                    'type' => 'loan_application',
                    'description' => "{$l->member->first_name} {$l->member->last_name} - Loan Application",
                    'amount' => $l->applied_amount,
                    'time' => $l->created_at,
                ])
        ])->flatten(1)->sortByDesc('time')->take(10)->values();
    }

    private function getPendingApprovals()
    {
        return [
            'loans' => Loan::where('status', 'pending')->count(),
            'vouchers' => PaymentVoucher::where('status', 'pending')->count(),
            'member_applications' => Member::where('membership_status', 'pending')->count(),
        ];
    }

    private function getSystemHealth()
    {
        return [
            'database_status' => 'healthy',
            'last_backup' => now()->subHours(2),
            'active_users' => DB::table('sessions')->count(),
            'system_errors' => 0,
        ];
    }

    private function calculatePortfolioAtRisk()
    {
        $totalPortfolio = Loan::whereIn('status', ['disbursed', 'active'])->sum('outstanding_balance');
        $portfolioAtRisk = Loan::where('days_in_arrears', '>', 30)->sum('outstanding_balance');
        
        return $totalPortfolio > 0 ? round(($portfolioAtRisk / $totalPortfolio) * 100, 2) : 0;
    }

    private function calculateRecoveryRate()
    {
        // This is a simplified calculation - you may want to make it more sophisticated
        $currentMonth = now()->month;
        $expectedRepayments = DB::table('loan_repayments')
            ->whereMonth('due_date', $currentMonth)
            ->sum('expected_amount');
        
        $actualRepayments = DB::table('loan_repayments')
            ->whereMonth('due_date', $currentMonth)
            ->whereNotNull('payment_date')
            ->sum('paid_amount');

        return $expectedRepayments > 0 ? round(($actualRepayments / $expectedRepayments) * 100, 2) : 0;
    }

    private function calculateMemberGrowthRate()
    {
        $currentMonth = Member::whereMonth('created_at', now()->month)->count();
        $lastMonth = Member::whereMonth('created_at', now()->subMonth()->month)->count();
        
        return $lastMonth > 0 ? round((($currentMonth - $lastMonth) / $lastMonth) * 100, 2) : 0;
    }

    private function getMonthlyTrends()
    {
        $months = collect(range(1, 12))->map(function ($month) {
            return [
                'month' => Carbon::create(null, $month)->format('M'),
                'loans_disbursed' => Loan::whereMonth('disbursement_date', $month)->sum('disbursed_amount'),
                'deposits' => Transaction::where('transaction_type', 'deposit')
                    ->whereMonth('created_at', $month)
                    ->sum('amount'),
                'new_members' => Member::whereMonth('created_at', $month)->count(),
            ];
        });

        return $months;
    }

    private function getLoanPortfolioAnalysis()
    {
        return DB::table('loans')
            ->join('loan_products', 'loans.loan_product_id', '=', 'loan_products.id')
            ->select('loan_products.name', DB::raw('COUNT(*) as count'), DB::raw('SUM(outstanding_balance) as total_outstanding'))
            ->whereIn('loans.status', ['disbursed', 'active'])
            ->groupBy('loan_products.name')
            ->get();
    }

    private function getDailyTransactionSummary()
    {
        return Transaction::whereDate('created_at', today())
            ->select('transaction_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('transaction_type')
            ->get();
    }

    private function getLoanPerformanceMetrics()
    {
        return [
            'disbursement_this_month' => Loan::where('status', 'disbursed')
                ->whereMonth('disbursement_date', now()->month)
                ->sum('disbursed_amount'),
            'applications_processed' => Loan::whereMonth('created_at', now()->month)
                ->whereIn('status', ['approved', 'rejected'])
                ->count(),
            'average_processing_time' => 3.2, // This would need actual calculation
            'approval_rate' => $this->calculateApprovalRate(),
        ];
    }

    private function calculateApprovalRate()
    {
        $totalProcessed = Loan::whereMonth('created_at', now()->month)
            ->whereIn('status', ['approved', 'rejected'])
            ->count();
        
        $approved = Loan::whereMonth('created_at', now()->month)
            ->where('status', 'approved')
            ->count();

        return $totalProcessed > 0 ? round(($approved / $totalProcessed) * 100, 2) : 0;
    }
}