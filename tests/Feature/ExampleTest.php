<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Database\Seeders\CompanySeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_root_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_is_redirected_from_root(): void
    {
        $user = User::first();
        if (! $user) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $this->seed([
                PermissionSeeder::class,
                RoleSeeder::class,
                CompanySeeder::class,
            ]);

            $user = User::first();
        }

        $this->actingAs($user)
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->get('/')
            ->assertRedirect(route('login'));
    }
}
