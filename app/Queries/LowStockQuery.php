<?php

namespace App\Queries;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Read model: every (branch, product) pair whose stock is below the product minimum.
 * A missing stock row counts as zero units.
 */
class LowStockQuery
{
    /**
     * @return Collection<int, object{branch_id: int, branch_name: string, product_id: int, sku: string, product_name: string, unit: string, quantity: int, min_stock: int, shortage: int}>
     */
    public function get(?int $branchId = null, int $limit = 50): Collection
    {
        return $this->baseQuery()
            ->when($branchId, fn (Builder $query) => $query->where('branches.id', $branchId))
            ->orderByDesc('shortage')
            ->orderBy('products.name')
            ->limit($limit)
            ->get();
    }

    public function count(): int
    {
        return $this->baseQuery()->count();
    }

    private function baseQuery(): Builder
    {
        return DB::table('branches')
            ->crossJoin('products')
            ->leftJoin('stocks', function ($join) {
                $join->on('stocks.branch_id', '=', 'branches.id')
                    ->on('stocks.product_id', '=', 'products.id');
            })
            ->whereRaw('COALESCE(stocks.quantity, 0) < products.min_stock')
            ->select([
                'branches.id as branch_id',
                'branches.name as branch_name',
                'products.id as product_id',
                'products.sku',
                'products.name as product_name',
                'products.unit',
                DB::raw('COALESCE(stocks.quantity, 0) as quantity'),
                'products.min_stock',
                DB::raw('products.min_stock - COALESCE(stocks.quantity, 0) as shortage'),
            ]);
    }
}
