<?php

namespace App\Policies;

class AgentRunPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'automation';
    }
}
