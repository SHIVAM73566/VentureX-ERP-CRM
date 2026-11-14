<?php

namespace App\Policies;

class LeaveRequestPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'hr';
    }
}
