<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Http\Middleware\InstallerGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class InstallerController extends Controller
{
    private static array $requiredExtensions = [
        'pdo_mysql', 'mbstring', 'openssl', 'curl', 'gd',
        'xml', 'zip', 'bcmath', 'fileinfo', 'intl',
    ];

    public function welcome(): mixed
    {
        if (InstallerGuard::isInstalled()) {
            return redirect()->route('login');
        }

        $requirements = $this->checkRequirements();

        return view('installer.welcome', compact('requirements'));
    }

    public function database(): mixed
    {
        if (InstallerGuard::isInstalled()) {
            return redirect()->route('login');
        }

        return view('installer.database');
    }

    public function storeDatabase(Request $request): mixed
    {
        if (InstallerGuard::isInstalled()) {
            return redirect()->route('login');
        }

        $request->validate([
            'db_host' => 'required|string|max:255',
            'db_port' => 'required|integer|min:1|max:65535',
            'db_database' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z0-9_]+$/'],
            'db_username' => 'required|string|max:255',
            'db_password' => 'nullable|string|max:255',
        ]);

        $host = $request->input('db_host');
        $port = $request->input('db_port');
        $database = $request->input('db_database');
        $username = $request->input('db_username');
        $password = $request->input('db_password');

        try {
            $connection = @new \PDO(
                "mysql:host={$host};port={$port}",
                $username,
                $password,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );

            $connection->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            $connection->exec("USE `{$database}`");

            $connection = null;
        } catch (\PDOException $e) {
            return back()->withInput()->withErrors([
                'db_connection' => 'Database connection failed: '.$e->getMessage(),
            ]);
        }

        $this->writeEnvDatabaseConfig($host, $port, $database, $username, $password);

        session(['installer_db' => compact('host', 'port', 'database', 'username', 'password')]);

        return redirect()->route('installer.environment');
    }

    public function environment(): mixed
    {
        if (InstallerGuard::isInstalled()) {
            return redirect()->route('login');
        }

        $envConfig = $this->loadEnvDefaults();

        return view('installer.environment', compact('envConfig'));
    }

    public function storeEnvironment(Request $request): mixed
    {
        if (InstallerGuard::isInstalled()) {
            return redirect()->route('login');
        }

        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url|max:255',
            'app_debug' => 'required|boolean',
        ]);

        $envData = $request->only([
            'app_name', 'app_url', 'app_debug',
            'ai_provider', 'openai_api_key', 'gemini_api_key', 'anthropic_api_key',
            'nvidia_api_key', 'rapidapi_key',
            'mail_mailer', 'mail_host', 'mail_port', 'mail_username', 'mail_password',
            'mail_from_address', 'mail_from_name',
        ]);

        $appKey = 'base64:'.base64_encode(random_bytes(32));
        $envData['app_key'] = $appKey;

        $this->writeEnvConfig($envData);

        session(['installer_env' => $envData]);

        return redirect()->route('installer.admin');
    }

    public function admin(): mixed
    {
        if (InstallerGuard::isInstalled()) {
            return redirect()->route('login');
        }

        return view('installer.admin');
    }

    public function storeAdmin(Request $request): mixed
    {
        if (InstallerGuard::isInstalled()) {
            return redirect()->route('login');
        }

        $request->validate([
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'admin_password' => [
                'required', 'string', 'min:10', 'max:255',
                'confirmed',
            ],
        ]);

        $password = $request->input('admin_password');

        $hasUpper = preg_match('/[A-Z]/', $password);
        $hasLower = preg_match('/[a-z]/', $password);
        $hasDigit = preg_match('/\d/', $password);
        $hasSpecial = preg_match('/[^A-Za-z0-9]/', $password);

        if (! ($hasUpper && $hasLower && $hasDigit && $hasSpecial)) {
            return back()->withInput()->withErrors([
                'admin_password' => 'Password must contain uppercase, lowercase, numbers, and special characters.',
            ]);
        }

        session([
            'installer_admin' => $request->only(['admin_name', 'admin_email']),
            'installer_admin_password' => $password,
        ]);

        return redirect()->route('installer.run');
    }

    public function run(): mixed
    {
        if (InstallerGuard::isInstalled()) {
            return redirect()->route('login');
        }

        return view('installer.install');
    }

    public function execute(Request $request): mixed
    {
        if (InstallerGuard::isInstalled()) {
            return response()->json(['error' => 'Already installed', 'redirect' => route('login')]);
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
            $migrateOutput = Artisan::output();

            Artisan::call('db:seed', ['--class' => 'DemoCredentialSeeder', '--force' => true]);
            $seedOutput = Artisan::output();

            $this->createAdminUser();

            $this->setPermissions();

            InstallerGuard::markInstalled();

            return response()->json([
                'success' => true,
                'redirect' => route('installer.complete'),
                'output' => $migrateOutput."\n".$seedOutput,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function complete(): mixed
    {
        if (! InstallerGuard::isInstalled()) {
            return redirect()->route('installer.welcome');
        }

        return view('installer.complete');
    }

    private function checkRequirements(): array
    {
        $requirements = [];

        $requirements[] = [
            'label' => 'PHP '.static::$requiredExtensions[0],
            'test' => 'PHP >= 8.3',
            'passed' => version_compare(PHP_VERSION, '8.3.0', '>='),
            'detail' => 'Current: '.PHP_VERSION,
        ];

        foreach (static::$requiredExtensions as $ext) {
            $requirements[] = [
                'label' => "Extension: {$ext}",
                'test' => "php-{$ext}",
                'passed' => extension_loaded($ext),
                'detail' => extension_loaded($ext) ? 'Loaded' : 'Not installed',
            ];
        }

        $mysqlVersion = 'N/A';
        $mysqlPassed = false;
        try {
            if (class_exists(\PDO::class)) {
                $pdo = @new \PDO('mysql:host=127.0.0.1', null, null, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
                $mysqlVersion = $pdo->query('SELECT VERSION()')->fetchColumn();
                $mysqlPassed = true;
                $pdo = null;
            }
        } catch (\PDOException $e) {
            $mysqlVersion = 'Cannot connect: '.$e->getMessage();
        }
        $requirements[] = [
            'label' => 'MySQL / MariaDB',
            'test' => 'MySQL 5.7+ / MariaDB 10.3+',
            'passed' => $mysqlPassed,
            'detail' => $mysqlVersion,
        ];

        $storageWritable = is_writable(base_path('storage')) || @chmod(base_path('storage'), 0755);
        $requirements[] = [
            'label' => 'storage/ directory writable',
            'test' => 'storage/',
            'passed' => $storageWritable,
            'detail' => $storageWritable ? 'Writable' : 'Not writable',
        ];

        $cachePath = base_path('bootstrap/cache');
        $cacheWritable = is_writable($cachePath) || @chmod($cachePath, 0755);
        $requirements[] = [
            'label' => 'bootstrap/cache/ directory writable',
            'test' => 'bootstrap/cache/',
            'passed' => $cacheWritable,
            'detail' => $cacheWritable ? 'Writable' : 'Not writable',
        ];

        $envPath = base_path('.env');
        $envWritable = (file_exists($envPath) && is_writable($envPath)) || is_writable(dirname($envPath));
        $requirements[] = [
            'label' => '.env file writable',
            'test' => '.env',
            'passed' => $envWritable,
            'detail' => $envWritable ? 'Writable' : 'Not writable',
        ];

        $canGenerateKey = function_exists('random_bytes') && function_exists('base64_encode');
        $requirements[] = [
            'label' => 'APP_KEY generation',
            'test' => 'random_bytes + base64',
            'passed' => $canGenerateKey,
            'detail' => $canGenerateKey ? 'Supported' : 'Not supported',
        ];

        return $requirements;
    }

    private function writeEnvDatabaseConfig(
        string $host,
        string|int $port,
        string $database,
        string $username,
        string $password
    ): void {
        $envPath = base_path('.env');
        $envContent = file_exists($envPath) ? file_get_contents($envPath) : file_get_contents(base_path('.env.example'));

        $replacements = [
            'DB_HOST' => $host,
            'DB_PORT' => (string) $port,
            'DB_DATABASE' => $database,
            'DB_USERNAME' => $username,
            'DB_PASSWORD' => $password,
        ];

        foreach ($replacements as $key => $value) {
            if (preg_match("/^{$key}=.*/m", $envContent)) {
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envPath, $envContent);
    }

    private function writeEnvConfig(array $data): void
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        $map = [
            'app_name' => 'APP_NAME',
            'app_url' => 'APP_URL',
            'app_debug' => 'APP_DEBUG',
            'app_key' => 'APP_KEY',
            'ai_provider' => 'AI_PROVIDER',
            'openai_api_key' => 'OPENAI_API_KEY',
            'gemini_api_key' => 'GEMINI_API_KEY',
            'anthropic_api_key' => 'ANTHROPIC_API_KEY',
            'nvidia_api_key' => 'NVIDIA_API_KEY',
            'rapidapi_key' => 'RAPIDAPI_KEY',
            'mail_mailer' => 'MAIL_MAILER',
            'mail_host' => 'MAIL_HOST',
            'mail_port' => 'MAIL_PORT',
            'mail_username' => 'MAIL_USERNAME',
            'mail_password' => 'MAIL_PASSWORD',
            'mail_from_address' => 'MAIL_FROM_ADDRESS',
            'mail_from_name' => 'MAIL_FROM_NAME',
        ];

        foreach ($map as $formKey => $envKey) {
            $value = $data[$formKey] ?? null;
            if ($value === null) {
                continue;
            }

            if ($envKey === 'APP_NAME') {
                $value = '"'.$value.'"';
            }

            if (preg_match("/^" . preg_quote($envKey, '/') . "=.*/m", $envContent)) {
                $envContent = preg_replace("/^" . preg_quote($envKey, '/') . "=.*/m", "{$envKey}={$value}", $envContent);
            } else {
                $envContent .= "\n{$envKey}={$value}";
            }
        }

        file_put_contents($envPath, $envContent);
    }

    private function createAdminUser(): void
    {
        $admin = session('installer_admin');
        $password = session('installer_admin_password');

        if (! $admin || ! $password) {
            throw new RuntimeException('Admin data not found in session. Please go back and try again.');
        }

        $existingUser = \App\Models\User::where('email', $admin['admin_email'])->first();

        if ($existingUser) {
            $existingUser->update(['password' => Hash::make($password)]);

            return;
        }

        $company = \App\Models\Company::firstOrCreate(
            ['name' => $admin['admin_name']."'s Company"],
            [
                'legal_name' => $admin['admin_name']."'s Company",
                'email' => $admin['admin_email'],
                'is_active' => true,
            ]
        );

        $user = \App\Models\User::create([
            'name' => $admin['admin_name'],
            'email' => $admin['admin_email'],
            'password' => Hash::make($password),
            'company_id' => $company->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $user->assignRole('super_admin');
    }

    private function setPermissions(): void
    {
        $dirs = [
            base_path('storage'),
            base_path('bootstrap/cache'),
        ];

        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                @chmod($dir, 0755);
                $this->setPermissionsRecursive($dir);
            }
        }
    }

    private function setPermissionsRecursive(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($items as $item) {
            @chmod($item->getPathname(), $item->isDir() ? 0755 : 0644);
        }
    }

    private function loadEnvDefaults(): array
    {
        $envPath = base_path('.env');
        $content = file_exists($envPath) ? file_get_contents($envPath) : '';

        $defaults = [];
        foreach (explode("\n", $content) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $defaults[trim($key)] = trim($value, ' "');
            }
        }

        return $defaults;
    }
}
