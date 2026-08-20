<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id', 'name', 'key_hash', 'encrypted_key', 'provider',
    'is_active', 'expires_at', 'metadata', 'created_by',
])]
class ApiKeyVault extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
            'last_used_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * Store an API key encrypted — never store plaintext
     */
    public static function storeKey(string $name, string $key, ?string $provider = null, ?int $companyId = null): self
    {
        $encrypted = EncryptionService::encryptApiKey($key);
        $hash = hash_hmac('sha256', $key, config('app.key'));

        return static::updateOrCreate(
            ['name' => $name, 'company_id' => $companyId ?? company_id()],
            [
                'key_hash' => $hash,
                'encrypted_key' => $encrypted,
                'provider' => $provider,
                'created_by' => auth()->id(),
            ]
        );
    }

    /**
     * Retrieve decrypted key — only used in server-side code
     */
    public static function getKey(string $name, ?int $companyId = null): ?string
    {
        $vault = static::where('name', $name)
            ->where('company_id', $companyId ?? company_id())
            ->where('is_active', true)
            ->first();

        if (! $vault) {
            return null;
        }

        // Check expiry
        if ($vault->expires_at && $vault->expires_at->isPast()) {
            return null;
        }

        // Update last used
        $vault->update(['last_used_at' => now()]);

        return EncryptionService::decryptApiKey($vault->encrypted_key);
    }

    /**
     * Mask key for display (e.g., "nvapi-****83236")
     */
    public function maskedKey(): string
    {
        $key = EncryptionService::decryptApiKey($this->encrypted_key);

        return EncryptionService::mask($key, 6);
    }

    /**
     * Check if key is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
