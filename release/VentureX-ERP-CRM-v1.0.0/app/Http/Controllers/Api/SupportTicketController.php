<?php

namespace App\Http\Controllers\Api;

use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportTicketController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = SupportTicket::ofCompany()
            ->with('assignee')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($inner) use ($search) {
                    $inner->where('ticket_number', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->string('priority')))
            ->latest();

        return $this->paginatedResponse($query->paginate($request->integer('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'module' => 'nullable|string|max:100',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
            'category' => 'nullable|string|in:bug,feature_request,question,installation,other',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $ticket = SupportTicket::create([
            ...$validated,
            'ticket_number' => SupportTicket::generateTicketNumber(),
            'company_id' => auth()->user()->company_id,
            'user_id' => auth()->id(),
        ]);

        return $this->successResponse($ticket->load('assignee'), 'Ticket created', 201);
    }

    public function show(SupportTicket $supportTicket): JsonResponse
    {
        if ($supportTicket->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        return $this->successResponse($supportTicket->load('assignee', 'replies', 'errorReport'));
    }

    public function update(Request $request, SupportTicket $supportTicket): JsonResponse
    {
        if ($supportTicket->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        $validated = $request->validate([
            'subject' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'status' => 'nullable|string|in:open,investigating,in_progress,resolved,closed',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
            'category' => 'nullable|string|in:bug,feature_request,question,installation,other',
            'assigned_to' => 'nullable|exists:users,id',
            'resolved_at' => 'nullable|date',
            'closed_at' => 'nullable|date',
            'customer_satisfaction' => 'nullable|integer|min:1|max:5',
        ]);

        $supportTicket->update($validated);

        return $this->successResponse($supportTicket->fresh()->load('assignee'), 'Ticket updated');
    }

    public function destroy(SupportTicket $supportTicket): JsonResponse
    {
        if ($supportTicket->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        $supportTicket->delete();

        return $this->successResponse(null, 'Ticket deleted');
    }
}
