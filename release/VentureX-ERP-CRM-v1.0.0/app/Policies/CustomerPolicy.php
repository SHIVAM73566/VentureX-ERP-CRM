<?php

namespace App\Policies;

class CustomerPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'customers';
    }
}
