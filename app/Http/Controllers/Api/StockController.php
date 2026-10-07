<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /**
     * Stock level of every product at one branch (missing rows count as zero).
     */
    public function __invoke(Branch $branch): JsonResponse
    {
        $rows = DB::table('products')
            ->leftJoin('stocks', function ($join) use ($branch) {
                $join->on('stocks.product_id', '=', 'products.id')
                    ->where('stocks.branch_id', '=', $branch->id);
            })
            ->orderBy('products.name')
            ->get([
                'products.id as product_id',
                'products.sku',
                'products.name',
                'products.unit',
                'products.min_stock',
                DB::raw('COALESCE(stocks.quantity, 0) as quantity'),
            ])
            ->map(fn (object $row) => [
                'product_id' => (int) $row->product_id,
                'sku' => $row->sku,
                'name' => $row->name,
                'unit' => $row->unit,
                'min_stock' => (int) $row->min_stock,
                'quantity' => (int) $row->quantity,
                'is_low' => (int) $row->quantity < (int) $row->min_stock,
            ]);

        return response()->json(['data' => $rows]);
    }
}
