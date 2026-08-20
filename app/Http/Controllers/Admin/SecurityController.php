<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityEvent;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use App\Services\SecurityEventService;
use App\Services\SecurityStateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function index(): View
    {
        $this->authorize('configure');

        $companyId = CompanyContext::id();

        $recentEvents = SecurityEvent::query()
            ->where('company_id', $companyId)
            ->with('user:id,name,email')
            ->latest()
            ->limit(20)
            ->get();

        $alerts = SecurityEvent::query()
            ->where('company_id', $companyId)
            ->whereIn('severity', ['high', 'critical'])
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        return view('admin.security.index', [
            'score' => SecurityStateService::score(),
            'alerts' => $alerts,
            'lockdown' => SecurityStateService::lockdownEnabled(),
            'recentEvents' => $recentEvents,
        ]);
    }

    public function events(Request $request): View
    {
        $this->authorize('configure');

        $companyId = CompanyContext::id();

        $events = SecurityEvent::query()
            ->where('company_id', $companyId)
            ->with('user:id,name,email')
            ->when($request->filled('severity'), fn ($q) => $q->where('severity', $request->string('severity')))
            ->when($request->filled('event'), fn ($q) => $q->where('event', $request->string('event')))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.security.events', ['events' => $events]);
    }

    public function blockIp(Request $request): RedirectResponse
    {
        $this->authorize('configure');

        $data = $request->validate([
            'ip' => ['required', 'ip'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $cacheKey = 'security:blocked_ip:'.$data['ip'];
        cache()->put($cacheKey, [
            'blocked_by' => auth()->id(),
            'reason' => $data['reason'] ?? 'Manual block',
            'blocked_at' => now()->toIso8601String(),
        ], now()->addDays(30));

        AuditLogger::log('ip_blocked', 'security', null, null, ['ip' => $data['ip'], 'reason' => $data['reason']]);
        SecurityEventService::record(
            'security',
            'ip_blocked',
            "IP {$data['ip']} blocked by ".auth()->user()->name,
            'high',
            null,
            null,
            ['ip' => $data['ip'], 'reason' => $data['reason']]
        );

        return back()->with('success', "IP {$data['ip']} has been blocked for 30 days.");
    }

    public function lockdown(Request $request): RedirectResponse
    {
        $this->authorize('configure');

        $active = (bool) $request->boolean('active');
        SecurityStateService::setLockdown($active);

        AuditLogger::log($active ? 'lockdown_activated' : 'lockdown_lifted', 'security');
        SecurityEventService::record(
            'system',
            $active ? 'lockdown_activated' : 'lockdown_lifted',
            $active ? 'Emergency lockdown activated' : 'Emergency lockdown lifted',
            $active ? 'high' : 'info',
        );

        return back()->with('success', $active ? 'Emergency lockdown is now ACTIVE.' : 'Emergency lockdown lifted.');
    }
}
