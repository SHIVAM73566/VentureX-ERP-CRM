<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'title', 'message', 'type', 'target', 'target_companies',
    'is_active', 'published_at', 'expires_at', 'created_by',
])]
class SystemAnnouncement extends Model
{
    public const TYPES = [
        'info' => 'Info',
        'warning' => 'Warning',
        'critical' => 'Critical',
        'update' => 'Update',
    ];

    public const TARGETS = [
        'all' => 'All',
        'specific_companies' => 'Specific Companies',
    ];

    protected function casts(): array
    {
        return [
            'target_companies' => 'array',
            'is_active' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->published_at !== null && $this->published_at->isFuture()) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
