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

        // A valid signature is not enough: the token must be for THIS
        // tenant/installation and must have a bounded lifetime by default.
        $bindingError = $this->validateBinding($companyId, $payload);
        if ($bindingError !== null) {
            return ['ok' => false, 'message' => $bindingError];
        }

        $exp = isset($payload['exp']) && is_numeric($payload['exp']) ? (int) $payload['exp'] : null;
        if ($exp === null && ! (bool) config('license.allow_unbounded_tokens', false)) {
            return ['ok' => false, 'message' => 'License token must include a valid expiry (exp).'];
        }
        if ($exp !== null && $exp <= now()->getTimestamp()) {
            return ['ok' => false, 'message' => 'License token has expired.'];
        }

        $expiresAt = $exp !== null ? now()->setTimestamp($exp) : null;
        $graceDays = max(1, (int) config('license.offline_grace_days', 14));
        $graceExpiresAt = $expiresAt
            ? $expiresAt->copy()->addDays($graceDays)
            : now()->addDays($graceDays);

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
                'installation_id' => $installationId ?? config('license.installation_id'),
                'customer_key' => $customerKey ?? config('license.customer_key'),
                'company_id' => $companyId,
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
     * Reject tokens issued for a different tenant/installation and replayed
     * token IDs. Only claims that are actually present are checked; operators
     * can bind installations with LICENSE_INSTALLATION_ID/CUSTOMER_KEY.
     */
    private function validateBinding(int $companyId, array $payload): ?string
    {
        $claims = [
            'company_id' => config('license.installation_id'),
            'installation_id' => config('license.installation_id'),
            'customer_key' => config('license.customer_key'),
        ];

        if (isset($payload['company_id']) && (int) $payload['company_id'] !== $companyId) {
            return 'License token was issued for a different company.';
        }

        foreach (['installation_id', 'customer_key'] as $claim) {
            $expected = $claims[$claim];
            if ($expected !== null && $expected !== '' && isset($payload[$claim])
                && (string) $payload[$claim] !== (string) $expected) {
                return 'License token installation binding does not match this server.';
            }
        }

        $jti = isset($payload['jti']) ? (string) $payload['jti'] : '';
        if ($jti !== '') {
            $replay = LicenseToken::query()
                ->where('company_id', '!=', $companyId)
                ->where('payload->jti', $jti)
                ->exists();
            if ($replay) {
                return 'License token ID has already been used by another company.';
            }
        }

        return null;
    }

    /**
     * Ed25519 verification needs exactly 32 raw bytes. SPKI PEM decodes to a
     * DER wrapper whose final 32 bytes are the public key.
     */
    private function normalizePublicKey(string $key): ?string
    {
        $key = trim($key);
        if ($key === '') {
            return null;
        }

        if (str_contains($key, 'BEGIN PUBLIC KEY')) {
            $keyData = preg_replace('/-----[^-]+-----/', '', $key);
            $keyData = preg_replace('/\s+/', '', (string) $keyData);
            $raw = base64_decode((string) $keyData, true);
        } else {
            $raw = base64_decode($key, true);
        }

        if ($raw === false || strlen($raw) < SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            return null;
        }

        // Raw 32-byte keys stay as-is; SPKI/DER wrappers resolve to the tail.
        $publicKey = substr($raw, -SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES);

        return strlen($publicKey) === SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES ? $publicKey : null;
    }
}
