<?php

namespace App\Domain\PurchaseOrders;

/**
 * Port: where stock movements produced by purchasing are applied.
 */
interface StockLedger
{
    public function apply(StockMovement $movement): void;
}
