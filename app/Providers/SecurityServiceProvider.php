<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class SecurityServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $key = 'login:'.mb_strtolower((string) $request->string('email')).'|'.$request->ip();

            return Limit::perMinutes(5, 5)->by($key);
        });

        RateLimiter::for('mfa', function (Request $request) {
            return Limit::perMinutes(5, 5)->by('mfa:'.$request->user()?->id ?? $request->ip());
        });

        RateLimiter::for('ai', function (Request $request) {
            $uid = $request->user()?->id ?? $request->ip();

            return [
                Limit::perMinute((int) config('security.ai.per_minute', 10))->by('ai:min:'.$uid),
                Limit::perHour((int) config('security.ai.per_hour', 60))->by('ai:hr:'.$uid),
                Limit::perDay((int) config('security.ai.per_day', 200))->by('ai:day:'.$uid),
            ];
        });

        RateLimiter::for('export', function (Request $request) {
            $uid = $request->user()?->id ?? $request->ip();

            return Limit::perDay((int) config('security.export.max_per_day', 20))->by('export:'.$uid);
        });

        RateLimiter::for('api', function (Request $request) {
            $uid = $request->user()?->id ?? $request->ip();

            return Limit::perMinute(60)->by('api:'.$uid);
        });

        RateLimiter::for('search', function (Request $request) {
            $uid = $request->user()?->id ?? $request->ip();

            return Limit::perMinute(30)->by('search:'.$uid);
        });

        RateLimiter::for('password_reset', function (Request $request) {
            return Limit::perMinutes(60, 3)->by('reset:'.mb_strtolower((string) $request->string('email')).'|'.$request->ip());
        });

        RateLimiter::for('upload', function (Request $request) {
            $uid = $request->user()?->id ?? $request->ip();

            return Limit::perHour(20)->by('upload:'.$uid);
        });
    }

    public function register(): void
    {
        //
    }
}
