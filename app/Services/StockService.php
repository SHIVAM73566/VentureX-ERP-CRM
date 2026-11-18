<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Model observers are registered once per application container (one per
     * request/test) so direct writes to stock_movements also invalidate the
     * request-scoped snapshot.
     */
    private static ?object $observedApplication = null;

    /** @var array<string, true> */
    private static array $snapshotKeys = [];

    public static function recordSaleItems(SalesOrder $order): void
    {
        $companyId = (int) $order->company_id;

        self::transaction(function () use ($order, $companyId) {
            $defaultWarehouse = self::defaultWarehouse($companyId);

            foreach ($order->items as $item) {
                if (! $item->product_id) {
                    continue;
                }

                if (! $defaultWarehouse) {
                    continue;
                }

                self::lockProduct((int) $item->product_id, $companyId);

                StockMovement::create([
                    'company_id' => $companyId,
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

            self::invalidate($companyId);
        });
    }

    public static function recordPurchaseReceipt(PurchaseOrder $order): void
    {
        $companyId = (int) $order->company_id;

        self::transaction(function () use ($order, $companyId) {
            $defaultWarehouse = self::defaultWarehouse($companyId);

            foreach ($order->items as $item) {
                if (! $item->product_id) {
                    continue;
                }

                if (! $defaultWarehouse) {
                    continue;
                }

                self::lockProduct((int) $item->product_id, $companyId);

                StockMovement::create([
                    'company_id' => $companyId,
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

            self::invalidate($companyId);
        });
    }

    public static function reverseSaleItems(SalesOrder $order): void
    {
        $companyId = (int) $order->company_id;

        self::transaction(function () use ($order, $companyId) {
            StockMovement::where('reference_type', SalesOrder::class)
                ->where('reference_id', $order->id)
                ->where('type', 'out')
                ->delete();

            self::invalidate($companyId);
        });
    }

    public static function reversePurchaseReceipt(PurchaseOrder $order): void
    {
        $companyId = (int) $order->company_id;

        self::transaction(function () use ($order, $companyId) {
            StockMovement::where('reference_type', PurchaseOrder::class)
                ->where('reference_id', $order->id)
                ->where('type', 'in')
                ->delete();

            self::invalidate($companyId);
        });
    }

    /**
     * Grouped balance for one product. One grouped query is shared by every
     * product/warehouse combination requested during the current request.
     */
    public static function availableStock(int $productId, ?int $warehouseId = null): float
    {
        return self::balance($productId, CompanyContext::id(), $warehouseId);
    }

    /**
     * Request-scoped balance lookup scoped to a specific company.
     */
    public static function balance(int $productId, ?int $companyId = null, ?int $warehouseId = null): float
    {
        return (float) (self::balances($companyId, $warehouseId)[$productId] ?? 0);
    }

    /**
     * @return array<int, float> product id => signed stock balance
     */
    public static function balances(?int $companyId = null, ?int $warehouseId = null): array
    {
        self::registerInvalidationObservers();

        $key = self::snapshotKey($companyId, $warehouseId);

        if (! app()->bound($key)) {
            $balances = StockMovement::query()
                ->selectRaw(
                    'product_id, SUM(CASE WHEN type = ? THEN quantity WHEN type = ? THEN -quantity WHEN type = ? THEN quantity ELSE 0 END) as balance',
                    ['in', 'out', 'adjustment']
                )
                ->when($companyId !== null, fn ($query) => $query->where('company_id', $companyId))
                ->when($warehouseId !== null, fn ($query) => $query->where('warehouse_id', $warehouseId))
                ->groupBy('product_id')
                ->pluck('balance', 'product_id')
                ->all();

            self::$snapshotKeys[$key] = true;
            app()->instance($key, array_map('floatval', $balances));
        }

        return app()->make($key);
    }

    /**
     * Drop the request-scoped balance snapshots (and dashboard aggregates)
     * after a stock write. Passing null clears every company.
     */
    public static function invalidate(?int $companyId = null): void
    {
        foreach (array_keys(self::$snapshotKeys) as $key) {
            if ($companyId === null || str_starts_with($key, 'stock-balance:'.$companyId.':')) {
                app()->forgetInstance($key);
                unset(self::$snapshotKeys[$key]);
            }
        }

        $warehousePrefix = 'stock-warehouse:';
        foreach (array_keys(self::$snapshotKeys) as $key) {
            if ($companyId === null || str_starts_with($key, $warehousePrefix.$companyId)) {
                app()->forgetInstance($key);
                unset(self::$snapshotKeys[$key]);
            }
        }

        DashboardCacheService::forget($companyId);
    }

    /**
     * Test/debug helper: forget all snapshots held by this process.
     */
    public static function flush(): void
    {
        self::invalidate();
    }

    private static function snapshotKey(?int $companyId, ?int $warehouseId): string
    {
        return 'stock-balance:'.($companyId ?? 0).':'.($warehouseId ?? 0);
    }

    private static function defaultWarehouse(int $companyId): ?Warehouse
    {
        self::registerInvalidationObservers();

        $key = 'stock-warehouse:'.$companyId;

        if (! app()->bound($key)) {
            self::$snapshotKeys[$key] = true;
            app()->instance($key, Warehouse::ofCompany($companyId)
                ->where('is_default', true)
                ->first());
        }

        return app()->make($key);
    }

    /**
     * Serialise stock postings on the affected product rows when called from
     * inside an existing transaction, otherwise start one.
     */
    private static function lockProduct(int $productId, int $companyId): void
    {
        Product::ofCompany($companyId)
            ->whereKey($productId)
            ->lockForUpdate()
            ->first();
    }

    private static function transaction(callable $callback): mixed
    {
        return DB::transactionLevel() > 0 ? $callback() : DB::transaction($callback);
    }

    private static function registerInvalidationObservers(): void
    {
        $application = app();

        if (self::$observedApplication === $application) {
            return;
        }

        self::$observedApplication = $application;

        foreach (['created', 'updated', 'deleted'] as $event) {
            StockMovement::registerModelEvent($event, function (StockMovement $movement): void {
                self::invalidate((int) $movement->company_id);
            });
        }

        foreach (['created', 'updated', 'deleted'] as $event) {
            Warehouse::registerModelEvent($event, function (Warehouse $warehouse): void {
                self::invalidate((int) $warehouse->company_id);
            });
        }
    }
}
