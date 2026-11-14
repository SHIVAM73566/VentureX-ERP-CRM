<?php

namespace App\Policies;

class EmployeePolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'hr';
    }
}
