<?php

namespace App\Application\PurchaseOrders\Data;

final readonly class CreatePurchaseOrderData
{
    /**
     * @param  list<PurchaseOrderLineData>  $lines
     */
    public function __construct(
        public int $branchId,
        public string $supplier,
        public array $lines,
        public int $createdBy,
        public ?string $notes = null,
    ) {}
}
