<?php

namespace App\Policies;

class ShipmentPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'logistics';
    }
}
