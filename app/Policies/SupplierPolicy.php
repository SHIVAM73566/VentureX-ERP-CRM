<?php

namespace App\Policies;

class SupplierPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'suppliers';
    }
}
