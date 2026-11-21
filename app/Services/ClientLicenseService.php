<?php

namespace App\Services;

use App\Models\LicenseToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClientLicenseService
{
    /**
     * Verify and store a license token (format: base64url(payloadJson).base64url(ed25519SigOverPayloadJson))
     * Public key from config('license.public_key') (PEM or raw base64). Never logs token.
     */
    public function applyToken(int $companyId, string $token): array
    {
        $publicKey = config('license.public_key');
        if (! $publicKey) {
            return ['ok' => false, 'message' => 'License public key is not configured on this server.'];
        }

        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            return ['ok' => false, 'message' => 'Invalid token format.'];
        }

        [$payloadB64, $sigB64] = $parts;
        $payloadJson = $this->base64UrlDecode($payloadB64);
        $sig = $this->base64UrlDecode($sigB64);

        if ($payloadJson === false || $sig === false || $payloadJson === '' || strlen($sig) === 0) {
            return ['ok' => false, 'message' => 'Invalid token encoding.'];
        }

        $pubKeyBin = $this->normalizePublicKey($publicKey);
        if ($pubKeyBin === null) {
            return ['ok' => false, 'message' => 'License public key is not in a supported format.'];
        }

        if (! function_exists('sodium_crypto_sign_verify_detached')) {
            return ['ok' => false, 'message' => 'libsodium (sodium) is required for Ed25519 verification.'];
        }

        try {
            $verified = sodium_crypto_sign_verify_detached($sig, $payloadJson, $pubKeyBin);
        } catch (\Throwable $e) {
            Log::warning('License token verification error', ['error' => $e->getMessage()]);
            return ['ok' => false, 'message' => 'Token verification failed.'];
        }

        if (! $verified) {
            return ['ok' => false, 'message' => 'Token signature is invalid.'];
        }

        $payload = json_decode($payloadJson, true);
        if (! is_array($payload)) {
            return ['ok' => false, 'message' => 'Token payload is not valid JSON.'];
        }

        $exp = isset($payload['exp']) ? (int) $payload['exp'] : null;
        $expiresAt = $exp ? now()->setTimestamp($exp) : null;
        $graceDays = (int) config('license.offline_grace_days', 14);
        $graceExpiresAt = $expiresAt ? $expiresAt->copy()->addDays($graceDays) : now()->addDays($graceDays);

        LicenseToken::updateOrCreate(
            ['company_id' => $companyId],
            [
                'token' => $token,
                'payload' => $payload,
                'verified_at' => now(),
                'expires_at' => $expiresAt,
                'grace_expires_at' => $graceExpiresAt,
            ]
        );

        return ['ok' => true, 'message' => 'License token applied successfully.'];
    }

    /**
     * Attempt to sync from license server. Returns array with ok/message.
     */
    public function syncFromServer(int $companyId, ?string $installationId = null, ?string $customerKey = null): array
    {
        $url = config('license.server_url');
        if (! $url) {
            return ['ok' => false, 'message' => 'LICENSE_SERVER_URL is not configured.'];
        }

        try {
            $response = Http::timeout(15)->post(rtrim($url, '/') . '/api/license/verify', [
                'installation_id' => $installationId,
                'customer_key' => $customerKey,
            ]);
        } catch (\Throwable $e) {
            Log::warning('License server sync failed', ['error' => $e->getMessage()]);
            return ['ok' => false, 'message' => 'Could not reach license server.'];
        }

        if (! $response->ok()) {
            return ['ok' => false, 'message' => 'License server returned an error (' . $response->status() . ').'];
        }

        $data = $response->json();
        $token = $data['token'] ?? null;
        if (! is_string($token) || $token === '') {
            return ['ok' => false, 'message' => 'License server response did not include a token.'];
        }

        return $this->applyToken($companyId, $token);
    }

    private function base64UrlDecode(string $data): string|false
    {
        $remainder = strlen($data) % 4;
        if ($remainder > 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        $decoded = base64_decode(strtr($data, '-_', '+/'), true);
        return $decoded;
    }

    /**
     * Accept PEM or raw base64 (32 bytes for Ed25519 public key).
     */
    private function normalizePublicKey(string $key): ?string
    {
        $key = trim($key);
        if ($key === '') {
            return null;
        }
        // PEM format
        if (str_contains($key, 'BEGIN PUBLIC KEY')) {
            $pem = $key;
            $keyData = preg_replace('/-----[^-]+-----/', '', $pem);
            $keyData = preg_replace('/\s+/', '', (string) $keyData);
            $raw = base64_decode($keyData, true);
            return $raw !== false && strlen($raw) > 0 ? $raw : null;
        }
        // Try raw base64
        $raw = base64_decode($key, true);
        if ($raw !== false && strlen($raw) >= 32) {
            // If it's longer (SPKI wrapped in some cases), take last 32? Or use as-is if sodium expects raw 32-byte key in some libs; better try as-is.
            return $raw;
        }
        return null;
    }
}
