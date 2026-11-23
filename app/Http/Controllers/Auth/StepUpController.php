<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StepUpController extends Controller
{
    public function show(Request $request): View
    {
        return view('auth.step-up', [
            'intended' => $this->localPath($request->string('intended')->toString()) ?? route('dashboard'),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'password' => ['required', 'string'],
            'code' => ['nullable', 'string'],
            'intended' => ['nullable', 'string', 'max:2048'],
        ]);

        // Password re-entry OR MFA code.
        $validPassword = $data['password'] !== '' && Auth::validate([
            'email' => $user->email,
            'password' => $data['password'],
        ]);

        $validMfa = false;
        if ($user->hasMfa() && ! empty($data['code'])) {
            $validMfa = app(TwoFactorAuthService::class)->verify($data['code'], (string) app(TwoFactorAuthService::class)->secretFor($user));
        }

        if (! $validPassword && ! $validMfa) {
            AuditLogger::log('step_up_failed', 'auth');

            throw ValidationException::withMessages([
                'password' => 'Re-authentication failed. Enter your current password or a valid MFA code.',
            ]);
        }

        $request->session()->put('step_up_verified_at', [
            'user_id' => $user->id,
            'at' => now()->getTimestamp(),
        ]);
        AuditLogger::log('step_up_verified', 'auth');

        $intended = $this->localPath($data['intended'] ?? '');

        return $intended !== null
            ? redirect()->to($intended)
            : redirect()->route('dashboard');
    }

    /**
     * Only a single-leading-slash local path may be used after step-up.
     * This blocks protocol-relative redirects (//evil.example), backslashes
     * and any absolute URL with a scheme/host.
     */
    protected function localPath(string $value): ?string
    {
        if ($value === '' || str_contains($value, '\\') || str_contains($value, '://')) {
            return null;
        }

        $parsed = parse_url($value);
        if ($parsed === false || isset($parsed['scheme']) || isset($parsed['host'])) {
            return null;
        }

        $path = $parsed['path'] ?? '';
        if (! str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return null;
        }

        return $path.(isset($parsed['query']) ? '?'.$parsed['query'] : '');
    }
}
