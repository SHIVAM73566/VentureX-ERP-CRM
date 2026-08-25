<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'correlation_id', 'severity', 'category', 'event', 'title', 'details',
    'user_id', 'company_id', 'ip', 'user_agent', 'status', 'assigned_to',
    'resolved_at', 'resolution',
])]
class SecurityEvent extends Model
{
    use BelongsToCompany, HasFactory;

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
