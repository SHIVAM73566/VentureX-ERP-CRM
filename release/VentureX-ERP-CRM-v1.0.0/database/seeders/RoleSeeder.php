<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $all = Permission::pluck('name')->all();

        $roles = [
            'super_admin' => [
                'label' => 'Super Admin',
                'permissions' => $all,
            ],
            'company_admin' => [
                'label' => 'Company Admin',
                'permissions' => array_merge(
                    $this->forModules(['dashboard', 'customers', 'contacts', 'leads', 'opportunities', 'activities', 'branches', 'departments', 'suppliers', 'supplier_offers', 'documents', 'sales', 'purchase', 'inventory', 'finance', 'logistics', 'hr', 'projects', 'reports', 'automation', 'workflows', 'ai_skills']),
                    ['settings.configure', 'users.manage', 'audit.view', 'reports.view', 'ai.chat', 'ai.configure', 'ai.skills_manage', 'approvals.review', 'approvals.approve']
                ),
            ],
            'ceo' => [
                'label' => 'CEO',
                'permissions' => $this->viewOnly(['dashboard', 'customers', 'contacts', 'leads', 'opportunities', 'suppliers', 'supplier_offers', 'documents', 'sales', 'purchase', 'inventory', 'finance', 'logistics', 'hr', 'projects', 'reports', 'audit_logs', 'workflows', 'ai']),
            ],
            'cfo' => [
                'label' => 'CFO',
                'permissions' => array_merge(
                    $this->viewOnly(['dashboard', 'finance', 'purchase', 'sales', 'customers', 'suppliers', 'reports', 'inventory', 'audit_logs']),
                    $this->editable('finance', ['view', 'create', 'edit', 'approve', 'export']),
                    $this->editable('sales', ['view', 'edit', 'approve']),
                    $this->editable('purchase', ['view', 'edit', 'approve']),
                ),
            ],
            'coo' => [
                'label' => 'COO',
                'permissions' => array_merge(
                    $this->viewOnly(['dashboard', 'customers', 'leads', 'opportunities', 'suppliers', 'supplier_offers', 'sales', 'purchase', 'inventory', 'logistics', 'projects', 'workflows', 'documents', 'reports']),
                    $this->editable('inventory', ['view', 'create', 'edit', 'export']),
                    $this->editable('logistics', ['view', 'create', 'edit', 'approve', 'export']),
                ),
            ],
            'sales_manager' => [
                'label' => 'Sales Manager',
                'permissions' => array_merge(
                    $this->editable('customers', ['view', 'create', 'edit', 'export', 'import']),
                    $this->editable('contacts', ['view', 'create', 'edit', 'export']),
                    $this->editable('leads', ['view', 'create', 'edit', 'export', 'import', 'approve']),
                    $this->editable('opportunities', ['view', 'create', 'edit', 'export', 'approve']),
                    $this->editable('activities', ['view', 'create', 'edit']),
                    $this->editable('sales', ['view', 'create', 'edit', 'approve', 'export']),
                    $this->viewOnly(['dashboard', 'reports', 'documents']),
                ),
            ],
            'sales_executive' => [
                'label' => 'Sales Executive',
                'permissions' => array_merge(
                    $this->editable('customers', ['view', 'create', 'edit']),
                    $this->editable('contacts', ['view', 'create', 'edit']),
                    $this->editable('leads', ['view', 'create', 'edit']),
                    $this->editable('opportunities', ['view', 'create', 'edit']),
                    $this->editable('activities', ['view', 'create', 'edit']),
                    $this->viewOnly(['dashboard', 'sales']),
                ),
            ],
            'purchase_manager' => [
                'label' => 'Purchase Manager',
                'permissions' => array_merge(
                    $this->editable('suppliers', ['view', 'create', 'edit', 'approve', 'export']),
                    $this->editable('supplier_offers', ['view', 'create', 'edit', 'approve', 'export', 'import']),
                    $this->editable('purchase', ['view', 'create', 'edit', 'approve', 'export']),
                    $this->editable('activities', ['view', 'create', 'edit']),
                    $this->editable('documents', ['view', 'create', 'edit']),
                    $this->viewOnly(['dashboard', 'reports', 'inventory', 'logistics']),
                ),
            ],
            'procurement_officer' => [
                'label' => 'Procurement Officer',
                'permissions' => array_merge(
                    $this->editable('suppliers', ['view', 'create', 'edit', 'export']),
                    $this->editable('supplier_offers', ['view', 'create', 'edit', 'export', 'import']),
                    $this->editable('purchase', ['view', 'create', 'edit', 'export']),
                    $this->editable('documents', ['view', 'create', 'edit']),
                    $this->viewOnly(['dashboard', 'inventory', 'logistics']),
                ),
            ],
            'warehouse_manager' => [
                'label' => 'Warehouse Manager',
                'permissions' => array_merge(
                    $this->editable('inventory', ['view', 'create', 'edit', 'export', 'import']),
                    $this->editable('suppliers', ['view']),
                    $this->editable('documents', ['view', 'create']),
                    $this->viewOnly(['dashboard', 'purchase', 'sales']),
                ),
            ],
            'logistics_manager' => [
                'label' => 'Logistics Manager',
                'permissions' => array_merge(
                    $this->editable('logistics', ['view', 'create', 'edit', 'export']),
                    $this->viewOnly(['dashboard', 'suppliers', 'purchase', 'sales', 'documents']),
                ),
            ],
            'finance_manager' => [
                'label' => 'Finance Manager',
                'permissions' => array_merge(
                    $this->editable('finance', ['view', 'create', 'edit', 'approve', 'export']),
                    $this->editable('purchase', ['view', 'create', 'edit', 'approve']),
                    $this->editable('sales', ['view', 'create', 'edit', 'approve']),
                    $this->viewOnly(['dashboard', 'customers', 'suppliers', 'reports', 'audit_logs']),
                ),
            ],
            'accountant' => [
                'label' => 'Accountant',
                'permissions' => array_merge(
                    $this->editable('finance', ['view', 'create', 'edit', 'export']),
                    $this->viewOnly(['dashboard', 'customers', 'suppliers', 'sales', 'purchase', 'reports']),
                ),
            ],
            'hr_manager' => [
                'label' => 'HR Manager',
                'permissions' => array_merge(
                    $this->editable('hr', ['view', 'create', 'edit', 'export', 'import', 'approve']),
                    $this->editable('documents', ['view', 'create', 'edit']),
                    $this->viewOnly(['dashboard', 'users', 'departments']),
                ),
            ],
            'employee' => [
                'label' => 'Employee',
                'permissions' => $this->viewOnly(['dashboard', 'hr', 'documents', 'projects', 'activities']),
            ],
            'viewer' => [
                'label' => 'Viewer',
                'permissions' => $this->viewOnly(['dashboard', 'customers', 'leads', 'opportunities', 'suppliers', 'supplier_offers', 'sales', 'purchase', 'inventory', 'finance', 'logistics', 'documents', 'reports', 'hr', 'projects']),
            ],
        ];

        foreach ($roles as $name => $config) {
            $role = Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            $role->update(['label' => $config['label']]);
            $role->syncPermissions($config['permissions']);
        }
    }

    protected function forModules(array $modules): array
    {
        $result = [];
        foreach ($modules as $module) {
            $result = array_merge($result, $this->editable($module, ['view', 'create', 'edit', 'delete', 'export', 'import', 'approve']));
        }

        return $result;
    }

    protected function viewOnly(array $modules): array
    {
        $result = [];
        foreach ($modules as $module) {
            $result[] = "{$module}.view";
        }

        return $result;
    }

    protected function editable(string $module, array $actions): array
    {
        return array_map(fn ($action) => "{$module}.{$action}", $actions);
    }
}
