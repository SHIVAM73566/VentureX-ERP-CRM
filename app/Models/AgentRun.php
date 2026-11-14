<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id', 'agent_action_id', 'action_key', 'trigger', 'status',
    'dry_run', 'summary', 'details', 'duration_ms', 'started_at',
    'finished_at', 'created_by',
])]
class AgentRun extends Model
{
    use BelongsToCompany;

    public const STATUSES = [
        'running' => 'Running',
        'success' => 'Success',
        'skipped' => 'Skipped',
        'failed' => 'Failed',
        'rolled_back' => 'Rolled Back',
    ];

    protected function casts(): array
    {
        return [
            'dry_run' => 'boolean',
            'details' => 'array',
            'duration_ms' => 'decimal:2',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(AgentAction::class, 'agent_action_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
