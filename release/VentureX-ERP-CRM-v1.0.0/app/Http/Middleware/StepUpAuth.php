<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Step-up authentication: requires a fresh password/MFA verification
 * (default 5 minutes) before high-risk actions.
 */
class StepUpAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $stepUpAt = (int) $request->session()->get('step_up_verified_at', 0);

        if ($request->user() && $stepUpAt > (now()->subMinutes(5)->getTimestamp())) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Step-up authentication required. Re-authenticate and retry.'], 423);
        }

        return redirect()->route('step-up.show', ['intended' => $request->fullUrl()]);
    }
}
