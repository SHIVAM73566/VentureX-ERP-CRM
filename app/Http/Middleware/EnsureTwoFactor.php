<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\TwoFactorAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactor
{
    public function __construct(protected TwoFactorAuthService $totp) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return $next($request);
        }

        $enforced = (bool) config('security.mfa.enforce', false);

        // A user who enrolled MFA is always challenged, regardless of the
        // global enforcement flag. The flag only controls whether privileged
        // roles are *forced* to enrol. Skipping enrolled users when
        // MFA_ENFORCE=false would silently disable their second factor.
        if ($user->hasMfa()) {
            if ($this->totp->secretFor($user) === null) {
                // Broken/undecryptable secret (APP_KEY rotation or corrupt
                // row). Reset the enrolment rather than trapping the user in
                // an impossible verification loop.
                $this->totp->disable($user);
                $request->session()->forget('two_factor_verified_at');

                return $enforced && $user->hasMandatoryMfaRole()
                    ? redirect()->route('mfa.setup')->with('error', 'Your authenticator enrolment was reset. Please enrol again.')
                    : $next($request);
            }

            if ($request->session()->get('two_factor_verified_at')) {
                return $next($request);
            }

            return redirect()->route('mfa.challenge');
        }

        // Not enrolled: force enrolment only for privileged roles when
        // enforcement is enabled.
        if ($enforced && $user->hasMandatoryMfaRole()) {
            return redirect()->route('mfa.setup');
        }

        return $next($request);
    }
}
