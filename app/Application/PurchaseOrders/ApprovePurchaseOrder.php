<?php

namespace App\Application\PurchaseOrders;

use App\Application\Shared\Clock;
use App\Application\Shared\TransactionManager;
use App\Domain\PurchaseOrders\PurchaseOrder;
use App\Domain\PurchaseOrders\PurchaseOrderRepository;

final readonly class ApprovePurchaseOrder
{
    public function __construct(
        private PurchaseOrderRepository $orders,
        private TransactionManager $transactions,
        private Clock $clock,
    ) {}

    public function handle(int $purchaseOrderId, int $approverId): PurchaseOrder
    {
        return $this->transactions->run(function () use ($purchaseOrderId, $approverId) {
            $order = $this->orders->getForUpdate($purchaseOrderId);

            $order->approve($approverId, $this->clock->now());

            return $this->orders->save($order);
        });
    }
}
