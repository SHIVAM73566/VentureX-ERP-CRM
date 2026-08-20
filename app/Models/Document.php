<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id', 'type', 'documentable_type', 'documentable_id', 'title',
    'original_name', 'storage_path', 'mime_type', 'size', 'status',
    'extracted_data', 'ai_analysis', 'expires_at', 'tags', 'uploaded_by',
    'scan_status', 'scan_result', 'scanned_at', 'is_quarantined',
])]
class Document extends Model
{
    use BelongsToCompany, SoftDeletes;

    public const STATUSES = [
        'new' => 'New',
        'processing' => 'Processing',
        'reviewed' => 'Reviewed',
        'verified' => 'Verified',
        'rejected' => 'Rejected',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'extracted_data' => 'array',
            'ai_analysis' => 'array',
            'expires_at' => 'date',
            'tags' => 'array',
            'is_quarantined' => 'boolean',
            'scanned_at' => 'datetime',
        ];
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
