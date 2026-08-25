<?php

namespace App\Http\Controllers\Api;

use App\Models\AiConversation;
use App\Models\AiMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiController extends ApiController
{
    public function conversations(Request $request): JsonResponse
    {
        $query = AiConversation::query()
            ->where('user_id', auth()->id())
            ->with('messages')
            ->latest();

        return $this->paginatedResponse($query->paginate($request->integer('per_page', 15)));
    }

    public function storeConversation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'skill_slug' => 'nullable|string|max:100',
            'context' => 'nullable|array',
        ]);

        $conversation = AiConversation::create([
            ...$validated,
            'company_id' => auth()->user()->company_id,
            'user_id' => auth()->id(),
        ]);

        return $this->successResponse($conversation, 'Conversation created', 201);
    }

    public function showConversation(AiConversation $conversation): JsonResponse
    {
        if ($conversation->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse($conversation->load('messages'));
    }

    public function sendMessage(Request $request, AiConversation $conversation): JsonResponse
    {
        if ($conversation->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:10000',
        ]);

        $message = AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $validated['content'],
        ]);

        $response = AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => 'This is a placeholder response. AI integration will be connected here.',
        ]);

        return $this->successResponse(['user' => $message, 'assistant' => $response], 'Message sent');
    }

    public function insights(Request $request): JsonResponse
    {
        $user = auth()->user();

        $data = [
            'total_customers' => \App\Models\Customer::where('company_id', $user->company_id)->count(),
            'total_leads' => \App\Models\Lead::where('company_id', $user->company_id)->count(),
            'total_invoices' => \App\Models\Invoice::where('company_id', $user->company_id)->count(),
            'total_products' => \App\Models\Product::where('company_id', $user->company_id)->count(),
            'open_tickets' => \App\Models\SupportTicket::where('company_id', $user->company_id)->where('status', 'open')->count(),
        ];

        return $this->successResponse($data, 'Insights retrieved');
    }
}
