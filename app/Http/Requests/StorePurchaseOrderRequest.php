<?php

namespace App\Http\Requests;

use App\Application\PurchaseOrders\Data\CreatePurchaseOrderData;
use App\Application\PurchaseOrders\Data\PurchaseOrderLineData;
use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    /**
     * Input shape validation. Business rules (e.g. no duplicated products)
     * are enforced again by the domain, which is the source of truth.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'supplier' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'lines' => ['required', 'array', 'min:1', 'max:100'],
            'lines.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'lines.*.quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'lines.*.unit_cost' => ['required', 'integer', 'min:0', 'max:100000000'],
        ];
    }

    /**
     * Convert the validated HTTP input into the use case's DTO.
     */
    public function toData(): CreatePurchaseOrderData
    {
        return new CreatePurchaseOrderData(
            branchId: $this->integer('branch_id'),
            supplier: $this->string('supplier')->toString(),
            lines: array_map(
                fn (array $line) => new PurchaseOrderLineData(
                    productId: (int) $line['product_id'],
                    quantity: (int) $line['quantity'],
                    unitCost: (int) $line['unit_cost'],
                ),
                array_values($this->validated('lines')),
            ),
            createdBy: (int) $this->user()->id,
            notes: $this->validated('notes'),
        );
    }
}
