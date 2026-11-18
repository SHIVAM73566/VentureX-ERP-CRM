<?php

namespace App\Http\Middleware;

use App\Services\Installer\InstallState;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Web-installer lockout guard.
 *
 * @author Shivam Chaturvedi <chaturvedishivam179@gmail.com>
 */
class InstallerGuard
{
    protected static string $flagFile = 'storage/.installed';

    /**
     * Installed when EITHER the legacy flag file or the database singleton says
     * so. Removing only one of them must not re-open the wizard.
     */
    public static function isInstalled(): bool
    {
        if (self::flagExists()) {
            return true;
        }

        try {
            return app(InstallState::class)->isInstalled();
        } catch (Throwable) {
            // No database yet (fresh install) — the flag file is authoritative.
            return false;
        }
    }

    protected static function flagExists(): bool
    {
        $path = config('installer.flag_path');

        return file_exists(is_string($path) && $path !== '' ? $path : base_path(self::$flagFile));
    }

    protected static function flagPath(): string
    {
        $path = config('installer.flag_path');

        return is_string($path) && $path !== '' ? $path : base_path(self::$flagFile);
    }

    public static function markInstalled(?string $mode = null): void
    {
        $path = self::flagPath();
        $directory = dirname($path);

        if (! is_dir($directory)) {
            File::makeDirectory($directory, 0755, true, true);
        }

        file_put_contents($path, now()->toIso8601String(), LOCK_EX);

        try {
            app(InstallState::class)->record(mode: $mode, installed: true);
        } catch (Throwable) {
            // The file flag still protects the installer if the database is
            // unavailable at this exact moment.
        }
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Identify installer endpoints by their registered route names, never by
        // the raw request path. A path-based check can be tricked by encoded
        // separators or suffix tricks; a route name can only exist on a route
        // we explicitly registered inside the installer group.
        $isInstallerRoute = $request->routeIs('installer.*');
        $isCompletionRoute = $request->routeIs('installer.complete');
        $installed = self::isInstalled();

        if ($installed && $isInstallerRoute && ! $isCompletionRoute) {
            return redirect()->route('login');
        }

        if (! $installed && ! $isInstallerRoute) {
            return redirect()->route('installer.welcome');
        }

        return $next($request);
    }
}
