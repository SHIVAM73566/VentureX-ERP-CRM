<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Central approval workflow engine. Handles creating, approving, rejecting,
 * and expiring approval requests. Supports multi-level and dual-control.
 */
class ApprovalService
{
    /**
     * Create an approval request. If the request type doesn't require approval
     * (based on risk level and role thresholds), it is auto-approved.
     */
    public function request(
        string $type,
        string $title,
        ?string $description = null,
        ?array $metadata = null,
        ?string $resourceType = null,
        ?int $resourceId = null,
        string $riskLevel = 'low',
        int $requiredApprovals = 1,
    ): ApprovalRequest {
        $user = auth()->user();

        return ApprovalRequest::create([
            'company_id' => CompanyContext::id(),
            'requester_id' => $user?->id,
            'request_type' => $type,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'title' => $title,
            'description' => $description,
            'metadata' => $metadata,
            'risk_level' => $riskLevel,
            'status' => 'pending',
            'required_approvals' => $requiredApprovals,
            'expires_at' => now()->addDays(7),
        ]);
    }

    /**
     * Approve a pending request. Checks that the approver is not the requester
     * (dual control) when the request requires it.
     */
    public function approve(ApprovalRequest $request, User $approver, ?string $comment = null): void
    {
        if ($request->status !== 'pending') {
            throw new \RuntimeException('This request is not pending.');
        }

        if ($request->expires_at && $request->expires_at->isPast()) {
            $request->forceFill(['status' => 'expired'])->save();
            throw new \RuntimeException('This request has expired.');
        }

        if ((int) $request->requester_id === (int) $approver->id && $request->risk_level !== 'low') {
            throw new \RuntimeException('You cannot approve your own request for medium or higher risk actions.');
        }

        $request->approve($approver, $comment);
    }

    /**
     * Reject a pending request.
     */
    public function reject(ApprovalRequest $request, User $approver, ?string $comment = null): void
    {
        if ($request->status !== 'pending') {
            throw new \RuntimeException('This request is not pending.');
        }

        $request->reject($approver, $comment);
    }

    /**
     * Expire requests that have passed their expiration date.
     */
    public function expireStale(): int
    {
        return ApprovalRequest::query()
            ->where('status', 'pending')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);
    }

    /**
     * Get pending approvals for a user (based on their permissions).
     */
    public function pendingFor(User $user): Builder
    {
        return ApprovalRequest::query()
            ->where('company_id', $user->company_id)
            ->where('status', 'pending')
            ->where('requester_id', '!=', $user->id)
            ->latest();
    }

    /**
     * Get all approvals for a user (as requester).
     */
    public function forUser(User $user): Builder
    {
        return ApprovalRequest::query()
            ->where('requester_id', $user->id)
            ->latest();
    }

    /**
     * Count pending approvals for a company.
     */
    public function pendingCount(?int $companyId = null): int
    {
        $query = ApprovalRequest::query()->where('status', 'pending');
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->count();
    }
}
