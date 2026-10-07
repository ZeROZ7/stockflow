<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BranchRequest;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * Plain CRUD: no domain layer needed here (see "Design decisions" in the README).
 */
class BranchController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return BranchResource::collection(Branch::query()->orderBy('name')->get());
    }

    public function store(BranchRequest $request): BranchResource
    {
        Gate::authorize('create', Branch::class);

        return BranchResource::make(Branch::create($request->validated()));
    }

    public function show(Branch $branch): BranchResource
    {
        return BranchResource::make($branch);
    }

    public function update(BranchRequest $request, Branch $branch): BranchResource
    {
        Gate::authorize('update', $branch);

        $branch->update($request->validated());

        return BranchResource::make($branch);
    }

    public function destroy(Branch $branch): Response
    {
        Gate::authorize('delete', $branch);

        abort_if($branch->purchaseOrders()->exists(), 409, 'This branch has purchase orders and cannot be deleted.');

        $branch->delete();

        return response()->noContent();
    }
}
