<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Services\ApprovalService;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function __construct(protected ApprovalService $approvals) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ApprovalRequest::class);

        $user = auth()->user();
        $tab = $request->string('tab', 'pending');

        $query = match ($tab) {
            'my-requests' => $this->approvals->forUser($user),
            'all' => ApprovalRequest::query()->where('company_id', $user->company_id)->latest(),
            default => $this->approvals->pendingFor($user),
        };

        $requests = $query
            ->with('requester:id,name,email', 'finalApprover:id,name,email')
            ->paginate(15)
            ->withQueryString();

        return view('admin.approvals.index', [
            'requests' => $requests,
            'tab' => $tab,
            'pendingCount' => $this->approvals->pendingCount($user->company_id),
        ]);
    }

    public function show(ApprovalRequest $request): View
    {
        $this->authorize('viewAny', ApprovalRequest::class);

        // IDOR protection: ensure approval request belongs to current user's company
        if ((int) $request->company_id !== (int) auth()->user()->company_id && ! auth()->user()->hasRole('super_admin')) {
            abort(404);
        }

        $request->load('requester', 'finalApprover', 'actions.approver');

        return view('admin.approvals.show', ['request' => $request]);
    }

    public function approve(ApprovalRequest $request): RedirectResponse
    {
        $this->authorize('approve', $request);

        try {
            $this->approvals->approve($request, auth()->user());
            AuditLogger::log('approval_granted', 'approvals', $request, null, ['action' => 'approve']);

            return back()->with('success', 'Request approved.');
        } catch (\RuntimeException $e) {
            Log::warning('Approval action failed', ['id' => $request->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Could not approve this request. It may have already been processed.');
        }
    }

    public function reject(Request $request, ApprovalRequest $approval): RedirectResponse
    {
        $this->authorize('approve', $approval);

        try {
            $this->approvals->reject($approval, auth()->user(), $request->input('comment'));
            AuditLogger::log('approval_rejected', 'approvals', $approval, null, ['action' => 'reject']);

            return back()->with('success', 'Request rejected.');
        } catch (\RuntimeException $e) {
            Log::warning('Rejection action failed', ['id' => $approval->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Could not reject this request. It may have already been processed.');
        }
    }

    public function cancel(ApprovalRequest $request): RedirectResponse
    {
        $this->authorize('viewAny', ApprovalRequest::class);

        // Company check: must own the request
        if ((int) $request->company_id !== (int) auth()->user()->company_id && ! auth()->user()->hasRole('super_admin')) {
            abort(404);
        }

        if ((int) $request->requester_id !== (int) auth()->id()) {
            abort(403);
        }

        if ($request->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be cancelled.');
        }

        $request->forceFill(['status' => 'cancelled'])->save();
        AuditLogger::log('approval_cancelled', 'approvals', $request);

        return back()->with('success', 'Request cancelled.');
    }
}
