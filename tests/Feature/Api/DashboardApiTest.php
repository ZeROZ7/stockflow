<?php

use App\Models\Branch;
use App\Models\Product;
use App\Models\PurchaseOrder;

it('summarises the operation and lists low-stock items', function () {
    $branch = Branch::factory()->create(['name' => 'Valparaíso Centro']);
    $low = Product::factory()->create(['sku' => 'LOW-1', 'min_stock' => 20]);
    $ok = Product::factory()->create(['sku' => 'OK-1', 'min_stock' => 5]);
    $branch->stocks()->createMany([
        ['product_id' => $low->id, 'quantity' => 4],
        ['product_id' => $ok->id, 'quantity' => 50],
    ]);
    PurchaseOrder::factory()->withLines([$low->id => 10])->create(['branch_id' => $branch->id]);
    PurchaseOrder::factory()->received()->withLines([$ok->id => 10])->create(['branch_id' => $branch->id]);

    $this->actingAs(staff())
        ->getJson('/api/dashboard')
        ->assertOk()
        ->assertJsonPath('data.stats.branches', 1)
        ->assertJsonPath('data.stats.products', 2)
        ->assertJsonPath('data.stats.open_purchase_orders', 1)
        ->assertJsonPath('data.stats.low_stock_items', 1)
        ->assertJsonPath('data.low_stock.0.sku', 'LOW-1')
        ->assertJsonPath('data.low_stock.0.shortage', 16)
        ->assertJsonCount(2, 'data.recent_purchase_orders');
});
