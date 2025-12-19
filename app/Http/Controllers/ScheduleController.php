<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    /**
     * Show loan disbursement schedule
     */
    public function loanDisbursement()
    {
     
        $loans = []; // help me get this

        return Inertia::render('Admin/Schedule/LoanDisbursement', [
            'loans' => $loans,
        ]);
    }

    /**
     * Show loan repayment schedule
     */
    public function loanRepayment()
    {
        $repayments = []; // help me get this 

        return Inertia::render('Admin/Schedule/LoanRepayment', [
            'repayments' => $repayments,
        ]);
    }

    /**
     * Show monthly deposit schedule
     */
    public function monthlyDeposit()
    {
        $deposits = []; // help me get this

        return Inertia::render('Admin/Schedule/MonthlyDeposit', [
            'deposits' => $deposits,
        ]);
    }

    /**
     * Show dividend repayment schedule
     */
    public function dividendRepayment()
    {
        $dividends = []; // help code this

        return Inertia::render('Admin/Schedule/DividendRepayment', [
            'dividends' => $dividends,
        ]);
    }
}
