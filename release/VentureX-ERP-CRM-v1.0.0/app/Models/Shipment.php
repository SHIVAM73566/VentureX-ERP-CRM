<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id', 'shipment_number', 'container_id', 'customer_id', 'supplier_id',
    'sales_order_id', 'purchase_order_id', 'warehouse_id', 'type', 'status', 'mode', 'carrier',
    'tracking_number', 'origin', 'destination', 'departure_date', 'arrival_date',
    'shipped_at', 'delivered_at', 'weight', 'volume', 'notes', 'created_by',
])]
class Shipment extends Model
{
    use BelongsToCompany, SoftDeletes;

    public const TYPES = ['inbound' => 'Inbound', 'outbound' => 'Outbound'];

    public const STATUSES = [
        'draft' => 'Draft',
        'scheduled' => 'Scheduled',
        'in_transit' => 'In Transit',
        'customs' => 'At Customs',
        'delivered' => 'Delivered',
        'delayed' => 'Delayed',
        'cancelled' => 'Cancelled',
    ];

    public const MODES = [
        'sea' => 'Sea',
        'air' => 'Air',
        'road' => 'Road',
        'rail' => 'Rail',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'arrival_date' => 'date',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'weight' => 'decimal:4',
            'volume' => 'decimal:4',
        ];
    }

    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
