<?php

namespace App\Services\Ai;

use App\Models\AiProvider;
use App\Services\CompanyContext;
use Throwable;

/**
 * Enforces usage limits BEFORE a request reaches a provider.
 *
 * Limits come from the company's stored provider config (UI) when set, and
 * otherwise fall back to the ai.rate_limit defaults. Both per-user and
 * per-org (company) daily/monthly buckets are enforced so a single user or an
 * entire tenant cannot blow past their entitlement.
 */
class AiRateLimitService
{
    public function __construct(
        protected AiUsageService $usage,
        protected AiProviderManager $providers,
    ) {}

    /**
     * Effective limits for a provider. Returns null when the provider has no
     * limit configured anywhere (meaning: unlimited).
     *
     * @return array{per_user_daily: int|null, per_user_monthly: int|null, org_daily: int|null, org_monthly: int|null, fallback: bool}
     */
    public function effectiveLimits(string $provider, ?int $companyId = null): array
    {
        $companyId ??= CompanyContext::id();

        $defaults = [
            'per_user_daily' => (int) max(0, config('ai.rate_limit.max_per_user_per_day', 0)),
            'per_user_monthly' => (int) max(0, config('ai.rate_limit.max_per_user_per_month', 0)),
            'org_daily' => (int) max(0, config('ai.rate_limit.max_per_company_per_hour', 0) * 4),
            'org_monthly' => null,
        ];

        $row = null;
        if ($companyId !== null) {
            $row = AiProvider::query()
                ->where('company_id', $companyId)
                ->where('provider', $provider)
                ->first();
        }

        if ($row === null) {
            return [
                'per_user_daily' => $defaults['per_user_daily'] > 0 ? $defaults['per_user_daily'] : null,
                'per_user_monthly' => $defaults['per_user_monthly'] > 0 ? $defaults['per_user_monthly'] : null,
                'org_daily' => $defaults['org_daily'] > 0 ? $defaults['org_daily'] : null,
                'org_monthly' => null,
                'fallback' => true,
            ];
        }

        return [
            'per_user_daily' => $row->per_user_daily_limit ?? ($defaults['per_user_daily'] > 0 ? $defaults['per_user_daily'] : null),
            'per_user_monthly' => $row->per_user_monthly_limit ?? ($defaults['per_user_monthly'] > 0 ? $defaults['per_user_monthly'] : null),
            'org_daily' => $row->org_daily_limit ?? ($defaults['org_daily'] > 0 ? $defaults['org_daily'] : null),
            'org_monthly' => $row->org_monthly_limit ?? null,
            'fallback' => false,
        ];
    }

    /**
     * Assert the current user + org are under their limits for a provider.
     *
     * @throws AiException
     */
    public function assert(string $provider): void
    {
        $companyId = CompanyContext::id();
        $userId = auth()->id();

        $limits = $this->effectiveLimits($provider, $companyId);

        // Per-user daily
        if ($limits['per_user_daily'] !== null && $userId !== null) {
            $used = $this->usage->dailyCount($userId, $provider);
            if ($used >= $limits['per_user_daily']) {
                throw new AiException(
                    "Your daily AI usage limit for {$this->friendlyName($provider)} has been reached. "
                    .'Please try again tomorrow or contact your administrator to increase your limit.'
                );
            }
        }

        // Per-user monthly
        if ($limits['per_user_monthly'] !== null && $userId !== null) {
            $used = $this->usage->monthlyCount($userId, $provider);
            if ($used >= $limits['per_user_monthly']) {
                throw new AiException(
                    "Your monthly AI usage limit for {$this->friendlyName($provider)} has been reached. "
                    .'Please try again next month or contact your administrator.'
                );
            }
        }

        // Org daily
        if ($limits['org_daily'] !== null && $companyId !== null) {
            $used = $this->usage->dailyCount(null, $provider);
            if ($used >= $limits['org_daily']) {
                throw new AiException(
                    "Your company's daily AI usage limit has been reached for {$this->friendlyName($provider)}. "
                    .'Please try again tomorrow or ask your administrator to raise the limit.'
                );
            }
        }

        // Org monthly
        if ($limits['org_monthly'] !== null && $companyId !== null) {
            $used = $this->usage->monthlyCount(null, $provider);
            if ($used >= $limits['org_monthly']) {
                throw new AiException(
                    "Your company's monthly AI usage limit has been reached for {$this->friendlyName($provider)}."
                );
            }
        }
    }

    protected function friendlyName(string $provider): string
    {
        return ucfirst($provider);
    }
}