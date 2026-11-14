<?php

namespace App\Policies;

class AttendancePolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'hr';
    }
}
