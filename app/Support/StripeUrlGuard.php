<?php

namespace App\Support;

use RuntimeException;

/**
 * Validates every URL that can either carry the Stripe bearer token or be
 * used as a post-checkout redirect. Configuration is trusted only when it
 * matches a narrow allowlist; anything else fails closed.
 */
final class StripeUrlGuard
{
    public static function apiBase(): string
    {
        $base = rtrim((string) config('services.stripe.api_base', 'https://api.stripe.com'), '/');
        $allowed = array_map(
            fn ($value) => rtrim((string) $value, '/'),
            (array) config('services.stripe.api_base_allowed', ['https://api.stripe.com'])
        );

        if (! in_array($base, $allowed, true)) {
            throw new RuntimeException('Stripe API endpoint is not configured safely.');
        }

        return $base;
    }

    /**
     * Checkout and Billing Portal URLs are Stripe-hosted. A signed upstream
     * response is still validated so a poisoned response cannot be used as an
     * open redirect from a trusted admin URL.
     */
    public static function sessionUrl(string $url): string
    {
        $parts = parse_url($url);

        if ($parts === false
            || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || trim((string) ($parts['host'] ?? '')) === ''
            || isset($parts['user']) || isset($parts['pass'])
            || (isset($parts['port']) && (int) $parts['port'] !== 443)) {
            throw new RuntimeException('Stripe returned an invalid session URL.');
        }

        $host = strtolower((string) $parts['host']);
        $allowed = array_map(
            fn ($value) => strtolower(trim((string) $value)),
            (array) config('services.stripe.redirect_hosts', ['checkout.stripe.com', 'billing.stripe.com'])
        );

        if (! in_array($host, $allowed, true)) {
            throw new RuntimeException('Stripe returned an invalid session URL.');
        }

        return $url;
    }

    /**
     * Return URLs must stay inside this application. They may be absolute
     * HTTPS URLs in production and localhost URLs during local development.
     */
    public static function returnUrl(?string $configured, string $fallback): string
    {
        $configured = trim((string) $configured);
        if ($configured === '') {
            return $fallback;
        }

        $parts = parse_url($configured);
        if ($parts === false
            || ! in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)
            || trim((string) ($parts['host'] ?? '')) === ''
            || isset($parts['user']) || isset($parts['pass'])) {
            throw new RuntimeException('Stripe return URL is not configured safely.');
        }

        $host = strtolower((string) $parts['host']);
        $allowed = array_filter(array_map(
            fn ($value) => strtolower(trim((string) $value)),
            array_merge(
                [(string) parse_url((string) config('app.url'), PHP_URL_HOST), (string) parse_url($fallback, PHP_URL_HOST)],
                (array) config('services.stripe.return_hosts', [])
            )
        ));

        if (! in_array($host, $allowed, true)) {
            throw new RuntimeException('Stripe return URL is not configured safely.');
        }

        if (app()->environment('production')
            && strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            && ! in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            throw new RuntimeException('Stripe return URL must use HTTPS in production.');
        }

        return $configured;
    }
}
