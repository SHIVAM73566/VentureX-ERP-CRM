<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id', 'user_id', 'skill_slug', 'provider', 'model', 'status',
    'input_type', 'input', 'output', 'error', 'prompt_tokens',
    'completion_tokens', 'cost', 'started_at', 'finished_at',
])]
class AiRun extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return [
            'input' => 'array',
            'output' => 'array',
            'error' => 'array',
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'cost' => 'decimal:6',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
