<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiRun;
use App\Services\Ai\AiQuotaService;
use App\Services\Ai\AiUsageMonitor;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiQuotaController extends Controller
{
    public function __construct(
        protected AiQuotaService $quota,
        protected AiUsageMonitor $usage,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', AiRun::class);

        $quotas = $this->quota->getAllQuotas();
        $stats = $this->quota->check();
        $usageStats = $this->usage->stats(7);

        return view('admin.ai-quotas.index', compact('quotas', 'stats', 'usageStats'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermissionTo('settings.configure'), 403);

        $data = $request->validate([
            'quotas' => ['required', 'array'],
            'quotas.*.daily' => ['required', 'integer', 'min:0', 'max:9999'],
            'quotas.*.weekly' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);

        $overrides = [];
        $defaults = config('ai.quota.defaults', []);

        foreach ($data['quotas'] as $role => $limits) {
            if (! isset($defaults[$role])) {
                continue;
            }
            if ((int) $limits['daily'] !== (int) $defaults[$role]['daily']
                || (int) $limits['weekly'] !== (int) $defaults[$role]['weekly']) {
                $overrides[$role] = [
                    'daily' => (int) $limits['daily'],
                    'weekly' => (int) $limits['weekly'],
                ];
            }
        }

        $this->quota->saveOverrides($overrides);
        AuditLogger::log('ai_quotas_updated', 'ai', null, null, ['overrides' => $overrides]);

        return back()->with('success', 'AI quota settings updated.');
    }
}
