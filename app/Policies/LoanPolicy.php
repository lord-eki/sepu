<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;

class LoanPolicy
{
    private const STAFF = ['admin', 'management', 'accountant', 'loan_officer'];

    private function owns(User $user, Loan $loan): bool
    {
        return $user->role === 'member'
            && $user->member
            && (int) $user->member->id === (int) $loan->member_id;
    }

    public function view(User $user, Loan $loan): bool
    {
        return in_array($user->role, self::STAFF, true) || $this->owns($user, $loan);
    }

    public function update(User $user, Loan $loan): bool
    {
        if (in_array($user->role, ['admin', 'loan_officer'], true)) {
            return true;
        }

        return $this->owns($user, $loan) && $loan->status === 'pending';
    }
}