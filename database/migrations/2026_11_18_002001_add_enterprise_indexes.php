<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enterprise query indexes. Every addition is guarded so the migration is
 * safe to re-run and works on SQLite (used by the test suite) as well as
 * MySQL/PostgreSQL.
 */
return new class extends Migration
{
    /** @var array<int, array{table: string, name: string, columns: array<int, string>, unique?: bool}> */
    private array $indexes = [
        // Stock ledger: grouped balance, movements history and posting lookups.
        ['table' => 'stock_movements', 'name' => 'idx_ent_stock_movements_company_product_type_warehouse_created', 'columns' => ['company_id', 'product_id', 'type', 'warehouse_id', 'created_at']],
        ['table' => 'stock_movements', 'name' => 'idx_ent_stock_movements_product_created', 'columns' => ['product_id', 'created_at']],
        ['table' => 'stock_movements', 'name' => 'idx_ent_stock_movements_reference', 'columns' => ['reference_type', 'reference_id']],

        // Support desk workload/queue filters.
        ['table' => 'support_tickets', 'name' => 'idx_ent_support_tickets_company_assigned_status', 'columns' => ['company_id', 'assigned_to', 'status']],
        ['table' => 'support_tickets', 'name' => 'idx_ent_support_tickets_company_status_created', 'columns' => ['company_id', 'status', 'created_at']],

        // Import pipeline: one row per row number and default template lookup.
        ['table' => 'import_rows', 'name' => 'idx_ent_import_rows_import_row_unique', 'columns' => ['import_id', 'row_number'], 'unique' => true],
        ['table' => 'import_rows', 'name' => 'idx_ent_import_rows_import_status', 'columns' => ['import_id', 'status']],
        ['table' => 'import_templates', 'name' => 'idx_ent_import_templates_company_default', 'columns' => ['company_id', 'is_default']],
        ['table' => 'imports', 'name' => 'idx_ent_imports_company_status_created', 'columns' => ['company_id', 'status', 'created_at']],

        // Tenant FK + status/date filters used by dashboards and list views.
        ['table' => 'products', 'name' => 'idx_ent_products_company_sku', 'columns' => ['company_id', 'sku']],
        ['table' => 'products', 'name' => 'idx_ent_products_company_supplier', 'columns' => ['company_id', 'supplier_id']],
        ['table' => 'products', 'name' => 'idx_ent_products_company_created', 'columns' => ['company_id', 'created_at']],
        ['table' => 'invoices', 'name' => 'idx_ent_invoices_company_customer_status', 'columns' => ['company_id', 'customer_id', 'status']],
        ['table' => 'invoices', 'name' => 'idx_ent_invoices_company_issue_date', 'columns' => ['company_id', 'issue_date']],
        ['table' => 'payments', 'name' => 'idx_ent_payments_company_invoice', 'columns' => ['company_id', 'invoice_id']],
        ['table' => 'payments', 'name' => 'idx_ent_payments_company_status', 'columns' => ['company_id', 'status']],
        ['table' => 'purchase_orders', 'name' => 'idx_ent_purchase_orders_company_supplier_status', 'columns' => ['company_id', 'supplier_id', 'status']],
        ['table' => 'purchase_orders', 'name' => 'idx_ent_purchase_orders_company_order_date', 'columns' => ['company_id', 'order_date']],
        ['table' => 'purchase_order_items', 'name' => 'idx_ent_purchase_order_items_po_product', 'columns' => ['purchase_order_id', 'product_id']],
        ['table' => 'sales_orders', 'name' => 'idx_ent_sales_orders_company_customer_status', 'columns' => ['company_id', 'customer_id', 'status']],
        ['table' => 'sales_order_items', 'name' => 'idx_ent_sales_order_items_order_product', 'columns' => ['sales_order_id', 'product_id']],
        ['table' => 'invoice_items', 'name' => 'idx_ent_invoice_items_invoice_product', 'columns' => ['invoice_id', 'product_id']],
        ['table' => 'customers', 'name' => 'idx_ent_customers_company_status', 'columns' => ['company_id', 'status']],
        ['table' => 'leads', 'name' => 'idx_ent_leads_company_status_follow_up', 'columns' => ['company_id', 'status', 'next_follow_up']],
        ['table' => 'opportunities', 'name' => 'idx_ent_opportunities_company_stage', 'columns' => ['company_id', 'stage']],
        ['table' => 'shipments', 'name' => 'idx_ent_shipments_company_status_date', 'columns' => ['company_id', 'status', 'departure_date']],
        ['table' => 'employees', 'name' => 'idx_ent_employees_company_status', 'columns' => ['company_id', 'status']],
        ['table' => 'agent_runs', 'name' => 'idx_ent_agent_runs_company_status_created', 'columns' => ['company_id', 'status', 'created_at']],
        ['table' => 'support_replies', 'name' => 'idx_ent_support_replies_ticket_created', 'columns' => ['support_ticket_id', 'created_at']],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $definition) {
            $this->addIndex($definition);
        }
    }

    public function down(): void
    {
        // Only remove indexes owned by this migration, and only when present.
        foreach (array_reverse($this->indexes) as $definition) {
            if (! Schema::hasTable($definition['table'])) {
                continue;
            }

            $existing = collect(Schema::getIndexes($definition['table']))->pluck('name')->all();

            if (in_array($definition['name'], $existing, true)) {
                Schema::table($definition['table'], function (Blueprint $table) use ($definition): void {
                    $table->dropIndex($definition['name']);
                });
            }
        }
    }

    /** @param array{table: string, name: string, columns: array<int, string>, unique?: bool} $definition */
    private function addIndex(array $definition): void
    {
        $table = $definition['table'];

        if (! Schema::hasTable($table)) {
            return;
        }

        $columns = array_values(array_filter($definition['columns'], fn ($column) => Schema::hasColumn($table, $column)));

        if (count($columns) !== count($definition['columns'])) {
            return;
        }

        $existing = Schema::getIndexes($table);
        $names = collect($existing)->pluck('name')->all();

        if (in_array($definition['name'], $names, true)) {
            return;
        }

        // Avoid duplicate indexes when an earlier migration used a different
        // name for the exact same column list.
        foreach ($existing as $index) {
            if (($index['columns'] ?? []) === $columns) {
                return;
            }
        }

        Schema::table($table, function (Blueprint $blueprint) use ($definition, $columns): void {
            if (! empty($definition['unique'])) {
                $blueprint->unique($columns, $definition['name']);
            } else {
                $blueprint->index($columns, $definition['name']);
            }
        });
    }
};
