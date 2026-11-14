<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id', 'key', 'name', 'description', 'category', 'trigger',
    'is_enabled', 'requires_approval', 'cooldown_minutes', 'config',
    'confidence', 'run_count', 'success_count', 'failure_count',
    'last_run_at', 'last_result',
])]
class AgentAction extends Model
{
    use BelongsToCompany;

    public const CATEGORIES = [
        'inventory' => 'Inventory',
        'finance' => 'Finance',
        'support' => 'Support',
        'hr' => 'Human Resources',
        'operations' => 'Operations',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'requires_approval' => 'boolean',
            'config' => 'array',
            'confidence' => 'decimal:4',
            'last_run_at' => 'datetime',
        ];
    }

    public function runs(): HasMany
    {
        return $this->hasMany(AgentRun::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function isDue(): bool
    {
        if (! $this->is_enabled) {
            return false;
        }

        if (! $this->last_run_at) {
            return true;
        }

        return $this->last_run_at->copy()->addMinutes((int) $this->cooldown_minutes)->isPast();
    }

    public function successRate(): float
    {
        $total = (int) $this->success_count + (int) $this->failure_count;

        return $total > 0 ? (int) $this->success_count / $total : 0.0;
    }
}
