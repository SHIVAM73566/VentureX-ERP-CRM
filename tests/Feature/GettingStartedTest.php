<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureTwoFactor;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GettingStartedTest extends TestCase
{
    protected Company $company;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureTwoFactor::class);

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $this->company = Company::create([
            'name' => 'Getting Started Corp',
            'is_active' => true,
            'currency_code' => 'USD',
        ]);

        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $this->admin = User::create([
            'name' => 'Guide Admin',
            'email' => 'guide-admin@test.com',
            'password' => Hash::make('password'),
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);

        $this->admin->assignRole($role);
    }

    public function test_guests_are_redirected_from_the_getting_started_page(): void
    {
        $this->get(route('getting-started'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_sees_the_product_tour_and_examples(): void
    {
        $response = $this->actingAs($this->admin)->get(route('getting-started'));

        $response->assertOk();
        $response->assertSee('Welcome to VentureX ERP', false);
        $response->assertSee('Your first 7 steps', false);
        $response->assertSee('Try these AI questions', false);
        $response->assertSee('Your onboarding checklist', false);
        $response->assertSee('How companies actually use it', false);
        $response->assertSee('Which customers have overdue invoices this month?', false);
    }

    public function test_getting_started_is_linked_from_the_sidebar(): void
    {
        $response = $this->actingAs($this->admin)->get(route('getting-started'));

        $response->assertOk();
        $response->assertSee(route('getting-started'), false);
        $response->assertSee('Getting Started', false);
    }

    public function test_dashboard_shows_the_onboarding_banner(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Take the 10-minute product tour', false);
        $response->assertSee(route('getting-started'), false);
    }

    public function test_help_center_links_to_getting_started(): void
    {
        $response = $this->actingAs($this->admin)->get(route('support.index'));

        $response->assertOk();
        $response->assertSee('Start with the product tour', false);
    }
}
