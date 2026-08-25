<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypto;

/**
 * Data Encryption Service for VentureX ERP & CRM
 *
 * Encrypts sensitive data at rest and in transit.
 * Protects: API keys, passwords, tokens, personal data
 */
class EncryptionService
{
    /**
     * Encrypt sensitive data before storage
     */
    public static function encrypt(string $data): string
    {
        return Crypto::encrypt($data);
    }

    /**
     * Decrypt sensitive data
     */
    public static function decrypt(string $encryptedData): string
    {
        return Crypto::decrypt($encryptedData);
    }

    /**
     * Hash a value (one-way, for comparisons)
     */
    public static function hash(string $value): string
    {
        return hash_hmac('sha256', $value, config('app.key'));
    }

    /**
     * Verify a hash
     */
    public static function verifyHash(string $value, string $hash): bool
    {
        return hash_equals($hash, self::hash($value));
    }

    /**
     * Encrypt API keys before storing in database
     */
    public static function encryptApiKey(string $apiKey): string
    {
        return self::encrypt($apiKey);
    }

    /**
     * Decrypt API keys for use
     */
    public static function decryptApiKey(string $encryptedKey): string
    {
        return self::decrypt($encryptedKey);
    }

    /**
     * Mask sensitive data for display (e.g., API keys)
     */
    public static function mask(string $data, int $visibleChars = 4): string
    {
        $length = strlen($data);
        if ($length <= $visibleChars) {
            return str_repeat('*', $length);
        }

        $masked = str_repeat('*', $length - $visibleChars);

        return $masked.substr($data, -$visibleChars);
    }

    /**
     * Generate a secure random token
     */
    public static function generateToken(int $length = 64): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Generate a secure API key
     */
    public static function generateApiKey(): string
    {
        $prefix = 'twiterp_';
        $key = bin2hex(random_bytes(32));

        return $prefix.$key;
    }

    /**
     * Encrypt sensitive fields in an array
     */
    public static function encryptFields(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            if (isset($data[$field]) && ! empty($data[$field])) {
                $data[$field] = self::encrypt($data[$field]);
            }
        }

        return $data;
    }

    /**
     * Decrypt sensitive fields in an array
     */
    public static function decryptFields(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            if (isset($data[$field]) && ! empty($data[$field])) {
                try {
                    $data[$field] = self::decrypt($data[$field]);
                } catch (\Exception $e) {
                    // If decryption fails, the data might not be encrypted
                    // Leave it as is
                }
            }
        }

        return $data;
    }
}
