<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id', 'provider', 'encrypted_key', 'key_hash', 'model', 'base_url',
    'path', 'auth_mode', 'enabled', 'fallback_enabled', 'per_user_daily_limit',
    'per_user_monthly_limit', 'org_daily_limit', 'org_monthly_limit',
    'request_count', 'error_count', 'last_success_at', 'last_error_at',
    'last_error', 'created_by',
])]
class AiProvider extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'fallback_enabled' => 'boolean',
            'per_user_daily_limit' => 'integer',
            'per_user_monthly_limit' => 'integer',
            'org_daily_limit' => 'integer',
            'org_monthly_limit' => 'integer',
            'request_count' => 'integer',
            'error_count' => 'integer',
        ];
    }

    /**
     * Decrypt the stored API key. Plaintext keys are only ever available on the
     * server at request time — never to any client.
     */
    public function getDecryptedKey(): ?string
    {
        if (empty($this->encrypted_key)) {
            return null;
        }

        try {
            return EncryptionService::decrypt($this->encrypted_key);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Masked key for display (e.g. "****abcd"). The full key is never shown.
     */
    public function maskedKey(): ?string
    {
        $key = $this->getDecryptedKey();

        return $key !== null ? EncryptionService::mask($key, 4) : null;
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
