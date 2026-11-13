<?php

use App\Http\Middleware\ApiProtection;
use App\Http\Middleware\EmergencyLockdown;
use App\Http\Middleware\EnsureAiSecurity;
use App\Http\Middleware\EnsureTwoFactor;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\InstallerGuard;
use App\Http\Middleware\LoadAiProviders;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SelfHealing;
use App\Http\Middleware\SetCompanyContext;
use App\Http\Middleware\StepUpAuth;
use App\Services\SelfHealingService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;

require_once __DIR__.'/../app/Support/helpers.php';

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        then: function () {
            if (! InstallerGuard::isInstalled() || request()->is('install*')) {
                require __DIR__.'/../routes/installer.php';
            }
        },
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            InstallerGuard::class,
            SetCompanyContext::class,
            LoadAiProviders::class,
            SecurityHeaders::class,
            EnsureAiSecurity::class,
        ]);

        $middleware->prepend([
            SelfHealing::class,
            ForceHttps::class,
            EmergencyLockdown::class,
        ]);

        $middleware->validateCsrfTokens(except: [
        ]);

        $middleware->alias([
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
            'two_factor' => EnsureTwoFactor::class,
            'step_up' => StepUpAuth::class,
            'api.protection' => ApiProtection::class,
            'installer' => InstallerGuard::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // A stale/encrypted session or a rotated APP_KEY can produce a 419
        // token mismatch. Instead of showing a dead-end error page, clear the
        // broken session state and send the user back to sign in safely.
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
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

            $result = $healing->capture($e, $request, $status);

            if ((bool) config('app.debug') || ! (bool) config('self-healing.render_fallback', true)) {
                return null;
            }

            return $healing->fallback($request, $status, $result['fingerprint']);
        });
    })->create();
