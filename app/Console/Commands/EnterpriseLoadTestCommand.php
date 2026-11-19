<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Lead;
use App\Models\LeaveRequest;
use App\Models\Opportunity;
use App\Models\Payment;
use App\Models\PayrollRun;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Rfq;
use App\Models\SalesOrder;
use App\Models\Shipment;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Services\CompanyContext;
use App\Services\StockService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Deterministic, local-only load simulation. It does not require real HTTP
 * concurrency: each simulated staff member executes the same read paths a
 * dashboard/list request would execute, in batches of 25.
 */
class EnterpriseLoadTestCommand extends Command
{
    protected $signature = 'enterprise:load-test
        {--users=30 : Simulated staff count (30, 100, 300 or 500)}
        {--company= : Restrict the simulation to one company id}';

    protected $description = 'Run a deterministic multi-module staff/query load simulation';

    private const ALLOWED_USERS = [30, 100, 300, 500];

    private const BATCH_SIZE = 25;

    private const MODULES = [
        'Sales', 'CRM', 'Procurement', 'Inventory',
        'Logistics', 'Finance', 'HR',
    ];

    public function handle(): int
    {
        $users = (int) $this->option('users');

        if (! in_array($users, self::ALLOWED_USERS, true)) {
            $this->error('--users must be one of: '.implode(', ', self::ALLOWED_USERS));

            return self::FAILURE;
        }

        $companyId = $this->resolveCompanyId();

        if ($companyId !== null) {
            CompanyContext::set($companyId);
        }

        StockService::flush();

        $paths = $this->modulePaths();
        $stats = array_fill_keys(self::MODULES, ['users' => 0, 'queries' => 0, 'ms' => 0.0]);
        $errors = [];
        $totalQueries = 0;
        $started = microtime(true);

        DB::enableQueryLog();
        DB::flushQueryLog();

        try {
            foreach (array_chunk(range(1, $users), self::BATCH_SIZE) as $batch) {
                foreach ($batch as $index) {
                    $module = self::MODULES[($index - 1) % count(self::MODULES)];
                    $before = count(DB::getQueryLog());
                    $moduleStart = microtime(true);

                    try {
                        foreach ($paths[$module] as $path) {
                            $path($companyId);
                        }
                    } catch (Throwable $e) {
                        $errors[] = "{$module} (staff #{$index}): ".$e->getMessage();
                        Log::error('enterprise:load-test query path failed.', [
                            'module' => $module,
                            'staff' => $index,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    $stats[$module]['users']++;
                    $stats[$module]['queries'] += count(DB::getQueryLog()) - $before;
                    $stats[$module]['ms'] += (microtime(true) - $moduleStart) * 1000;
                    $totalQueries += count(DB::getQueryLog()) - $before;
                }
            }
        } finally {
            DB::flushQueryLog();
            DB::disableQueryLog();

            if ($companyId !== null) {
                CompanyContext::clear();
            }
        }

        $rows = [];
        foreach (self::MODULES as $module) {
            $rows[] = [
                $module,
                $stats[$module]['users'],
                $stats[$module]['queries'],
                number_format($stats[$module]['ms'], 2),
            ];
        }

        $this->table(['Module', 'Simulated staff', 'Queries', 'Time (ms)'], $rows);
        $this->info(sprintf(
            'Load test: %d staff, %d batches, %d queries, %s ms, %d error(s).',
            $users,
            (int) ceil($users / self::BATCH_SIZE),
            $totalQueries,
            number_format((microtime(true) - $started) * 1000, 2),
            count($errors)
        ));

        foreach (array_slice($errors, 0, 10) as $error) {
            $this->error($error);
        }

        return $errors === [] ? self::SUCCESS : self::FAILURE;
    }

    private function resolveCompanyId(): ?int
    {
        if ($this->option('company') !== null) {
            return Company::find((int) $this->option('company'))?->id;
        }

        return Company::query()->orderBy('id')->first()?->id;
    }

    /**
     * @return array<string, array<int, callable(?int): mixed>>
     */
    private function modulePaths(): array
    {
        return [
            'Sales' => [
                fn (?int $id) => SalesOrder::ofCompany($id)->whereIn('status', ['confirmed', 'processing', 'shipped'])->count(),
                fn (?int $id) => Invoice::ofCompany($id)->whereIn('status', ['sent', 'partial', 'overdue'])->sum('total'),
                fn (?int $id) => Payment::ofCompany($id)->where('status', 'completed')->count(),
            ],
            'CRM' => [
                fn (?int $id) => Customer::ofCompany($id)->count(),
                fn (?int $id) => Lead::ofCompany($id)->where('status', 'new')->count(),
                fn (?int $id) => Opportunity::ofCompany($id)->whereNotIn('stage', ['won', 'lost'])->sum('expected_value'),
            ],
            'Procurement' => [
                fn (?int $id) => PurchaseOrder::ofCompany($id)->whereIn('status', ['draft', 'pending', 'approved'])->count(),
                fn (?int $id) => Supplier::ofCompany($id)->count(),
                fn (?int $id) => Rfq::ofCompany($id)->count(),
            ],
            'Inventory' => [
                fn (?int $id) => Product::ofCompany($id)->where('status', 'active')->count(),
                fn (?int $id) => StockMovement::ofCompany($id)->where('type', 'out')->sum('quantity'),
                // One grouped query no matter how many products are checked.
                fn (?int $id) => StockService::balances($id),
            ],
            'Logistics' => [
                fn (?int $id) => Shipment::ofCompany($id)->count(),
                fn (?int $id) => Shipment::ofCompany($id)->whereNotIn('status', ['delivered', 'cancelled'])->count(),
            ],
            'Finance' => [
                fn (?int $id) => Invoice::ofCompany($id)->selectRaw('COALESCE(SUM(total - paid_amount), 0) as outstanding')->value('outstanding'),
                fn (?int $id) => Payment::ofCompany($id)->sum('amount'),
                fn (?int $id) => JournalEntry::ofCompany($id)->count(),
            ],
            'HR' => [
                fn (?int $id) => Employee::ofCompany($id)->whereIn('status', ['active', 'probation'])->count(),
                fn (?int $id) => LeaveRequest::ofCompany($id)->where('status', 'pending')->count(),
                fn (?int $id) => PayrollRun::ofCompany($id)->count(),
            ],
        ];
    }
}
