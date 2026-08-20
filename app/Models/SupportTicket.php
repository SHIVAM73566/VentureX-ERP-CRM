<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id', 'user_id', 'ticket_number', 'subject', 'description', 'module',
    'priority', 'status', 'category', 'assigned_to', 'resolved_at', 'closed_at',
    'customer_satisfaction',
])]
class SupportTicket extends Model
{
    use BelongsToCompany, SoftDeletes;

    public const PRIORITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ];

    public const STATUSES = [
        'open' => 'Open',
        'investigating' => 'Investigating',
        'in_progress' => 'In Progress',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ];

    public const CATEGORIES = [
        'bug' => 'Bug',
        'feature_request' => 'Feature Request',
        'question' => 'Question',
        'installation' => 'Installation',
        'other' => 'Other',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'customer_satisfaction' => 'integer',
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

    public function replies(): HasMany
    {
        return $this->hasMany(SupportReply::class, 'ticket_id');
    }

    public function errorReports(): HasMany
    {
        return $this->hasMany(ErrorReport::class, 'ticket_id');
    }

    public function errorReport(): HasOne
    {
        return $this->hasOne(ErrorReport::class, 'ticket_id');
    }

    public static function generateTicketNumber(): string
    {
        $date = now()->format('Ymd');
        $last = static::where('ticket_number', 'like', "TKT-{$date}-%")
            ->orderByDesc('ticket_number')
            ->value('ticket_number');

        $sequence = $last ? (int) substr($last, -4) + 1 : 1;

        return sprintf('TKT-%s-%04d', $date, $sequence);
    }
}
