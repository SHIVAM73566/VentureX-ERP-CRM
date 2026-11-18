<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use App\Models\UserPreference;
use App\Services\CompanyContext;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    protected Company $company;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Hardening Corp', 'is_active' => true, 'currency_code' => 'USD',
        ]);

        $this->admin = User::create([
            'name' => 'Hardening Admin', 'email' => 'hardening-admin@test.com',
            'password' => Hash::make('password'), 'company_id' => $this->company->id, 'is_active' => true,
        ]);
        $this->admin->assignRole(Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']));
    }

    protected function stepUpSession(): array
    {
        return [
            'two_factor_verified_at' => now()->timestamp,
            'step_up_verified_at' => now()->timestamp,
        ];
    }

    // =====================================================================
    // Tenant isolation
    // =====================================================================

    public function test_of_company_fails_closed_without_a_company_context(): void
    {
        CompanyContext::clear();

        $this->expectException(HttpException::class);
        Customer::ofCompany()->get();
    }

    public function test_user_of_company_also_fails_closed(): void
    {
        CompanyContext::clear();

        $this->expectException(HttpException::class);
        User::ofCompany()->get();
    }

    public function test_of_company_still_works_with_an_explicit_company_id(): void
    {
        CompanyContext::clear();

        Customer::create(['company_id' => $this->company->id, 'name' => 'Scoped Customer']);

        $this->assertSame(
            1,
            Customer::ofCompany($this->company->id)->where('name', 'Scoped Customer')->count()
        );
    }

    // =====================================================================
    // Registration
    // =====================================================================

    protected function invitation(string $email, ?int $companyId = null, $expiresAt = null): string
    {
        return Crypt::encryptString(json_encode([
            'purpose' => 'registration_invite',
            'company_id' => $companyId ?? $this->company->id,
            'email' => $email,
            'expires_at' => ($expiresAt ?? now()->addDay())->toIso8601String(),
        ]));
    }

    public function test_self_registration_without_invitation_is_rejected(): void
    {
        $this->postJson(route('register.step1'), [
            'name' => 'No Invite',
            'email' => 'no-invite@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertStatus(422);

        $this->assertDatabaseMissing('users', ['email' => 'no-invite@test.com']);
    }

    public function test_tampered_or_expired_invitation_is_rejected(): void
    {
        $email = 'tampered@test.com';
        $payload = [
            'name' => 'Tampered', 'email' => $email,
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
        ];

        $this->postJson(route('register.step1'), $payload + [
            'invitation_token' => $this->invitation($email, 999999),
        ])->assertForbidden();

        $this->postJson(route('register.step1'), $payload + [
            'invitation_token' => $this->invitation($email, $this->company->id, now()->subMinute()),
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => $email]);
    }

    public function test_verified_registration_creates_a_company_bound_user_without_logging_in(): void
    {
        $email = 'invited@test.com';
        $password = 'Password123!';

        // Mail::fake() does not record Mail::raw(), so intercept the raw mail
        // call itself and inspect the generated message.
        $otp = null;
        Mail::shouldReceive('raw')->once()->withArgs(
            function (string $body, $callback) use (&$otp, $email): bool {
                if (preg_match('/\b(\d{6})\b/', $body, $matches) !== 1) {
                    return false;
                }

                $message = new Message(new SymfonyEmail());
                $callback($message);

                $recipients = array_map(
                    fn ($address) => $address->getAddress(),
                    $message->getSymfonyMessage()->getTo()
                );

                if (! in_array($email, $recipients, true)) {
                    return false;
                }

                $otp = $matches[1];

                return true;
            }
        );

        $this->postJson(route('register.step1'), [
            'name' => 'Invited User',
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
            'invitation_token' => $this->invitation($email),
        ])->assertOk()->assertJsonPath('step', 1);

        $this->assertNotNull($otp);

        $this->postJson(route('register.step2'), [
            'otp' => $otp,
            'phone' => '+15551234567',
        ])->assertOk()->assertJsonPath('step', 2);

        // Recover the phone OTP from its stored hash (only two digits are
        // masked, leaving 100 candidates) — no test-only backdoor in the app.
        $otpId = session('register_step1.otp_id');
        $this->assertNotEmpty($otpId);
        $challenge = Cache::get('registration:phone_otp:'.$otpId);
        $this->assertIsArray($challenge);

        $prefix = substr((string) $challenge['masked'], 0, 2);
        $suffix = substr((string) $challenge['masked'], -2);
        $phoneOtp = null;
        for ($i = 0; $i < 100; $i++) {
            $candidate = $prefix.str_pad((string) $i, 2, '0', STR_PAD_LEFT).$suffix;
            if (hash_equals((string) $challenge['hash'], hash_hmac('sha256', $candidate, config('app.key')))) {
                $phoneOtp = $candidate;
                break;
            }
        }
        $this->assertNotNull($phoneOtp);

        $this->postJson(route('register.step3'), ['phone_otp' => $phoneOtp])
            ->assertOk()
            ->assertJsonPath('step', 3);

        $user = User::query()->where('email', $email)->firstOrFail();
        $this->assertSame($this->company->id, $user->company_id);
        $this->assertNotNull($user->email_verified_at);
        $this->assertGuest();
    }

    public function test_registration_step3_cannot_create_a_null_company_user(): void
    {
        // A tampered session without the invitation binding is rejected.
        $this->withSession(['register_step2' => ['phone' => '+15551234567']])
            ->postJson(route('register.step3'), ['phone_otp' => '000000'])
            ->assertRedirect(route('register'));

        $this->assertDatabaseCount('users', 1); // just the seeded admin
    }

    // =====================================================================
    // Security headers / CSP
    // =====================================================================

    public function test_api_responses_carry_the_full_security_header_set_without_unsafe_eval(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/user');

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString('camera=()', (string) $response->headers->get('Permissions-Policy'));

        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringNotContainsString('unsafe-eval', $csp);
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
    }

    // =====================================================================
    // Mass assignment / key handling
    // =====================================================================

    public function test_user_preference_mass_assignment_is_fillable_only(): void
    {
        $preference = new UserPreference([
            'user_id' => $this->admin->id,
            'key' => 'theme',
            'value' => ['mode' => 'dark'],
        ]);

        $this->assertSame('theme', $preference->key);
        $this->assertNull($preference->id);

        $injected = new UserPreference(['id' => 999, 'created_at' => '2000-01-01']);
        $this->assertNull($injected->id);
        $this->assertNull($injected->created_at);
    }

    public function test_ai_key_test_flash_never_contains_any_part_of_the_key(): void
    {
        $key = 'nvapi-abcdefghijklmnopqrstuvwxyz0123456789';

        $this->actingAs($this->admin)
            ->withSession($this->stepUpSession())
            ->post(route('admin.settings.ai-key.store', 'nvidia'), ['api_key' => $key])
            ->assertRedirect();

        $this->actingAs($this->admin)
            ->withSession($this->stepUpSession())
            ->post(route('admin.settings.ai-key.test', 'nvidia'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $message = (string) session('success');
        $this->assertStringNotContainsString($key, $message);
        $this->assertStringNotContainsString(substr($key, 0, 6), $message);
        $this->assertStringNotContainsString(substr($key, -4), $message);
        $this->assertStringContainsString((string) strlen($key), $message);
    }
}
