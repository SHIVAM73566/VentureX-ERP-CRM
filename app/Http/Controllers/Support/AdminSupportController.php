<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\SupportNotification;
use App\Models\SupportReply;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminSupportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', SupportTicket::class);

        $query = SupportTicket::ofCompany()->with(['user', 'assignee']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->string('priority'));
        }

        $tickets = $query->latest()->paginate(20)->withQueryString();

        $companyId = auth()->user()->company_id;
        $stats = [
            'total' => SupportTicket::where('company_id', $companyId)->count(),
            'open' => SupportTicket::where('company_id', $companyId)->where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('company_id', $companyId)->where('status', 'in_progress')->count(),
            'investigating' => SupportTicket::where('company_id', $companyId)->where('status', 'investigating')->count(),
            'resolved' => SupportTicket::where('company_id', $companyId)->where('status', 'resolved')->count(),
            'closed' => SupportTicket::where('company_id', $companyId)->where('status', 'closed')->count(),
        ];

        return view('admin.support.index', compact('tickets', 'stats'));
    }

    public function show(SupportTicket $ticket): View
    {
        // SECURITY: Verify ticket belongs to user's company
        if ($ticket->company_id !== auth()->user()->company_id && ! auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized access to support ticket.');
        }

        $ticket->load(['user', 'assignee', 'replies.user']);

        $admins = User::role('super_admin')->get();

        return view('admin.support.show', compact('ticket', 'admins'));
    }

    public function assign(Request $request, SupportTicket $ticket): RedirectResponse
    {
        // SECURITY: Verify ticket belongs to user's company
        if ($ticket->company_id !== auth()->user()->company_id && ! auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized access to support ticket.');
        }

        $validated = $request->validate([
            'assigned_to' => ['required', 'exists:users,id', function ($attribute, $value, $fail) {
                $user = User::find($value);
                if (! $user || $user->company_id !== auth()->user()->company_id) {
                    $fail('The selected user is not in your company.');
                }
            }],
        ]);

        $oldAssignee = $ticket->assigned_to;

        $ticket->update($validated);

        AuditLogger::action($ticket, 'ticket_assigned', 'support_tickets');

        if ($ticket->assigned_to && $ticket->assigned_to !== auth()->id()) {
            SupportNotification::create([
                'user_id' => $ticket->assigned_to,
                'type' => 'ticket_created',
                'notifiable_type' => SupportTicket::class,
                'notifiable_id' => $ticket->id,
                'title' => 'Ticket Assigned',
                'message' => "Ticket {$ticket->ticket_number} has been assigned to you.",
                'data' => ['ticket_number' => $ticket->ticket_number],
            ]);
        }

        return back()->with('success', 'Ticket assigned.');
    }

    public function updateStatus(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(SupportTicket::STATUSES))],
        ]);

        $oldStatus = $ticket->status;

        $ticket->update($validated);

        if ($validated['status'] === 'resolved') {
            $ticket->update(['resolved_at' => now()]);
        } elseif ($validated['status'] === 'closed') {
            $ticket->update(['closed_at' => now()]);
        }

        AuditLogger::action($ticket, 'status_changed', 'support_tickets');

        if ((int) $ticket->user_id !== (int) auth()->id()) {
            SupportNotification::create([
                'user_id' => $ticket->user_id,
                'type' => 'ticket_resolved',
                'notifiable_type' => SupportTicket::class,
                'notifiable_id' => $ticket->id,
                'title' => 'Ticket Status Updated',
                'message' => "Ticket {$ticket->ticket_number} status changed to ".SupportTicket::STATUSES[$validated['status']].'.',
                'data' => ['ticket_number' => $ticket->ticket_number, 'old_status' => $oldStatus, 'new_status' => $validated['status']],
            ]);
        }

        return back()->with('success', 'Status updated.');
    }

    public function addNote(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:10000'],
        ]);

        SupportReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $validated['note'],
            'is_internal' => true,
        ]);

        AuditLogger::log('internal_note_added', 'support_tickets', $ticket);

        return back()->with('success', 'Internal note added.');
    }

    public function update(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(SupportTicket::STATUSES))],
            'assignee_id' => ['nullable', 'exists:users,id'],
        ]);

        $oldValues = $ticket->only(['status', 'assignee_id']);

        $ticket->update($validated);

        if ($validated['status'] === 'resolved') {
            $ticket->update(['resolved_at' => now()]);
        } elseif ($validated['status'] === 'closed') {
            $ticket->update(['closed_at' => now()]);
        }

        AuditLogger::updated($ticket, $oldValues);

        return back()->with('success', 'Ticket updated.');
    }
}
