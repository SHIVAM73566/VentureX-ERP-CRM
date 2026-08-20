<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'users';
    }

    public function update(User $user, User $model): bool
    {
        if (! $user->hasPermissionTo('users.edit')) {
            return false;
        }

        if ($model->hasRole('super_admin') && ! $user->hasRole('super_admin')) {
            return false;
        }

        return $model->company_id === $user->company_id || $user->hasRole('super_admin');
    }

    public function delete(User $user, User $model): bool
    {
        if (! $user->hasPermissionTo('users.delete')) {
            return false;
        }

        if ($model->is($user)) {
            return false;
        }

        if ($model->hasRole('super_admin') && ! $user->hasRole('super_admin')) {
            return false;
        }

        return $model->company_id === $user->company_id || $user->hasRole('super_admin');
    }
}
