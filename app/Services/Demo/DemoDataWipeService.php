<?php

declare(strict_types=1);

namespace App\Services\Demo;

use App\Models\User;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Removes demo business data for one company while leaving the buyer's
 * structural and security configuration untouched.
 *
 * Explicitly preserved: settings, roles, permissions and their pivots for real
 * users, the chart of accounts/account structure, audit history, the buyer
 * admin account and every non-demo user's access.
 *
 * @author Shivam Chaturvedi <chaturvedishivam179@gmail.com>
 */
class DemoDataWipeService
{
    /** Publicly documented demo accounts that must never survive a wipe. */
    public const DEMO_EMAILS = [
        'demo_admin@example.com',
        'demo_manager@example.com',
        'demo_sales@example.com',
    ];

    /** Sample-company users created by the demo seed chain. */
    public const SEEDED_DEMO_EMAILS = [
        'admin@jainmetal.example',
        'prakash@jainmetal.example',
        'purchase@jainmetal.example',
        'procurement@jainmetal.example',
        'accounts@jainmetal.example',
        'sales@jainmetal.example',
    ];

    /** Tables that must never be touched by a demo wipe. */
    public const PROTECTED_TABLES = [
        'settings',
        'roles',
        'permissions',
        'model_has_roles',
        'model_has_permissions',
        'role_has_permissions',
        'chart_of_accounts',
        'audit_logs',
        'users',
        'companies',
        'install_state',
    ];

    /**
     * Ordered children-first list of demo-owned tables. The order matters when
     * a database enforces foreign keys.
     *
     * @var list<string>
     */
    private const PURGE_TABLES = [
        'journal_entry_lines', 'journal_entries',
        'import_rows', 'import_templates', 'imports',
        'quotation_items', 'quotations',
        'sales_order_items', 'sales_orders',
        'invoice_items', 'invoices', 'payments',
        'purchase_requisition_items', 'purchase_requisitions',
        'rfq_items', 'rfq_responses', 'rfqs',
        'purchase_order_items', 'purchase_orders',
        'supplier_offers',
        'stock_movements', 'products', 'warehouses',
        'shipments', 'containers', 'landed_costs',
        'attendances', 'leave_requests',
        'payroll_items', 'payroll_runs',
        'performance_reviews', 'onboarding_tasks', 'employees', 'departments',
        'support_replies', 'support_tickets', 'error_reports', 'support_notifications',
        'activities', 'opportunities', 'leads', 'contacts', 'customers', 'suppliers',
        'ai_messages', 'ai_conversations', 'ai_runs', 'ai_usage_logs',
        'rag_query_logs', 'rag_documents',
        'approval_actions', 'approval_requests',
        'document_versions', 'documents',
        'automation_runs', 'automation_workflows',
        'agent_runs', 'agent_actions',
        'notifications',
    ];

    /**
     * Child table => [foreign key column, parent table]. Parent rows are scoped
     * by the parent's company_id before child rows are removed.
     */
    private const CHILD_SCOPES = [
        'journal_entry_lines' => ['journal_entry_id', 'journal_entries'],
        'import_rows' => ['import_id', 'imports'],
        'quotation_items' => ['quotation_id', 'quotations'],
        'sales_order_items' => ['sales_order_id', 'sales_orders'],
        'invoice_items' => ['invoice_id', 'invoices'],
        'purchase_requisition_items' => ['purchase_requisition_id', 'purchase_requisitions'],
        'rfq_items' => ['rfq_id', 'rfqs'],
        'rfq_responses' => ['rfq_id', 'rfqs'],
        'purchase_order_items' => ['purchase_order_id', 'purchase_orders'],
        'document_versions' => ['document_id', 'documents'],
        'ai_messages' => ['conversation_id', 'ai_conversations'],
        'approval_actions' => ['request_id', 'approval_requests'],
        'payroll_items' => ['payroll_run_id', 'payroll_runs'],
        'support_replies' => ['ticket_id', 'support_tickets'],
    ];

    /**
     * @return array<string, int> Table name => deleted row count.
     */
    public function wipe(int $companyId, ?int $preserveUserId = null): array
    {
        if ($companyId <= 0) {
            throw new InvalidArgumentException('A valid company context is required.');
        }

        CompanyContext::set($companyId);

        // The operator who requested the wipe is always retained, even if a
        // misconfigured install gave them a demo-looking address.
        $preserveUserId ??= auth()->id() !== null ? (int) auth()->id() : null;

        try {
            $deleted = DB::transaction(fn (): array => $this->purge($companyId, $preserveUserId));
        } catch (Throwable $e) {
            throw new RuntimeException('Demo data wipe failed and was rolled back.', 0, $e);
        }

        AuditLogger::log('demo_data_wipe', 'admin', null, null, [
            'company_id' => $companyId,
            'tables' => $deleted,
            'total_rows' => array_sum($deleted),
        ]);

        return $deleted;
    }

