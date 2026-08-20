<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalRequest extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'requester_id', 'request_type', 'resource_type', 'resource_id',
        'title', 'description', 'metadata', 'risk_level',
        'required_approvals', 'final_approver_id',
        'expires_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'required_approvals' => 'integer',
        'current_approvals' => 'integer',
        'finalized_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public const STATUSES = ['pending', 'approved', 'rejected', 'expired', 'cancelled'];

    public const RISK_LEVELS = ['low', 'medium', 'high', 'critical'];

    public const REQUEST_TYPES = [
        'user', 'role', 'permission', 'export', 'purchase', 'payment',
        'inventory', 'ai_quota', 'ai_advanced', 'security',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function finalApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'final_approver_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ApprovalAction::class, 'approval_request_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function approve(User $approver, ?string $comment = null): void
    {
        $this->actions()->create([
            'approver_id' => $approver->id,
            'action' => 'approve',
            'comment' => $comment,
        ]);

        $this->increment('current_approvals');

        if ($this->current_approvals >= $this->required_approvals) {
            $this->forceFill([
                'status' => 'approved',
                'final_approver_id' => $approver->id,
                'finalized_at' => now(),
            ])->save();
        }
    }

    public function reject(User $approver, ?string $comment = null): void
    {
        $this->actions()->create([
            'approver_id' => $approver->id,
            'action' => 'reject',
            'comment' => $comment,
        ]);

        $this->forceFill([
            'status' => 'rejected',
            'final_approver_id' => $approver->id,
            'finalized_at' => now(),
        ])->save();
    }
}
