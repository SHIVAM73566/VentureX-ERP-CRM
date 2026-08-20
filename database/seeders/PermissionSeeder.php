<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $actions = ['view', 'create', 'edit', 'delete', 'export', 'import', 'approve'];

        $modules = [
            'dashboard',
            'customers',
            'contacts',
            'leads',
            'opportunities',
            'activities',
            'companies',
            'branches',
            'departments',
            'users',
            'roles',
            'settings',
            'suppliers',
            'supplier_offers',
            'documents',
            'ai',
            'ai_skills',
            'automation',
            'audit_logs',
            'reports',
            'sales',
            'purchase',
            'inventory',
            'finance',
            'logistics',
            'hr',
            'projects',
            'workflows',
            'exports',
        ];

        $permissions = [];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permissions[] = "{$module}.{$action}";
            }
        }

        $permissions = array_merge($permissions, [
            'settings.configure',
            'users.manage',
            'roles.manage',
            'audit.view',
            'reports.view',
            'ai.chat',
            'ai.configure',
            'ai.skills_manage',
            'approvals.review',
            'approvals.approve',
        ]);

        $now = now();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}
