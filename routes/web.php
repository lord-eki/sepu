<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use Illuminate\Http\Request;
use App\Http\Controllers\{
    DashboardController,
    MemberController,
    LoanController,
    LoanCalculatorController,
    DividendController,
    AccountController,
    TransactionController,
    PaymentVoucherController,
    BudgetController,
    NotificationController,
    ReportController,
    SettingsController,
    MpesaController
};

// Public routes
Route::get('/', fn() => Inertia::render('Welcome'))->name('home');

// Authentication routes
require __DIR__ . '/auth.php';

Route::get('/email/verify', function (Request $request) {
    return Inertia::render('auth/VerifyEmail', [
        'status' => session('status'), 
    ]);
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/dashboard');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');


// Loan Calculator
Route::get('/loan-calculator', [LoanCalculatorController::class, 'index'])->name('loan-calculator.index');
Route::post('/loan-calculator/calculate', [LoanCalculatorController::class, 'calculate'])->name('loan-calculator.calculate');

// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {


 
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

  // MEMBERS ROUTES
    
      // Member info display/update
    Route::get('/member/profile', [MemberController::class, 'profile'])->name('member.profile');
    Route::put('/member/profile', [MemberController::class, 'updateProfile'])->name('member.updateProfile');
    Route::post('/addmember', [MemberController::class, 'store'])->name('addmember.store');


    Route::prefix('members')->name('members.')->group(function () {
        Route::get('/', [MemberController::class, 'index'])->name('index');
        Route::get('/create', [MemberController::class, 'create'])->name('create');
        Route::post('/', [MemberController::class, 'store'])->name('store');
        Route::get('/{member}', [MemberController::class, 'show'])->name('show');
        Route::get('/{member}/edit', [MemberController::class, 'edit'])->name('edit');
        Route::put('/{member}', [MemberController::class, 'update'])->name('update');
        Route::delete('/{member}', [MemberController::class, 'destroy'])->name('destroy');

        // Member-specific actions
        Route::post('/{member}/activate', [MemberController::class, 'activate'])->name('activate');
        Route::post('/{member}/deactivate', [MemberController::class, 'deactivate'])->name('deactivate');
        Route::post('/{member}/suspend', [MemberController::class, 'suspend'])->name('suspend');
        Route::get('/{member}/accounts', [MemberController::class, 'accounts'])->name('accounts');
        Route::get('/{member}/transactions', [MemberController::class, 'transactions'])->name('transactions');
        Route::get('/{member}/loans', [MemberController::class, 'loans'])->name('loans');
        Route::get('/{member}/dividends', [MemberController::class, 'dividends'])->name('dividends');
        Route::get('/{member}/guarantees', [MemberController::class, 'guarantees'])->name('guarantees');

        // Next of kin management
        Route::get('/{member}/next-of-kin', [MemberController::class, 'nextOfKin'])->name('next-of-kin');
        Route::post('/{member}/next-of-kin', [MemberController::class, 'storeNextOfKin'])->name('store-next-of-kin');
        Route::put('/{member}/next-of-kin/{nextOfKin}', [MemberController::class, 'updateNextOfKin'])->name('update-next-of-kin');
        Route::delete('/{member}/next-of-kin/{nextOfKin}', [MemberController::class, 'destroyNextOfKin'])->name('destroy-next-of-kin');

        // Document uploads
        Route::post('/{member}/documents', [MemberController::class, 'uploadDocuments'])->name('upload-documents');
        Route::delete('/{member}/documents/{document}', [MemberController::class, 'deleteDocument'])->name('delete-document');

        // Bulk operations
        Route::post('/bulk-import', [MemberController::class, 'bulkImport'])->name('bulk-import');
        Route::post('/bulk-export', [MemberController::class, 'bulkExport'])->name('bulk-export');


    }); 

     // ACCOUNTS & SHARES ROUTES
     Route::prefix('accounts')->name('accounts.')->group(function () {
        // Basic account operations - Accountant, Admin, Management
        Route::middleware('role:accountant,admin,management')->group(function () {
            Route::get('/', [AccountController::class, 'index'])->name('index');
            Route::get('/create', [AccountController::class, 'create'])->name('create');
            Route::post('/', [AccountController::class, 'store'])->name('store');
            Route::get('/{account}', [AccountController::class, 'show'])->name('show');
            Route::get('/{account}/edit', [AccountController::class, 'edit'])->name('edit');
            Route::put('/{account}', [AccountController::class, 'update'])->name('update');
            Route::delete('/{account}', [AccountController::class, 'destroy'])->middleware('role:admin')->name('destroy');
        });

        // Account operations - Accountant and above
        Route::middleware('role:accountant,admin,management')->group(function () {
            Route::post('/{account}/activate', [AccountController::class, 'activate'])->name('activate');
            Route::post('/{account}/deactivate', [AccountController::class, 'deactivate'])->name('deactivate');
            Route::get('/{account}/transactions', [AccountController::class, 'transactions'])->name('transactions');
            Route::get('/{account}/statement', [AccountController::class, 'statement'])->name('statement');

            // Deposit and Withdrawal operations
            Route::get('/{account}/deposit', [AccountController::class, 'showDeposit'])->name('deposit.show');
            Route::post('/{account}/deposit', [AccountController::class, 'deposit'])->name('deposit');
            Route::get('/{account}/withdrawal', [AccountController::class, 'showWithdrawal'])->name('withdrawal.show');
            Route::post('/{account}/withdrawal', [AccountController::class, 'withdrawal'])->name('withdrawal');

            // Share operations
            Route::get('/{account}/share-transfer', [AccountController::class, 'showShareTransfer'])->name('share-transfer.show');
            Route::post('/{account}/share-transfer', [AccountController::class, 'shareTransfer'])->name('share-transfer');
        });

        // Shares management - Management and Admin
        Route::middleware('role:admin,management')->group(function () {
            Route::get('/shares/summary', [AccountController::class, 'sharesSummary'])->name('shares.summary');
            Route::get('/shares/register', [AccountController::class, 'sharesRegister'])->name('shares.register');
            Route::post('/shares/transfer', [AccountController::class, 'transferShares'])->name('shares.transfer');
        });

        // Member account access - Members can view their own accounts
        // Route::middleware('role:member')->group(function () {
        //     Route::get('/my-accounts', function () {
        //         $member = auth()->user()->member;
        //         return redirect()->route('members.accounts', $member);
        //     })->name('my-accounts');
        // });
    });

   // LOANS ROUTES
    Route::prefix('loans')->name('loans.')->group(function () {

        Route::get('/', [LoanController::class, 'index'])->name('index');
        Route::get('/create', [LoanController::class, 'create'])->name('create');
        Route::post('/', [LoanController::class, 'store'])->name('store');
        Route::get('/{loan}', [LoanController::class, 'show'])->name('show');
        Route::get('/{loan}/edit', [LoanController::class, 'edit'])->name('edit');
        Route::put('/{loan}', [LoanController::class, 'update'])->name('update');
        Route::delete('/{loan}', [LoanController::class, 'destroy'])->name('destroy');
        
        // Loan application workflow
        Route::post('/{loan}/submit', [LoanController::class, 'submit'])->name('submit');
        Route::post('/{loan}/review', [LoanController::class, 'review'])->name('review');
        Route::post('/{loan}/approve', [LoanController::class, 'approve'])->name('approve');
        Route::post('/{loan}/reject', [LoanController::class, 'reject'])->name('reject');
        Route::post('/{loan}/disburse', [LoanController::class, 'disburse'])->name('disburse');

        // Loan management
        Route::get('/{loan}/schedule', [LoanController::class, 'schedule'])->name('schedule');
        Route::get('/{loan}/repayments', [LoanController::class, 'repayments'])->name('repayments');
        Route::post('/{loan}/repayment', [LoanController::class, 'recordRepayment'])->name('record-repayment');
        Route::get('/{loan}/statement', [LoanController::class, 'statement'])->name('statement');

        // Guarantors
        Route::get('/{loan}/guarantors', [LoanController::class, 'guarantors'])->name('guarantors');
        Route::post('/{loan}/guarantors', [LoanController::class, 'addGuarantor'])->name('add-guarantor');
        Route::delete('/{loan}/guarantors/{guarantor}', [LoanController::class, 'removeGuarantor'])->name('remove-guarantor');
        Route::post('/{loan}/guarantors/{guarantor}/accept', [LoanController::class, 'acceptGuarantee'])->name('accept-guarantee');
        Route::post('/{loan}/guarantors/{guarantor}/reject', [LoanController::class, 'rejectGuarantee'])->name('reject-guarantee');

        // Loan restructuring
        Route::post('/{loan}/restructure', [LoanController::class, 'restructure'])->name('restructure');
        Route::post('/{loan}/write-off', [LoanController::class, 'writeOff'])->name('write-off');

        // Loan products
        Route::get('/products', [LoanController::class, 'products'])->name('products');
        Route::get('/products/create', [LoanController::class, 'createProduct'])->name('products.create');
        Route::post('/products', [LoanController::class, 'storeProduct'])->name('products.store');
        Route::get('/products/{product}', [LoanController::class, 'showProduct'])->name('products.show');
        Route::get('/products/{product}/edit', [LoanController::class, 'editProduct'])->name('products.edit');
        Route::put('/products/{product}', [LoanController::class, 'updateProduct'])->name('products.update');
        Route::delete('/products/{product}', [LoanController::class, 'destroyProduct'])->name('products.destroy');

        // Loan analytics
        Route::get('/analytics/portfolio', [LoanController::class, 'portfolio'])->name('analytics.portfolio');
        Route::get('/analytics/arrears', [LoanController::class, 'arrears'])->name('analytics.arrears');
        Route::get('/analytics/performance', [LoanController::class, 'performance'])->name('analytics.performance');
    });

  // DIVIDENDS ROUTES
    Route::prefix('dividends')->name('dividends.')->group(function () {
        Route::get('/', [DividendController::class, 'index'])->name('index');
        Route::get('/create', [DividendController::class, 'create'])->name('create');
        Route::post('/', [DividendController::class, 'store'])->name('store');
        Route::get('/{dividend}', [DividendController::class, 'show'])->name('show');
        Route::get('/{dividend}/edit', [DividendController::class, 'edit'])->name('edit');
        Route::put('/{dividend}', [DividendController::class, 'update'])->name('update');
        Route::delete('/{dividend}', [DividendController::class, 'destroy'])->name('destroy');
        
        // Dividend calculation and distribution
        Route::post('/calculate/{year}', [DividendController::class, 'calculate'])->name('calculate');
        Route::post('/{dividend}/approve', [DividendController::class, 'approve'])->name('approve');
        Route::post('/{dividend}/distribute', [DividendController::class, 'distribute'])->name('distribute');
        Route::post('/{dividend}/reverse', [DividendController::class, 'reverse'])->name('reverse');
        
        // Member dividend details
        Route::get('/{dividend}/members', [DividendController::class, 'members'])->name('members');
        Route::get('/{dividend}/members/{member}', [DividendController::class, 'memberDetails'])->name('member-details');
        Route::post('/{dividend}/members/{member}/pay', [DividendController::class, 'payMemberDividend'])->name('pay-member');
        
        // Dividend reports
        Route::get('/{dividend}/report', [DividendController::class, 'report'])->name('report');
        Route::get('/analytics/history', [DividendController::class, 'history'])->name('analytics.history');
        Route::get('/analytics/projections', [DividendController::class, 'projections'])->name('analytics.projections');
    });

    // BUDGET & FINANCIAL MANAGEMENT ROUTES
    Route::prefix('budgets')->name('budgets.')->group(function () {
        Route::get('/', [BudgetController::class, 'index'])->name('index');
        Route::get('/create', [BudgetController::class, 'create'])->name('create');
        Route::post('/', [BudgetController::class, 'store'])->name('store');
        Route::get('/{budget}', [BudgetController::class, 'show'])->name('show');
        Route::get('/{budget}/edit', [BudgetController::class, 'edit'])->name('edit');
        Route::put('/{budget}', [BudgetController::class, 'update'])->name('update');
        Route::delete('/{budget}', [BudgetController::class, 'destroy'])->name('destroy');
        
        // Budget approval
        Route::post('/{budget}/approve', [BudgetController::class, 'approve'])->name('approve');
        Route::post('/{budget}/activate', [BudgetController::class, 'activate'])->name('activate');
        Route::post('/{budget}/close', [BudgetController::class, 'close'])->name('close');
        
        // Budget items
        Route::get('/{budget}/items', [BudgetController::class, 'items'])->name('items');
        Route::post('/{budget}/items', [BudgetController::class, 'storeItem'])->name('store-item');
        Route::put('/{budget}/items/{item}', [BudgetController::class, 'updateItem'])->name('update-item');
        Route::delete('/{budget}/items/{item}', [BudgetController::class, 'destroyItem'])->name('destroy-item');
        
        // Budget monitoring
        Route::get('/{budget}/variance', [BudgetController::class, 'variance'])->name('variance');
        Route::get('/{budget}/utilization', [BudgetController::class, 'utilization'])->name('utilization');
    });

    // PAYMENT VOUCHERS ROUTES
    Route::prefix('vouchers')->name('vouchers.')->group(function () {
        Route::get('/', [PaymentVoucherController::class, 'index'])->name('index');
        Route::get('/create', [PaymentVoucherController::class, 'create'])->name('create');
        Route::post('/', [PaymentVoucherController::class, 'store'])->name('store');
        Route::get('/{voucher}', [PaymentVoucherController::class, 'show'])->name('show');
        Route::get('/{voucher}/edit', [PaymentVoucherController::class, 'edit'])->name('edit');
        Route::put('/{voucher}', [PaymentVoucherController::class, 'update'])->name('update');
        Route::delete('/{voucher}', [PaymentVoucherController::class, 'destroy'])->name('destroy');
        
        // Voucher workflow
        Route::post('/{voucher}/submit', [PaymentVoucherController::class, 'submit'])->name('submit');
        Route::post('/{voucher}/approve', [PaymentVoucherController::class, 'approve'])->name('approve');
        Route::post('/{voucher}/reject', [PaymentVoucherController::class, 'reject'])->name('reject');
        Route::post('/{voucher}/pay', [PaymentVoucherController::class, 'pay'])->name('pay');
        Route::post('/{voucher}/cancel', [PaymentVoucherController::class, 'cancel'])->name('cancel');
        
        // Voucher documents
        Route::post('/{voucher}/documents', [PaymentVoucherController::class, 'uploadDocuments'])->name('upload-documents');
        Route::delete('/{voucher}/documents/{document}', [PaymentVoucherController::class, 'deleteDocument'])->name('delete-document');
        
        // Voucher reports
        Route::get('/reports/pending', [PaymentVoucherController::class, 'pendingReport'])->name('reports.pending');
        Route::get('/reports/approved', [PaymentVoucherController::class, 'approvedReport'])->name('reports.approved');
        Route::get('/reports/paid', [PaymentVoucherController::class, 'paidReport'])->name('reports.paid');
    });

    // REPORTS ROUTES
    Route::prefix('reports')->name('reports.')->group(function () {
        // Financial reports
        Route::get('/financial/balance-sheet', [ReportController::class, 'balanceSheet'])->name('financial.balance-sheet');
        Route::get('/financial/income-statement', [ReportController::class, 'incomeStatement'])->name('financial.income-statement');
        Route::get('/financial/cash-flow', [ReportController::class, 'cashFlow'])->name('financial.cash-flow');
        Route::get('/financial/trial-balance', [ReportController::class, 'trialBalance'])->name('financial.trial-balance');
        
        // Member reports
        Route::get('/members/register', [ReportController::class, 'memberRegister'])->name('members.register');
        Route::get('/members/shares', [ReportController::class, 'memberShares'])->name('members.shares');
        Route::get('/members/savings', [ReportController::class, 'memberSavings'])->name('members.savings');
        Route::get('/members/loans', [ReportController::class, 'memberLoans'])->name('members.loans');
        
        // Loan reports
        Route::get('/loans/portfolio', [ReportController::class, 'loanPortfolio'])->name('loans.portfolio');
        Route::get('/loans/arrears', [ReportController::class, 'loanArrears'])->name('loans.arrears');
        Route::get('/loans/disbursement', [ReportController::class, 'loanDisbursement'])->name('loans.disbursement');
        Route::get('/loans/collection', [ReportController::class, 'loanCollection'])->name('loans.collection');
        
        // Transaction reports
        Route::get('/transactions/daily', [ReportController::class, 'dailyTransactions'])->name('transactions.daily');
        Route::get('/transactions/monthly', [ReportController::class, 'monthlyTransactions'])->name('transactions.monthly');
        Route::get('/transactions/annual', [ReportController::class, 'annualTransactions'])->name('transactions.annual');
        
        // Regulatory reports
        Route::get('/regulatory/statutory', [ReportController::class, 'statutoryReports'])->name('regulatory.statutory');
        Route::get('/regulatory/compliance', [ReportController::class, 'complianceReports'])->name('regulatory.compliance');
        
        // Custom reports
        Route::get('/custom/builder', [ReportController::class, 'customBuilder'])->name('custom.builder');
        Route::post('/custom/generate', [ReportController::class, 'generateCustom'])->name('custom.generate');
    });

    // NOTIFICATIONS ROUTES
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/{notification}', [NotificationController::class, 'show'])->name('show');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        
        // Notification settings
        Route::get('/settings', [NotificationController::class, 'settings'])->name('settings');
        Route::post('/settings', [NotificationController::class, 'updateSettings'])->name('update-settings');
        
        // Bulk notifications
        Route::get('/bulk/create', [NotificationController::class, 'createBulk'])->name('bulk.create');
        Route::post('/bulk/send', [NotificationController::class, 'sendBulk'])->name('bulk.send');
    });

    // SETTINGS ROUTES
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::get('/general', [SettingsController::class, 'general'])->name('general');
        Route::post('/general', [SettingsController::class, 'updateGeneral'])->name('update-general');
        
        Route::get('/financial', [SettingsController::class, 'financial'])->name('financial');
        Route::post('/financial', [SettingsController::class, 'updateFinancial'])->name('update-financial');
        
        Route::get('/loan', [SettingsController::class, 'loan'])->name('loan');
        Route::post('/loan', [SettingsController::class, 'updateLoan'])->name('update-loan');
        
        Route::get('/notification', [SettingsController::class, 'notification'])->name('notification');
        Route::post('/notification', [SettingsController::class, 'updateNotification'])->name('update-notification');
        
        Route::get('/security', [SettingsController::class, 'security'])->name('security');
        Route::post('/security', [SettingsController::class, 'updateSecurity'])->name('update-security');
        
        Route::get('/backup', [SettingsController::class, 'backup'])->name('backup');
        Route::post('/backup/create', [SettingsController::class, 'createBackup'])->name('create-backup');
        Route::post('/backup/restore', [SettingsController::class, 'restoreBackup'])->name('restore-backup');
    });

    // API ROUTES (for AJAX requests)
    Route::prefix('api')->name('api.')->group(function () {
        // Search endpoints
        Route::get('/search/members', [MemberController::class, 'searchMembers'])->name('search.members');
        Route::get('/search/accounts', [AccountController::class, 'searchAccounts'])->name('search.accounts');
        Route::get('/search/loans', [LoanController::class, 'searchLoans'])->name('search.loans');
        
        // Quick stats
        Route::get('/stats/dashboard', [ReportController::class, 'dashboardStats'])->name('stats.dashboard');
        Route::get('/stats/members', [MemberController::class, 'memberStats'])->name('stats.members');
        Route::get('/stats/loans', [LoanController::class, 'loanStats'])->name('stats.loans');
        Route::get('/stats/transactions', [TransactionController::class, 'transactionStats'])->name('stats.transactions');
        
        // Validation endpoints
        Route::post('/validate/member-id', [MemberController::class, 'validateMemberId'])->name('validate.member-id');
        Route::post('/validate/account-number', [AccountController::class, 'validateAccountNumber'])->name('validate.account-number');
        Route::post('/validate/loan-eligibility', [LoanController::class, 'validateLoanEligibility'])->name('validate.loan-eligibility');
        
        // Calculation endpoints
        Route::post('/calculate/loan-schedule', [LoanController::class, 'calculateLoanSchedule'])->name('calculate.loan-schedule');
        Route::post('/calculate/dividend-projection', [DividendController::class, 'calculateDividendProjection'])->name('calculate.dividend-projection');
        
        // Export endpoints
        Route::get('/export/members', [MemberController::class, 'exportMembers'])->name('export.members');
        Route::get('/export/transactions', [TransactionController::class, 'exportTransactions'])->name('export.transactions');
        Route::get('/export/loans', [LoanController::class, 'exportLoans'])->name('export.loans');
        Route::get('/export/dividends/{dividend}', [DividendController::class, 'exportDividend'])->name('export.dividend');
    });
});

