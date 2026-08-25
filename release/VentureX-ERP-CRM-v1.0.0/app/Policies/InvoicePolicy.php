<?php

namespace App\Policies;

class InvoicePolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'sales';
    }
}
