<?php

namespace App\Services;

class LicenseTokenService
{
    /**
     * Sign a license payload with Ed25519 (libsodium).
     * TODO: load private key from env/secret manager, sign canonical JSON, return token string.
     */
    public function issueToken(array $payload): string
    {
        // Example (pseudocode):
        // $privateKey = ...; // load securely
        // $message = json_encode($payload, JSON_UNESCAPED_SLASHES);
        // $sig = sodium_crypto_sign_detached($message, $privateKey);
        // return base64url_encode($message) . '.' . base64url_encode($sig);
        throw new \RuntimeException('Not implemented: Ed25519 token signing.');
    }

    /**
     * Verify token signature with public key (for testing/server-side checks).
     */
    public function verifyToken(string $token, string $publicKeyPemOrRaw): bool
    {
        throw new \RuntimeException('Not implemented: Ed25519 token verification.');
    }
}
