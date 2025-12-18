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
        // Here you can fetch disbursement data from your database
        $loans = []; // Replace with actual data

        return Inertia::render('Admin/Schedule/LoanDisbursement', [
            'loans' => $loans,
        ]);
    }

    /**
     * Show loan repayment schedule
     */
    public function loanRepayment()
    {
        $repayments = []; // Replace with actual data

        return Inertia::render('Admin/Schedule/LoanRepayment', [
            'repayments' => $repayments,
        ]);
    }

    /**
     * Show monthly deposit schedule
     */
    public function monthlyDeposit()
    {
        $deposits = []; // Replace with actual data

        return Inertia::render('Admin/Schedule/MonthlyDeposit', [
            'deposits' => $deposits,
        ]);
    }

    /**
     * Show dividend repayment schedule
     */
    public function dividendRepayment()
    {
        $dividends = []; // Replace with actual data

        return Inertia::render('Admin/Schedule/DividendRepayment', [
            'dividends' => $dividends,
        ]);
    }
}
