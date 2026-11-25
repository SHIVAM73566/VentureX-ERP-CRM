<?php

use App\Services\CompanyContext;
use App\Services\EntitlementService;

if (! function_exists('company_id')) {
    /**
     * Resolve the current tenant (company) id from the authenticated context.
     * Returns null when no company context is active.
     */
    function company_id(): ?int
    {
        return CompanyContext::id();
    }
}

if (! function_exists('layer_enabled')) {
    /**
     * Check if a layer entitlement is active for the current company context.
     */
    function layer_enabled(string $layerKey): bool
    {
        try {
            return app(EntitlementService::class)->has($layerKey);
        } catch (Throwable $e) {
            return false;
        }
    }
}

if (! function_exists('mask_secret')) {
    /**
     * Return a display-safe masked representation of a secret. The original
     * value is never exposed beyond the last few characters.
     */
    function mask_secret(string $secret, int $visibleChars = 4): string
    {
        $length = strlen($secret);
        if ($length <= $visibleChars) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', $length - $visibleChars).substr($secret, -$visibleChars);
    }
}
