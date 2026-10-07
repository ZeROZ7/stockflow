<?php

namespace App\Domain\PurchaseOrders;

/**
 * Value object describing stock that must be added to a branch
 * as a consequence of receiving a purchase order.
 */
final readonly class StockMovement
{
    public function __construct(
        public int $branchId,
        public int $productId,
        public int $quantity,
    ) {}
}
