<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\{
    Member,
    Account,
    Transaction,
    Loan,
    LoanRepayment,
    Dividend,
    MemberDividend,
    Budget,
    BudgetItem,
    PaymentVoucher
};
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    /**
     * Financial Reports
     */
    public function balanceSheet(Request $request)
    {
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));
        $asOf = Carbon::parse($date)->endOfDay();

        // Assets
        $assets = [
            'current_assets' => [
                'cash_and_equivalents' => $this->getCashAndEquivalents($asOf),
                'loans_receivable' => $this->getLoansReceivable($asOf),
                'other_receivables' => $this->getOtherReceivables($asOf),
            ],
            'non_current_assets' => [
                'property_equipment' => $this->getPropertyEquipment($asOf),
                'investments' => $this->getInvestments($asOf),
            ]
        ];

        // Liabilities
        $liabilities = [
            'current_liabilities' => [
                'member_deposits' => $this->getMemberDeposits($asOf),
                'accrued_expenses' => $this->getAccruedExpenses($asOf),
                'other_payables' => $this->getOtherPayables($asOf),
            ],
            'non_current_liabilities' => [
                'long_term_debt' => $this->getLongTermDebt($asOf),
            ]
        ];

        // Equity
        $equity = [
            'share_capital' => $this->getShareCapital($asOf),
            'retained_earnings' => $this->getRetainedEarnings($asOf),
            'reserves' => $this->getReserves($asOf),
        ];

        $totalAssets = collect($assets)->flatten()->sum();
        $totalLiabilities = collect($liabilities)->flatten()->sum();
        $totalEquity = collect($equity)->flatten()->sum();

        return Inertia::render('Reports/Financial/BalanceSheet', [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'totals' => [
                'assets' => $totalAssets,
                'liabilities' => $totalLiabilities,
                'equity' => $totalEquity,
            ],
            'date' => $date,
        ]);
    }

    public function incomeStatement(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Revenue
        $revenue = [
            'interest_income' => $this->getInterestIncome($start, $end),
            'loan_fees' => $this->getLoanFees($start, $end),
            'service_charges' => $this->getServiceCharges($start, $end),
            'other_income' => $this->getOtherIncome($start, $end),
        ];

        // Expenses
        $expenses = [
            'interest_expense' => $this->getInterestExpense($start, $end),
            'staff_costs' => $this->getStaffCosts($start, $end),
            'administrative_expenses' => $this->getAdministrativeExpenses($start, $end),
            'loan_loss_provision' => $this->getLoanLossProvision($start, $end),
            'other_expenses' => $this->getOtherExpenses($start, $end),
        ];

        $totalRevenue = collect($revenue)->sum();
        $totalExpenses = collect($expenses)->sum();
        $netIncome = $totalRevenue - $totalExpenses;

        return Inertia::render('Reports/Financial/IncomeStatement', [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'totals' => [
                'revenue' => $totalRevenue,
                'expenses' => $totalExpenses,
                'net_income' => $netIncome,
            ],
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    public function cashFlow(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Operating Activities
        $operatingActivities = [
            'net_income' => $this->getNetIncome($start, $end),
            'loan_disbursements' => $this->getLoanDisbursements($start, $end),
            'loan_repayments' => $this->getLoanRepayments($start, $end),
            'member_deposits' => $this->getMemberDepositsFlow($start, $end),
            'member_withdrawals' => $this->getMemberWithdrawalsFlow($start, $end),
        ];

        // Investing Activities
        $investingActivities = [
            'equipment_purchases' => $this->getEquipmentPurchases($start, $end),
            'investments' => $this->getInvestmentFlow($start, $end),
        ];

        // Financing Activities
        $financingActivities = [
            'share_capital' => $this->getShareCapitalFlow($start, $end),
            'dividend_payments' => $this->getDividendPayments($start, $end),
        ];

        $netOperatingCash = collect($operatingActivities)->sum();
        $netInvestingCash = collect($investingActivities)->sum();
        $netFinancingCash = collect($financingActivities)->sum();
        $netCashFlow = $netOperatingCash + $netInvestingCash + $netFinancingCash;

        return Inertia::render('Reports/Financial/CashFlow', [
            'operating_activities' => $operatingActivities,
            'investing_activities' => $investingActivities,
            'financing_activities' => $financingActivities,
            'totals' => [
                'operating' => $netOperatingCash,
                'investing' => $netInvestingCash,
                'financing' => $netFinancingCash,
                'net_cash_flow' => $netCashFlow,
            ],
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    public function trialBalance(Request $request)
    {
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));
        $asOf = Carbon::parse($date)->endOfDay();

        // This would typically come from a chart of accounts
        $accounts = $this->getTrialBalanceAccounts($asOf);

        $totalDebits = collect($accounts)->sum('debit');
        $totalCredits = collect($accounts)->sum('credit');

        return Inertia::render('Reports/Financial/TrialBalance', [
            'accounts' => $accounts,
            'totals' => [
                'debits' => $totalDebits,
                'credits' => $totalCredits,
                'difference' => $totalDebits - $totalCredits,
            ],
            'date' => $date,
        ]);
    }

    /**
     * Member Reports
     */
    public function memberRegister(Request $request)
    {
        $status = $request->input('status');
        $county = $request->input('county');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Member::with(['user', 'accounts'])
            ->select([
                'id', 'user_id', 'membership_id', 'first_name', 'last_name', 
                'id_number', 'phone', 'email', 'county', 'occupation', 
                'membership_status', 'membership_date'
            ]);

        if ($status) {
            $query->where('membership_status', $status);
        }

        if ($county) {
            $query->where('county', $county);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('membership_date', [$startDate, $endDate]);
        }

        $members = $query->orderBy('membership_date', 'desc')->paginate(100);

        // Calculate summary statistics
        $summary = [
            'total_members' => $members->total(),
            'active_members' => Member::where('membership_status', 'active')->count(),
            'inactive_members' => Member::where('membership_status', 'inactive')->count(),
            'suspended_members' => Member::where('membership_status', 'suspended')->count(),
            'new_members_this_month' => Member::whereMonth('membership_date', Carbon::now()->month)
                ->whereYear('membership_date', Carbon::now()->year)->count(),
        ];

        return Inertia::render('Reports/Members/Register', [
            'members' => $members,
            'summary' => $summary,
            'filters' => [
                'status' => $status,
                'county' => $county,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    public function memberShares(Request $request)
    {
        $members = Member::with(['accounts' => function($query) {
            $query->where('account_type', 'shares');
        }])
        ->whereHas('accounts', function($query) {
            $query->where('account_type', 'shares');
        })
        ->select(['id', 'membership_id', 'first_name', 'last_name', 'membership_date'])
        ->get()
        ->map(function($member) {
            $shareAccount = $member->accounts->first();
            return [
                'member_id' => $member->id,
                'membership_id' => $member->membership_id,
                'name' => $member->first_name . ' ' . $member->last_name,
                'shares_balance' => $shareAccount ? $shareAccount->balance : 0,
                'account_number' => $shareAccount ? $shareAccount->account_number : null,
                'membership_date' => $member->membership_date,
            ];
        });

        $summary = [
            'total_members' => $members->count(),
            'total_shares' => $members->sum('shares_balance'),
            'average_shares' => $members->avg('shares_balance'),
            'highest_shares' => $members->max('shares_balance'),
        ];

        return Inertia::render('Reports/Members/Shares', [
            'members' => $members,
            'summary' => $summary,
        ]);
    }

    public function memberSavings(Request $request)
    {
        $members = Member::with(['accounts' => function($query) {
            $query->where('account_type', 'savings');
        }])
        ->whereHas('accounts', function($query) {
            $query->where('account_type', 'savings');
        })
        ->select(['id', 'membership_id', 'first_name', 'last_name', 'membership_date'])
        ->get()
        ->map(function($member) {
            $savingsAccount = $member->accounts->first();
            return [
                'member_id' => $member->id,
                'membership_id' => $member->membership_id,
                'name' => $member->first_name . ' ' . $member->last_name,
                'savings_balance' => $savingsAccount ? $savingsAccount->balance : 0,
                'account_number' => $savingsAccount ? $savingsAccount->account_number : null,
                'last_transaction' => $savingsAccount ? $savingsAccount->last_transaction_at : null,
            ];
        });

        $summary = [
            'total_members' => $members->count(),
            'total_savings' => $members->sum('savings_balance'),
            'average_savings' => $members->avg('savings_balance'),
            'highest_savings' => $members->max('savings_balance'),
        ];

        return Inertia::render('Reports/Members/Savings', [
            'members' => $members,
            'summary' => $summary,
        ]);
    }

    public function memberLoans(Request $request)
    {
        $status = $request->input('status');
        
        $query = Member::with(['loans.loanProduct'])
            ->whereHas('loans');

        if ($status) {
            $query->whereHas('loans', function($q) use ($status) {
                $q->where('status', $status);
            });
        }

        $members = $query->select(['id', 'membership_id', 'first_name', 'last_name'])
            ->get()
            ->map(function($member) use ($status) {
                $loansQuery = $member->loans();
                if ($status) {
                    $loansQuery->where('status', $status);
                }
                $loans = $loansQuery->get();

                return [
                    'member_id' => $member->id,
                    'membership_id' => $member->membership_id,
                    'name' => $member->first_name . ' ' . $member->last_name,
                    'total_loans' => $loans->count(),
                    'total_disbursed' => $loans->sum('disbursed_amount'),
                    'total_outstanding' => $loans->sum('outstanding_balance'),
                    'loans' => $loans->map(function($loan) {
                        return [
                            'loan_number' => $loan->loan_number,
                            'product_name' => $loan->loanProduct->name,
                            'disbursed_amount' => $loan->disbursed_amount,
                            'outstanding_balance' => $loan->outstanding_balance,
                            'status' => $loan->status,
                            'days_in_arrears' => $loan->days_in_arrears,
                        ];
                    }),
                ];
            });

        $summary = [
            'total_members' => $members->count(),
            'total_loans' => $members->sum('total_loans'),
            'total_disbursed' => $members->sum('total_disbursed'),
            'total_outstanding' => $members->sum('total_outstanding'),
        ];

        return Inertia::render('Reports/Members/Loans', [
            'members' => $members,
            'summary' => $summary,
            'filters' => ['status' => $status],
        ]);
    }

    /**
     * Loan Reports
     */
    public function loanPortfolio(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $loans = Loan::with(['member', 'loanProduct'])
            ->whereBetween('disbursement_date', [$startDate, $endDate])
            ->whereNotNull('disbursement_date')
            ->select([
                'id', 'loan_number', 'member_id', 'loan_product_id', 
                'disbursed_amount', 'outstanding_balance', 'status', 
                'disbursement_date', 'maturity_date', 'days_in_arrears'
            ])
            ->get();

        $portfolioByProduct = $loans->groupBy('loanProduct.name')->map(function($productLoans) {
            return [
                'count' => $productLoans->count(),
                'total_disbursed' => $productLoans->sum('disbursed_amount'),
                'total_outstanding' => $productLoans->sum('outstanding_balance'),
                'average_loan_size' => $productLoans->avg('disbursed_amount'),
            ];
        });

        $portfolioByStatus = $loans->groupBy('status')->map(function($statusLoans) {
            return [
                'count' => $statusLoans->count(),
                'total_disbursed' => $statusLoans->sum('disbursed_amount'),
                'total_outstanding' => $statusLoans->sum('outstanding_balance'),
            ];
        });

        $summary = [
            'total_loans' => $loans->count(),
            'total_disbursed' => $loans->sum('disbursed_amount'),
            'total_outstanding' => $loans->sum('outstanding_balance'),
            'average_loan_size' => $loans->avg('disbursed_amount'),
            'portfolio_at_risk' => $loans->where('days_in_arrears', '>', 0)->sum('outstanding_balance'),
        ];

        return Inertia::render('Reports/Loans/Portfolio', [
            'loans' => $loans,
            'portfolio_by_product' => $portfolioByProduct,
            'portfolio_by_status' => $portfolioByStatus,
            'summary' => $summary,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    public function loanArrears(Request $request)
    {
        $loans = Loan::with(['member', 'loanProduct'])
            ->where('status', 'active')
            ->where('days_in_arrears', '>', 0)
            ->select([
                'id', 'loan_number', 'member_id', 'loan_product_id',
                'disbursed_amount', 'outstanding_balance', 'days_in_arrears',
                'disbursement_date', 'maturity_date'
            ])
            ->orderBy('days_in_arrears', 'desc')
            ->get();

        $arrearsAnalysis = [
            '1-30_days' => $loans->whereBetween('days_in_arrears', [1, 30]),
            '31-60_days' => $loans->whereBetween('days_in_arrears', [31, 60]),
            '61-90_days' => $loans->whereBetween('days_in_arrears', [61, 90]),
            '91-180_days' => $loans->whereBetween('days_in_arrears', [91, 180]),
            'over_180_days' => $loans->where('days_in_arrears', '>', 180),
        ];

        $summary = [
            'total_loans_in_arrears' => $loans->count(),
            'total_amount_in_arrears' => $loans->sum('outstanding_balance'),
            'average_days_in_arrears' => $loans->avg('days_in_arrears'),
            'portfolio_at_risk_ratio' => $this->calculatePortfolioAtRiskRatio(),
        ];

        return Inertia::render('Reports/Loans/Arrears', [
            'loans' => $loans,
            'arrears_analysis' => $arrearsAnalysis->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total_outstanding' => $group->sum('outstanding_balance'),
                    'percentage' => $group->count() > 0 ? ($group->sum('outstanding_balance') / $group->sum('outstanding_balance')) * 100 : 0,
                ];
            }),
            'summary' => $summary,
        ]);
    }

    public function loanDisbursement(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $disbursements = Loan::with(['member', 'loanProduct', 'disbursedBy'])
            ->whereBetween('disbursement_date', [$startDate, $endDate])
            ->whereNotNull('disbursement_date')
            ->select([
                'id', 'loan_number', 'member_id', 'loan_product_id',
                'disbursed_amount', 'disbursement_date', 'disbursed_by'
            ])
            ->orderBy('disbursement_date', 'desc')
            ->get();

        $dailyDisbursements = $disbursements->groupBy(function($loan) {
            return Carbon::parse($loan->disbursement_date)->format('Y-m-d');
        })->map(function($dayLoans) {
            return [
                'count' => $dayLoans->count(),
                'total_amount' => $dayLoans->sum('disbursed_amount'),
            ];
        });

        $summary = [
            'total_disbursements' => $disbursements->count(),
            'total_amount' => $disbursements->sum('disbursed_amount'),
            'average_loan_size' => $disbursements->avg('disbursed_amount'),
            'daily_average' => $disbursements->count() > 0 ? $disbursements->sum('disbursed_amount') / $dailyDisbursements->count() : 0,
        ];

        return Inertia::render('Reports/Loans/Disbursement', [
            'disbursements' => $disbursements,
            'daily_disbursements' => $dailyDisbursements,
            'summary' => $summary,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    public function loanCollection(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $repayments = LoanRepayment::with(['loan.member', 'transaction'])
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->whereNotNull('payment_date')
            ->select([
                'id', 'loan_id', 'transaction_id', 'due_date', 'payment_date',
                'expected_amount', 'paid_amount', 'principal_amount', 
                'interest_amount', 'penalty_amount'
            ])
            ->orderBy('payment_date', 'desc')
            ->get();

        $dailyCollections = $repayments->groupBy(function($repayment) {
            return Carbon::parse($repayment->payment_date)->format('Y-m-d');
        })->map(function($dayRepayments) {
            return [
                'count' => $dayRepayments->count(),
                'total_amount' => $dayRepayments->sum('paid_amount'),
                'principal' => $dayRepayments->sum('principal_amount'),
                'interest' => $dayRepayments->sum('interest_amount'),
                'penalty' => $dayRepayments->sum('penalty_amount'),
            ];
        });

        $summary = [
            'total_repayments' => $repayments->count(),
            'total_collected' => $repayments->sum('paid_amount'),
            'total_principal' => $repayments->sum('principal_amount'),
            'total_interest' => $repayments->sum('interest_amount'),
            'total_penalty' => $repayments->sum('penalty_amount'),
            'collection_rate' => $this->calculateCollectionRate($startDate, $endDate),
        ];

        return Inertia::render('Reports/Loans/Collection', [
            'repayments' => $repayments,
            'daily_collections' => $dailyCollections,
            'summary' => $summary,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    /**
     * Transaction Reports
     */
    public function dailyTransactions(Request $request)
    {
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));
        $transactionDate = Carbon::parse($date);

        $transactions = Transaction::with(['member', 'account', 'processedBy'])
            ->whereDate('created_at', $transactionDate)
            ->select([
                'id', 'transaction_id', 'member_id', 'account_id', 'transaction_type',
                'amount', 'description', 'payment_method', 'status', 'processed_by', 'created_at'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = [
            'total_transactions' => $transactions->count(),
            'total_amount' => $transactions->sum('amount'),
            'deposits' => $transactions->where('transaction_type', 'deposit'),
            'withdrawals' => $transactions->where('transaction_type', 'withdrawal'),
            'transfers' => $transactions->where('transaction_type', 'transfer'),
        ];

        $hourlyBreakdown = $transactions->groupBy(function($transaction) {
            return Carbon::parse($transaction->created_at)->format('H:00');
        })->map(function($hourTransactions) {
            return [
                'count' => $hourTransactions->count(),
                'total_amount' => $hourTransactions->sum('amount'),
            ];
        });

        return Inertia::render('Reports/Transactions/Daily', [
            'transactions' => $transactions,
            'summary' => $summary,
            'hourly_breakdown' => $hourlyBreakdown,
            'date' => $date,
        ]);
    }

    public function monthlyTransactions(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        $transactions = Transaction::with(['member', 'account'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select([
                'id', 'transaction_type', 'amount', 'created_at', 'status'
            ])
            ->get();

        $dailyBreakdown = $transactions->groupBy(function($transaction) {
            return Carbon::parse($transaction->created_at)->format('Y-m-d');
        })->map(function($dayTransactions) {
            return [
                'count' => $dayTransactions->count(),
                'total_amount' => $dayTransactions->sum('amount'),
                'deposits' => $dayTransactions->where('transaction_type', 'deposit')->sum('amount'),
                'withdrawals' => $dayTransactions->where('transaction_type', 'withdrawal')->sum('amount'),
            ];
        });

        $typeBreakdown = $transactions->groupBy('transaction_type')->map(function($typeTransactions) {
            return [
                'count' => $typeTransactions->count(),
                'total_amount' => $typeTransactions->sum('amount'),
            ];
        });

        $summary = [
            'total_transactions' => $transactions->count(),
            'total_amount' => $transactions->sum('amount'),
            'successful_transactions' => $transactions->where('status', 'completed')->count(),
            'failed_transactions' => $transactions->where('status', 'failed')->count(),
        ];

        return Inertia::render('Reports/Transactions/Monthly', [
            'daily_breakdown' => $dailyBreakdown,
            'type_breakdown' => $typeBreakdown,
            'summary' => $summary,
            'month' => $month,
        ]);
    }

    public function annualTransactions(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();

        $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                'transaction_type', 'amount', 'created_at', 'status'
            ])
            ->get();

        $monthlyBreakdown = $transactions->groupBy(function($transaction) {
            return Carbon::parse($transaction->created_at)->format('Y-m');
        })->map(function($monthTransactions) {
            return [
                'count' => $monthTransactions->count(),
                'total_amount' => $monthTransactions->sum('amount'),
                'deposits' => $monthTransactions->where('transaction_type', 'deposit')->sum('amount'),
                'withdrawals' => $monthTransactions->where('transaction_type', 'withdrawal')->sum('amount'),
            ];
        });

        $quarterlyBreakdown = $transactions->groupBy(function($transaction) {
            $quarter = Carbon::parse($transaction->created_at)->quarter;
            return "Q{$quarter}";
        })->map(function($quarterTransactions) {
            return [
                'count' => $quarterTransactions->count(),
                'total_amount' => $quarterTransactions->sum('amount'),
            ];
        });

        $summary = [
            'total_transactions' => $transactions->count(),
            'total_amount' => $transactions->sum('amount'),
            'growth_rate' => $this->calculateTransactionGrowthRate($year),
            'average_monthly_volume' => $transactions->sum('amount') / 12,
        ];

        return Inertia::render('Reports/Transactions/Annual', [
            'monthly_breakdown' => $monthlyBreakdown,
            'quarterly_breakdown' => $quarterlyBreakdown,
            'summary' => $summary,
            'year' => $year,
        ]);
    }

    /**
     * Dashboard Statistics API
     */
    public function dashboardStats(Request $request)
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();

        return response()->json([
            'members' => [
                'total' => Member::count(),
                'active' => Member::where('membership_status', 'active')->count(),
                'new_this_month' => Member::where('membership_date', '>=', $thisMonth)->count(),
            ],
            'loans' => [
                'total' => Loan::count(),
                'active' => Loan::where('status', 'active')->count(),
                'total_outstanding' => Loan::where('status', 'active')->sum('outstanding_balance'),
                'in_arrears' => Loan::where('days_in_arrears', '>', 0)->count(),
            ],
            'transactions' => [
                'today' => Transaction::whereDate('created_at', $today)->count(),
                'today_amount' => Transaction::whereDate('created_at', $today)->sum('amount'),
                'this_month' => Transaction::where('created_at', '>=', $thisMonth)->count(),
                'this_month_amount' => Transaction::where('created_at', '>=', $thisMonth)->sum('amount'),
            'this_year' => Transaction::where('created_at', '>=', $thisYear)->count(),
            'this_year_amount' => Transaction::where('created_at', '>=', $thisYear)->sum('amount'),
        ],
        'savings' => [
            'total_balance' => Account::where('account_type', 'savings')->sum('balance'),
            'total_accounts' => Account::where('account_type', 'savings')->count(),
            'average_balance' => Account::where('account_type', 'savings')->avg('balance'),
        ],
        'shares' => [
            'total_balance' => Account::where('account_type', 'shares')->sum('balance'),
            'total_accounts' => Account::where('account_type', 'shares')->count(),
            'average_balance' => Account::where('account_type', 'shares')->avg('balance'),
        ],
    ]);
}

/**
 * Regulatory Reports
 */
public function statutoryReports(Request $request)
{
    $year = $request->input('year', Carbon::now()->year);
    $quarter = $request->input('quarter');
    
    // Define reporting periods
    $periods = $this->getReportingPeriods($year, $quarter);
    
    // Generate statutory reports data
    $reports = [
        'membership_report' => $this->generateMembershipReport($periods),
        'financial_performance' => $this->generateFinancialPerformanceReport($periods),
        'loan_portfolio_report' => $this->generateLoanPortfolioReport($periods),
        'liquidity_report' => $this->generateLiquidityReport($periods),
        'capital_adequacy' => $this->generateCapitalAdequacyReport($periods),
        'governance_report' => $this->generateGovernanceReport($periods),
    ];
    
    return Inertia::render('Reports/Regulatory/Statutory', [
        'reports' => $reports,
        'year' => $year,
        'quarter' => $quarter,
        'periods' => $periods,
    ]);
}

public function complianceReports(Request $request)
{
    $startDate = $request->input('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
    $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
    
    $start = Carbon::parse($startDate);
    $end = Carbon::parse($endDate);
    
    // Compliance monitoring data
    $compliance = [
        'kyc_compliance' => $this->checkKYCCompliance($start, $end),
        'aml_monitoring' => $this->checkAMLCompliance($start, $end),
        'liquidity_ratios' => $this->calculateLiquidityRatios($start, $end),
        'capital_ratios' => $this->calculateCapitalRatios($start, $end),
        'lending_limits' => $this->checkLendingLimits($start, $end),
        'reserve_requirements' => $this->checkReserveRequirements($start, $end),
        'regulatory_submissions' => $this->getRegulatorySubmissions($start, $end),
    ];
    
    // Compliance score calculation
    $complianceScore = $this->calculateComplianceScore($compliance);
    
    return Inertia::render('Reports/Regulatory/Compliance', [
        'compliance' => $compliance,
        'compliance_score' => $complianceScore,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);
}

/**
 * Custom Reports
 */
public function customBuilder(Request $request)
{
    // Available data sources for custom reports
    $dataSources = [
        'members' => [
            'name' => 'Members',
            'fields' => [
                'membership_id', 'first_name', 'last_name', 'id_number', 
                'phone', 'email', 'county', 'occupation', 'membership_status',
                'membership_date', 'created_at', 'updated_at'
            ],
        ],
        'accounts' => [
            'name' => 'Accounts',
            'fields' => [
                'account_number', 'account_type', 'balance', 'available_balance',
                'status', 'last_transaction_at', 'created_at'
            ],
        ],
        'transactions' => [
            'name' => 'Transactions',
            'fields' => [
                'transaction_id', 'transaction_type', 'amount', 'balance_before',
                'balance_after', 'description', 'payment_method', 'status',
                'created_at'
            ],
        ],
        'loans' => [
            'name' => 'Loans',
            'fields' => [
                'loan_number', 'disbursed_amount', 'outstanding_balance',
                'interest_rate', 'status', 'disbursement_date', 'maturity_date',
                'days_in_arrears', 'created_at'
            ],
        ],
        'loan_repayments' => [
            'name' => 'Loan Repayments',
            'fields' => [
                'due_date', 'payment_date', 'expected_amount', 'paid_amount',
                'principal_amount', 'interest_amount', 'penalty_amount',
                'status', 'created_at'
            ],
        ],
        'dividends' => [
            'name' => 'Dividends',
            'fields' => [
                'year', 'profit_amount', 'dividend_rate', 'total_shares',
                'status', 'approved_at', 'created_at'
            ],
        ],
    ];
    
    // Report templates
    $templates = [
        'member_summary' => [
            'name' => 'Member Summary Report',
            'description' => 'Comprehensive member information with account balances',
            'data_sources' => ['members', 'accounts'],
        ],
        'loan_analysis' => [
            'name' => 'Loan Analysis Report',
            'description' => 'Detailed loan portfolio analysis with repayment tracking',
            'data_sources' => ['loans', 'loan_repayments', 'members'],
        ],
        'financial_activity' => [
            'name' => 'Financial Activity Report',
            'description' => 'Transaction analysis and financial movement tracking',
            'data_sources' => ['transactions', 'accounts', 'members'],
        ],
        'dividend_distribution' => [
            'name' => 'Dividend Distribution Report',
            'description' => 'Member dividend allocations and payment status',
            'data_sources' => ['dividends', 'member_dividends', 'members'],
        ],
    ];
    
    return Inertia::render('Reports/Custom/Builder', [
        'data_sources' => $dataSources,
        'templates' => $templates,
        'saved_reports' => $this->getSavedCustomReports(),
    ]);
}

public function generateCustom(Request $request)
{
    $validated = $request->validate([
        'report_name' => 'required|string|max:255',
        'data_sources' => 'required|array',
        'selected_fields' => 'required|array',
        'filters' => 'nullable|array',
        'group_by' => 'nullable|string',
        'sort_by' => 'nullable|string',
        'sort_direction' => 'nullable|in:asc,desc',
        'date_range' => 'nullable|array',
        'format' => 'nullable|in:table,chart,export',
        'save_report' => 'nullable|boolean',
    ]);
    
    try {
        // Build the query based on selected data sources and fields
        $query = $this->buildCustomQuery($validated);
        
        // Apply filters
        if (!empty($validated['filters'])) {
            $query = $this->applyCustomFilters($query, $validated['filters']);
        }
        
        // Apply date range
        if (!empty($validated['date_range'])) {
            $query = $this->applyDateRange($query, $validated['date_range']);
        }
        
        // Apply grouping
        if (!empty($validated['group_by'])) {
            $query = $this->applyGrouping($query, $validated['group_by']);
        }
        
        // Apply sorting
        if (!empty($validated['sort_by'])) {
            $direction = $validated['sort_direction'] ?? 'asc';
            $query = $query->orderBy($validated['sort_by'], $direction);
        }
        
        // Execute query
        $results = $query->get();
        
        // Process results based on format
        $processedResults = $this->processCustomResults($results, $validated);
        
        // Save report if requested
        if ($validated['save_report'] ?? false) {
            $this->saveCustomReport($validated, $processedResults);
        }
        
        return response()->json([
            'success' => true,
            'data' => $processedResults,
            'report_name' => $validated['report_name'],
            'generated_at' => Carbon::now()->toISOString(),
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Error generating custom report: ' . $e->getMessage(),
        ], 500);
    }
}

/**
 * Export Methods
 */
public function exportReport(Request $request)
{
    $reportType = $request->input('report_type');
    $format = $request->input('format', 'excel');
    $data = $request->input('data', []);
    
    switch ($format) {
        case 'pdf':
            return $this->exportToPDF($reportType, $data);
        case 'csv':
            return $this->exportToCSV($reportType, $data);
        case 'excel':
        default:
            return $this->exportToExcel($reportType, $data);
    }
}

/**
 * Helper Methods for Balance Sheet
 */
private function getCashAndEquivalents($asOf)
{
    return Account::where('account_type', 'cash')
        ->where('created_at', '<=', $asOf)
        ->sum('balance');
}

private function getLoansReceivable($asOf)
{
    return Loan::where('status', 'active')
        ->where('disbursement_date', '<=', $asOf)
        ->sum('outstanding_balance');
}

private function getOtherReceivables($asOf)
{
    return Transaction::where('transaction_type', 'receivable')
        ->where('created_at', '<=', $asOf)
        ->sum('amount');
}

private function getPropertyEquipment($asOf)
{
    return PaymentVoucher::where('voucher_type', 'asset_purchase')
        ->where('approved_at', '<=', $asOf)
        ->sum('amount');
}

private function getInvestments($asOf)
{
    return Account::where('account_type', 'investment')
        ->where('created_at', '<=', $asOf)
        ->sum('balance');
}

private function getMemberDeposits($asOf)
{
    return Account::whereIn('account_type', ['savings', 'shares'])
        ->where('created_at', '<=', $asOf)
        ->sum('balance');
}

private function getAccruedExpenses($asOf)
{
    return PaymentVoucher::where('status', 'approved')
        ->where('voucher_type', 'expense')
        ->where('approved_at', '<=', $asOf)
        ->whereNull('paid_at')
        ->sum('amount');
}

private function getOtherPayables($asOf)
{
    return Transaction::where('transaction_type', 'payable')
        ->where('created_at', '<=', $asOf)
        ->sum('amount');
}

private function getLongTermDebt($asOf)
{
    return Account::where('account_type', 'debt')
        ->where('created_at', '<=', $asOf)
        ->sum('balance');
}

private function getShareCapital($asOf)
{
    return Account::where('account_type', 'shares')
        ->where('created_at', '<=', $asOf)
        ->sum('balance');
}

private function getRetainedEarnings($asOf)
{
    $currentYear = Carbon::parse($asOf)->year;
    $netIncome = $this->getNetIncome(
        Carbon::createFromDate($currentYear, 1, 1),
        $asOf
    );
    
    return $netIncome;
}

private function getReserves($asOf)
{
    return Account::where('account_type', 'reserve')
        ->where('created_at', '<=', $asOf)
        ->sum('balance');
}

/**
 * Helper Methods for Income Statement
 */
private function getInterestIncome($start, $end)
{
    return LoanRepayment::whereBetween('payment_date', [$start, $end])
        ->sum('interest_amount');
}

private function getLoanFees($start, $end)
{
    return Transaction::where('transaction_type', 'fee')
        ->whereBetween('created_at', [$start, $end])
        ->sum('amount');
}

private function getServiceCharges($start, $end)
{
    return Transaction::where('transaction_type', 'service_charge')
        ->whereBetween('created_at', [$start, $end])
        ->sum('amount');
}

private function getOtherIncome($start, $end)
{
    return Transaction::where('transaction_type', 'other_income')
        ->whereBetween('created_at', [$start, $end])
        ->sum('amount');
}

private function getInterestExpense($start, $end)
{
    return Transaction::where('transaction_type', 'interest_expense')
        ->whereBetween('created_at', [$start, $end])
        ->sum('amount');
}

private function getStaffCosts($start, $end)
{
    return PaymentVoucher::where('voucher_type', 'staff_cost')
        ->whereBetween('approved_at', [$start, $end])
        ->sum('amount');
}

private function getAdministrativeExpenses($start, $end)
{
    return PaymentVoucher::where('voucher_type', 'administrative')
        ->whereBetween('approved_at', [$start, $end])
        ->sum('amount');
}

private function getLoanLossProvision($start, $end)
{
    return Transaction::where('transaction_type', 'loan_loss_provision')
        ->whereBetween('created_at', [$start, $end])
        ->sum('amount');
}

private function getOtherExpenses($start, $end)
{
    return PaymentVoucher::where('voucher_type', 'other_expense')
        ->whereBetween('approved_at', [$start, $end])
        ->sum('amount');
}

private function getNetIncome($start, $end)
{
    $revenue = $this->getInterestIncome($start, $end) + 
               $this->getLoanFees($start, $end) + 
               $this->getServiceCharges($start, $end) + 
               $this->getOtherIncome($start, $end);
    
    $expenses = $this->getInterestExpense($start, $end) + 
                $this->getStaffCosts($start, $end) + 
                $this->getAdministrativeExpenses($start, $end) + 
                $this->getLoanLossProvision($start, $end) + 
                $this->getOtherExpenses($start, $end);
    
    return $revenue - $expenses;
}

/**
 * Helper Methods for Cash Flow
 */
private function getLoanDisbursements($start, $end)
{
    return Loan::whereBetween('disbursement_date', [$start, $end])
        ->sum('disbursed_amount') * -1; // Negative for cash outflow
}

private function getLoanRepayments($start, $end)
{
    return LoanRepayment::whereBetween('payment_date', [$start, $end])
        ->sum('paid_amount');
}

private function getMemberDepositsFlow($start, $end)
{
    return Transaction::where('transaction_type', 'deposit')
        ->whereBetween('created_at', [$start, $end])
        ->sum('amount');
}

private function getMemberWithdrawalsFlow($start, $end)
{
    return Transaction::where('transaction_type', 'withdrawal')
        ->whereBetween('created_at', [$start, $end])
        ->sum('amount') * -1; // Negative for cash outflow
}

private function getEquipmentPurchases($start, $end)
{
    return PaymentVoucher::where('voucher_type', 'equipment')
        ->whereBetween('approved_at', [$start, $end])
        ->sum('amount') * -1; // Negative for cash outflow
}

private function getInvestmentFlow($start, $end)
{
    return Transaction::where('transaction_type', 'investment')
        ->whereBetween('created_at', [$start, $end])
        ->sum('amount') * -1; // Negative for cash outflow
}

private function getShareCapitalFlow($start, $end)
{
    return Transaction::where('transaction_type', 'share_capital')
        ->whereBetween('created_at', [$start, $end])
        ->sum('amount');
}

private function getDividendPayments($start, $end)
{
    return MemberDividend::whereBetween('paid_at', [$start, $end])
        ->sum('amount') * -1; // Negative for cash outflow
}

/**
 * Helper Methods for Trial Balance
 */
private function getTrialBalanceAccounts($asOf)
{
    // This is a simplified version - in a real implementation,
    // you would have a proper chart of accounts
    return collect([
        ['account_name' => 'Cash', 'debit' => $this->getCashAndEquivalents($asOf), 'credit' => 0],
        ['account_name' => 'Loans Receivable', 'debit' => $this->getLoansReceivable($asOf), 'credit' => 0],
        ['account_name' => 'Member Deposits', 'debit' => 0, 'credit' => $this->getMemberDeposits($asOf)],
        ['account_name' => 'Share Capital', 'debit' => 0, 'credit' => $this->getShareCapital($asOf)],
        ['account_name' => 'Retained Earnings', 'debit' => 0, 'credit' => $this->getRetainedEarnings($asOf)],
    ]);
}

/**
 * Additional Helper Methods
 */
private function calculatePortfolioAtRiskRatio()
{
    $totalOutstanding = Loan::where('status', 'active')->sum('outstanding_balance');
    $portfolioAtRisk = Loan::where('status', 'active')
        ->where('days_in_arrears', '>', 0)
        ->sum('outstanding_balance');
    
    return $totalOutstanding > 0 ? ($portfolioAtRisk / $totalOutstanding) * 100 : 0;
}

private function calculateCollectionRate($startDate, $endDate)
{
    $expectedAmount = LoanRepayment::whereBetween('due_date', [$startDate, $endDate])
        ->sum('expected_amount');
    
    $collectedAmount = LoanRepayment::whereBetween('payment_date', [$startDate, $endDate])
        ->sum('paid_amount');
    
    return $expectedAmount > 0 ? ($collectedAmount / $expectedAmount) * 100 : 0;
}

private function calculateTransactionGrowthRate($year)
{
    $currentYear = Transaction::whereYear('created_at', $year)->sum('amount');
    $previousYear = Transaction::whereYear('created_at', $year - 1)->sum('amount');
    
    return $previousYear > 0 ? (($currentYear - $previousYear) / $previousYear) * 100 : 0;
}

// Additional helper methods for regulatory and custom reports would go here...
private function getReportingPeriods($year, $quarter)
{
    // Implementation for reporting periods
    return [];
}

private function generateMembershipReport($periods)
{
    // Implementation for membership report
    return [];
}

private function generateFinancialPerformanceReport($periods)
{
    // Implementation for financial performance report
    return [];
}

private function generateLoanPortfolioReport($periods)
{
    // Implementation for loan portfolio report
    return [];
}

private function generateLiquidityReport($periods)
{
    // Implementation for liquidity report
    return [];
}

private function generateCapitalAdequacyReport($periods)
{
    // Implementation for capital adequacy report
    return [];
}

private function generateGovernanceReport($periods)
{
    // Implementation for governance report
    return [];
}

private function checkKYCCompliance($start, $end)
{
    // Implementation for KYC compliance check
    return [];
}

private function checkAMLCompliance($start, $end)
{
    // Implementation for AML compliance check
    return [];
}

private function calculateLiquidityRatios($start, $end)
{
    // Implementation for liquidity ratios
    return [];
}

private function calculateCapitalRatios($start, $end)
{
    // Implementation for capital ratios
    return [];
}

private function checkLendingLimits($start, $end)
{
    // Implementation for lending limits check
    return [];
}

private function checkReserveRequirements($start, $end)
{
    // Implementation for reserve requirements check
    return [];
}

private function getRegulatorySubmissions($start, $end)
{
    // Implementation for regulatory submissions
    return [];
}

private function calculateComplianceScore($compliance)
{
    // Implementation for compliance score calculation
    return 0;
}

private function getSavedCustomReports()
{
    // Implementation to get saved custom reports
    return [];
}

private function buildCustomQuery($validated)
{
    // Implementation to build custom query
    return collect();
}

private function applyCustomFilters($query, $filters)
{
    // Implementation to apply custom filters
    return $query;
}

private function applyDateRange($query, $dateRange)
{
    // Implementation to apply date range
    return $query;
}

private function applyGrouping($query, $groupBy)
{
    // Implementation to apply grouping
    return $query;
}

private function processCustomResults($results, $validated)
{
    // Implementation to process custom results
    return $results;
}

private function saveCustomReport($validated, $results)
{
    // Implementation to save custom report
    return true;
}

private function exportToPDF($reportType, $data)
{
    // Implementation for PDF export
    return Response::make('', 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="report.pdf"'
    ]);
}

private function exportToCSV($reportType, $data)
{
    // Implementation for CSV export
    return Response::make('', 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="report.csv"'
    ]);
}

private function exportToExcel($reportType, $data)
{
    // Implementation for Excel export
    return Response::make('', 200, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Content-Disposition' => 'attachment; filename="report.xlsx"'
    ]);
}
}

