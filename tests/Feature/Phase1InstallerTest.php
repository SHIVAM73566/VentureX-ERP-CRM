<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Installer\InstallerController;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\InstallerGuard;
use App\Models\Company;
use App\Models\User;
use App\Services\Installer\EnvWriter;
use App\Services\Installer\InstallerService;
use App\Services\Installer\InstallState;
use Database\Seeders\ComprehensiveDemoSeeder;
use Database\Seeders\DemoCredentialSeeder;
use Database\Seeders\DemoDataSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use PDOException;
use ReflectionClass;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * @author Shivam Chaturvedi <chaturvedishivam179@gmail.com>
 */
class Phase1InstallerTest extends TestCase
{
    private string $flagPath = '';

    private string $envPath = '';

    /**
     * Redirect the TestCase markInstalled() call into isolated temp paths so
     * the real .env and storage/.installed are never touched by these tests.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'venturex-phase1';
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $this->flagPath = $directory.DIRECTORY_SEPARATOR.'installed-'.uniqid('', true).'.flag';
        $this->envPath = $directory.DIRECTORY_SEPARATOR.'env-'.uniqid('', true);
        file_put_contents($this->envPath, "APP_NAME=\"Test App\"\nOPENAI_API_KEY=\"dummy-key-must-not-render\"\n");

        $app['config']->set('installer.flag_path', $this->flagPath);
        $app['config']->set('installer.env_path', $this->envPath);

        return $app;
    }

    protected function tearDown(): void
    {
        if ($this->flagPath !== '' && file_exists($this->flagPath)) {
            @unlink($this->flagPath);
        }
        if ($this->envPath !== '' && file_exists($this->envPath)) {
            @unlink($this->envPath);
        }

        parent::tearDown();
    }

    public function test_all_six_installer_steps_render_when_unlocked(): void
    {
        $this->unlockInstaller();

        foreach ([
            'installer.welcome',
            'installer.mode',
            'installer.database',
            'installer.environment',
            'installer.company',
            'installer.run',
        ] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_installer_is_locked_by_flag_even_when_database_says_not_installed(): void
    {
        $this->unlockInstaller();
        file_put_contents($this->flagPath, now()->toIso8601String());

        $this->get(route('installer.welcome'))->assertRedirect(route('login'));
        $this->post(route('installer.mode.store'), ['mode' => 'demo'])->assertRedirect(route('login'));
    }

    public function test_installer_is_locked_by_database_even_when_flag_file_is_missing(): void
    {
        $this->unlockInstaller();
        app(InstallState::class)->record(mode: InstallerService::MODE_PRODUCTION, installed: true);

        $this->get(route('installer.welcome'))->assertRedirect(route('login'));
    }

    public function test_mode_step_persists_install_mode_to_env_and_database(): void
    {
        $this->unlockInstaller();

        $this->post(route('installer.mode.store'), ['mode' => InstallerService::MODE_DEMO])
            ->assertRedirect(route('installer.database'));

        $this->assertStringContainsString('INSTALL_MODE="demo"', (string) file_get_contents($this->envPath));
        $this->assertSame(InstallerService::MODE_DEMO, app(InstallState::class)->mode());
    }

    public function test_environment_step_never_renders_stored_api_keys(): void
    {
        $this->unlockInstaller();

        $this->get(route('installer.environment'))
            ->assertOk()
            ->assertDontSee('dummy-key-must-not-render')
            ->assertSee('Configured');
    }

    public function test_installer_post_without_csrf_token_returns_json_419(): void
    {
        $this->unlockInstaller();
        $this->withoutMiddleware(ForceHttps::class);
        app()->instance('env', 'production');

        $this->postJson('/install/mode', ['mode' => InstallerService::MODE_PRODUCTION])
            ->assertStatus(419)
            ->assertSee('session expired');
    }

    public function test_env_writer_quotes_values_and_rejects_newline_injection(): void
    {
        $writer = app(EnvWriter::class);

        $writer->write(['APP_NAME' => 'Acme "ERP" $Co']);
        $content = (string) file_get_contents($this->envPath);
        $this->assertStringContainsString('APP_NAME="Acme \\"ERP\\" \\$Co"', $content);

        $this->expectException(InvalidArgumentException::class);
        $writer->write(['APP_NAME' => "Bad\nINJECTED=yes"]);
    }

    public function test_env_writer_rejects_tabs_and_other_control_characters(): void
    {
        $writer = app(EnvWriter::class);

        $this->expectException(InvalidArgumentException::class);
        $writer->write(['APP_NAME' => "Bad\tValue"]);
    }

    public function test_missing_extension_hint_and_database_privilege_message_are_friendly(): void
    {
        $controller = $this->app->make(InstallerController::class);
        $reflection = new ReflectionClass($controller);

        $property = $reflection->getProperty('requiredExtensions');
        $original = $property->getValue();
        $property->setValue(null, [...$original, 'fake_missing_extension']);

        try {
            $requirements = $reflection->getMethod('checkRequirements')->invoke($controller);
            $missing = collect($requirements)->firstWhere('test', 'php-fake_missing_extension');

            $this->assertNotNull($missing);
            $this->assertFalse($missing['passed']);
            $this->assertStringContainsString('cPanel', (string) $missing['hint']);
        } finally {
            $property->setValue(null, $original);
        }

        $message = $reflection->getMethod('databaseErrorMessage')->invoke(
            $controller,
            new PDOException('CREATE DATABASE command denied to user', 1044)
        );

        $this->assertStringContainsString('cPanel', $message);
        $this->assertStringContainsString('CREATE databases', $message);
    }

    public function test_seed_chain_is_mode_aware_and_permissions_precede_super_admin_creation(): void
    {
        $reflection = new ReflectionClass(InstallerService::class);
        $systemSeeders = $reflection->getConstant('SYSTEM_SEEDERS');
        $demoSeeders = $reflection->getConstant('DEMO_SEEDERS');

        $this->assertContains(PermissionSeeder::class, $systemSeeders);
        $this->assertContains(RoleSeeder::class, $systemSeeders);
        $this->assertLessThan(
            array_search(RoleSeeder::class, $systemSeeders, true),
            array_search(PermissionSeeder::class, $systemSeeders, true)
        );

        $this->assertContains(DemoCredentialSeeder::class, $demoSeeders);
        $this->assertContains(DemoDataSeeder::class, $demoSeeders);
        $this->assertContains(ComprehensiveDemoSeeder::class, $demoSeeders);
        $this->assertNotContains(PermissionSeeder::class, $demoSeeders);
        $this->assertTrue(InstallerService::isValidMode(InstallerService::MODE_PRODUCTION));
        $this->assertTrue(InstallerService::isValidMode(InstallerService::MODE_DEMO));
    }

    public function test_production_mode_purges_demo_credentials_and_auth_artifacts(): void
    {
        $company = Company::create(['name' => 'Install Co', 'currency_code' => 'USD', 'is_active' => true]);
        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $ids = [];
        foreach (InstallerService::DEMO_EMAILS as $email) {
            $user = User::factory()->create([
                'company_id' => $company->id,
                'email' => $email,
                'password' => 'DemoPassword123!',
            ]);
            $user->assignRole($role);
            $ids[] = $user->id;

            DB::table('sessions')->insert([
                'id' => bin2hex(random_bytes(16)),
                'user_id' => $user->id,
                'payload' => 'demo-session',
                'last_activity' => now()->timestamp,
            ]);
        }

        $counts = app(InstallerService::class)->purgeDemoCredentials();

        $this->assertSame(3, $counts['users']);
        foreach (InstallerService::DEMO_EMAILS as $email) {
            $this->assertDatabaseMissing('users', ['email' => $email]);
        }
        $this->assertSame(0, DB::table('model_has_roles')->whereIn('model_id', $ids)->count());
        $this->assertSame(0, DB::table('sessions')->whereIn('user_id', $ids)->count());
    }

    public function test_company_name_is_explicit_and_never_derived_from_admin_name(): void
    {
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $company = app(InstallerService::class)->createCompanyAndSuperAdmin(
            [
                'company_name' => 'Acme Holdings Ltd',
                'currency_code' => 'inr',
                'tax_id' => 'TAX-1',
                'logo_path' => null,
            ],
            [
                'admin_name' => 'Shivam Chaturvedi',
                'admin_email' => 'owner@example.com',
                'admin_password' => 'OwnerPassword123!',
            ]
        );

        $this->assertSame('Acme Holdings Ltd', $company->name);
        $this->assertSame('INR', $company->currency_code);

        $admin = User::where('email', 'owner@example.com')->firstOrFail();
        $this->assertTrue($admin->hasRole('super_admin'));
        $this->assertSame($company->id, $admin->company_id);
    }

    public function test_company_step_accepts_a_valid_logo_in_private_storage(): void
    {
        $this->unlockInstaller();
        Storage::fake('local');

        $this->post(route('installer.mode.store'), ['mode' => InstallerService::MODE_PRODUCTION])
            ->assertRedirect(route('installer.database'));

        $this->post(route('installer.company.store'), [
            'company_name' => 'Logo Co',
            'currency_code' => 'USD',
            'tax_id' => 'TAX-LOGO',
            'logo' => UploadedFile::fake()->image('logo.png', 1, 1),
            'admin_name' => 'Logo Owner',
            'admin_email' => 'owner@example.test',
            'admin_password' => 'OwnerPassword123!',
            'admin_password_confirmation' => 'OwnerPassword123!',
        ])->assertRedirect(route('installer.run'));

        $path = session('installer_company.logo_path');
        $this->assertIsString($path);
        $this->assertStringStartsWith('branding/', $path);
        $this->assertTrue(Storage::disk('local')->exists($path));
    }

    public function test_complete_step_remains_reachable_after_lockout(): void
    {
        $this->unlockInstaller();
        InstallerGuard::markInstalled(InstallerService::MODE_PRODUCTION);

        $this->get(route('installer.complete'))
            ->assertOk()
            ->assertSee('Installation Complete');

        $this->get(route('installer.welcome'))->assertRedirect(route('login'));
    }

    private function unlockInstaller(): void
    {
        if (file_exists($this->flagPath)) {
            @unlink($this->flagPath);
        }
        app(InstallState::class)->reset();
    }
}
