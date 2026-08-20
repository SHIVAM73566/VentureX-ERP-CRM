<?php

namespace App\Policies;

class AuditLogPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'audit_logs';
    }
}
