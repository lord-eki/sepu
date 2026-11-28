<?php

namespace Tests\Support;

use App\Models\Account;
use App\Models\ChartOfAccount;
use App\Models\Loan;
use App\Models\LoanMigrationBatch;
use App\Models\LoanMigrationRecord;
use App\Models\LoanProduct;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\MemberFinanceConfig;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class Scenario
{
    private static int $sequence = 0;

    // Creates an active, verified user with the given role.
    public static function user(string $role = 'member', array $attributes = []): User
    {
        $n = ++self::$sequence;

        $user = (new User())->forceFill(array_merge([
            'name' => ucfirst($role) . " {$n}",
            'email' => "{$role}{$n}@example.test",
            'username' => "{$role}{$n}",
            'phone' => '+2547' . str_pad((string) (10000000 + $n), 8, '0', STR_PAD_LEFT),
            'password' => Hash::make('password'),
            'role' => $role,
            'is_active' => true,
            'must_change_password' => false,
            'email_verified_at' => now(),
        ], $attributes));

        $user->save();

        return $user;
    }

    // Creates an active member together with the member's user login.
    public static function member(array $memberAttributes = [], array $userAttributes = []): Member
    {
        $user = self::user('member', $userAttributes);
        $n = ++self::$sequence;

        $member = (new Member())->forceFill(array_merge([
            'user_id' => $user->id,
            'membership_id' => 'MEM' . str_pad((string) $n, 6, '0', STR_PAD_LEFT),
            'first_name' => 'Test',
            'last_name' => "Member{$n}",
            'id_number' => (string) (30000000 + $n),
            'id_type' => 'national_id',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'marital_status' => 'single',
            'physical_address' => 'Test Street',
            'city' => 'Machakos',
            'county' => 'Machakos',
            'emergency_contact_name' => 'Next Of Kin',
            'emergency_contact_phone' => '+254700000000',
            'emergency_contact_relationship' => 'friend',
            'membership_date' => now()->toDateString(),
            'membership_status' => 'active',
        ], $memberAttributes));

        $member->save();
        $member->setRelation('user', $user);

        return $member;
    }

    // Creates an account of the given type for a member with the given balance.
    public static function account(Member $member, string $type = 'share_deposits', float $balance = 0): Account
    {
        $n = ++self::$sequence;

        $account = (new Account())->forceFill([
            'member_id' => $member->id,
            'account_type' => $type,
            'account_number' => strtoupper(substr($type, 0, 3)) . str_pad((string) $n, 8, '0', STR_PAD_LEFT),
            'balance' => $balance,
            'available_balance' => $balance,
            'is_active' => true,
        ]);

        $account->save();

        return $account;
    }

    // Creates the chart of accounts rows the ledger posts to.
    public static function chart(): void
    {
        $rows = [
            '11100' => ['Cash on Hand', 'asset', 'current_asset', 'debit'],
            '11201' => ['Bank', 'asset', 'current_asset', 'debit'],
            '11301' => ['Performing Loans', 'asset', 'current_asset', 'debit'],
            '11400' => ['Interest Receivable', 'asset', 'current_asset', 'debit'],
            '21100' => ['Members Deposits', 'liability', 'current_liability', 'credit'],
            '21300' => ['Accounts Payable', 'liability', 'current_liability', 'credit'],
            '21700' => ['Dividends Payable', 'liability', 'current_liability', 'credit'],
            '31000' => ['Share Capital', 'equity', 'member_equity', 'credit'],
            '41100' => ['Interest Income', 'revenue', 'operating_revenue', 'credit'],
            '42100' => ['Entrance Fees', 'revenue', 'operating_revenue', 'credit'],
            '42200' => ['Processing Fees', 'revenue', 'operating_revenue', 'credit'],
            '42400' => ['Penalty Charges', 'revenue', 'operating_revenue', 'credit'],
            '42500' => ['Withdrawal Fees', 'revenue', 'operating_revenue', 'credit'],
            '33000' => ['Retained Earnings', 'equity', 'member_equity', 'credit'],
        ];

        foreach ($rows as $code => [$name, $type, $category, $normal]) {
            (new ChartOfAccount())->forceFill([
                'account_code' => $code,
                'account_name' => $name,
                'account_type' => $type,
                'account_category' => $category,
                'normal_balance' => $normal,
            ])->save();
        }
    }

    // Creates a member's finance configuration with contribution and loan deduction switched on.
    public static function financeConfig(Member $member, array $attributes = []): MemberFinanceConfig
    {
        $config = (new MemberFinanceConfig())->forceFill(array_merge([
            'member_id' => $member->id,
            'contribution_active' => true,
            'monthly_contribution' => 0,
            'loan_auto_deduct' => true,
        ], $attributes));

        $config->save();

        return $config;
    }

    // Creates an active loan with its principal on the member's loan account and the given instalments.
    public static function loan(Member $member, int $instalments = 2): Loan
    {
        $n = ++self::$sequence;

        $product = self::loanProduct();

        $loan = (new Loan())->forceFill([
            'loan_number' => 'LN' . str_pad((string) $n, 6, '0', STR_PAD_LEFT),
            'member_id' => $member->id,
            'loan_product_id' => $product->id,
            'applied_amount' => 1000 * $instalments,
            'interest_rate' => 12,
            'term_months' => $instalments,
            'purpose' => 'Test loan',
            'application_date' => now()->toDateString(),
            'status' => 'active',
            'principal_balance' => 1000 * $instalments,
            'interest_balance' => 100 * $instalments,
            'penalty_balance' => 0,
            'outstanding_balance' => 1100 * $instalments,
        ]);
        $loan->save();

        for ($i = 0; $i < $instalments; $i++) {
            (new LoanRepayment())->forceFill([
                'loan_id' => $loan->id,
                'due_date' => now()->setDate(2026, 11 + $i, 1)->toDateString(),
                'expected_amount' => 1100,
                'principal_amount' => 1000,
                'interest_amount' => 100,
                'outstanding_amount' => 1100,
                'status' => 'pending',
            ])->save();
        }

        self::account($member, 'loan_outstanding', 1000 * $instalments);

        return $loan->refresh();
    }

    // Creates a loan product usable by loans and migration records.
    public static function loanProduct(): LoanProduct
    {
        $n = ++self::$sequence;

        $product = (new LoanProduct())->forceFill([
            'name' => "Product {$n}",
            'code' => "PRD{$n}",
            'min_amount' => 1000,
            'max_amount' => 1000000,
            'interest_rate' => 12,
            'min_term_months' => 1,
            'max_term_months' => 60,
        ]);
        $product->save();

        return $product;
    }

    // Creates a draft migration batch whose records each belong to a new active member.
    public static function migrationBatch(User $creator, int $records = 2): LoanMigrationBatch
    {
        $n = ++self::$sequence;
        $product = self::loanProduct();

        $batch = (new LoanMigrationBatch())->forceFill([
            'batch_number' => "MIG-TEST-{$n}",
            'status' => 'draft',
            'created_by' => $creator->id,
        ]);
        $batch->save();

        for ($i = 0; $i < $records; $i++) {
            $member = self::member();

            (new LoanMigrationRecord())->forceFill([
                'batch_id' => $batch->id,
                'member_id' => $member->id,
                'member_number' => $member->membership_id,
                'loan_number' => 'MLN' . ++self::$sequence,
                'loan_product_id' => $product->id,
                'original_loan_amount' => 10000,
                'date_disbursed' => '2026-01-01',
                'interest_rate' => 12,
                'repayment_period' => 12,
                'remaining_period' => 6,
                'outstanding_balance' => 6000,
                'total_amount_paid' => 4000,
                'last_repayment_date' => '2026-11-01',
                'next_due_date' => '2026-12-01',
                'loan_status' => 'active',
                'is_top_up' => false,
            ])->save();
        }

        $batch->update([
            'total_records' => $records,
            'total_original_amount' => 10000 * $records,
            'total_amount_paid' => 4000 * $records,
            'total_outstanding_balance' => 6000 * $records,
        ]);

        return $batch->refresh();
    }
}