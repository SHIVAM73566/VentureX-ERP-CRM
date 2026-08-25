<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\SupplierOffer;
use App\Services\Ai\AiInsightService;
use App\Services\Ai\AiQuotaService;
use App\Services\CompanyContext;
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
            ['name' => 'HR', 'slug' => 'hr', 'ready' => false, 'icon' => 'badge', 'route' => null, 'permission' => 'hr.view'],
            ['name' => 'Projects', 'slug' => 'projects', 'ready' => false, 'icon' => 'briefcase', 'route' => null, 'permission' => 'projects.view'],
            ['name' => 'Documents', 'slug' => 'documents', 'ready' => true, 'icon' => 'folder', 'route' => 'admin.documents.index', 'permission' => 'documents.view'],
            ['name' => 'AI Center', 'slug' => 'ai', 'ready' => true, 'icon' => 'spark', 'route' => 'ai.copilot', 'permission' => 'ai.chat'],
            ['name' => 'Automation', 'slug' => 'automation', 'ready' => false, 'icon' => 'bolt', 'route' => null, 'permission' => null],
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

        return view('dashboard.index', compact(
            'stats', 'leadsByStatus', 'opportunitiesByStage', 'offers',
            'recentCustomers', 'recentLeads', 'recentOffers', 'followUps', 'modules',
            'ruleInsights', 'aiInsights', 'aiEnabled', 'aiQuota', 'isExecutive',
            'quickActions', 'roleNames'
        ));
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
