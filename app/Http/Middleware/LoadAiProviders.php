<?php

namespace App\Http\Middleware;

use App\Services\Ai\AiProviderManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Loads the current tenant's database-backed AI providers into the runtime
 * provider config on every request. Runs after SetCompanyContext so the
 * company is always derived from the authenticated request.
 */
class LoadAiProviders
{
    public function __construct(protected AiProviderManager $providers) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->providers->syncForCurrentContext();

        return $next($request);
    }
}
