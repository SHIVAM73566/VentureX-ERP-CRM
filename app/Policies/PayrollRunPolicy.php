<?php

namespace App\Policies;

class PayrollRunPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'hr';
    }
}
