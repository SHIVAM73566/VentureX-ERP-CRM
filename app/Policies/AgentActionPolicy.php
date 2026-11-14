<?php

namespace App\Policies;

class AgentActionPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'automation';
    }
}
