<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'company_id', 'type', 'subject_type', 'subject_id', 'title', 'description',
    'due_at', 'completed_at', 'status', 'assigned_to', 'created_by',
])]
class Activity extends Model
{
    use BelongsToCompany;

    public const TYPES = [
        'call' => 'Call',
        'meeting' => 'Meeting',
        'email' => 'Email',
        'task' => 'Task',
        'note' => 'Note',
        'follow_up' => 'Follow-up',
    ];

    protected function casts(): array
    {
        return ['due_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
