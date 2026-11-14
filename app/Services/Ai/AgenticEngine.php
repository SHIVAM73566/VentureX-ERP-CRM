<?php

namespace App\Services\Ai;

use App\Models\AgentAction;
use App\Models\AgentRun;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use App\Services\Hr\OnboardingService;
use App\Services\NumberGenerator;
use App\Services\SelfHealingService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * AERO X Agentic Engine.
 *
 * Executes a small, auditable set of autonomous business rules. Every rule is
 * company-scoped, idempotent and cooldown-gated, and every execution runs in a
 * database transaction. A failed run is rolled back, recorded in the ledger
 * with the captured error and reported to the self-healing error center, so a
 * partial write can never leak into production data.
 */
class AgenticEngine
{
    public function __construct(
        protected OnboardingService $onboarding,
        protected SelfHealingService $healer,
    ) {}

    /**
     * The rules shipped with the engine. Kept declarative so operators can see
     * exactly what the agent is allowed to do before it runs.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function defaults(): array
    {
        return [
            [
                'key' => 'inventory_auto_purchase',
                'name' => 'Auto-create purchase orders for low stock',
                'description' => 'Groups every product at or below its reorder level by preferred supplier and drafts a purchase order to restore cover.',
                'category' => 'inventory',
                'trigger' => 'scheduled',
                'cooldown_minutes' => 360,
                'requires_approval' => false,
                'config' => ['target_multiplier' => 2, 'lead_days' => 7],
            ],
            [
                'key' => 'finance_overdue_invoice_flag',
                'name' => 'Auto-flag overdue invoices',
                'description' => 'Moves sent and partially paid invoices past their due date into the overdue state so collections can act.',
                'category' => 'finance',
                'trigger' => 'scheduled',
                'cooldown_minutes' => 60,
                'requires_approval' => false,
                'config' => [],
            ],
            [
                'key' => 'support_ticket_self_assign',
                'name' => 'Self-assign unowned support tickets',
                'description' => 'Assigns open, unowned tickets to the support user with the smallest current workload.',
                'category' => 'support',
                'trigger' => 'scheduled',
                'cooldown_minutes' => 30,
                'requires_approval' => false,
                'config' => [],
            ],
            [
                'key' => 'hr_onboarding_autopilot',
                'name' => 'Generate onboarding checklists for new hires',
                'description' => 'Creates the standard onboarding checklist for active employees hired in the last 30 days who have none.',
                'category' => 'hr',
                'trigger' => 'scheduled',
                'cooldown_minutes' => 720,
                'requires_approval' => false,
                'config' => ['lookback_days' => 30],
            ],
        ];
    }

    /**
     * Seed the default rules for a company without duplicating existing rows.
     */
    public function seedDefaults(?int $companyId = null): int
    {
        $companyId ??= CompanyContext::id();
        $created = 0;

        foreach (self::defaults() as $rule) {
            $action = AgentAction::firstOrCreate(
                ['company_id' => $companyId, 'key' => $rule['key']],
                [
                    ...$rule,
                    'company_id' => $companyId,
                    'is_enabled' => true,
                    'confidence' => 0.8,
                ]
            );

            if ($action->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }

    /**
     * @return Collection<int, AgentAction>
     */
    public function actions(?int $companyId = null, bool $onlyDue = false)
    {
        $companyId ??= CompanyContext::id();

        return AgentAction::ofCompany($companyId)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->when($onlyDue, fn ($actions) => $actions->filter->isDue())
            ->values();
    }

    /**
     * Run every enabled rule whose cooldown has elapsed.
     *
     * @return array<int, AgentRun>
     */
    public function runAll(?int $companyId = null, bool $dryRun = false, ?int $userId = null): array
    {
        $companyId ??= CompanyContext::id();
        $this->seedDefaults($companyId);

        $runs = [];

        foreach ($this->actions($companyId, true) as $action) {
            $runs[] = $this->run($action, $dryRun, $userId);
        }

        return $runs;
    }

    public function run(AgentAction $action, bool $dryRun = false, ?int $userId = null): AgentRun
    {
        $started = microtime(true);

        $run = AgentRun::create([
            'company_id' => $action->company_id,
            'agent_action_id' => $action->id,
            'action_key' => $action->key,
            'trigger' => $dryRun ? 'manual_dry_run' : 'scheduled',
            'status' => 'running',
            'dry_run' => $dryRun,
            'started_at' => now(),
            'created_by' => $userId ?? auth()->id(),
        ]);

        if (! $action->is_enabled) {
            return $this->finish($run, 'skipped', 'Rule is disabled.', [], $started);
        }

        if (! $dryRun && ! $action->isDue()) {
            return $this->finish($run, 'skipped', 'Cooldown has not elapsed.', [], $started);
        }

        if ($action->requires_approval && ! $dryRun) {
            return $this->finish($run, 'skipped', 'Rule requires human approval before execution.', [], $started);
        }

        try {
            [$summary, $details] = DB::transaction(function () use ($action, $dryRun) {
                return $this->execute($action, $dryRun);
            });

            if (! $dryRun) {
                $this->recordSuccess($action);
            }

            $run = $this->finish($run, $dryRun ? 'success' : 'success', $summary, $details, $started);
            $run->update(['dry_run' => $dryRun]);

            AuditLogger::log(
                $dryRun ? 'agent.dry_run' : 'agent.run',
                'automation',
                $action,
                null,
                ['summary' => $summary, 'details' => $details]
            );

            return $run;
        } catch (Throwable $e) {
            // The transaction has already rolled back; persist the failure in a
            // fresh write outside it and route the exception to the healer.
            $this->recordFailure($action);

            try {
                $this->healer->capture($e, request());
            } catch (Throwable) {
                // Never let telemetry mask the original failure.
            }

            AuditLogger::log('agent.rollback', 'automation', $action, null, [
                'error' => mb_substr($e->getMessage(), 0, 500),
            ]);

            return $this->finish(
                $run,
                'rolled_back',
                'Transaction rolled back: '.mb_substr($e->getMessage(), 0, 300),
                ['exception' => $e::class],
                $started
            );
        }
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    protected function execute(AgentAction $action, bool $dryRun): array
    {
        return match ($action->key) {
            'inventory_auto_purchase' => $this->autoPurchaseOrders($action, $dryRun),
            'finance_overdue_invoice_flag' => $this->flagOverdueInvoices($action, $dryRun),
            'support_ticket_self_assign' => $this->assignSupportTickets($action, $dryRun),
            'hr_onboarding_autopilot' => $this->seedOnboarding($action, $dryRun),
            default => throw new \RuntimeException("Unknown agent rule [{$action->key}]."),
        };
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    protected function autoPurchaseOrders(AgentAction $action, bool $dryRun): array
    {
        $config = (array) $action->config;
        $multiplier = max(1, (float) ($config['target_multiplier'] ?? 2));
        $leadDays = max(1, (int) ($config['lead_days'] ?? 7));

        $products = Product::ofCompany($action->company_id)
            ->whereNotNull('reorder_level')
            ->whereNotNull('supplier_id')
            ->get()
            ->filter(fn (Product $product) => $product->availableStock() <= (float) $product->reorder_level);

        $created = [];
        $skipped = 0;

        foreach ($products->groupBy('supplier_id') as $supplierId => $group) {
            $pending = PurchaseOrderItem::query()
                ->whereIn('product_id', $group->pluck('id'))
                ->whereHas('purchaseOrder', fn ($q) => $q
                    ->where('company_id', $action->company_id)
                    ->whereNotIn('status', ['received', 'cancelled']))
                ->pluck('product_id')
                ->all();

            $needed = $group->reject(fn (Product $product) => in_array($product->id, $pending, true));

            if ($needed->isEmpty()) {
                $skipped += $group->count();

                continue;
            }

            if ($dryRun) {
                $created[] = ['supplier_id' => $supplierId, 'products' => $needed->pluck('name')->all()];

                continue;
            }

            $order = PurchaseOrder::create([
                'company_id' => $action->company_id,
                'po_number' => NumberGenerator::next('PO', 'purchase_orders'),
                'supplier_id' => $supplierId,
                'status' => 'pending',
                'order_date' => now()->toDateString(),
                'expected_date' => now()->addDays($leadDays)->toDateString(),
                'notes' => 'Created automatically by AERO X Agentic Engine ('.$action->key.').',
                'created_by' => auth()->id(),
            ]);

            $subtotal = 0.0;

            foreach ($needed->values() as $sort => $product) {
                $available = $product->availableStock();
                $target = max((float) $product->reorder_level * $multiplier, (float) $product->reorder_level);
                $quantity = max($target - $available, (float) $product->reorder_level, 1);
                $unitPrice = (float) ($product->purchase_price ?? 0);
                $lineTotal = round($quantity * $unitPrice, 4);
                $subtotal += $lineTotal;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'product_id' => $product->id,
                    'description' => $product->name,
                    'quantity' => round($quantity, 4),
                    'unit' => $product->unit?->code,
                    'unit_price' => $unitPrice,
                    'tax_rate' => 0,
                    'line_total' => $lineTotal,
                    'sort' => $sort,
                ]);
            }

            $order->update(['subtotal' => $subtotal, 'total' => $subtotal]);
            $created[] = ['po' => $order->po_number, 'products' => $needed->count()];
        }

        $summary = $dryRun
            ? sprintf('Would draft %d purchase order(s) for %d low-stock product(s).', count($created), $products->count() - $skipped)
            : sprintf('Drafted %d purchase order(s) covering %d low-stock product(s).', count($created), $products->count() - $skipped);

        return [$summary, ['orders' => $created, 'skipped_existing' => $skipped]];
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    protected function flagOverdueInvoices(AgentAction $action, bool $dryRun): array
    {
        $query = Invoice::ofCompany($action->company_id)
            ->whereIn('status', ['sent', 'partial'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString());

        $count = (clone $query)->count();

        if (! $dryRun && $count > 0) {
            $query->update(['status' => 'overdue', 'updated_at' => now()]);
        }

        $summary = $dryRun
            ? "Would flag {$count} invoice(s) as overdue."
            : "Flagged {$count} invoice(s) as overdue.";

        return [$summary, ['flagged' => $count]];
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    protected function assignSupportTickets(AgentAction $action, bool $dryRun): array
    {
        $tickets = SupportTicket::ofCompany($action->company_id)
            ->whereNull('assigned_to')
            ->whereIn('status', ['open', 'investigating'])
            ->get();

        if ($tickets->isEmpty()) {
            return ['No unowned support tickets to assign.', ['assigned' => 0]];
        }

        $agent = User::query()
            ->where('company_id', $action->company_id)
            ->where('is_active', true)
            ->get()
            ->sortBy(fn (User $user) => SupportTicket::ofCompany($action->company_id)
                ->where('assigned_to', $user->id)
                ->whereNotIn('status', ['resolved', 'closed'])
                ->count())
            ->first();

        if (! $agent) {
            return ['No eligible support agent found.', ['assigned' => 0]];
        }

        if (! $dryRun) {
            SupportTicket::ofCompany($action->company_id)
                ->whereIn('id', $tickets->pluck('id'))
                ->update(['assigned_to' => $agent->id, 'updated_at' => now()]);
        }

        $summary = $dryRun
            ? "Would assign {$tickets->count()} ticket(s) to {$agent->displayName()}."
            : "Assigned {$tickets->count()} ticket(s) to {$agent->displayName()}.";

        return [$summary, ['assigned' => $tickets->count(), 'agent_id' => $agent->id]];
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    protected function seedOnboarding(AgentAction $action, bool $dryRun): array
    {
        $lookback = max(1, (int) (((array) $action->config)['lookback_days'] ?? 30));

        $employees = Employee::ofCompany($action->company_id)
            ->active()
            ->whereDate('hire_date', '>=', now()->subDays($lookback)->toDateString())
            ->whereDoesntHave('onboardingTasks')
            ->get();

        $created = 0;

        foreach ($employees as $employee) {
            if (! $dryRun) {
                $created += $this->onboarding->seedFor($employee);
            } else {
                $created += count((array) config('hr.onboarding', []));
            }
        }

        $summary = $dryRun
            ? "Would generate {$created} onboarding task(s) for {$employees->count()} new hire(s)."
            : "Generated {$created} onboarding task(s) for {$employees->count()} new hire(s).";

        return [$summary, ['employees' => $employees->count(), 'tasks' => $created]];
    }

    protected function recordSuccess(AgentAction $action): void
    {
        $confidence = min(1.0, (float) $action->confidence + (1 - (float) $action->confidence) * 0.05);

        $action->update([
            'run_count' => (int) $action->run_count + 1,
            'success_count' => (int) $action->success_count + 1,
            'confidence' => round($confidence, 4),
            'last_run_at' => now(),
        ]);
    }

    protected function recordFailure(AgentAction $action): void
    {
        $confidence = max(0.0, (float) $action->confidence - 0.05);

        $action->update([
            'run_count' => (int) $action->run_count + 1,
            'failure_count' => (int) $action->failure_count + 1,
            'confidence' => round($confidence, 4),
            'last_run_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $details
     */
    protected function finish(AgentRun $run, string $status, string $summary, array $details, float $started): AgentRun
    {
        $run->update([
            'status' => $status,
            'summary' => $summary,
            'details' => $details,
            'duration_ms' => round((microtime(true) - $started) * 1000, 2),
            'finished_at' => now(),
        ]);

        return $run;
    }
}
