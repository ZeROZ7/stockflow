<?php

namespace App\Domain\PurchaseOrders\Exceptions;

final class PurchaseOrderNotFound extends PurchaseOrderException
{
    public static function withId(int $id): self
    {
        return new self("Purchase order #{$id} was not found.");
    }
}
