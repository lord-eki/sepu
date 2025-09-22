<?php

namespace App\Jobs;

use App\Models\Loan;
use App\Models\LoanRepayment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateLoanRepaymentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $loan;

    public function __construct(Loan $loan)
    {
        $this->loan = $loan;
    }

    public function handle()
    {
        $principalAmount = $this->loan->approved_amount;
        $interestRate = $this->loan->interest_rate / 100 / 12; // Monthly interest rate
        $termMonths = $this->loan->term_months;
        $monthlyPayment = $this->loan->monthly_repayment;
        
        $currentDate = Carbon::parse($this->loan->first_repayment_date);
        $remainingPrincipal = $principalAmount;

        for ($i = 1; $i <= $termMonths; $i++) {
            $interestPayment = $remainingPrincipal * $interestRate;
            $principalPayment = $monthlyPayment - $interestPayment;
            
            // Ensure last payment covers any remaining principal
            if ($i == $termMonths) {
                $principalPayment = $remainingPrincipal;
                $monthlyPayment = $principalPayment + $interestPayment;
            }

            LoanRepayment::create([
                'loan_id' => $this->loan->id,
                'due_date' => $currentDate->copy(),
                'expected_amount' => $monthlyPayment,
                'principal_amount' => $principalPayment,
                'interest_amount' => $interestPayment,
                'penalty_amount' => 0,
                'paid_amount' => 0,
                'outstanding_amount' => $monthlyPayment,
                'status' => 'pending',
                'days_late' => 0
            ]);

            $remainingPrincipal -= $principalPayment;
            $currentDate->addMonth();
        }
    }
}