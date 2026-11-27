<?php

namespace App\Policies;

use App\Models\LoanMigrationBatch;
use App\Models\User;

class LoanMigrationBatchPolicy
{
    // Staff who may open the migration module.
    public function access(User $user): bool
    {
        return in_array($user->role, ['admin', 'loan_officer', 'accountant'], true);
    }

    // Staff who may capture, import, validate and submit batches.
    public function capture(User $user, ?LoanMigrationBatch $batch = null): bool
    {
        return in_array($user->role, ['admin', 'loan_officer'], true);
    }

    // The Accounts Officer sign-off, played by the accountant role, by someone other than the batch creator or submitter.
    public function verify(User $user, LoanMigrationBatch $batch): bool
    {
        return in_array($user->role, ['admin', 'accountant'], true)
            && $this->isAnotherPerson($user, [$batch->created_by, $batch->submitted_by]);
    }

    // Final approval, by someone other than the batch creator, submitter or verifier.
    public function approve(User $user, LoanMigrationBatch $batch): bool
    {
        return in_array($user->role, ['admin', 'loan_officer'], true)
            && $this->isAnotherPerson($user, [$batch->created_by, $batch->submitted_by, $batch->verified_by]);
    }

    // Staff who may turn a submitted batch down.
    public function reject(User $user, LoanMigrationBatch $batch): bool
    {
        return $this->access($user);
    }

    // Applies the maker-checker rule when it is switched on in the configuration.
    private function isAnotherPerson(User $user, array $others): bool
    {
        return ! config('sepu.maker_checker') || ! in_array($user->id, array_filter($others), true);
    }
}