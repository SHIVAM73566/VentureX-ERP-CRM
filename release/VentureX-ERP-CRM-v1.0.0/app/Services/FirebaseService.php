<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class FirebaseService
{
    private bool $initialized = false;

    private array $config;

    public function __construct()
    {
        $this->config = config('firebase', []);
    }

    /**
     * Check if Firebase is configured and enabled.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->config['project_id']) && ! empty($this->config['api_key']);
    }

    /**
     * Check if a specific feature is enabled.
     */
    public function isFeatureEnabled(string $feature): bool
    {
        return $this->isConfigured() && ($this->config['features'][$feature] ?? false);
    }

    /**
     * Get Firebase config for JavaScript SDK (safe for client-side).
     * Only returns public config values — no secrets.
     */
    public function getClientConfig(): array
    {
        return [
            'apiKey' => $this->config['api_key'],
            'authDomain' => $this->config['auth_domain'],
            'projectId' => $this->config['project_id'],
            'storageBucket' => $this->config['storage_bucket'],
            'messagingSenderId' => $this->config['messaging_sender_id'],
            'appId' => $this->config['app_id'],
            'measurementId' => $this->config['measurement_id'],
        ];
    }

    /**
     * Get the Firebase project ID.
     */
    public function getProjectId(): string
    {
        return $this->config['project_id'] ?? '';
    }

    /**
     * Initialize Firebase Admin SDK (server-side only).
     * Requires the service account JSON file.
     */
    public function initialize(): bool
    {
        if ($this->initialized) {
            return true;
        }

        if (! $this->isConfigured()) {
            Log::warning('Firebase not configured — skipping initialization');

            return false;
        }

        $serviceAccountPath = $this->config['service_account_path'] ?? '';

        if (! file_exists($serviceAccountPath)) {
            Log::warning('Firebase service account file not found', ['path' => $serviceAccountPath]);

            return false;
        }

        try {
            // Firebase Admin SDK initialization would go here
            // require_once vendor/firebase-php/firebase-php-sdk/autoload.php;
            // $factory = (new Factory)->withServiceAccount($serviceAccountPath);
            // $this->initialized = true;
            return true;
        } catch (\Exception $e) {
            Log::error('Firebase initialization failed', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
