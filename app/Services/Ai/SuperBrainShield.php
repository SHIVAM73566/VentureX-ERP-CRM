<?php

namespace App\Services\Ai;

use App\Models\AiProvider;
use App\Services\CompanyContext;
use App\Services\SecurityEventService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * AERO X Super Intelligence Brain — AI security shield.
 *
 * Four layers, applied around every AI interaction:
 *   1. Encryption      — provider keys are encrypted at rest by AiCredentialService.
 *   2. Sanitisation    — prompts are stripped of secrets and PII before they leave
 *                        the server, and prompt-injection instructions are neutralised.
 *   3. Threat detection— SQLi / traversal / scanner patterns are blocked and abnormal
 *                        request spikes are rate limited per user and per company.
 *   4. Audit telemetry — every block and redaction is written to SecurityEventService
 *                        (the Security & Audit Logs module). Prompt text is never logged.
 */
class SuperBrainShield
{
    /**
     * Secrets and PII that must never be sent to an external model.
     * Order matters: API keys first, then payment/identity data.
     */
    protected const REDACTION_RULES = [
        'api_key' => '/(nvapi-[A-Za-z0-9_\-]{8,}|AIza[A-Za-z0-9_\-]{16,}|sk-ant-[A-Za-z0-9_\-]{8,}|sk-[A-Za-z0-9_\-]{16,})/',
        'bearer_token' => '/\bBearer\s+[A-Za-z0-9\-._~+\/]{16,}=*/i',
        'email' => '/[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}/',
        'payment_card' => '/\b(?:\d[ -]*?){13,19}\b/',
        'phone' => '/(?<![\dA-Za-z])(?:\+\d{1,3}[\s.\-]?)?(?:\(\d{2,4}\)[\s.\-]?)?\d{3,5}[\s.\-]\d{3,5}(?:[\s.\-]\d{2,4})?(?![\dA-Za-z])/',
        'iban' => '/\b[A-Z]{2}\d{2}[A-Z0-9]{11,30}\b/',
    ];

    /** Instructions that try to override the system prompt. */
    protected const INJECTION_RULES = [
        '/(ignore|disregard|forget)\s+(all\s+)?(previous|prior|above)\s+(instructions|rules|prompts?)/i',
        '/(reveal|show|print|repeat|leak)\s+(your\s+)?(system\s+)?(prompt|instructions|rules)/i',
        '/\b(developer|god|debug)\s+mode\b/i',
        '/\bjailbreak\b/i',
        '/\bdo\s+anything\s+now\b/i',
        '/\byou\s+are\s+now\s+(a|an|the)\s+/i',
    ];

    /** Hard-blocked attack signatures (SQL injection, traversal, scanners). */
    protected const THREAT_RULES = [
        'sql_injection' => [
            '/\bunion\s+(all\s+)?select\b/i',
            '/\bdrop\s+table\b/i',
            '/\binsert\s+into\b/i',
            '/\bdelete\s+from\b/i',
            '/\bupdate\s+\w+\s+set\b/i',
            '/\bor\s+1\s*=\s*1\b/i',
            '/\bsleep\s*\(/i',
            '/\bbenchmark\s*\(/i',
            '/\binformation_schema\b/i',
            '/\bxp_cmdshell\b/i',
            '/;\s*--/',
        ],
        'path_traversal' => [
            '/\.\.\/\.\.\//',
            '/etc\/passwd/i',
            '/proc\/self\/environ/i',
        ],
        'scanner' => [
            '/\bwp-admin\b/i',
            '/\bwp-login\b/i',
            '/\bxmlrpc\.php\b/i',
            '/\b(sqlmap|nikto|nmap|dirbuster|acunetix)\b/i',
        ],
    ];

    public function enabled(): bool
    {
        return (bool) config('security.ai.shield.enabled', true);
    }

    /**
     * The shield activates as soon as the company (or environment) has at
     * least one usable AI key.
     */
    public function isActive(): bool
    {
        if (! $this->enabled()) {
            return false;
        }

        $companyId = CompanyContext::id();

        if ($companyId === null) {
            return $this->envHasKey();
        }

        $hasStoredKey = AiProvider::query()
            ->where('company_id', $companyId)
            ->where('enabled', true)
            ->whereNotNull('encrypted_key')
            ->where('encrypted_key', '!=', '')
            ->exists();

        return $hasStoredKey || $this->envHasKey();
    }

