<?php

namespace App\Services\Loans;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use DomainException;


class LoanCalculator
{
    public function toCents(float|int|string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    public function installmentCents(int $principalCents, float $annualRatePercent, int $months): int
    {
        if ($principalCents <= 0 || $months <= 0) {
            throw new DomainException('Loan amount and term must be greater than zero.');
        }

        $r = $annualRatePercent / 100 / 12;

        if ($r <= 0) {
            return (int) round($principalCents / $months);
        }

        $factor = pow(1 + $r, $months);

        return (int) round($principalCents * ($r * $factor) / ($factor - 1));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function schedule(
        float|int|string $principal,
        float $annualRatePercent,
        int $months,
        CarbonInterface|string|null $firstDueDate = null,
    ): array {
        $balance     = $this->toCents($principal);
        $installment = $this->installmentCents($balance, $annualRatePercent, $months);
        $r           = $annualRatePercent / 100 / 12;
        $first       = $firstDueDate
            ? Carbon::parse($firstDueDate)->startOfDay()
            : now()->startOfDay()->addMonthNoOverflow();

        $rows = [];
        $cumInterest = 0;
        $cumPrincipal = 0;

        for ($i = 1; $i <= $months; $i++) {
            $interest = (int) round($balance * $r);

            if ($i === $months) {
                $principalPart = $balance;                    
            } else {
                $principalPart = min($installment - $interest, $balance);
            }

            $payment = $principalPart + $interest;
            $closing = $balance - $principalPart;

            $cumInterest  += $interest;
            $cumPrincipal += $principalPart;

            $rows[] = [
                'payment_number'       => $i,
                'payment_date'         => $first->copy()->addMonthsNoOverflow($i - 1)->toDateString(),
                'opening_balance'      => $balance / 100,
                'principal_amount'     => $principalPart / 100,
                'interest_amount'      => $interest / 100,
                'payment_amount'       => $payment / 100,
                'closing_balance'      => $closing / 100,
                'cumulative_interest'  => $cumInterest / 100,
                'cumulative_principal' => $cumPrincipal / 100,
            ];

            $balance = $closing;
        }

        return $rows;
    }

    /**
     * Everything the application, approval and calculator screens need, in one place.
     */
    public function summary(
        float|int|string $principal,
        float $annualRatePercent,
        int $months,
        float $processingFeeRatePercent = 0.0,
        float $insuranceRatePercent = 0.0,
        CarbonInterface|string|null $firstDueDate = null,
    ): array {
        $schedule = $this->schedule($principal, $annualRatePercent, $months, $firstDueDate);

        $principalAmount = round((float) $principal, 2);
        $totalInterest   = round(array_sum(array_column($schedule, 'interest_amount')), 2);
        $processingFee   = round($principalAmount * $processingFeeRatePercent / 100, 2);
        $insuranceFee    = round($principalAmount * $insuranceRatePercent / 100, 2);
        $totalFees       = round($processingFee + $insuranceFee, 2);

        return [
            'monthly_payment'  => $schedule[0]['payment_amount'],
            'total_interest'   => $totalInterest,
            'total_repayable'  => round($principalAmount + $totalInterest, 2),
            'processing_fee'   => $processingFee,
            'insurance_fee'    => $insuranceFee,
            'total_fees'       => $totalFees,
            'net_disbursement' => round($principalAmount - $totalFees, 2),
            'schedule'         => $schedule,
        ];
    }
}