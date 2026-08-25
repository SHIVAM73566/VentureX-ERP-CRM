<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TrustedDevice;
use App\Models\User;
use App\Services\AdvancedSecurityService;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     * Step 1: Basic info + email verification
     */
    public function registerStep1(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'string', 'min:10', 'confirmed', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'regex:/[^A-Za-z0-9]/'],
        ]);

        // Store in session for multi-step
        $request->session()->put('register_step1', $validated);

        // Generate email OTP
        $otp = AdvancedSecurityService::generateEmailOtp($validated['email']);

        // In production, send email with OTP
        // Mail::to($validated['email'])->send(new OtpMail($otp));

        return response()->json([
            'step' => 1,
            'message' => 'Verification code sent to '.$validated['email'],
        ]);
    }

    /**
     * Step 2: Phone verification
     */
    public function registerStep2(Request $request)
    {
        $step1 = $request->session()->get('register_step1');
        if (! $step1) {
            return redirect()->route('register');
        }

        $otp = $request->input('otp');
        if (! AdvancedSecurityService::verifyEmailOtp($step1['email'], $otp)) {
            return back()->withErrors(['otp' => 'Invalid verification code']);
        }

        $validated = $request->validate([
            'phone' => 'required|string|max:20',
        ]);

        $request->session()->put('register_step2', $validated);

        // Send phone OTP
        AdvancedSecurityService::sendPhoneOtp(
            auth()->id() ?? 0,
            $validated['phone'],
            $request
        );

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
        if (! $step2) {
            return redirect()->route('register');
        }

        $step1 = $request->session()->get('register_step1');

        // Verify phone OTP — always required, no bypass
        $phoneOtp = $request->input('phone_otp');
        if (! AdvancedSecurityService::verifyPhoneOtp($step2['email'] ?? $step1['email'], $phoneOtp)) {
            return back()->withErrors(['phone_otp' => 'Invalid phone code']);
        }

        // Complete registration

        $user = User::create([
            'name' => $step1['name'],
            'email' => $step1['email'],
            'password' => $step1['password'],
            'phone' => $step2['phone'],
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Clear session
        $request->session()->forget(['register_step1', 'register_step2']);

        Auth::login($user);

        return response()->json([
            'step' => 3,
            'message' => 'Account created. Please upload a selfie to complete verification.',
            'redirect' => route('selfie.upload'),
        ]);
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
