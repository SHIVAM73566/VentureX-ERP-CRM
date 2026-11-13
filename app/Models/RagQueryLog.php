<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id', 'user_id', 'pipeline_id', 'query', 'mode',
    'top_score', 'result_count', 'latency_ms',
])]
class RagQueryLog extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return [
            'top_score' => 'float',
            'result_count' => 'integer',
            'latency_ms' => 'integer',
        ];
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(RagPipeline::class, 'pipeline_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
