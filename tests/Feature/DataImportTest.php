<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureTwoFactor;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Import;
use App\Models\ImportRow;
use App\Models\ImportTemplate;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DataImportTest extends TestCase
{
    protected Company $company;

    protected User $admin;

    protected User $viewer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(EnsureTwoFactor::class);

        ImportRow::query()->forceDelete();
        Import::query()->forceDelete();
        ImportTemplate::query()->forceDelete();

        $this->company = Company::create(['name' => 'Import Test Co', 'is_active' => true, 'currency_code' => 'USD']);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $actions = ['view', 'create', 'edit', 'delete', 'export', 'import', 'approve'];
        $modules = ['customers', 'suppliers', 'products', 'leads', 'invoices', 'payments', 'exports'];
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$module}.{$action}", 'guard_name' => 'web']);
            }
        }
        Permission::firstOrCreate(['name' => 'settings.configure', 'guard_name' => 'web']);

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $viewerRole = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $viewerRole->syncPermissions(['customers.view', 'suppliers.view']);

        $this->admin = User::factory()->create(['company_id' => $this->company->id]);
        $this->admin->assignRole($superAdminRole);

        $this->viewer = User::factory()->create(['company_id' => $this->company->id]);
        $this->viewer->assignRole($viewerRole);

        Storage::fake('local');
    }

    public function test_import_requires_auth(): void
    {
        $this->get(route('admin.imports.index'))->assertRedirect();
    }

    public function test_import_requires_super_admin(): void
    {
        $this->actingAs($this->viewer)
            ->get(route('admin.imports.index'))
            ->assertForbidden();
    }

    public function test_import_index_loads(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.imports.index'))
            ->assertOk();
    }

    public function test_import_create_page_loads(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.imports.create'))
            ->assertOk();
    }

    public function test_upload_requires_file(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.imports.upload'), [])
            ->assertSessionHasErrors('file');
    }

    public function test_upload_csv_creates_import(): void
    {
        $csv = "name,email,phone\nAcme Corp,acme@test.com,555-0100\nGlobex Inc,globex@test.com,555-0200\n";

        $file = UploadedFile::fake()->createWithContent('test.csv', $csv);

        $this->actingAs($this->admin)
            ->post(route('admin.imports.upload'), ['file' => $file])
            ->assertOk();

        $this->assertDatabaseHas('imports', [
            'company_id' => $this->company->id,
            'file_name' => 'test.csv',
            'file_type' => 'csv',
            'total_rows' => 2,
        ]);

        $import = Import::where('file_name', 'test.csv')->first();
        $this->assertNotNull($import);
        $this->assertEquals('uploaded', $import->status);
    }

    public function test_upload_json_creates_import(): void
    {
        $json = json_encode([
            ['name' => 'Test Customer', 'email' => 'test@test.com'],
        ]);

        $file = UploadedFile::fake()->createWithContent('test.json', $json);

        $this->actingAs($this->admin)
            ->post(route('admin.imports.upload'), ['file' => $file])
            ->assertOk();

        $this->assertDatabaseHas('imports', [
            'company_id' => $this->company->id,
            'file_name' => 'test.json',
            'file_type' => 'json',
            'total_rows' => 1,
        ]);
    }

    public function test_upload_rejects_invalid_extension(): void
    {
        $file = UploadedFile::fake()->createWithContent('test.exe', 'binary');
        $this->actingAs($this->admin)
            ->post(route('admin.imports.upload'), ['file' => $file])
            ->assertSessionHasErrors('file');
    }

    public function test_destination_auto_detection(): void
    {
        $csv = "name,email,phone\ntest@test.com,test@test.com,555-0000\n";
        $file = UploadedFile::fake()->createWithContent('customers.csv', $csv);

        $this->actingAs($this->admin)
            ->post(route('admin.imports.upload'), ['file' => $file])
            ->assertOk()
            ->assertViewHas('detection', function ($detection) {
                return in_array($detection['destination'], ['customers', 'leads']);
            });
    }

    public function test_mapping_save_creates_validation_results(): void
    {
        $csv = "name,email,phone\nTest Customer,test@test.com,555-0000\n";
        $file = UploadedFile::fake()->createWithContent('test.csv', $csv);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.imports.upload'), ['file' => $file]);
        $response->assertOk();

        $import = Import::where('file_name', 'test.csv')->first();

        $mapping = [
            ['column' => 'name', 'field' => 'name'],
            ['column' => 'email', 'field' => 'email'],
            ['column' => 'phone', 'field' => 'phone'],
        ];

        $this->post(route('admin.imports.mapping.save'), [
            'import_id' => $import->id,
            'destination' => 'customers',
            'mappings' => $mapping,
        ])->assertOk()->assertViewHas('validation');
    }

    public function test_execute_import_creates_records(): void
    {
        $csv = "name,email,phone\nAlpha Company,alpha@test.com,555-0001\nBeta Company,beta@test.com,555-0002\n";
        $file = UploadedFile::fake()->createWithContent('import_test.csv', $csv);

        $this->actingAs($this->admin)
            ->post(route('admin.imports.upload'), ['file' => $file]);

        $import = Import::where('file_name', 'import_test.csv')->first();
        $mapping = [
            ['column' => 'name', 'field' => 'name'],
            ['column' => 'email', 'field' => 'email'],
            ['column' => 'phone', 'field' => 'phone'],
        ];

        $this->post(route('admin.imports.mapping.save'), [
            'import_id' => $import->id,
            'destination' => 'customers',
            'mappings' => $mapping,
        ]);

        $this->post(route('admin.imports.execute'), [
            'import_id' => $import->id,
            'duplicate_strategy' => 'skip',
        ])->assertOk();

        $this->assertDatabaseHas('customers', [
            'name' => 'Alpha Company',
            'email' => 'alpha@test.com',
            'company_id' => $this->company->id,
        ]);
        $this->assertDatabaseHas('customers', [
            'name' => 'Beta Company',
            'email' => 'beta@test.com',
            'company_id' => $this->company->id,
        ]);

        $import->refresh();
        $this->assertEquals('completed', $import->status);
        $this->assertEquals(2, $import->created_rows);
    }

    public function test_import_with_skip_duplicate_strategy(): void
    {
        Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Existing Customer',
            'email' => 'existing@test.com',
            'is_active' => true,
        ]);

        $csv = "name,email\nExisting Customer,existing@test.com\nNew Customer,new@test.com\n";
        $file = UploadedFile::fake()->createWithContent('dupe_test.csv', $csv);

        $this->actingAs($this->admin)
            ->post(route('admin.imports.upload'), ['file' => $file]);

        $import = Import::where('file_name', 'dupe_test.csv')->first();
        $mapping = [
            ['column' => 'name', 'field' => 'name'],
            ['column' => 'email', 'field' => 'email'],
        ];

        $this->post(route('admin.imports.mapping.save'), [
            'import_id' => $import->id,
            'destination' => 'customers',
            'mappings' => $mapping,
        ]);

        $this->post(route('admin.imports.execute'), [
            'import_id' => $import->id,
            'duplicate_strategy' => 'skip',
        ]);

        $import->refresh();
        $this->assertEquals('completed', $import->status);
        $this->assertEquals(1, $import->created_rows);
        $this->assertEquals(1, $import->skipped_rows);

        $this->assertDatabaseHas('customers', ['email' => 'new@test.com', 'company_id' => $this->company->id]);
    }

    public function test_import_with_update_duplicate_strategy(): void
    {
        Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Old Name',
            'email' => 'update@test.com',
            'phone' => '555-9999',
            'is_active' => true,
        ]);

        $csv = "name,email,phone\nNew Name,update@test.com,555-0000\n";
        $file = UploadedFile::fake()->createWithContent('update_test.csv', $csv);

        $this->actingAs($this->admin)
            ->post(route('admin.imports.upload'), ['file' => $file]);

        $import = Import::where('file_name', 'update_test.csv')->first();
        $mapping = [
            ['column' => 'name', 'field' => 'name'],
            ['column' => 'email', 'field' => 'email'],
            ['column' => 'phone', 'field' => 'phone'],
        ];

        $this->post(route('admin.imports.mapping.save'), [
            'import_id' => $import->id,
            'destination' => 'customers',
            'mappings' => $mapping,
        ]);

        $this->post(route('admin.imports.execute'), [
            'import_id' => $import->id,
            'duplicate_strategy' => 'update',
        ]);

        $import->refresh();
        $this->assertEquals('completed', $import->status);
        $this->assertEquals(1, $import->updated_rows);

        $customer = Customer::where('email', 'update@test.com')->first();
        $this->assertEquals('New Name', $customer->name);
    }

    public function test_import_history_page_loads(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.imports.history'))
            ->assertOk();
    }

    public function test_import_show_page_loads(): void
    {
        $import = Import::create([
            'company_id' => $this->company->id,
            'user_id' => $this->admin->id,
            'file_name' => 'test.csv',
            'file_path' => '/tmp/test.csv',
            'file_type' => 'csv',
            'file_size' => 1024,
            'destination' => 'customers',
            'status' => 'completed',
            'total_rows' => 5,
            'created_rows' => 5,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.imports.show', $import))
            ->assertOk();
    }

    public function test_import_delete(): void
    {
        $import = Import::create([
            'company_id' => $this->company->id,
            'user_id' => $this->admin->id,
            'file_name' => 'delete_me.csv',
            'file_path' => '/tmp/delete_me.csv',
            'file_type' => 'csv',
            'file_size' => 1024,
            'destination' => 'customers',
            'status' => 'completed',
            'total_rows' => 0,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.imports.destroy', $import));

        $this->assertSoftDeleted('imports', ['id' => $import->id]);
    }

    public function test_cross_company_import_blocked(): void
    {
        $otherCompany = Company::create(['name' => 'Other', 'is_active' => true, 'currency_code' => 'USD']);
        $import = Import::create([
            'company_id' => $otherCompany->id,
            'user_id' => $this->admin->id,
            'file_name' => 'other.csv',
            'file_path' => '/tmp/other.csv',
            'file_type' => 'csv',
            'file_size' => 1024,
            'destination' => 'customers',
            'status' => 'completed',
            'total_rows' => 0,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.imports.show', $import))
            ->assertForbidden();
    }

    public function test_import_template_store(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.imports.templates.store'), [
                'name' => 'My Customer Template',
                'destination' => 'customers',
                'column_mapping' => [
                    ['column' => 'name', 'field' => 'name'],
                    ['column' => 'email', 'field' => 'email'],
                ],
            ])->assertRedirect();

        $this->assertDatabaseHas('import_templates', [
            'company_id' => $this->company->id,
            'name' => 'My Customer Template',
            'destination' => 'customers',
        ]);
    }

    public function test_import_template_delete(): void
    {
        $template = ImportTemplate::create([
            'company_id' => $this->company->id,
            'user_id' => $this->admin->id,
            'name' => 'Delete Template',
            'destination' => 'customers',
            'column_mapping' => [],
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.imports.templates.destroy', $template));

        $this->assertSoftDeleted('import_templates', ['id' => $template->id]);
    }

    public function test_error_report_download(): void
    {
        $import = Import::create([
            'company_id' => $this->company->id,
            'user_id' => $this->admin->id,
            'file_name' => 'errors.csv',
            'file_path' => '/tmp/errors.csv',
            'file_type' => 'csv',
            'file_size' => 1024,
            'destination' => 'customers',
            'status' => 'completed_with_errors',
            'total_rows' => 1,
            'failed_rows' => 1,
        ]);

        ImportRow::create([
            'import_id' => $import->id,
            'row_number' => 1,
            'raw_data' => ['name' => 'Bad Row'],
            'status' => 'failed',
            'errors' => 'Missing email',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.imports.error-report', $import))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_import_creates_row_records(): void
    {
        $csv = "name,email\nTest A,a@test.com\nTest B,b@test.com\nTest C,c@test.com\n";
        $file = UploadedFile::fake()->createWithContent('rows_test.csv', $csv);

        $this->actingAs($this->admin)
            ->post(route('admin.imports.upload'), ['file' => $file]);

        $import = Import::where('file_name', 'rows_test.csv')->first();
        $mapping = [
            ['column' => 'name', 'field' => 'name'],
            ['column' => 'email', 'field' => 'email'],
        ];

        $this->post(route('admin.imports.mapping.save'), [
            'import_id' => $import->id,
            'destination' => 'customers',
            'mappings' => $mapping,
        ]);

        $this->post(route('admin.imports.execute'), [
            'import_id' => $import->id,
            'duplicate_strategy' => 'skip',
        ]);

        $rows = ImportRow::where('import_id', $import->id)->get();
        $this->assertEquals(3, $rows->count());
        $this->assertTrue($rows->every(fn ($r) => $r->status === 'created'));
    }
}
