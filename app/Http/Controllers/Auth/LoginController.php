<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\LoginThrottle;
use App\Services\SecurityEventService;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(
        protected LoginThrottle $throttle,
        protected TwoFactorAuthService $totp,
    ) {}

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = LoginThrottle::attemptKey($credentials['email']);
        $remember = $request->boolean('remember');

        if ($this->throttle->blocked($key)) {
            SecurityEventService::recordLogin('[REDACTED]', false, reason: 'throttled');
            throw ValidationException::withMessages([
                'email' => 'Too many login attempts. Please wait '.$this->throttle->delaySeconds($key).' seconds before trying again.',
            ]);
        }

        if (! Auth::attempt($credentials, $remember)) {
            $this->throttle->hit($key);
            SecurityEventService::recordLogin('[REDACTED]', false, reason: 'invalid_credentials');
            SecurityEventService::evaluateLoginPattern($credentials['email']);
            AuditLogger::log('login_failed', 'auth');

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $user = $request->user();

        if (! $user->is_active) {
            Auth::logout();
            SecurityEventService::recordLogin('[REDACTED]', false, $user, 'disabled_account');

            throw ValidationException::withMessages([
                'email' => 'Your account is disabled. Contact your administrator.',
            ]);
        }

        // Clear stale second-factor state BEFORE regenerating the session id.
        // Regenerating first and then forgetting could leave a verification
        // timestamp created for a previous account attached to the new
        // session, which is the root cause of the "invalid token" loop on
        // administrative sign-in. We intentionally do not touch recovery
        // codes or the stored TOTP secret here.
        $request->session()->forget([
            'two_factor_verified_at',
            'mfa_setup_secret',
            'mfa_recovery_codes',
        ]);

        $request->session()->regenerate();
        $this->throttle->clear($key);

        SecurityEventService::recordLogin($user->email, true, $user, 'success');

        $user->update([
            'last_login_at' => now(),
        ]);

        AuditLogger::log('login', 'auth');
        SecurityEventService::detectImpossibleTravel($user, (string) $request->ip());

        return $this->redirectAfterLogin($request, $user);
    }

    /**
     * Decide where a freshly authenticated user should land.
     *
     * - Enrolled users always get the TOTP challenge (even when mandatory
     *   enforcement is disabled, otherwise opt-in MFA would be bypassable).
     * - Mandatory roles without enrolment are sent to setup only when
     *   enforcement is active.
     * - A stored secret that cannot be decrypted (for example after an
     *   APP_KEY rotation) is treated as a broken enrolment and reset safely
     *   so the owner can re-enrol instead of being locked out.
     */
    protected function redirectAfterLogin(Request $request, User $user): RedirectResponse
    {
        if ($user->hasMfa() && $this->totp->secretFor($user) === null) {
            $this->totp->disable($user);
            $request->session()->forget('two_factor_verified_at');

            AuditLogger::log('mfa_secret_reset', 'auth', $user, null, [
                'reason' => 'stored_secret_undecryptable',
            ]);

            if ((bool) config('security.mfa.enforce', false) && $user->hasMandatoryMfaRole()) {
                return redirect()->route('mfa.setup')->with(
                    'error',
                    'Your authenticator enrolment could not be verified and was reset. Please enrol again.',
                );
            }

            return redirect()->intended(route('dashboard'));
        }

        if ($user->hasMfa()) {
            return redirect()->route('mfa.challenge');
        }

        if ((bool) config('security.mfa.enforce', false) && $user->hasMandatoryMfaRole()) {
            return redirect()->route('mfa.setup');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        AuditLogger::log('logout', 'auth');
        SecurityEventService::record('auth', 'logout', 'User logged out', 'info');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
