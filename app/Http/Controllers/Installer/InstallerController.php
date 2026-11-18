<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Http\Middleware\InstallerGuard;
use App\Services\Installer\EnvWriter;
use App\Services\Installer\InstallerService;
use App\Services\UploadSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

/**
 * Six-step web installer.
 *
 * @author Shivam Chaturvedi <chaturvedishivam179@gmail.com>
 */
class InstallerController extends Controller
{
    private static array $requiredExtensions = [
        'pdo_mysql', 'mbstring', 'openssl', 'curl', 'gd',
        'xml', 'zip', 'bcmath', 'fileinfo', 'intl',
    ];

    public function __construct(
        protected InstallerService $installer,
        protected EnvWriter $env,
    ) {}

    public function welcome(): mixed
    {
        if (InstallerGuard::isInstalled()) {
            return redirect()->route('login');
        }

        $requirements = $this->checkRequirements();
        $failed = collect($requirements)->where('passed', false)->count();

        return view('installer.welcome', [
            'requirements' => $requirements,
            'failed' => $failed,
            'currentStep' => 1,
        ]);
    }

    public function mode(): mixed
    {
        if (InstallerGuard::isInstalled()) {
            return redirect()->route('login');
        }

        return view('installer.mode', [
            'mode' => session('installer_mode', InstallerService::MODE_PRODUCTION),
            'currentStep' => 2,
        ]);
    }

