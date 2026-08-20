<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id', 'shipment_id', 'container_id', 'purchase_order_id', 'cost_type', 'description',
    'amount', 'currency', 'paid_to', 'paid_at', 'notes', 'created_by',
])]
class LandedCost extends Model
{
    use BelongsToCompany, SoftDeletes;

    public const COST_TYPES = [
        'freight' => 'Freight',
        'customs_duty' => 'Customs Duty',
        'insurance' => 'Insurance',
        'handling' => 'Handling',
        'storage' => 'Storage',
        'inspection' => 'Inspection',
        'other' => 'Other',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'paid_at' => 'date',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
