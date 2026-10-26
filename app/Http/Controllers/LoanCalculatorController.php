<?php

namespace App\Http\Controllers;

use App\Models\LoanProduct;
use App\Services\Loans\LoanCalculator;
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
            ->select([
                'id',
                'name',
                'code',
                'min_amount',
                'max_amount',
                'interest_rate',
                'min_term_months',
                'max_term_months',
                'processing_fee_rate',
                'insurance_rate',
                'grace_period_days'
            ])
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

        $calculation = $this->calculateLoan($loanProduct, $principalAmount, $termMonths);

        return response()->json([
            'success'     => true,
            'calculation' => $calculation
        ]);
    }


    private function calculateLoan(LoanProduct $loanProduct, float $principalAmount, int $termMonths): array
    {
        $graceDays = (int) ($loanProduct->grace_period_days ?? 0);
        $firstDue  = now()->addDays($graceDays)->addMonthNoOverflow();

        $c = app(LoanCalculator::class)->summary(
            $principalAmount,
            (float) $loanProduct->interest_rate,
            $termMonths,
            (float) $loanProduct->processing_fee_rate,
            (float) $loanProduct->insurance_rate,
            $firstDue,
        );

        $schedule = array_map(fn($row) => $row + [
            'installment' => $row['payment_amount'],
            'm_interest'  => $row['interest_amount'],
        ], $c['schedule']);

        $first = $schedule[0];
        $last  = $schedule[count($schedule) - 1];

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
                'monthly_rate'        => round($loanProduct->interest_rate / 12, 4),
                'annual_rate'         => round((float) $loanProduct->interest_rate, 4),
                'principal_per_month' => $first['principal_amount'],
                'm_interest'          => $first['interest_amount'],
                'monthly_payment'     => $c['monthly_payment'],
                'total_interest'      => $c['total_interest'],
                'total_repayment'     => $c['total_repayable'],
                'processing_fee'      => $c['processing_fee'],
                'insurance_fee'       => $c['insurance_fee'],
                'total_fees'          => $c['total_fees'],
                'total_cost_of_loan'  => round($c['total_repayable'] + $c['total_fees'], 2),
                'net_disbursement'    => $c['net_disbursement'],
            ],
            'summary' => [
                'total_payments'       => $termMonths,
                'total_principal_paid' => round($principalAmount, 2),
                'total_interest_paid'  => $c['total_interest'],
                'first_payment_date'   => $first['payment_date'],
                'last_payment_date'    => $last['payment_date'],
            ],
            'amortization_schedule' => $schedule,
        ];
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
