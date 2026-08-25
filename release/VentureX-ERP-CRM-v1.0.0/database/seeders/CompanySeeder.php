<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Country;
use App\Models\Department;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('code', 'IN')->first();

        $company = Company::updateOrCreate(
            ['name' => 'Jain Metal Industries'],
            [
                'legal_name' => 'Jain Metal Industries Private Limited',
                'email' => 'admin@jainmetal.example',
                'phone' => '+91 90000 00000',
                'tax_id' => 'GSTIN-27XXXXXX0000X0Z5',
                'industry' => 'Scrap Metal Trading & Procurement',
                'address_line1' => 'GIDC Industrial Estate, Plot 42',
                'city' => 'Jamnagar',
                'state' => 'Gujarat',
                'postal_code' => '361004',
                'country_id' => $country?->id,
                'currency_code' => 'INR',
                'timezone' => 'Asia/Kolkata',
                'is_active' => true,
            ]
        );

        $head = Branch::updateOrCreate(
            ['company_id' => $company->id, 'code' => 'HQ'],
            ['name' => 'Head Office', 'city' => 'Jamnagar', 'state' => 'Gujarat', 'is_active' => true]
        );

        $purchase = Department::updateOrCreate(
            ['company_id' => $company->id, 'code' => 'PUR'],
            ['name' => 'Purchase & Procurement', 'branch_id' => $head->id, 'is_active' => true]
        );

        $sales = Department::updateOrCreate(
            ['company_id' => $company->id, 'code' => 'SAL'],
            ['name' => 'Sales', 'branch_id' => $head->id, 'is_active' => true]
        );

        $finance = Department::updateOrCreate(
            ['company_id' => $company->id, 'code' => 'FIN'],
            ['name' => 'Finance & Accounts', 'branch_id' => $head->id, 'is_active' => true]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@jainmetal.example'],
            [
                'name' => 'System Administrator',
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'password' => Hash::make('Change_Me_'.Str::random(16)),
                'company_id' => $company->id,
                'branch_id' => $head->id,
                'department_id' => null,
                'job_title' => 'Administrator',
                'is_active' => true,
            ]
        );
        $admin->assignRole('super_admin');

        $this->createUser('Prakash Jain', 'prakash@jainmetal.example', 'ceo', $company, $head, null, 'CEO');
        $this->createUser('Ramesh Patel', 'purchase@jainmetal.example', 'purchase_manager', $company, $head, $purchase, 'Purchase Manager');
        $this->createUser('Sunil Sharma', 'procurement@jainmetal.example', 'procurement_officer', $company, $head, $purchase, 'Procurement Officer');
        $this->createUser('Anita Desai', 'accounts@jainmetal.example', 'finance_manager', $company, $head, $finance, 'Finance Manager');
        $this->createUser('Vikram Singh', 'sales@jainmetal.example', 'sales_manager', $company, $head, $sales, 'Sales Manager');

        $settings = app(SettingService::class);
        $settings->set('ai.provider', 'nvidia', ['company_id' => $company->id, 'group' => 'ai']);
        $settings->set('ai.model', env('NVIDIA_MODEL', 'nvidia/llama-3.1-nemotron-70b-instruct'), ['company_id' => $company->id, 'group' => 'ai']);
        $settings->set('ai.temperature', 0.4, ['company_id' => $company->id, 'group' => 'ai']);
        $settings->set('ai.top_p', 0.9, ['company_id' => $company->id, 'group' => 'ai']);
        $settings->set('ai.max_tokens', 2048, ['company_id' => $company->id, 'group' => 'ai']);
    }

    protected function createUser(string $name, string $email, string $role, Company $company, Branch $branch, ?Department $department, string $title): void
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('Demo_'.Str::random(16)),
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'department_id' => $department?->id,
                'job_title' => $title,
                'is_active' => true,
            ]
        );
        $user->assignRole($role);
    }
}
