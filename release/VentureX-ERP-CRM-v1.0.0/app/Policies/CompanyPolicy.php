<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'companies';
    }

    public function delete(User $user, Company $company): bool
    {
        if ($user->hasRole('super_admin')) {
            return $user->hasPermissionTo('companies.delete');
        }

        return false;
    }
}
