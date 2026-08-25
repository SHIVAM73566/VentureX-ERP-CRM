<?php

namespace App\Services;

use App\Models\PasswordHistory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordPolicyService
{
    public static function validate(?string $password, ?string $confirmation = null): ?string
    {
        if ($password === null || $password === '') {
            return null;
        }

        $min = (int) config('security.password.min_length', 10);
        if (mb_strlen($password) < $min) {
            return "Password must be at least {$min} characters.";
        }
        if (config('security.password.require_upper') && ! preg_match('/[A-Z]/', $password)) {
            return 'Password must contain an uppercase letter.';
        }
        if (config('security.password.require_lower') && ! preg_match('/[a-z]/', $password)) {
            return 'Password must contain a lowercase letter.';
        }
        if (config('security.password.require_digit') && ! preg_match('/[0-9]/', $password)) {
            return 'Password must contain a number.';
        }
        if (config('security.password.require_special') && ! preg_match('/[^A-Za-z0-9]/', $password)) {
            return 'Password must contain a special character.';
        }
        if ($confirmation !== null && $password !== $confirmation) {
            return 'Password confirmation does not match.';
        }

        return null;
    }

    /** True if the password was used within the last N hashes. */
    public static function reused(User $user, string $password): bool
    {
        $history = (int) config('security.password.history', 5);
        if ($history <= 0) {
            return false;
        }

        return PasswordHistory::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit($history)
            ->get()
            ->contains(fn ($h) => Hash::check($password, $h->password));
    }

    /** Record a password hash and trim history to the configured depth. */
    public static function remember(User $user, string $hashed): void
    {
        PasswordHistory::create(['user_id' => $user->id, 'password' => $hashed]);

        $history = (int) config('security.password.history', 5);
        $ids = PasswordHistory::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->limit($history)
            ->pluck('id');

        PasswordHistory::query()->where('user_id', $user->id)->whereNotIn('id', $ids)->delete();
    }
}