    /**
     * Status payload for the Settings UI. Contains no secret material.
     *
     * @return array{active: bool, enabled: bool, providers: int, layers: array<int, array{name: string, state: string, detail: string}>}
     */
    public function status(): array
    {
        $configured = AiProvider::query()
            ->when(CompanyContext::id(), fn ($q, $id) => $q->where('company_id', $id))
            ->where('enabled', true)
            ->whereNotNull('encrypted_key')
            ->where('encrypted_key', '!=', '')
            ->count();

        return [
            'active' => $this->isActive(),
            'enabled' => $this->enabled(),
            'providers' => $configured,
            'layers' => [
                [
                    'name' => 'Encryption',
                    'state' => config('app.key') ? 'active' : 'attention',
                    'detail' => 'AES ciphertext at rest + keyed HMAC fingerprint. Plaintext never leaves the server.',
                ],
                [
                    'name' => 'Prompt sanitisation',
                    'state' => config('security.ai.shield.redact_pii', true) ? 'active' : 'off',
                    'detail' => 'API keys, emails, phone numbers and payment data are redacted before external transmission.',
                ],
                [
                    'name' => 'Threat detection',
                    'state' => 'active',
                    'detail' => 'SQL injection, traversal and scanner signatures are blocked; request spikes are rate limited.',
                ],
                [
                    'name' => 'Audit telemetry',
                    'state' => 'active',
                    'detail' => 'Every block and redaction is written to Security & Audit Logs without prompt text.',
                ],
            ],
        ];
    }

    /**
     * Redact secrets/PII and neutralise prompt-injection instructions.
     *
     * @return array{text: string, redactions: int, categories: array<string, int>, injections: int}
     */
    public function sanitize(string $text): array
    {
        $categories = [];
        $redactions = 0;

        foreach (self::REDACTION_RULES as $category => $pattern) {
            if ($category !== 'api_key' && $category !== 'bearer_token'
                && ! config('security.ai.shield.redact_pii', true)) {
                continue;
            }

            $count = 0;
            $text = (string) preg_replace_callback($pattern, function () use (&$count, $category) {
                $count++;

                return '[REDACTED_'.strtoupper($category).']';
            }, $text);

            if ($count > 0) {
                $categories[$category] = $count;
                $redactions += $count;
            }
        }

        $injections = 0;
        foreach (self::INJECTION_RULES as $pattern) {
            $text = (string) preg_replace_callback($pattern, function () use (&$injections) {
                $injections++;

                return '[BLOCKED-INSTRUCTION]';
            }, $text);
        }

        return [
            'text' => $text,
            'redactions' => $redactions,
            'categories' => $categories,
            'injections' => $injections,
        ];
    }

    /**
     * @return array{system: string, user: string, redactions: int, categories: array<string, int>, injections: int}
     */
    public function sanitizePrompt(string $system, string $user): array
    {
        if (! $this->enabled()) {
            return ['system' => $system, 'user' => $user, 'redactions' => 0, 'categories' => [], 'injections' => 0];
        }

        $systemResult = $this->sanitize($system);
        $userResult = $this->sanitize($user);

        return [
            'system' => $systemResult['text'],
            'user' => $userResult['text'],
            'redactions' => $systemResult['redactions'] + $userResult['redactions'],
            'categories' => $this->mergeCounts($systemResult['categories'], $userResult['categories']),
            'injections' => $systemResult['injections'] + $userResult['injections'],
        ];
    }

    /**
     * Persist sanitisation telemetry (metadata only — never the prompt).
     *
     * @param  array{redactions: int, categories: array<string, int>, injections: int}  $result
     */
    public function recordSanitization(array $result): void
    {
        if (($result['redactions'] ?? 0) > 0) {
            SecurityEventService::record(
                'ai',
                'prompt_redacted',
                'AI prompt sanitised: '.$result['redactions'].' sensitive value(s) removed',
                'low',
                null,
                null,
                ['categories' => $result['categories']],
            );
        }

        if (($result['injections'] ?? 0) > 0) {
            SecurityEventService::record(
                'ai',
                'prompt_injection_attempt',
                'Prompt-injection instruction neutralised before AI transmission',
                'medium',
                null,
                null,
                ['count' => $result['injections']],
            );
        }
    }

