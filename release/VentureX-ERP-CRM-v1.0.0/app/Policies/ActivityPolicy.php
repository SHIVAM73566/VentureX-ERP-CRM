<?php

namespace App\Policies;

class ActivityPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'activities';
    }
}
