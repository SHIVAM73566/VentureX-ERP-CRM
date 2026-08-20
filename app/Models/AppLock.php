<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppLock extends Model
{
    protected $fillable = [
        'user_id', 'pin_hash', 'is_active', 'failed_attempts',
        'locked_until', 'last_unlocked_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'failed_attempts' => 'integer',
            'locked_until' => 'datetime',
            'last_unlocked_at' => 'datetime',
        ];
    }

    /**
     * Set PIN — hashed, never stored plaintext
     */
    public static function setPin(string $userId, string $pin): self
    {
        $hash = password_hash($pin, PASSWORD_ARGON2ID);

        return static::updateOrCreate(
            ['user_id' => $userId],
            ['pin_hash' => $hash, 'is_active' => true]
        );
    }

    /**
     * Verify PIN
     */
    public function verifyPin(string $pin): bool
    {
        // Check lockout
        if ($this->locked_until && $this->locked_until->isFuture()) {
            return false;
        }

        if (password_verify($pin, $this->pin_hash)) {
            $this->update([
                'failed_attempts' => 0,
                'locked_until' => null,
                'last_unlocked_at' => now(),
            ]);

            return true;
        }

        $this->increment('failed_attempts');

        // Lock after 5 failed attempts
        if ($this->failed_attempts >= 5) {
            $this->update(['locked_until' => now()->addMinutes(15)]);
        }

        return false;
    }

    /**
     * Lock the app immediately
     */
    public function lock(): void
    {
        $this->update([
            'is_active' => true,
            'last_unlocked_at' => null,
        ]);
    }

    /**
     * Check if app is locked
     */
    public function isLocked(): bool
    {
        return $this->is_active && $this->last_unlocked_at === null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
