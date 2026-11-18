<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\ApiController;
use App\Models\Company;
use App\Models\Product;
use App\Services\CompanyContext;
use App\Services\DashboardCacheService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QueryBudgetTest extends TestCase
{
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        CompanyContext::clear();

        $this->company = Company::create([
            'name' => 'Query Budget Corp',
            'is_active' => true,
            'currency_code' => 'USD',
        ]);

        CompanyContext::set($this->company->id);
        StockService::flush();
    }

    public function test_500_stock_iterations_stay_within_query_budget(): void
    {
        for ($i = 0; $i < 100; $i++) {
            Product::create([
                'company_id' => $this->company->id,
                'sku' => sprintf('BUDGET-%03d', $i),
                'name' => "Budget Product {$i}",
                'reorder_level' => 5,
                'status' => 'active',
            ]);
        }

        StockService::flush();
        DB::flushQueryLog();
        DB::enableQueryLog();

        $products = Product::ofCompany()->get();
        for ($iteration = 0; $iteration < 500; $iteration++) {
            foreach ($products as $product) {
                $product->availableStock();
                $product->isLowStock();
            }
        }

        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(4, $queryCount, '500 iterations must reuse the grouped stock snapshot.');
    }

    public function test_dashboard_aggregates_are_cached_and_invalidated(): void
    {
        $calls = 0;
        $callback = function () use (&$calls): array {
            $calls++;

            return ['value' => $calls];
        };

        $first = DashboardCacheService::remember($this->company->id, $callback);
        $second = DashboardCacheService::remember($this->company->id, $callback);

        $this->assertSame($first, $second);
        $this->assertSame(1, $calls);
        $this->assertSame(60, DashboardCacheService::TTL_SECONDS);

        DashboardCacheService::forget($this->company->id);

        $third = DashboardCacheService::remember($this->company->id, $callback);
        $this->assertSame(2, $third['value']);
        $this->assertSame(2, $calls);
    }

    public function test_api_pagination_is_clamped_centrally(): void
    {
        $controller = new class extends ApiController
        {
            public function clamp(Request $request): int
            {
                return $this->perPage($request);
            }

            public function response(LengthAwarePaginator $paginator)
            {
                return $this->paginatedResponse($paginator);
            }
        };

        $this->assertSame(1, $controller->clamp(Request::create('/', 'GET', ['per_page' => 0])));
        $this->assertSame(100, $controller->clamp(Request::create('/', 'GET', ['per_page' => 500])));
        $this->assertSame(15, $controller->clamp(Request::create('/')));

        $items = range(1, 150);
        $paginator = new LengthAwarePaginator($items, 150, 150, 1);
        $payload = $controller->response($paginator)->getData(true);

        $this->assertSame(100, $payload['pagination']['per_page']);
        $this->assertCount(100, $payload['data']);
    }

    public function test_enterprise_load_test_500_iteration_query_budget(): void
    {
        $exitCode = Artisan::call('enterprise:load-test', ['--users' => 500]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode, $output);
        $this->assertStringContainsString('Load test: 500 staff', $output);
        $this->assertStringContainsString('0 error(s)', $output);
        $this->assertSame(1, preg_match('/(\d+) queries/', $output, $matches), $output);
        $this->assertLessThan(5000, (int) $matches[1]);
    }
}
