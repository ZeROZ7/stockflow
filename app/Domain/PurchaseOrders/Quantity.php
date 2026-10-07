<?php

namespace App\Domain\PurchaseOrders;

use App\Domain\PurchaseOrders\Exceptions\InvalidPurchaseOrder;

/**
 * A strictly positive amount of units (kg, boxes, litres...).
 */
final readonly class Quantity
{
    private function __construct(public int $value) {}

    public static function of(int $value): self
    {
        if ($value <= 0) {
            throw InvalidPurchaseOrder::nonPositiveQuantity($value);
        }

        return new self($value);
    }
}
