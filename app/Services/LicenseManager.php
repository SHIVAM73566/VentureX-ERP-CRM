<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypto;

/**
 * License Key Manager for VentureX ERP & CRM
 *
 * Protects the product from unauthorized use.
 * Reference: VentureX-ERP-2026-01483863236
 */
class LicenseManager
{
    /**
     * Generate a license key for a domain
     */
    public static function generateKey(string $domain, string $tier = 'professional'): string
    {
        $payload = json_encode([
            'domain' => $domain,
            'tier' => $tier,
            'issued' => now()->timestamp,
            'reference' => 'VentureX-ERP-2026-01483863236',
            'version' => '1.0.0',
        ]);

        $encrypted = Crypto::encrypt($payload);
        $hash = hash_hmac('sha256', $encrypted, config('app.key'));

        return strtoupper(substr($hash, 0, 4).'-'.
            substr($hash, 4, 4).'-'.
            substr($hash, 8, 4).'-'.
            substr($hash, 12, 4).'-'.
            substr($hash, 16, 12));
    }

    /**
     * Validate a license key
     */
    public static function validateKey(string $key): array
    {
        try {
            // Check if key format is valid
            if (! preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}$/', $key)) {
                return ['valid' => false, 'error' => 'Invalid key format'];
            }

            // Check cache for revoked keys
            $cacheKey = 'license_revoked_'.$key;
            if (Cache::has($cacheKey)) {
                return ['valid' => false, 'error' => 'License has been revoked'];
            }

            // In production, this would decode and verify the key
            // For now, check against stored valid keys
            $storedKeys = Cache::get('valid_license_keys', []);

            if (in_array($key, $storedKeys)) {
                return [
                    'valid' => true,
                    'tier' => 'professional',
                    'domain' => request()->getHost(),
                ];
            }

            // Check configured master key from environment
            if ($key === config('app.license_key')) {
                return [
                    'valid' => true,
                    'tier' => 'enterprise',
                    'domain' => '*',
                ];
            }

            return ['valid' => false, 'error' => 'Invalid license key'];

        } catch (\Exception $e) {
            return ['valid' => false, 'error' => 'License validation failed'];
        }
    }

    /**
     * Get current domain's license status
     */
    public static function checkLicense(): array
    {
        $key = config('app.license_key', '');

        if (empty($key)) {
            return [
                'licensed' => false,
                'message' => 'No license key configured. Add LICENSE_KEY to your .env file.',
            ];
        }

        $result = self::validateKey($key);

        if ($result['valid']) {
            return [
                'licensed' => true,
                'tier' => $result['tier'] ?? 'standard',
                'reference' => 'VentureX-ERP-2026-01483863236',
            ];
        }

        return [
            'licensed' => false,
            'message' => $result['error'] ?? 'Invalid license',
        ];
    }

    /**
     * Revoke a license key
     */
    public static function revokeKey(string $key): bool
    {
        $cacheKey = 'license_revoked_'.$key;
        Cache::put($cacheKey, true, now()->addYears(10));

        return true;
    }

    /**
     * Get license info for display
     */
    public static function getLicenseInfo(): array
    {
        $status = self::checkLicense();

        return [
            'reference' => 'VentureX-ERP-2026-01483863236',
            'version' => '1.0.0',
            'licensed' => $status['licensed'],
            'tier' => $status['tier'] ?? 'none',
            'message' => $status['message'] ?? '',
            'purchase_url' => config('app.purchase_url', 'https://codecanyon.net/item/VentureX-ERP/YOUR_ITEM_ID'),
        ];
    }
}
