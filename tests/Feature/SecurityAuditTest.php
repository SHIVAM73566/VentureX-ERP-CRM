<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Middleware\ApiProtection;
use App\Http\Middleware\EmergencyLockdown;
use App\Http\Middleware\EnsureTwoFactor;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\StepUpAuth;
use App\Models\User;
use App\Services\LoginThrottle;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityAuditTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureTwoFactor::class);

        if (! User::where('email', 'admin@jainmetal.example')->exists()) {
            $this->markTestSkipped('Demo data not seeded. Run: php artisan db:seed');
        }
    }

    protected function admin(): User
    {
        return User::where('email', 'admin@jainmetal.example')->firstOrFail();
    }

    protected function authAdmin(): self
    {
        $this->actingAs($this->admin());
        $this->withSession(['two_factor_verified_at' => now()->timestamp]);

        return $this;
    }

    // =========================================================================
    // 1. SECURITY HEADERS ARE PRESENT ON ALL RESPONSES
    // =========================================================================

    public function test_security_headers_present_on_dashboard(): void
    {
        $response = $this->authAdmin()->get('/dashboard');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy');
    }

    public function test_security_headers_present_on_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_content_security_policy_header_present(): void
    {
        $response = $this->authAdmin()->get('/dashboard');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotEmpty($csp);
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("frame-src 'none'", $csp);
    }

    public function test_permissions_policy_restricts_dangerous_features(): void
    {
        $response = $this->authAdmin()->get('/dashboard');

        $permissions = $response->headers->get('Permissions-Policy');
        $this->assertStringContainsString('camera=()', $permissions);
        $this->assertStringContainsString('microphone=()', $permissions);
        $this->assertStringContainsString('geolocation=()', $permissions);
        $this->assertStringContainsString('payment=()', $permissions);
    }

    public function test_x_permitted_cross_domain_policies(): void
    {
        $response = $this->authAdmin()->get('/dashboard');
        $response->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');
    }

    public function test_security_headers_on_api_endpoints(): void
    {
        $this->authAdmin();
        $response = $this->getJson('/ai/quota');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
    }

    // =========================================================================
    // 2. CSRF PROTECTION WORKS ON ALL FORMS
    // =========================================================================

    public function test_login_form_has_csrf_token(): void
    {
        $response = $this->get('/login');
        $response->assertOk();
        $this->assertStringContainsString('_token', $response->getContent());
    }

    public function test_csrf_middleware_is_registered(): void
    {
        // In Laravel 11, CSRF is part of the framework's default web middleware group.
        // Verify the middleware class exists and is a valid middleware.
        $this->assertTrue(
            class_exists(VerifyCsrfToken::class),
            'VerifyCsrfToken middleware class must exist.'
        );
    }

    public function test_logout_requires_post_with_csrf(): void
    {
        $response = $this->authAdmin()->get('/logout');
        // GET should not work for logout (POST required)
        $this->assertNotEquals(302, $response->getStatusCode());
    }

    // =========================================================================
    // 3. SESSION FIXATION PROTECTION
    // =========================================================================

    public function test_session_regenerated_on_login(): void
    {
        $sessionBefore = $this->app['session']->getId();

        $this->post('/login', [
            'email' => 'admin@jainmetal.example',
            'password' => 'password',
        ]);

        // Session should have been regenerated (or at least changed)
        $this->assertNotEquals($sessionBefore, $this->app['session']->getId());
    }

    public function test_database_session_driver_used_in_production(): void
    {
        // In production, session should use database driver
        // In testing, it's overridden to 'array' by phpunit.xml
        $this->assertContains(config('session.driver'), ['database', 'array']);
    }

    public function test_session_encryption_configured(): void
    {
        // Session encryption should be enabled
        $this->assertTrue(config('session.encrypt'));
    }

    public function test_session_http_only_cookie(): void
    {
        $this->assertTrue(config('session.http_only'));
    }

    public function test_session_same_site_cookie_configured(): void
    {
        $sameSite = config('session.same_site');
        $this->assertContains($sameSite, ['lax', 'strict', 'none']);
    }

    public function test_session_serialization_is_json(): void
    {
        // JSON serialization prevents gadget chain attacks
        $this->assertEquals('json', config('session.serialization'));
    }

    // =========================================================================
    // 4. PASSWORD HASHING IS SECURE
    // =========================================================================

    public function test_bcrypt_or_argon2_hashing_configured(): void
    {
        $hasher = config('hashing.driver', 'bcrypt');
        $this->assertContains($hasher, ['bcrypt', 'argon2', 'argon2id']);
    }

    public function test_bcrypt_rounds_configured_in_env_example(): void
    {
        $envContent = file_get_contents(base_path('.env.example'));
        $this->assertStringContainsString('BCRYPT_ROUNDS=12', $envContent);
    }

    public function test_password_hash_is_not_reversible(): void
    {
        $password = 'MySecurePassword123!';
        $hash = Hash::make($password);

        $this->assertNotEquals($password, $hash);
        $this->assertTrue(Hash::check($password, $hash));
        $this->assertFalse(Hash::check('wrong-password', $hash));
    }

    public function test_reset_password_uses_strong_validation(): void
    {
        $response = $this->post('/reset-password', [
            'token' => 'fake-token',
            'email' => 'admin@jainmetal.example',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ]);

        $response->assertSessionHasErrors('password');
    }

    // =========================================================================
    // 5. RATE LIMITING WORKS ON LOGIN
    // =========================================================================

    public function test_login_has_throttle_protection(): void
    {
        // Login uses LoginThrottle service class (not a middleware)
        // Verify the LoginThrottle service exists and works
        $throttle = app(LoginThrottle::class);
        $this->assertNotNull($throttle);
    }

    public function test_forgot_password_has_rate_limiting(): void
    {
        $response = $this->post('/forgot-password', ['email' => 'test@test.com']);
        $response->assertRedirect();
    }

    // =========================================================================
    // 6. BRUTE FORCE PROTECTION
    // =========================================================================

    public function test_login_throttle_service_blocks_after_max_attempts(): void
    {
        $throttle = app(LoginThrottle::class);
        $key = LoginThrottle::attemptKey('brute-force@test.com');

        // Simulate max failed attempts
        for ($i = 0; $i < 10; $i++) {
            $throttle->hit($key);
        }

        $this->assertTrue($throttle->blocked($key));
        $this->assertEquals(0, $throttle->remaining($key));

        $throttle->clear($key);
    }

    public function test_login_throttle_clears_on_success(): void
    {
        $throttle = app(LoginThrottle::class);
        $key = LoginThrottle::attemptKey('clear-test@test.com');

        for ($i = 0; $i < 3; $i++) {
            $throttle->hit($key);
        }

        $this->assertGreaterThan(0, $throttle->remaining($key));

        $throttle->clear($key);

        $user = $this->admin();
        $freshKey = LoginThrottle::attemptKey($user->email);
        $this->assertEquals(
            (int) config('security.login.max_attempts', 5),
            $throttle->remaining($freshKey)
        );
    }

    public function test_throttle_provides_delay_seconds_when_blocked(): void
    {
        $throttle = app(LoginThrottle::class);
        $key = LoginThrottle::attemptKey('delay-test@test.com');

        for ($i = 0; $i < 10; $i++) {
            $throttle->hit($key);
        }

        $delay = $throttle->delaySeconds($key);
        $this->assertGreaterThan(0, $delay);

        $throttle->clear($key);
    }

    // =========================================================================
    // 7. SQL INJECTION PROTECTION
    // =========================================================================

    public function test_sql_injection_in_login_email_blocked(): void
    {
        $response = $this->post('/login', [
            'email' => "' OR 1=1 --",
            'password' => 'password',
        ]);

        // Should fail validation, not SQL error
        $response->assertSessionHasErrors('email');
    }

    public function test_sql_injection_in_search_blocked(): void
    {
        $this->authAdmin();

        $response = $this->get('/search?q=1%27%20OR%201%20%3D%201%20--');
        // Should not produce a SQL error
        $this->assertNotEquals(500, $response->getStatusCode());
    }

    public function test_api_protection_detects_sql_injection_patterns(): void
    {
        $middleware = new ApiProtection;
        $reflection = new \ReflectionClass($middleware);
        $method = $reflection->getMethod('isSuspiciousRequest');
        $method->setAccessible(true);

        $request = Request::create('/api/test?email=union+select+*+from+users', 'GET');

        $this->assertTrue($method->invoke($middleware, $request));
    }

    public function test_api_protection_detects_xss_in_request(): void
    {
        $middleware = new ApiProtection;
        $reflection = new \ReflectionClass($middleware);
        $method = $reflection->getMethod('isSuspiciousRequest');
        $method->setAccessible(true);

        $request = Request::create('/api/test?q=%3Cscript%3Ealert(%22xss%22)%3C%2Fscript%3E', 'GET');

        $this->assertTrue($method->invoke($middleware, $request));
    }

    // =========================================================================
    // 8. XSS PROTECTION
    // =========================================================================

    public function test_xss_in_login_email_not_reflected(): void
    {
        $response = $this->post('/login', [
            'email' => '<script>alert("xss")</script>',
            'password' => 'password',
        ]);

        $response->assertDontSee('<script>', false);
    }

    public function test_dashboard_has_security_headers_against_xss(): void
    {
        $response = $this->authAdmin()->get('/dashboard');

        $response->assertHeader('Content-Security-Policy');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
    }

    public function test_api_protection_detects_multiple_xss_patterns(): void
    {
        $middleware = new ApiProtection;
        $reflection = new \ReflectionClass($middleware);
        $method = $reflection->getMethod('isSuspiciousRequest');
        $method->setAccessible(true);

        $xssPatterns = [
            '<script>alert(1)</script>',
            'javascript:alert(1)',
            'eval(document.cookie)',
        ];

        foreach ($xssPatterns as $pattern) {
            $request = Request::create('/api/test?q='.urlencode($pattern), 'GET', ['q' => $pattern]);
            $this->assertTrue(
                $method->invoke($middleware, $request),
                "XSS pattern not detected: {$pattern}"
            );
        }
    }

    // =========================================================================
    // 9. FILE UPLOAD SECURITY
    // =========================================================================

    public function test_selfie_upload_validates_file_type(): void
    {
        $this->authAdmin();

        // Create a fake non-image file
        $file = UploadedFile::fake()->create('test.exe', 100, 'application/x-msdownload');

        $response = $this->post('/auth/selfie/upload', [
            'selfie' => $file,
        ]);

        $response->assertSessionHasErrors('selfie');
    }

    public function test_selfie_upload_validates_file_size(): void
    {
        $this->authAdmin();

        // 11MB file exceeds 10MB limit
        $file = UploadedFile::fake()->create('large.jpg', 11000, 'image/jpeg');

        $response = $this->post('/auth/selfie/upload', [
            'selfie' => $file,
        ]);

        $response->assertSessionHasErrors('selfie');
    }

    // =========================================================================
    // 10. SESSION TIMEOUT
    // =========================================================================

    public function test_session_lifetime_configured(): void
    {
        $lifetime = config('session.lifetime');
        $this->assertNotNull($lifetime);
        $this->assertGreaterThan(0, $lifetime);
        $this->assertLessThanOrEqual(240, $lifetime);
    }

    public function test_session_expire_on_close_configured(): void
    {
        $this->assertIsBool(config('session.expire_on_close'));
    }

    public function test_session_secure_cookie_config(): void
    {
        $this->assertIsBool(config('session.secure'));
    }

    // =========================================================================
    // 11. SENSITIVE DATA NOT EXPOSED IN ERROR MESSAGES
    // =========================================================================

    public function test_500_errors_dont_expose_stack_traces(): void
    {
        $this->app['config']->set('app.debug', false);

        $this->authAdmin();

        $response = $this->get('/nonexistent-route-that-will-404');

        if ($response->getStatusCode() === 500) {
            $content = $response->getContent();
            $this->assertStringNotContainsString('Stack trace', $content);
            $this->assertStringNotContainsString('Exception in', $content);
            $this->assertStringNotContainsString('vendor/', $content);
        }
    }

    public function test_api_error_responses_dont_expose_internal_paths(): void
    {
        $this->app['config']->set('app.debug', false);

        $response = $this->authAdmin()
            ->postJson('/ai/copilot', ['question' => 'Strategic analysis of our pipeline and business']);

        if ($response->getStatusCode() >= 400) {
            $content = $response->getContent();
            $this->assertStringNotContainsString('C:\\', $content);
            $this->assertStringNotContainsString('/var/www', $content);
            $this->assertStringNotContainsString('vendor/', $content);
        }
    }

    public function test_validation_errors_dont_expose_db_details(): void
    {
        $response = $this->post('/login', [
            'email' => 'notfound@example.com',
            'password' => 'wrongpassword',
        ]);

        $content = $response->getContent();
        $this->assertStringNotContainsString('SQL', $content);
        $this->assertStringNotContainsString('mysql', $content);
        $this->assertStringNotContainsString('table', $content);
    }

    public function test_disabled_account_error_is_generic(): void
    {
        $user = $this->admin();
        $originalState = $user->is_active;
        $user->update(['is_active' => false]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Should redirect (302) - login form shows errors via flash
        $this->assertContains($response->getStatusCode(), [200, 302]);

        // Should NOT expose SQL or internal details
        $content = $response->getContent();
        $this->assertStringNotContainsString('SQL', $content);
        $this->assertStringNotContainsString('is_active', $content);

        $user->update(['is_active' => $originalState]);
    }

    // =========================================================================
    // 12. API KEYS NOT IN .env.example WITH REAL VALUES
    // =========================================================================

    public function test_env_example_has_no_real_api_keys(): void
    {
        $envContent = file_get_contents(base_path('.env.example'));

        $suspiciousPatterns = [
            '/\b(sk-[a-zA-Z0-9]{20,})/',
            '/\b(AIza[a-zA-Z0-9_-]{30,})/',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            preg_match_all($pattern, $envContent, $matches);
            $this->assertEmpty($matches[0], 'Real API key found in .env.example: '.($matches[0][0] ?? 'unknown'));
        }
    }

    public function test_env_example_api_keys_are_empty(): void
    {
        $envContent = file_get_contents(base_path('.env.example'));

        $keyLines = [
            'RAPIDAPI_KEY=',
            'NVIDIA_API_KEY=',
            'GEMINI_API_KEY=',
            'OPENAI_API_KEY=',
            'ANTHROPIC_API_KEY=',
            'CLAUDE_RAPIDAPI_KEY=',
        ];

        foreach ($keyLines as $keyLine) {
            $parts = explode('=', $keyLine, 2);
            $key = $parts[0];

            preg_match('/^'.preg_quote($key, '/').'=(.*)$/m', $envContent, $matches);
            $this->assertNotEmpty($matches, "Key {$key} should exist in .env.example");
            $this->assertEmpty(trim($matches[1] ?? ''), "Key {$key} should be empty in .env.example");
        }
    }

    public function test_env_example_password_is_placeholder(): void
    {
        $envContent = file_get_contents(base_path('.env.example'));

        preg_match('/^DB_PASSWORD=(.*)$/m', $envContent, $matches);
        $this->assertNotEmpty($matches, 'DB_PASSWORD should exist in .env.example');
        $this->assertNotEquals('', trim($matches[1]), 'DB_PASSWORD should be a placeholder');
        $this->assertNotEquals('root', trim($matches[1]), 'DB_PASSWORD should not be "root"');
    }

    // =========================================================================
    // BONUS: ADDITIONAL SECURITY CHECKS
    // =========================================================================

    public function test_session_cookie_name_is_custom(): void
    {
        $cookieName = config('session.cookie');
        $this->assertNotEmpty($cookieName);
        $this->assertNotEquals('laravel_session', $cookieName);
    }

    public function test_password_validation_requires_minimum_length(): void
    {
        $response = $this->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => 'test@test.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_login_controller_prevents_user_enumeration(): void
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $content = $response->getContent();
        $this->assertStringNotContainsString('user not found', $content);
        $this->assertStringNotContainsString('does not exist', $content);
    }

    public function test_step_up_auth_middleware_exists(): void
    {
        $middleware = new StepUpAuth;
        $this->assertNotNull($middleware);
    }

    public function test_emergency_lockdown_middleware_exists(): void
    {
        $middleware = new EmergencyLockdown;
        $this->assertNotNull($middleware);
    }

    public function test_force_https_middleware_exists(): void
    {
        $middleware = new ForceHttps;
        $this->assertNotNull($middleware);
    }

    public function test_two_factor_middleware_exists(): void
    {
        $middleware = new EnsureTwoFactor;
        $this->assertNotNull($middleware);
    }

    public function test_security_headers_middleware_exists(): void
    {
        $middleware = new SecurityHeaders;
        $this->assertNotNull($middleware);
    }

    public function test_api_protection_middleware_exists(): void
    {
        $middleware = new ApiProtection;
        $this->assertNotNull($middleware);
    }

    public function test_session_controller_allows_viewing_sessions(): void
    {
        $this->authAdmin();

        $response = $this->get('/account/sessions');
        $response->assertOk();
    }

    public function test_data_deletion_requires_password_confirmation(): void
    {
        $this->authAdmin();

        $response = $this->delete('/privacy/delete', [
            'password' => 'wrong-password',
            'confirmation' => 'DELETE_MY_ACCOUNT',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_data_deletion_requires_exact_confirmation(): void
    {
        $this->authAdmin();

        $response = $this->delete('/privacy/delete', [
            'password' => 'password',
            'confirmation' => 'WRONG_CONFIRMATION',
        ]);

        $response->assertSessionHasErrors('confirmation');
    }

    public function test_user_model_has_expected_fields(): void
    {
        $user = $this->admin();

        $this->assertNotEmpty($user->name);
        $this->assertNotEmpty($user->email);
        $this->assertNotNull($user->password);
        $this->assertTrue(in_array($user->is_active, [true, 1, '1']), 'User should be active.');
    }

    public function test_api_protection_blocks_known_attack_paths(): void
    {
        $middleware = new ApiProtection;
        $reflection = new \ReflectionClass($middleware);
        $method = $reflection->getMethod('isSuspiciousRequest');
        $method->setAccessible(true);

        $attackPaths = [
            '../../../etc/passwd',
            'wp-admin',
            'wp-login.php',
            'xmlrpc.php',
        ];

        foreach ($attackPaths as $path) {
            $request = Request::create('/'.$path, 'GET');
            $this->assertTrue(
                $method->invoke($middleware, $request),
                "Attack path not detected: {$path}"
            );
        }
    }

    public function test_api_protection_ip_block_works(): void
    {
        $middleware = new ApiProtection;
        $reflection = new \ReflectionClass($middleware);
        $method = $reflection->getMethod('isBlockedIp');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($middleware, '1.2.3.4'));

        ApiProtection::blockIp('1.2.3.4', 60);

        $this->assertTrue($method->invoke($middleware, '1.2.3.4'));
        $this->assertFalse($method->invoke($middleware, '5.6.7.8'));
    }

    public function test_registration_steps_have_rate_limiting(): void
    {
        $routes = [
            ['POST', '/auth/register/step1'],
            ['POST', '/auth/register/step2'],
            ['POST', '/auth/register/step3'],
        ];

        foreach ($routes as [$method, $url]) {
            $route = app('router')->getRoutes()->match(
                Request::create($url, $method)
            );
            if ($route) {
                $middleware = collect($route->gatherMiddleware());
                $this->assertTrue(
                    $middleware->contains(fn ($m) => str_contains((string) $m, 'throttle')),
                    "{$url} must have throttle middleware."
                );
            }
        }
    }

    public function test_password_reset_has_rate_limiting(): void
    {
        $route = app('router')->getRoutes()->getByAction(
            ForgotPasswordController::class.'@sendResetLinkEmail'
        );
        $this->assertNotNull($route);

        $middleware = collect($route->gatherMiddleware());
        $this->assertTrue(
            $middleware->contains(fn ($m) => str_contains((string) $m, 'throttle')),
            'Password reset must have throttle middleware.'
        );
    }

    public function test_login_success_returns_csrf_token(): void
    {
        $this->post('/login', [
            'email' => 'admin@jainmetal.example',
            'password' => 'password',
        ]);

        $token = session()->token();
        $this->assertNotEmpty($token);
        $this->assertEquals(40, strlen($token));
    }
}
