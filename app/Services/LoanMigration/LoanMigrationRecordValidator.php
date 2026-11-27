<?php

namespace App\Services\LoanMigration;

use App\Models\Loan;
use App\Models\LoanMigrationRecord;
use App\Models\LoanProduct;
use App\Models\Member;

class LoanMigrationRecordValidator
{
    // Returns field => messages for everything wrong with a record, or an empty array when it is valid.
    public function errors(LoanMigrationRecord $record): array
    {
        return array_merge_recursive(
            $this->memberErrors($record),
            $this->productErrors($record),
            $this->loanNumberErrors($record),
            $this->amountErrors($record),
            $this->termErrors($record),
            $this->statusErrors($record),
            $this->parentErrors($record),
        );
    }

    // Checks the record points at an active member whose number matches.
    private function memberErrors(LoanMigrationRecord $record): array
    {
        $member = $record->member_id ? Member::find($record->member_id) : null;

        if (! $member || $member->membership_id !== trim((string) $record->member_number)) {
            return ['member_number' => ['Member number does not match a member.']];
        }

        if ($member->membership_status !== 'active') {
            return ['member_number' => ['The member is not active.']];
        }

        return [];
    }

    // Checks the loan product exists.
    private function productErrors(LoanMigrationRecord $record): array
    {
        if (LoanProduct::whereKey($record->loan_product_id)->exists()) {
            return [];
        }

        return ['loan_product_id' => ['Loan product does not exist.']];
    }

    // Checks the loan number is present and not used by an existing loan or another migration record.
    private function loanNumberErrors(LoanMigrationRecord $record): array
    {
        $number = trim((string) $record->loan_number);

        if ($number === '') {
            return ['loan_number' => ['Loan number is required.']];
        }

        if (Loan::where('loan_number', $number)->exists()) {
            return ['loan_number' => ['Loan number already belongs to an existing loan.']];
        }

        $repeated = LoanMigrationRecord::where('loan_number', $number)->where('id', '!=', $record->id)->exists();

        return $repeated ? ['loan_number' => ['Loan number is used by another migration record.']] : [];
    }

    // Checks the money fields, the interest rate and the disbursement date.
    private function amountErrors(LoanMigrationRecord $record): array
    {
        $errors = [];
        $original = (float) $record->original_loan_amount;
        $outstanding = (float) $record->outstanding_balance;
        $rate = (float) $record->interest_rate;

        if ($original <= 0) {
            $errors['original_loan_amount'][] = 'Original loan amount must be greater than zero.';
        }

        if ($outstanding < 0 || $outstanding > $original) {
            $errors['outstanding_balance'][] = 'Outstanding balance must be between zero and the original amount.';
        }

        if ((float) $record->total_amount_paid < 0) {
            $errors['total_amount_paid'][] = 'Total amount paid cannot be negative.';
        }

        if ($rate < 0 || $rate > 100) {
            $errors['interest_rate'][] = 'Interest rate must be between 0 and 100.';
        }

        if ($record->date_disbursed && $record->date_disbursed->isFuture()) {
            $errors['date_disbursed'][] = 'Disbursement date cannot be in the future.';
        }

        return $errors;
    }

    // Checks the repayment period, the remaining period and the next due date.
    private function termErrors(LoanMigrationRecord $record): array
    {
        $errors = [];

        if ((int) $record->repayment_period < 1) {
            $errors['repayment_period'][] = 'Repayment period must be at least one month.';
        }

        if ((int) $record->remaining_period > (int) $record->repayment_period) {
            $errors['remaining_period'][] = 'Remaining period cannot exceed the repayment period.';
        }

        if ((float) $record->outstanding_balance > 0 && (int) $record->remaining_period < 1) {
            $errors['remaining_period'][] = 'A loan with a balance needs at least one month remaining.';
        }

        if ((float) $record->outstanding_balance > 0 && ! $record->next_due_date) {
            $errors['next_due_date'][] = 'Next due date is required while a balance is outstanding.';
        }

        return $errors;
    }

    // Checks the legacy status is one the new system can hold and agrees with the balance.
    private function statusErrors(LoanMigrationRecord $record): array
    {
        $status = strtolower(trim((string) $record->loan_status));
        $outstanding = (float) $record->outstanding_balance;

        if ($status === 'restructured') {
            return ['loan_status' => ['Restructured loans are not supported yet. Migrate them as active with their current terms.']];
        }

        if (! in_array($status, ['active', 'cleared'], true)) {
            return ['loan_status' => ['Loan status must be Active or Cleared.']];
        }

        if ($status === 'cleared' && $outstanding > 0) {
            return ['loan_status' => ['A cleared loan cannot have an outstanding balance.']];
        }

        if ($status === 'active' && $outstanding <= 0) {
            return ['loan_status' => ['An active loan must have an outstanding balance.']];
        }

        return [];
    }

    // Checks a top-up names a parent loan that exists and belongs to the same member.
    private function parentErrors(LoanMigrationRecord $record): array
    {
        if (! $record->is_top_up) {
            return [];
        }

        $parent = trim((string) $record->parent_loan_number);

        if ($parent === '') {
            return ['parent_loan_number' => ['Parent loan number is required for a top-up.']];
        }

        $owner = Loan::where('loan_number', $parent)->value('member_id')
            ?? LoanMigrationRecord::where('loan_number', $parent)->where('id', '!=', $record->id)->value('member_id');

        if ($owner === null) {
            return ['parent_loan_number' => ['Parent loan was not found.']];
        }

        return (int) $owner === (int) $record->member_id
            ? []
            : ['parent_loan_number' => ['Parent loan belongs to a different member.']];
    }
}