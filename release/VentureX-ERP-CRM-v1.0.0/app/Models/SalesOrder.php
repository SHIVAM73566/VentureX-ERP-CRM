<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id', 'order_number', 'customer_id', 'quotation_id', 'status',
    'payment_status', 'order_date', 'delivery_date', 'subtotal', 'discount',
    'tax', 'shipping', 'total', 'paid_amount', 'notes', 'created_by',
])]
class SalesOrder extends Model
{
    use BelongsToCompany, SoftDeletes;

    public const STATUSES = [
        'draft' => 'Draft',
        'confirmed' => 'Confirmed',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'completed' => 'Completed',
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
            'delivery_date' => 'date',
            'subtotal' => 'decimal:4',
            'discount' => 'decimal:4',
            'tax' => 'decimal:4',
            'shipping' => 'decimal:4',
            'total' => 'decimal:4',
            'paid_amount' => 'decimal:4',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class)->orderBy('sort');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function outstandingBalance(): float
    {
        return (float) $this->total - (float) $this->paid_amount;
    }
}
