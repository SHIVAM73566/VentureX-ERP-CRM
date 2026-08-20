<?php

namespace App\Policies;

class ContainerPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'logistics';
    }
}
