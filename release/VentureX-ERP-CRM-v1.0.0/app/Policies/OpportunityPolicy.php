<?php

namespace App\Policies;

class OpportunityPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'opportunities';
    }
}
