<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class DiagnosticService
{
    protected static array $sensitiveFields = [
        'password', 'password_confirmation', 'secret', 'api_key', 'token',
        'access_token', 'refresh_token', 'authorization', 'credit_card',
        'card_number', 'cvv', 'ssn', 'bank_account', 'routing_number',
        'iban', 'swift_code', 'tax_id', 'private_key', 'ssl_key',
    ];

    public static function collectDiagnostics(?Request $request = null): array
    {
        $diagnostics = [
            'app_version' => config('app.version', '1.0.0'),
            'php_version' => PHP_VERSION,
            'laravel_version' => App::version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'os' => PHP_OS_FAMILY,
            'timestamp' => now()->toIso8601String(),
        ];

        if ($request) {
            $diagnostics['browser'] = self::parseUserAgent($request->userAgent());
            $diagnostics['module'] = self::detectModule($request->route());
            $diagnostics['route'] = $request->route()?->getName();
            $diagnostics['http_method'] = $request->method();
            $diagnostics['url'] = $request->url();
            $diagnostics['ip_hash'] = hash('sha256', $request->ip() ?? 'unknown');
        }

        return $diagnostics;
    }

    public static function sanitizeRequestData(array $data): array
    {
        foreach (self::$sensitiveFields as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = '[REDACTED]';
            }
        }

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = self::sanitizeString($value);
            }
        }

        return $data;
    }

    public static function sanitizeString(string $value): string
    {
        $patterns = [
            '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/' => '[EMAIL]',
            '/\b\d{4}[\s-]?\d{4}[\s-]?\d{4}[\s-]?\d{4}\b/' => '[CARD]',
            '/\b(?:sk|pk|ak|rk)_[a-zA-Z0-9]{20,}\b/' => '[API_KEY]',
            '/\b[A-Za-z0-9]{32,}\b/' => '[POSSIBLE_SECRET]',
        ];

        return preg_replace(array_keys($patterns), array_values($patterns), $value);
    }

    public static function sanitizeStackTrace(string $stackTrace): string
    {
        $sanitized = self::sanitizeString($stackTrace);
        $sanitized = preg_replace('/in [^\s]+:\d+/', 'in [internal]:***', $sanitized);

        return $sanitized;
    }

    protected static function parseUserAgent(?string $userAgent = null): string
    {
        if (! $userAgent) {
            return 'Unknown';
        }

        if (str_contains($userAgent, 'Chrome/')) {
            return 'Chrome';
        }
        if (str_contains($userAgent, 'Firefox/')) {
            return 'Firefox';
        }
        if (str_contains($userAgent, 'Safari/') && ! str_contains($userAgent, 'Chrome/')) {
            return 'Safari';
        }
        if (str_contains($userAgent, 'Edg/')) {
            return 'Edge';
        }
        if (str_contains($userAgent, 'OPR/')) {
            return 'Opera';
        }

        return 'Other';
    }

    protected static function detectModule($route = null): string
    {
        if (! $route) {
            return 'unknown';
        }
        $name = $route->getName() ?? '';

        $modules = [
            'customers' => 'crm', 'leads' => 'crm', 'opportunities' => 'crm',
            'pipeline' => 'crm', 'contacts' => 'crm', 'activities' => 'crm',
            'sales' => 'sales', 'invoices' => 'sales', 'quotations' => 'sales',
            'payments' => 'sales', 'orders' => 'sales',
            'procurement' => 'procurement', 'purchase' => 'procurement', 'rfq' => 'procurement',
            'suppliers' => 'procurement', 'supplier' => 'procurement',
            'inventory' => 'inventory', 'products' => 'inventory',
            'warehouses' => 'inventory', 'stock' => 'inventory',
            'logistics' => 'logistics', 'shipments' => 'logistics',
            'containers' => 'logistics', 'landed-costs' => 'logistics',
            'finance' => 'finance', 'accounts' => 'finance', 'journals' => 'finance',
            'ai' => 'ai', 'copilot' => 'ai', 'assistant' => 'ai',
            'admin' => 'admin', 'settings' => 'admin', 'users' => 'admin',
            'support' => 'support', 'errors' => 'support', 'tickets' => 'support',
        ];

        foreach ($modules as $key => $module) {
            if (str_contains($name, $key)) {
                return $module;
            }
        }

        return 'other';
    }

    public static function getApplicationInfo(): array
    {
        return [
            'app_name' => config('app.name'),
            'app_version' => config('app.version', '1.0.0'),
            'app_env' => config('app.env'),
            'php_version' => PHP_VERSION,
            'laravel_version' => App::version(),
            'database' => config('database.default'),
            'cache' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'os' => PHP_OS_FAMILY,
        ];
    }
}
