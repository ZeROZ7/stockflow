<?php

namespace App\Application\PurchaseOrders\Data;

final readonly class PurchaseOrderLineData
{
    public function __construct(
        public int $productId,
        public int $quantity,
        public int $unitCost,
    ) {}
}
