<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Import extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'user_id', 'file_name', 'file_path', 'file_type',
        'file_size', 'destination', 'import_mode', 'duplicate_strategy',
        'column_mapping', 'transformations', 'settings', 'total_rows',
        'processed_rows', 'created_rows', 'updated_rows', 'skipped_rows',
        'failed_rows', 'status', 'error_summary', 'started_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'column_mapping' => 'array',
            'transformations' => 'array',
            'settings' => 'array',
            'total_rows' => 'integer',
            'processed_rows' => 'integer',
            'created_rows' => 'integer',
            'updated_rows' => 'integer',
            'skipped_rows' => 'integer',
            'failed_rows' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ImportRow::class);
    }

    public function getDestinationLabel(): string
    {
        return match ($this->destination) {
            'customers' => 'Customers',
            'contacts' => 'Contacts',
            'leads' => 'Leads',
            'opportunities' => 'Opportunities',
            'products' => 'Products',
            'suppliers' => 'Suppliers',
            'invoices' => 'Invoices',
            'payments' => 'Payments',
            'purchase_orders' => 'Purchase Orders',
            'quotations' => 'Quotations',
            'sales_orders' => 'Sales Orders',
            'employees' => 'Employees',
            default => ucfirst(str_replace('_', ' ', $this->destination)),
        };
    }
}
