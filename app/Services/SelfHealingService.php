<?php

namespace App\Services;

use App\Models\Company;
use App\Models\ErrorReport;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Autonomous self-healing service.
 *
 * Captures runtime exceptions, deduplicates them, writes them to the Error
 * Center (error_reports) and the audit trail (/admin/audit-logs), attempts a
 * small set of safe runtime repairs, and can render a safe fallback response.
 *
 * The service is deliberately defensive: a failure inside the healer must
 * never take down the request it is trying to heal.
 */
class SelfHealingService
{
    /**
     * @return array{fingerprint: string, status: int, report_id: int|null, hot_fix: string|null}
     */
    public function capture(Throwable $e, ?Request $request = null, ?int $status = null): array
    {
        $status ??= $this->statusFor($e);
        $fingerprint = $this->fingerprint($e);

        $hotFix = $this->attemptHotFix($e);
        $reportId = null;

        try {
            $reportId = $this->recordErrorReport($e, $request, $status);
        } catch (Throwable $inner) {
            Log::warning('self_healing.report_failed', [
                'fingerprint' => $fingerprint,
                'error' => $inner->getMessage(),
            ]);
        }

        try {
            $this->writeAudit($e, $request, $status, $fingerprint, $hotFix);
        } catch (Throwable $inner) {
            Log::warning('self_healing.audit_failed', [
                'fingerprint' => $fingerprint,
                'error' => $inner->getMessage(),
            ]);
        }

        // Always keep a low-level copy in the application log as a last resort.
        try {
            Log::error('self_healing.captured', [
                'type' => $e::class,
                'message' => mb_substr($e->getMessage(), 0, 500),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'status' => $status,
                'fingerprint' => $fingerprint,
                'hot_fix' => $hotFix,
            ]);
        } catch (Throwable) {
            // Ignore.
        }

        return [
            'fingerprint' => $fingerprint,
            'status' => $status,
            'report_id' => $reportId,
            'hot_fix' => $hotFix,
        ];
    }

    /**
     * Exceptions that must keep their native Laravel behaviour.
     */
    public function mustRethrow(Throwable $e): bool
    {
        return $e instanceof ValidationException
            || $e instanceof AuthenticationException
            || $e instanceof AuthorizationException
            || $e instanceof HttpResponseException
            || $e instanceof TokenMismatchException;
    }

    public function statusFor(Throwable $e): int
    {
        if ($e instanceof ValidationException) {
            return 422;
        }

        if ($e instanceof AuthenticationException) {
            return 401;
        }

        if ($e instanceof AuthorizationException) {
            return 403;
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return 404;
        }

        if ($e instanceof TokenMismatchException) {
            return 419;
        }

        if ($e instanceof HttpExceptionInterface) {
            return $e->getStatusCode();
        }

        return 500;
    }

    /**
     * Render a safe fallback response for the request.
     */
    public function fallback(Request $request, int $status = 500, ?string $fingerprint = null): SymfonyResponse
    {
        $reference = $fingerprint ? substr($fingerprint, 0, 12) : null;

        $message = match (true) {
            $status === 404 => 'The page you requested could not be found.',
            $status === 422 => 'The submitted data could not be processed.',
            $status === 419 => 'Your session expired. Please sign in again.',
            default => 'Something went wrong. Our self-healing system has logged the issue and a safe fallback was used.',
        };

        if ($request->expectsJson() || $request->is('api/*')) {
            return new JsonResponse([
                'success' => false,
                'message' => $message,
                'reference' => $reference,
                'status' => $status,
            ], $status);
        }

        $view = match ($status) {
            404 => 'errors.404',
            419 => 'errors.419',
            default => 'errors.self-healed',
        };

        try {
            return new Response(view($view, [
                'status' => $status,
                'reference' => $reference,
                'message' => $message,
            ])->render(), $status);
        } catch (Throwable) {
            return new Response($message, $status);
        }
    }

    public function fingerprint(Throwable $e): string
    {
        return hash('sha256', implode('|', [
            $e::class,
            $e->getMessage(),
            $e->getFile(),
            (string) $e->getLine(),
        ]));
    }

