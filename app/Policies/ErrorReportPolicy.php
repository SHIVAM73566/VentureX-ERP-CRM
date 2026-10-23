<?php

namespace App\Policies;

use App\Models\User;

class ErrorReportPolicy
{
    /**
     * Any authenticated user may report an error.
     */
    public function create(User $user): bool
    {
        return true;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('tickets.view');
    }

    public function view(User $user, $model): bool
    {
        return $user->hasPermissionTo('tickets.view') && $this->inCompany($user, $model);
    }

    public function update(User $user, $model): bool
    {
        return $user->hasPermissionTo('tickets.edit') && $this->inCompany($user, $model);
    }

    public function delete(User $user, $model): bool
    {
        return $user->hasPermissionTo('tickets.delete') && $this->inCompany($user, $model);
    }

    protected function inCompany(User $user, $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if (isset($model->company_id)) {
            return (int) $model->company_id === (int) $user->company_id;
        }

        return false;
    }
}
