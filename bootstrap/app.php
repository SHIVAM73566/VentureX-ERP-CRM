<?php

use App\Http\Middleware\ApiProtection;
use App\Http\Middleware\EmergencyLockdown;
use App\Http\Middleware\EnsureTwoFactor;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\InstallerGuard;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetCompanyContext;
use App\Http\Middleware\StepUpAuth;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;

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
            SecurityHeaders::class,
        ]);

        $middleware->prepend([
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
    })->create();
