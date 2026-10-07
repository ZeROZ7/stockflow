<?php

namespace App\Http\Controllers\Api;

use App\Domain\PurchaseOrders\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PurchaseOrderResource;
use App\Models\Branch;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Queries\LowStockQuery;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(LowStockQuery $lowStock): JsonResponse
    {
        $openStatuses = [PurchaseOrderStatus::Draft, PurchaseOrderStatus::Approved];

        return response()->json([
            'data' => [
                'stats' => [
                    'branches' => Branch::query()->count(),
                    'products' => Product::query()->count(),
                    'open_purchase_orders' => PurchaseOrder::query()->whereIn('status', $openStatuses)->count(),
                    'low_stock_items' => $lowStock->count(),
                ],
                'low_stock' => $lowStock->get(limit: 10),
                'recent_purchase_orders' => PurchaseOrderResource::collection(
                    PurchaseOrder::query()->with(['branch', 'lines'])->latest('id')->limit(5)->get(),
                ),
            ],
        ]);
    }
}
