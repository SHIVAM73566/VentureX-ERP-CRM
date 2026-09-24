<?php

namespace App\Services\Ai;

use App\Services\EncryptionService;

/**
 * Handles the cryptographic lifecycle of AI provider API keys.
 *
 * Secrets are symmetric-encrypted at rest (Laravel Crypto) and a keyed HMAC
 * hash is stored alongside so rotation can cheaply detect key reuse without
 * decrypting. Plaintext keys are only ever materialised server-side inside a
 * single request and are never persisted to logs or exposed to any client.
 */
class AiCredentialService
{
    public function encryptKey(string $apiKey): string
    {
        return EncryptionService::encryptApiKey($apiKey);
    }

    public function decryptKey(string $encrypted): ?string
    {
        try {
            return EncryptionService::decryptApiKey($encrypted);
        } catch (\Throwable) {
            return null;
        }
    }

    public function hashKey(string $apiKey): string
    {
        return EncryptionService::hash('ai.provider: '.$apiKey);
    }

    public function keyMasked(string $apiKey): string
    {
        return mask_secret($apiKey, 4);
    }
}