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
     * Calculate loan 
     */
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'loan_product_id' => 'required|exists:loan_products,id',
            'principal_amount' => 'required|numeric|min:1',
            'term_months' => 'required|integer|min:1',
            'member_id' => 'nullable|exists:members,id',
            'calculation_method' => 'nullable|in:reducing_balance,flat_rate',
        ]);

        $loanProduct = LoanProduct::findOrFail($validated['loan_product_id']);
        $principalAmount = $validated['principal_amount'];
        $termMonths = $validated['term_months'];
        $method = $validated['calculation_method'] ?? 'reducing_balance';

        // Validate amount and term
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

        // Calculate using selected method
        if ($method === 'reducing_balance') {
            $calculation = $this->calculateReducingBalance($loanProduct, $principalAmount, $termMonths);
        } else {
            $calculation = $this->calculateFlatRate($loanProduct, $principalAmount, $termMonths);
        }

        return response()->json([
            'success' => true,
            'calculation' => $calculation
        ]);
    }

    /**
     * 
     * Principal reduces with each payment
     * Interest calculated on outstanding balance
     */
    private function calculateReducingBalance(LoanProduct $loanProduct, float $principalAmount, int $termMonths)
    {
        // Get rates
        $annualInterestRate = $loanProduct->interest_rate / 100;
        $monthlyInterestRate = $annualInterestRate / 12;
        $processingFeeRate = $loanProduct->processing_fee_rate / 100;
        $insuranceRate = $loanProduct->insurance_rate / 100;

        // Calculate fees (one-time charges)
        $processingFee = $principalAmount * $processingFeeRate;
        $insuranceFee = $principalAmount * $insuranceRate;
        $totalFees = $processingFee + $insuranceFee;

        // Calculate monthly payment 
        // PMT = P * [r(1 + r)^n] / [(1 + r)^n - 1]
        if ($monthlyInterestRate > 0) {
            $monthlyPayment = $principalAmount * 
                ($monthlyInterestRate * pow(1 + $monthlyInterestRate, $termMonths)) / 
                (pow(1 + $monthlyInterestRate, $termMonths) - 1);
        } else {
            $monthlyPayment = $principalAmount / $termMonths;
        }

        // Generate amortization schedule
        $schedule = $this->generateReducingBalanceSchedule(
            $principalAmount,
            $monthlyInterestRate,
            $monthlyPayment,
            $termMonths,
            $loanProduct->grace_period_days
        );

        // Calculate totals from schedule
        $totalInterest = array_sum(array_column($schedule, 'interest_amount'));
        $totalRepayment = array_sum(array_column($schedule, 'payment_amount'));

        return [
            'method' => 'reducing_balance',
            'loan_product' => [
                'name' => $loanProduct->name,
                'code' => $loanProduct->code,
                'interest_rate' => $loanProduct->interest_rate,
                'grace_period_days' => $loanProduct->grace_period_days,
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
                'total_cost_of_loan' => round($totalRepayment + $totalFees, 2),
                'net_disbursement' => round($principalAmount - $totalFees, 2),
            ],
            'amortization_schedule' => $schedule,
            'summary' => [
                'total_payments' => $termMonths,
                'total_principal_paid' => round($principalAmount, 2),
                'total_interest_paid' => round($totalInterest, 2),
                'average_monthly_payment' => round($monthlyPayment, 2),
                'first_payment_date' => now()->addDays($loanProduct->grace_period_days)->addMonth()->format('Y-m-d'),
                'last_payment_date' => now()->addDays($loanProduct->grace_period_days)->addMonths($termMonths)->format('Y-m-d'),
                'interest_savings_vs_flat' => $this->compareWithFlatRate($principalAmount, $monthlyInterestRate * 12, $termMonths),
            ]
        ];
    }

    /**
     * Generate Reducing balance amortization schedule
     */
    private function generateReducingBalanceSchedule(
        float $principal,
        float $monthlyRate,
        float $monthlyPayment,
        int $termMonths,
        int $gracePeriodDays = 0
    ) {
        $schedule = [];
        $remainingBalance = $principal;
        $cumulativeInterest = 0;
        $cumulativePrincipal = 0;

        $currentDate = now()->addDays($gracePeriodDays)->addMonth();

        for ($month = 1; $month <= $termMonths; $month++) {
            // Interest on remaining balance
            $interestPayment = $remainingBalance * $monthlyRate;
            
            // Principal payment
            $principalPayment = $monthlyPayment - $interestPayment;
            
            // Adjust for final payment to clear any rounding differences
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
                'opening_balance' => round($remainingBalance + $principalPayment, 2),
                'payment_amount' => round($monthlyPayment, 2),
                'principal_amount' => round($principalPayment, 2),
                'interest_amount' => round($interestPayment, 2),
                'closing_balance' => round(max(0, $remainingBalance), 2),
                'cumulative_interest' => round($cumulativeInterest, 2),
                'cumulative_principal' => round($cumulativePrincipal, 2),
            ];
            
            $currentDate = $currentDate->addMonth();
        }

        return $schedule;
    }

    /**
     * 
     * Interest calculated on original principal for entire term
     */
    private function calculateFlatRate(LoanProduct $loanProduct, float $principalAmount, int $termMonths)
    {
        $annualInterestRate = $loanProduct->interest_rate / 100;
        $processingFeeRate = $loanProduct->processing_fee_rate / 100;
        $insuranceRate = $loanProduct->insurance_rate / 100;

        // Flat rate: Interest = Principal × Rate × Time
        $totalInterest = $principalAmount * $annualInterestRate * ($termMonths / 12);
        $totalRepayment = $principalAmount + $totalInterest;
        $monthlyPayment = $totalRepayment / $termMonths;

        $processingFee = $principalAmount * $processingFeeRate;
        $insuranceFee = $principalAmount * $insuranceRate;
        $totalFees = $processingFee + $insuranceFee;

        // Generate flat rate schedule
        $schedule = $this->generateFlatRateSchedule(
            $principalAmount,
            $totalInterest,
            $monthlyPayment,
            $termMonths,
            $loanProduct->grace_period_days
        );

        return [
            'method' => 'flat_rate',
            'loan_product' => [
                'name' => $loanProduct->name,
                'code' => $loanProduct->code,
                'interest_rate' => $loanProduct->interest_rate,
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
                'total_cost_of_loan' => round($totalRepayment + $totalFees, 2),
                'net_disbursement' => round($principalAmount - $totalFees, 2),
            ],
            'amortization_schedule' => $schedule,
        ];
    }

    /**
     * Generate flat rate schedule
     */
    private function generateFlatRateSchedule(
        float $principal,
        float $totalInterest,
        float $monthlyPayment,
        int $termMonths,
        int $gracePeriodDays = 0
    ) {
        $schedule = [];
        $principalPerMonth = $principal / $termMonths;
        $interestPerMonth = $totalInterest / $termMonths;
        $remainingBalance = $principal;

        $currentDate = now()->addDays($gracePeriodDays)->addMonth();

        for ($month = 1; $month <= $termMonths; $month++) {
            $remainingBalance -= $principalPerMonth;
            
            $schedule[] = [
                'payment_number' => $month,
                'payment_date' => $currentDate->format('Y-m-d'),
                'opening_balance' => round($remainingBalance + $principalPerMonth, 2),
                'payment_amount' => round($monthlyPayment, 2),
                'principal_amount' => round($principalPerMonth, 2),
                'interest_amount' => round($interestPerMonth, 2),
                'closing_balance' => round(max(0, $remainingBalance), 2),
                'cumulative_interest' => round($interestPerMonth * $month, 2),
                'cumulative_principal' => round($principalPerMonth * $month, 2),
            ];
            
            $currentDate = $currentDate->addMonth();
        }

        return $schedule;
    }

    /**
     * Compare reducing balance savings vs flat rate
     */
    private function compareWithFlatRate($principal, $annualRate, $months)
    {
        $flatInterest = $principal * $annualRate * ($months / 12);
        
        // Approximate reducing balance interest
        $monthlyRate = $annualRate / 12;
        $monthlyPayment = $principal * 
            ($monthlyRate * pow(1 + $monthlyRate, $months)) / 
            (pow(1 + $monthlyRate, $months) - 1);
        $reducingInterest = ($monthlyPayment * $months) - $principal;
        
        return round($flatInterest - $reducingInterest, 2);
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
                'min_guarantors' => $loanProduct->min_guarantors,
            ]
        ]);
    }
}