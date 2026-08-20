<?php

namespace App\Policies;

class StockMovementPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'inventory';
    }
}
