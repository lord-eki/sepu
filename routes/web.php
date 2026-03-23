<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\RoleSwitchController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DividendController;
use App\Http\Controllers\LoanCalculatorController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanProductController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberDepositCommitmentController; 
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentVoucherController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SystemUserController;
use App\Http\Controllers\TransactionController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ─────────────────────────────────────────────────────────────────────────────
// PUBLIC ROUTES
// ─────────────────────────────────────────────────────────────────────────────

Route::get('/', fn () => Inertia::render('Welcome'))->name('home');

require __DIR__.'/auth.php';

Route::get('/email/verify', function (Request $request) {
    return Inertia::render('auth/VerifyEmail', ['status' => session('status')]);
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/dashboard');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

// Loan Calculator (public)
Route::get('/loan-calculator', [LoanCalculatorController::class, 'index'])->name('loan-calculator.index');
Route::post('/loan-calculator/calculate', [LoanCalculatorController::class, 'calculate'])->name('loan-calculator.calculate');

// Static pages
Route::get('/about',               fn () => Inertia::render('AboutUs'))->name('about');
Route::get('/terms',               fn () => Inertia::render('Terms'))->name('terms');
Route::get('/contact',             fn () => Inertia::render('Contact'))->name('contact');
Route::get('/awaiting-activation', fn () => Inertia::render('Profile/AwaitingActivation'))->name('awaiting-activation');
Route::get('/awaiting-payment',    fn () => Inertia::render('Profile/AwaitingPayment'))->name('awaiting-payment');

// ─────────────────────────────────────────────────────────────────────────────
// PROTECTED ROUTES  (auth + verified)
// ─────────────────────────────────────────────────────────────────────────────

Route::middleware(['auth', 'verified'])->group(function () {

    // ── Dashboard ─────────────────────────────────────────────────────────
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Member profile (staff shortcut) ───────────────────────────────────
    Route::get('/member/profile', [MemberController::class, 'profile'])->name('member.profile');
    Route::put('/member/profile', [MemberController::class, 'updateProfile'])->name('member.updateProfile');
    Route::post('/addmember',     [MemberController::class, 'store'])->name('addmember.store');

    // ── Members ───────────────────────────────────────────────────────────
    Route::prefix('members')->name('members.')->group(function () {

        // List / Create
        Route::get('/',       [MemberController::class, 'index'])->name('index');
        Route::get('/create', [MemberController::class, 'create'])->name('create');
        Route::post('/',      [MemberController::class, 'store'])->name('store');

        // ── Static-segment routes MUST come before /{member} wildcard ──────

        // Import / Export
        Route::get('/import',                   [MemberController::class, 'showImportForm'])->name('import.form');
        Route::post('/import',                  [MemberController::class, 'bulkImport'])->name('import');
        Route::get('/import/template',          [MemberController::class, 'downloadTemplate'])->name('import.template');
        Route::get('/export',                   [MemberController::class, 'exportMembers'])->name('export');

        // Deposits bulk import
        Route::get('/deposits/import',          [MemberController::class, 'showDepositsImportForm'])->name('deposits.import.form');
        Route::post('/deposits/import',         [MemberController::class, 'importDeposits'])->name('deposits.import');
        Route::get('/deposits/import/template', [MemberController::class, 'downloadDepositsTemplate'])->name('deposits.import.template');

        // Non-member-bound POST actions (no {member} param)
        Route::post('/confirm-payment',  [MemberController::class, 'confirmPayment'])->name('confirm-payment');
        Route::post('/check-activation', [MemberController::class, 'checkActivation'])->name('check-activation');

        // ── Wildcard {member} routes ────────────────────────────────────────

        // CRUD
        Route::get('/{member}',      [MemberController::class, 'show'])->name('show');
        Route::get('/{member}/edit', [MemberController::class, 'edit'])->name('edit');
        Route::put('/{member}',      [MemberController::class, 'update'])->name('update');
        Route::delete('/{member}',   [MemberController::class, 'destroy'])->name('destroy');

        // Status actions
        Route::post('/{member}/activate',   [MemberController::class, 'activate'])->name('activate');
        Route::post('/{member}/deactivate', [MemberController::class, 'deactivate'])->name('deactivate');
        Route::post('/{member}/approve',    [MemberController::class, 'approve'])->name('approve');
        Route::post('/{member}/reject',     [MemberController::class, 'reject'])->name('reject');
        Route::post('/{member}/suspend',    [MemberController::class, 'suspend'])->name('suspend');

        // Related data
        Route::get('/{member}/accounts',     [MemberController::class, 'accounts'])->name('accounts');
        Route::get('/{member}/transactions', [MemberController::class, 'transactions'])->name('transactions');
        Route::get('/{member}/loans',        [MemberController::class, 'loans'])->name('loans');
        Route::get('/{member}/dividends',    [MemberController::class, 'dividends'])->name('dividends');
        Route::get('/{member}/guarantees',   [MemberController::class, 'guarantees'])->name('guarantees');

        // Next of kin
        Route::get('/{member}/next-of-kin',                    [MemberController::class, 'nextOfKin'])->name('next-of-kin');
        Route::post('/{member}/next-of-kin',                   [MemberController::class, 'storeNextOfKin'])->name('store-next-of-kin');
        Route::put('/{member}/next-of-kin/{nextOfKin}',        [MemberController::class, 'updateNextOfKin'])->name('update-next-of-kin');
        Route::delete('/{member}/next-of-kin/{nextOfKin}',     [MemberController::class, 'destroyNextOfKin'])->name('destroy-next-of-kin');

        // Documents
        Route::post('/{member}/documents',                     [MemberController::class, 'uploadDocuments'])->name('upload-documents');
        Route::delete('/{member}/documents/{document}',        [MemberController::class, 'deleteDocument'])->name('delete-document');

        // ── Monthly Deposit Commitments (NEW) ───────────────────────────────
        // Defines how much each member commits to deposit per month per
        // account type.  The schedule engine reads these rows at runtime
        // instead of using a hard-coded fixed amount.
        //
        // GET    /members/{member}/deposit-commitments                        → list
        // POST   /members/{member}/deposit-commitments                        → create
        // PUT    /members/{member}/deposit-commitments/{commitment}            → update amount / dates
        // DELETE /members/{member}/deposit-commitments/{commitment}            → delete
        // PATCH  /members/{member}/deposit-commitments/{commitment}/toggle     → activate / deactivate
        Route::get('/{member}/deposit-commitments',
            [MemberDepositCommitmentController::class, 'index'])->name('deposit-commitments.index');

        Route::post('/{member}/deposit-commitments',
            [MemberDepositCommitmentController::class, 'store'])->name('deposit-commitments.store');

        Route::put('/{member}/deposit-commitments/{commitment}',
            [MemberDepositCommitmentController::class, 'update'])->name('deposit-commitments.update');

        Route::delete('/{member}/deposit-commitments/{commitment}',
            [MemberDepositCommitmentController::class, 'destroy'])->name('deposit-commitments.destroy');

        Route::patch('/{member}/deposit-commitments/{commitment}/toggle',
            [MemberDepositCommitmentController::class, 'toggle'])->name('deposit-commitments.toggle');
    });

    // Loan eligibility — outside prefix to avoid route-model-binding conflict
    Route::post('members/loans/check-eligibility', [LoanController::class, 'checkEligibility'])
        ->name('members.loans.check-eligibility');
    Route::get('/members/{member}/loan-eligibility', [MemberController::class, 'loanEligibility'])
        ->name('members.loan-eligibility');

    // ── System Users (Admin only) ─────────────────────────────────────────
    Route::middleware('role:admin')->prefix('system-users')->name('system-users.')->group(function () {
        Route::get('/',                               [SystemUserController::class, 'index'])->name('index');
        Route::get('/create',                         [SystemUserController::class, 'create'])->name('create');
        Route::post('/',                              [SystemUserController::class, 'store'])->name('store');
        Route::get('/{systemUser}',                   [SystemUserController::class, 'show'])->name('show');
        Route::get('/{systemUser}/edit',              [SystemUserController::class, 'edit'])->name('edit');
        Route::put('/{systemUser}',                   [SystemUserController::class, 'update'])->name('update');
        Route::delete('/{systemUser}',                [SystemUserController::class, 'destroy'])->name('destroy');
        Route::patch('/{systemUser}/toggle-status',   [SystemUserController::class, 'toggleStatus'])->name('toggle-status');
        Route::patch('/{systemUser}/update-password', [SystemUserController::class, 'updatePassword'])->name('update-password');
        Route::get('/roles/manage',                   [SystemUserController::class, 'roles'])->name('roles');
        Route::post('/bulk-action',                   [SystemUserController::class, 'bulkAction'])->name('bulk-action');
    });

    // ── Accounts & Shares ─────────────────────────────────────────────────
    Route::prefix('accounts')->name('accounts.')->group(function () {

        Route::middleware('role:accountant,admin,management')->group(function () {
            Route::get('/',               [AccountController::class, 'index'])->name('index');
            Route::get('/create',         [AccountController::class, 'create'])->name('create');
            Route::post('/',              [AccountController::class, 'store'])->name('store');
            Route::get('/{account}',      [AccountController::class, 'show'])->name('show');
            Route::get('/{account}/edit', [AccountController::class, 'edit'])->name('edit');
            Route::put('/{account}',      [AccountController::class, 'update'])->name('update');
            Route::delete('/{account}',   [AccountController::class, 'destroy'])->middleware('role:admin')->name('destroy');

            Route::post('/{account}/activate',       [AccountController::class, 'activate'])->name('activate');
            Route::post('/{account}/deactivate',     [AccountController::class, 'deactivate'])->name('deactivate');
            Route::get('/{account}/transactions',    [AccountController::class, 'transactions'])->name('transactions');
            Route::get('/{account}/statement',       [AccountController::class, 'statement'])->name('statement');

            Route::get('/{account}/deposit',         [AccountController::class, 'showDeposit'])->name('deposit.show');
            Route::post('/{account}/deposit',        [AccountController::class, 'deposit'])->name('deposit');
            Route::get('/{account}/withdrawal',      [AccountController::class, 'showWithdrawal'])->name('withdrawal.show');
            Route::post('/{account}/withdrawal',     [AccountController::class, 'withdrawal'])->name('withdrawal');

            Route::get('/{account}/share-transfer',  [AccountController::class, 'showShareTransfer'])->name('share-transfer.show');
            Route::post('/{account}/share-transfer', [AccountController::class, 'shareTransfer'])->name('share-transfer');
        });

        Route::middleware('role:admin,management')->group(function () {
            Route::get('/shares/summary',   [AccountController::class, 'sharesSummary'])->name('shares.summary');
            Route::get('/shares/register',  [AccountController::class, 'sharesRegister'])->name('shares.register');
            Route::post('/shares/transfer', [AccountController::class, 'transferShares'])->name('shares.transfer');
        });
    });

    // ── Loan Products ─────────────────────────────────────────────────────
    Route::prefix('loan-products')->name('loan-products.')->middleware('role:admin,management')->group(function () {
        Route::get('/',                             [LoanProductController::class, 'index'])->name('index');
        Route::get('/create',                       [LoanProductController::class, 'create'])->name('create');
        Route::post('/',                            [LoanProductController::class, 'store'])->name('store');
        Route::get('/{loanProduct}',                [LoanProductController::class, 'show'])->name('show');
        Route::get('/{loanProduct}/edit',           [LoanProductController::class, 'edit'])->name('edit');
        Route::put('/{loanProduct}',                [LoanProductController::class, 'update'])->name('update');
        Route::delete('/{loanProduct}',             [LoanProductController::class, 'destroy'])->name('destroy');
        Route::post('/{loanProduct}/toggle-status', [LoanProductController::class, 'toggleStatus'])->name('toggle-status');
    });

    // ── Loans ─────────────────────────────────────────────────────────────
    Route::prefix('loans')->name('loans.')->group(function () {
        Route::get('/',            [LoanController::class, 'index'])->name('index');
        Route::get('/create',      [LoanController::class, 'create'])->name('create');
        Route::post('/',           [LoanController::class, 'store'])->name('store');
        Route::get('/{loan}',      [LoanController::class, 'show'])->name('show');
        Route::get('/{loan}/edit', [LoanController::class, 'edit'])->name('edit');
        Route::put('/{loan}',      [LoanController::class, 'update'])->name('update');
        Route::delete('/{loan}',   [LoanController::class, 'destroy'])->name('destroy');

        Route::post('/{loan}/submit',   [LoanController::class, 'submit'])->name('submit');
        Route::post('/{loan}/review',   [LoanController::class, 'review'])->name('review');
        Route::post('/{loan}/approve',  [LoanController::class, 'approve'])->name('approve');
        Route::post('/{loan}/reject',   [LoanController::class, 'reject'])->name('reject');
        Route::post('/{loan}/disburse', [LoanController::class, 'disburse'])->name('disburse');

        Route::get('/{loan}/schedule',   [LoanController::class, 'schedule'])->name('schedule');
        Route::get('/{loan}/repayments', [LoanController::class, 'repayments'])->name('repayments');
        Route::post('/{loan}/repayment', [LoanController::class, 'recordRepayment'])->name('record-repayment');
        Route::get('/{loan}/statement',  [LoanController::class, 'statement'])->name('statement');

        Route::get('/{loan}/guarantors',                     [LoanController::class, 'guarantors'])->name('guarantors');
        Route::post('/{loan}/guarantors',                    [LoanController::class, 'addGuarantor'])->name('add-guarantor');
        Route::delete('/{loan}/guarantors/{guarantor}',      [LoanController::class, 'removeGuarantor'])->name('remove-guarantor');
        Route::post('/{loan}/guarantors/{guarantor}/accept', [LoanController::class, 'acceptGuarantee'])->name('accept-guarantee');
        Route::post('/{loan}/guarantors/{guarantor}/reject', [LoanController::class, 'rejectGuarantee'])->name('reject-guarantee');

        Route::post('/{loan}/restructure', [LoanController::class, 'restructure'])->name('restructure');
        Route::post('/{loan}/write-off',   [LoanController::class, 'writeOff'])->name('write-off');

        Route::get('/products',                [LoanController::class, 'products'])->name('products');
        Route::get('/products/create',         [LoanController::class, 'createProduct'])->name('products.create');
        Route::post('/products',               [LoanController::class, 'storeProduct'])->name('products.store');
        Route::get('/products/{product}',      [LoanController::class, 'showProduct'])->name('products.show');
        Route::get('/products/{product}/edit', [LoanController::class, 'editProduct'])->name('products.edit');
        Route::put('/products/{product}',      [LoanController::class, 'updateProduct'])->name('products.update');
        Route::delete('/products/{product}',   [LoanController::class, 'destroyProduct'])->name('products.destroy');

        Route::get('/analytics/portfolio',   [LoanController::class, 'portfolio'])->name('analytics.portfolio');
        Route::get('/analytics/arrears',     [LoanController::class, 'arrears'])->name('analytics.arrears');
        Route::get('/analytics/performance', [LoanController::class, 'performance'])->name('analytics.performance');
    });

    // ── Dividends ─────────────────────────────────────────────────────────
    Route::prefix('dividends')->name('dividends.')->group(function () {
        Route::get('/',            [DividendController::class, 'index'])->name('index');
        Route::get('/create',      [DividendController::class, 'create'])->name('create');
        Route::post('/',           [DividendController::class, 'store'])->name('store');
        Route::get('/{dividend}',      [DividendController::class, 'show'])->name('show');
        Route::get('/{dividend}/edit', [DividendController::class, 'edit'])->name('edit');
        Route::put('/{dividend}',      [DividendController::class, 'update'])->name('update');
        Route::delete('/{dividend}',   [DividendController::class, 'destroy'])->name('destroy');

        Route::post('/calculate/{year}',           [DividendController::class, 'calculate'])->name('calculate');
        Route::post('/{dividend}/approve',          [DividendController::class, 'approve'])->name('approve');
        Route::post('/{dividend}/distribute',       [DividendController::class, 'distribute'])->name('distribute');
        Route::post('/{dividend}/reverse',          [DividendController::class, 'reverse'])->name('reverse');

        Route::get('/{dividend}/members',               [DividendController::class, 'members'])->name('members');
        Route::get('/{dividend}/members/{member}',      [DividendController::class, 'memberDetails'])->name('member-details');
        Route::post('/{dividend}/members/{member}/pay', [DividendController::class, 'payMemberDividend'])->name('pay-member');

        Route::get('/{dividend}/report',     [DividendController::class, 'report'])->name('report');
        Route::get('/analytics/history',     [DividendController::class, 'history'])->name('analytics.history');
        Route::get('/analytics/projections', [DividendController::class, 'projections'])->name('analytics.projections');
    });

    // ── Budgets ───────────────────────────────────────────────────────────
    Route::prefix('budgets')->name('budgets.')->group(function () {
        Route::get('/',            [BudgetController::class, 'index'])->name('index');
        Route::get('/create',      [BudgetController::class, 'create'])->name('create');
        Route::post('/',           [BudgetController::class, 'store'])->name('store');
        Route::get('/{budget}',      [BudgetController::class, 'show'])->name('show');
        Route::get('/{budget}/edit', [BudgetController::class, 'edit'])->name('edit');
        Route::put('/{budget}',      [BudgetController::class, 'update'])->name('update');
        Route::delete('/{budget}',   [BudgetController::class, 'destroy'])->name('destroy');

        Route::post('/{budget}/submit',   [BudgetController::class, 'submit'])->name('submit');
        Route::post('/{budget}/approve',  [BudgetController::class, 'approve'])->name('approve');
        Route::post('/{budget}/activate', [BudgetController::class, 'activate'])->name('activate');
        Route::post('/{budget}/close',    [BudgetController::class, 'close'])->name('close');

        Route::get('/{budget}/items',           [BudgetController::class, 'items'])->name('items');
        Route::post('/{budget}/items',          [BudgetController::class, 'storeItem'])->name('store-item');
        Route::put('/{budget}/items/{item}',    [BudgetController::class, 'updateItem'])->name('update-item');
        Route::delete('/{budget}/items/{item}', [BudgetController::class, 'destroyItem'])->name('destroy-item');

        Route::get('/{budget}/variance',    [BudgetController::class, 'variance'])->name('variance');
        Route::get('/{budget}/utilization', [BudgetController::class, 'utilization'])->name('utilization');
    });

    // ── Chart of Accounts ─────────────────────────────────────────────────
    Route::prefix('chart-of-accounts')->name('chart-of-accounts.')->group(function () {

        Route::get('/',       [ChartOfAccountController::class, 'index'])->name('index');
        Route::get('/create', [ChartOfAccountController::class, 'create'])->name('create');
        Route::post('/',      [ChartOfAccountController::class, 'store'])->name('store');

        Route::get('/{chartOfAccount}',      [ChartOfAccountController::class, 'show'])->name('show');
        Route::get('/{chartOfAccount}/edit', [ChartOfAccountController::class, 'edit'])->name('edit');
        Route::put('/{chartOfAccount}',      [ChartOfAccountController::class, 'update'])->name('update');
        Route::delete('/{chartOfAccount}',   [ChartOfAccountController::class, 'destroy'])->name('destroy');

        Route::post('/{chartOfAccount}/toggle-active',
            [ChartOfAccountController::class, 'toggleActive'])->name('toggle-active');

      
        Route::get('/api/postable',
            [ChartOfAccountController::class, 'postableAccounts'])->name('api.postable');

     
        Route::get('/api/budget-lines',  [ChartOfAccountController::class, 'budgetLineAccounts'])->name('api.budget-lines');

        Route::get('/api/tree', [ChartOfAccountController::class, 'accountTree'])->name('api.tree');
        Route::get('/api/next-code' , [ChartOfAccountController::class , 'nextCode'])->name('api.next-code');
        Route::post('/api/categories', [ChartOfAccountController::class , 'storeCategory'])->name('api.categories.store');

    });

  
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::resource('chart-of-accounts', ChartOfAccountController::class)->names([
            'index'   => 'coa.index',
            'create'  => 'coa.create',
            'store'   => 'coa.store',
            'show'    => 'coa.show',
            'edit'    => 'coa.edit',
            'update'  => 'coa.update',
            'destroy' => 'coa.destroy',
        ]);
        Route::post('chart-of-accounts/{chartOfAccount}/toggle-active',
            [ChartOfAccountController::class, 'toggleActive'])->name('coa.toggle-active');
        Route::get('api/postable-accounts',
            [ChartOfAccountController::class, 'postableAccounts'])->name('coa.postable-accounts');
    });

    // ── Payment Vouchers ──────────────────────────────────────────────────
    Route::prefix('vouchers')->name('vouchers.')->group(function () {
        Route::get('/',            [PaymentVoucherController::class, 'index'])->name('index');
        Route::get('/create',      [PaymentVoucherController::class, 'create'])->name('create');
        Route::post('/',           [PaymentVoucherController::class, 'store'])->name('store');
        Route::get('/{voucher}',      [PaymentVoucherController::class, 'show'])->name('show');
        Route::get('/{voucher}/edit', [PaymentVoucherController::class, 'edit'])->name('edit');
        Route::put('/{voucher}',      [PaymentVoucherController::class, 'update'])->name('update');
        Route::delete('/{voucher}',   [PaymentVoucherController::class, 'destroy'])->name('destroy');

        Route::post('/{voucher}/submit',    [PaymentVoucherController::class, 'submit'])->name('submit');
        Route::post('/{voucher}/approve',   [PaymentVoucherController::class, 'approve'])->name('approve');
        Route::post('/{voucher}/reject',    [PaymentVoucherController::class, 'reject'])->name('reject');
        Route::post('/{voucher}/pay',       [PaymentVoucherController::class, 'pay'])->name('pay');
        Route::post('/{voucher}/cancel',    [PaymentVoucherController::class, 'cancel'])->name('cancel');
        Route::post('/{voucher}/duplicate', [PaymentVoucherController::class, 'duplicate'])->name('duplicate');
        Route::get('/{voucher}/pdf',        [PaymentVoucherController::class, 'downloadPdf'])->name('pdf');

        Route::post('/{voucher}/documents',                 [PaymentVoucherController::class, 'uploadDocuments'])->name('upload-documents');
        Route::delete('/{voucher}/documents/{document}',    [PaymentVoucherController::class, 'deleteDocument'])->name('delete-document');

        Route::get('/reports/pending',  [PaymentVoucherController::class, 'pendingReport'])->name('reports.pending');
        Route::get('/reports/approved', [PaymentVoucherController::class, 'approvedReport'])->name('reports.approved');
        Route::get('/reports/paid',     [PaymentVoucherController::class, 'paidReport'])->name('reports.paid');
    });

    // ── Reports ───────────────────────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');

        Route::get('/financial',                  [ReportController::class, 'financialIndex'])->name('financial.index');
        Route::get('/financial/balance-sheet',    [ReportController::class, 'balanceSheet'])->name('financial.balance-sheet');
        Route::get('/financial/income-statement', [ReportController::class, 'incomeStatement'])->name('financial.income-statement');
        Route::get('/financial/cash-flow',        [ReportController::class, 'cashFlow'])->name('financial.cash-flow');
        Route::get('/financial/trial-balance',    [ReportController::class, 'trialBalance'])->name('financial.trial-balance');

        Route::get('/members/report',   [ReportController::class, 'memberIndex'])->name('membersReport.index');
        Route::get('/members/register', [ReportController::class, 'memberRegister'])->name('members.register');
        Route::get('/members/shares',   [ReportController::class, 'memberShares'])->name('members.shares');
        Route::get('/members/savings',  [ReportController::class, 'memberSavings'])->name('members.savings');
        Route::get('/members/loans',    [ReportController::class, 'memberLoans'])->name('members.loans');

        Route::get('/loans/report',       [ReportController::class, 'loanIndex'])->name('loansReport.index');
        Route::get('/loans/portfolio',    [ReportController::class, 'loanPortfolio'])->name('loans.portfolio');
        Route::get('/loans/arrears',      [ReportController::class, 'loanArrears'])->name('loans.arrears');
        Route::get('/loans/disbursement', [ReportController::class, 'loanDisbursement'])->name('loans.disbursement');
        Route::get('/loans/collection',   [ReportController::class, 'loanCollection'])->name('loans.collection');

        Route::get('/transactions/report',   [ReportController::class, 'transactionIndex'])->name('transactionsReport.index');
        Route::get('/transactions/daily',    [ReportController::class, 'dailyTransactions'])->name('transactions.daily');
        Route::get('/transactions/monthly',  [ReportController::class, 'monthlyTransactions'])->name('transactions.monthly');
        Route::get('/transactions/annual',   [ReportController::class, 'annualTransactions'])->name('transactions.annual');

        Route::get('/regulatory/report',     [ReportController::class, 'RegulatoryIndex'])->name('regulatoryReport.index');
        Route::get('/regulatory/statutory',  [ReportController::class, 'statutoryReports'])->name('regulatory.statutory');
        Route::get('/regulatory/compliance', [ReportController::class, 'complianceReports'])->name('regulatory.compliance');

        Route::get('/custom/builder',   [ReportController::class, 'customBuilder'])->name('custom.builder');
        Route::post('/custom/generate', [ReportController::class, 'generateCustom'])->name('custom.generate');
        Route::get('/export',           [ReportController::class, 'exportReport'])->name('export');
    });

    // ── Notifications ─────────────────────────────────────────────────────
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',                     [NotificationController::class, 'index'])->name('index');
        Route::get('/{notification}',       [NotificationController::class, 'show'])->name('show');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/mark-all-read',       [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{notification}',    [NotificationController::class, 'destroy'])->name('destroy');

        Route::get('/settings',  [NotificationController::class, 'settings'])->name('settings');
        Route::post('/settings', [NotificationController::class, 'updateSettings'])->name('update-settings');

        Route::get('/bulk/create', [NotificationController::class, 'createBulk'])->name('bulk.create');
        Route::post('/bulk/send',  [NotificationController::class, 'sendBulk'])->name('bulk.send');
    });

    // ── Admin System Settings ─────────────────────────────────────────────
    Route::prefix('admin/settings')->name('admin.settings.')->middleware('role:admin')->group(function () {
        Route::get('/',              [SettingsController::class, 'index'])->name('index');
        Route::get('/general',       [SettingsController::class, 'general'])->name('general');
        Route::post('/general',      [SettingsController::class, 'updateGeneral'])->name('update-general');
        Route::get('/financial',     [SettingsController::class, 'financial'])->name('financial');
        Route::post('/financial',    [SettingsController::class, 'updateFinancial'])->name('update-financial');
        Route::get('/loan',          [SettingsController::class, 'loan'])->name('loan');
        Route::post('/loan',         [SettingsController::class, 'updateLoan'])->name('update-loan');
        Route::get('/notification',  [SettingsController::class, 'notification'])->name('notification');
        Route::post('/notification', [SettingsController::class, 'updateNotification'])->name('update-notification');
        Route::get('/security',      [SettingsController::class, 'security'])->name('security');
        Route::post('/security',     [SettingsController::class, 'updateSecurity'])->name('update-security');
        Route::get('/backup',        [SettingsController::class, 'backup'])->name('backup');
        Route::post('/backup/create',   [SettingsController::class, 'createBackup'])->name('create-backup');
        Route::post('/backup/restore',  [SettingsController::class, 'restoreBackup'])->name('restore-backup');
    });

    // ── Internal API (AJAX / JSON) ─────────────────────────────────────────
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/search/members',  [MemberController::class, 'searchMembers'])->name('search.members');
        Route::get('/search/accounts', [AccountController::class, 'searchAccounts'])->name('search.accounts');
        Route::get('/search/loans',    [LoanController::class, 'searchLoans'])->name('search.loans');

        Route::get('/stats/dashboard',    [ReportController::class, 'dashboardStats'])->name('stats.dashboard');
        Route::get('/stats/members',      [MemberController::class, 'memberStats'])->name('stats.members');
        Route::get('/stats/loans',        [LoanController::class, 'loanStats'])->name('stats.loans');
        Route::get('/stats/transactions', [TransactionController::class, 'transactionStats'])->name('stats.transactions');

        Route::post('/validate/member-id',        [MemberController::class, 'validateMemberId'])->name('validate.member-id');
        Route::post('/validate/account-number',   [AccountController::class, 'validateAccountNumber'])->name('validate.account-number');
        Route::post('/validate/loan-eligibility', [LoanController::class, 'validateLoanEligibility'])->name('validate.loan-eligibility');

        Route::post('/calculate/loan-schedule',       [LoanController::class, 'calculateLoanSchedule'])->name('calculate.loan-schedule');
        Route::post('/calculate/dividend-projection', [DividendController::class, 'calculateDividendProjection'])->name('calculate.dividend-projection');

        Route::get('/export/members',              [MemberController::class, 'exportMembers'])->name('export.members');
        Route::get('/export/transactions',         [TransactionController::class, 'exportTransactions'])->name('export.transactions');
        Route::get('/export/loans',                [LoanController::class, 'exportLoans'])->name('export.loans');
        Route::get('/export/dividends/{dividend}', [DividendController::class, 'exportDividend'])->name('export.dividend');
    });

    // ── Schedules ─────────────────────────────────────────────────────────
    Route::prefix('schedule')->name('schedule.')->group(function () {
        Route::get('/', [ScheduleController::class, 'index'])->name('index');

        Route::get('/monthly-deposit',          [ScheduleController::class, 'monthlyDeposit'])->name('monthly-deposit');
        Route::post('/monthly-deposit/preview', [ScheduleController::class, 'previewMonthlyDeposits'])->name('monthly-deposit.preview');
        Route::post('/monthly-deposit/run',     [ScheduleController::class, 'runMonthlyDeposits'])->name('monthly-deposit.run');

        Route::get('/loan-repayment',       [ScheduleController::class, 'loanRepayment'])->name('loan-repayment');
        Route::post('/loan-repayment/run',  [ScheduleController::class, 'runLoanRepayments'])->name('loan-repayment.run');

        Route::get('/loan-disbursement',          [ScheduleController::class, 'loanDisbursement'])->name('loan-disbursement');
        Route::post('/loan-disbursement/run',     [ScheduleController::class, 'runLoanDisbursements'])->name('loan-disbursement.run');
        Route::get('/loan-disbursement/export',   [ScheduleController::class, 'exportLoanDisbursement'])->name('loan-disbursement.export');

        Route::get('/dividend-payment',       [ScheduleController::class, 'dividendPayment'])->name('dividend-payment');
        Route::post('/dividend-payment/run',  [ScheduleController::class, 'runDividendPayments'])->name('dividend-payment.run');
    });

    // ── Admin quick pages ─────────────────────────────────────────────────
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('pending-members',      [DashboardController::class, 'pendingMembersPage'])->name('admin.pending-members');
        Route::get('pending-members/list', [DashboardController::class, 'pendingMembers']);
    });

}); 

