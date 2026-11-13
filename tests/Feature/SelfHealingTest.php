<?php

namespace Tests\Feature;

use App\Models\ErrorReport;
use App\Services\SelfHealingService;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class SelfHealingTest extends TestCase
{
    public function test_capture_records_error_report_and_audit_log(): void
    {
        $message = 'Self healing capture test '.uniqid();
        $result = app(SelfHealingService::class)->capture(new RuntimeException($message), request());

        $this->assertSame(500, $result['status']);
        $this->assertNotEmpty($result['fingerprint']);
        $this->assertNotNull($result['report_id']);

        $report = ErrorReport::query()->where('error_message', $message)->first();
        $this->assertNotNull($report);
        $this->assertSame('exception', $report->error_type);
        $this->assertSame(500, (int) $report->http_status);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'self_healing',
            'module' => 'system',
        ]);
    }

    public function test_request_data_is_sanitised(): void
    {
        $message = 'Self healing sanitise test '.uniqid();
        $request = Request::create('/billing', 'POST', [
            'email' => 'owner@example.test',
            'password' => 'SuperSecret123!',
            'api_key' => 'sk-live-should-not-be-stored',
        ]);

        app(SelfHealingService::class)->capture(new RuntimeException($message), $request);

        $report = ErrorReport::query()->where('error_message', $message)->firstOrFail();
        $data = $report->request_data ?? [];

        $this->assertSame('[REDACTED]', $data['password'] ?? null);
        $this->assertSame('[REDACTED]', $data['api_key'] ?? null);
        $this->assertSame('owner@example.test', $data['email'] ?? null);
    }

    public function test_middleware_renders_safe_fallback_outside_debug(): void
    {
        config(['app.debug' => false, 'self-healing.enabled' => true, 'self-healing.render_fallback' => true]);

        Route::middleware('web')->get('/__self_healing_probe', function () {
            throw new RuntimeException('Probe failure '.uniqid());
        });

        $response = $this->get('/__self_healing_probe');

        $response->assertStatus(500);
        $response->assertSee('We recovered this page');
    }

    public function test_middleware_rethrows_validation_exceptions(): void
    {
        config(['app.debug' => false, 'self-healing.enabled' => true, 'self-healing.render_fallback' => true]);

        Route::middleware('web')->get('/__self_healing_validation', function () {
            throw ValidationException::withMessages(['email' => 'Invalid email']);
        });

        $response = $this->get('/__self_healing_validation');

        // Native Laravel redirect-back behaviour, not the self-healed page.
        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }

    public function test_csrf_exceptions_are_never_swallowed(): void
    {
        $service = app(SelfHealingService::class);

        $this->assertTrue($service->mustRethrow(new TokenMismatchException('CSRF')));
        $this->assertSame(419, $service->statusFor(new TokenMismatchException('CSRF')));
        $this->assertFalse($service->mustRethrow(new RuntimeException('boom')));
        $this->assertSame(500, $service->statusFor(new RuntimeException('boom')));
    }

    public function test_duplicate_errors_are_deduplicated_into_one_report(): void
    {
        $message = 'Self healing dedupe test '.uniqid();
        $service = app(SelfHealingService::class);
        $exception = new RuntimeException($message);

        $service->capture($exception, request());
        $service->capture($exception, request());

        $reports = ErrorReport::query()->where('error_message', $message)->get();

        $this->assertCount(1, $reports);
        $this->assertGreaterThanOrEqual(2, (int) $reports->first()->occurrence_count);
    }
}
