<?php

namespace Tests\Feature;

use App\Models\AiRun;
use App\Models\Company;
use App\Models\Setting;
use App\Models\User;
use App\Services\UploadSecurityService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UploadSecurityTest extends TestCase
{
    protected Company $company;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('private');

        $this->company = Company::create([
            'name' => 'Upload Corp', 'is_active' => true, 'currency_code' => 'USD',
        ]);

        $this->admin = User::create([
            'name' => 'Upload Admin', 'email' => 'upload-admin@test.com',
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
    // Service-level magic byte checks
    // =====================================================================

    public function test_validate_image_accepts_a_real_png(): void
    {
        $file = UploadedFile::fake()->image('avatar.png');

        $this->assertSame('png', app(UploadSecurityService::class)->validateImage($file));
    }

    public function test_validate_image_rejects_svg_and_script_polyglots(): void
    {
        $service = app(UploadSecurityService::class);

        $svg = UploadedFile::fake()->createWithContent('logo.svg', '<svg onload="alert(1)"></svg>');
        try {
            $service->validateImage($svg, ['png', 'jpg', 'jpeg', 'webp']);
            $this->fail('SVG should have been rejected.');
        } catch (ValidationException) {
            $this->addToAssertionCount(1);
        }

        $polyglot = UploadedFile::fake()->createWithContent('logo.png', '<svg onload="alert(1)"></svg>');
        try {
            $service->validateImage($polyglot, ['png', 'jpg', 'jpeg', 'webp']);
            $this->fail('SVG content disguised as PNG should have been rejected.');
        } catch (ValidationException) {
            $this->addToAssertionCount(1);
        }
    }

    // =====================================================================
    // Branding logo
    // =====================================================================

    public function test_logo_upload_rejects_svg(): void
    {
        $file = UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>');

        $this->actingAs($this->admin)
            ->withSession($this->stepUpSession())
            ->post(route('admin.settings.branding'), ['logo' => $file])
            ->assertSessionHasErrors('logo');

        Storage::disk('public')->assertMissing('branding/logo.svg');
    }

    public function test_logo_upload_accepts_valid_raster_and_uses_uuid_name(): void
    {
        $file = UploadedFile::fake()->image('logo.png');

        $this->actingAs($this->admin)
            ->withSession($this->stepUpSession())
            ->post(route('admin.settings.branding'), ['logo' => $file])
            ->assertRedirect();

        $stored = Setting::query()->where('key', 'branding.logo_path')->value('value');
        $this->assertNotEmpty($stored);
        $this->assertMatchesRegularExpression(
            '#^branding/[^/]+/[0-9a-f-]{36}\.png$#',
            (string) $stored
        );
        Storage::disk('public')->assertExists((string) $stored);
    }

    // =====================================================================
    // Support screenshots
    // =====================================================================

    public function test_support_screenshot_is_private_and_uses_uuid_filename(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->post(route('ai.support-assistant.ask'), [
                'question' => 'How do I create a customer?',
                'screenshot' => UploadedFile::fake()->image('shot.png'),
            ]);

        $response->assertOk();

        $run = AiRun::query()->latest('id')->firstOrFail();
        $path = $run->input['screenshot'] ?? null;
        $this->assertNotEmpty($path);
        $this->assertMatchesRegularExpression(
            '#^support-screenshots/[0-9a-f-]{36}\.png$#',
            (string) $path
        );
        Storage::disk('private')->assertExists((string) $path);
        Storage::disk('public')->assertMissing((string) $path);
    }

    public function test_support_screenshot_rejects_svg(): void
    {
        $this->actingAs($this->admin)
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->post(route('ai.support-assistant.ask'), [
                'question' => 'How do I create a customer?',
                'screenshot' => UploadedFile::fake()->createWithContent('shot.svg', '<svg onload="alert(1)"></svg>'),
            ])
            ->assertSessionHasErrors('screenshot');
    }
}