// ─────────────────────────────────────────────────────────────────────────────
// MEMBER SELF-SERVICE ROUTES
// ─────────────────────────────────────────────────────────────────────────────

require __DIR__.'/settings.php';

Route::prefix('profile')->name('profile.')->middleware('role:member')->group(function () {
    Route::get('/', function () {
        return redirect()->route('members.show', auth()->user()->member);
    })->name('index');
});

Route::middleware('role:member')->group(function () {
    Route::get('/my-loans', function () {
        return redirect()->route('members.loans', auth()->user()->member);
    })->name('my-loans');

    Route::get('/my-accounts', function () {
        return redirect()->route('members.accounts', auth()->user()->member);
    })->name('my-accounts');

    Route::get('/my-dividends', function () {
        return redirect()->route('members.dividends', auth()->user()->member);
    })->name('my-dividends');

    Route::get('/my-transactions', function () {
        return redirect()->route('members.transactions', auth()->user()->member);
    })->name('my-transactions');
});

Route::middleware(['auth', 'role:member'])->group(function () {
    Route::get('/{member}/my-accounts/{account}/statement', [AccountController::class, 'myStatement'])
        ->name('my-accounts.statement');
});

Route::get('/my-accounts/{account}/statement/pdf', [AccountController::class, 'statementPdf'])
    ->name('my-accounts.statement.pdf');

