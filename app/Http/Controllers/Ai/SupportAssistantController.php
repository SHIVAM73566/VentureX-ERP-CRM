<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiRun;
use App\Services\Ai\AiException;
use App\Services\Ai\AiGateway;
use App\Services\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SupportAssistantController extends Controller
{
    public function __construct(
        protected AiGateway $gateway,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', AiRun::class);

        return view('ai.support-assistant');
    }

    public function ask(Request $request): JsonResponse
    {
        ini_set('max_execution_time', '180');
        $this->authorize('create', AiRun::class);

        $data = $request->validate([
            'question' => ['required', 'string', 'max:5000'],
            'screenshot' => ['nullable', 'file', 'max:5120', 'image'],
        ]);

        $question = $data['question'];

        $screenshotPath = null;
        if ($request->hasFile('screenshot')) {
            $screenshotPath = $request->file('screenshot')->store('support-screenshots', 'public');
        }

        $user = $request->user();
        $company = CompanyContext::current();

        $run = AiRun::create([
            'company_id' => CompanyContext::id(),
            'user_id' => $user->id,
            'provider' => config('ai.provider'),
            'model' => config('ai.model'),
            'status' => 'running',
            'input_type' => 'support',
            'input' => ['question' => $question, 'screenshot' => $screenshotPath],
            'started_at' => now(),
        ]);

        try {
            $system = $this->buildSystemPrompt($user, $company);
            $userPrompt = $this->buildUserPrompt($question, $screenshotPath);

            $result = $this->gateway->chat(
                $system,
                $userPrompt,
                [
                    'task' => 'support_assistant',
                    'max_tokens' => 2048,
                    'context' => 'support:support-assistant',
                ]
            );

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

            return response()->json([
                'content' => $result['content'],
                'provider' => $result['provider'],
                'model' => $result['model'],
                'cached' => $result['cached'],
                'run_id' => $run->id,
            ]);
        } catch (AiException $e) {
            $run->update([
                'status' => 'failed',
                'error' => ['message' => $e->getMessage()],
                'finished_at' => now(),
            ]);

            Log::warning('Support assistant failed', ['run' => $run->id, 'error' => $e->getMessage()]);

            return response()->json(['error' => 'AI support assistant is temporarily unavailable. Please try again later.'], 502);
        }
    }

    protected function buildSystemPrompt($user, $company): string
    {
        $companyName = $company?->name ?? 'this company';

        return <<<'PROMPT'
You are the VentureX AI Support Assistant — a world-class, knowledgeable support engineer for the VentureX CRM & ERP platform. You provide exceptional, step-by-step guidance to help users accomplish tasks, troubleshoot errors, and get the most out of the system.

## Core Principles
- Be precise, actionable, and clear. Give step-by-step instructions whenever the user asks how to do something.
- Reference actual modules: Dashboard, CRM (Customers, Contacts, Leads, Opportunities, Pipeline, Activities), Sales (Quotations, Sales Orders, Invoices, Payments), Purchase & Procurement (Suppliers, Supplier Offers, Purchase Requisitions, Purchase Orders, RFQs), Inventory (Products, Warehouses, Stock), Logistics (Shipments, Containers, Landed Cost), Finance (Dashboard, Chart of Accounts, Journal Entries, Receivables, Payables), Documents (Document Manager, AI Document Reader), AI Center (AI Assistant, Business Copilot, AI Insights, AI Usage, AI Skills, Procurement AI), Administration (Companies, Users, Roles & Permissions, Settings, Security Center, Export/Import, Audit Logs, System Health).
- NEVER hallucinate features or modules that do not exist. If you are unsure about a feature, say so honestly and suggest the user contact the support team or check the documentation.
- If the user shares a screenshot, analyse the visual information and provide specific guidance based on what you see.
- When troubleshooting errors, ask clarifying questions and walk through diagnostic steps methodically.
- Always recommend the safest next action. Never suggest destructive operations without warnings.
- You may reference ERP data contextually (company: {$companyName}) but never fabricate data, prices, supplier names, HS codes, or other business specifics.
- Label your responses clearly:
  [STEP] for step-by-step instructions
  [TIP] for helpful tips and shortcuts
  [WARNING] for important cautionary notes
  [ANSWER] for direct answers to questions

## Response Style
- Start with a brief summary of what you will address.
- Use numbered steps for procedures.
- Include navigation paths (e.g., "Go to Sales > Invoices > click New Invoice").
- If a task requires admin permissions, mention that explicitly.
- End with a suggested next action or offer to help with follow-up questions.
PROMPT;
    }

    protected function buildUserPrompt(string $question, ?string $screenshotPath): string
    {
        $user = auth()->user();
        $company = CompanyContext::current();

        $prompt = "Company: {$company?->name}\n";
        $prompt .= "User: {$user->displayName()} (Roles: {$user->roles->pluck('name')->implode(', ')})\n";

        if ($screenshotPath) {
            $prompt .= "Screenshot uploaded: {$screenshotPath}\n";
            $prompt .= "(Note: Analyse the screenshot context and provide guidance based on the likely UI state.)\n";
        }

        $prompt .= "\n---\nUser question:\n{$question}";

        return $prompt;
    }
}
