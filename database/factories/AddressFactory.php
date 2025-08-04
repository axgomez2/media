<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\ClientUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'user_id' => ClientUser::factory(),
            'street' => $this->faker->streetName(),
            'number' => $this->faker->buildingNumber(),
            'complement' => $this->faker->optional(0.3)->secondaryAddress(),
            'neighborhood' => $this->faker->citySuffix(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'zip_code' => $this->faker->postcode(),
            'is_default' => false,
        ];
    }

    /**
     * Indicate that the address should be the default address.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    /**
     * Create an address for a specific user.
     */
    public function forUser(ClientUser $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create an address in a specific city.
     */
    public function inCity(string $city): static
    {
        return $this->state(fn (array $attributes) => [
            'city' => $city,
        ]);
    }

    /**
     * Create an address in a specific state.
     */
    public function inState(string $state): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => $state,
        ]);
    }

    /**
     * Create an address with a specific zip code.
     */
    public function withZipCode(string $zipCode): static
    {
        return $this->state(fn (array $attributes) => [
            'zip_code' => $zipCode,
        ]);
    }
}
