<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id', 'name', 'slug', 'description', 'provider', 'model', 'temperature',
    'top_p', 'max_tokens', 'instructions', 'input_schema', 'output_schema',
    'safety_rules', 'activation_rules', 'version', 'is_active', 'created_by',
])]
class AiSkill extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return [
            'temperature' => 'decimal:2',
            'top_p' => 'decimal:2',
            'max_tokens' => 'integer',
            'input_schema' => 'array',
            'output_schema' => 'array',
            'safety_rules' => 'array',
            'activation_rules' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
