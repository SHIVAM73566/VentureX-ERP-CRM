<?php

namespace App\Policies;

use App\Models\User;

class ApprovalRequestPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'approvals';
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('approvals.review') || $user->hasRole(['super_admin', 'company_admin']);
    }

    public function approve(User $user): bool
    {
        return $user->hasPermissionTo('approvals.approve') || $user->hasRole(['super_admin', 'company_admin']);
    }
}
