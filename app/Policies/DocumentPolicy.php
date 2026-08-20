<?php

namespace App\Policies;

class DocumentPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'documents';
    }
}
