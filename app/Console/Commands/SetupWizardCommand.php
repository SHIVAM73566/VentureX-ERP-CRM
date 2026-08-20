<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Country;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

class SetupWizardCommand extends Command
{
    protected $signature = 'setup';

    protected $description = 'Interactive setup wizard for VentureX ERP & CRM';

    private array $answers = [];

    public function handle(DatabaseManager $db): int
    {
        $this->newLine();
        $this->info('â•”â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•—');
        $this->info('â•‘           VentureX ERP & CRM â€” Setup Wizard                    â•‘');
        $this->info('â•‘   Configure your application in a few simple steps.     â•‘');
        $this->info('â•šâ•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•');
        $this->newLine();

        // â”€â”€ Step 13 (early): Generate APP_KEY if missing â”€â”€
        $this->generateAppKey();

        // â”€â”€ Step 10: Database connection test â”€â”€
        if (! $this->testDatabaseConnection($db)) {
            $this->error('Database connection failed. Please configure your .env file and try again.');

            return self::FAILURE;
        }

        // â”€â”€ Steps 1-6: Company information â”€â”€
        $this->collectCompanyInfo();

        // â”€â”€ Steps 7-9: Admin user â”€â”€
        $this->collectAdminInfo();

        // â”€â”€ Step 11: Run migrations â”€â”€
        $this->answers['run_migrations'] = $this->confirm(
            'Run database migrations now?',
            true
        );

        // â”€â”€ Step 12: Seed demo data â”€â”€
        $this->answers['seed_demo'] = $this->confirm(
            'Seed demo data (sample companies, products, orders)?',
            false
        );

        // â”€â”€ Summary â”€â”€
        $this->showSummary();

        if (! $this->confirm('Proceed with the above configuration?', true)) {
            $this->warn('Setup cancelled. No changes were made.');

            return self::SUCCESS;
        }

        // â”€â”€ Execute â”€â”€
        return $this->executeSetup();
    }

    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    //  APP Key
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    private function generateAppKey(): void
    {
        $key = config('app.key');

        if ($key && $key !== 'base64:') {
            $this->line('  APP_KEY is already set. Skipping generation.');

            return;
        }

        $this->line('  APP_KEY is not set. Generating a new key...');
        Artisan::call('key:generate', [], $this->getOutput());
        $this->info('  APP_KEY generated successfully.');
        $this->newLine();
    }

    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    //  Database connection
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    private function testDatabaseConnection(DatabaseManager $db): bool
    {
        $this->line('  Testing database connection...');

        try {
            $db->connection()->getPdo();
            $this->info('  Database connection successful.');
            $this->newLine();

            return true;
        } catch (\Exception $e) {
            $this->error('  Could not connect to database: '.$e->getMessage());
            $this->newLine();

            return false;
        }
    }

    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    //  Company information (Steps 1-6)
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    private function collectCompanyInfo(): void
    {
        $this->line('â”€â”€ Company Information â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€');
        $this->newLine();

        // Step 1: Company name
        $this->answers['company_name'] = $this->ask(
            'Company name',
            $this->answers['company_name'] ?? 'My Company'
        );

        // Step 2: Company email
        $this->answers['company_email'] = $this->anticipate(
            'Company email',
            fn (string $input) => $this->validateEmail($input),
            $this->answers['company_email'] ?? 'admin@company.com'
        );

        // Step 3: Company phone (optional)
        $this->answers['company_phone'] = $this->ask(
            'Company phone (optional)',
            ''
        );

        // Step 4: Country (from database)
        $this->answers['country_id'] = $this->selectCountry();

        // Step 5: Currency code
        $countryCurrency = Country::find($this->answers['country_id'])?->currency_code ?? 'USD';
        $this->answers['currency_code'] = strtoupper($this->ask(
            'Currency code',
            $countryCurrency
        ));

        // Step 6: Timezone
        $this->answers['timezone'] = $this->anticipate(
            'Timezone',
            fn (string $input) => collect(timezone_identifiers_list())
                ->filter(fn (string $tz) => str_contains(strtolower($tz), strtolower($input)))
                ->values()
                ->all(),
            'UTC'
        );

        $this->newLine();
    }