    protected function recordErrorReport(Throwable $e, ?Request $request, int $status): ?int
    {
        $companyId = CompanyContext::id();

        // A stale/static company context (deleted tenant, previous request in
        // the same worker) must never make the Error Center insert fail on the
        // foreign key. Fall back to a company-agnostic report.
        if ($companyId !== null && ! Company::query()->whereKey($companyId)->exists()) {
            $companyId = null;
        }

        $hash = ErrorReport::generateErrorHash($e->getMessage(), $e->getFile(), $e->getLine(), $companyId);

        // Deduplicate bursts of the same exception to protect the database.
        $dedupeKey = 'self_healing:'.$hash;
        Cache::add($dedupeKey, 1, now()->addSeconds((int) config('self-healing.dedupe_seconds', 60)));

        $report = ErrorReport::firstOrNew(['error_hash' => $hash]);
        $isNew = ! $report->exists;

        $report->fill([
            'company_id' => $companyId,
            'user_id' => auth()->id(),
            'error_type' => 'exception',
            'error_message' => mb_substr($e->getMessage(), 0, 4000),
            'module' => $this->moduleFor($request, $e),
            'controller' => $this->controllerFor($request),
            'action' => $this->actionFor($request),
            'route' => $request?->route()?->getName(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'stack_trace' => mb_substr($e->getTraceAsString(), 0, (int) config('self-healing.max_trace_chars', 12000)),
            'http_method' => $request?->method(),
            'http_status' => $status,
            'url' => $request ? mb_substr($request->fullUrl(), 0, 2000) : null,
            'ip_address' => $request?->ip(),
            'user_agent' => mb_substr((string) $request?->userAgent(), 0, 1000),
            'app_version' => (string) config('app.version', '1.0.0'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $request?->server('SERVER_SOFTWARE'),
            'request_data' => $request ? $this->sanitize($request->all()) : null,
            'session_data' => null,
            'status' => $isNew ? 'new' : ($report->status ?: 'new'),
            'occurrence_count' => (int) ($report->occurrence_count ?? 0) + 1,
            'first_seen_at' => $report->first_seen_at ?? now(),
            'last_seen_at' => now(),
        ]);

        $report->save();

        return $report->id;
    }

    protected function writeAudit(Throwable $e, ?Request $request, int $status, string $fingerprint, ?string $hotFix): void
    {
        AuditLogger::log('self_healing', (string) config('self-healing.audit_module', 'system'), null, null, [
            'fingerprint' => $fingerprint,
            'type' => $e::class,
            'message' => mb_substr($e->getMessage(), 0, 1000),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'status' => $status,
            'method' => $request?->method(),
            'url' => $request ? mb_substr($request->fullUrl(), 0, 1000) : null,
            'route' => $request?->route()?->getName(),
            'hot_fix' => $hotFix,
            'healed_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Only a small, explicitly safe set of repairs is attempted. Anything
     * that could mutate business data is never touched.
     */
    protected function attemptHotFix(Throwable $e): ?string
    {
        $message = $e->getMessage();

        try {
            if (str_contains($message, 'compiled view') || str_contains($message, 'View path not found')) {
                Artisan::call('view:clear');

                return 'view_cache_cleared';
            }

            if (str_contains($message, 'Target class') && str_contains($message, 'does not exist')) {
                Artisan::call('optimize:clear');

                return 'optimization_cache_cleared';
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    protected function moduleFor(?Request $request, Throwable $e): string
    {
        $route = $request?->route()?->getName();

        if ($route) {
            return str($route)->before('.')->toString();
        }

        return str($e->getFile())
            ->after('app'.DIRECTORY_SEPARATOR)
            ->before(DIRECTORY_SEPARATOR)
            ->lower()
            ->toString() ?: 'system';
    }

    protected function controllerFor(?Request $request): ?string
    {
        try {
            $action = $request?->route()?->getActionName();

            if (! $action || $action === 'Closure') {
                return null;
            }

            return str($action)->before('@')->afterLast('\\')->toString();
        } catch (Throwable) {
            return null;
        }
    }

    protected function actionFor(?Request $request): ?string
    {
        try {
            $action = $request?->route()?->getActionName();

            return $action && str_contains($action, '@') ? str($action)->after('@')->toString() : null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Recursively redact secrets and truncate large payloads.
     */
    protected function sanitize(array $data, int $depth = 0): array
    {
        $sensitive = [
            'password', 'password_confirmation', 'current_password', 'token', '_token',
            'api_key', 'secret', 'authorization', 'cookie', 'two_factor_secret',
            'two_factor_recovery_codes', 'remember_token',
        ];

        $out = [];
        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), $sensitive, true)) {
                $out[$key] = '[REDACTED]';

                continue;
            }

            if (is_array($value)) {
                $out[$key] = $depth < 3 ? $this->sanitize($value, $depth + 1) : '[TRUNCATED]';
            } elseif (is_object($value)) {
                $out[$key] = '[OBJECT]';
            } elseif (is_string($value)) {
                $out[$key] = mb_substr($value, 0, 500);
            } else {
                $out[$key] = $value;
            }
        }

        return $out;
    }
}
