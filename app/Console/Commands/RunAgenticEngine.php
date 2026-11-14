<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\Ai\AgenticEngine;
use App\Services\CompanyContext;
use Illuminate\Console\Command;

class RunAgenticEngine extends Command
{
    protected $signature = 'agents:run
        {--company= : Run only for a specific company ID}
        {--dry-run : Evaluate rules without writing any changes}';

    protected $description = 'Execute the AERO X Agentic Engine business rules';

    public function handle(AgenticEngine $engine): int
    {
        $companies = $this->option('company')
            ? Company::whereKey((int) $this->option('company'))->get()
            : Company::where('is_active', true)->get();

        if ($companies->isEmpty()) {
            $this->warn('No active companies found.');

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $totals = ['success' => 0, 'skipped' => 0, 'rolled_back' => 0, 'failed' => 0];

        foreach ($companies as $company) {
            CompanyContext::set($company->id);

            try {
                $runs = $engine->runAll($company->id, $dryRun);
            } finally {
                CompanyContext::clear();
            }

            foreach ($runs as $run) {
                $totals[$run->status] = ($totals[$run->status] ?? 0) + 1;
            }

            $this->line(sprintf(
                '%s (#%d): %d rule(s) evaluated',
                $company->name,
                $company->id,
                count($runs)
            ));
        }

        $this->info(sprintf(
            '%s: %d succeeded, %d skipped, %d rolled back.',
            $dryRun ? 'Dry run complete' : 'Agentic run complete',
            $totals['success'],
            $totals['skipped'],
            $totals['rolled_back']
        ));

        return $totals['rolled_back'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
