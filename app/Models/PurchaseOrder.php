<?php

namespace App\Models;

use App\Domain\PurchaseOrders\PurchaseOrderStatus;
use Database\Factories\PurchaseOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Persistence model only. Business rules live in App\Domain\PurchaseOrders\PurchaseOrder.
 */
#[Fillable([
    'branch_id', 'supplier', 'status', 'notes',
    'created_by', 'approved_by', 'approved_at', 'received_by', 'received_at',
])]
class PurchaseOrder extends Model
{
    /** @use HasFactory<PurchaseOrderFactory> */
    use HasFactory;

    /** @return BelongsTo<Branch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /** @return HasMany<PurchaseOrderLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseOrderLine::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return BelongsTo<User, $this> */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    protected function casts(): array
    {
        return [
            'status' => PurchaseOrderStatus::class,
            'approved_at' => 'immutable_datetime',
            'received_at' => 'immutable_datetime',
        ];
    }
}
