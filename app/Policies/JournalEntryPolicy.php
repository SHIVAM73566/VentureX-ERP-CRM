<?php

namespace App\Policies;

class JournalEntryPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'finance';
    }
}