    /**
     * Read-only counts used to render the confirmation screen.
     *
     * @return array<string, int>
     */
    public function preview(int $companyId, ?int $preserveUserId = null): array
    {
        if ($companyId <= 0) {
            return [];
        }

        $counts = [];
        foreach (self::PURGE_TABLES as $table) {
            $count = $this->scopeQuery($table, $companyId)?->count() ?? 0;
            if ($count > 0) {
                $counts[$table] = $count;
            }
        }

        $preserveUserId ??= auth()->id() !== null ? (int) auth()->id() : null;
        $demoUsers = $this->demoUserQuery($companyId, $preserveUserId)->count();
        if ($demoUsers > 0) {
            $counts['demo_users'] = $demoUsers;
        }

        return $counts;
    }

    /**
     * @return array<string, int>
     */
    protected function purge(int $companyId, ?int $preserveUserId): array
    {
        $deleted = [];

        foreach (self::PURGE_TABLES as $table) {
            if (in_array($table, self::PROTECTED_TABLES, true)) {
                continue;
            }

            $query = $this->scopeQuery($table, $companyId);
            if ($query === null) {
                continue;
            }

            $deleted[$table] = $query->delete();
        }

        $demoUsers = $this->purgeDemoUsers($companyId, $preserveUserId);
        if ($demoUsers > 0) {
            // Reported under a distinct key so "users" remains protected and
            // the audit trail still shows how many demo accounts were removed.
            $deleted['demo_users'] = $demoUsers;
        }

        return $deleted;
    }

    /**
     * Delete only the known demo accounts, together with their auth artefacts.
     * Roles/permissions themselves are never modified.
     */
    protected function purgeDemoUsers(int $companyId, ?int $preserveUserId): int
    {
        $users = $this->demoUserQuery($companyId, $preserveUserId)->get(['id']);
        $ids = $users->pluck('id')->all();

        if ($ids === []) {
            return 0;
        }

        $pivotTables = [
            'model_has_roles' => 'model_id',
            'model_has_permissions' => 'model_id',
        ];

        foreach ($pivotTables as $table => $column) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            $query = DB::table($table)->whereIn($column, $ids);
            if (Schema::hasColumn($table, 'model_type')) {
                $query->where('model_type', User::class);
            }
            $query->delete();
        }

        foreach ([
            'sessions' => 'user_id',
            'personal_access_tokens' => 'tokenable_id',
            'password_history' => 'user_id',
            'trusted_devices' => 'user_id',
            'user_preferences' => 'user_id',
        ] as $table => $column) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            $query = DB::table($table)->whereIn($column, $ids);
            if ($table === 'personal_access_tokens' && Schema::hasColumn($table, 'tokenable_type')) {
                $query->where('tokenable_type', User::class);
            }
            $query->delete();
        }

        User::withTrashed()->whereIn('id', $ids)->forceDelete();

        return count($ids);
    }

    /**
     * @return Builder
     */
    protected function demoUserQuery(int $companyId, ?int $preserveUserId)
    {
        $query = User::withTrashed()
            ->where('company_id', $companyId)
            ->whereIn('email', array_merge(self::DEMO_EMAILS, self::SEEDED_DEMO_EMAILS));

        if ($preserveUserId !== null && $preserveUserId > 0) {
            $query->where('id', '!=', $preserveUserId);
        }

        return $query;
    }

    /**
     * Build a company-scoped query for one table. Returns null when the table
     * does not exist or cannot be safely scoped.
     */
    protected function scopeQuery(string $table, int $companyId): ?\Illuminate\Database\Query\Builder
    {
        if (! Schema::hasTable($table)) {
            return null;
        }

        $query = DB::table($table);

        if (Schema::hasColumn($table, 'company_id')) {
            return $query->where('company_id', $companyId);
        }

        if (! isset(self::CHILD_SCOPES[$table])) {
            return null;
        }

        [$foreignKey, $parent] = self::CHILD_SCOPES[$table];

        if (! Schema::hasTable($parent) || ! Schema::hasColumn($table, $foreignKey) || ! Schema::hasColumn($parent, 'company_id')) {
            return null;
        }

        $parentIds = DB::table($parent)
            ->where('company_id', $companyId)
            ->pluck('id')
            ->all();

        if ($parentIds === []) {
            return null;
        }

        return $query->whereIn($foreignKey, $parentIds);
    }
}
