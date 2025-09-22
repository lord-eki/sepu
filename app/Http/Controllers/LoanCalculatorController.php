<?php

namespace App\Http\Controllers;

use App\Models\LoanProduct;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoanCalculatorController extends Controller
{
    /**
     * Display the loan calculator page
     */
    public function index()
    {
        // Get active loan products for the dropdown
        $loanProducts = LoanProduct::where('is_active', true)
            ->select(['id', 'name', 'code', 'min_amount', 'max_amount', 'interest_rate', 
                     'min_term_months', 'max_term_months', 'processing_fee_rate', 
                     'insurance_rate', 'grace_period_days'])
            ->get();

        return Inertia::render('LoanCalculator/Index', [
            'loanProducts' => $loanProducts
        ]);
    }

    /**
     * Calculate loan breakdown
     */
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'loan_product_id' => 'required|exists:loan_products,id',
            'principal_amount' => 'required|numeric|min:1',
            'term_months' => 'required|integer|min:1',
            'member_id' => 'nullable|exists:members,id'
        ]);

        $loanProduct = LoanProduct::findOrFail($validated['loan_product_id']);
        $principalAmount = $validated['principal_amount'];
        $termMonths = $validated['term_months'];

        // Validate amount and term against loan product limits
        if ($principalAmount < $loanProduct->min_amount || $principalAmount > $loanProduct->max_amount) {
            return response()->json([
                'error' => "Loan amount must be between {$loanProduct->min_amount} and {$loanProduct->max_amount}"
            ], 422);
        }

        if ($termMonths < $loanProduct->min_term_months || $termMonths > $loanProduct->max_term_months) {
            return response()->json([
                'error' => "Loan term must be between {$loanProduct->min_term_months} and {$loanProduct->max_term_months} months"
            ], 422);
        }

        // Calculate loan breakdown
        $calculation = $this->calculateLoanBreakdown($loanProduct, $principalAmount, $termMonths);

        return response()->json([
            'success' => true,
            'calculation' => $calculation
        ]);
    }

    /**
     * Calculate detailed loan breakdown
     */
    private function calculateLoanBreakdown(LoanProduct $loanProduct, float $principalAmount, int $termMonths)
    {
        // Get rates
        $annualInterestRate = $loanProduct->interest_rate / 100; 
        $monthlyInterestRate = $annualInterestRate / 12;
        $processingFeeRate = $loanProduct->processing_fee_rate / 100;
        $insuranceRate = $loanProduct->insurance_rate / 100;

        // Calculate fees
        $processingFee = $principalAmount * $processingFeeRate;
        $insuranceFee = $principalAmount * $insuranceRate;
        $totalFees = $processingFee + $insuranceFee;

        // Calculate monthly payment using loan payment formula
        // PMT = P * [r(1 + r)^n] / [(1 + r)^n - 1]
        if ($monthlyInterestRate > 0) {
            $monthlyPayment = $principalAmount * 
                ($monthlyInterestRate * pow(1 + $monthlyInterestRate, $termMonths)) / 
                (pow(1 + $monthlyInterestRate, $termMonths) - 1);
        } else {
            // If no interest, simple division
            $monthlyPayment = $principalAmount / $termMonths;
        }

        // Calculate total amounts
        $totalRepayment = $monthlyPayment * $termMonths;
        $totalInterest = $totalRepayment - $principalAmount;
        $totalCostOfLoan = $totalRepayment + $totalFees;

        // Generate amortization schedule
        $schedule = $this->generateAmortizationSchedule(
            $principalAmount, 
            $monthlyInterestRate, 
            $monthlyPayment, 
            $termMonths,
            $loanProduct->grace_period_days
        );

        return [
            'loan_product' => [
                'name' => $loanProduct->name,
                'code' => $loanProduct->code,
                'interest_rate' => $loanProduct->interest_rate,
                'grace_period_days' => $loanProduct->grace_period_days
            ],
            'loan_details' => [
                'principal_amount' => round($principalAmount, 2),
                'term_months' => $termMonths,
                'monthly_payment' => round($monthlyPayment, 2),
                'total_repayment' => round($totalRepayment, 2),
                'total_interest' => round($totalInterest, 2),
                'processing_fee' => round($processingFee, 2),
                'insurance_fee' => round($insuranceFee, 2),
                'total_fees' => round($totalFees, 2),
                'total_cost_of_loan' => round($totalCostOfLoan, 2),
                'effective_annual_rate' => $this->calculateEffectiveRate($principalAmount, $totalCostOfLoan, $termMonths)
            ],
            'amortization_schedule' => $schedule,
            'summary' => [
                'total_payments' => $termMonths,
                'total_principal_paid' => round($principalAmount, 2),
                'total_interest_paid' => round($totalInterest, 2),
                'average_monthly_payment' => round($monthlyPayment, 2),
                'first_payment_date' => now()->addMonth()->format('Y-m-d'),
                'last_payment_date' => now()->addMonths($termMonths)->format('Y-m-d')
            ]
        ];
    }

    /**
     * Generate amortization schedule
     */
    private function generateAmortizationSchedule(float $principal, float $monthlyRate, float $monthlyPayment, int $termMonths, int $gracePeriodDays = 0)
    {
        $schedule = [];
        $remainingBalance = $principal;
        $cumulativeInterest = 0;
        $cumulativePrincipal = 0;

        // Calculate first payment date considering grace period
        $currentDate = now()->addDays($gracePeriodDays)->addMonth();

        for ($month = 1; $month <= $termMonths; $month++) {
            // Calculate interest for this payment
            $interestPayment = $remainingBalance * $monthlyRate;
            
            // Calculate principal payment
            $principalPayment = $monthlyPayment - $interestPayment;
            
            // Adjust for final payment to avoid rounding issues
            if ($month == $termMonths) {
                $principalPayment = $remainingBalance;
                $monthlyPayment = $principalPayment + $interestPayment;
            }
            
            // Update running totals
            $cumulativeInterest += $interestPayment;
            $cumulativePrincipal += $principalPayment;
            
            // Update remaining balance
            $remainingBalance -= $principalPayment;
            
            $schedule[] = [
                'payment_number' => $month,
                'payment_date' => $currentDate->format('Y-m-d'),
                'payment_amount' => round($monthlyPayment, 2),
                'principal_amount' => round($principalPayment, 2),
                'interest_amount' => round($interestPayment, 2),
                'remaining_balance' => round(max(0, $remainingBalance), 2),
                'cumulative_interest' => round($cumulativeInterest, 2),
                'cumulative_principal' => round($cumulativePrincipal, 2)
            ];
            
            // Move to next month
            $currentDate = $currentDate->addMonth();
        }

        return $schedule;
    }

    /**
     * Calculate effective annual rate
     */
    private function calculateEffectiveRate(float $principal, float $totalCost, int $termMonths): float
    {
        if ($principal <= 0 || $termMonths <= 0) {
            return 0;
        }

        $totalInterestAndFees = $totalCost - $principal;
        $annualRate = ($totalInterestAndFees / $principal) * (12 / $termMonths) * 100;
        
        return round($annualRate, 2);
    }

    /**
     * Get loan product details
     */
    public function getLoanProduct($id)
    {
        $loanProduct = LoanProduct::where('id', $id)
            ->where('is_active', true)
            ->first();

        if (!$loanProduct) {
            return response()->json(['error' => 'Loan product not found'], 404);
        }

        return response()->json([
            'loan_product' => [
                'id' => $loanProduct->id,
                'name' => $loanProduct->name,
                'code' => $loanProduct->code,
                'description' => $loanProduct->description,
                'min_amount' => $loanProduct->min_amount,
                'max_amount' => $loanProduct->max_amount,
                'interest_rate' => $loanProduct->interest_rate,
                'min_term_months' => $loanProduct->min_term_months,
                'max_term_months' => $loanProduct->max_term_months,
                'processing_fee_rate' => $loanProduct->processing_fee_rate,
                'insurance_rate' => $loanProduct->insurance_rate,
                'grace_period_days' => $loanProduct->grace_period_days,
                'requires_guarantor' => $loanProduct->requires_guarantor,
                'min_guarantors' => $loanProduct->min_guarantors
            ]
        ]);
    }
}
