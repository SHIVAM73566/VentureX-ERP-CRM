<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\CompanyContext;
use App\Services\NumberGenerator;
use App\Services\StockService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EnterpriseConcurrencyTest extends TestCase
{
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        CompanyContext::clear();

        $this->company = Company::create([
            'name' => 'Concurrency Test Corp',
            'is_active' => true,
            'currency_code' => 'USD',
        ]);

        CompanyContext::set($this->company->id);
        StockService::flush();
    }

    public function test_grouped_stock_lookup_avoids_n_plus_one_queries(): void
    {
        $products = [];

        for ($i = 0; $i < 24; $i++) {
            $product = Product::create([
                'company_id' => $this->company->id,
                'sku' => sprintf('CONC-%03d', $i),
                'name' => "Concurrency Product {$i}",
                'reorder_level' => 5,
                'status' => 'active',
            ]);
            $products[] = $product;

            StockMovement::create([
                'company_id' => $this->company->id,
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => 20,
            ]);
            StockMovement::create([
                'company_id' => $this->company->id,
                'product_id' => $product->id,
                'type' => 'out',
                'quantity' => 3,
            ]);
        }

        StockService::flush();
        DB::flushQueryLog();
        DB::enableQueryLog();

        $loaded = Product::ofCompany()->get();
        foreach ($loaded as $product) {
            $this->assertEquals(17.0, $product->availableStock());
            $this->assertFalse($product->isLowStock());
        }

        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        // One products query + one grouped stock query (+ tolerated overhead).
        $this->assertLessThanOrEqual(4, $queryCount, 'Stock reads must not issue one query per product.');
        $this->assertCount(24, $products);
    }

    public function test_direct_stock_writes_invalidate_the_grouped_snapshot(): void
    {
        $product = Product::create([
            'company_id' => $this->company->id,
            'sku' => 'CONC-INVALIDATE',
            'name' => 'Invalidation Product',
            'reorder_level' => 10,
            'status' => 'active',
        ]);

        StockMovement::create([
            'company_id' => $this->company->id,
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 12,
        ]);

        $this->assertEquals(12.0, $product->fresh()->availableStock());

        StockMovement::create([
            'company_id' => $this->company->id,
            'product_id' => $product->id,
            'type' => 'out',
            'quantity' => 5,
        ]);

        $this->assertEquals(7.0, $product->fresh()->availableStock());
        $this->assertTrue($product->fresh()->isLowStock());
    }

    public function test_parallel_number_attempts_yield_unique_numbers(): void
    {
        $attempts = 30;
        $numbers = [];

        // Each attempt runs its own transaction/lock, mirroring concurrent
        // requests. SQLite serialises writes, MySQL uses FOR UPDATE.
        for ($i = 0; $i < $attempts; $i++) {
            $numbers[] = DB::transaction(fn () => NumberGenerator::next('INV', 'invoices'));
        }

        $this->assertCount($attempts, array_unique($numbers));
        $this->assertMatchesRegularExpression('/^INV-\d{8}-\d{4}$/', $numbers[0]);
        $this->assertSame(1, DB::table('document_sequences')->count());
        $this->assertEquals($attempts, DB::table('document_sequences')->value('current_value'));
    }

    public function test_number_sequences_are_isolated_per_company(): void
    {
        $first = NumberGenerator::next('PO', 'purchase_orders');

        $other = Company::create([
            'name' => 'Other Concurrency Corp',
            'is_active' => true,
            'currency_code' => 'EUR',
        ]);
        CompanyContext::set($other->id);

        $second = NumberGenerator::next('PO', 'purchase_orders');

        // Sequences are company-scoped: both tenants start at 0001 but have
        // independent counters in the database.
        $this->assertStringEndsWith('-0001', $first);
        $this->assertStringEndsWith('-0001', $second);
        $this->assertSame(2, DB::table('document_sequences')->count());
        $this->assertEquals(1, DB::table('document_sequences')->where('company_id', $this->company->id)->value('current_value'));
        $this->assertEquals(1, DB::table('document_sequences')->where('company_id', $other->id)->value('current_value'));
    }

    public function test_enterprise_load_test_command_runs_for_30_users(): void
    {
        $exitCode = Artisan::call('enterprise:load-test', [
            '--users' => 30,
            '--company' => $this->company->id,
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode, $output);
        $this->assertStringContainsString('Load test: 30 staff', $output);
        $this->assertStringContainsString('0 error(s)', $output);
        $this->assertStringContainsString('Inventory', $output);
        $this->assertStringContainsString('Finance', $output);
    }
}
