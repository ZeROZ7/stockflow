<?php

namespace App\Domain\PurchaseOrders;

use App\Domain\PurchaseOrders\Exceptions\InvalidPurchaseOrder;

/**
 * Value object: one product line of a purchase order.
 * Unit cost is stored in whole CLP (the Chilean peso has no decimals).
 */
final readonly class PurchaseOrderLine
{
    public function __construct(
        public int $productId,
        public Quantity $quantity,
        public int $unitCost,
    ) {
        if ($unitCost < 0) {
            throw InvalidPurchaseOrder::negativeUnitCost($productId);
        }
    }

    public function subtotal(): int
    {
        return $this->quantity->value * $this->unitCost;
    }
}
