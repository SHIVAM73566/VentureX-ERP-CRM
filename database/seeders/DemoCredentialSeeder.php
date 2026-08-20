<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Demo Credential Seeder — FOR TESTING ONLY
 *
 * Creates demo user accounts with known passwords for buyer testing.
 * These credentials are publicly documented and MUST be changed before production use.
 *
 * Run: php artisan db:seed --class=DemoCredentialSeeder
 */
class DemoCredentialSeeder extends Seeder
{
    protected array $demoUsers = [
        [
            'email' => 'demo_admin@example.com',
            'password' => 'Demo_Admin_2026!',
            'name' => 'Demo Administrator',
            'role' => 'super_admin',
            'warning' => 'Demo credentials — change before production use',
        ],
        [
            'email' => 'demo_manager@example.com',
            'password' => 'Demo_Manager_2026!',
            'name' => 'Demo Manager',
            'role' => 'ceo',
            'warning' => 'Demo credentials — change before production use',
        ],
        [
            'email' => 'demo_sales@example.com',
            'password' => 'Demo_Sales_2026!',
            'name' => 'Demo Sales User',
            'role' => 'sales_manager',
            'warning' => 'Demo credentials — change before production use',
        ],
    ];

    public function run(): void
    {
        $this->command->warn('⚠️  SECURITY NOTICE: Creating DEMO accounts with KNOWN passwords.');
        $this->command->warn('    These credentials are for TESTING ONLY.');
        $this->command->warn('    Change all passwords before production use.');
        $this->command->newLine();

        $company = Company::firstOrCreate(
            ['name' => 'Jain Metal Industries'],
            [
                'legal_name' => 'Jain Metal Industries Private Limited',
                'email' => 'info@jainmetal.example',
                'is_active' => true,
            ]
        );

        foreach ($this->demoUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'company_id' => $company->id,
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]
            );

            if ($user->wasRecentlyCreated) {
                $user->assignRole($userData['role']);
                $this->command->info("  ✅ Created: {$userData['email']}");
                $this->command->info("     Password: {$userData['password']}");
                $this->command->warn("     ⚠️  {$userData['warning']}");
            } else {
                if (! $user->hasRole($userData['role'])) {
                    $user->syncRoles($userData['role']);
                }
                $this->command->info("  ⏭️  Already exists: {$userData['email']}");
            }
        }

        $this->command->newLine();
        $this->command->warn('📋 Demo Credentials Summary:');
        $this->command->newLine();

        foreach ($this->demoUsers as $userData) {
            $this->command->info("  Email:    {$userData['email']}");
            $this->command->info("  Password: {$userData['password']}");
            $this->command->newLine();
        }

        $this->command->warn('🔒 IMPORTANT: Change these passwords before deploying to production!');
    }
}