    /**
     * Inspect an inbound request for attack signatures and abnormal spikes.
     *
     * @return array{allowed: bool, status: int, reason: string|null, threats: array<int, string>}
     */
    public function inspectRequest(Request $request): array
    {
        if (! $this->enabled()) {
            return ['allowed' => true, 'status' => 200, 'reason' => null, 'threats' => []];
        }

        $identity = $request->user()?->id ?? $request->ip();
        $companyId = CompanyContext::id() ?? $request->ip();

        // Per-user burst protection (defence in depth behind throttle:ai).
        $userKey = 'superbrain:ai:user:'.$identity;
        $userLimit = (int) config('security.ai.shield.per_minute', 30);
        if ($userLimit > 0 && RateLimiter::tooManyAttempts($userKey, $userLimit)) {
            return $this->block($request, 'rate_limit', 'Too many AI requests in a short period. Please wait and retry.', 429);
        }

        // Per-company anomaly ceiling so one tenant cannot monopolise the gateway.
        $companyKey = 'superbrain:ai:company:'.$companyId;
        $companyLimit = (int) config('security.ai.shield.company_per_minute', 180);
        if ($companyLimit > 0 && RateLimiter::tooManyAttempts($companyKey, $companyLimit)) {
            return $this->block($request, 'company_rate_limit', 'The AI request rate for this company is unusually high. Please retry shortly.', 429);
        }

        $haystack = $this->requestHaystack($request);
        $threats = [];

        foreach (self::THREAT_RULES as $category => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $haystack) === 1) {
                    $threats[] = $category;

                    break;
                }
            }
        }

        if ($threats !== []) {
            return $this->block($request, 'threat_signature', 'Request blocked by the AI security shield.', 403, $threats);
        }

        // Prompt-injection attempts are neutralised at prompt-build time rather
        // than blocked, so legitimate business questions keep working.
        $injections = 0;
        foreach (self::INJECTION_RULES as $pattern) {
            $injections += (int) preg_match_all($pattern, $haystack);
        }

        if ($injections > 0) {
            SecurityEventService::record(
                'ai',
                'prompt_injection_attempt',
                'Prompt-injection instruction detected on an AI request',
                'medium',
                null,
                null,
                ['count' => $injections, 'route' => $request->route()?->getName()],
            );
        }

        if ($request->isMethod('post') || $request->isMethod('put') || $request->isMethod('patch') || $request->isMethod('delete')) {
            if ($userLimit > 0) {
                RateLimiter::hit($userKey, 60);
            }
            if ($companyLimit > 0) {
                RateLimiter::hit($companyKey, 60);
            }
        }

        return ['allowed' => true, 'status' => 200, 'reason' => null, 'threats' => []];
    }

    /**
     * @param  array<int, string>  $threats
     * @return array{allowed: bool, status: int, reason: string, threats: array<int, string>}
     */
    protected function block(Request $request, string $event, string $reason, int $status, array $threats = []): array
    {
        SecurityEventService::record(
            'ai',
            'shield_'.$event,
            'AI security shield blocked a request',
            $status === 429 ? 'medium' : 'high',
            null,
            null,
            [
                'route' => $request->route()?->getName(),
                'method' => $request->method(),
                'status' => $status,
                'threats' => $threats,
            ],
        );

        return ['allowed' => false, 'status' => $status, 'reason' => $reason, 'threats' => $threats];
    }

    protected function requestHaystack(Request $request): string
    {
        $parts = [
            (string) $request->getPathInfo(),
            (string) $request->getQueryString(),
            $this->flatten($request->all()),
        ];

        return mb_substr(implode(' ', array_filter($parts)), 0, 50000);
    }

    protected function flatten(mixed $value, int $depth = 0): string
    {
        if ($depth > 6) {
            return '';
        }

        if (is_array($value)) {
            $out = [];
            foreach ($value as $item) {
                $out[] = $this->flatten($item, $depth + 1);
            }

            return implode(' ', $out);
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return '';
    }

    /**
     * @param  array<string, int>  $a
     * @param  array<string, int>  $b
     * @return array<string, int>
     */
    protected function mergeCounts(array $a, array $b): array
    {
        foreach ($b as $key => $count) {
            $a[$key] = ($a[$key] ?? 0) + $count;
        }

        return $a;
    }

    protected function envHasKey(): bool
    {
        foreach ((array) config('ai.providers', []) as $config) {
            if (! empty($config['api_key'])) {
                return true;
            }
        }

        return false;
    }
}
