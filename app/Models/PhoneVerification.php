<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhoneVerification extends Model
{
    protected $fillable = [
        'user_id', 'phone', 'otp_code', 'otp_hash', 'status',
        'attempts', 'expires_at', 'verified_at', 'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    /**
     * Generate a 6-digit OTP and store hashed
     */
    public static function generate(string $userId, string $phone, ?string $ip = null): self
    {
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $hash = hash_hmac('sha256', $otp, config('app.key'));

        return static::create([
            'user_id' => $userId,
            'phone' => $phone,
            'otp_code' => substr($otp, 0, 2).'****'.substr($otp, -2), // Store masked
            'otp_hash' => $hash,
            'status' => 'pending',
            'expires_at' => now()->addMinutes(5),
            'ip_address' => $ip,
        ]);
    }

    /**
     * Verify OTP
     */
    public function verify(string $otp): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }
        if ($this->expires_at->isPast()) {
            $this->update(['status' => 'expired']);

            return false;
        }
        if ($this->attempts >= 5) {
            $this->update(['status' => 'failed']);

            return false;
        }

        $this->increment('attempts');

        $hash = hash_hmac('sha256', $otp, config('app.key'));
        if (hash_equals($this->otp_hash, $hash)) {
            $this->update(['status' => 'verified', 'verified_at' => now()]);

            return true;
        }

        return false;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
