<?php

use App\Models\Branch;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Stock;

beforeEach(function () {
    $this->branch = Branch::factory()->create();
    $this->bread = Product::factory()->create(['min_stock' => 100]);
    $this->coffee = Product::factory()->create(['min_stock' => 5]);
});

function orderPayload(array $overrides = []): array
{
    return array_merge([
        'branch_id' => test()->branch->id,
        'supplier' => 'Panificadora del Puerto',
        'notes' => 'Deliver before 8 AM',
        'lines' => [
            ['product_id' => test()->bread->id, 'quantity' => 200, 'unit_cost' => 180],
            ['product_id' => test()->coffee->id, 'quantity' => 4, 'unit_cost' => 18_990],
        ],
    ], $overrides);
}

it('creates a draft purchase order', function () {
    $staff = staff();

    $this->actingAs($staff)
        ->postJson('/api/purchase-orders', orderPayload())
        ->assertCreated()
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.supplier', 'Panificadora del Puerto')
        ->assertJsonPath('data.total', 200 * 180 + 4 * 18_990)
        ->assertJsonPath('data.created_by', $staff->name)
        ->assertJsonCount(2, 'data.lines');

    expect(PurchaseOrder::query()->sole())
        ->created_by->toBe($staff->id)
        ->lines->toHaveCount(2);
});

it('validates purchase order input', function (array $payload, array $errors) {
    $this->actingAs(staff())
        ->postJson('/api/purchase-orders', orderPayload($payload))
        ->assertUnprocessable()
        ->assertJsonValidationErrors($errors);
})->with([
    'no lines' => [['lines' => []], ['lines']],
    'unknown branch' => [['branch_id' => 999], ['branch_id']],
    'zero quantity' => [fn () => ['lines' => [['product_id' => test()->bread->id, 'quantity' => 0, 'unit_cost' => 10]]], ['lines.0.quantity']],
    'duplicated product' => [fn () => ['lines' => [
        ['product_id' => test()->bread->id, 'quantity' => 1, 'unit_cost' => 10],
        ['product_id' => test()->bread->id, 'quantity' => 2, 'unit_cost' => 10],
    ]], ['lines.0.product_id', 'lines.1.product_id']],
]);

it('runs the full lifecycle: draft -> approved -> received, increasing stock', function () {
    Stock::factory()->create(['branch_id' => $this->branch->id, 'product_id' => $this->bread->id, 'quantity' => 30]);
    $manager = manager();

    $id = $this->actingAs(staff())->postJson('/api/purchase-orders', orderPayload())->json('data.id');

    $this->actingAs($manager)
        ->postJson("/api/purchase-orders/{$id}/approve")
        ->assertOk()
        ->assertJsonPath('data.status', 'approved')
        ->assertJsonPath('data.approved_by', $manager->name)
        ->assertJsonPath('data.can.approve', false)
        ->assertJsonPath('data.can.receive', true);

    $this->actingAs(staff())
        ->postJson("/api/purchase-orders/{$id}/receive")
        ->assertOk()
        ->assertJsonPath('data.status', 'received');

    $stock = fn (Product $product) => Stock::query()
        ->where(['branch_id' => $this->branch->id, 'product_id' => $product->id])
        ->value('quantity');

    expect($stock($this->bread))->toBe(230)   // 30 on hand + 200 received
        ->and($stock($this->coffee))->toBe(4); // stock row created on first receipt
});

it('only lets managers approve', function () {
    $order = PurchaseOrder::factory()->withLines()->create();

    $this->actingAs(staff())->postJson("/api/purchase-orders/{$order->id}/approve")->assertForbidden();

    expect($order->fresh()->status->value)->toBe('draft');
});

it('rejects receiving a draft order with 409 and leaves stock untouched', function () {
    $order = PurchaseOrder::factory()->withLines([$this->bread->id => 50])->create(['branch_id' => $this->branch->id]);

    $this->actingAs(manager())
        ->postJson("/api/purchase-orders/{$order->id}/receive")
        ->assertConflict()
        ->assertJsonPath('message', 'A purchase order cannot go from "draft" to "received".');

    expect(Stock::query()->count())->toBe(0);
});

it('rejects receiving the same order twice', function () {
    $order = PurchaseOrder::factory()->received()->withLines()->create();

    $this->actingAs(manager())->postJson("/api/purchase-orders/{$order->id}/receive")->assertConflict();
});

it('lists orders filtered by status', function () {
    PurchaseOrder::factory()->withLines()->create();
    PurchaseOrder::factory()->approved()->withLines()->create();

    $this->actingAs(staff())
        ->getJson('/api/purchase-orders?status=approved')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.status', 'approved');
});

it('shows a single order with its lines', function () {
    $order = PurchaseOrder::factory()->withLines([$this->bread->id => 3])->create();

    $this->actingAs(staff())
        ->getJson("/api/purchase-orders/{$order->id}")
        ->assertOk()
        ->assertJsonPath('data.reference', sprintf('PO-%05d', $order->id))
        ->assertJsonPath('data.lines.0.product.id', $this->bread->id)
        ->assertJsonPath('data.lines.0.subtotal', 3 * 1000);
});

it('returns 404 for an unknown order', function () {
    $this->actingAs(manager())->postJson('/api/purchase-orders/999/approve')->assertNotFound();
});
