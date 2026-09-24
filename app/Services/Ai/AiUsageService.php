<?php

namespace App\Services\Ai;

use App\Models\AiUsageLog;
use App\Services\CompanyContext;
use Illuminate\Support\Facades\Log;

/**
 * Persists AI request metadata for the admin dashboard and per-user / per-org
 * usage-limit enforcement. Never stores prompts, responses or API keys.
 */
class AiUsageService
{
    /**
     * Record a single usage event. Failures are swallowed so usage tracking can
     * never break an AI request or the ERP.
     */
    public function record(
        string $provider,
        string $status = 'success',
        ?string $task = null,
        ?string $requestType = null,
        ?string $model = null,
        ?int $latencyMs = null,
        ?int $promptTokens = null,
        ?int $completionTokens = null,
        ?float $cost = null,
        ?string $errorCategory = null,
        ?string $errorMessage = null,
    ): void {
        try {
            $user = auth()->user();

            AiUsageLog::query()->insert([
                'company_id' => CompanyContext::id(),
                'user_id' => $user?->id,
                'provider' => $provider,
                'model' => $model,
                'task' => $task,
                'request_type' => $requestType,
                'status' => $status,
                'latency_ms' => $latencyMs,
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'cost' => $cost,
                'error_category' => $errorCategory,
                'error_message' => $errorMessage !== null ? mb_substr($errorMessage, 0, 500) : null,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to record AI usage event', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Count usage in a period. When $userId is null, counts company-wide.
     * When $provider is null, counts across providers.
     */
    public function countForPeriod(
        \DateTimeInterface $from,
        ?int $userId = null,
        ?string $provider = null,
        ?int $companyId = null,
        string $status = 'success',
    ): int {
        $companyId ??= CompanyContext::id();

        $query = AiUsageLog::query()->where('created_at', '>=', $from);

        if ($companyId !== null) {
            $query->where('company_id', $companyId);
        }
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }
        if ($provider !== null) {
            $query->where('provider', $provider);
        }

        return (int) $query->where('status', $status)->count();
    }

    /**
     * Daily successful request count for a user (or company when null).
     */
    public function dailyCount(?int $userId = null, ?string $provider = null): int
    {
        return $this->countForPeriod(now()->startOfDay(), $userId, $provider);
    }

    /**
     * Monthly successful request count for a user (or company when null).
     */
    public function monthlyCount(?int $userId = null, ?string $provider = null): int
    {
        return $this->countForPeriod(now()->startOfMonth(), $userId, $provider);
    }
}
