<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\LoginThrottle;
use App\Services\SecurityEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(protected LoginThrottle $throttle) {}

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

        $request->session()->regenerate();
        $this->throttle->clear($key);

        SecurityEventService::recordLogin($user->email, true, $user, 'success');

        $user->update([
            'last_login_at' => now(),
        ]);

        AuditLogger::log('login', 'auth');
        SecurityEventService::detectImpossibleTravel($user, (string) $request->ip());

        if ($user->hasMfa()) {
            $request->session()->forget('two_factor_verified_at');

            return redirect()->route('mfa.challenge');
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