Route::middleware('role:member')->prefix('members')->name('members.')->group(function () {
    Route::get('/{member}/accounts/{account}/deposit',  [MemberController::class, 'showDeposit'])->name('accounts.deposit.show');
    Route::post('/{member}/accounts/{account}/deposit', [MemberController::class, 'deposit'])->name('accounts.deposit');
});

Route::get('/members/{member}/accounts/{account}/withdrawal',  [MemberController::class, 'showWithdrawal'])
    ->name('members.accounts.withdrawal.show');
Route::post('/members/{member}/accounts/{account}/withdrawal', [MemberController::class, 'withdrawal'])
    ->name('members.accounts.withdrawal');

// ─────────────────────────────────────────────────────────────────────────────
// PROFILE
// ─────────────────────────────────────────────────────────────────────────────

Route::middleware(['auth'])->group(function () {
    Route::get('/profile/complete',  [ProfileController::class, 'complete'])->name('profile.complete');
    Route::post('/profile/complete', [ProfileController::class, 'store'])->name('profile.complete.store');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile/show',  [ProfileController::class, 'show'])->name('member.profile.show');
    Route::put('/profile/update',[ProfileController::class, 'updateProfile'])->name('member.updateProfile');
});

Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

Route::post('/member/profile/photo', [ProfileController::class, 'updatePhoto'])->name('member.updatePhoto');

