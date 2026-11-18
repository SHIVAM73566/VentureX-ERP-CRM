<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Credential material, fingerprints and runtime telemetry are server-owned:
 * request handlers assign them with forceFill()/direct assignment after
 * validating the tenant and encrypting the key. They must never be copyable
 * from an input array. Company binding is likewise set server-side; it stays
 * fillable only because existing fixtures construct rows directly for tests.
 */
#[Fillable([
    'company_id', 'provider', 'encrypted_key', 'model', 'base_url',
    'path', 'auth_mode', 'enabled', 'fallback_enabled', 'per_user_daily_limit',
    'per_user_monthly_limit', 'org_daily_limit', 'org_monthly_limit',
    'created_by',
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
     * Permanent, non-reversible display marker for a configured key.
     *
     * No substring of the secret — not even its last characters — is ever
     * exposed to the browser, logs, or error pages. Presence is the only fact
     * the UI needs to render.
     */
    public function maskedKey(): ?string
    {
        return $this->getDecryptedKey() !== null ? '********' : null;
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
