<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class TrustedDevice extends Model
{
    protected $fillable = [
        'user_id', 'device_fingerprint', 'device_name', 'ip_address',
        'last_ip', 'is_trusted', 'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'is_trusted' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * Generate device fingerprint from request
     */
    public static function fingerprint(Request $request): string
    {
        $ua = $request->userAgent();
        $accept = $request->header('Accept-Language', '');
        $encoding = $request->header('Accept-Encoding', '');

        return hash('sha256', $ua.'|'.$accept.'|'.$encoding);
    }

    /**
     * Check if this device is trusted
     */
    public static function isTrusted(Request $request, int $userId): bool
    {
        $fp = self::fingerprint($request);

        return static::where('user_id', $userId)
            ->where('device_fingerprint', $fp)
            ->where('is_trusted', true)
            ->exists();
    }

    /**
     * Record device activity
     */
    public static function record(Request $request, int $userId): self
    {
        $fp = self::fingerprint($request);

        return static::updateOrCreate(
            ['user_id' => $userId, 'device_fingerprint' => $fp],
            [
                'device_name' => self::parseDeviceName($request),
                'ip_address' => $request->ip(),
                'last_ip' => $request->ip(),
                'last_seen_at' => now(),
            ]
        );
    }

    /**
     * Trust this device
     */
    public function trust(): void
    {
        $this->update(['is_trusted' => true]);
    }

    /**
     * Parse human-readable device name from user agent
     */
    protected static function parseDeviceName(Request $request): string
    {
        $ua = $request->userAgent();

        $browser = 'Unknown Browser';
        if (str_contains($ua, 'Chrome')) {
            $browser = 'Chrome';
        } elseif (str_contains($ua, 'Firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($ua, 'Safari')) {
            $browser = 'Safari';
        } elseif (str_contains($ua, 'Edge')) {
            $browser = 'Edge';
        }

        $os = 'Unknown OS';
        if (str_contains($ua, 'Windows')) {
            $os = 'Windows';
        } elseif (str_contains($ua, 'Mac OS X')) {
            $os = 'Mac';
        } elseif (str_contains($ua, 'Linux')) {
            $os = 'Linux';
        } elseif (str_contains($ua, 'Android')) {
            $os = 'Android';
        } elseif (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) {
            $os = 'iOS';
        }

        return "$browser on $os";
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
