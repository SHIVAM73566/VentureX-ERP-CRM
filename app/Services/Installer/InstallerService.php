<?php

declare(strict_types=1);

namespace App\Services\Installer;

use App\Models\Company;
use App\Models\User;
use App\Services\SettingService;
use Database\Seeders\AiSkillSeeder;
use Database\Seeders\CompanySeeder;
use Database\Seeders\ComprehensiveDemoSeeder;
use Database\Seeders\DemoCredentialSeeder;
use Database\Seeders\DemoDataSeeder;
use Database\Seeders\HrDemoSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RagPipelineSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

/**
 * Mode-aware installation orchestration.
 *
 * Production installs never run the demo seeders and proactively remove the
 * publicly documented demo accounts. Demo installs run the complete chain.
 *
 * @author Shivam Chaturvedi <chaturvedishivam179@gmail.com>
 */
class InstallerService
{
    public const MODE_PRODUCTION = 'production';

    public const MODE_DEMO = 'demo';

    public const DEMO_EMAILS = [
        'demo_admin@example.com',
        'demo_manager@example.com',
        'demo_sales@example.com',
    ];

    /** Roles/permissions/master data are required by every install. */
    private const SYSTEM_SEEDERS = [
        PermissionSeeder::class,
        RoleSeeder::class,
        MasterDataSeeder::class,
        RagPipelineSeeder::class,
    ];

    /** Seeders that create sample companies, records or known credentials. */
    private const DEMO_SEEDERS = [
        CompanySeeder::class,
        DemoDataSeeder::class,
        HrDemoSeeder::class,
        AiSkillSeeder::class,
        ComprehensiveDemoSeeder::class,
        DemoCredentialSeeder::class,
    ];

    public function __construct(
        protected InstallState $state,
        protected EnvWriter $env,
    ) {}

    public static function isValidMode(?string $mode): bool
    {
        return in_array($mode, [self::MODE_PRODUCTION, self::MODE_DEMO], true);
    }

    public function persistMode(string $mode): void
    {
        if (! self::isValidMode($mode)) {
            throw new RuntimeException('Invalid installation mode.');
        }

        $this->env->write(['INSTALL_MODE' => $mode]);
        $this->state->record(mode: $mode);
    }

    public function currentMode(): string
    {
        return $this->state->mode() ?? self::MODE_PRODUCTION;
    }

