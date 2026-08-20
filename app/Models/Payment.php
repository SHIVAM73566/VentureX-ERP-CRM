<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id', 'payment_number', 'customer_id', 'invoice_id', 'payment_date',
    'amount', 'method', 'reference', 'status', 'notes', 'created_by',
])]
class Payment extends Model
{
    use BelongsToCompany, SoftDeletes;

    public const STATUSES = [
        'pending' => 'Pending',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'refunded' => 'Refunded',
    ];

    public const METHODS = [
        'cash' => 'Cash',
        'bank' => 'Bank Transfer',
        'cheque' => 'Cheque',
        'card' => 'Card',
        'upi' => 'UPI',
        'other' => 'Other',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:4',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public function markCompleted(array $metadata = []): void
    {
        $this->update(['status' => 'completed']);
    }

    public function markFailed(string $reason = ''): void
    {
        $this->update(['status' => 'failed', 'notes' => $reason]);
    }

    public function markRefunded(): void
    {
        $this->update(['status' => 'refunded']);
    }
}
