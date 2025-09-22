<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $transactionTypes = [
            'deposit', 'withdrawal', 'share_purchase', 'share_sale',
            'share_transfer_in', 'share_transfer_out', 'loan_disbursement',
            'loan_repayment', 'dividend_payment', 'account_opening', 'account_closure'
        ];

        $amount = $this->faker->randomFloat(2, 10, 10000);
        $balanceBefore = $this->faker->randomFloat(2, 0, 50000);
        $balanceAfter = $balanceBefore + $amount;

        return [
            'transaction_id' => 'TXN' . date('Ymd') . str_pad($this->faker->unique()->numberBetween(100000, 999999), 6, '0', STR_PAD_LEFT),
            'account_id' => Account::factory(),
            'member_id' => Member::factory(),
            'transaction_type' => $this->faker->randomElement($transactionTypes),
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'description' => $this->faker->sentence(),
            'payment_method' => $this->faker->randomElement(['cash', 'mobile_money', 'bank_transfer', 'cheque', 'system_transfer']),
            'payment_reference' => $this->faker->optional()->regexify('[A-Z]{3}[0-9]{6}'),
            'status' => 'completed',
            'processed_by' => User::factory(),
            'processed_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the transaction is a deposit.
     */
    public function deposit(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_type' => 'deposit',
            'description' => 'Deposit transaction',
        ]);
    }

    /**
     * Indicate that the transaction is a withdrawal.
     */
    public function withdrawal(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_type' => 'withdrawal',
            'description' => 'Withdrawal transaction',
            'balance_after' => $attributes['balance_before'] - $attributes['amount'],
        ]);
    }

    /**
     * Indicate that the transaction is a share purchase.
     */
    public function sharePurchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_type' => 'share_purchase',
            'description' => 'Share purchase transaction',
        ]);
    }

    /**
     * Indicate that the transaction is a share sale.
     */
    public function shareSale(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_type' => 'share_sale',
            'description' => 'Share sale transaction',
            'balance_after' => $attributes['balance_before'] - $attributes['amount'],
        ]);
    }

    /**
     * Indicate that the transaction is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'processed_at' => null,
        ]);
    }

    /**
     * Indicate that the transaction failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }

    /**
     * Indicate that the transaction was reversed.
     */
    public function reversed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'reversed',
        ]);
    }

    /**
     * Set a specific amount for the transaction.
     */
    public function withAmount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
            'balance_after' => $attributes['balance_before'] + $amount,
        ]);
    }

    /**
     * Configure the transaction for a specific account.
     */
    public function forAccount(Account $account): static
    {
        return $this->state(fn (array $attributes) => [
            'account_id' => $account->id,
            'member_id' => $account->member_id,
        ]);
    }

    /**
     * Configure the transaction with specific balance values.
     */
    public function withBalances(float $balanceBefore, float $balanceAfter): static
    {
        return $this->state(fn (array $attributes) => [
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'amount' => $balanceAfter - $balanceBefore,
        ]);
    }
}