    public function storeMode(Request $request): mixed
    {
        if (InstallerGuard::isInstalled()) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'mode' => ['required', 'in:production,demo'],
        ]);

        try {
            $this->env->assertSafe($data);
            $this->installer->persistMode($data['mode']);
        } catch (Throwable $e) {
            Log::error('Installer mode persistence failed', ['error' => mb_substr($e->getMessage(), 0, 500)]);

            return back()->withInput()->withErrors([
                'mode' => 'The installation mode could not be saved. Check file permissions and try again.',
            ]);
        }

        session(['installer_mode' => $data['mode']]);

        return redirect()->route('installer.database');
    }

    public function database(): mixed
    {
        if (InstallerGuard::isInstalled()) {
            return redirect()->route('login');
        }

        return view('installer.database', [
            'db' => [
                'db_host' => $this->env->read('DB_HOST', '127.0.0.1'),
                'db_port' => $this->env->read('DB_PORT', '3306'),
                'db_database' => $this->env->read('DB_DATABASE', ''),
                'db_username' => $this->env->read('DB_USERNAME', ''),
            ],
            'currentStep' => 3,
        ]);
    }

    public function storeDatabase(Request $request): mixed
    {
        if (InstallerGuard::isInstalled()) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'db_host' => ['required', 'string', 'max:255'],
            'db_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'db_database' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z0-9_]+$/'],
            'db_username' => ['required', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->env->assertSafe($data);
        } catch (InvalidArgumentException) {
            return back()->withInput()->withErrors([
                'db_connection' => 'Database values must not contain newlines or control characters.',
            ]);
        }

        $host = (string) $data['db_host'];
        $port = (int) $data['db_port'];
        $database = (string) $data['db_database'];
        $username = (string) $data['db_username'];
        $password = (string) ($data['db_password'] ?? '');

        try {
            $connection = @new PDO(
                "mysql:host={$host};port={$port};charset=utf8mb4",
                $username,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $connection->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $connection->exec("USE `{$database}`");
            $connection = null;
        } catch (PDOException $e) {
            Log::error('Installer database check failed', [
                'exception' => $e::class,
                'code' => $e->getCode(),
                'message' => mb_substr($e->getMessage(), 0, 1000),
            ]);

            $message = $this->databaseErrorMessage($e);

            return back()->withErrors(['db_connection' => $message]);
        } catch (Throwable $e) {
            Log::error('Installer database check exception', [
                'exception' => $e::class,
                'message' => mb_substr($e->getMessage(), 0, 1000),
            ]);

            return back()->withErrors(['db_connection' => 'Could not connect to the database. Verify the host, port and credentials, then try again.']);
        }

        try {
            $this->env->write([
                'DB_HOST' => $host,
                'DB_PORT' => (string) $port,
                'DB_DATABASE' => $database,
                'DB_USERNAME' => $username,
                'DB_PASSWORD' => $password,
            ]);
        } catch (Throwable $e) {
            Log::error('Installer database env write failed', ['error' => mb_substr($e->getMessage(), 0, 500)]);

            return back()->withErrors(['db_connection' => 'Database settings could not be saved. Check .env permissions and try again.']);
        }

        session(['installer_db' => [
            'host' => $host, 'port' => $port, 'database' => $database,
            'username' => $username,
        ]]);

        return redirect()->route('installer.environment');
    }

    public function environment(): mixed
    {
        if (InstallerGuard::isInstalled()) {
            return redirect()->route('login');
        }

        $secretKeys = ['OPENAI_API_KEY', 'GEMINI_API_KEY', 'ANTHROPIC_API_KEY', 'NVIDIA_API_KEY', 'RAPIDAPI_KEY', 'MAIL_PASSWORD'];
        $configured = $this->env->configuredFlags($secretKeys);

        return view('installer.environment', [
            'envConfig' => [
                'APP_NAME' => $this->env->read('APP_NAME', 'VentureX ERP & CRM'),
                'APP_URL' => $this->env->read('APP_URL', url('/')),
                'APP_DEBUG' => $this->env->read('APP_DEBUG', 'false'),
                'AI_PROVIDER' => $this->env->read('AI_PROVIDER', 'nvidia'),
                'MAIL_MAILER' => $this->env->read('MAIL_MAILER', 'log'),
                'MAIL_HOST' => $this->env->read('MAIL_HOST', '127.0.0.1'),
                'MAIL_PORT' => $this->env->read('MAIL_PORT', '2525'),
                'MAIL_USERNAME' => $this->env->read('MAIL_USERNAME', ''),
                'MAIL_FROM_ADDRESS' => $this->env->read('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'MAIL_FROM_NAME' => $this->env->read('MAIL_FROM_NAME', 'VentureX ERP & CRM'),
            ],
            'configured' => $configured,
            'currentStep' => 4,
        ]);
    }

    public function storeEnvironment(Request $request): mixed
    {
        if (InstallerGuard::isInstalled()) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'app_url' => ['required', 'url', 'max:255'],
            'app_debug' => ['required', 'boolean'],
            'ai_provider' => ['nullable', 'string', 'max:32'],
            'openai_api_key' => ['nullable', 'string', 'max:512'],
            'gemini_api_key' => ['nullable', 'string', 'max:512'],
            'anthropic_api_key' => ['nullable', 'string', 'max:512'],
            'nvidia_api_key' => ['nullable', 'string', 'max:512'],
            'rapidapi_key' => ['nullable', 'string', 'max:512'],
            'mail_mailer' => ['nullable', 'string', 'max:32'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:512'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->env->assertSafe($data);
        } catch (InvalidArgumentException) {
            return back()->withInput()->withErrors([
                'app_name' => 'Submitted values must not contain newlines or control characters.',
            ]);
        }

        $map = [
            'app_name' => 'APP_NAME',
            'app_url' => 'APP_URL',
            'app_debug' => 'APP_DEBUG',
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

        $values = [];
        foreach ($map as $formKey => $envKey) {
            if (array_key_exists($formKey, $data) && $data[$formKey] !== null && $data[$formKey] !== '') {
                $values[$envKey] = $data[$formKey];
            }
        }
        $values['APP_DEBUG'] = $data['app_debug'] ? 'true' : 'false';

        try {
            $this->env->write($values);
            $this->env->write(['APP_KEY' => 'base64:'.base64_encode(random_bytes(32))]);
        } catch (Throwable $e) {
            Log::error('Installer environment write failed', ['error' => mb_substr($e->getMessage(), 0, 500)]);

            return back()->withErrors([
                'app_name' => 'Application settings could not be saved. Check .env permissions and try again.',
            ]);
        }

        // Only harmless metadata is kept in the session; API keys are never
        // flashed back to the browser.
        session(['installer_env' => [
            'app_name' => $data['app_name'],
            'app_url' => $data['app_url'],
            'app_debug' => (bool) $data['app_debug'],
        ]]);

        return redirect()->route('installer.company');
    }

    public function company(): mixed
    {
        if (InstallerGuard::isInstalled()) {
            return redirect()->route('login');
        }

        return view('installer.company', [
            'mode' => session('installer_mode', InstallerService::MODE_PRODUCTION),
            'currentStep' => 5,
        ]);
    }

    public function storeCompany(Request $request): mixed
    {
        if (InstallerGuard::isInstalled()) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:150'],
            'currency_code' => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_password' => ['required', 'string', 'max:255', 'confirmed'],
        ]);

        // UploadedFile objects are stored on disk, never in .env. Exclude the
        // file before checking scalar values for control-character injection.
        $safeValues = $data;
        unset($safeValues['logo']);

        try {
            $this->env->assertSafe($safeValues);
        } catch (InvalidArgumentException) {
            return back()->withErrors(['company_name' => 'Submitted values must not contain newlines or control characters.']);
        }

        $passwordError = $this->installer->validatePassword((string) $data['admin_password']);
        if ($passwordError !== null) {
            return back()->withInput()->withErrors(['admin_password' => $passwordError]);
        }

        if (in_array(strtolower((string) $data['admin_email']), InstallerService::DEMO_EMAILS, true)) {
            return back()->withInput()->withErrors([
                'admin_email' => 'This address is reserved for demo accounts. Choose your own email address.',
            ]);
        }

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');

            // The shared upload service adds magic-byte checks on top of the
            // Laravel image|mimes rules when it is available.
            if (class_exists(UploadSecurityService::class)) {
                try {
                    app(UploadSecurityService::class)->validateImage($logo, ['png', 'jpg', 'jpeg', 'webp']);
                } catch (ValidationException) {
                    return back()->withInput()->withErrors([
                        'logo' => 'The logo file failed security validation. Upload a valid PNG, JPG or WEBP image.',
                    ]);
                }
            }

            // Both "local" and "private" disks resolve to storage/app/private;
            // never allow the public disk for buyer branding assets.
            $disk = config('installer.logo_disk', 'private');
            if (! in_array($disk, ['local', 'private', 'branding'], true)) {
                $disk = 'private';
            }
            if (! array_key_exists($disk, config('filesystems.disks', []))) {
                $disk = 'private';
            }

            $logoPath = $logo->store('branding', $disk);
        }

        session([
            'installer_company' => [
                'company_name' => trim((string) $data['company_name']),
                'currency_code' => strtoupper((string) $data['currency_code']),
                'tax_id' => $data['tax_id'] ?? null,
                'logo_path' => $logoPath,
            ],
            'installer_admin' => [
                'admin_name' => trim((string) $data['admin_name']),
                'admin_email' => strtolower((string) $data['admin_email']),
            ],
            'installer_admin_password' => (string) $data['admin_password'],
        ]);

        return redirect()->route('installer.run');
    }

    public function run(): mixed
    {
        if (InstallerGuard::isInstalled()) {
            return redirect()->route('login');
        }

        return view('installer.install', [
            'mode' => session('installer_mode', InstallerService::MODE_PRODUCTION),
            'currentStep' => 6,
        ]);
    }

    public function execute(): mixed
    {
        if (InstallerGuard::isInstalled()) {
            return response()->json(['error' => 'Already installed', 'redirect' => route('login')]);
        }

        $mode = session('installer_mode', InstallerService::MODE_PRODUCTION);
        $company = session('installer_company');
        $admin = session('installer_admin');
        $password = session('installer_admin_password');

        if (! InstallerService::isValidMode($mode) || ! is_array($company) || ! is_array($admin) || ! is_string($password)) {
            return response()->json([
                'error' => 'Installation details are missing. Please start the wizard again.',
            ], 422);
        }

        try {
            $this->installer->seedForMode($mode);
            $this->installer->createCompanyAndSuperAdmin($company, [...$admin, 'admin_password' => $password]);
            $this->setPermissions();

            InstallerGuard::markInstalled($mode);

            // The buyer password has served its purpose; never leave it in the
            // session after the lockout is active.
            session()->forget('installer_admin_password');

            return response()->json([
                'success' => true,
                'redirect' => route('installer.complete'),
                'mode' => $mode,
            ]);
        } catch (Throwable $e) {
            Log::error('Installation failed', [
                'exception' => $e::class,
                'message' => mb_substr($e->getMessage(), 0, 2000),
            ]);

            return response()->json([
                'error' => 'Installation failed. Check the application log for details and try again.',
            ], 500);
        }
    }

    public function complete(): mixed
    {
        if (! InstallerGuard::isInstalled()) {
            return redirect()->route('installer.welcome');
        }

        return view('installer.complete', ['currentStep' => 6]);
    }

    protected function databaseErrorMessage(PDOException $e): string
    {
        $message = strtolower($e->getMessage());
        $code = (string) $e->getCode();

        // cPanel/MySQL error 1044 (42000) = user lacks CREATE privilege.
        if ($code === '1044' || str_contains($message, 'create database') || (str_contains($message, 'access denied') && str_contains($message, 'create'))) {
            return 'Your database user does not have permission to CREATE databases. In cPanel, create the database first (MySQL Databases) or grant the user ALL PRIVILEGES, then try again.';
        }

        if (str_contains($message, 'access denied') || $code === '1045') {
            return 'Database access was denied. Check the database username and password in cPanel, then try again.';
        }

        if (str_contains($message, 'unknown database') || str_contains($message, 'can\'t connect') || str_contains($message, 'server has gone away')) {
            return 'The database could not be reached. Confirm the host, port and database name, then try again.';
        }

        return 'Could not connect to the database with these settings. Verify the host, port, database name and credentials, then try again.';
    }

    protected function checkRequirements(): array
    {
        $requirements = [];

        $phpPassed = version_compare(PHP_VERSION, '8.4.0', '>=');
        $requirements[] = [
            'label' => 'PHP 8.4+',
            'test' => 'PHP >= 8.4',
            'passed' => $phpPassed,
            'detail' => 'Current: '.PHP_VERSION,
            'hint' => $phpPassed ? null : 'Ask your host to enable PHP 8.4 (cPanel: Select PHP Version).',
        ];

        foreach (static::$requiredExtensions as $ext) {
            $passed = extension_loaded($ext);
            $requirements[] = [
                'label' => "Extension: {$ext}",
                'test' => "php-{$ext}",
                'passed' => $passed,
                'detail' => $passed ? 'Loaded' : 'Not installed',
                'hint' => $passed ? null : "Enable the {$ext} extension in php.ini (cPanel: Select PHP Version → Extensions).",
            ];
        }

        $mysqlVersion = 'Not verified';
        $mysqlPassed = false;
        try {
            if (class_exists(PDO::class)) {
                $pdo = @new PDO('mysql:host=127.0.0.1', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                $mysqlVersion = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
                $mysqlPassed = true;
                $pdo = null;
            }
        } catch (PDOException $e) {
            Log::info('Installer requirement probe could not reach MySQL');
        }
        $requirements[] = [
            'label' => 'MySQL / MariaDB',
            'test' => 'MySQL 5.7+ / MariaDB 10.3+',
            'passed' => $mysqlPassed,
            'detail' => $mysqlPassed ? $mysqlVersion : 'Connection not available yet — database credentials are checked on the next step.',
            'hint' => null,
        ];

        $storageWritable = is_writable(base_path('storage')) || @chmod(base_path('storage'), 0755);
        $requirements[] = [
            'label' => 'storage/ directory writable',
            'test' => 'storage/',
            'passed' => $storageWritable,
            'detail' => $storageWritable ? 'Writable' : 'Not writable',
            'hint' => $storageWritable ? null : 'Run: chmod 755 storage (and subdirectories) or set ownership to the web user.',
        ];

        $cachePath = base_path('bootstrap/cache');
        $cacheWritable = is_writable($cachePath) || @chmod($cachePath, 0755);
        $requirements[] = [
            'label' => 'bootstrap/cache/ directory writable',
            'test' => 'bootstrap/cache/',
            'passed' => $cacheWritable,
            'detail' => $cacheWritable ? 'Writable' : 'Not writable',
            'hint' => $cacheWritable ? null : 'Run: chmod 755 bootstrap/cache.',
        ];

        $envPath = $this->env->path();
        $envWritable = (file_exists($envPath) && is_writable($envPath)) || is_writable(dirname($envPath));
        $requirements[] = [
            'label' => '.env file writable',
            'test' => '.env',
            'passed' => $envWritable,
            'detail' => $envWritable ? 'Writable' : 'Not writable',
            'hint' => $envWritable ? null : 'Copy .env.example to .env and make it writable by the web user.',
        ];

        $canGenerateKey = function_exists('random_bytes') && function_exists('base64_encode');
        $requirements[] = [
            'label' => 'APP_KEY generation',
            'test' => 'random_bytes + base64',
            'passed' => $canGenerateKey,
            'detail' => $canGenerateKey ? 'Supported' : 'Not supported',
            'hint' => $canGenerateKey ? null : 'Upgrade PHP or ask your host to enable the random_bytes extension.',
        ];

        return $requirements;
    }

    protected function setPermissions(): void
    {
        foreach ([base_path('storage'), base_path('bootstrap/cache')] as $dir) {
            if (is_dir($dir)) {
                @chmod($dir, 0755);
                $this->setPermissionsRecursive($dir);
            }
        }
    }

    protected function setPermissionsRecursive(string $dir): void
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
}
