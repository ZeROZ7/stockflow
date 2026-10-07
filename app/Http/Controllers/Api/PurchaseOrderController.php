<?php

namespace App\Http\Controllers\Api;

use App\Application\PurchaseOrders\ApprovePurchaseOrder;
use App\Application\PurchaseOrders\CreatePurchaseOrder;
use App\Application\PurchaseOrders\ReceivePurchaseOrder;
use App\Domain\PurchaseOrders\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * Thin controller: HTTP in, use case call, API resource out.
 * Writes go through the application layer; reads query Eloquent directly.
 */
class PurchaseOrderController extends Controller
{
    private const RELATIONS = ['branch', 'lines.product', 'creator', 'approver', 'receiver'];

    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'status' => ['nullable', Rule::enum(PurchaseOrderStatus::class)],
            'branch_id' => ['nullable', 'integer'],
        ]);

        $orders = PurchaseOrder::query()
            ->with(['branch', 'lines'])
            ->when($request->input('status'), fn ($query, string $status) => $query->where('status', $status))
            ->when($request->integer('branch_id'), fn ($query, int $branchId) => $query->where('branch_id', $branchId))
            ->latest('id')
            ->paginate(15);

        return PurchaseOrderResource::collection($orders);
    }

    public function store(StorePurchaseOrderRequest $request, CreatePurchaseOrder $createPurchaseOrder): JsonResponse
    {
        Gate::authorize('create', PurchaseOrder::class);

        $order = $createPurchaseOrder->handle($request->toData());

        return $this->present($order->id())->response()->setStatusCode(201);
    }

    public function show(PurchaseOrder $purchaseOrder): PurchaseOrderResource
    {
        return PurchaseOrderResource::make($purchaseOrder->load(self::RELATIONS));
    }

    public function approve(Request $request, PurchaseOrder $purchaseOrder, ApprovePurchaseOrder $approvePurchaseOrder): PurchaseOrderResource
    {
        Gate::authorize('approve', $purchaseOrder);

        $approvePurchaseOrder->handle($purchaseOrder->id, $request->user()->id);

        return $this->present($purchaseOrder->id);
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder, ReceivePurchaseOrder $receivePurchaseOrder): PurchaseOrderResource
    {
        Gate::authorize('receive', $purchaseOrder);

        $receivePurchaseOrder->handle($purchaseOrder->id, $request->user()->id);

        return $this->present($purchaseOrder->id);
    }

    private function present(int $id): PurchaseOrderResource
    {
        return PurchaseOrderResource::make(PurchaseOrder::query()->with(self::RELATIONS)->findOrFail($id));
    }
}
