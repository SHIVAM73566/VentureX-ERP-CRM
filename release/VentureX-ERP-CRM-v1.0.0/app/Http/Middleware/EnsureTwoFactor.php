<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return $next($request);
        }

        // MFA enforcement can be disabled via env (set MFA_ENFORCE=false in .env).
        if (! (bool) config('security.mfa.enforce', true)) {
            return $next($request);
        }

        // Fully enrolled + this session already verified -> continue.
        if ($user->hasMfa() && $request->session()->get('two_factor_verified_at')) {
            return $next($request);
        }

        // Not enrolled but in a mandatory-MFA role -> force enrollment.
        if (! $user->hasMfa() && $user->hasMandatoryMfaRole()) {
            return redirect()->route('mfa.setup');
        }

        // Not enrolled and not mandatory -> skip (policy-based opt-in).
        if (! $user->hasMfa()) {
            return $next($request);
        }

        // Enrolled but not yet verified on this session -> challenge.
        return redirect()->route('mfa.challenge');
    }
}
