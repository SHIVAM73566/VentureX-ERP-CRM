<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginIpRateLimitTest extends TestCase
{
    public function test_rotating_email_addresses_from_one_ip_is_throttled(): void
    {
        $ip = '203.0.113.25';
        RateLimiter::clear('login-ip:'.$ip);

        for ($attempt = 1; $attempt <= 20; $attempt++) {
            $response = $this->withServerVariables(['REMOTE_ADDR' => $ip])->post('/login', [
                'email' => "rotation-{$attempt}@example.invalid",
                'password' => 'WrongPassword123!',
            ]);

            $response->assertStatus(302);
        }

        $this->withServerVariables(['REMOTE_ADDR' => $ip])->post('/login', [
            'email' => 'rotation-21@example.invalid',
            'password' => 'WrongPassword123!',
        ])->assertStatus(429);

        RateLimiter::clear('login-ip:'.$ip);
    }
}
