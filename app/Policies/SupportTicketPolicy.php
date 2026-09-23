<?php

namespace App\Policies;

class SupportTicketPolicy
{
    use HasModulePermissions;

    protected function module(): string
    {
        return 'tickets';
    }
}