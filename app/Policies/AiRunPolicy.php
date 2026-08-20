<?php

namespace App\Policies;

class AiRunPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'ai';
    }
}
