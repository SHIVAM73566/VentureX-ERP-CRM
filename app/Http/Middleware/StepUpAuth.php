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
        $stepUp = $request->session()->get('step_up_verified_at');
        $user = $request->user();

        $verifiedAt = 0;
        if (is_array($stepUp)) {
            // New format binds the verification to the exact account.
            if ($user && (int) ($stepUp['user_id'] ?? 0) === (int) $user->id) {
                $verifiedAt = (int) ($stepUp['at'] ?? 0);
            }
        } else {
            // Legacy scalar format kept for existing sessions/tests.
            $verifiedAt = (int) $stepUp;
        }

        if ($user && $verifiedAt > now()->subMinutes(5)->getTimestamp()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Step-up authentication required. Re-authenticate and retry.'], 423);
        }

        // Pass only the local path+query; StepUpController validates it again.
        return redirect()->route('step-up.show', ['intended' => $request->getRequestUri()]);
    }
}
