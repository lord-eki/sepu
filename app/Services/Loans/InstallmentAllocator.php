<?php

namespace App\Services\Loans;

final class InstallmentAllocator
{
    // Splits a payment into penalty, then interest, then principal for one instalment, allowing for what it already received.
    public static function split(float $amount, float $penalty, float $interest, float $principal, float $alreadyPaid): array
    {
        $penaltyLeft = max(0, $penalty - $alreadyPaid);
        $afterPenalty = max(0, $alreadyPaid - $penalty);
        $interestLeft = max(0, $interest - $afterPenalty);
        $afterInterest = max(0, $afterPenalty - $interest);
        $principalLeft = max(0, $principal - $afterInterest);

        $penaltyPaid = round(min($amount, $penaltyLeft), 2);
        $interestPaid = round(min($amount - $penaltyPaid, $interestLeft), 2);
        $principalPaid = round(min($amount - $penaltyPaid - $interestPaid, $principalLeft), 2);

        return [
            'penalty' => $penaltyPaid,
            'interest' => $interestPaid,
            'principal' => $principalPaid,
            'unallocated' => round($amount - $penaltyPaid - $interestPaid - $principalPaid, 2),
        ];
    }
}