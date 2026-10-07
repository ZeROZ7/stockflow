<?php

use App\Models\Branch;
use App\Models\Product;
use App\Models\PurchaseOrder;

describe('products', function () {
    it('lists products and filters by name or SKU', function () {
        Product::factory()->create(['sku' => 'CAF-001', 'name' => 'Café en grano']);
        Product::factory()->create(['sku' => 'PAN-001', 'name' => 'Pan amasado']);

        $this->actingAs(staff())
            ->getJson('/api/products?search=caf')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sku', 'CAF-001');
    });

    it('lets a manager create a product', function () {
        $this->actingAs(manager())
            ->postJson('/api/products', ['sku' => 'ver-003', 'name' => 'Lechuga', 'unit' => 'unit', 'min_stock' => 30])
            ->assertCreated()
            ->assertJsonPath('data.sku', 'VER-003');

        $this->assertDatabaseHas('products', ['sku' => 'VER-003', 'min_stock' => 30]);
    });

    it('validates product input', function () {
        Product::factory()->create(['sku' => 'DUP-001']);

        $this->actingAs(manager())
            ->postJson('/api/products', ['sku' => 'DUP-001', 'unit' => 'bucket', 'min_stock' => -1])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sku', 'name', 'unit', 'min_stock']);
    });

    it('lets a manager update and delete a product', function () {
        $product = Product::factory()->create();
        $manager = manager();

        $this->actingAs($manager)
            ->putJson("/api/products/{$product->id}", ['sku' => $product->sku, 'name' => 'Renamed', 'unit' => 'kg', 'min_stock' => 3])
            ->assertOk()
            ->assertJsonPath('data.name', 'Renamed');

        $this->actingAs($manager)->deleteJson("/api/products/{$product->id}")->assertNoContent();
        $this->assertModelMissing($product);
    });

    it('refuses to delete a product used in purchase orders', function () {
        $product = Product::factory()->create();
        PurchaseOrder::factory()->withLines([$product->id => 5])->create();

        $this->actingAs(manager())->deleteJson("/api/products/{$product->id}")->assertConflict();
    });

    it('forbids staff from changing the catalog', function () {
        $product = Product::factory()->create();

        $this->actingAs(staff())->postJson('/api/products', ['sku' => 'X-1', 'name' => 'X', 'unit' => 'kg', 'min_stock' => 1])->assertForbidden();
        $this->actingAs(staff())->deleteJson("/api/products/{$product->id}")->assertForbidden();
    });
});

describe('branches', function () {
    it('lets a manager create a branch and staff list them', function () {
        $this->actingAs(manager())
            ->postJson('/api/branches', ['code' => 'lse-01', 'name' => 'La Serena', 'city' => 'La Serena'])
            ->assertCreated()
            ->assertJsonPath('data.code', 'LSE-01');

        $this->actingAs(staff())->getJson('/api/branches')->assertOk()->assertJsonCount(1, 'data');
    });

    it('forbids staff from creating branches', function () {
        $this->actingAs(staff())
            ->postJson('/api/branches', ['code' => 'LSE-01', 'name' => 'La Serena', 'city' => 'La Serena'])
            ->assertForbidden();
    });

    it('refuses to delete a branch with purchase orders', function () {
        $order = PurchaseOrder::factory()->create();

        $this->actingAs(manager())->deleteJson("/api/branches/{$order->branch_id}")->assertConflict();
    });

    it('shows stock per product for a branch, counting missing rows as zero', function () {
        $branch = Branch::factory()->create();
        $flour = Product::factory()->create(['name' => 'A flour', 'min_stock' => 10]);
        $oil = Product::factory()->create(['name' => 'B oil', 'min_stock' => 5]);
        $branch->stocks()->create(['product_id' => $flour->id, 'quantity' => 25]);

        $this->actingAs(staff())
            ->getJson("/api/branches/{$branch->id}/stock")
            ->assertOk()
            ->assertJsonPath('data.0.quantity', 25)
            ->assertJsonPath('data.0.is_low', false)
            ->assertJsonPath('data.1.product_id', $oil->id)
            ->assertJsonPath('data.1.quantity', 0)
            ->assertJsonPath('data.1.is_low', true);
    });
});
