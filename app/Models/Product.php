<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['sku', 'name', 'unit', 'min_stock'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /** @return HasMany<Stock, $this> */
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    protected function casts(): array
    {
        return [
            'min_stock' => 'integer',
        ];
    }
}
