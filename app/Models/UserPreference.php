<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'key', 'value'])]
class UserPreference extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get a preference value for a user, with an optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $user = auth()->user();
        if (! $user) {
            return $default;
        }

        $pref = self::where('user_id', $user->id)->where('key', $key)->first();
        return $pref?->value ?? $default;
    }

    /**
     * Set a preference value for a user.
     */
    public static function set(string $key, mixed $value): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        self::updateOrCreate(
            ['user_id' => $user->id, 'key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Scope preferences by key.
     */
    public function scopeForKey($query, string $key)
    {
        return $query->where('key', $key);
    }
}
