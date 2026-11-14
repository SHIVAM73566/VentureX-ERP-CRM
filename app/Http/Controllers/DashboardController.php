<?php

namespace App\Http\Controllers;

use App\Models\AgentAction;
use App\Models\AgentRun;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\LeaveRequest;
use App\Models\OnboardingTask;
use App\Models\Opportunity;
use App\Models\PayrollRun;
use App\Models\PurchaseOrder;
use App\Models\SupplierOffer;
use App\Services\Ai\AiInsightService;
use App\Services\Ai\AiQuotaService;
use App\Services\CompanyContext;
use App\Services\SettingService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Customer::class);

        $companyId = CompanyContext::id();
        $user = auth()->user();
        $roleNames = $user->getRoleNames()->toArray();

        $stats = [
            'customers' => Customer::ofCompany()->count(),
            'leads' => Lead::ofCompany()->count(),
            'leads_new' => Lead::ofCompany()->where('status', 'new')->count(),
            'open_opportunities' => Opportunity::ofCompany()->whereNotIn('stage', ['won', 'lost'])->count(),
            'pipeline_value' => (float) Opportunity::ofCompany()
                ->whereNotIn('stage', ['won', 'lost'])
                ->sum('expected_value'),
            'won_value' => (float) Opportunity::ofCompany()->where('stage', 'won')->sum('expected_value'),
        ];

        $leadsByStatus = Lead::ofCompany()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $opportunitiesByStage = Opportunity::ofCompany()
            ->select('stage', DB::raw('count(*) as total'))
            ->groupBy('stage')
            ->pluck('total', 'stage')
            ->toArray();

        $offers = [
            'total' => SupplierOffer::ofCompany()->count(),
            'green' => SupplierOffer::ofCompany()->where('quality_status', 'GREEN')->count(),
            'yellow' => SupplierOffer::ofCompany()->where('quality_status', 'YELLOW')->count(),
            'red' => SupplierOffer::ofCompany()->where('quality_status', 'RED')->count(),
        ];

        $recentCustomers = Customer::ofCompany()->latest()->limit(5)->get();
        $recentLeads = Lead::ofCompany()->with('assignedTo')->latest()->limit(5)->get();
        $recentOffers = SupplierOffer::ofCompany()->with('supplier')->latest()->limit(5)->get();

        $followUps = Lead::ofCompany()
            ->whereNotNull('next_follow_up')
            ->where('status', '!=', 'won')
            ->where('next_follow_up', '<=', now()->addDays(7))
            ->orderBy('next_follow_up')
            ->limit(8)
            ->get();

        $allModules = [
            ['name' => 'CRM', 'slug' => 'crm', 'ready' => true, 'icon' => 'users', 'route' => 'customers.index', 'permission' => 'customers.view'],
            ['name' => 'Sales', 'slug' => 'sales', 'ready' => true, 'icon' => 'chart', 'route' => 'sales.quotations.index', 'permission' => 'sales.view'],
            ['name' => 'Purchase', 'slug' => 'purchase', 'ready' => true, 'icon' => 'cart', 'route' => 'suppliers.index', 'permission' => 'suppliers.view'],
            ['name' => 'Procurement', 'slug' => 'procurement', 'ready' => true, 'icon' => 'scan', 'route' => 'supplier-offers.index', 'permission' => 'supplier_offers.view'],
            ['name' => 'Inventory', 'slug' => 'inventory', 'ready' => true, 'icon' => 'box', 'route' => 'inventory.products.index', 'permission' => 'inventory.view'],
            ['name' => 'Finance', 'slug' => 'finance', 'ready' => true, 'icon' => 'wallet', 'route' => 'finance.dashboard', 'permission' => 'finance.view'],
            ['name' => 'Logistics', 'slug' => 'logistics', 'ready' => true, 'icon' => 'ship', 'route' => 'logistics.shipments.index', 'permission' => 'logistics.view'],
            ['name' => 'HR', 'slug' => 'hr', 'ready' => true, 'icon' => 'badge', 'route' => 'hr.dashboard', 'permission' => 'hr.view'],
            ['name' => 'Projects', 'slug' => 'projects', 'ready' => false, 'icon' => 'briefcase', 'route' => null, 'permission' => 'projects.view'],
            ['name' => 'Documents', 'slug' => 'documents', 'ready' => true, 'icon' => 'folder', 'route' => 'admin.documents.index', 'permission' => 'documents.view'],
            ['name' => 'AI Center', 'slug' => 'ai', 'ready' => true, 'icon' => 'spark', 'route' => 'ai.copilot', 'permission' => 'ai.chat'],
            ['name' => 'Automation', 'slug' => 'automation', 'ready' => true, 'icon' => 'bolt', 'route' => 'admin.agentic.index', 'permission' => null],
        ];

        $modules = array_filter($allModules, function ($m) use ($user) {
            if (! $m['permission']) {
                return true;
            }
            try {
                return $user->hasPermissionTo($m['permission']) || $user->hasRole('super_admin');
            } catch (\Throwable) {
                return $user->hasRole('super_admin');
            }
        });

        $insightService = app(AiInsightService::class);
        $ruleInsights = $insightService->rules($companyId);
        $aiInsights = $insightService->cachedAi($companyId);
        $aiEnabled = $insightService->gatewayEnabled();

        $quotaService = app(AiQuotaService::class);
        $aiQuota = $quotaService->check();

        $primaryRole = $user->getRoleNames()->first();
        $isExecutive = in_array($primaryRole, ['super_admin', 'company_admin', 'ceo', 'cfo', 'coo']);

        $quickActions = $this->buildQuickActions($user, $roleNames);

        $cashFlow = $this->cashFlowForecast();
        $hrSummary = $this->hrSummary();
        $agenticSummary = $this->agenticSummary();
        $briefing = $this->buildBriefing($stats, $cashFlow, $hrSummary, $agenticSummary);

        return view('dashboard.index', compact(
            'stats', 'leadsByStatus', 'opportunitiesByStage', 'offers',
            'recentCustomers', 'recentLeads', 'recentOffers', 'followUps', 'modules',
            'ruleInsights', 'aiInsights', 'aiEnabled', 'aiQuota', 'isExecutive',
            'quickActions', 'roleNames', 'cashFlow', 'hrSummary', 'agenticSummary', 'briefing'
        ));
    }

    /**
     * Six-month cash-flow forecast built from outstanding receivables and
     * open purchase-order commitments. Safe when finance tables are empty.
     *
     * @return array{months: array<int, array<string, mixed>>, overdue_total: float, overdue_count: int}
     */
    protected function cashFlowForecast(): array
    {
        $months = [];
        $start = now()->startOfMonth();

        try {
            for ($i = 0; $i < 6; $i++) {
                $month = $start->copy()->addMonthsNoOverflow($i);

                $inflow = (float) Invoice::ofCompany()
                    ->whereIn('status', ['sent', 'partial', 'overdue'])
                    ->whereYear('due_date', $month->year)
                    ->whereMonth('due_date', $month->month)
                    ->selectRaw('COALESCE(SUM(total - paid_amount), 0) as balance')
                    ->value('balance');

                $outflow = (float) PurchaseOrder::ofCompany()
                    ->whereIn('status', ['approved', 'ordered', 'partially_received'])
                    ->whereYear('expected_date', $month->year)
                    ->whereMonth('expected_date', $month->month)
                    ->selectRaw('COALESCE(SUM(total - paid_amount), 0) as balance')
                    ->value('balance');

                $months[] = [
                    'label' => $month->format('M'),
                    'full' => $month->format('M Y'),
                    'inflow' => round(max(0, $inflow), 2),
                    'outflow' => round(max(0, $outflow), 2),
                    'net' => round($inflow - $outflow, 2),
                ];
            }

            $overdueQuery = Invoice::ofCompany()->where('status', 'overdue');
            $overdueCount = (clone $overdueQuery)->count();
            $overdueTotal = (float) $overdueQuery
                ->selectRaw('COALESCE(SUM(total - paid_amount), 0) as balance')
                ->value('balance');
        } catch (\Throwable) {
            $months = [];
            $overdueCount = 0;
            $overdueTotal = 0.0;
        }

        return [
            'months' => $months,
            'overdue_total' => round(max(0, $overdueTotal), 2),
            'overdue_count' => $overdueCount,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function hrSummary(): array
    {
        try {
            $latestPayroll = PayrollRun::ofCompany()->latest('period_end')->first();

            return [
                'headcount' => Employee::ofCompany()->whereIn('status', ['active', 'probation'])->count(),
                'on_leave' => Employee::ofCompany()->where('status', 'on_leave')->count(),
                'pending_leave' => LeaveRequest::ofCompany()->where('status', 'pending')->count(),
                'onboarding_open' => OnboardingTask::ofCompany()->where('status', '!=', 'completed')->count(),
                'latest_payroll_net' => $latestPayroll ? (float) $latestPayroll->net_total : 0.0,
                'latest_payroll_status' => $latestPayroll?->status,
                'latest_payroll_period' => $latestPayroll?->period_end?->format('M Y'),
            ];
        } catch (\Throwable) {
            return [
                'headcount' => 0,
                'on_leave' => 0,
                'pending_leave' => 0,
                'onboarding_open' => 0,
                'latest_payroll_net' => 0.0,
                'latest_payroll_status' => null,
                'latest_payroll_period' => null,
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function agenticSummary(): array
    {
        try {
            $rules = AgentAction::ofCompany()->count();
            $enabled = AgentAction::ofCompany()->where('is_enabled', true)->count();
            $runsToday = AgentRun::ofCompany()->whereDate('created_at', today())->count();
            $rollbacks = AgentRun::ofCompany()
                ->whereIn('status', ['rolled_back', 'failed'])
                ->where('created_at', '>=', now()->subDays(7))
                ->count();
            $success = AgentRun::ofCompany()
                ->where('status', 'success')
                ->where('created_at', '>=', now()->subDays(7))
                ->count();
            $completed = AgentRun::ofCompany()
                ->whereIn('status', ['success', 'failed', 'rolled_back'])
                ->where('created_at', '>=', now()->subDays(7))
                ->count();

            return [
                'rules' => $rules,
                'enabled' => $enabled,
                'runs_today' => $runsToday,
                'rollbacks' => $rollbacks,
                'success_rate' => $completed > 0 ? round(($success / $completed) * 100, 1) : 100.0,
            ];
        } catch (\Throwable) {
            return [
                'rules' => 0,
                'enabled' => 0,
                'runs_today' => 0,
                'rollbacks' => 0,
                'success_rate' => 100.0,
            ];
        }
    }

    /**
     * @param  array<string, mixed>  $stats
     * @param  array<string, mixed>  $cashFlow
     * @param  array<string, mixed>  $hr
     * @param  array<string, mixed>  $agentic
     * @return array<int, array{label: string, value: string, tone: string}>
     */
    protected function buildBriefing(array $stats, array $cashFlow, array $hr, array $agentic): array
    {
        $nextMonth = $cashFlow['months'][1] ?? $cashFlow['months'][0] ?? null;
        $currency = (string) (app(SettingService::class)->get('currency.default') ?: 'USD');

        $briefing = [
            [
                'label' => 'Pipeline',
                'value' => $stats['open_opportunities'].' open opportunities worth '.$currency.' '.number_format((float) $stats['pipeline_value'], 0),
                'tone' => 'blue',
            ],
        ];

        if ($nextMonth) {
            $briefing[] = [
                'label' => 'Cash next month',
                'value' => ($nextMonth['net'] >= 0 ? '+' : '-').$currency.' '.number_format(abs((float) $nextMonth['net']), 0).' net ('.$nextMonth['full'].')',
                'tone' => $nextMonth['net'] >= 0 ? 'green' : 'red',
            ];
        }

        if ($cashFlow['overdue_count'] > 0) {
            $briefing[] = [
                'label' => 'Overdue receivables',
                'value' => $cashFlow['overdue_count'].' invoice(s), '.$currency.' '.number_format((float) $cashFlow['overdue_total'], 0).' outstanding',
                'tone' => 'amber',
            ];
        }

        $briefing[] = [
            'label' => 'Workforce',
            'value' => $hr['headcount'].' active employees · '.$hr['pending_leave'].' leave request(s) pending',
            'tone' => 'violet',
        ];

        $briefing[] = [
            'label' => 'Autonomous engine',
            'value' => $agentic['enabled'].'/'.$agentic['rules'].' rules enabled · '.$agentic['runs_today'].' run(s) today · '.$agentic['rollbacks'].' rollback(s) this week',
            'tone' => $agentic['rollbacks'] > 0 ? 'amber' : 'green',
        ];

        return $briefing;
    }

    protected function buildQuickActions($user, array $roleNames): array
    {
        $actions = [];

        if ($user->hasAnyPermission(['customers.create', 'leads.create'])) {
            $actions[] = ['label' => 'New customer', 'route' => 'customers.create', 'color' => 'blue'];
        }
        if ($this->safeHasPermission($user, 'leads.create')) {
            $actions[] = ['label' => 'New lead', 'route' => 'leads.create', 'color' => 'violet'];
        }
        if ($this->safeHasPermission($user, 'supplier_offers.create')) {
            $actions[] = ['label' => 'Log supplier offer', 'route' => 'supplier-offers.create', 'color' => 'amber'];
        }
        if ($this->safeHasPermission($user, 'finance.view')) {
            $actions[] = ['label' => 'Finance dashboard', 'route' => 'finance.dashboard', 'color' => 'green'];
        }
        if ($this->safeHasPermission($user, 'exports.view') || $user->hasRole('super_admin')) {
            $actions[] = ['label' => 'Export center', 'route' => 'admin.exports.index', 'color' => 'gray'];
        }
        if ($this->safeHasPermission($user, 'exports.import') || $user->hasRole('super_admin')) {
            $actions[] = ['label' => 'Import data', 'route' => 'admin.imports.index', 'color' => 'gray'];
        }
        if ($this->safeHasPermission($user, 'ai.chat')) {
            $actions[] = ['label' => 'AI copilot', 'route' => 'ai.copilot', 'color' => 'purple'];
        }

        return array_slice($actions, 0, 6);
    }

    protected function safeHasPermission($user, string $permission): bool
    {
        try {
            return $user->hasPermissionTo($permission);
        } catch (\Throwable) {
            return false;
        }
    }
}
