<?php

namespace App\Policies;

use App\Models\User;

class ExportRequestPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'exports';
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('exports.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('exports.export');
    }

    public function approve(User $user): bool
    {
        return $user->hasPermissionTo('exports.approve');
    }
}
