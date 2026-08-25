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
            'intended' => $request->string('intended', route('dashboard'))->toString(),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'password' => ['required', 'string'],
            'code' => ['nullable', 'string'],
            'intended' => ['nullable', 'url'],
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

        $request->session()->put('step_up_verified_at', now()->getTimestamp());
        AuditLogger::log('step_up_verified', 'auth');

        $intended = $data['intended'] ?? null;
        if ($intended) {
            $parsedUrl = parse_url($intended);
            $localPath = $parsedUrl['path'] ?? $intended;
            if (str_starts_with($localPath, '/') && ! str_contains($localPath, '://')) {
                return redirect()->to($localPath);
            }
        }

        return redirect()->route('dashboard');
    }
}
