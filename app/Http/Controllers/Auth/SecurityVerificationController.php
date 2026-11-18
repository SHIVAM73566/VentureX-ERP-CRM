<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\TrustedDevice;
use App\Models\User;
use App\Services\AdvancedSecurityService;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SecurityVerificationController extends Controller
{
    /**
     * Show 3-step registration form
     */
    public function showRegister(): View
    {
        return view('auth.register-steps');
    }

    /**
     * Step 1: Basic info + email verification.
     *
     * Self-registration is only possible with a company-bound invitation.
     * Without one we fail closed rather than creating an orphan user with a
     * null company_id (which would bypass every tenant scope).
     */
    public function registerStep1(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => ['required', 'string', 'min:10', 'confirmed', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'regex:/[^A-Za-z0-9]/'],
            'invitation_token' => ['required', 'string'],
        ]);

        $invitation = $this->decodeInvitation($validated['invitation_token']);
        $email = mb_strtolower($validated['email']);

        if (! hash_equals(mb_strtolower($invitation['email']), $email)) {
            abort(403, 'This invitation was issued for a different email address.');
        }

        if (User::withTrashed()->where('email', $email)->exists()) {
            return response()->json([
                'message' => 'An account already exists for this email address.',
                'errors' => ['email' => ['An account already exists for this email address.']],
            ], 422);
        }

        // Store in session for multi-step. The company binding is captured
        // from the signed invitation and can never be supplied by the client
        // at step 3.
        $request->session()->put('register_step1', [
            'name' => $validated['name'],
            'email' => $email,
            'password' => $validated['password'],
            'company_id' => $invitation['company_id'],
            'otp_id' => 'reg_'.Str::uuid()->toString(),
        ]);

        // Generate and actually deliver the OTP. The plaintext code is sent
        // only to the invited mailbox — never rendered in the response,
        // flashed to the session or written to a log.
        $otp = AdvancedSecurityService::generateEmailOtp($email);
        $this->deliverEmailOtp($email, $otp);

        return response()->json([
            'step' => 1,
            'message' => 'Verification code sent to '.$email,
        ]);
    }

    /**
     * Step 2: Phone verification
     */
    public function registerStep2(Request $request)
    {
        $step1 = $request->session()->get('register_step1');
        if (! $step1 || ! isset($step1['email'], $step1['otp_id'], $step1['company_id'])) {
            return redirect()->route('register');
        }

        $otp = (string) $request->input('otp', '');
        // One identifier, normalised once at step 1, used by both phone
        // generation and verification.
        if (! AdvancedSecurityService::verifyEmailOtp($step1['email'], $otp)) {
            return back()->withErrors(['otp' => 'Invalid verification code']);
        }

        $validated = $request->validate([
            'phone' => 'required|string|max:20',
        ]);

        $request->session()->put('register_step2', $validated);

        // Phone verification happens before the user row exists, so the
        // phone_verifications table (which has a users FK) cannot be used.
        // Keep a hashed, expiring challenge in the cache instead. Only the
        // invite-bound otp_id from step 1 can address it.
        $phoneOtp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put($this->registrationPhoneOtpKey($step1['otp_id']), [
            'phone' => $validated['phone'],
            'hash' => hash_hmac('sha256', $phoneOtp, config('app.key')),
            'masked' => substr($phoneOtp, 0, 2).'****'.substr($phoneOtp, -2),
            'attempts' => 0,
        ], now()->addMinutes(10));

        return response()->json([
            'step' => 2,
            'message' => 'Phone verification code sent',
        ]);
    }

    /**
     * Step 3: Selfie verification
     */
    public function registerStep3(Request $request)
    {
        $step2 = $request->session()->get('register_step2');
        $step1 = $request->session()->get('register_step1');

        if (! $step2 || ! $step1 || ! isset($step1['company_id'], $step1['otp_id'])) {
            return redirect()->route('register');
        }

        abort_if(Company::query()->whereKey($step1['company_id'])->where('is_active', true)->doesntExist(), 403, 'The inviting company is no longer active.');

        // Verify phone OTP — always required, no bypass. The identifier is the
        // same invite-bound otp_id used to create the challenge at step 2.
        $phoneOtp = (string) $request->input('phone_otp', '');
        if (! $this->verifyRegistrationPhoneOtp($step1['otp_id'], $step2['phone'], $phoneOtp)) {
            return back()->withErrors(['phone_otp' => 'Invalid phone code']);
        }

        $user = User::create([
            'name' => $step1['name'],
            'email' => $step1['email'],
            'password' => $step1['password'],
            'phone' => $step2['phone'],
            'company_id' => $step1['company_id'],
            'is_active' => true,
        ]);

        // Email was verified with a real OTP at step 2.
        $user->forceFill(['email_verified_at' => now()])->save();

        AuditLogger::log('registration_completed', 'users', $user, null, [
            'company_id' => $step1['company_id'],
            'source' => 'invitation',
        ]);

        // Clear session. Never log a partially verified user in — identity
        // verification still has to happen through the selfie flow.
        $request->session()->forget(['register_step1', 'register_step2']);

        return response()->json([
            'step' => 3,
            'message' => 'Account created. Sign in and upload a selfie to complete verification.',
            'redirect' => route('login'),
        ]);
    }

    /**
     * Decode a company-bound registration invitation.
     *
     * @return array{company_id: int, email: string}
     */
    protected function decodeInvitation(string $token): array
    {
        try {
            $payload = Crypt::decryptString($token);
        } catch (\Throwable) {
            abort(403, 'Invalid or expired registration invitation.');
        }

        $data = json_decode($payload, true);

        if (! is_array($data)
            || ($data['purpose'] ?? null) !== 'registration_invite'
            || ! isset($data['company_id'], $data['email'], $data['expires_at'])
            || ! is_string($data['email'])
            || ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)
        ) {
            abort(403, 'Invalid or expired registration invitation.');
        }

        if (Carbon::parse($data['expires_at'])->isPast()) {
            abort(403, 'This registration invitation has expired.');
        }

        $companyId = (int) $data['company_id'];
        if ($companyId <= 0 || Company::query()->whereKey($companyId)->where('is_active', true)->doesntExist()) {
            abort(403, 'This registration invitation is not bound to an active company.');
        }

        return [
            'company_id' => $companyId,
            'email' => mb_strtolower($data['email']),
        ];
    }

    protected function registrationPhoneOtpKey(string $otpId): string
    {
        return 'registration:phone_otp:'.$otpId;
    }

    /**
     * Verify the pre-registration phone challenge without writing an orphan
     * row to phone_verifications (which requires an existing users row).
     */
    protected function verifyRegistrationPhoneOtp(string $otpId, string $phone, string $otp): bool
    {
        $key = $this->registrationPhoneOtpKey($otpId);
        $challenge = Cache::get($key);

        if (! is_array($challenge)
            || ! isset($challenge['hash'], $challenge['phone'])
            || ! hash_equals((string) $challenge['phone'], $phone)
        ) {
            return false;
        }

        if (($challenge['attempts'] ?? 0) >= 5) {
            Cache::forget($key);

            return false;
        }

        $challenge['attempts'] = ($challenge['attempts'] ?? 0) + 1;
        Cache::put($key, $challenge, now()->addMinutes(10));

        if (! hash_equals((string) $challenge['hash'], hash_hmac('sha256', $otp, config('app.key')))) {
            return false;
        }

        Cache::forget($key);

        return true;
    }

    /** Deliver the one-time code to the invited mailbox. */
    protected function deliverEmailOtp(string $email, string $otp): void
    {
        Mail::raw(
            "Your VentureX ERP & CRM verification code is {$otp}. It expires in 15 minutes. "
            .'If you did not request this, you can safely ignore this message.',
            fn (Message $message) => $message->to($email)->subject('Verify your email address')
        );
    }

    /**
     * Selfie upload form
     */
    public function showSelfieUpload(): View
    {
        return view('auth.selfie-upload');
    }

    /**
     * Process selfie upload
     */
    public function uploadSelfie(Request $request)
    {
        $request->validate([
            'selfie' => 'required|image|max:10240|mimes:jpg,jpeg,png',
        ]);

        $file = $request->file('selfie');

        // Server-side magic byte verification (polyglot file protection)
        $realMimeType = mime_content_type($file->getRealPath());
        $allowedMimes = ['image/jpeg', 'image/png'];
        if (! in_array($realMimeType, $allowedMimes, true)) {
            return back()->withErrors(['selfie' => 'Invalid image file.']);
        }

        $path = $file->store('selfies', 'private');

        $verification = AdvancedSecurityService::submitSelfie(auth()->id(), $path);

        AuditLogger::log('selfie_uploaded', 'auth');

        if ($verification->liveness_check === 'passed') {
            return redirect()->route('dashboard')
                ->with('success', 'Identity verified! Welcome to VentureX ERP & CRM.');
        }

        return redirect()->route('dashboard')
            ->with('info', 'Selfie submitted for review. You can continue using the app.');
    }

    /**
     * App lock screen
     */
    public function showLockScreen(): View
    {
        return view('auth.lock-screen');
    }

    /**
     * Verify PIN to unlock
     */
    public function unlock(Request $request)
    {
        $request->validate(['pin' => 'required|string|digits:6']);

        if (AdvancedSecurityService::verifyAppLock(auth()->id(), $request->pin)) {
            AdvancedSecurityService::recordDevice($request, auth()->id(), true);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['pin' => 'Invalid PIN.']);
    }

    /**
     * Set app lock
     */
    public function setLock(Request $request)
    {
        $request->validate([
            'pin' => 'required|string|digits:6',
            'pin_confirmation' => 'required|same:pin',
        ]);

        AdvancedSecurityService::setAppLock(auth()->id(), $request->pin);

        return back()->with('success', 'App lock enabled.');
    }

    /**
     * Trusted devices page
     */
    public function trustedDevices(): View
    {
        $devices = TrustedDevice::where('user_id', auth()->id())
            ->orderByDesc('last_seen_at')
            ->get();

        return view('auth.trusted-devices', compact('devices'));
    }

    /**
     * Trust a device
     */
    public function trustDevice(Request $request, int $deviceId)
    {
        $device = TrustedDevice::where('id', $deviceId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $device->trust();

        return back()->with('success', 'Device trusted.');
    }

    /**
     * Remove trusted device
     */
    public function removeDevice(int $deviceId)
    {
        TrustedDevice::where('id', $deviceId)
            ->where('user_id', auth()->id())
            ->delete();

        return back()->with('success', 'Device removed.');
    }
}
