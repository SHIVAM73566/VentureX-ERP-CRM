<?php

namespace App\Policies;

class AccountPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'finance';
    }
}
