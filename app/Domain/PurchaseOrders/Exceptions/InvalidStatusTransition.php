<?php

namespace App\Domain\PurchaseOrders\Exceptions;

use App\Domain\PurchaseOrders\PurchaseOrderStatus;

final class InvalidStatusTransition extends PurchaseOrderException
{
    public static function between(PurchaseOrderStatus $from, PurchaseOrderStatus $to): self
    {
        return new self("A purchase order cannot go from \"{$from->value}\" to \"{$to->value}\".");
    }
}
