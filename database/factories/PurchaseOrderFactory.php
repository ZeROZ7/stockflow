<?php

namespace Database\Factories;

use App\Domain\PurchaseOrders\PurchaseOrderStatus;
use App\Models\Branch;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'supplier' => fake()->company(),
            'status' => PurchaseOrderStatus::Draft,
            'created_by' => User::factory(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PurchaseOrderStatus::Approved,
            'approved_by' => User::factory()->manager(),
            'approved_at' => now(),
        ]);
    }

    public function received(): static
    {
        return $this->approved()->state(fn (array $attributes) => [
            'status' => PurchaseOrderStatus::Received,
            'received_by' => User::factory(),
            'received_at' => now(),
        ]);
    }

    /**
     * Attach one line per given product (or one new product).
     *
     * @param  array<int, int>  $quantities  product id => quantity
     */
    public function withLines(array $quantities = []): static
    {
        return $this->afterCreating(function (PurchaseOrder $order) use ($quantities) {
            if ($quantities === []) {
                $quantities = [Product::factory()->create()->id => 10];
            }

            foreach ($quantities as $productId => $quantity) {
                $order->lines()->create([
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'unit_cost' => 1000,
                ]);
            }
        });
    }
}
