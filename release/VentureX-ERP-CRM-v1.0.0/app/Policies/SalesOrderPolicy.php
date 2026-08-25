<?php

namespace App\Policies;

class SalesOrderPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'sales';
    }
}
