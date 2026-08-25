<?php

namespace App\Policies;

class ContactPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'contacts';
    }
}
