<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'version', 'title', 'description', 'changelog', 'type', 'min_php_version',
    'min_laravel_version', 'download_url', 'is_mandatory', 'released_at',
    'created_by',
])]
class ProductUpdate extends Model
{
    public const TYPES = [
        'patch' => 'Patch',
        'minor' => 'Minor',
        'major' => 'Major',
    ];

    protected function casts(): array
    {
        return [
            'is_mandatory' => 'boolean',
            'released_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
