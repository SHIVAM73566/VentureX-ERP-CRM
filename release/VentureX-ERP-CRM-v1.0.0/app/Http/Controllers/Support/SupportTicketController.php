<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\SupportNotification;
use App\Models\SupportTicket;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', SupportTicket::class);

        $query = SupportTicket::ofCompany()
            ->where('user_id', auth()->id())
            ->with('assignee');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->string('priority'));
        }

        if ($request->filled('module')) {
            $query->where('module', $request->string('module'));
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();

        return view('support.tickets.index', compact('tickets'));
    }

    public function create(): View
    {
        $this->authorize('create', SupportTicket::class);

        return view('support.tickets.create', [
            'priorities' => SupportTicket::PRIORITIES,
            'categories' => SupportTicket::CATEGORIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', SupportTicket::class);

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'module' => ['nullable', 'string', 'max:128'],
            'category' => ['required', Rule::in(array_keys(SupportTicket::CATEGORIES))],
            'priority' => ['required', Rule::in(array_keys(SupportTicket::PRIORITIES))],
        ]);

        $validated['company_id'] = CompanyContext::id();
        $validated['user_id'] = auth()->id();
        $validated['ticket_number'] = SupportTicket::generateTicketNumber();
        $validated['status'] = 'open';

        $ticket = SupportTicket::create($validated);

        SupportNotification::create([
            'user_id' => auth()->id(),
            'type' => 'ticket_created',
            'notifiable_type' => SupportTicket::class,
            'notifiable_id' => $ticket->id,
            'title' => 'Ticket Created',
            'message' => "Ticket {$ticket->ticket_number} has been created.",
            'data' => ['ticket_number' => $ticket->ticket_number],
        ]);

        AuditLogger::created($ticket);

        return redirect()->route('support.tickets.show', $ticket)
            ->with('success', 'Support ticket created successfully.');
    }

    public function show(SupportTicket $ticket): View
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

        $ticket->load(['replies.user', 'assignee']);

        $replies = $ticket->replies()->where('is_internal', false)->get();

        return view('support.tickets.show', compact('ticket', 'replies', 'isOwner', 'isAdmin'));
    }

    public function update(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        if ((int) $ticket->company_id !== (int) CompanyContext::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['sometimes', Rule::in(array_keys(SupportTicket::STATUSES))],
            'priority' => ['sometimes', Rule::in(array_keys(SupportTicket::PRIORITIES))],
        ]);

        $oldValues = $ticket->only(array_keys($validated));

        $ticket->update($validated);

        AuditLogger::updated($ticket, $oldValues);

        return back()->with('success', 'Ticket updated.');
    }
}
