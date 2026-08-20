<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\SupportNotification;
use App\Models\SupportReply;
use App\Models\SupportTicket;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupportReplyController extends Controller
{
    public function store(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('view', $ticket);

        if ((int) $ticket->company_id !== (int) CompanyContext::id()) {
            abort(403);
        }

        $isOwner = (int) $ticket->user_id === (int) auth()->id();
        $isAdmin = auth()->user()->hasRole('super_admin');

        if (! $isOwner && ! $isAdmin) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:10000'],
            'is_internal' => ['sometimes', 'boolean'],
        ]);

        $reply = SupportReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
            'is_internal' => $isAdmin && ($validated['is_internal'] ?? false),
        ]);

        $reply->load('user');

        AuditLogger::log('reply_created', 'support_tickets', $ticket);

        if (! $reply->is_internal) {
            $this->notifyParticipants($ticket);
        }

        return back()->with('success', $reply->is_internal ? 'Internal note added.' : 'Reply submitted.');
    }

    protected function notifyParticipants(SupportTicket $ticket): void
    {
        if ($ticket->user_id !== auth()->id()) {
            SupportNotification::create([
                'user_id' => $ticket->user_id,
                'type' => 'ticket_replied',
                'notifiable_type' => SupportTicket::class,
                'notifiable_id' => $ticket->id,
                'title' => 'New Reply on Ticket',
                'message' => "New reply on ticket {$ticket->ticket_number}.",
                'data' => ['ticket_number' => $ticket->ticket_number],
            ]);
        }

        if ($ticket->assigned_to && $ticket->assigned_to !== auth()->id()) {
            SupportNotification::create([
                'user_id' => $ticket->assigned_to,
                'type' => 'ticket_replied',
                'notifiable_type' => SupportTicket::class,
                'notifiable_id' => $ticket->id,
                'title' => 'New Reply on Assigned Ticket',
                'message' => "New reply on ticket {$ticket->ticket_number} assigned to you.",
                'data' => ['ticket_number' => $ticket->ticket_number],
            ]);
        }
    }
}
