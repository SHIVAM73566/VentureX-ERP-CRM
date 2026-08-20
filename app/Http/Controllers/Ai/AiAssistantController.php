<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\AiRun;
use App\Models\AiSkill;
use App\Services\Ai\AiException;
use App\Services\Ai\AiGateway;
use App\Services\Ai\AiLocalIntelligence;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AiAssistantController extends Controller
{
    public function __construct(
        protected AiGateway $gateway,
        protected AiLocalIntelligence $local,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AiRun::class);

        $conversation = null;
        if ($request->integer('conversation') && $request->user()->can('view', AiRun::class)) {
            $conversation = AiConversation::ofCompany()
                ->where('id', $request->integer('conversation'))
                ->where('user_id', Auth::id())
                ->with('messages')
                ->first();
        }

        return view('ai.assistant', [
            'conversation' => $conversation,
            'conversations' => AiConversation::ofCompany()->where('user_id', Auth::id())->latest('updated_at')->limit(10)->get(),
            'skills' => AiSkill::ofCompany()->where(function ($q) {
                $q->where('company_id', CompanyContext::id())->orWhereNull('company_id');
            })->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function send(Request $request)
    {
        ini_set('max_execution_time', '180');

        $this->authorize('create', AiRun::class);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:20000', 'min:1'],
            'conversation_id' => ['nullable', 'exists:ai_conversations,id'],
            'skill_slug' => ['nullable', 'exists:ai_skills,slug'],
            'type' => ['sometimes', 'string', 'max:32'],
        ]);

        $user = $request->user();

        $conversation = null;
        if (! empty($data['conversation_id'])) {
            $conversation = AiConversation::ofCompany()->findOrFail($data['conversation_id']);
            abort_unless($conversation->user_id === $user->id, 403);
        }

        if (! $conversation) {
            $conversation = AiConversation::create([
                'company_id' => CompanyContext::id(),
                'user_id' => $user->id,
                'title' => str($data['message'])->limit(60)->toString(),
                'skill_slug' => $data['skill_slug'] ?? null,
            ]);
        }

        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $data['message'],
        ]);

        $skill = null;
        if (! empty($data['skill_slug'])) {
            $skill = AiSkill::ofCompany()->where(function ($q) {
                $q->where('company_id', CompanyContext::id())->orWhereNull('company_id');
            })->where('slug', $data['skill_slug'])->first();
        }

        $run = AiRun::create([
            'company_id' => CompanyContext::id(),
            'user_id' => $user->id,
            'skill_slug' => $skill?->slug,
            'provider' => $skill?->provider ?? config('ai.provider'),
            'model' => $skill?->model ?? config('ai.model'),
            'status' => 'running',
            'input_type' => $data['type'] ?? 'chat',
            'input' => ['message' => $data['message']],
            'started_at' => now(),
        ]);

        try {
            $system = $this->systemPrompt($skill, $user);

            $history = $conversation->messages()
                ->orderBy('created_at')
                ->take(20)
                ->get()
                ->map(fn ($m) => ($m->role === 'user' ? 'User' : 'Assistant').': '.$m->content)
                ->implode("\n");

            $userPrompt = $history
                ? "Previous conversation:\n{$history}\n\nUser: {$data['message']}"
                : $this->contextualPrompt($data['message'], $user);

            $result = $this->gateway->chat(
                $system,
                $userPrompt,
                [
                    'task' => 'general_assistant',
                    'provider' => $skill?->provider,
                    'model' => $skill?->model,
                    'temperature' => $skill?->temperature !== null ? (float) $skill->temperature : null,
                    'max_tokens' => $skill?->max_tokens ?? null,
                    'context' => 'chat:'.($skill?->slug ?? 'general'),
                ]
            );

            $assistantMessage = AiMessage::create([
                'conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $result['content'],
                'metadata' => [
                    'model' => $result['model'],
                    'provider' => $result['provider'],
                    'cached' => $result['cached'],
                    'latency_ms' => $result['latency_ms'],
                    'tags' => $this->detectTags($result['content']),
                ],
            ]);

            $run->update([
                'status' => 'completed',
                'provider' => $result['provider'],
                'model' => $result['model'],
                'output' => ['content' => $result['content']],
                'prompt_tokens' => $result['prompt_tokens'],
                'completion_tokens' => $result['completion_tokens'],
                'cost' => $result['cost'],
                'finished_at' => now(),
            ]);

            $conversation->touch();

            AuditLogger::log('ai_analysis', 'ai', null, null, ['run_id' => $run->id, 'skill' => $skill?->slug]);

            return response()->json([
                'message' => $assistantMessage,
                'run' => $run,
            ]);
        } catch (AiException $e) {
            $run->update([
                'status' => 'failed',
                'error' => ['message' => $e->getMessage()],
                'finished_at' => now(),
            ]);

            $localAnswer = $this->local->answer($data['message']);
            if ($localAnswer !== null) {
                $assistantMessage = AiMessage::create([
                    'conversation_id' => $conversation->id,
                    'role' => 'assistant',
                    'content' => "AI analysis is not currently configured. Here is the answer from your ERP data:\n\n".$localAnswer,
                    'metadata' => ['mode' => 'local', 'tags' => ['fact']],
                ]);
                $conversation->touch();

                return response()->json([
                    'message' => $assistantMessage,
                    'run' => $run,
                ]);
            }

            Log::warning('AI assistant failed', ['run' => $run->id, 'error' => $e->getMessage()]);

            $errorMsg = str_contains($e->getMessage(), 'not configured')
                ? 'AI is not configured. Set RAPIDAPI_KEY in your .env file. Your ERP data is still accessible through the modules.'
                : 'AI assistant is temporarily unavailable. Please try again later.';

            return response()->json(['error' => $errorMsg], 502);
        }
    }

    protected function systemPrompt(?AiSkill $skill, $user): string
    {
        $base = "You are the VentureX ERP & CRM AI Assistant. You operate inside a business platform with strict data governance.\n\n"
            ."Current user: {$user->displayName()} (role: {$user->roles->pluck('name')->implode(', ')}).\n"
            .'You may only reference or retrieve records the current user is authorised to access. Never expose confidential finance, payroll, or other users\' private data to a user without permission.\n'
            ."Label responses:\n[FACT] retrieved from the system.\n[CALCULATION] computed result with working.\n[ASSUMPTION] not directly confirmed.\n[RECOMMENDATION] AI suggestion for human review.\n"
            ."Never invent company-specific suppliers, prices, HS codes, duties, specifications or ERP processes. Never auto-approve or auto-reject a supplier. Humans make final decisions.\n"
            .'If asked about data outside the user\'s permissions, explain the limitation.'."\n\n---\n\n";

        return $skill ? $base."Active skill: {$skill->name} (v{$skill->version}).\n\n{$skill->instructions}" : $base;
    }

    protected function contextualPrompt(string $message, $user): string
    {
        $company = CompanyContext::current();

        $context = "Company: {$company?->name}\n";

        return "Context:\n{$context}\n---\nUser message:\n{$message}";
    }

    protected function detectTags(string $content): array
    {
        $tags = [];
        foreach (['FACT', 'CALCULATION', 'ASSUMPTION', 'RECOMMENDATION'] as $tag) {
            if (str_contains($content, "[{$tag}]")) {
                $tags[] = strtolower($tag);
            }
        }

        return $tags;
    }
}
