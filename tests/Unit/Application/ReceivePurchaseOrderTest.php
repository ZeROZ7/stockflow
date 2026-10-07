<?php

use App\Application\PurchaseOrders\ApprovePurchaseOrder;
use App\Application\PurchaseOrders\CreatePurchaseOrder;
use App\Application\PurchaseOrders\Data\CreatePurchaseOrderData;
use App\Application\PurchaseOrders\Data\PurchaseOrderLineData;
use App\Application\PurchaseOrders\ReceivePurchaseOrder;
use App\Domain\PurchaseOrders\Exceptions\InvalidStatusTransition;
use App\Domain\PurchaseOrders\Exceptions\PurchaseOrderNotFound;
use App\Domain\PurchaseOrders\PurchaseOrderStatus;
use App\Domain\PurchaseOrders\StockMovement;
use Tests\Support\FrozenClock;
use Tests\Support\ImmediateTransactionManager;
use Tests\Support\InMemoryPurchaseOrderRepository;
use Tests\Support\InMemoryStockLedger;

// The use cases depend only on ports, so they run here with no Laravel and no database.
beforeEach(function () {
    $this->orders = new InMemoryPurchaseOrderRepository;
    $this->stock = new InMemoryStockLedger;
    $this->clock = new FrozenClock(new DateTimeImmutable('2026-10-07 09:30'));
    $transactions = new ImmediateTransactionManager;

    $this->create = new CreatePurchaseOrder($this->orders);
    $this->approve = new ApprovePurchaseOrder($this->orders, $transactions, $this->clock);
    $this->receive = new ReceivePurchaseOrder($this->orders, $this->stock, $transactions, $this->clock);

    $this->order = $this->create->handle(new CreatePurchaseOrderData(
        branchId: 3,
        supplier: 'Agrícola Los Andes',
        lines: [new PurchaseOrderLineData(productId: 10, quantity: 25, unitCost: 3_490)],
        createdBy: 1,
    ));
});

it('increases branch stock when an approved order is received', function () {
    $this->stock->apply(new StockMovement(3, 10, 5)); // 5 already on hand

    $this->approve->handle($this->order->id(), approverId: 2);
    $received = $this->receive->handle($this->order->id(), receiverId: 4);

    expect($received->status())->toBe(PurchaseOrderStatus::Received)
        ->and($received->receivedAt())->toEqual($this->clock->now())
        ->and($this->stock->quantity(branchId: 3, productId: 10))->toBe(30);
});

it('does not touch stock when the order is still a draft', function () {
    expect(fn () => $this->receive->handle($this->order->id(), receiverId: 4))
        ->toThrow(InvalidStatusTransition::class);

    expect($this->stock->quantity(3, 10))->toBe(0);
});

it('fails for an unknown order', function () {
    $this->approve->handle(999, approverId: 2);
})->throws(PurchaseOrderNotFound::class);
