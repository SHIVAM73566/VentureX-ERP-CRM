<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'user_id', 'type', 'notifiable_type', 'notifiable_id', 'title', 'message',
    'is_read', 'read_at', 'data',
])]
class SupportNotification extends Model
{
    public const TYPES = [
        'ticket_created' => 'Ticket Created',
        'ticket_replied' => 'Ticket Replied',
        'ticket_resolved' => 'Ticket Resolved',
        'ticket_closed' => 'Ticket Closed',
        'announcement' => 'Announcement',
        'update_available' => 'Update Available',
    ];

    protected function casts(): array
    {
        return [
            'notifiable_id' => 'integer',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function markAsRead(): void
    {
        if (! $this->is_read) {
            $this->update(['is_read' => true, 'read_at' => now()]);
        }
    }
}
