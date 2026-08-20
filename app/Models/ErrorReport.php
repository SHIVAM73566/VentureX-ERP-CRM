<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id', 'user_id', 'ticket_id', 'error_hash', 'error_type', 'error_message',
    'module', 'controller', 'action', 'route', 'file', 'line', 'stack_trace',
    'http_method', 'http_status', 'url', 'ip_address', 'user_agent', 'app_version',
    'php_version', 'laravel_version', 'server_software', 'request_data',
    'session_data', 'status', 'diagnosis', 'resolution', 'fix_applied', 'fixed_at',
    'occurrence_count', 'first_seen_at', 'last_seen_at',
])]
class ErrorReport extends Model
{
    use BelongsToCompany;

    public const ERROR_TYPES = [
        'error' => 'Error',
        'exception' => 'Exception',
        'warning' => 'Warning',
        'crash' => 'Crash',
    ];

    public const STATUSES = [
        'new' => 'New',
        'investigating' => 'Investigating',
        'confirmed' => 'Confirmed',
        'fixed' => 'Fixed',
        'wont_fix' => 'Won\'t Fix',
        'duplicate' => 'Duplicate',
    ];

    protected function casts(): array
    {
        return [
            'line' => 'integer',
            'http_status' => 'integer',
            'request_data' => 'array',
            'session_data' => 'array',
            'fixed_at' => 'datetime',
            'occurrence_count' => 'integer',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public static function generateErrorHash(string $message, string $file = '', int $line = 0): string
    {
        return hash('sha256', "{$message}:{$file}:{$line}");
    }
}
