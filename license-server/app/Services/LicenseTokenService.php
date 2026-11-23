<?php

namespace App\Services;

use RuntimeException;

/**
 * Ed25519 license token helpers for the separate license/billing server.
 * Private keys are read from the server environment only and never logged.
 */
class LicenseTokenService
{
    public function issueToken(array $payload): string
    {
        $privateKey = $this->privateKey();

        $payload = array_merge([
            'iat' => time(),
            'exp' => time() + max(1, (int) env('LICENSE_TOKEN_TTL_DAYS', 30)) * 86400,
            'jti' => bin2hex(random_bytes(16)),
        ], $payload);

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $signature = sodium_crypto_sign_detached($json, $privateKey);

        return $this->base64UrlEncode($json).'.'.$this->base64UrlEncode($signature);
    }

    public function verifyToken(string $token, string $publicKeyPemOrRaw): bool
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            return false;
        }

        $json = $this->base64UrlDecode($parts[0]);
        $signature = $this->base64UrlDecode($parts[1]);
        if ($json === false || $signature === false || strlen($signature) !== SODIUM_CRYPTO_SIGN_BYTES) {
            return false;
        }

        $publicKey = $this->normalizePublicKey($publicKeyPemOrRaw);
        if ($publicKey === null) {
            return false;
        }

        return sodium_crypto_sign_verify_detached($signature, $json, $publicKey);
    }

    public function payload(string $token): ?array
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            return null;
        }

        $json = $this->base64UrlDecode($parts[0]);

        return $json === false ? null : (json_decode($json, true) ?: null);
    }

    protected function privateKey(): string
    {
        $configured = (string) env('LICENSE_ED25519_PRIVATE_KEY', '');
        $path = (string) env('LICENSE_ED25519_PRIVATE_KEY_PATH', '');

        if ($path !== '' && is_readable($path)) {
            $configured = (string) file_get_contents($path);
        }
        if ($configured === '') {
            throw new RuntimeException('License signing key is not configured on this server.');
        }

        if (str_contains($configured, 'BEGIN PRIVATE KEY')) {
            $keyData = preg_replace('/-----[^-]+-----/', '', $configured);
            $raw = base64_decode(preg_replace('/\s+/', '', (string) $keyData) ?: '', true);
            if ($raw === false || strlen($raw) < 32) {
                throw new RuntimeException('Invalid Ed25519 private key.');
            }

            return sodium_crypto_sign_secretkey(sodium_crypto_sign_seed_keypair(substr($raw, -32)));
        }

        $raw = base64_decode($configured, true);
        if ($raw === false) {
            throw new RuntimeException('Invalid Ed25519 private key.');
        }
        if (strlen($raw) === 32) {
            return sodium_crypto_sign_secretkey(sodium_crypto_sign_seed_keypair($raw));
        }
        if (strlen($raw) === SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) {
            return $raw;
        }

        throw new RuntimeException('Invalid Ed25519 private key length.');
    }

    protected function normalizePublicKey(string $key): ?string
    {
        if (str_contains($key, 'BEGIN PUBLIC KEY')) {
            $keyData = preg_replace('/-----[^-]+-----/', '', $key);
            $raw = base64_decode(preg_replace('/\s+/', '', (string) $keyData) ?: '', true);
        } else {
            $raw = base64_decode($key, true);
        }

        if ($raw === false || strlen($raw) < SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            return null;
        }

        // Ed25519 SPKI PEM wraps the 32-byte key in a DER header.
        return substr($raw, -SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES);
    }

    protected function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    protected function base64UrlDecode(string $value): string|false
    {
        return base64_decode(strtr($value, '-_', '+/'), true);
    }
}
