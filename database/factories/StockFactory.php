<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stock>
 */
class StockFactory extends Factory
{
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(0, 100),
        ];
    }
}
