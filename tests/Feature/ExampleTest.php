<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
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
            $this->markTestSkipped('No users in database');
        }

        $this->actingAs($user)
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->get('/')
            ->assertRedirect(route('dashboard'));
    }
}
