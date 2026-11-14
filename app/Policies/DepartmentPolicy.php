<?php

namespace App\Policies;

class DepartmentPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'departments';
    }
}