    private function selectCountry(): int
    {
        $countries = Country::orderBy('name')->get();

        if ($countries->isEmpty()) {
            $this->warn('  No countries found in the database. Using default (US).');
            $default = Country::where('code', 'US')->first();

            return $default?->id ?? 0;
        }

        $this->line('  Available countries:');

        $choices = $countries->map(fn (Country $c) => "{$c->name} ({$c->code})")->values()->all();
        $selected = $this->choice(
            'Country',
            $choices,
            array_search('United States (US)', $choices) ?: 0
        );

        $code = explode('(', str_replace(')', '', $selected))[1] ?? 'US';

        return Country::where('code', trim($code))->first()?->id ?? 0;
    }

    private function validateEmail(string $input): array
    {
        $suggestions = [];
        if (strpos($input, '@') === false && strlen($input) > 0) {
            $suggestions[] = $input.'@gmail.com';
            $suggestions[] = $input.'@company.com';
        }

        return $suggestions;
    }

    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    //  Admin user (Steps 7-9)
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    private function collectAdminInfo(): void
    {
        $this->line('â”€â”€ Admin Account â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€');
        $this->newLine();

        // Step 7: Admin email
        $this->answers['admin_email'] = $this->anticipate(
            'Admin email (super_admin)',
            fn (string $input) => $this->validateEmail($input),
            $this->answers['company_email'] ?? 'admin@company.com'
        );

        // Step 8: Admin name
        $this->answers['admin_name'] = $this->ask(
            'Admin name',
            'System Administrator'
        );

        // Step 9: Admin password with confirmation
        $this->answers['admin_password'] = $this->collectPassword();

        $this->newLine();
    }

