<?php

namespace App\Policies;

class LeadPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'leads';
    }
}
