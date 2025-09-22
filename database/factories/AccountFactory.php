<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $accountType = $this->faker->randomElement(['savings', 'shares', 'deposits']);
        $prefix = match ($accountType) {
            'savings' => 'SAV',
            'shares' => 'SHR',
            'deposits' => 'DEP',
            default => 'ACC'
        };


        $balance = $this->faker->randomFloat(2, 0, 100000);

        return [
            'member_id' => Member::factory(),
            'account_number' => $prefix . str_pad($this->faker->unique()->numberBetween(1000000, 9999999), 7, '0', STR_PAD_LEFT),
            'account_type' => $accountType,
            'balance' => $balance,
            'available_balance' => $balance,
            'is_active' => true,
            'last_transaction_at' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the account is a savings account.
     */
    public function savings(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => 'savings',
            'account_number' => 'SAV' . str_pad($this->faker->unique()->numberBetween(1000000, 9999999), 7, '0', STR_PAD_LEFT),
        ]);
    }

    /**
     * Indicate that the account is a shares account.
     */
    public function shares(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => 'shares',
            'account_number' => 'SHR' . str_pad($this->faker->unique()->numberBetween(1000000, 9999999), 7, '0', STR_PAD_LEFT),
        ]);
    }

    /**
     * Indicate that the account is a deposits account.
     */
    public function deposits(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => 'deposits',
            'account_number' => 'DEP' . str_pad($this->faker->unique()->numberBetween(1000000, 9999999), 7, '0', STR_PAD_LEFT),
        ]);
    }

    /**
     * Indicate that the account is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the account has zero balance.
     */
    public function zeroBalance(): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => 0,
            'available_balance' => 0,
        ]);
    }

    /**
     * Set a specific balance for the account.
     */
    public function withBalance(float $balance): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => $balance,
            'available_balance' => $balance,
        ]);
    }

    /**
     * Configure the account for a specific member.
     */
    public function forMember(Member $member): static
    {
        return $this->state(fn (array $attributes) => [
            'member_id' => $member->id,
        ]);
    }
}