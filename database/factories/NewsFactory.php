<?php

namespace Database\Factories;

use App\Models\News;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'slug' => $this->faker->unique()->slug(),
            'excerpt' => $this->faker->paragraph(2),
            'content' => $this->faker->paragraphs(5, true),
            'featured_image' => null,
            'gallery_images' => null,
            'topics' => null,
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            'meta_description' => $this->faker->sentence(),
            'meta_keywords' => implode(', ', $this->faker->words(5)),
            'published_at' => $this->faker->optional()->dateTimeBetween('-1 month', '+1 month'),
            'author_id' => User::factory(),
            'views_count' => $this->faker->numberBetween(0, 1000),
            'featured' => $this->faker->boolean(20), // 20% chance of being featured
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured' => true,
        ]);
    }

    public function withImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured_image' => 'news/featured-' . $this->faker->uuid() . '.jpg',
        ]);
    }

    public function withGallery(): static
    {
        return $this->state(fn (array $attributes) => [
            'gallery_images' => [
                'news/gallery-' . $this->faker->uuid() . '.jpg',
                'news/gallery-' . $this->faker->uuid() . '.jpg',
                'news/gallery-' . $this->faker->uuid() . '.jpg',
            ],
        ]);
    }
}
