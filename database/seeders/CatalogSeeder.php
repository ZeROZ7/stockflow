<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Database\Seeder;

/**
 * Fictional sandwich & coffee chain with stores in central and southern Chile.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $branches = collect([
            ['code' => 'VAL-01', 'name' => 'Valparaíso Centro', 'city' => 'Valparaíso', 'address' => 'Av. Pedro Montt 1800'],
            ['code' => 'VIN-01', 'name' => 'Viña del Mar', 'city' => 'Viña del Mar', 'address' => 'Av. Libertad 1000'],
            ['code' => 'SCL-01', 'name' => 'Providencia', 'city' => 'Santiago', 'address' => 'Av. Providencia 2100'],
            ['code' => 'SCL-02', 'name' => 'Ñuñoa', 'city' => 'Santiago', 'address' => 'Av. Irarrázaval 3400'],
            ['code' => 'CCP-01', 'name' => 'Concepción', 'city' => 'Concepción', 'address' => 'Barros Arana 700'],
        ])->map(fn (array $branch) => Branch::create($branch));

        $products = collect([
            ['sku' => 'PAN-001', 'name' => 'Pan amasado', 'unit' => 'unit', 'min_stock' => 120],
            ['sku' => 'PAN-002', 'name' => 'Pan frica', 'unit' => 'unit', 'min_stock' => 80],
            ['sku' => 'CAR-001', 'name' => 'Lomo vetado', 'unit' => 'kg', 'min_stock' => 20],
            ['sku' => 'CAR-002', 'name' => 'Pollo trozado', 'unit' => 'kg', 'min_stock' => 25],
            ['sku' => 'LAC-001', 'name' => 'Queso mantecoso', 'unit' => 'kg', 'min_stock' => 15],
            ['sku' => 'VER-001', 'name' => 'Palta Hass', 'unit' => 'kg', 'min_stock' => 18],
            ['sku' => 'VER-002', 'name' => 'Tomate', 'unit' => 'kg', 'min_stock' => 20],
            ['sku' => 'ABA-001', 'name' => 'Aceite vegetal', 'unit' => 'l', 'min_stock' => 30],
            ['sku' => 'ABA-002', 'name' => 'Mayonesa casera (balde)', 'unit' => 'box', 'min_stock' => 6],
            ['sku' => 'CAF-001', 'name' => 'Café en grano', 'unit' => 'kg', 'min_stock' => 5],
            ['sku' => 'BEB-001', 'name' => 'Jugo natural 1 L', 'unit' => 'unit', 'min_stock' => 36],
            ['sku' => 'DES-001', 'name' => 'Envases para llevar', 'unit' => 'pack', 'min_stock' => 10],
        ])->map(fn (array $product) => Product::create($product));

        // Deterministic stock levels so the demo always looks the same.
        mt_srand(2026);

        foreach ($branches as $branch) {
            foreach ($products as $product) {
                $factor = mt_rand(30, 300) / 100;

                Stock::create([
                    'branch_id' => $branch->id,
                    'product_id' => $product->id,
                    'quantity' => (int) round($product->min_stock * $factor),
                ]);
            }
        }
    }
}
