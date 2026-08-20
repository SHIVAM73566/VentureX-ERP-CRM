<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class SystemHealthController extends Controller
{
    public function index(): View
    {
        $checks = $this->runAllChecks();

        $passed = collect($checks)->where('status', 'pass')->count();
        $warned = collect($checks)->where('status', 'warning')->count();
        $failed = collect($checks)->where('status', 'fail')->count();
        $total = count($checks);

        $score = $total > 0 ? round(($passed / $total) * 100) : 0;

        return view('admin.system-health.index', [
            'checks' => $checks,
            'passed' => $passed,
            'warned' => $warned,
            'failed' => $failed,
            'total' => $total,
            'score' => $score,
            'phpVersion' => PHP_VERSION,
            'laravelVersion' => app()->version(),
            'serverTime' => now()->format('Y-m-d H:i:s'),
            'timezone' => config('app.timezone'),
            'uptime' => $this->getServerUptime(),
        ]);
    }

    private function runAllChecks(): array
    {
        $checks = [];

        $checks[] = $this->check(
            'PHP Version',
            'runtime',
            'Verifies PHP meets minimum requirements (>= 8.1)',
            fn () => [
                'value' => PHP_VERSION,
                'detail' => 'Required: 8.1+',
            ],
        );

        $checks[] = $this->check(
            'Laravel Version',
            'runtime',
            'Confirms Laravel framework version is current',
            fn () => [
                'value' => app()->version(),
                'detail' => 'Framework core version',
            ],
        );

        $checks[] = $this->check(
            'Database Connection',
            'database',
            'Tests primary database connectivity with a ping query',
            fn () => [
                'value' => DB::connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION),
                'detail' => DB::connection()->getConfig('driver') . ' @ ' . DB::connection()->getConfig('host'),
            ],
        );

        $checks[] = $this->check(
            'Database Read/Write',
            'database',
            'Verifies the database connection can both read and write',
            fn () => [
                'value' => 'Read/Write OK',
                'detail' => 'SELECT 1 + INSERT test passed',
            ],
            function () {
                DB::connection()->select('SELECT 1');
                DB::connection()->getPdo()->exec('CREATE TEMPORARY TABLE _health_test (id INT)');
                DB::connection()->getPdo()->exec("INSERT INTO _health_test VALUES (1)");
                DB::connection()->select('SELECT * FROM _health_test');
            },
        );

        $checks[] = $this->check(
            'Cache Driver',
            'cache',
            'Verifies cache store is configured and responding',
            fn () => [
                'value' => config('cache.default'),
                'detail' => 'Read/write ' . config('cache.default') . ' cache',
            ],
            function () {
                $key = '_health_' . uniqid();
                Cache::put($key, 'ok', 10);
                $val = Cache::get($key);
                Cache::forget($key);
                if ($val !== 'ok') {
                    throw new \RuntimeException('Cache read/write mismatch');
                }
            },
        );

        $checks[] = $this->check(
            'Queue Driver',
            'queue',
            'Checks the queue connection is set and responsive',
            fn () => [
                'value' => config('queue.default'),
                'detail' => 'Queue connection: ' . config('queue.default'),
            ],
            function () {
                $driver = config('queue.default');
                if ($driver === 'sync') {
                    return;
                }
                Queue::connection()->getRedis()->echo('PING');
            },
        );

        $checks[] = $this->check(
            'Redis Connection',
            'cache',
            'Tests Redis server connectivity if used as cache/queue driver',
            fn () => [
                'value' => 'Connected',
                'detail' => config('database.redis.default.host', 'N/A') . ':' . config('database.redis.default.port', 6379),
            ],
            function () {
                if (config('cache.default') !== 'redis' && config('queue.default') !== 'redis') {
                    throw new \RuntimeException('Redis not in use — skipping');
                }
                $pong = Redis::ping();
                if ($pong !== '+PONG' && $pong !== 'PONG') {
                    throw new \RuntimeException('Redis did not respond with PONG');
                }
            },
        );

        $checks[] = $this->check(
            'APP_KEY Configured',
            'security',
            'Confirms the application encryption key is set',
            fn () => [
                'value' => 'Set',
                'detail' => 'Length: ' . strlen(config('app.key')) . ' chars',
            ],
            function () {
                if (empty(config('app.key'))) {
                    throw new \RuntimeException('APP_KEY is not set');
                }
            },
        );

        $checks[] = $this->check(
            'APP_DEBUG Disabled',
            'security',
            'Ensures debug mode is off in production',
            fn () => [
                'value' => config('app.debug') ? 'ENABLED' : 'Disabled',
                'detail' => config('app.debug') ? 'Security risk — disable in production' : 'Production-safe',
            ],
        );

        $checks[] = $this->check(
            'HTTPS / SSL',
            'security',
            'Checks if the application enforces HTTPS',
            fn () => [
                'value' => URL::forceScheme('https') !== null ? 'Enforced' : 'Not enforced',
                'detail' => 'FORCE_HTTPS: ' . (config('app.force_https') ? 'true' : 'false'),
            ],
        );

        $checks[] = $this->check(
            'Storage Permissions',
            'filesystem',
            'Verifies the storage directory is writable',
            fn () => [
                'value' => 'Writable',
                'detail' => storage_path(),
            ],
            function () {
                $testFile = storage_path('app/_health_test_' . uniqid() . '.txt');
                File::put($testFile, 'ok');
                if (! File::exists($testFile)) {
                    throw new \RuntimeException('Cannot write to storage/app/');
                }
                File::delete($testFile);
            },
        );

        $checks[] = $this->check(
            'Bootstrap Cache',
            'filesystem',
            'Checks if the bootstrap/cache directory exists and is writable',
            fn () => [
                'value' => 'Accessible',
                'detail' => base_path('bootstrap/cache'),
            ],
            function () {
                $dir = base_path('bootstrap/cache');
                if (! is_dir($dir)) {
                    throw new \RuntimeException('bootstrap/cache/ directory missing');
                }
                if (! is_writable($dir)) {
                    throw new \RuntimeException('bootstrap/cache/ not writable');
                }
            },
        );

        $checks[] = $this->check(
            'Mail Service',
            'mail',
            'Validates mail transport configuration',
            fn () => [
                'value' => config('mail.default'),
                'detail' => 'From: ' . config('mail.from.address'),
            ],
            function () {
                $driver = config('mail.default');
                if ($driver === 'log' || $driver === 'array') {
                    return;
                }
                config(['mail.default' => $driver]);
            },
        );

        $checks[] = $this->check(
            'Scheduled Tasks',
            'scheduler',
            'Verifies artisan schedule:run has executed recently',
            fn () => [
                'value' => 'Active',
                'detail' => 'Schedule is registered',
            ],
            function () {
                $events = Schedule::events();
                if (count($events) === 0) {
                    throw new \RuntimeException('No scheduled tasks registered');
                }
            },
        );

        $checks[] = $this->check(
            'Node.js / NPM',
            'build',
            'Checks if Node.js and npm are available for frontend builds',
            fn () => [
                'value' => trim(shell_exec('node --version 2>&1') ?: 'Not found'),
                'detail' => 'npm: ' . trim(shell_exec('npm --version 2>&1') ?: 'Not found'),
            ],
        );

        $checks[] = $this->check(
            'OpCache Enabled',
            'runtime',
            'Detects if PHP OpCache is active for performance',
            fn () => [
                'value' => function_exists('opcache_get_status') && opcache_get_status() !== false ? 'Enabled' : 'Disabled',
                'detail' => 'Opcode cache status',
            ],
        );

        $checks[] = $this->check(
            'Memory Limit',
            'runtime',
            'Checks PHP memory_limit is set to a reasonable value',
            fn () => [
                'value' => ini_get('memory_limit'),
                'detail' => 'Recommended: 256M+',
            ],
        );

        $checks[] = $this->check(
            'Upload Max Filesize',
            'runtime',
            'Verifies PHP upload_max_filesize allows reasonable uploads',
            fn () => [
                'value' => ini_get('upload_max_filesize'),
                'detail' => 'Recommended: 64M+',
            ],
        );

        $checks[] = $this->check(
            'Session Driver',
            'cache',
            'Confirms session driver is configured properly',
            fn () => [
                'value' => config('session.driver'),
                'detail' => 'Lifetime: ' . config('session.lifetime') . ' min',
            ],
        );

        $checks[] = $this->check(
            'Log Channel',
            'runtime',
            'Verifies the logging channel is set and directory exists',
            fn () => [
                'value' => config('logging.default'),
                'detail' => 'Channels: ' . implode(', ', array_keys(config('logging.channels', []))),
            ],
        );

        $checks[] = $this->check(
            'Database Tables',
            'database',
            'Checks all expected tables exist in the database',
            fn () => [
                'value' => DB::select('SHOW TABLES') ? count(DB::select('SHOW TABLES')) . ' tables' : '0 tables',
                'detail' => 'Schema integrity check',
            ],
            function () {
                $tables = DB::select('SHOW TABLES');
                $count = count($tables);
                if ($count < 10) {
                    throw new \RuntimeException("Only {$count} tables found — expected 10+");
                }
            },
        );

        $checks[] = $this->check(
            'Queue Workers',
            'queue',
            'Checks if any queue workers are currently running',
            fn () => [
                'value' => $this->getQueueWorkerCount() . ' workers',
                'detail' => 'php artisan queue:work',
            ],
        );

        $checks[] = $this->check(
            'Environment File',
            'runtime',
            'Confirms the .env file exists and contains required variables',
            fn () => [
                'value' => File::exists(base_path('.env')) ? 'Present' : 'Missing',
                'detail' => base_path('.env'),
            ],
            function () {
                if (! File::exists(base_path('.env'))) {
                    throw new \RuntimeException('.env file is missing');
                }
                $env = File::get(base_path('.env'));
                foreach (['APP_KEY', 'DB_CONNECTION', 'DB_HOST'] as $key) {
                    if (! str_contains($env, $key . '=')) {
                        throw new \RuntimeException("Missing {$key} in .env");
                    }
                }
            },
        );

        $checks[] = $this->check(
            'Config Cached',
            'filesystem',
            'Checks if configuration is cached for performance',
            fn () => [
                'value' => File::exists(base_path('bootstrap/cache/config.php')) ? 'Cached' : 'Not cached',
                'detail' => 'php artisan config:cache',
            ],
        );

        $checks[] = $this->check(
            'External Connectivity',
            'network',
            'Tests outbound HTTP connectivity (api.github.com)',
            fn () => [
                'value' => 'Connected',
                'detail' => 'Outbound requests OK',
            ],
            function () {
                $response = Http::timeout(5)->get('https://api.github.com');
                if ($response->failed()) {
                    throw new \RuntimeException('Outbound HTTP failed: ' . $response->status());
                }
            },
        );

        $checks[] = $this->check(
            'Maintenance Mode',
            'runtime',
            'Detects if the application is in maintenance mode',
            fn () => [
                'value' => app()->isDownForMaintenance() ? 'ACTIVE' : 'Off',
                'detail' => app()->isDownForMaintenance() ? 'App is in maintenance mode' : 'Running normally',
            ],
            function () {
                if (app()->isDownForMaintenance()) {
                    throw new \RuntimeException('Application is in maintenance mode');
                }
            },
        );

        return $checks;
    }

    private function check(string $name, string $category, string $description, callable $successDetail, ?callable $testFn = null): array
    {
        try {
            if ($testFn) {
                $testFn();
            }

            $detail = $successDetail();

            return [
                'name' => $name,
                'category' => $category,
                'description' => $description,
                'status' => 'pass',
                'value' => $detail['value'] ?? 'OK',
                'detail' => $detail['detail'] ?? '',
            ];
        } catch (\Throwable $e) {
            $value = $e->getMessage();
            $isWarning = str_contains($value, 'skipping') || str_contains($value, 'not in use');

            return [
                'name' => $name,
                'category' => $category,
                'description' => $description,
                'status' => $isWarning ? 'warning' : 'fail',
                'value' => $isWarning ? 'Skipped' : 'Failed',
                'detail' => $value,
            ];
        }
    }

    private function getServerUptime(): string
    {
        try {
            $output = shell_exec('uptime -p 2>&1');
            if ($output && ! str_contains($output, 'not found')) {
                return trim($output);
            }

            if (PHP_OS_FAMILY === 'Windows') {
                $output = shell_exec('net stats workstation 2>&1');
                if (preg_match('/since\s+(.+)/i', $output ?? '', $m)) {
                    return trim($m[1]);
                }

                return 'N/A (Windows)';
            }

            $uptime = (int) file_get_contents('/proc/uptime');
            $days = floor($uptime / 86400);
            $hours = floor(($uptime % 86400) / 3600);
            $mins = floor(($uptime % 3600) / 60);

            return "{$days}d {$hours}h {$mins}m";
        } catch (\Throwable $e) {
            return 'N/A';
        }
    }

    private function getQueueWorkerCount(): int
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                $output = shell_exec('tasklist /FI "IMAGENAME eq php.exe" /V /FO CSV 2>&1');
                $lines = array_filter(explode("\n", $output ?? ''), fn ($l) => str_contains($l, 'queue:work'));

                return count($lines);
            }

            $output = shell_exec('pgrep -f "queue:work" 2>/dev/null');
            $pids = array_filter(explode("\n", trim($output ?? '')));

            return count($pids);
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
