<?php

namespace Tests\Support;

use App\Domain\PurchaseOrders\Exceptions\PurchaseOrderNotFound;
use App\Domain\PurchaseOrders\PurchaseOrder;
use App\Domain\PurchaseOrders\PurchaseOrderRepository;

/**
 * Test double for the repository port: lets use cases run without a database.
 */
final class InMemoryPurchaseOrderRepository implements PurchaseOrderRepository
{
    /** @var array<int, PurchaseOrder> */
    private array $orders = [];

    private int $nextId = 1;

    public function getForUpdate(int $id): PurchaseOrder
    {
        return $this->orders[$id] ?? throw PurchaseOrderNotFound::withId($id);
    }

    public function save(PurchaseOrder $order): PurchaseOrder
    {
        if ($order->id() === null) {
            $order = PurchaseOrder::reconstitute(
                $this->nextId++, $order->branchId(), $order->supplier(), $order->status(), $order->lines(),
                $order->notes(), $order->createdBy(), $order->approvedBy(), $order->approvedAt(),
                $order->receivedBy(), $order->receivedAt(),
            );
        }

        return $this->orders[$order->id()] = $order;
    }
}
