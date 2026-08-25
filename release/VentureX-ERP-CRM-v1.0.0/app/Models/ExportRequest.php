<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'export_id', 'user_id', 'company_id', 'department', 'data_type',
    'record_ids', 'record_count', 'reason', 'format', 'sensitivity',
    'status', 'approved_by', 'approved_at', 'expires_at', 'max_downloads',
    'download_count', 'storage_path', 'ip', 'user_agent',
])]
class ExportRequest extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return [
            'record_ids' => 'array',
            'approved_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
