<?php

namespace App\Services\Loans;

use App\Models\Loan;
use App\Models\LoanProduct;

class TopUpEligibilityService
{
    public function __construct(private LoanSettlementService $settlements) {}

    // Checks whether a running loan may be topped up under a product and lists the reasons when it may not.
    public function check(Loan $parent, ?LoanProduct $product = null): array
    {
        $product ??= $parent->loanProduct;
        $progress = $this->progress($parent);
        $reasons = [];

        if (! $product->allow_top_up) {
            $reasons[] = 'This loan product does not allow top-ups.';
        }

        if ((int) $product->id !== (int) $parent->loan_product_id) {
            $reasons[] = 'A top-up must use the same loan product as the loan it tops up.';
        }

        if ($parent->status !== 'active') {
            $reasons[] = 'Only an active loan can be topped up.';
        }

        if ($progress < (float) $product->top_up_eligibility_percent) {
            $reasons[] = "At least {$product->top_up_eligibility_percent}% of the principal must be repaid before a top-up; {$progress}% is repaid.";
        }

        if ($this->topUpDepth($parent) >= (int) $product->max_top_ups) {
            $reasons[] = 'The maximum number of top-ups for this loan has been reached.';
        }

        if (Loan::where('parent_loan_id', $parent->id)->whereIn('status', Loan::OPEN_STATUSES)->exists()) {
            $reasons[] = 'A top-up application for this loan is already in progress.';
        }

        return [
            'eligible' => $reasons === [],
            'reasons' => $reasons,
            'progress_percent' => $progress,
            'settlement_amount' => $product->settle_existing_balance ? $this->settlements->quote($parent)['total'] : 0.0,
            'max_top_up_amount' => $product->max_top_up_amount !== null ? (float) $product->max_top_up_amount : null,
        ];
    }

    // Returns the share of the original principal already repaid, as a percentage.
    private function progress(Loan $loan): float
    {
        $original = (float) ($loan->approved_amount ?: $loan->applied_amount);

        if ($original <= 0) {
            return 0.0;
        }

        $repaid = $original - (float) $loan->principal_balance;

        return round(max(0, min(100, ($repaid / $original) * 100)), 2);
    }

    // Counts how many top-up links sit above the loan, which is how many top-ups its chain already has.
    private function topUpDepth(Loan $loan): int
    {
        $depth = 0;
        $current = $loan;

        while ($current && $current->parent_loan_id && $depth < 20) {
            $depth++;
            $current = Loan::find($current->parent_loan_id);
        }

        return $depth;
    }
}