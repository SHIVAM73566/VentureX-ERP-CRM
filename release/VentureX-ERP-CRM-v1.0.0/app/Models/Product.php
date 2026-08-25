<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id', 'sku', 'name', 'category', 'description', 'unit_id',
    'purchase_price', 'selling_price', 'tax_rate_id', 'reorder_level',
    'supplier_id', 'status', 'notes', 'created_by',
])]
class Product extends Model
{
    use BelongsToCompany, SoftDeletes;

    public const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:4',
            'selling_price' => 'decimal:4',
            'reorder_level' => 'decimal:4',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function availableStock(): float
    {
        $in = (float) $this->stockMovements()->where('type', 'in')->sum('quantity');
        $out = (float) $this->stockMovements()->where('type', 'out')->sum('quantity');
        $adjustment = (float) $this->stockMovements()->where('type', 'adjustment')->sum('quantity');

        return $in - $out + $adjustment;
    }

    public function isLowStock(): bool
    {
        return (float) $this->availableStock() <= (float) $this->reorder_level;
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
