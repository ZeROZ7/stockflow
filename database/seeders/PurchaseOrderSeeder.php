<?php

namespace Database\Seeders;

use App\Application\PurchaseOrders\ApprovePurchaseOrder;
use App\Application\PurchaseOrders\CreatePurchaseOrder;
use App\Application\PurchaseOrders\Data\CreatePurchaseOrderData;
use App\Application\PurchaseOrders\Data\PurchaseOrderLineData;
use App\Application\PurchaseOrders\ReceivePurchaseOrder;
use App\Models\Branch;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds orders through the real use cases, so demo data obeys the same rules as the API.
 */
class PurchaseOrderSeeder extends Seeder
{
    public function run(CreatePurchaseOrder $create, ApprovePurchaseOrder $approve, ReceivePurchaseOrder $receive): void
    {
        $manager = User::query()->where('email', 'demo@stockflow.test')->firstOrFail();
        $staff = User::query()->where('email', 'staff@stockflow.test')->firstOrFail();
        $branch = fn (string $code) => Branch::query()->where('code', $code)->value('id');
        $product = fn (string $sku) => Product::query()->where('sku', $sku)->value('id');

        $orders = [
            ['VAL-01', 'Panificadora del Puerto', [['PAN-001', 200, 180], ['PAN-002', 120, 200]], 'received'],
            ['SCL-01', 'Carnes Ñuble', [['CAR-001', 30, 11990], ['CAR-002', 40, 4290]], 'approved'],
            ['VIN-01', 'Agrícola Los Andes', [['VER-001', 25, 3490], ['VER-002', 30, 1290]], 'draft'],
            ['CCP-01', 'Lácteos del Sur', [['LAC-001', 20, 8990]], 'draft'],
            ['SCL-02', 'Tostaduría Cerro Alegre', [['CAF-001', 8, 18990]], 'received'],
        ];

        foreach ($orders as [$branchCode, $supplier, $lines, $finalStatus]) {
            $order = $create->handle(new CreatePurchaseOrderData(
                branchId: $branch($branchCode),
                supplier: $supplier,
                lines: array_map(
                    fn (array $line) => new PurchaseOrderLineData($product($line[0]), $line[1], $line[2]),
                    $lines,
                ),
                createdBy: $staff->id,
            ));

            if (in_array($finalStatus, ['approved', 'received'], true)) {
                $approve->handle($order->id(), $manager->id);
            }

            if ($finalStatus === 'received') {
                $receive->handle($order->id(), $staff->id);
            }
        }
    }
}
