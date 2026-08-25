<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\SecurityEventService;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MfaController extends Controller
{
    public function __construct(protected TwoFactorAuthService $totp) {}

    public function setup(Request $request): View
    {
        $user = $request->user();

        if ($user->hasMfa()) {
            return view('auth.mfa-setup', [
                'enabled' => true,
                'mandatory' => $user->hasMandatoryMfaRole(),
            ]);
        }

        $secret = $request->session()->get('mfa_setup_secret') ?? $this->totp->generateSecret();
        $request->session()->put('mfa_setup_secret', $secret);

        return view('auth.mfa-setup', [
            'enabled' => false,
            'mandatory' => $user->hasMandatoryMfaRole(),
            'secret' => $secret,
            'otp_uri' => $this->totp->otpUri($user, $secret),
        ]);
    }

    public function enable(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasMfa()) {
            return redirect()->route('dashboard');
        }

        $secret = (string) $request->session()->get('mfa_setup_secret');
        $code = (string) $request->string('code');

        if ($secret === '' || ! $this->totp->verify($code, $secret)) {
            throw ValidationException::withMessages([
                'code' => 'The verification code is invalid or expired.',
            ]);
        }

        $recoveryCodes = $this->totp->enable($user, $secret);
        $request->session()->forget('mfa_setup_secret');

        AuditLogger::log('mfa_enabled', 'auth');
        SecurityEventService::record('auth', 'mfa_enabled', 'Two-factor authentication enabled', 'info', $user);

        // Recovery codes are shown exactly once.
        $request->session()->put('mfa_recovery_codes', $recoveryCodes);

        return redirect()->route('mfa.recovery');
    }

    public function recovery(Request $request)
    {
        $codes = $request->session()->pull('mfa_recovery_codes');

        if (! $codes) {
            return redirect()->route('dashboard');
        }

        $request->session()->put('two_factor_verified_at', now()->getTimestamp());

        return view('auth.mfa-recovery', ['codes' => $codes]);
    }

    public function challenge(Request $request)
    {
        $user = $request->user();

        if ($user->hasMfa() && $request->session()->get('two_factor_verified_at')) {
            return redirect()->route('dashboard');
        }

        if (! $user->hasMfa()) {
            return $user->hasMandatoryMfaRole()
                ? redirect()->route('mfa.setup')
                : redirect()->intended(route('dashboard'));
        }

        return view('auth.mfa-challenge');
    }

    public function verify(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasMfa()) {
            return redirect()->route('mfa.setup');
        }

        $code = trim((string) $request->string('code'));
        $secret = $this->totp->secretFor($user);

        $valid = $secret && $this->totp->verify($code, $secret);
        $valid = $valid || $this->totp->consumeRecoveryCode($user, $code);

        if (! $valid) {
            AuditLogger::log('mfa_failed', 'auth');
            SecurityEventService::record('auth', 'mfa_failed', 'Invalid MFA code entered', 'medium', $user);

            throw ValidationException::withMessages([
                'code' => 'The authentication code is invalid or expired.',
            ]);
        }

        $request->session()->put('two_factor_verified_at', now()->getTimestamp());
        AuditLogger::log('mfa_verified', 'auth');

        return redirect()->intended(route('dashboard'));
    }

    public function disable(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasMandatoryMfaRole()) {
            return back()->with('error', 'MFA is mandatory for your role and cannot be disabled.');
        }

        $this->totp->disable($user);
        $request->session()->forget('two_factor_verified_at');

        AuditLogger::log('mfa_disabled', 'auth');
        SecurityEventService::record('auth', 'mfa_disabled', 'Two-factor authentication disabled', 'high', $user);

        return back()->with('success', 'Two-factor authentication disabled.');
    }
}
