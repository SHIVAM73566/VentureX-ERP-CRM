<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\EnsureTwoFactor;
use App\Models\Account;
use App\Models\Company;
use App\Models\Import;
use App\Models\ImportTemplate;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\CompanyContext;
use App\Services\Import\ImportProcessor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;
use ZipArchive;

/**
 * @author Shivam Chaturvedi <chaturvedishivam179@gmail.com>
 */
class Phase1ImporterTest extends TestCase
{
    private Company $company;

    private User $admin;

    /** @var list<string> */
    private array $tempFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(EnsureTwoFactor::class);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->company = Company::create([
            'name' => 'Import Phase1 Co',
            'currency_code' => 'USD',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create(['company_id' => $this->company->id]);
        $this->admin->assignRole(Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']));

        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $path) {
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        parent::tearDown();
    }

    public function test_create_page_links_all_five_templates_and_demo_panel(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.imports.create'))
            ->assertOk()
            ->assertSee('Customers CSV template')
            ->assertSee('Suppliers CSV template')
            ->assertSee('Leads CSV template')
            ->assertSee('Products / Inventory CSV template')
            ->assertSee('Opening Balances CSV template')
            ->assertSee(route('admin.demo-data.index'));
    }

    public function test_template_download_returns_csv_headers_for_opening_balances(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.imports.templates.download', 'opening_balances'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertSee('account_code')
            ->assertSee('as_of_date')
            ->assertSee('debit')
            ->assertSee('credit')
            ->assertSee('currency_code');
    }

    public function test_csv_upload_is_stored_privately_and_json_txt_are_rejected(): void
    {
        $csv = "name,email\nAcme Corp,acme@example.test\nGlobex Inc,globex@example.test\n";

        $this->actingAs($this->admin)
            ->post(route('admin.imports.upload'), ['file' => UploadedFile::fake()->createWithContent('customers.csv', $csv)])
            ->assertOk();

        $import = Import::where('file_name', 'customers.csv')->firstOrFail();
        $this->assertSame('csv', $import->file_type);
        $this->assertSame(2, $import->total_rows);
        $this->assertStringStartsWith('imports/'.$this->company->id.'/', $import->file_path);
        $this->assertTrue(Storage::disk('local')->exists($import->file_path));

        $this->post(route('admin.imports.upload'), [
            'file' => UploadedFile::fake()->createWithContent('payload.json', '[]'),
        ])->assertSessionHasErrors('file');

        $this->post(route('admin.imports.upload'), [
            'file' => UploadedFile::fake()->createWithContent('payload.txt', "name,email\nA,a@example.test"),
        ])->assertSessionHasErrors('file');

        $this->assertSame(0, Import::whereIn('file_name', ['payload.json', 'payload.txt'])->count());
    }

    public function test_csv_with_embedded_php_is_rejected_by_upload_security(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.imports.upload'), [
                'file' => UploadedFile::fake()->createWithContent(
                    'payload.csv',
                    "<?php echo 'blocked';\nname,email\nBad,actor@example.test\n"
                ),
            ])
            ->assertSessionHasErrors('file');

        $this->assertSame(0, Import::where('file_name', 'payload.csv')->count());
    }

