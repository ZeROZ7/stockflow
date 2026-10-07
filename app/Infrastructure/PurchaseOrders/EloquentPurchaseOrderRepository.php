<?php

namespace App\Infrastructure\PurchaseOrders;

use App\Domain\PurchaseOrders\Exceptions\PurchaseOrderNotFound;
use App\Domain\PurchaseOrders\PurchaseOrder;
use App\Domain\PurchaseOrders\PurchaseOrderLine;
use App\Domain\PurchaseOrders\PurchaseOrderRepository;
use App\Domain\PurchaseOrders\Quantity;
use App\Models\PurchaseOrder as PurchaseOrderModel;
use App\Models\PurchaseOrderLine as PurchaseOrderLineModel;

/**
 * Adapter: maps the domain aggregate to/from Eloquent models.
 */
final class EloquentPurchaseOrderRepository implements PurchaseOrderRepository
{
    public function getForUpdate(int $id): PurchaseOrder
    {
        $model = PurchaseOrderModel::query()->with('lines')->lockForUpdate()->find($id);

        if ($model === null) {
            throw PurchaseOrderNotFound::withId($id);
        }

        return $this->toDomain($model);
    }

    public function save(PurchaseOrder $order): PurchaseOrder
    {
        $model = $order->id() === null
            ? new PurchaseOrderModel
            : PurchaseOrderModel::query()->findOrFail($order->id());

        $model->fill([
            'branch_id' => $order->branchId(),
            'supplier' => $order->supplier(),
            'status' => $order->status(),
            'notes' => $order->notes(),
            'created_by' => $order->createdBy(),
            'approved_by' => $order->approvedBy(),
            'approved_at' => $order->approvedAt(),
            'received_by' => $order->receivedBy(),
            'received_at' => $order->receivedAt(),
        ])->save();

        // Lines are immutable once the order exists, so they are written only on creation.
        if ($model->wasRecentlyCreated) {
            $model->lines()->createMany(array_map(fn (PurchaseOrderLine $line) => [
                'product_id' => $line->productId,
                'quantity' => $line->quantity->value,
                'unit_cost' => $line->unitCost,
            ], $order->lines()));
        }

        return $this->toDomain($model->load('lines'));
    }

    private function toDomain(PurchaseOrderModel $model): PurchaseOrder
    {
        return PurchaseOrder::reconstitute(
            id: $model->id,
            branchId: $model->branch_id,
            supplier: $model->supplier,
            status: $model->status,
            lines: $model->lines->map(fn (PurchaseOrderLineModel $line) => new PurchaseOrderLine(
                $line->product_id,
                Quantity::of($line->quantity),
                $line->unit_cost,
            ))->values()->all(),
            notes: $model->notes,
            createdBy: $model->created_by,
            approvedBy: $model->approved_by,
            approvedAt: $model->approved_at,
            receivedBy: $model->received_by,
            receivedAt: $model->received_at,
        );
    }
}
