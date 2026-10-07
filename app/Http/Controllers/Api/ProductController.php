<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\PurchaseOrderLine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * Plain CRUD: no domain layer needed here (see "Design decisions" in the README).
 */
class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = Product::query()
            ->when($request->string('search')->trim()->toString(), function ($query, string $search) {
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            })
            ->orderBy('name')
            ->get();

        return ProductResource::collection($products);
    }

    public function store(ProductRequest $request): ProductResource
    {
        Gate::authorize('create', Product::class);

        return ProductResource::make(Product::create($request->validated()));
    }

    public function show(Product $product): ProductResource
    {
        return ProductResource::make($product);
    }

    public function update(ProductRequest $request, Product $product): ProductResource
    {
        Gate::authorize('update', $product);

        $product->update($request->validated());

        return ProductResource::make($product);
    }

    public function destroy(Product $product): Response
    {
        Gate::authorize('delete', $product);

        abort_if(
            PurchaseOrderLine::query()->where('product_id', $product->id)->exists(),
            409,
            'This product is used in purchase orders and cannot be deleted.',
        );

        $product->delete();

        return response()->noContent();
    }
}
