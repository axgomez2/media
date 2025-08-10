<?php

namespace Database\Factories;

use App\Models\NewsTopic;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsTopicFactory extends Factory
{
    protected $model = NewsTopic::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'slug' => $this->faker->unique()->slug(),
            'color' => $this->faker->hexColor(),
            'description' => $this->faker->optional()->paragraph(),
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
