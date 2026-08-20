<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InstallerGuard
{
    protected static string $flagFile = 'storage/.installed';

    public static function isInstalled(): bool
    {
        return file_exists(base_path(self::$flagFile));
    }

    public static function markInstalled(): void
    {
        file_put_contents(base_path(self::$flagFile), now()->toIso8601String());
    }

    public function handle(Request $request, Closure $next): Response
    {
        $isInstallRoute = $request->is('install*');
        $installed = self::isInstalled();

        if ($installed && $isInstallRoute) {
            return redirect()->route('login');
        }

        if (! $installed && ! $isInstallRoute) {
            return redirect()->route('installer.welcome');
        }

        return $next($request);
    }
}