    public function test_xlsx_upload_is_parsed_with_native_reader(): void
    {
        $path = $this->makeXlsx([
            ['name', 'email'],
            ['Xlsx Customer', 'xlsx@example.test'],
        ]);
        $file = new UploadedFile(
            $path,
            'customers.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $this->actingAs($this->admin)
            ->post(route('admin.imports.upload'), ['file' => $file])
            ->assertOk();

        $this->assertDatabaseHas('imports', [
            'company_id' => $this->company->id,
            'file_name' => 'customers.xlsx',
            'file_type' => 'xlsx',
            'total_rows' => 1,
        ]);
    }

    public function test_saved_template_id_is_applied_to_upload_mapping(): void
    {
        $template = ImportTemplate::create([
            'company_id' => $this->company->id,
            'user_id' => $this->admin->id,
            'name' => 'Full name to notes',
            'destination' => 'customers',
            'column_mapping' => [
                ['column' => 'full_name', 'field' => 'notes'],
            ],
        ]);

        $csv = "full_name\nTemplate Customer\n";

        $this->actingAs($this->admin)
            ->post(route('admin.imports.upload'), [
                'file' => UploadedFile::fake()->createWithContent('template.csv', $csv),
                'template_id' => $template->id,
            ])
            ->assertOk()
            ->assertViewHas('mappings', function (array $mappings): bool {
                $mapping = collect($mappings)->firstWhere('column', 'full_name');

                return $mapping !== null && $mapping['field'] === 'notes';
            });
    }

    public function test_balanced_opening_balances_create_one_posted_journal(): void
    {
        CompanyContext::set($this->company->id);
        $this->actingAs($this->admin);

        $cash = $this->account('CASH', 'asset');
        $equity = $this->account('EQUITY', 'equity');

        $import = $this->import('opening_balances');
        $rows = [
            ['account_code' => 'CASH', 'as_of_date' => '2026-01-01', 'debit' => '10000.00', 'credit' => '', 'currency_code' => 'USD', 'reference' => 'OPEN-1', 'description' => 'Opening cash'],
            ['account_code' => 'EQUITY', 'as_of_date' => '2026-01-01', 'debit' => '', 'credit' => '10000.00', 'currency_code' => 'USD', 'reference' => 'OPEN-1', 'description' => 'Opening equity'],
        ];

        $result = app(ImportProcessor::class)->execute($import, $rows, $this->openingBalanceMappings(), $this->company->id);

        $this->assertSame(2, $result['created']);
        $this->assertSame(0, $result['failed']);

        $entry = JournalEntry::where('company_id', $this->company->id)->firstOrFail();
        $this->assertSame('posted', $entry->status);
        $this->assertSame(2, $entry->lines()->count());
        $this->assertTrue($entry->isBalanced());
        $this->assertEqualsCanonicalizing(
            [$cash->id, $equity->id],
            $entry->lines()->pluck('account_id')->all()
        );

        $import->refresh();
        $this->assertSame('completed', $import->status);
        $this->assertSame(2, $import->created_rows);
    }

    public function test_unbalanced_opening_balances_roll_back_the_whole_batch(): void
    {
        CompanyContext::set($this->company->id);
        $this->actingAs($this->admin);

        $this->account('CASH', 'asset');
        $this->account('EQUITY', 'equity');

        $import = $this->import('opening_balances');
        $rows = [
            ['account_code' => 'CASH', 'as_of_date' => '2026-01-01', 'debit' => '10000.00', 'credit' => '', 'currency_code' => 'USD', 'reference' => 'OPEN-1', 'description' => 'Opening cash'],
            ['account_code' => 'EQUITY', 'as_of_date' => '2026-01-01', 'debit' => '', 'credit' => '7500.00', 'currency_code' => 'USD', 'reference' => 'OPEN-1', 'description' => 'Opening equity'],
        ];

        $result = app(ImportProcessor::class)->execute($import, $rows, $this->openingBalanceMappings(), $this->company->id);

        $this->assertSame(0, $result['created']);
        $this->assertSame(2, $result['failed']);
        $this->assertSame(0, JournalEntry::where('company_id', $this->company->id)->count());

        $import->refresh();
        $this->assertSame('completed_with_errors', $import->status);
    }

    public function test_product_import_creates_opening_stock_movement(): void
    {
        CompanyContext::set($this->company->id);
        $this->actingAs($this->admin);

        $warehouse = Warehouse::create([
            'company_id' => $this->company->id,
            'code' => 'MAIN',
            'name' => 'Main Warehouse',
            'is_default' => true,
            'status' => 'active',
        ]);

        $import = $this->import('products');
        $rows = [[
            'name' => 'Opening Stock Product',
            'sku' => 'SKU-OPEN-1',
            'warehouse_code' => 'MAIN',
            'opening_quantity' => '25',
            'unit_cost' => '12.50',
            'valuation_date' => '2026-01-01',
        ]];
        $mappings = [
            ['column' => 'name', 'field' => 'name'],
            ['column' => 'sku', 'field' => 'sku'],
            ['column' => 'warehouse_code', 'field' => 'warehouse_code'],
            ['column' => 'opening_quantity', 'field' => 'opening_quantity'],
            ['column' => 'unit_cost', 'field' => 'unit_cost'],
            ['column' => 'valuation_date', 'field' => 'valuation_date'],
        ];

        $result = app(ImportProcessor::class)->execute($import, $rows, $mappings, $this->company->id);

        $this->assertSame(1, $result['created']);
        $product = Product::where('sku', 'SKU-OPEN-1')->firstOrFail();
        $movement = StockMovement::where('product_id', $product->id)->firstOrFail();

        $this->assertSame($warehouse->id, $movement->warehouse_id);
        $this->assertSame('in', $movement->type);
        $this->assertEquals(25, (float) $movement->quantity);
        $this->assertEquals(12.5, (float) $movement->unit_cost);
        $this->assertSame('opening_balance', $movement->reference_type);
        $this->assertStringContainsString('2026-01-01', (string) $movement->note);
    }

    public function test_execute_revalidates_rows_before_writing(): void
    {
        CompanyContext::set($this->company->id);
        $this->actingAs($this->admin);

        $import = $this->import('products');
        $rows = [['sku' => 'SKU-MISSING-NAME']];
        $mappings = [['column' => 'sku', 'field' => 'sku']];

        $result = app(ImportProcessor::class)->execute($import, $rows, $mappings, $this->company->id);

        $this->assertSame(0, $result['created']);
        $this->assertSame(1, $result['failed']);
        $this->assertSame(0, Product::where('sku', 'SKU-MISSING-NAME')->count());
    }

    /**
     * @param  list<list<string>>  $rows
     */
    private function makeXlsx(array $rows): string
    {
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'venturex-phase1';
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $path = $directory.DIRECTORY_SEPARATOR.'import-'.uniqid('', true).'.xlsx';
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CREATE) !== true) {
            $this->fail('Unable to create the test XLSX fixture.');
        }

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="xml" ContentType="application/xml"/></Types>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheets><sheet name="Sheet1" sheetId="1"/></sheets></workbook>');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
        foreach ($rows as $rowIndex => $row) {
            $xml .= '<row r="'.($rowIndex + 1).'">';
            foreach ($row as $columnIndex => $value) {
                $reference = chr(65 + $columnIndex).($rowIndex + 1);
                $xml .= '<c r="'.$reference.'" t="inlineStr"><is><t>'.htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</t></is></c>';
            }
            $xml .= '</row>';
        }
        $xml .= '</sheetData></worksheet>';

