<?php

namespace App\Policies;

class WarehousePolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'inventory';
    }
}
