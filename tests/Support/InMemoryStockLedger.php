<?php

namespace Tests\Support;

use App\Domain\PurchaseOrders\StockLedger;
use App\Domain\PurchaseOrders\StockMovement;

final class InMemoryStockLedger implements StockLedger
{
    /** @var array<string, int> "branchId:productId" => quantity */
    private array $levels = [];

    public function apply(StockMovement $movement): void
    {
        $key = "{$movement->branchId}:{$movement->productId}";

        $this->levels[$key] = ($this->levels[$key] ?? 0) + $movement->quantity;
    }

    public function quantity(int $branchId, int $productId): int
    {
        return $this->levels["{$branchId}:{$productId}"] ?? 0;
    }
}
