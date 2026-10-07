<?php

namespace App\Application\PurchaseOrders;

use App\Application\Shared\Clock;
use App\Application\Shared\TransactionManager;
use App\Domain\PurchaseOrders\PurchaseOrder;
use App\Domain\PurchaseOrders\PurchaseOrderRepository;
use App\Domain\PurchaseOrders\StockLedger;

/**
 * Receiving goods closes the order and increases branch stock,
 * both inside one transaction so they can never get out of sync.
 */
final readonly class ReceivePurchaseOrder
{
    public function __construct(
        private PurchaseOrderRepository $orders,
        private StockLedger $stock,
        private TransactionManager $transactions,
        private Clock $clock,
    ) {}

    public function handle(int $purchaseOrderId, int $receiverId): PurchaseOrder
    {
        return $this->transactions->run(function () use ($purchaseOrderId, $receiverId) {
            $order = $this->orders->getForUpdate($purchaseOrderId);

            $movements = $order->receive($receiverId, $this->clock->now());

            foreach ($movements as $movement) {
                $this->stock->apply($movement);
            }

            return $this->orders->save($order);
        });
    }
}
