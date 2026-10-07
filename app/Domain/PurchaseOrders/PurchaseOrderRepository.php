<?php

namespace App\Domain\PurchaseOrders;

use App\Domain\PurchaseOrders\Exceptions\PurchaseOrderNotFound;

/**
 * Port: how the domain expects purchase orders to be stored.
 * The Eloquent adapter lives in App\Infrastructure\PurchaseOrders.
 */
interface PurchaseOrderRepository
{
    /**
     * Load an order and lock it for the rest of the current transaction.
     *
     * @throws PurchaseOrderNotFound
     */
    public function getForUpdate(int $id): PurchaseOrder;

    /**
     * Persist the order and return it with its identity assigned.
     */
    public function save(PurchaseOrder $order): PurchaseOrder;
}
