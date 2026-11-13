<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Services\TwoFactorAuthService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthMfaFlowTest extends TestCase
{
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->company = Company::create([
            'name' => 'Auth Test Corp',
            'is_active' => true,
            'currency_code' => 'USD',
        ]);

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        // The dashboard/sidebar check concrete permissions; seeding them keeps
        // these tests focused on auth/MFA instead of permission drift.
        $this->seed(PermissionSeeder::class);
    }

    protected function makeUser(string $email, array $overrides = []): User
    {
        $user = User::create([
            'name' => 'Auth User',
            'email' => $email,
            'password' => Hash::make('password'),
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);

        if ($overrides !== []) {
            // two_factor_* columns are intentionally not mass-assignable.
            $user->forceFill($overrides)->save();
        }

        $user->assignRole('super_admin');

        return $user;
    }

    public function test_owner_without_mfa_signs_in_and_lands_on_dashboard(): void
    {
        config(['security.mfa.enforce' => false]);
        $this->makeUser('owner@test.com');

        $response = $this->post('/login', [
            'email' => 'owner@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_enrolled_user_is_challenged_even_when_enforcement_is_disabled(): void
    {
        config(['security.mfa.enforce' => false]);
        $user = $this->makeUser('enrolled@test.com', [
            'two_factor_secret' => Crypt::encryptString('JBSWY3DPEHPK3PXP'),
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('mfa.challenge'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_undecryptable_secret_is_reset_instead_of_locking_the_owner_out(): void
    {
        config(['security.mfa.enforce' => false]);
        $user = $this->makeUser('corrupt@test.com', [
            'two_factor_secret' => 'this-is-not-encrypted',
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user->refresh();
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_mandatory_role_is_sent_to_setup_only_when_enforced(): void
    {
        config(['security.mfa.enforce' => true]);
        $user = $this->makeUser('mandatory@test.com');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('mfa.setup'));
    }

    public function test_owner_login_is_not_blocked_by_stale_second_factor_session_keys(): void
    {
        config(['security.mfa.enforce' => false]);
        $user = $this->makeUser('stale@test.com');

        $response = $this->withSession([
            'two_factor_verified_at' => now()->subDays(3)->timestamp,
            'mfa_setup_secret' => 'STALE-SECRET',
        ])->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionMissing('mfa_setup_secret');
    }

    public function test_authenticated_user_with_enrolled_mfa_is_redirected_to_challenge_by_middleware(): void
    {
        config(['security.mfa.enforce' => false]);
        $user = $this->makeUser('middleware@test.com', [
            'two_factor_secret' => Crypt::encryptString('JBSWY3DPEHPK3PXP'),
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('mfa.challenge'));
    }

    public function test_authenticated_user_with_broken_secret_is_not_trapped(): void
    {
        config(['security.mfa.enforce' => false]);
        $user = $this->makeUser('broken-middleware@test.com', [
            'two_factor_secret' => 'broken',
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $this->assertNull($user->refresh()->two_factor_secret);
    }

    public function test_valid_totp_code_verifies_the_session(): void
    {
        config(['security.mfa.enforce' => false]);
        $secret = 'JBSWY3DPEHPK3PXP';
        $user = $this->makeUser('totp@test.com', [
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_confirmed_at' => now(),
        ]);

        $totp = app(TwoFactorAuthService::class);
        $counter = (int) floor(time() / 30);
        $code = (new \ReflectionMethod($totp, 'generateCode'))->invoke($totp, $counter, $secret);

        $response = $this->actingAs($user)->post(route('mfa.verify'), ['code' => $code]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('two_factor_verified_at');
    }
}
