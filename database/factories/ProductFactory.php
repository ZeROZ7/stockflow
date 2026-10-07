<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sku' => strtoupper(fake()->unique()->bothify('???-####')),
            'name' => ucfirst(fake()->words(2, true)),
            'unit' => fake()->randomElement(['unit', 'kg', 'l', 'box']),
            'min_stock' => fake()->numberBetween(5, 50),
        ];
    }
}
