<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiRun;
use App\Models\PurchaseRequisition;
use App\Models\Supplier;
use App\Models\SupplierOffer;
use App\Services\Ai\AiException;
use App\Services\Ai\AiGateway;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ProcurementAiController extends Controller
{
    public function __construct(protected AiGateway $gateway) {}

    public function index(): View
    {
        $this->authorize('viewAny', AiRun::class);

        $runs = AiRun::ofCompany()
            ->where('input_type', 'procurement_ai')
            ->latest()
            ->limit(10)
            ->get();

        $summary = [
            'offers' => SupplierOffer::ofCompany()->count(),
            'open_requisitions' => PurchaseRequisition::ofCompany()->whereIn('status', ['draft', 'pending_approval'])->count(),
            'suppliers' => Supplier::ofCompany()->count(),
            'red_offers' => SupplierOffer::ofCompany()->where('quality_status', 'RED')->count(),
        ];

        return view('ai.procurement', compact('runs', 'summary'));
    }

    public function analyze(Request $request): RedirectResponse
    {
        ini_set('max_execution_time', '180');

        $this->authorize('create', AiRun::class);

        $data = $request->validate([
            'question' => ['required', 'string', 'max:5000'],
        ]);

        $run = AiRun::create([
            'company_id' => CompanyContext::id(),
            'user_id' => auth()->id(),
            'skill_slug' => null,
            'provider' => config('ai.provider'),
            'model' => config('ai.model'),
            'status' => 'running',
            'input_type' => 'procurement_ai',
            'input' => ['question' => $data['question']],
            'started_at' => now(),
        ]);

        try {
            $system = 'You are a procurement intelligence analyst for a metals trading company. '
                .'You assist buyers with offer comparisons, price analysis, supplier risk assessment, and sourcing decisions. '
                .'Use only the data provided in the context. '
                .'Label facts [FACT], calculations [CALCULATION], assumptions [ASSUMPTION], and suggestions [RECOMMENDATION]. '
                .'Never auto-approve or auto-reject a supplier. Always recommend human review for final decisions.';

            $context = $this->buildContext();

            $result = $this->gateway->chat($system, $context."\n\nBuyer question:\n".$data['question'], [
                'task' => 'procurement_analysis',
                'max_tokens' => 4000,
                'context' => 'procurement',
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

            AuditLogger::log('ai_analysis', 'ai', null, null, ['run_id' => $run->id, 'type' => 'procurement_ai']);

            return redirect()->route('ai.procurement', ['run' => $run->id])
                ->with('success', 'Procurement analysis complete.');
        } catch (AiException $e) {
            $run->update([
                'status' => 'failed',
                'error' => ['message' => $e->getMessage()],
                'finished_at' => now(),
            ]);
            Log::warning('Procurement AI failed', ['run' => $run->id, 'error' => $e->getMessage()]);

            // Local fallback: show the procurement context as facts
            $localAnalysis = "**Procurement Analysis (Local Mode)**\n\n"
                .$context."\n\n"
                ."---\n"
                ."[NOTE] AI-powered analysis requires an AI provider API key. "
                ."Add one to your .env file and run: `php artisan config:clear`\n"
                ."The data above shows your current procurement status for manual review.";

            $run->update([
                'status' => 'completed',
                'provider' => 'local',
                'model' => 'local',
                'output' => ['content' => $localAnalysis],
                'finished_at' => now(),
            ]);

            return redirect()->route('ai.procurement', ['run' => $run->id])
                ->with('info', 'AI analysis unavailable. Showing local procurement data.');
        }
    }

    protected function buildContext(): string
    {
        $company = CompanyContext::current();

        $lines = ["Company: {$company?->name}\n"];

        $offers = SupplierOffer::ofCompany()
            ->with('supplier')
            ->latest()
            ->limit(25)
            ->get()
            ->map(fn ($o) => sprintf(
                '%s | supplier=%s | cat=%s | grade=%s | qty_mt=%.2f | price_mt=%.2f %s | status=%s | risk=%s',
                $o->id,
                $o->supplier?->name ?? 'unlinked',
                $o->material_category,
                $o->grade ?? 'n/a',
                (float) $o->quantity_mt,
                (float) $o->price_per_mt,
                $o->currency_code ?? 'USD',
                $o->quality_status,
                $o->risk_level ?? 'n/a',
            ))
            ->implode("\n");

        $lines[] = "Recent supplier offers:\n{$offers}";

        $open = PurchaseRequisition::ofCompany()
            ->with('items')
            ->whereIn('status', ['draft', 'pending_approval', 'approved'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($r) => sprintf(
                '%s | dept=%s | priority=%s | status=%s | items=%d',
                $r->pr_number ?? ('#'.$r->id),
                $r->department?->name ?? 'n/a',
                $r->priority,
                $r->status,
                $r->items->count(),
            ))
            ->implode("\n");

        $lines[] = "Open purchase requisitions:\n{$open}";

        return implode("\n---\n", $lines);
    }
}
