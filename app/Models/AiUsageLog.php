<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id', 'user_id', 'provider', 'model', 'task', 'request_type',
    'status', 'latency_ms', 'prompt_tokens', 'completion_tokens', 'cost',
    'error_category', 'error_message', 'ip_address',
])]
class AiUsageLog extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'latency_ms' => 'integer',
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'cost' => 'decimal:8',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
