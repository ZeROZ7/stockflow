<?php

namespace App\Domain\PurchaseOrders;

/**
 * Lifecycle of a purchase order: draft -> approved -> received.
 */
enum PurchaseOrderStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';
    case Received = 'received';

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Draft => $next === self::Approved,
            self::Approved => $next === self::Received,
            self::Received => false,
        };
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
