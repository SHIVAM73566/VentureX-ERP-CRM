<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureTwoFactor;
use App\Models\AgentAction;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\OnboardingTask;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\Ai\AgenticEngine;
use App\Services\CompanyContext;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * AERO X Agentic Engine: default rules, cooldown/idempotency gating, dry runs,
 * real executions and transactional rollback telemetry.
 */
class AgenticEngineTest extends TestCase
{
    protected Company $company;

    protected User $admin;

    protected AgenticEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureTwoFactor::class);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->company = Company::create([
            'name' => 'Agentic Test Corp',
            'is_active' => true,
            'currency_code' => 'USD',
        ]);

        CompanyContext::set($this->company->id);

        $actions = ['view', 'create', 'edit', 'delete', 'export', 'import', 'approve'];
        foreach (['automation', 'hr', 'finance', 'inventory', 'support'] as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$module}.{$action}", 'guard_name' => 'web']);
            }
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $this->admin = User::create([
            'name' => 'Agent Admin',
            'email' => 'agent-admin@test.com',
            'password' => Hash::make('password'),
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);
        $this->admin->assignRole($superAdmin);

        $this->actingAs($this->admin);

        $this->engine = app(AgenticEngine::class);
    }

    protected function tearDown(): void
    {
        CompanyContext::clear();

        parent::tearDown();
    }

    protected function makeEmployee(array $overrides = []): Employee
    {
        return Employee::create(array_merge([
            'company_id' => $this->company->id,
            'employee_number' => 'EMP-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'first_name' => 'New',
            'last_name' => 'Hire',
            'employment_type' => 'full_time',
            'status' => 'active',
            'hire_date' => now()->toDateString(),
            'base_salary' => 1000,
            'currency_code' => 'USD',
            'pay_frequency' => 'monthly',
        ], $overrides));
    }

    protected function makeOverdueInvoice(): Invoice
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Overdue Customer',
            'email' => 'overdue@test.com',
            'status' => 'active',
            'currency_code' => 'USD',
        ]);

        return Invoice::create([
            'company_id' => $this->company->id,
            'invoice_number' => 'INV-AGT-'.random_int(1000, 9999),
            'customer_id' => $customer->id,
            'status' => 'sent',
            'issue_date' => now()->subMonth()->toDateString(),
            'due_date' => now()->subDays(5)->toDateString(),
            'subtotal' => 500,
            'total' => 500,
            'paid_amount' => 0,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_default_rules_are_seeded_idempotently(): void
    {
        $this->assertSame(4, $this->engine->seedDefaults($this->company->id));
        $this->assertSame(0, $this->engine->seedDefaults($this->company->id));

        $this->assertSame(4, AgentAction::ofCompany($this->company->id)->count());
        $this->assertSame(
            ['finance_overdue_invoice_flag', 'hr_onboarding_autopilot', 'inventory_auto_purchase', 'support_ticket_self_assign'],
            AgentAction::ofCompany($this->company->id)->orderBy('key')->pluck('key')->all()
        );
    }

    public function test_disabled_rule_is_skipped_without_writing(): void
    {
        $this->engine->seedDefaults($this->company->id);
        $action = AgentAction::where('key', 'finance_overdue_invoice_flag')->firstOrFail();
        $action->update(['is_enabled' => false]);

        $invoice = $this->makeOverdueInvoice();

        $run = $this->engine->run($action);

        $this->assertSame('skipped', $run->status);
        $this->assertStringContainsString('disabled', $run->summary);
        $this->assertSame('sent', $invoice->fresh()->status);
    }

    public function test_cooldown_prevents_repeat_runs(): void
    {
        $this->engine->seedDefaults($this->company->id);
        $action = AgentAction::where('key', 'finance_overdue_invoice_flag')->firstOrFail();
        $action->update(['last_run_at' => now()]);

        $run = $this->engine->run($action);

        $this->assertSame('skipped', $run->status);
        $this->assertStringContainsString('Cooldown', $run->summary);
    }

    public function test_dry_run_reports_changes_without_writing(): void
    {
        $this->engine->seedDefaults($this->company->id);
        $action = AgentAction::where('key', 'finance_overdue_invoice_flag')->firstOrFail();

        $invoice = $this->makeOverdueInvoice();

        $run = $this->engine->run($action, true);

        $this->assertSame('success', $run->status);
        $this->assertTrue($run->dry_run);
        $this->assertStringContainsString('Would flag 1', $run->summary);
        $this->assertSame('sent', $invoice->fresh()->status);
        $this->assertNull($action->fresh()->last_run_at);
    }

    public function test_overdue_invoices_are_flagged_on_a_real_run(): void
    {
        $this->engine->seedDefaults($this->company->id);
        $action = AgentAction::where('key', 'finance_overdue_invoice_flag')->firstOrFail();

        $invoice = $this->makeOverdueInvoice();

        $run = $this->engine->run($action);

        $this->assertSame('success', $run->status);
        $this->assertSame('overdue', $invoice->fresh()->status);
        $this->assertSame(1, (int) $action->fresh()->success_count);
        $this->assertNotNull($action->fresh()->last_run_at);
    }

    public function test_low_stock_rule_drafts_purchase_orders(): void
    {
        $this->engine->seedDefaults($this->company->id);
        $action = AgentAction::where('key', 'inventory_auto_purchase')->firstOrFail();

        $supplier = Supplier::create([
            'company_id' => $this->company->id,
            'name' => 'Primary Supplier',
            'status' => 'approved',
            'currency_code' => 'USD',
        ]);

        Product::create([
            'company_id' => $this->company->id,
            'sku' => 'SKU-LOW-1',
            'name' => 'Low Stock Widget',
            'purchase_price' => 10,
            'selling_price' => 15,
            'reorder_level' => 20,
            'supplier_id' => $supplier->id,
            'status' => 'active',
        ]);

        $dry = $this->engine->run($action, true);
        $this->assertSame('success', $dry->status);
        $this->assertStringContainsString('Would draft 1', $dry->summary);
        $this->assertSame(0, PurchaseOrder::ofCompany($this->company->id)->count());

        $run = $this->engine->run($action);

        $this->assertSame('success', $run->status);

        $order = PurchaseOrder::ofCompany($this->company->id)->with('items')->firstOrFail();
        $this->assertSame($supplier->id, $order->supplier_id);
        $this->assertSame('pending', $order->status);
        $this->assertCount(1, $order->items);
        $this->assertGreaterThanOrEqual(20, (float) $order->items->first()->quantity);
    }

    public function test_unowned_support_tickets_are_self_assigned(): void
    {
        $this->engine->seedDefaults($this->company->id);
        $action = AgentAction::where('key', 'support_ticket_self_assign')->firstOrFail();

        $ticket = SupportTicket::create([
            'company_id' => $this->company->id,
            'user_id' => $this->admin->id,
            'ticket_number' => 'TKT-AGT-0001',
            'subject' => 'Cannot export report',
            'description' => 'The export button does nothing.',
            'priority' => 'medium',
            'status' => 'open',
            'category' => 'bug',
        ]);

        $run = $this->engine->run($action);

        $this->assertSame('success', $run->status);
        $this->assertSame($this->admin->id, $ticket->fresh()->assigned_to);
    }

    public function test_onboarding_autopilot_seeds_checklists_for_recent_hires(): void
    {
        $this->engine->seedDefaults($this->company->id);
        $action = AgentAction::where('key', 'hr_onboarding_autopilot')->firstOrFail();

        $employee = $this->makeEmployee();

        $run = $this->engine->run($action);

        $this->assertSame('success', $run->status);
        $this->assertSame(
            count(config('hr.onboarding')),
            OnboardingTask::where('employee_id', $employee->id)->count()
        );

        // A second run must not duplicate the checklist.
        $action->update(['last_run_at' => null]);
        $this->engine->run($action);

        $this->assertSame(
            count(config('hr.onboarding')),
            OnboardingTask::where('employee_id', $employee->id)->count()
        );
    }

    public function test_failed_transaction_is_rolled_back_and_recorded(): void
    {
        $action = AgentAction::create([
            'company_id' => $this->company->id,
            'key' => 'exploding_rule',
            'name' => 'Exploding rule',
            'description' => 'Always throws to prove rollback telemetry.',
            'category' => 'operations',
            'trigger' => 'manual',
            'is_enabled' => true,
            'requires_approval' => false,
            'cooldown_minutes' => 5,
            'config' => [],
            'confidence' => 0.5,
        ]);

        $run = $this->engine->run($action);

        $this->assertSame('rolled_back', $run->status);
        $this->assertStringContainsString('rolled back', strtolower($run->summary));
        $this->assertSame('RuntimeException', $run->details['exception'] ?? null);
        $this->assertSame(1, (int) $action->fresh()->failure_count);
        $this->assertLessThan(0.5, (float) $action->fresh()->confidence);

        $this->assertDatabaseHas('agent_runs', [
            'id' => $run->id,
            'status' => 'rolled_back',
            'action_key' => 'exploding_rule',
        ]);
    }
}
