<?php

namespace Database\Factories;

use App\Models\ClientUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClientUserFactory extends Factory
{
    protected $model = ClientUser::class;

    public function definition(): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->optional(0.8)->phoneNumber(),
            'cpf' => $this->faker->optional(0.6)->numerify('###.###.###-##'),
            'birth_date' => $this->faker->optional(0.7)->dateTimeBetween('-80 years', '-18 years'),
            'password' => bcrypt('password'),
            'google_id' => $this->faker->optional(0.3)->uuid(),
            'email_verified_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 year', 'now'),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'status_updated_at' => now(),
            'status_reason' => null,
            'status_updated_by' => null,
            'remember_token' => null,
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Indicate that the user's email address should be verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Indicate that the user's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user should be active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'updated_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the user should be inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the user should be new this month.
     */
    public function newThisMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween(now()->startOfMonth(), now()),
        ]);
    }

    /**
     * Indicate that the user should be old (created more than a month ago).
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-2 years', now()->subMonth()),
        ]);
    }
}
