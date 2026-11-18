<?php

namespace App\Http\Controllers;

use App\Models\AgentAction;
use App\Models\AgentRun;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\LeaveRequest;
use App\Models\OnboardingTask;
use App\Models\Opportunity;
use App\Models\PayrollRun;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SupplierOffer;
use App\Services\Ai\AiInsightService;
use App\Services\Ai\AiQuotaService;
use App\Services\CompanyContext;
use App\Services\DashboardCacheService;
use App\Services\SettingService;
use App\Services\StockService;
use App\Support\NavigationAccess;
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

        $currencyCode = strtoupper((string) (Company::find($companyId)?->currency_code ?: 'USD'));
        $currencySymbol = match ($currencyCode) {
            'INR' => '₹',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => $currencyCode.' ',
        };

        try {
            $aggregates = DashboardCacheService::remember($companyId, function (): array {
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

                return [
                    'stats' => $stats,
                    'leadsByStatus' => $leadsByStatus,
                    'opportunitiesByStage' => $opportunitiesByStage,
                    'offers' => $offers,
                    'recentCustomers' => Customer::ofCompany()->latest()->limit(5)->get(),
                    'recentLeads' => Lead::ofCompany()->with('assignedTo')->latest()->limit(5)->get(),
                    'recentOffers' => SupplierOffer::ofCompany()->with('supplier')->latest()->limit(5)->get(),
                    'followUps' => Lead::ofCompany()
                        ->whereNotNull('next_follow_up')
                        ->where('status', '!=', 'won')
                        ->where('next_follow_up', '<=', now()->addDays(7))
                        ->orderBy('next_follow_up')
                        ->limit(8)
                        ->get(),
                    'cashFlow' => $this->cashFlowForecast(),
                    'hrSummary' => $this->hrSummary(),
                    'agenticSummary' => $this->agenticSummary(),
                    'analytics' => [
                        'revenueVelocity' => $this->revenueVelocity(),
                        'pipelineConversion' => $this->pipelineConversion(),
                        'inventoryTurnover' => $this->inventoryTurnover(),
                    ],
                ];
            });
        } catch (\Throwable $e) {
            report($e);
            $aggregates = $this->emptyAggregates();
        }

        $stats = $aggregates['stats'];
        $leadsByStatus = $aggregates['leadsByStatus'];
        $opportunitiesByStage = $aggregates['opportunitiesByStage'];
        $offers = $aggregates['offers'];
        $recentCustomers = $aggregates['recentCustomers'];
        $recentLeads = $aggregates['recentLeads'];
        $recentOffers = $aggregates['recentOffers'];
        $followUps = $aggregates['followUps'];

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
            ['name' => 'Documents', 'slug' => 'documents', 'ready' => true, 'icon' => 'folder', 'route' => 'admin.documents.index', 'permission' => 'documents.view', 'role' => 'super_admin'],
            ['name' => 'AI Center', 'slug' => 'ai', 'ready' => true, 'icon' => 'spark', 'route' => 'ai.copilot', 'permission' => 'ai.chat'],
            ['name' => 'Automation', 'slug' => 'automation', 'ready' => true, 'icon' => 'bolt', 'route' => 'admin.agentic.index', 'permission' => null, 'role' => 'super_admin'],
        ];

        $modules = array_filter($allModules, function ($m) use ($user) {
            if (! empty($m['role']) && ! $user->hasRole($m['role'])) {
                return false;
            }
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
        $navigationAccess = app(NavigationAccess::class);
        $ruleInsights = collect($insightService->rules($companyId))
            ->filter(fn (array $insight) => empty($insight['route']) || $navigationAccess->can($insight['route'], $user))
            ->values()
            ->all();
        $aiInsights = $insightService->cachedAi($companyId);
        $aiEnabled = $insightService->gatewayEnabled();

        // Check the quota exactly once per request and share the result with
        // both the quota widget and the trend analytics.
        try {
            $aiQuota = app(AiQuotaService::class)->check();
        } catch (\Throwable $e) {
            report($e);
            $aiQuota = [];
        }

        $primaryRole = $user->getRoleNames()->first();
        $isExecutive = in_array($primaryRole, ['super_admin', 'company_admin', 'ceo', 'cfo', 'coo']);

        $quickActions = $this->buildQuickActions($user, $roleNames);

        $cashFlow = $aggregates['cashFlow'];
        $hrSummary = $aggregates['hrSummary'];
        $agenticSummary = $aggregates['agenticSummary'];
        $briefing = $this->buildBriefing($stats, $cashFlow, $hrSummary, $agenticSummary);

        $analytics = $aggregates['analytics'];
        $analytics['aiQuotaTrend'] = $this->aiQuotaTrend($aiQuota);

        return view('dashboard.index', compact(
            'stats', 'leadsByStatus', 'opportunitiesByStage', 'offers',
            'recentCustomers', 'recentLeads', 'recentOffers', 'followUps', 'modules',
            'ruleInsights', 'aiInsights', 'aiEnabled', 'aiQuota', 'isExecutive',
            'quickActions', 'roleNames', 'cashFlow', 'hrSummary', 'agenticSummary', 'briefing', 'analytics', 'currencySymbol',
            'navigationAccess'
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
     * Monthly invoiced revenue (paid + outstanding) over the last 6 months.
     */
    protected function revenueVelocity(): array
    {
        $months = [];
        $start = now()->subMonths(5)->startOfMonth();
        try {
            for ($i = 0; $i < 6; $i++) {
                $month = $start->copy()->addMonthsNoOverflow($i);
                $revenue = (float) Invoice::ofCompany()
                    ->whereYear('issue_date', $month->year)
                    ->whereMonth('issue_date', $month->month)
                    ->whereIn('status', ['sent', 'partial', 'paid', 'overdue'])
                    ->sum('total');
                $months[] = [
                    'label' => $month->format('M Y'),
                    'short' => $month->format('M'),
                    'revenue' => round($revenue, 2),
                ];
            }
        } catch (\Throwable) {
            $months = [];
        }
        return ['labels' => array_column($months, 'short'), 'data' => array_column($months, 'revenue')];
    }

    /**
     * Pipeline conversion ratios by stage (won vs total by stage family).
     */
    protected function pipelineConversion(): array
    {
        try {
            $won = (float) Opportunity::ofCompany()->where('stage', 'won')->sum('expected_value');
            $open = (float) Opportunity::ofCompany()->whereNotIn('stage', ['won', 'lost'])->sum('expected_value');
            $lost = (float) Opportunity::ofCompany()->where('stage', 'lost')->sum('expected_value');
            $total = $won + $open + $lost;
            $conversionRate = $total > 0 ? round(($won / $total) * 100, 1) : 0;
            $winRate = ($won + $lost) > 0 ? round(($won / ($won + $lost)) * 100, 1) : 0;
            return [
                'won' => round($won, 2),
                'open' => round($open, 2),
                'lost' => round($lost, 2),
                'conversion_rate' => $conversionRate,
                'win_rate' => $winRate,
            ];
        } catch (\Throwable) {
            return ['won' => 0, 'open' => 0, 'lost' => 0, 'conversion_rate' => 0, 'win_rate' => 0];
        }
    }

    /**
     * Inventory turnover velocity based on stock movements (last 30 days).
     */
    protected function inventoryTurnover(): array
    {
        try {
            $stockOut = (float) \App\Models\StockMovement::ofCompany()
                ->where('type', 'out')
                ->where('created_at', '>=', now()->subDays(30))
                ->sum('quantity');
            $stockIn = (float) \App\Models\StockMovement::ofCompany()
                ->where('type', 'in')
                ->where('created_at', '>=', now()->subDays(30))
                ->sum('quantity');
            // One grouped stock query for the whole catalogue (no per-product
            // movement queries) via the shared request-scoped snapshot.
            $companyId = CompanyContext::id();
            $balances = StockService::balances($companyId);
            $activeProducts = Product::ofCompany()->where('status', 'active')->get(['id', 'reorder_level']);
            $lowStock = $activeProducts->filter(
                fn (Product $product) => ($balances[$product->id] ?? 0.0) <= (float) $product->reorder_level
            )->count();
            return [
                'stock_out' => round($stockOut, 2),
                'stock_in' => round($stockIn, 2),
                'turnover' => $stockIn > 0 ? round(($stockOut / $stockIn) * 100, 1) : 0,
                'active_products' => $activeProducts->count(),
                'low_stock' => $lowStock,
            ];
        } catch (\Throwable) {
            return ['stock_out' => 0, 'stock_in' => 0, 'turnover' => 0, 'active_products' => 0, 'low_stock' => 0];
        }
    }

    /**
     * AI quota utilization trend (daily used vs limit) and current quota.
     * The quota is supplied by the caller so it is resolved only once.
     *
     * @param  array<string, mixed>  $quota
     */
    protected function aiQuotaTrend(array $quota): array
    {
        try {
            return [
                'daily_used' => (int) ($quota['daily_used'] ?? 0),
                'daily_limit' => (int) ($quota['daily_limit'] ?? 0),
                'weekly_used' => (int) ($quota['weekly_used'] ?? 0),
                'weekly_limit' => (int) ($quota['weekly_limit'] ?? 0),
                'utilization' => ($quota['daily_limit'] ?? 0) > 0 ? round(((float) ($quota['daily_used'] ?? 0) / (float) $quota['daily_limit']) * 100, 1) : 0,
            ];
        } catch (\Throwable) {
            return ['daily_used' => 0, 'daily_limit' => 0, 'weekly_used' => 0, 'weekly_limit' => 0, 'utilization' => 0];
        }
    }

    /**
     * Safe defaults when an aggregate query is unavailable.
     *
     * @return array<string, mixed>
     */
    protected function emptyAggregates(): array
    {
        return [
            'stats' => [
                'customers' => 0, 'leads' => 0, 'leads_new' => 0,
                'open_opportunities' => 0, 'pipeline_value' => 0.0, 'won_value' => 0.0,
            ],
            'leadsByStatus' => [],
            'opportunitiesByStage' => [],
            'offers' => ['total' => 0, 'green' => 0, 'yellow' => 0, 'red' => 0],
            'recentCustomers' => collect(),
            'recentLeads' => collect(),
            'recentOffers' => collect(),
            'followUps' => collect(),
            'cashFlow' => ['months' => [], 'overdue_total' => 0.0, 'overdue_count' => 0],
            'hrSummary' => [
                'headcount' => 0, 'on_leave' => 0, 'pending_leave' => 0,
                'onboarding_open' => 0, 'latest_payroll_net' => 0.0,
                'latest_payroll_status' => null, 'latest_payroll_period' => null,
            ],
            'agenticSummary' => [
                'rules' => 0, 'enabled' => 0, 'runs_today' => 0, 'rollbacks' => 0, 'success_rate' => 100.0,
            ],
            'analytics' => [
                'revenueVelocity' => ['labels' => [], 'data' => []],
                'pipelineConversion' => ['won' => 0, 'open' => 0, 'lost' => 0, 'conversion_rate' => 0, 'win_rate' => 0],
                'inventoryTurnover' => ['stock_out' => 0, 'stock_in' => 0, 'turnover' => 0, 'active_products' => 0, 'low_stock' => 0],
            ],
        ];
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
            'value' => $hr['headcount'].' active employees Â· '.$hr['pending_leave'].' leave request(s) pending',
            'tone' => 'violet',
        ];

        $briefing[] = [
            'label' => 'Autonomous engine',
            'value' => $agentic['enabled'].'/'.$agentic['rules'].' rules enabled Â· '.$agentic['runs_today'].' run(s) today Â· '.$agentic['rollbacks'].' rollback(s) this week',
            'tone' => $agentic['rollbacks'] > 0 ? 'amber' : 'green',
        ];

        return $briefing;
    }

    protected function buildQuickActions($user, array $roleNames): array
    {
        $actions = [];

        if ($this->safeHasPermission($user, 'customers.create')) {
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
        if ($user->hasRole('super_admin')) {
            $actions[] = ['label' => 'Export center', 'route' => 'admin.exports.index', 'color' => 'gray'];
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
