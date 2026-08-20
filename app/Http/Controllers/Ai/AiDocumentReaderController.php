<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiRun;
use App\Models\Document;
use App\Services\Ai\AiException;
use App\Services\Ai\AiGateway;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AiDocumentReaderController extends Controller
{
    public function __construct(protected AiGateway $gateway) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AiRun::class);

        $runs = AiRun::ofCompany()
            ->whereIn('input_type', ['document_reader', 'complex_document_analysis'])
            ->latest()
            ->limit(10)
            ->get();

        return view('ai.document-reader', [
            'runs' => $runs,
            'documents' => Document::ofCompany()->latest()->limit(50)->get(),
        ]);
    }

    public function analyze(Request $request): RedirectResponse
    {
        ini_set('max_execution_time', '180');

        $this->authorize('create', AiRun::class);

        $data = $request->validate([
            'document_id' => ['required', 'exists:documents,id'],
            'focus' => ['nullable', 'string', 'max:255'],
            'deep' => ['nullable', 'boolean'],
        ]);

        $deep = ! empty($data['deep']);
        $document = Document::ofCompany()->findOrFail($data['document_id']);

        $text = is_array($document->extracted_data)
            ? json_encode($document->extracted_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            : (string) ($document->extracted_data ?? '');

        if (strlen($text) < 10) {
            return back()->with('error', 'This document has no extractable text content. Use OCR or upload a text source.');
        }

        $run = AiRun::create([
            'company_id' => CompanyContext::id(),
            'user_id' => auth()->id(),
            'skill_slug' => null,
            'provider' => config('ai.provider'),
            'model' => config('ai.model'),
            'status' => 'running',
            'input_type' => $deep ? 'complex_document_analysis' : 'document_reader',
            'input' => ['document_id' => $document->id, 'focus' => $data['focus'] ?? null, 'deep' => $deep],
            'started_at' => now(),
        ]);

        try {
            if ($deep) {
                $system = 'You are a senior contracts and technical document analyst for a metals trading ERP. '
                    .'Perform a DEEP document analysis for human review using only the provided text. '
                    .'Identify key obligations, payment terms, price clauses, delivery terms, quality/specification '
                    .'requirements, risks, inconsistencies, and open questions. '
                    .'Label facts [FACT], calculations [CALCULATION], assumptions [ASSUMPTION], and recommendations [RECOMMENDATION]. '
                    .'Never invent clauses or figures not present in the text. Never create, sign or approve any legally '
                    .'binding document — the analysis is advisory for a human reviewer.';

                $prompt = "Document: {$document->original_name}\nFocus: ".($data['focus'] ?? 'deep analysis')
                    ."\n\nDocument text:\n".substr($text, 0, 24000);

                $result = $this->gateway->chat($system, $prompt, [
                    'task' => 'complex_document_analysis',
                    'max_tokens' => 5000,
                    'context' => 'doc:deep:'.$document->id,
                ]);
            } else {
                $system = 'You are an expert document analyst for a metals trading and procurement ERP. '
                    .'Extract structured, factual information from the provided document text. '
                    .'Label facts [FACT], calculations [CALCULATION], and assumptions [ASSUMPTION]. '
                    .'Never invent data that is not present in the text.';

                $prompt = "Document: {$document->original_name}\nFocus: ".($data['focus'] ?? 'general extraction')
                    ."\n\nDocument text:\n".substr($text, 0, 12000);

                $result = $this->gateway->chat($system, $prompt, [
                    'task' => 'document_analysis',
                    'max_tokens' => 4000,
                    'context' => 'doc:'.$document->id,
                ]);
            }

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

            AuditLogger::log('ai_analysis', 'ai', $document, null, ['run_id' => $run->id, 'type' => 'document_reader']);

            return redirect()->route('ai.document-reader', ['run' => $run->id])
                ->with('success', 'Document analysis complete.');
        } catch (AiException $e) {
            $run->update([
                'status' => 'failed',
                'error' => ['message' => $e->getMessage()],
                'finished_at' => now(),
            ]);
            Log::warning('Document reader AI failed', ['run' => $run->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Document analysis failed. Please try again later.');
        }
    }
}
