<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        // Rate limit: 3 attempts per email per 60 minutes
        $throttleKey = Str::lower($validated['email']).'|forgot_password';
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "Too many attempts. Please try again in {$seconds} seconds.",
            ]);
        }
        RateLimiter::hit($throttleKey, 60 * 60);

        // Always return the same response to prevent email enumeration
        $status = Password::sendResetLink(
            $request->only('email'),
            function ($user, $token) {
                // Token is automatically hashed and stored by Laravel's PasswordBroker
            }
        );

        return back()->with('status', __($status));
    }
}
