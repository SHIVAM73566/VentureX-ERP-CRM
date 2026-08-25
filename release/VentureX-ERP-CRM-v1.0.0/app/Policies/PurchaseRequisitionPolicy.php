<?php

namespace App\Policies;

class PurchaseRequisitionPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'purchase';
    }
}
