<?php

namespace App\Policies;

class RfqPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'purchase';
    }
}
