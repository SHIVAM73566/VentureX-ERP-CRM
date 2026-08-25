<?php

namespace App\Policies;

class SupplierOfferPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'supplier_offers';
    }
}
