<?php

namespace App\Services;

use App\Models\Member;
use App\Models\LoanProduct;
use App\Models\Account;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LoanEligibilityService
{
    const MINIMUM_SHARE_CAPITAL = 5000; // Kshs. 5,000
    const MINIMUM_MEMBERSHIP_MONTHS = 6; // 6 months
    const MAX_DEDUCTION_RATIO = 0.67; // 2/3 of gross salary

    /**
     * Check if member is eligible for a loan
     *
     * @param Member $member
     * @param LoanProduct $loanProduct
     * @param float $requestedAmount
     * @return array
     */
    public function checkEligibility(Member $member, LoanProduct $loanProduct, float $requestedAmount): array
    {
        $checks = [
            'is_active_member' => $this->isActiveMember($member),
            'has_regular_deposits' => $this->hasRegularDeposits($member),
            'meets_share_capital' => $this->meetsShareCapitalRequirement($member),
            'meets_membership_duration' => $this->meetsMembershipDuration($member),
            'has_income_source' => $this->hasIncomeSource($member),
            'has_good_credit_history' => $this->hasGoodCreditHistory($member),
            'deductions_within_limit' => $this->deductionsWithinLimit($member, $requestedAmount, $loanProduct),
        ];

        $eligible = !in_array(false, $checks, true);

        return [
            'eligible' => $eligible,
            'checks' => $checks,
            'messages' => $this->getEligibilityMessages($checks),
            'requirements' => $this->getRequirements($member, $loanProduct)
        ];
    }

    /**
     * Check if member is active
     */
    private function isActiveMember(Member $member): bool
    {
        return $member->membership_status === 'active' && 
               $member->user->is_active === true;
    }

    /**
     * Check if member has regular deposits/contributions
     */
    private function hasRegularDeposits(Member $member): bool
    {
        // Check if member has made deposits in the last 3 months
        $savingsAccount = $member->accounts()
            ->where('account_type', 'savings')
            ->first();

        if (!$savingsAccount) {
            return false;
        }

        $recentDeposits = $savingsAccount->transactions()
            ->where('transaction_type', 'deposit')
            ->where('created_at', '>=', Carbon::now()->subMonths(3))
            ->count();

        return $recentDeposits >= 2; // At least 2 deposits in last 3 months
    }

    /**
     * Check if member meets minimum share capital requirement
     */
    private function meetsShareCapitalRequirement(Member $member): bool
    {
        $sharesAccount = $member->accounts()
            ->where('account_type', 'shares')
            ->first();

        if (!$sharesAccount) {
            return false;
        }

        return $sharesAccount->balance >= self::MINIMUM_SHARE_CAPITAL;
    }

    /**
     * Check if member has been a member for at least 6 months
     */
    private function meetsMembershipDuration(Member $member): bool
    {
        if (!$member->membership_date) {
            return false;
        }

        $membershipMonths = Carbon::parse($member->membership_date)
            ->diffInMonths(Carbon::now());

        return $membershipMonths >= self::MINIMUM_MEMBERSHIP_MONTHS;
    }

    /**
     * Check if member has a regular source of income
     */
    private function hasIncomeSource(Member $member): bool
    {
        return !empty($member->occupation) && 
               !empty($member->employer) && 
               $member->monthly_income > 0;
    }

    /**
     * Check if member has good credit history (no adverse CRB listing)
     */
    private function hasGoodCreditHistory(Member $member): bool
    {
        // Check for any loans in default or with arrears > 90 days
        $badLoans = $member->loans()
            ->whereIn('status', ['defaulted', 'written_off'])
            ->exists();

        if ($badLoans) {
            return false;
        }

        // Check for loans with significant arrears
        $loansInArrears = $member->loans()
            ->where('status', 'active')
            ->where('days_in_arrears', '>', 90)
            ->exists();

        return !$loansInArrears;
    }

    /**
     * Check if total deductions don't exceed 2/3 of gross salary
     */
    private function deductionsWithinLimit(Member $member, float $requestedAmount, LoanProduct $loanProduct): bool
    {
        if ($member->monthly_income <= 0) {
            return false;
        }

        // Calculate proposed monthly repayment
        $proposedRepayment = $this->calculateMonthlyRepayment(
            $requestedAmount, 
            $loanProduct->interest_rate, 
            $loanProduct->max_term_months
        );

        // Get existing active loans total monthly repayment
        $existingLoansRepayment = $member->loans()
            ->where('status', 'active')
            ->sum('monthly_repayment');

        // Calculate total deductions
        $totalDeductions = $existingLoansRepayment + $proposedRepayment;

        // Check if deductions exceed 2/3 of gross salary
        $maxAllowedDeduction = $member->monthly_income * self::MAX_DEDUCTION_RATIO;

        return $totalDeductions <= $maxAllowedDeduction;
    }

    /**
     * Calculate monthly repayment using reducing balance method
     */
    private function calculateMonthlyRepayment(float $principal, float $annualRate, int $months): float
    {
        $monthlyRate = ($annualRate / 100) / 12;
        
        if ($monthlyRate == 0) {
            return $principal / $months;
        }

        $monthlyPayment = $principal * 
            ($monthlyRate * pow(1 + $monthlyRate, $months)) / 
            (pow(1 + $monthlyRate, $months) - 1);

        return round($monthlyPayment, 2);
    }

    /**
     * Get eligibility messages
     */
    private function getEligibilityMessages(array $checks): array
    {
        $messages = [];

        if (!$checks['is_active_member']) {
            $messages[] = 'Member account is not active';
        }

        if (!$checks['has_regular_deposits']) {
            $messages[] = 'Member must have regular deposits/contributions';
        }

        if (!$checks['meets_share_capital']) {
            $messages[] = 'Member must have minimum share capital of Kshs. ' . number_format(self::MINIMUM_SHARE_CAPITAL);
        }

        if (!$checks['meets_membership_duration']) {
            $messages[] = 'Member must have been a member for at least ' . self::MINIMUM_MEMBERSHIP_MONTHS . ' months';
        }

        if (!$checks['has_income_source']) {
            $messages[] = 'Member must have a regular source of income';
        }

        if (!$checks['has_good_credit_history']) {
            $messages[] = 'Member has adverse credit history or loans in arrears';
        }

        if (!$checks['deductions_within_limit']) {
            $messages[] = 'Total loan deductions would exceed 2/3 of gross salary';
        }

        if (empty($messages)) {
            $messages[] = 'Member is eligible for this loan';
        }

        return $messages;
    }

    /**
     * Get detailed requirements
     */
    private function getRequirements(Member $member, LoanProduct $loanProduct): array
    {
        $sharesAccount = $member->accounts()
            ->where('account_type', 'shares')
            ->first();

        $currentShareCapital = $sharesAccount ? $sharesAccount->balance : 0;
        $membershipMonths = $member->membership_date 
            ? Carbon::parse($member->membership_date)->diffInMonths(Carbon::now())
            : 0;

        $existingLoansRepayment = $member->loans()
            ->where('status', 'active')
            ->sum('monthly_repayment');

        $maxAllowedDeduction = $member->monthly_income * self::MAX_DEDUCTION_RATIO;
        $availableDeduction = $maxAllowedDeduction - $existingLoansRepayment;

        return [
            'share_capital' => [
                'required' => self::MINIMUM_SHARE_CAPITAL,
                'current' => $currentShareCapital,
                'met' => $currentShareCapital >= self::MINIMUM_SHARE_CAPITAL
            ],
            'membership_duration' => [
                'required_months' => self::MINIMUM_MEMBERSHIP_MONTHS,
                'current_months' => $membershipMonths,
                'met' => $membershipMonths >= self::MINIMUM_MEMBERSHIP_MONTHS
            ],
            'income_verification' => [
                'monthly_income' => $member->monthly_income,
                'occupation' => $member->occupation,
                'employer' => $member->employer,
                'met' => !empty($member->occupation) && $member->monthly_income > 0
            ],
            'deduction_limit' => [
                'max_allowed' => $maxAllowedDeduction,
                'current_deductions' => $existingLoansRepayment,
                'available' => $availableDeduction,
                'percentage_used' => $member->monthly_income > 0 
                    ? ($existingLoansRepayment / $member->monthly_income) * 100 
                    : 0
            ]
        ];
    }

    /**
     * Get maximum loan amount member can qualify for
     */
    public function getMaximumLoanAmount(Member $member, LoanProduct $loanProduct): float
    {
        if ($member->monthly_income <= 0) {
            return 0;
        }

        // Calculate available deduction capacity
        $existingLoansRepayment = $member->loans()
            ->where('status', 'active')
            ->sum('monthly_repayment');

        $maxAllowedDeduction = $member->monthly_income * self::MAX_DEDUCTION_RATIO;
        $availableDeduction = $maxAllowedDeduction - $existingLoansRepayment;

        if ($availableDeduction <= 0) {
            return 0;
        }

        // Calculate maximum principal based on available deduction
        $monthlyRate = ($loanProduct->interest_rate / 100) / 12;
        $months = $loanProduct->max_term_months;

        if ($monthlyRate == 0) {
            $maxPrincipal = $availableDeduction * $months;
        } else {
            $maxPrincipal = $availableDeduction * 
                (pow(1 + $monthlyRate, $months) - 1) / 
                ($monthlyRate * pow(1 + $monthlyRate, $months));
        }

        // Cap at product maximum
        if ($maxPrincipal > $loanProduct->max_amount) {
            $maxPrincipal = $loanProduct->max_amount;
        }

        // Cap at minimum if below product minimum
        if ($maxPrincipal < $loanProduct->min_amount) {
            return 0;
        }

        return round($maxPrincipal, 2);
    }
}