    /**
     * Run migrations and the seed chain for the selected mode.
     *
     * @return array<string, string> sanitized, operator-safe step output
     */
    public function seedForMode(string $mode): array
    {
        if (! self::isValidMode($mode)) {
            throw new RuntimeException('Invalid installation mode.');
        }

        $this->runArtisan('migrate', ['--force' => true]);

        $seeders = self::SYSTEM_SEEDERS;
        if ($mode === self::MODE_DEMO) {
            $seeders = array_merge($seeders, self::DEMO_SEEDERS);
        }

        foreach ($seeders as $seeder) {
            $this->runArtisan('db:seed', ['--class' => $seeder, '--force' => true]);
        }

        if ($mode === self::MODE_PRODUCTION) {
            $this->purgeDemoCredentials();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return ['mode' => $mode, 'status' => 'seeded'];
    }

    /**
     * Delete documented demo accounts and every auth artefact they own.
     * Safe to call repeatedly.
     *
     * @return array{users: int, sessions: int, tokens: int, roles: int}
     */
    public function purgeDemoCredentials(): array
    {
        $counts = ['users' => 0, 'sessions' => 0, 'tokens' => 0, 'roles' => 0];

        $query = User::withTrashed()->whereIn('email', self::DEMO_EMAILS);
        $users = $query->get(['id', 'email']);

        if ($users->isEmpty()) {
            return $counts;
        }

        $ids = $users->pluck('id')->all();
        $counts['users'] = $users->count();

        if (Schema::hasTable('model_has_roles')) {
            $counts['roles'] += DB::table('model_has_roles')
                ->where('model_type', User::class)
                ->whereIn('model_id', $ids)
                ->delete();
        }

        if (Schema::hasTable('model_has_permissions')) {
            DB::table('model_has_permissions')
                ->where('model_type', User::class)
                ->whereIn('model_id', $ids)
                ->delete();
        }

        foreach (['sessions', 'personal_access_tokens', 'password_history', 'trusted_devices', 'user_preferences'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $column = $table === 'personal_access_tokens' ? 'tokenable_id' : 'user_id';
            if (! Schema::hasColumn($table, $column)) {
                continue;
            }

            $affected = DB::table($table)->whereIn($column, $ids)->delete();
            if ($table === 'sessions') {
                $counts['sessions'] = $affected;
            } elseif ($table === 'personal_access_tokens') {
                $counts['tokens'] = $affected;
            }
        }

        if (Schema::hasTable('personal_access_tokens')) {
            DB::table('personal_access_tokens')
                ->where('tokenable_type', User::class)
                ->whereIn('tokenable_id', $ids)
                ->delete();
        }

        User::withTrashed()->whereIn('id', $ids)->forceDelete();

        return $counts;
    }

    /**
     * Create the buyer's company (never derived from the admin's name) and the
     * super admin. Roles/permissions have already been seeded by seedForMode().
     */
    public function createCompanyAndSuperAdmin(array $company, array $admin): Company
    {
        $logoPath = $company['logo_path'] ?? null;

        $model = Company::create([
            'name' => $company['company_name'],
            'legal_name' => $company['legal_name'] ?? $company['company_name'],
            'email' => $admin['admin_email'],
            'tax_id' => $company['tax_id'] ?? null,
            'currency_code' => strtoupper($company['currency_code']),
            'is_active' => true,
        ]);

        // `logo` is intentionally assigned outside mass assignment so no
        // model file outside this phase needs to be changed.
        if ($logoPath !== null) {
            $model->logo = $logoPath;
            $model->save();
        }

        $user = User::where('email', $admin['admin_email'])->first();
        if (! $user) {
            $user = new User;
        }

        $user->fill([
            'name' => $admin['admin_name'],
            'email' => $admin['admin_email'],
            'password' => Hash::make($admin['admin_password']),
            'company_id' => $model->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->save();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $user->syncRoles(['super_admin']);

        app(SettingService::class)->set('currency.default', strtoupper($company['currency_code']), [
            'company_id' => $model->id,
            'group' => 'general',
        ]);

        return $model;
    }

    public function validatePassword(string $password): ?string
    {
        if (mb_strlen($password) < 10) {
            return 'Password must be at least 10 characters.';
        }
        if (! preg_match('/[A-Z]/', $password) || ! preg_match('/[a-z]/', $password)
            || ! preg_match('/\d/', $password) || ! preg_match('/[^A-Za-z0-9]/', $password)) {
            return 'Password must contain uppercase, lowercase, numbers, and special characters.';
        }

        return null;
    }

    /**
     * Run an Artisan command with details in the log and a generic exception
     * to the browser. Command output may contain DSN/credential fragments.
     */
    protected function runArtisan(string $command, array $parameters): void
    {
        try {
            $exit = Artisan::call($command, $parameters);
            $output = (string) Artisan::output();

            if ($exit !== 0) {
                Log::error('Installer command failed', [
                    'command' => $command,
                    'exit' => $exit,
                    'output' => mb_substr($output, 0, 2000),
                ]);

                throw new RuntimeException('A database setup step failed. Check the application log for details.');
            }
        } catch (Throwable $e) {
            if ($e instanceof RuntimeException && str_starts_with($e->getMessage(), 'A database setup step')) {
                throw $e;
            }

            Log::error('Installer command exception', [
                'command' => $command,
                'exception' => $e::class,
                'message' => mb_substr($e->getMessage(), 0, 2000),
            ]);

            throw new RuntimeException('A database setup step failed. Check the application log for details.');
        }
    }
}
