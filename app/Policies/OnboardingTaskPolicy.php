<?php

namespace App\Policies;

class OnboardingTaskPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'hr';
    }
}
