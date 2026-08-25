<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Progressive login throttling: rate limit -> temporary lockout -> security alert.
 */
class LoginThrottle
{
    public function __construct(protected SecurityEventService $events) {}

    /** Remaining allowed attempts for the key, or 0 when blocked. */
    public function remaining(string $key): int
    {
        $max = (int) config('security.login.max_attempts', 5);

        return max(0, $max - $this->recent($key));
    }

    public function blocked(string $key): bool
    {
        if ($this->remaining($key) <= 0) {
            return true;
        }

        return Cache::get($this->lockoutKey($key)) !== null;
    }

    /** Current delay (seconds) for a locked-out key. */
    public function delaySeconds(string $key): int
    {
        $until = Cache::get($this->lockoutKey($key));

        return $until ? max(0, (int) $until - now()->getTimestamp()) : 0;
    }

    public function hit(string $key): void
    {
        $recent = $this->recent($key) + 1;
        $window = (int) config('security.login.decay_minutes', 5);
        Cache::put($this->hitsKey($key), $recent, now()->addMinutes($window));

        $lockThreshold = (int) config('security.login.lockout_threshold', 8);
        if ($recent >= $lockThreshold) {
            $lockMinutes = (int) config('security.login.lockout_minutes', 15);
            Cache::put($this->lockoutKey($key), now()->addMinutes($lockMinutes)->getTimestamp(), now()->addMinutes($lockMinutes));
            $this->events::record('auth', 'account_locked', "Account temporarily locked after {$recent} failed attempts", 'high', null, null, ['failures' => $recent]);
        }
    }

    public function clear(string $key): void
    {
        Cache::forget($this->hitsKey($key));
        Cache::forget($this->lockoutKey($key));
    }

    /** Attempts counted inside the decay window. */
    protected function recent(string $key): int
    {
        return (int) Cache::get($this->hitsKey($key), 0);
    }

    protected function hitsKey(string $key): string
    {
        return 'login:hits:'.sha1($key);
    }

    protected function lockoutKey(string $key): string
    {
        return 'login:lockout:'.sha1($key);
    }

    /** Attempt key combining per-IP and per-email identity. Email is hashed to avoid storing PII in cache. */
    public static function attemptKey(string $email): string
    {
        return hash('sha256', mb_strtolower($email)).'|'.(string) request()->ip();
    }
}
