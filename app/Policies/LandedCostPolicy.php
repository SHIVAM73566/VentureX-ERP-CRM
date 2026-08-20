<?php

namespace App\Policies;

class LandedCostPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'logistics';
    }
}
