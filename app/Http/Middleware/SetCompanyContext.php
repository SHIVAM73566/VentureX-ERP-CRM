<?php

namespace App\Http\Middleware;

use App\Services\CompanyContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCompanyContext
{
    public function handle(Request $request, Closure $next): Response
    {
        // Always (re)set, including null: an unscoped user must never inherit
        // a previous request's tenant on Octane/long-lived workers.
        $user = $request->user();
        CompanyContext::set($user && $user->company_id ? (int) $user->company_id : null);

        return $next($request);
    }

    /**
     * Called by the framework after the response is sent. Keeps static state
     * from leaking between requests in long-lived workers.
     */
    public function terminate(Request $request, Response $response): void
    {
        CompanyContext::clear();
    }
}