    private function collectPassword(): string
    {
        while (true) {
            $password = $this->secret('Admin password (min 10 characters)');

            if (strlen($password) < 10) {
                $this->error('  Password must be at least 10 characters. Try again.');

                continue;
            }

            $confirmation = $this->secret('Confirm admin password');

            if ($password === $confirmation) {
                return $password;
            }

            $this->error('  Passwords do not match. Try again.');
        }
    }

    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    //  Summary
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    private function showSummary(): void
    {
        $this->newLine();
        $this->line('â”€â”€ Setup Summary â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€');
        $this->newLine();

        $countryName = Country::find($this->answers['country_id'])?->name ?? 'N/A';

        $rows = [
            ['Company Name', $this->answers['company_name']],
            ['Company Email', $this->answers['company_email']],
            ['Company Phone', $this->answers['company_phone'] ?: '(not set)'],
            ['Country', $countryName],
            ['Currency', $this->answers['currency_code']],
            ['Timezone', $this->answers['timezone']],
            ['Admin Email', $this->answers['admin_email']],
            ['Admin Name', $this->answers['admin_name']],
            ['Run Migrations', $this->answers['run_migrations'] ? 'Yes' : 'No'],
            ['Seed Demo Data', $this->answers['seed_demo'] ? 'Yes' : 'No'],
        ];

        $this->table(['Setting', 'Value'], $rows);
    }

    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    //  Execute setup
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    private function executeSetup(): int
    {
        $this->newLine();
        $this->line('â”€â”€ Executing Setup â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€');
        $this->newLine();

        try {
            // Step 11: Run migrations
            if ($this->answers['run_migrations']) {
                $this->line('  Running migrations...');
                $exitCode = Artisan::call('migrate', ['--force' => true], $this->getOutput());
                if ($exitCode !== 0) {
                    $this->error('  Migrations failed. Check the error output above.');

                    return self::FAILURE;
                }
                $this->info('  Migrations completed successfully.');
                $this->newLine();
            }

            // Seed master data first (countries, currencies, units, etc.)
            $this->line('  Seeding master data...');
            Artisan::call('db:seed', ['--class' => 'MasterDataSeeder', '--force' => true], $this->getOutput());
            $this->info('  Master data seeded.');
            $this->newLine();

            // Seed permissions and roles
            $this->line('  Seeding permissions and roles...');
            Artisan::call('db:seed', ['--class' => 'PermissionSeeder', '--force' => true], $this->getOutput());
            Artisan::call('db:seed', ['--class' => 'RoleSeeder', '--force' => true], $this->getOutput());
            $this->info('  Permissions and roles seeded.');
            $this->newLine();

            // Create company and admin user
            $this->line('  Creating company and admin user...');
            $this->createCompanyAndAdmin();
            $this->info('  Company and admin user created.');
            $this->newLine();

            // Seed AI skills
            $this->line('  Seeding AI skills...');
            Artisan::call('db:seed', ['--class' => 'AiSkillSeeder', '--force' => true], $this->getOutput());
            $this->info('  AI skills seeded.');
            $this->newLine();

            // Step 12: Seed demo data
            if ($this->answers['seed_demo']) {
                $this->line('  Seeding demo data...');
                Artisan::call('db:seed', ['--class' => 'DemoDataSeeder', '--force' => true], $this->getOutput());
                Artisan::call('db:seed', ['--class' => 'ComprehensiveDemoSeeder', '--force' => true], $this->getOutput());
                $this->info('  Demo data seeded.');
                $this->newLine();
            }

            // Clear caches
            $this->line('  Clearing caches...');
            Artisan::call('config:clear', [], $this->getOutput());
            Artisan::call('cache:clear', [], $this->getOutput());
            Artisan::call('route:clear', [], $this->getOutput());
            Artisan::call('view:clear', [], $this->getOutput());
            $this->info('  Caches cleared.');
            $this->newLine();

            // Final success
            $this->info('â•”â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•—');
            $this->info('â•‘          VentureX ERP & CRM â€” Setup Complete!                  â•‘');
            $this->info('â• â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•£');
            $this->info('â•‘                                                         â•‘');
            $this->info('â•‘  Admin Login:                                           â•‘');
            $this->info('â•‘    Email:    '.str_pad($this->answers['admin_email'], 43).'â•‘');
            $this->info('â•‘    Password: (the one you just set)                     â•‘');
            $this->info('â•‘                                                         â•‘');
            $this->info('â•‘  Start the dev server:                                  â•‘');
            $this->info('â•‘    php artisan serve                                    â•‘');
            $this->info('â•‘                                                         â•‘');
            $this->info('â•šâ•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•');
            $this->newLine();

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('  Setup failed: '.$e->getMessage());
            $this->newLine();
            $this->error('  You may need to re-run migrations or check your database configuration.');

            return self::FAILURE;
        }
    }

    private function createCompanyAndAdmin(): void
    {
        $company = Company::updateOrCreate(
            ['name' => $this->answers['company_name']],
            [
                'email' => $this->answers['company_email'],
                'phone' => $this->answers['company_phone'] ?: null,
                'country_id' => $this->answers['country_id'],
                'currency_code' => $this->answers['currency_code'],
                'timezone' => $this->answers['timezone'],
                'is_active' => true,
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => $this->answers['admin_email']],
            [
                'name' => $this->answers['admin_name'],
                'first_name' => explode(' ', $this->answers['admin_name'])[0] ?? 'Admin',
                'last_name' => explode(' ', $this->answers['admin_name'])[1] ?? '',
                'password' => Hash::make($this->answers['admin_password']),
                'company_id' => $company->id,
                'job_title' => 'Administrator',
                'is_active' => true,
            ]
        );
        $admin->assignRole('super_admin');
    }
}
