<?php

namespace App\Policies;

class PerformanceReviewPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'hr';
    }
}
