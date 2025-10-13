<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\DividendController;
use App\Http\Controllers\LoanCalculatorController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/loan-products/{id}', [LoanCalculatorController::class, 'getLoanProduct'])
    ->name('loan-products.show');

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
