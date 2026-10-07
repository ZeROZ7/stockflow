<?php

namespace App\Infrastructure\PurchaseOrders;

use App\Domain\PurchaseOrders\StockLedger;
use App\Domain\PurchaseOrders\StockMovement;
use App\Models\Stock;

final class EloquentStockLedger implements StockLedger
{
    public function apply(StockMovement $movement): void
    {
        Stock::query()
            ->firstOrCreate(
                ['branch_id' => $movement->branchId, 'product_id' => $movement->productId],
                ['quantity' => 0],
            )
            ->increment('quantity', $movement->quantity);
    }
}
