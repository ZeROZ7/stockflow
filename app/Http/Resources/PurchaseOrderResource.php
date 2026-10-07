<?php

namespace App\Http\Resources;

use App\Domain\PurchaseOrders\PurchaseOrderStatus;
use App\Models\PurchaseOrderLine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PurchaseOrder */
class PurchaseOrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => sprintf('PO-%05d', $this->id),
            'status' => $this->status,
            'status_label' => $this->status->label(),
            'supplier' => $this->supplier,
            'notes' => $this->notes,
            'branch' => BranchResource::make($this->whenLoaded('branch')),
            'lines' => PurchaseOrderLineResource::collection($this->whenLoaded('lines')),
            'total' => $this->whenLoaded('lines', fn () => $this->lines->sum(
                fn (PurchaseOrderLine $line) => $line->quantity * $line->unit_cost,
            )),
            'created_by' => $this->whenLoaded('creator', fn () => $this->creator->name),
            'approved_by' => $this->whenLoaded('approver', fn () => $this->approver?->name),
            'received_by' => $this->whenLoaded('receiver', fn () => $this->receiver?->name),
            'created_at' => $this->created_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'received_at' => $this->received_at?->toIso8601String(),
            // Lets the UI show or hide buttons; the API still enforces both rules.
            'can' => [
                'approve' => $this->status->canTransitionTo(PurchaseOrderStatus::Approved)
                    && ($request->user()?->can('approve', $this->resource) ?? false),
                'receive' => $this->status->canTransitionTo(PurchaseOrderStatus::Received)
                    && ($request->user()?->can('receive', $this->resource) ?? false),
            ],
        ];
    }
}
