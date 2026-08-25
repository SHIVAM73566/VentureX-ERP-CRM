<?php

namespace App\Policies;

class ProductPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'inventory';
    }
}
