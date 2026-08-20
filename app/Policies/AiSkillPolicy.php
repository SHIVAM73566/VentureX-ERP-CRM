<?php

namespace App\Policies;

class AiSkillPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'ai_skills';
    }
}
