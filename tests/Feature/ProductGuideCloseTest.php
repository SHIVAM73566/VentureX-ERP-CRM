<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for the global product-guide overlay controls.
 *
 * The X button must always be present, focusable and backed by both Alpine
 * state and a native DOM fallback so a stale bundle cannot trap the user.
 */
class ProductGuideCloseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);
    }

    public function test_product_guide_has_working_open_and_close_controls(): void
    {
        $company = Company::create(['name' => 'Guide Co', 'email' => 'guide@example.test', 'is_active' => true]);
        $user = User::factory()->create(['company_id' => $company->id]);
        $user->assignRole('super_admin');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('data-guide-root', false);
        $response->assertSee('data-guide-open', false);
        $response->assertSee('data-guide-panel', false);
        $response->assertSee('data-guide-close', false);
        $response->assertSee('Close the product guide', false);
        $response->assertSee('aria-label="Close the product guide"', false);
    }

    public function test_close_control_is_not_buried_behind_the_backdrop(): void
    {
        $company = Company::create(['name' => 'Guide Co', 'email' => 'guide2@example.test', 'is_active' => true]);
        $user = User::factory()->create(['company_id' => $company->id]);
        $user->assignRole('super_admin');

        $html = $this->actingAs($user)->get(route('dashboard'))->getContent();

        $this->assertStringContainsString('data-guide-close', $html);
        $this->assertStringContainsString('relative z-50 cursor-pointer', $html);
        $this->assertStringNotContainsString('relative z-10 cursor-pointer', $html);
    }

    public function test_built_assets_include_the_native_close_fallback(): void
    {
        $asset = collect(glob(public_path('build/assets/app-*.js')))->last();

        $this->assertNotNull($asset, 'Compiled application JavaScript is missing; run npm run build.');

        $js = (string) file_get_contents($asset);
        $this->assertStringContainsString('data-guide-close', $js);
        $this->assertStringContainsString('data-guide-overlay', $js);
        $this->assertStringContainsString('data-guide-open', $js);
        $this->assertStringContainsString('data-close-overlay', $js);
    }

    public function test_every_x_and_dismiss_control_has_a_wired_close_hook(): void
    {
        $expectations = [
            'resources/views/components/product-guide.blade.php' => [
                'data-guide-close',
                'data-guide-overlay',
                'aria-label="Close the product guide"',
                '@keydown.escape.window="open = false"',
            ],
            'resources/views/components/ai/action.blade.php' => [
                'data-close-overlay',
                'aria-label="Close"',
                '@keydown.escape.window="close()"',
            ],
            'resources/views/dashboard/index.blade.php' => [
                'aria-label="Close layout customizer"',
                'data-close-overlay',
                "localStorage.setItem('vx-getting-started-banner', 'dismissed')",
                "sessionStorage.setItem('vx-upgrade-upsell-dismissed', '1')",
            ],
            'resources/views/components/upgrade-prompt.blade.php' => [
                'data-overlay',
                'data-close-overlay',
                'aria-label="Dismiss upgrade suggestion"',
            ],
            'resources/views/ai/support-assistant.blade.php' => [
                'aria-label="Remove screenshot"',
            ],
            'resources/views/sales/quotations/form.blade.php' => ['aria-label="Remove item"'],
            'resources/views/sales/orders/form.blade.php' => ['aria-label="Remove item"'],
            'resources/views/sales/invoices/form.blade.php' => ['aria-label="Remove item"'],
            'resources/views/procurement/requisitions/form.blade.php' => ['aria-label="Remove item"'],
            'resources/views/procurement/rfqs/form.blade.php' => ['aria-label="Remove item"'],
            'resources/views/procurement/orders/form.blade.php' => ['aria-label="Remove item"'],
            'resources/views/finance/journals/form.blade.php' => ['aria-label="Remove line"'],
        ];

        foreach ($expectations as $relativePath => $needles) {
            $contents = (string) file_get_contents(base_path($relativePath));

            foreach ($needles as $needle) {
                $this->assertStringContainsString($needle, $contents, $relativePath.' is missing '.$needle);
            }
        }
    }

    public function test_api_csp_never_allows_unsafe_eval(): void
    {
        $company = Company::create(['name' => 'Guide Co', 'email' => 'guide3@example.test', 'is_active' => true]);
        $user = User::factory()->create(['company_id' => $company->id]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/user');

        $response->assertOk();
        $this->assertStringNotContainsString('unsafe-eval', (string) $response->headers->get('Content-Security-Policy'));
    }
}
