<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'purchase_requisition_id', 'product_id', 'description', 'quantity', 'unit', 'notes', 'sort',
])]
class PurchaseRequisitionItem extends Model
{
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
        ];
    }

    public function purchaseRequisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
