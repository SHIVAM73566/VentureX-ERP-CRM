<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id', 'container_number', 'size', 'type', 'seal_number', 'capacity', 'weight', 'status', 'notes',
])]
class Container extends Model
{
    use BelongsToCompany, SoftDeletes;

    public const STATUSES = [
        'empty' => 'Empty',
        'loading' => 'Loading',
        'loaded' => 'Loaded',
        'in_transit' => 'In Transit',
        'at_port' => 'At Port',
        'delivered' => 'Delivered',
    ];

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function landedCosts(): HasMany
    {
        return $this->hasMany(LandedCost::class);
    }
}
