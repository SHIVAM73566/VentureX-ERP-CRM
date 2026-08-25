<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id', 'supplier_id', 'source_email', 'contact_person',
    'material_category', 'material_description', 'grade', 'isri_grade',
    'quantity_mt', 'price_per_mt', 'currency_code', 'delivery_location',
    'payment_terms', 'loading_terms', 'cu_percent', 'fe_percent', 'ni_percent',
    'cr_percent', 'pb_percent', 'zn_percent', 'al_percent', 'mn_percent',
    'mo_percent', 'other_elements', 'coa_number', 'spectro_report_number',
    'coa_available', 'offer_date', 'validity_date', 'grade_match',
    'quality_status', 'risk_level', 'estimated_metal_value', 'buyer_action',
    'ai_analysis', 'source_document_id', 'created_by',
])]
class SupplierOffer extends Model
{
    use BelongsToCompany, SoftDeletes;

    public const QUALITY_STATUSES = ['GREEN', 'YELLOW', 'RED'];

    protected function casts(): array
    {
        return [
            'quantity_mt' => 'decimal:4',
            'price_per_mt' => 'decimal:4',
            'cu_percent' => 'decimal:4',
            'fe_percent' => 'decimal:4',
            'ni_percent' => 'decimal:4',
            'cr_percent' => 'decimal:4',
            'pb_percent' => 'decimal:4',
            'zn_percent' => 'decimal:4',
            'al_percent' => 'decimal:4',
            'mn_percent' => 'decimal:4',
            'mo_percent' => 'decimal:4',
            'coa_available' => 'boolean',
            'offer_date' => 'date',
            'validity_date' => 'date',
            'estimated_metal_value' => 'decimal:4',
            'ai_analysis' => 'array',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function sourceDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'source_document_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
