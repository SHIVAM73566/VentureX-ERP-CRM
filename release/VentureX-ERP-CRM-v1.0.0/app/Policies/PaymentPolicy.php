<?php

namespace App\Policies;

class PaymentPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'sales';
    }
}
