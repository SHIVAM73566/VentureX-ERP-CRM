<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Laravel\Sanctum\TransientToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sanctum ability enforcement for the REST API.
 *
 * The required ability is derived from the request method (read for safe
 * verbs, write for mutating verbs) and the API module encoded in the path.
 * AI and RAG surfaces accept the module-scoped ai.read/ai.write and
 * rag.read/rag.write abilities as well as the short ai/rag abilities so both
 * token conventions stay supported.
 * Session-authenticated requests and Sanctum's first-party TransientToken are
 * allowed through: they are governed by the normal web policies instead.
 */
class EnsureTokenAbility
{
    /**
     * Map an API path segment to the module prefix used by issued tokens.
     *
     * @var array<string, string>
     */
    protected const MODULES = [
        'crm/customers' => 'customers',
        'crm/contacts' => 'contacts',
        'crm/leads' => 'leads',
        'crm/opportunities' => 'opportunities',
        'sales/quotations' => 'quotations',
        'sales/orders' => 'sales',
        'sales/invoices' => 'invoices',
        'sales/payments' => 'payments',
        'inventory/products' => 'products',
        'inventory/warehouses' => 'warehouses',
        'inventory/stock-movements' => 'stock',
        'procurement/suppliers' => 'suppliers',
        'procurement/purchase-orders' => 'purchase',
        'finance/accounts' => 'accounts',
        'finance/journal-entries' => 'journal',
        // RAG is matched before the generic AI prefix so it gets its own
        // ai/rag ability namespace (with ai.* kept as a compatible fallback).
        'ai/rag' => 'rag',
        'ai' => 'ai',
        'support/tickets' => 'tickets',
        // Token self-management is deliberately not exempt: an ability-limited
        // token must never be able to mint a replacement with more abilities.
        'tokens' => 'tokens',
    ];

    /** Routes that may be reached with any valid token (self profile only). */
    protected const EXEMPT_PREFIXES = ['user'];

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user()?->currentAccessToken();

        // No token (session auth) or a first-party transient token: policies
        // remain the authorisation boundary, but state-changing calls must
        // still carry a CSRF token — API routes are outside the web group and
        // would otherwise be callable cross-site via the session cookie.
        if ($token === null || $token instanceof TransientToken) {
            // State-changing calls authenticated only by a first-party session
            // must carry a CSRF token. API routes normally have no session
            // store (bearer-token clients), in which case policies and Sanctum
            // remain the authorisation boundary and we must not fatal here.
            if ($request->hasSession() && ! in_array($request->getRealMethod(), ['GET', 'HEAD', 'OPTIONS'], true)) {
                app(VerifyCsrfToken::class)->handle(
                    $request,
                    fn (Request $passed) => $next($passed)
                );
            }

            return $next($request);
        }

        $path = ltrim($request->path(), '/');

        // Strip the /api prefix that Laravel's API route file already applies.
        if (str_starts_with($path, 'api/')) {
            $path = substr($path, 4);
        }

        foreach (self::EXEMPT_PREFIXES as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return $next($request);
            }
        }

        $module = $this->moduleFor($path);
        if ($module === null) {
            // Unknown surfaces fail closed — an ability cannot be fabricated
            // for a route the policy does not understand.
            abort(403, 'This API surface has no token ability mapping.');
        }

        $mode = $this->modeFor($request, $path);

        foreach ($this->acceptedAbilities($token, $module, $mode) as $ability) {
            if ($token->can($ability)) {
                return $next($request);
            }
        }

        abort(403, 'This API token is not authorised for '.$module.'.'.$mode.'.');
    }

    /**
     * Abilities that satisfy this request. A wildcard token is handled first.
     *
     * @return array<int, string>
     */
    protected function acceptedAbilities($token, string $module, string $mode): array
    {
        if ($token->can('*')) {
            return ['*'];
        }

        // Token management is server-owned: only a wildcard/full-access token
        // may create, list or revoke tokens.
        if ($module === 'tokens') {
            return ['tokens.'.$mode];
        }

        $abilities = [$module.'.'.$mode, $mode];

        if (in_array($module, ['ai', 'rag'], true)) {
            // Both surfaces accept either namespace, plus short-form abilities.
            $abilities[] = 'ai.'.$mode;
            $abilities[] = 'rag.'.$mode;
            $abilities[] = $module;
            $abilities[] = 'ai';
            $abilities[] = 'rag';
        }

        return array_values(array_unique($abilities));
    }

    /**
     * Safe verbs require read scope; everything else requires write scope.
     * A RAG query is a POST but is semantically a read operation.
     */
    protected function modeFor(Request $request, string $path): string
    {
        if (in_array($request->getRealMethod(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return 'read';
        }

        if (str_starts_with($path, 'ai/rag/query')) {
            return 'read';
        }

        return 'write';
    }

    protected function moduleFor(string $path): ?string
    {
        foreach (self::MODULES as $prefix => $module) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return $module;
            }
        }

        return null;
    }
}
