<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Services\Ai\AiQuotaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsagePlanController extends Controller
{
    public function __construct(protected AiQuotaService $quota) {}

    public function index(): View
    {
        $user = auth()->user();
        $usage = $this->quota->check($user->id);
        $plans = $this->getPlans();

        return view('ai.usage-plan', compact('usage', 'plans', 'user'));
    }

    public function apiStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $usage = $this->quota->check($user->id);

        return response()->json([
            'plan' => $this->getUserPlan($user),
            'usage' => [
                'daily_used' => $usage['daily_used'] ?? 0,
                'daily_limit' => $usage['daily_limit'] ?? 1,
                'weekly_used' => $usage['weekly_used'] ?? 0,
                'weekly_limit' => $usage['weekly_limit'] ?? 3,
                'daily_remaining' => $usage['daily_remaining'] ?? 0,
                'weekly_remaining' => $usage['weekly_remaining'] ?? 0,
            ],
            'upgrade_url' => route('ai.usage-plan.index'),
        ]);
    }

    protected function getPlans(): array
    {
        return [
            'free' => [
                'name' => 'Free Plan',
                'price' => 0,
                'period' => 'forever',
                'daily_limit' => 1,
                'weekly_limit' => 3,
                'features' => [
                    'Basic AI Assistant',
                    'Local business intelligence',
                    '3 AI queries per week',
                    'Standard response time',
                ],
            ],
            'starter' => [
                'name' => 'Starter Plan',
                'price' => 29,
                'period' => 'month',
                'daily_limit' => 10,
                'weekly_limit' => 30,
                'features' => [
                    'All Free Plan features',
                    '10 AI queries per day',
                    'Email drafting',
                    'Customer summaries',
                    'Invoice analysis',
                    'Priority response time',
                ],
            ],
            'professional' => [
                'name' => 'Professional Plan',
                'price' => 79,
                'period' => 'month',
                'daily_limit' => 50,
                'weekly_limit' => 200,
                'features' => [
                    'All Starter Plan features',
                    '50 AI queries per day',
                    'Deep analysis (Claude-grade)',
                    'Strategic recommendations',
                    'Executive review',
                    'Procurement AI',
                    'Document analysis',
                ],
            ],
            'enterprise' => [
                'name' => 'Enterprise Plan',
                'price' => 199,
                'period' => 'month',
                'daily_limit' => 999,
                'weekly_limit' => 9999,
                'features' => [
                    'All Professional Plan features',
                    'Unlimited AI queries',
                    'Custom AI skills',
                    'API access',
                    'Priority support',
                    'Custom integrations',
                    'White-label options',
                ],
            ],
        ];
    }

    protected function getUserPlan($user): string
    {
        $roleNames = $user->getRoleNames()->toArray();
        if (in_array('super_admin', $roleNames) || in_array('company_admin', $roleNames)) {
            return 'enterprise';
        }
        if (in_array('sales_manager', $roleNames) || in_array('finance_manager', $roleNames) || in_array('purchase_manager', $roleNames)) {
            return 'professional';
        }
        if (in_array('staff', $roleNames) || in_array('sales_executive', $roleNames) || in_array('accountant', $roleNames)) {
            return 'starter';
        }

        return 'free';
    }
}