        $zip->addFromString('xl/worksheets/sheet1.xml', $xml);
        $zip->close();

        $this->tempFiles[] = $path;

        return $path;
    }

    private function account(string $code, string $type): Account
    {
        return Account::create([
            'company_id' => $this->company->id,
            'code' => $code,
            'name' => $code,
            'type' => $type,
            'is_active' => true,
        ]);
    }

    private function import(string $destination): Import
    {
        return Import::create([
            'company_id' => $this->company->id,
            'user_id' => $this->admin->id,
            'file_name' => $destination.'.csv',
            'file_path' => 'imports/'.$this->company->id.'/'.$destination.'.csv',
            'file_type' => 'csv',
            'file_size' => 128,
            'destination' => $destination,
            'status' => 'uploaded',
            'total_rows' => 0,
            'duplicate_strategy' => 'skip',
        ]);
    }

    /**
     * @return list<array<string, string>>
     */
    private function openingBalanceMappings(): array
    {
        return [
            ['column' => 'account_code', 'field' => 'account_code'],
            ['column' => 'as_of_date', 'field' => 'as_of_date'],
            ['column' => 'debit', 'field' => 'debit'],
            ['column' => 'credit', 'field' => 'credit'],
            ['column' => 'currency_code', 'field' => 'currency_code'],
            ['column' => 'reference', 'field' => 'reference'],
            ['column' => 'description', 'field' => 'description'],
        ];
    }
}
