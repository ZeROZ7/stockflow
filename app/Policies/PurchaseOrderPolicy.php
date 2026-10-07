<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

/**
 * Policies answer "who may do this". Whether the order is in the right
 * status is a business rule owned by the domain (PurchaseOrderStatus).
 */
class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function approve(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->isManager();
    }

    public function receive(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return true;
    }
}
