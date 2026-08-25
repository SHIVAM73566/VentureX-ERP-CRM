<?php

namespace App\Policies;

class QuotationPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'sales';
    }
}
