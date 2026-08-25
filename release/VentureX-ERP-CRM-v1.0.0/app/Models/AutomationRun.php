<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['workflow_id', 'status', 'context', 'results', 'error', 'ran_at'])]
class AutomationRun extends Model
{
    protected function casts(): array
    {
        return [
            'context' => 'array',
            'results' => 'array',
            'error' => 'array',
            'ran_at' => 'datetime',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(AutomationWorkflow::class);
    }
}
