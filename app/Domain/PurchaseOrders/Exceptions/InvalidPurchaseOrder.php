<?php

namespace App\Domain\PurchaseOrders\Exceptions;

final class InvalidPurchaseOrder extends PurchaseOrderException
{
    public static function missingSupplier(): self
    {
        return new self('A purchase order needs a supplier.');
    }

    public static function withoutLines(): self
    {
        return new self('A purchase order needs at least one line.');
    }

    public static function duplicatedProduct(): self
    {
        return new self('Each product can appear only once per purchase order.');
    }

    public static function nonPositiveQuantity(int $quantity): self
    {
        return new self("Quantity must be greater than zero, {$quantity} given.");
    }

    public static function negativeUnitCost(int $productId): self
    {
        return new self("Unit cost for product #{$productId} cannot be negative.");
    }
}
