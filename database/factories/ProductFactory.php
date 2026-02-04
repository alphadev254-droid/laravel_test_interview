<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['beauty', 'electronics', 'furniture', 'groceries', 'home-decoration', 'fragrances', 'laptops', 'smartphones'];

        return [
            'title' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement($categories),
            'price' => fake()->randomFloat(2, 5, 500),
            'discount_percentage' => fake()->optional(0.7)->randomFloat(2, 5, 20),
            'rating' => fake()->randomFloat(2, 3, 5),
            'stock' => fake()->numberBetween(10, 200),
            'thumbnail_path' => null,
            'created_by' => 1,
        ];
    }
}
