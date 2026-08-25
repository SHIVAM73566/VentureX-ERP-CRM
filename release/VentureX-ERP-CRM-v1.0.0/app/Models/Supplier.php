<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id', 'name', 'supplier_code', 'tax_id', 'contact_person', 'email',
    'phone', 'website', 'address_line1', 'city', 'state', 'postal_code',
    'country_id', 'currency_code', 'status', 'payment_terms', 'notes', 'created_by',
])]
class Supplier extends Model
{
    use BelongsToCompany, SoftDeletes;

    public const STATUSES = [
        'pending' => 'Pending',
        'verified' => 'Verified',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'blocked' => 'Blocked',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(SupplierOffer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
