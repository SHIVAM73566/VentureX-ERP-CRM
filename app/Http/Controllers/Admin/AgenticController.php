<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentAction;
use App\Models\AgentRun;
use App\Services\Ai\AgenticEngine;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgenticController extends Controller
{
    public function __construct(protected AgenticEngine $engine) {}

    public function index(): View
    {
        $this->authorize('viewAny', AgentAction::class);

        $companyId = CompanyContext::id();
        $this->engine->seedDefaults($companyId);

        $actions = AgentAction::ofCompany($companyId)
            ->withCount('runs')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $runs = AgentRun::ofCompany($companyId)
            ->with('action')
            ->latest()
            ->limit(25)
            ->get();

        $summary = [
            'rules' => $actions->count(),
            'enabled' => $actions->where('is_enabled', true)->count(),
            'runs_today' => AgentRun::ofCompany($companyId)->whereDate('created_at', now()->toDateString())->count(),
            'rollbacks' => AgentRun::ofCompany($companyId)->where('status', 'rolled_back')->count(),
            'success_rate' => round((float) $actions->avg('confidence') * 100, 1),
        ];

        return view('admin.agentic.index', compact('actions', 'runs', 'summary'));
    }

    public function runAll(Request $request): RedirectResponse
    {
        $this->authorize('create', AgentAction::class);

        $dryRun = $request->boolean('dry_run');
        $runs = $this->engine->runAll(CompanyContext::id(), $dryRun);

        $succeeded = collect($runs)->where('status', 'success')->count();
        $failed = collect($runs)->where('status', 'rolled_back')->count();
        $skipped = collect($runs)->where('status', 'skipped')->count();

        AuditLogger::log($dryRun ? 'agent.dry_run_all' : 'agent.run_all', 'automation', null, null, [
            'rules' => count($runs),
            'succeeded' => $succeeded,
            'failed' => $failed,
            'skipped' => $skipped,
        ]);

        $message = sprintf(
            '%s %d rule(s): %d succeeded, %d skipped, %d rolled back.',
            $dryRun ? 'Dry run completed for' : 'Agent run completed for',
            count($runs),
            $succeeded,
            $skipped,
            $failed
        );

        return back()->with($failed > 0 ? 'error' : 'success', $message);
    }

    public function runOne(Request $request, AgentAction $action): RedirectResponse
    {
        $this->authorize('create', AgentAction::class);
        $this->ensureCompany($action);

        $run = $this->engine->run($action, $request->boolean('dry_run'));

        $tone = match ($run->status) {
            'success' => 'success',
            'skipped' => 'error',
            default => 'error',
        };

        return back()->with($tone, $run->summary ?? 'Agent run finished with status: '.$run->status);
    }

    public function update(Request $request, AgentAction $action): RedirectResponse
    {
        $this->authorize('update', $action);
        $this->ensureCompany($action);

        $data = $request->validate([
            'is_enabled' => ['boolean'],
            'requires_approval' => ['boolean'],
            'cooldown_minutes' => ['nullable', 'integer', 'min:5', 'max:10080'],
            'confidence' => ['nullable', 'numeric', 'min:0', 'max:1'],
        ]);

        $action->update([
            'is_enabled' => $request->boolean('is_enabled'),
            'requires_approval' => $request->boolean('requires_approval'),
            'cooldown_minutes' => $data['cooldown_minutes'] ?? $action->cooldown_minutes,
            'confidence' => $data['confidence'] ?? $action->confidence,
        ]);

        AuditLogger::updated($action);

        return back()->with('success', 'Agent rule updated.');
    }

    protected function ensureCompany(AgentAction $action): void
    {
        if ((int) $action->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }
    }
}
