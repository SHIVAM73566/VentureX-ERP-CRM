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

            // Provide local knowledge base fallback
            $localAnswer = $this->getLocalHelp($question);
            $run->update(['status' => 'completed', 'provider' => 'local', 'model' => 'local']);

            return response()->json([
                'content' => $localAnswer,
                'provider' => 'local',
                'model' => 'local_knowledge_base',
                'cached' => false,
                'run_id' => $run->id,
            ]);
        }
    }

    protected function getLocalHelp(string $question): string
    {
        $q = mb_strtolower($question);

        $help = [
            '/(dashboard|home|overview)/i' => "**Dashboard**\nThe Dashboard shows key business metrics: total customers, revenue, open invoices, pending orders, and recent activities. Navigate to Dashboard from the sidebar.",
            '/(customer|contact|add.*customer)/i' => "**Managing Customers**\n[STEP]\n1. Go to CRM > Customers\n2. Click 'Create Customer'\n3. Fill in the company name, email, phone, and address\n4. Save the customer\nYou can also add contacts, track activities, and manage opportunities for each customer.",
            '/(lead|prospect|pipeline)/i' => "**Leads & Pipeline**\n[STEP]\n1. Go to CRM > Leads to view all leads\n2. Click 'Create Lead' to add a new lead\n3. Use the Pipeline view to drag-and-drop leads between stages\n4. Set follow-up dates to track engagement\nLeads can be converted to customers when they're ready to buy.",
            '/(invoice|billing|payment.*due)/i' => "**Invoices & Payments**\n[STEP]\n1. Go to Sales > Invoices\n2. Click 'Create Invoice' to generate a new invoice\n3. Select the customer, add line items, set dates\n4. Send the invoice via email or mark as paid\nYou can also generate PDF invoices and track payment status.",
            '/(quotation|quote|proposal)/i' => "**Quotations**\n[STEP]\n1. Go to Sales > Quotations\n2. Click 'Create Quotation'\n3. Add products/services with pricing\n4. Convert to Invoice when accepted\nQuotations can be exported as PDF and sent to customers.",
            '/(product|inventory|stock|warehouse)/i' => "**Inventory Management**\n[STEP]\n1. Go to Inventory > Products to manage your catalog\n2. Go to Inventory > Warehouses for warehouse management\n3. Use Stock Movements to track inventory changes\n4. Set reorder levels for automatic alerts\nProducts support categories, SKU tracking, and multi-warehouse locations.",
            '/(supplier|purchase|procurement|rfq)/i' => "**Procurement**\n[STEP]\n1. Go to Procurement > Suppliers to manage suppliers\n2. Create Purchase Requisitions for items needed\n3. Generate RFQs (Request for Quotations) to compare supplier pricing\n4. Create Purchase Orders from approved requisitions\n5. Track delivery and receiving in the system",
            '/(report|analytics|export)/i' => "**Reports & Analytics**\n[STEP]\n1. Go to Reports to view business analytics\n2. Use Export Center (Admin > Export) to export data as CSV\n3. Import Center (Admin > Import) allows bulk data imports\n4. Finance Dashboard shows receivables, payables, and cash flow",
            '/(role|permission|user.*manage|admin)/i' => "**User & Role Management**\n[STEP]\n1. Go to Admin > Users to manage user accounts\n2. Go to Admin > Roles to create and assign roles\n3. Use the Security Center for advanced security settings\n4. Two-factor authentication can be enabled in Account Settings",
            '/(error|bug|problem|issue|not.*work)/i' => "**Troubleshooting**\nIf you're experiencing an issue:\n[STEP]\n1. Check the Error Center in the sidebar for logged errors\n2. Verify your database connection in the admin settings\n3. Clear the cache: run `php artisan config:clear && php artisan cache:clear`\n4. Check the storage/logs/laravel.log file for detailed error messages\n5. Contact support with the error details from the Error Center",
            '/(shipping|shipment|logistics|container)/i' => "**Logistics & Shipping**\n[STEP]\n1. Go to Logistics > Shipments to manage shipments\n2. Track containers with Container Management\n3. Calculate landed costs in Landed Cost Management\n4. Link shipments to purchase orders for full traceability",
            '/(finance|account|journal|ledger)/i' => "**Finance Module**\n[STEP]\n1. Go to Finance > Dashboard for financial overview\n2. Manage Chart of Accounts under Finance > Accounts\n3. Create Journal Entries for manual transactions\n4. Track Receivables and Payables in dedicated views\n5. All financial data supports multi-currency",
            '/(ticket|support.*ticket|help.*desk)/i' => "**Support Tickets**\n[STEP]\n1. Go to Support > Tickets to view all tickets\n2. Click 'Create Ticket' to submit a new support request\n3. Assign priority and category\n4. Track status and resolution in the ticket view",
        ];

        foreach ($help as $pattern => $response) {
            if (preg_match($pattern, $q)) {
                return $response;
            }
        }

        return "**VentureX ERP & CRM Help**\n\nI can help you with:\n"
            ."- **CRM**: Customers, contacts, leads, opportunities, pipeline\n"
            ."- **Sales**: Quotations, orders, invoices, payments\n"
            ."- **Inventory**: Products, warehouses, stock movements\n"
            ."- **Procurement**: Suppliers, purchase orders, RFQs\n"
            ."- **Finance**: Accounts, journal entries, receivables, payables\n"
            ."- **Logistics**: Shipments, containers, landed costs\n"
            ."- **Admin**: Users, roles, settings, security\n"
            ."- **Reports & Analytics**: Data export, import, dashboards\n\n"
            ."[STEP]\nPlease describe your question in more detail, or navigate to the relevant module from the sidebar.\n"
            ."[TIP] AI-powered responses require an API key. Set NVIDIA_API_KEY in .env and run: php artisan config:clear";
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
