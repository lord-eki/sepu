<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Member::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'membership_id' => 'MEM' . str_pad($this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'middle_name' => $this->faker->optional()->firstName(),
            'id_number' => $this->faker->unique()->numerify('########'),
            'id_type' => $this->faker->randomElement(['national_id', 'passport', 'driving_license']),
            'date_of_birth' => $this->faker->date('Y-m-d', '2000-01-01'),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'marital_status' => $this->faker->randomElement(['single', 'married', 'divorced', 'widowed']),
            'occupation' => $this->faker->optional()->jobTitle(),
            'employer' => $this->faker->optional()->company(),
            'monthly_income' => $this->faker->optional()->randomFloat(2, 10000, 500000),
            'physical_address' => $this->faker->address(),
            'postal_address' => $this->faker->optional()->postcode() . ' ' . $this->faker->optional()->city(),
            'city' => $this->faker->city(),
            'county' => $this->faker->state(),
            'country' => 'Kenya',
            'emergency_contact_name' => $this->faker->name(),
            'emergency_contact_phone' => $this->faker->phoneNumber(),
            'emergency_contact_relationship' => $this->faker->randomElement(['spouse', 'parent', 'sibling', 'friend', 'relative']),
            'membership_status' => 'active',
            'membership_date' => $this->faker->date('Y-m-d', 'now'),
            'profile_photo' => $this->faker->optional()->imageUrl(200, 200, 'people'),
            'documents' => null,
        ];
    }

    /**
     * Indicate that the member is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'membership_status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the member is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'membership_status' => 'suspended',
        ]);
    }

    /**
     * Indicate that the member is terminated.
     */
    public function terminated(): static
    {
        return $this->state(fn (array $attributes) => [
            'membership_status' => 'terminated',
        ]);
    }

    /**
     * Configure the model factory with specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}