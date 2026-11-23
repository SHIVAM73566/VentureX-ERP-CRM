<?php

namespace App\Http\Middleware;

use App\Services\SecurityStateService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Emergency Security Mode: blocks access to the application except for
 * exempted routes and super administrators, until it is lifted.
 */
class EmergencyLockdown
{
    public function handle(Request $request, Closure $next): Response
    {
        // Before the one-time installer completes there is no cache/audit
        // schema yet. Let the installer run; lockdown applies afterwards.
        if (! file_exists(storage_path('.installed'))) {
            return $next($request);
        }

        if (! SecurityStateService::lockdownEnabled()) {
            return $next($request);
        }

        // Super admins may still reach the security controls to lift the lockdown.
        if ($request->user() && $request->user()->hasRole('super_admin')) {
            return $next($request);
        }

        $except = config('security.lockdown.except', []);
        if ($request->routeIs(...$except)) {
            return $next($request);
        }

        abort(503, 'Emergency security mode is active. Contact your administrator.');
    }
}
