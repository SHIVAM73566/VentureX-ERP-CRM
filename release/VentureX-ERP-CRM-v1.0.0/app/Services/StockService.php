<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use App\Models\Warehouse;

class StockService
{
    public static function recordSaleItems(SalesOrder $order): void
    {
        foreach ($order->items as $item) {
            if (! $item->product_id) {
                continue;
            }

            $defaultWarehouse = Warehouse::ofCompany()
                ->where('is_default', true)
                ->first();

            if (! $defaultWarehouse) {
                continue;
            }

            StockMovement::create([
                'company_id' => $order->company_id,
                'product_id' => $item->product_id,
                'warehouse_id' => $defaultWarehouse->id,
                'type' => 'out',
                'quantity' => $item->quantity,
                'reference_type' => SalesOrder::class,
                'reference_id' => $order->id,
                'note' => "Sale: {$order->order_number}",
                'created_by' => $order->created_by,
            ]);
        }
    }

    public static function recordPurchaseReceipt(PurchaseOrder $order): void
    {
        foreach ($order->items as $item) {
            if (! $item->product_id) {
                continue;
            }

            $defaultWarehouse = Warehouse::ofCompany()
                ->where('is_default', true)
                ->first();

            if (! $defaultWarehouse) {
                continue;
            }

            StockMovement::create([
                'company_id' => $order->company_id,
                'product_id' => $item->product_id,
                'warehouse_id' => $defaultWarehouse->id,
                'type' => 'in',
                'quantity' => $item->quantity,
                'unit_cost' => $item->unit_price,
                'reference_type' => PurchaseOrder::class,
                'reference_id' => $order->id,
                'note' => "Purchase receipt: {$order->po_number}",
                'created_by' => $order->created_by,
            ]);
        }
    }

    public static function reverseSaleItems(SalesOrder $order): void
    {
        StockMovement::where('reference_type', SalesOrder::class)
            ->where('reference_id', $order->id)
            ->where('type', 'out')
            ->delete();
    }

    public static function reversePurchaseReceipt(PurchaseOrder $order): void
    {
        StockMovement::where('reference_type', PurchaseOrder::class)
            ->where('reference_id', $order->id)
            ->where('type', 'in')
            ->delete();
    }

    public static function availableStock(int $productId, ?int $warehouseId = null): float
    {
        $query = StockMovement::where('product_id', $productId);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $in = (float) $query->clone()->where('type', 'in')->sum('quantity');
        $out = (float) $query->clone()->where('type', 'out')->sum('quantity');
        $adjustment = (float) $query->clone()->where('type', 'adjustment')->sum('quantity');

        return $in - $out + $adjustment;
    }
}