// Include additional route files
require __DIR__.'/settings.php';


    // Member profile routes - Members can access their own profile
    Route::prefix('profile')->name('profile.')->middleware('role:member')->group(function () {
        Route::get('/', function () {
            $member = auth()->user()->member;
            return redirect()->route('members.show', $member);
        })->name('index');

        Route::get('/edit', function () {
            $member = auth()->user()->member;
            return redirect()->route('members.edit', $member);
        })->name('edit');
    });


    // Member loan access
    Route::middleware('role:member')->group(function () {
        Route::get('/my-loans', function () {
            $member = auth()->user()->member;
            return redirect()->route('members.loans', $member);
        })->name('my-loans');
    });


    Route::middleware('role:member')->group(function () {
        Route::get('/my-accounts', function () {
            $member = auth()->user()->member;
            return redirect()->route('members.accounts', $member);
        })->name('my-accounts');
    });


    // Member dividend access
    Route::middleware('role:member')->group(function () {
        Route::get('/my-dividends', function () {
            $member = auth()->user()->member;
            return redirect()->route('members.dividends', $member);
        })->name('my-dividends');
    });

    // Member transaction access
    Route::middleware('role:member')->group(function () {
        Route::get('/my-transactions', function () {
            $member = auth()->user()->member;
            return redirect()->route('members.transactions', $member);
        })->name('my-transactions');
    });


    Route::middleware(['auth', 'role:member'])->group(function () {
        Route::get('/{member}/my-accounts/{account}/statement', [AccountController::class, 'myStatement'])
            ->name('my-accounts.statement');
    });
    Route::get('/my-accounts/{account}/statement/pdf', [AccountController::class, 'statementPdf'])
    ->name('my-accounts.statement.pdf');



    Route::middleware('role:member')->prefix('members')->name('members.')->group(function () {
        Route::get('/{member}/accounts/{account}/deposit', [MemberController::class, 'showDeposit'])->name('accounts.deposit.show');
        Route::post('/{member}/accounts/{account}/deposit', [MemberController::class, 'deposit'])->name('accounts.deposit');
    });

    Route::get('/members/{member}/accounts/{account}/withdrawal', [MemberController::class, 'showWithdrawal'])
    ->name('members.accounts.withdrawal.show');

    Route::post('/members/{member}/accounts/{account}/withdrawal', [MemberController::class, 'withdrawal'])
    ->name('members.accounts.withdrawal');

    Route::middleware(['auth'])->group(function () {
    Route::get('/profile/complete', [ProfileController::class, 'complete'])
        ->name('profile.complete');

    Route::post('/profile/complete', [ProfileController::class, 'store'])
        ->name('profile.complete.store');
});

Route::get('/awaiting-activation', function () {
    return Inertia::render('Profile/AwaitingActivation');
})->name('awaiting-activation');

Route::get('/about', fn() => Inertia::render('AboutUs'))->name('about');
Route::get('/terms', fn() => Inertia::render('Terms'))->name('terms');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile/show', [ProfileController::class, 'show'])->name('member.profile');
    Route::put('/profile/show', [ProfileController::class, 'updateProfile'])->name('member.updateProfile');
});

Route::post('/member/profile/photo', [ProfileController::class, 'updatePhoto'])
    ->name('member.updatePhoto');