// ─────────────────────────────────────────────────────────────────────────────
// TRANSACTIONS
// ─────────────────────────────────────────────────────────────────────────────

Route::resource('transactions', TransactionController::class);
Route::post('/transactions/{id}/approve', [TransactionController::class, 'approve'])->name('transactions.approve');
Route::post('/transactions/{id}/reject',  [TransactionController::class, 'reject'])->name('transactions.reject');
Route::post('/transactions/{id}/reverse', [TransactionController::class, 'reverse'])->name('transactions.reverse');
Route::get('/members/{id}/transactions',  [TransactionController::class, 'memberTransactions'])->name('members.transactions');
Route::get('/accounts/{id}/transactions', [TransactionController::class, 'accountTransactions'])->name('accounts.transactions');
Route::get('/stats/transactions',         [TransactionController::class, 'statistics'])->name('stats.transactions');

// ─────────────────────────────────────────────────────────────────────────────
// MISC
// ─────────────────────────────────────────────────────────────────────────────

Route::post('/switch-role', [RoleSwitchController::class, 'switch'])->name('role.switch');
Route::post('/stop-role',   [RoleSwitchController::class, 'stop'])->name('role.stop');

Route::post('/members/assign-usernames', [MemberController::class, 'assignUsernames'])
    ->name('members.assignUsernames');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::post('/members/bulk-delete', [MemberController::class, 'bulkDelete'])->name('members.bulkDelete');
});

Route::redirect('/settings', '/settings/profile');