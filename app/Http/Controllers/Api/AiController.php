<?php

namespace App\Http\Controllers\Api;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\Ai\AiException;
use App\Services\Ai\AiGateway;
use App\Services\Ai\AiLocalIntelligence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiController extends ApiController
{
    public function __construct(
        protected AiGateway $gateway,
        protected AiLocalIntelligence $local,
    ) {}
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

        $localAnswer = $this->local->answer($validated['content'], auth()->user()->company_id);
        if ($localAnswer !== null) {
            $response = AiMessage::create([
                'conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $localAnswer,
                'metadata' => ['mode' => 'local', 'tags' => ['fact']],
            ]);
            $conversation->touch();

            return $this->successResponse(['user' => $message, 'assistant' => $response], 'Message sent');
        }

        $history = $conversation->messages()
            ->orderBy('created_at')
            ->take(20)
            ->get()
            ->map(fn ($m) => ($m->role === 'user' ? 'User' : 'Assistant').': '.$m->content)
            ->implode("\n");

        $companyName = auth()->user()?->company?->name ?? 'N/A';

        $userPrompt = $history
            ? "Previous conversation:\n{$history}\n\nUser: {$validated['content']}"
            : "Company: {$companyName}\n---\nUser message:\n{$validated['content']}";

        try {
            $result = $this->gateway->chat(
                $this->systemPrompt(),
                $userPrompt,
                ['task' => 'general_assistant', 'context' => 'api:chat:'.($conversation->skill_slug ?? 'general')],
            );

            $response = AiMessage::create([
                'conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $result['content'],
                'metadata' => [
                    'model' => $result['model'],
                    'provider' => $result['provider'],
                    'cached' => $result['cached'],
                    'latency_ms' => $result['latency_ms'],
                ],
            ]);
            $conversation->touch();

            return $this->successResponse(['user' => $message, 'assistant' => $response], 'Message sent');
        } catch (AiException $e) {
            $fallbackContent = "AI analysis is unavailable right now. Your ERP system is running normally.\n\n"
                .'To enable AI features, add an AI provider API key to the environment. See documentation/AI-SETUP.md.';

            $response = AiMessage::create([
                'conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $fallbackContent,
                'metadata' => ['mode' => 'local_fallback', 'tags' => ['fact']],
            ]);
            $conversation->touch();

            return $this->successResponse(['user' => $message, 'assistant' => $response], 'Message sent');
        }
    }

    protected function systemPrompt(): string
    {
        $user = auth()->user();
        $roles = $user?->roles->pluck('name')->implode(', ') ?? '';

        return "You are the VentureX ERP & CRM AI Assistant. You operate inside a business platform with strict data governance.\n\n"
            ."Current user: {$user?->name} (role: {$roles}).\n"
            .'You may only reference or retrieve records the current user is authorised to access. Never expose confidential finance, payroll, or other users\' private data.\n'
            .'Label responses: [FACT] retrieved from the system, [CALCULATION] computed result, [ASSUMPTION] not directly confirmed, [RECOMMENDATION] AI suggestion for human review.\n'
            .'Never invent company-specific suppliers, prices, or ERP processes. Never auto-approve or auto-reject a decision. Humans make final decisions.';
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
