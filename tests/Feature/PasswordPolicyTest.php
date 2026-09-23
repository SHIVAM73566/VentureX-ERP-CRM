<?php

namespace Tests\Feature;

use App\Models\PasswordHistory;
use App\Models\User;
use App\Services\PasswordPolicyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_history_table_is_singular_and_reuse_detection_works(): void
    {
        $user = User::factory()->create();

        PasswordPolicyService::remember($user, Hash::make('OldPass_2026!'));
        PasswordPolicyService::remember($user, Hash::make('NewerPass_2026!'));

        $this->assertDatabaseHas('password_history', ['user_id' => $user->id]);

        $this->assertTrue(PasswordPolicyService::reused($user, 'OldPass_2026!'));
        $this->assertTrue(PasswordPolicyService::reused($user, 'NewerPass_2026!'));
        $this->assertFalse(PasswordPolicyService::reused($user, 'Unknown_2026!'));
    }
}