<?php

namespace App\Policies;

class PurchaseOrderPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'purchase';
    }
}
