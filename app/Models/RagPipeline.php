<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id', 'key', 'name', 'domain', 'description', 'source_models',
    'retrieval_strategy', 'weight', 'confidence', 'is_active', 'config',
    'document_count', 'last_indexed_at', 'last_trained_at',
])]
class RagPipeline extends Model
{
    use BelongsToCompany;

    public const DOMAINS = [
        'crm' => 'CRM & Sales Analytics',
        'procurement' => 'Procurement & Supply Chain',
        'inventory' => 'Inventory & Warehouse Intelligence',
        'finance' => 'Finance, Invoicing & Ledger Auditing',
        'logistics' => 'Logistics & Customs Clearance',
        'executive' => 'Executive Venture Intelligence & Admin',
    ];

    protected function casts(): array
    {
        return [
            'source_models' => 'array',
            'config' => 'array',
            'weight' => 'float',
            'confidence' => 'float',
            'is_active' => 'boolean',
            'document_count' => 'integer',
            'last_indexed_at' => 'datetime',
            'last_trained_at' => 'datetime',
        ];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(RagDocument::class, 'pipeline_id');
    }

    public function getRouteKeyName(): string
    {
        return 'key';
    }

    public function queryLogs(): HasMany
    {
        return $this->hasMany(RagQueryLog::class, 'pipeline_id');
    }

    public function domainLabel(): string
    {
        return self::DOMAINS[$this->domain] ?? ucfirst((string) $this->domain);
    }
}
