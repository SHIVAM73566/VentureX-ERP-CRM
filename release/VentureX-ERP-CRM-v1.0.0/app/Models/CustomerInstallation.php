<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id', 'installation_id', 'app_version', 'php_version',
    'laravel_version', 'mysql_version', 'server_os', 'server_software',
    'domain', 'last_heartbeat_at', 'status', 'metadata',
])]
class CustomerInstallation extends Model
{
    use BelongsToCompany;

    public const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'abandoned' => 'Abandoned',
    ];

    protected function casts(): array
    {
        return [
            'last_heartbeat_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
