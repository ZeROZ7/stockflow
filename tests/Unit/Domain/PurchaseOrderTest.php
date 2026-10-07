<?php

use App\Domain\PurchaseOrders\Exceptions\InvalidPurchaseOrder;
use App\Domain\PurchaseOrders\Exceptions\InvalidStatusTransition;
use App\Domain\PurchaseOrders\PurchaseOrder;
use App\Domain\PurchaseOrders\PurchaseOrderLine;
use App\Domain\PurchaseOrders\PurchaseOrderStatus;
use App\Domain\PurchaseOrders\Quantity;
use App\Domain\PurchaseOrders\StockMovement;

function line(int $productId = 1, int $quantity = 10, int $unitCost = 1_000): PurchaseOrderLine
{
    return new PurchaseOrderLine($productId, Quantity::of($quantity), $unitCost);
}

function draftOrder(array $lines = []): PurchaseOrder
{
    return PurchaseOrder::draft(branchId: 7, supplier: 'Carnes Ñuble', lines: $lines ?: [line()], createdBy: 1);
}

it('starts as a draft and computes its total in CLP', function () {
    $order = draftOrder([line(1, 3, 11_990), line(2, 10, 1_290)]);

    expect($order->status())->toBe(PurchaseOrderStatus::Draft)
        ->and($order->total())->toBe(3 * 11_990 + 10 * 1_290);
});

it('rejects invalid drafts', function (Closure $makeOrder, string $message) {
    expect($makeOrder)->toThrow(InvalidPurchaseOrder::class, $message);
})->with([
    'no lines' => [fn () => PurchaseOrder::draft(1, 'Supplier', [], 1), 'at least one line'],
    'blank supplier' => [fn () => PurchaseOrder::draft(1, '   ', [line()], 1), 'needs a supplier'],
    'duplicated product' => [fn () => draftOrder([line(5), line(5)]), 'only once'],
    'zero quantity' => [fn () => line(quantity: 0), 'greater than zero'],
    'negative cost' => [fn () => line(unitCost: -1), 'cannot be negative'],
]);

it('records who approved the order and when', function () {
    $order = draftOrder();
    $at = new DateTimeImmutable('2026-10-01 10:00');

    $order->approve(approverId: 42, at: $at);

    expect($order->status())->toBe(PurchaseOrderStatus::Approved)
        ->and($order->approvedBy())->toBe(42)
        ->and($order->approvedAt())->toEqual($at);
});

it('returns one stock movement per line when received', function () {
    $order = draftOrder([line(1, 10), line(2, 4)]);
    $order->approve(42, new DateTimeImmutable);

    $movements = $order->receive(receiverId: 9, at: new DateTimeImmutable);

    expect($order->status())->toBe(PurchaseOrderStatus::Received)
        ->and($movements)->toEqual([
            new StockMovement(branchId: 7, productId: 1, quantity: 10),
            new StockMovement(branchId: 7, productId: 2, quantity: 4),
        ]);
});

it('cannot be received before it is approved', function () {
    draftOrder()->receive(9, new DateTimeImmutable);
})->throws(InvalidStatusTransition::class, 'cannot go from "draft" to "received"');

it('cannot be received twice', function () {
    $order = draftOrder();
    $order->approve(42, new DateTimeImmutable);
    $order->receive(9, new DateTimeImmutable);

    $order->receive(9, new DateTimeImmutable);
})->throws(InvalidStatusTransition::class);
