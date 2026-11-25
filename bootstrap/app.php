<?php

use App\Http\Middleware\ApiProtection;
use App\Http\Middleware\EmergencyLockdown;
use App\Http\Middleware\EnsureAiSecurity;
use App\Http\Middleware\EnsureLayerEntitled;
use App\Http\Middleware\EnsureTwoFactor;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\InstallerGuard;
use App\Http\Middleware\LoadAiProviders;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SelfHealing;
use App\Http\Middleware\SetCompanyContext;
use App\Http\Middleware\StepUpAuth;
use App\Http\Middleware\SuperBrainShield;
use App\Services\SelfHealingService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpException;

require_once __DIR__.'/../app/Support/helpers.php';

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // Installer routes are always registered inside the web group so they
        // receive sessions, CSRF protection and the security-header stack.
        // InstallerGuard alone decides whether they may be reached.
        web: [
            __DIR__.'/../routes/web.php',
            __DIR__.'/../routes/installer.php',
        ],
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            InstallerGuard::class,
            SetCompanyContext::class,
            LoadAiProviders::class,
            SecurityHeaders::class,
            EnsureAiSecurity::class,
            SuperBrainShield::class,
        ]);

        $middleware->prepend([
            SelfHealing::class,
            ForceHttps::class,
            EmergencyLockdown::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            // Stripe authenticates with a signed raw payload instead of a
            // browser CSRF token. All other POST routes stay protected.
            'billing/stripe/webhook',
        ]);

        $middleware->alias([
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
            'two_factor' => EnsureTwoFactor::class,
            'step_up' => StepUpAuth::class,
            'super_brain' => SuperBrainShield::class,
            'api.protection' => ApiProtection::class,
            'installer' => InstallerGuard::class,
            'layer' => EnsureLayerEntitled::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Never round-trip secrets through the session when validation fails.
        // Without this, a rejected API key would be flashed as old input and
        // could leak back into the HTML on the next request.
        $exceptions->dontFlash([
            'api_key',
            'api_token',
            'access_token',
            'refresh_token',
            'authorization',
            'client_secret',
            'current_password',
            'password',
            'password_confirmation',
            'admin_password',
            'admin_password_confirmation',
            'db_password',
            'mail_password',
            'license_token',
            'license_key',
            'private_key',
            'recovery_code',
            'secret',
            'session_token',
            'stripe_secret',
            'webhook_secret',
            'stripe_webhook_secret',
            'two_factor_code',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'openai_api_key',
            'gemini_api_key',
            'anthropic_api_key',
            'nvidia_api_key',
            'rapidapi_key',
        ]);

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // A stale/encrypted session or a rotated APP_KEY can produce a 419
        // token mismatch. Instead of showing a dead-end error page, clear the
        // broken session state and send the user back to sign in safely.
        // Laravel converts TokenMismatchException to a 419 HttpException before
        // renderable callbacks run, so match the prepared exception here.
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            // Installer POSTs must fail loudly with 419 rather than silently
            // redirecting, so a missing/stale CSRF token is never mistaken for
            // a completed step.
            if ($request->is('install', 'install/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your session expired. Please refresh the installer and try again.',
                ], 419);
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your session expired. Please refresh and try again.',
                ], 419);
            }

            return redirect()
                ->route('login')
                ->with('error', 'Your session expired for security reasons. Please sign in again.');
        });

        // Route exceptions never reach the outer SelfHealing middleware because
        // Laravel's routing pipeline converts them to responses first. Hooking
        // the handler guarantees every unexpected failure is captured, while
        // validation/auth/authorization/CSRF and other 4xx responses keep their
        // native behaviour.
        $exceptions->render(function (Throwable $e, Request $request) {
            $healing = app(SelfHealingService::class);

            if (! (bool) config('self-healing.enabled', true)) {
                return null;
            }

            if ($healing->mustRethrow($e)) {
                return null;
            }

            $status = $healing->statusFor($e);

            if ($status < 500 && $status !== 404) {
                return null;
            }

            // Missing routes are normal client errors. Unless an operator has
            // explicitly opted in, do not log every scanner/probe 404 as an
            // application error or render the self-healing fallback page.
            if ($status === 404 && ! (bool) config('self-healing.capture_not_found', false)) {
                return null;
            }

            $result = $healing->capture($e, $request, $status);

            if ((bool) config('app.debug') || ! (bool) config('self-healing.render_fallback', true)) {
                return null;
            }

            return $healing->fallback($request, $status, $result['fingerprint']);
        });
    })->create();
