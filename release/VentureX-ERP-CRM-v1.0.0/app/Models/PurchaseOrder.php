<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id', 'po_number', 'supplier_id', 'rfq_id', 'status',
    'payment_status', 'order_date', 'expected_date', 'subtotal', 'discount',
    'tax', 'shipping', 'total', 'paid_amount', 'payment_terms', 'notes', 'created_by',
])]
class PurchaseOrder extends Model
{
    use BelongsToCompany, SoftDeletes;

    public const STATUSES = [
        'draft' => 'Draft',
        'pending' => 'Pending Approval',
        'approved' => 'Approved',
        'ordered' => 'Ordered',
        'partially_received' => 'Partially Received',
        'received' => 'Received',
        'cancelled' => 'Cancelled',
    ];

    public const PAYMENT_STATUSES = [
        'unpaid' => 'Unpaid',
        'partial' => 'Partially Paid',
        'paid' => 'Paid',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'expected_date' => 'date',
            'subtotal' => 'decimal:4',
            'discount' => 'decimal:4',
            'tax' => 'decimal:4',
            'shipping' => 'decimal:4',
            'total' => 'decimal:4',
            'paid_amount' => 'decimal:4',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class)->orderBy('sort');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function outstandingBalance(): float
    {
        return max(0, (float) $this->total - (float) $this->paid_amount);
    }
}
