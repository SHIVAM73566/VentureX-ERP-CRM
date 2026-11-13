<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id', 'pipeline_id', 'source_type', 'source_id', 'title', 'content',
    'embedding', 'embedding_dim', 'metadata', 'confidence', 'tokens',
])]
class RagDocument extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return [
            'embedding' => 'array',
            'metadata' => 'array',
            'embedding_dim' => 'integer',
            'confidence' => 'float',
            'tokens' => 'integer',
        ];
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(RagPipeline::class, 'pipeline_id');
    }
}
