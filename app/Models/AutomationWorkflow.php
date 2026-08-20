<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id', 'name', 'trigger_type', 'trigger_config', 'actions',
    'status', 'created_by',
])]
class AutomationWorkflow extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return [
            'trigger_config' => 'array',
            'actions' => 'array',
        ];
    }

    public function runs()
    {
        return $this->hasMany(AutomationRun::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
