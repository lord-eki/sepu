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
            'loan_product_id'    => 'required|exists:loan_products,id',
            'principal_amount'   => 'required|numeric|min:1',
            'term_months'        => 'required|integer|min:1',
            'member_id'          => 'nullable|exists:members,id',
        ]);

        $loanProduct     = LoanProduct::findOrFail($validated['loan_product_id']);
        $principalAmount = $validated['principal_amount'];
        $termMonths      = $validated['term_months'];

        // Validate amount range
        if ($principalAmount < $loanProduct->min_amount || $principalAmount > $loanProduct->max_amount) {
            return response()->json([
                'error' => "Loan amount must be between {$loanProduct->min_amount} and {$loanProduct->max_amount}"
            ], 422);
        }

        // Validate term range
        if ($termMonths < $loanProduct->min_term_months || $termMonths > $loanProduct->max_term_months) {
            return response()->json([
                'error' => "Loan term must be between {$loanProduct->min_term_months} and {$loanProduct->max_term_months} months"
            ], 422);
        }

        $calculation = $this->calculateLoan($loanProduct, $principalAmount, $termMonths);

        return response()->json([
            'success'     => true,
            'calculation' => $calculation
        ]);
    }

 
    private function calculateLoan(LoanProduct $loanProduct, float $principalAmount, int $termMonths): array
    {
        $monthlyRate         = $loanProduct->interest_rate / 100;
        $processingFeeRate   = $loanProduct->processing_fee_rate / 100;
        $insuranceRate       = $loanProduct->insurance_rate / 100;

        $processingFee = $principalAmount * $processingFeeRate;
        $insuranceFee  = $principalAmount * $insuranceRate;
        $totalFees     = $processingFee + $insuranceFee;
        $netDisbursement = $principalAmount - $totalFees;

        $principalPerMonth = $principalAmount / $termMonths;


        $totalInterest = $principalAmount * $monthlyRate * ($termMonths + 1) / 2;

        $mInterest = $totalInterest / $termMonths;

        $actualInstallment = $principalPerMonth + $mInterest;

        $totalRepayment = $actualInstallment * $termMonths; 

        $schedule = $this->generateSchedule(
            $principalAmount,
            $principalPerMonth,
            $monthlyRate,
            $mInterest,
            $actualInstallment,
            $termMonths,
            $loanProduct->grace_period_days ?? 0
        );

        $graceDays         = $loanProduct->grace_period_days ?? 0;
        $firstPaymentDate  = now()->addDays($graceDays)->addMonth()->format('Y-m-d');
        $lastPaymentDate   = now()->addDays($graceDays)->addMonths($termMonths)->format('Y-m-d');

        return [
            'loan_product' => [
                'name'              => $loanProduct->name,
                'code'              => $loanProduct->code,
                'interest_rate'     => $loanProduct->interest_rate,   
                'grace_period_days' => $graceDays,
            ],
            'loan_details' => [
                'principal_amount'    => round($principalAmount, 2),
                'term_months'         => $termMonths,
                'monthly_rate'        => round($monthlyRate * 100, 4),    
                'annual_rate'         => round($monthlyRate * 12 * 100, 4), 
                'principal_per_month' => round($principalPerMonth, 2),
                'm_interest'          => round($mInterest, 2),            
                'monthly_payment'     => round($actualInstallment, 2),    
                'total_interest'      => round($totalInterest, 2),
                'total_repayment'     => round($totalRepayment, 2),
                'processing_fee'      => round($processingFee, 2),
                'insurance_fee'       => round($insuranceFee, 2),
                'total_fees'          => round($totalFees, 2),
                'total_cost_of_loan'  => round($totalRepayment + $totalFees, 2),
                'net_disbursement'    => round($netDisbursement, 2),
            ],
            'summary' => [
                'total_payments'        => $termMonths,
                'total_principal_paid'  => round($principalAmount, 2),
                'total_interest_paid'   => round($totalInterest, 2),
                'first_payment_date'    => $firstPaymentDate,
                'last_payment_date'     => $lastPaymentDate,
            ],
            'amortization_schedule' => $schedule,
        ];
    }

    /**
     */
    private function generateSchedule(
        float $principal,
        float $principalPerMonth,
        float $monthlyRate,
        float $mInterest,
        float $actualInstallment,
        int   $termMonths,
        int   $graceDays = 0
    ): array {
        $schedule         = [];
        $openingBalance   = $principal;
        $cumulInterest    = 0;
        $cumulPrincipal   = 0;
        $currentDate      = now()->addDays($graceDays)->addMonth();

        for ($month = 1; $month <= $termMonths; $month++) {
            $interestThisMonth = $openingBalance * $monthlyRate;

            $reducingInstallment = $principalPerMonth + $interestThisMonth;

            $closingBalance = $openingBalance - $principalPerMonth;

            $cumulInterest  += $mInterest;          
            $cumulPrincipal += $principalPerMonth;

            $schedule[] = [
                'payment_number'       => $month,
                'payment_date'         => $currentDate->format('Y-m-d'),
                'opening_balance'      => round($openingBalance, 2),
                'principal_amount'     => round($principalPerMonth, 2),
                'interest_amount'      => round($interestThisMonth, 2),
                'installment'          => round($reducingInstallment, 2),
                'm_interest'           => round($mInterest, 2),
                'payment_amount'       => round($actualInstallment, 2),
                'closing_balance'      => round(max(0, $closingBalance), 2),
                'cumulative_interest'  => round($cumulInterest, 2),
                'cumulative_principal' => round($cumulPrincipal, 2),
            ];

            $openingBalance = $closingBalance;
            $currentDate    = $currentDate->addMonth();
        }

        return $schedule;
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
                'id'                  => $loanProduct->id,
                'name'                => $loanProduct->name,
                'code'                => $loanProduct->code,
                'description'         => $loanProduct->description,
                'min_amount'          => $loanProduct->min_amount,
                'max_amount'          => $loanProduct->max_amount,
                'interest_rate'       => $loanProduct->interest_rate,
                'min_term_months'     => $loanProduct->min_term_months,
                'max_term_months'     => $loanProduct->max_term_months,
                'processing_fee_rate' => $loanProduct->processing_fee_rate,
                'insurance_rate'      => $loanProduct->insurance_rate,
                'grace_period_days'   => $loanProduct->grace_period_days,
                'requires_guarantor'  => $loanProduct->requires_guarantor,
                'min_guarantors'      => $loanProduct->min_guarantors,
            ]
        ]);
    }
}