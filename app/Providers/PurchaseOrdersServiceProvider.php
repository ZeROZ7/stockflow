<?php

namespace App\Providers;

use App\Application\Shared\Clock;
use App\Application\Shared\TransactionManager;
use App\Domain\PurchaseOrders\PurchaseOrderRepository;
use App\Domain\PurchaseOrders\StockLedger;
use App\Infrastructure\PurchaseOrders\EloquentPurchaseOrderRepository;
use App\Infrastructure\PurchaseOrders\EloquentStockLedger;
use App\Infrastructure\Shared\DatabaseTransactionManager;
use App\Infrastructure\Shared\SystemClock;
use Illuminate\Support\ServiceProvider;

/**
 * Wires the purchasing ports (interfaces) to their Laravel adapters.
 */
class PurchaseOrdersServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        PurchaseOrderRepository::class => EloquentPurchaseOrderRepository::class,
        StockLedger::class => EloquentStockLedger::class,
        TransactionManager::class => DatabaseTransactionManager::class,
        Clock::class => SystemClock::class,
    ];
}
