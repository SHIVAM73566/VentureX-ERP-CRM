<?php

namespace App\Policies;

use App\Models\User;

trait HasModulePermissions
{
    abstract protected function module(): string;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo($this->module().'.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo($this->module().'.create');
    }

    public function view(User $user, $model): bool
    {
        return $user->hasPermissionTo($this->module().'.view') && $this->inCompany($user, $model);
    }

    public function update(User $user, $model): bool
    {
        return $user->hasPermissionTo($this->module().'.edit') && $this->inCompany($user, $model);
    }

    public function delete(User $user, $model): bool
    {
        return $user->hasPermissionTo($this->module().'.delete') && $this->inCompany($user, $model);
    }

    public function export(User $user, $model = null): bool
    {
        if (! $user->hasPermissionTo($this->module().'.export')) {
            return false;
        }

        return $model === null || $this->inCompany($user, $model);
    }

    public function import(User $user, $model = null): bool
    {
        if (! $user->hasPermissionTo($this->module().'.import')) {
            return false;
        }

        return $model === null || $this->inCompany($user, $model);
    }

    public function approve(User $user, $model = null): bool
    {
        if (! $user->hasPermissionTo($this->module().'.approve')) {
            return false;
        }

        return $model === null || $this->inCompany($user, $model);
    }

    protected function inCompany(User $user, $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // If model has company_id, it must match the user's company
        if (isset($model->company_id)) {
            return (int) $model->company_id === (int) $user->company_id;
        }

        // If model lacks company_id entirely, deny by default (secure fallback)
        return false;
    }
}
