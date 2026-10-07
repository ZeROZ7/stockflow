<?php

namespace App\Application\PurchaseOrders;

use App\Application\PurchaseOrders\Data\CreatePurchaseOrderData;
use App\Application\PurchaseOrders\Data\PurchaseOrderLineData;
use App\Domain\PurchaseOrders\PurchaseOrder;
use App\Domain\PurchaseOrders\PurchaseOrderLine;
use App\Domain\PurchaseOrders\PurchaseOrderRepository;
use App\Domain\PurchaseOrders\Quantity;

final readonly class CreatePurchaseOrder
{
    public function __construct(private PurchaseOrderRepository $orders) {}

    public function handle(CreatePurchaseOrderData $data): PurchaseOrder
    {
        $lines = array_map(
            fn (PurchaseOrderLineData $line) => new PurchaseOrderLine(
                $line->productId,
                Quantity::of($line->quantity),
                $line->unitCost,
            ),
            $data->lines,
        );

        $order = PurchaseOrder::draft($data->branchId, $data->supplier, $lines, $data->createdBy, $data->notes);

        return $this->orders->save($order);
    }
}
