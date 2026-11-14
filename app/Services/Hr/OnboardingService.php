<?php

namespace App\Services\Hr;

use App\Models\Employee;
use App\Models\OnboardingTask;

class OnboardingService
{
    /**
     * Create the standard onboarding checklist for an employee. Safe to call
     * repeatedly: an employee that already has tasks is left untouched.
     *
     * @return int Number of tasks created.
     */
    public function seedFor(Employee $employee, bool $force = false): int
    {
        if (! $force && $employee->onboardingTasks()->exists()) {
            return 0;
        }

        $created = 0;
        foreach ((array) config('hr.onboarding', []) as $sort => $task) {
            OnboardingTask::create([
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'title' => $task['title'],
                'category' => $task['category'] ?? 'general',
                'due_date' => ($employee->hire_date ?? now())->copy()->addDays((int) ($task['due_in_days'] ?? 0)),
                'status' => 'pending',
                'sort' => $sort,
            ]);
            $created++;
        }

        return $created;
    }

    public function complete(OnboardingTask $task): void
    {
        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function reopen(OnboardingTask $task): void
    {
        $task->update([
            'status' => 'pending',
            'completed_at' => null,
        ]);
    }
}
