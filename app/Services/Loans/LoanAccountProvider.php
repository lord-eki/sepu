<?php

namespace App\Services\Loans;

use App\Models\Account;

class LoanAccountProvider
{
    // Returns the member's loan account, creating it with a fresh account number when it does not exist.
    public function forMember(int $memberId): Account
    {
        $account = Account::where('member_id', $memberId)->where('account_type', 'loan_outstanding')->first();

        if ($account) {
            return $account;
        }

        do {
            $number = '01' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Account::where('account_number', $number)->exists());

        return Account::create([
            'member_id' => $memberId,
            'account_number' => $number,
            'account_type' => 'loan_outstanding',
            'balance' => 0,
            'available_balance' => 0,
            'is_active' => true,
        ]);
    }
}