<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payroll engine defaults
    |--------------------------------------------------------------------------
    | Simple, transparent statutory model used by the built-in payroll run.
    | Values are percentages of gross pay and can be overridden per employee
    | when a payroll item is edited before approval.
    */
    'payroll' => [
        'tax_rate' => (float) env('HR_PAYROLL_TAX_RATE', 10),
        'social_security_rate' => (float) env('HR_PAYROLL_SOCIAL_SECURITY_RATE', 5),
        'overtime_multiplier' => (float) env('HR_PAYROLL_OVERTIME_MULTIPLIER', 1.5),
        'standard_monthly_hours' => (float) env('HR_PAYROLL_MONTHLY_HOURS', 176),
    ],

    /*
    |--------------------------------------------------------------------------
    | Automated onboarding checklist
    |--------------------------------------------------------------------------
    | Created automatically for every new employee (and by the Agentic
    | Engine for recent hires that have no checklist yet).
    */
    'onboarding' => [
        ['title' => 'Collect signed offer letter and employment contract', 'category' => 'documentation', 'due_in_days' => 1],
        ['title' => 'Verify identity, address and tax documents', 'category' => 'compliance', 'due_in_days' => 2],
        ['title' => 'Create email account and grant system access', 'category' => 'it_access', 'due_in_days' => 1],
        ['title' => 'Add employee to payroll and benefits', 'category' => 'payroll', 'due_in_days' => 3],
        ['title' => 'Assign department induction and buddy', 'category' => 'orientation', 'due_in_days' => 3],
        ['title' => 'Complete security and code-of-conduct training', 'category' => 'compliance', 'due_in_days' => 7],
        ['title' => 'Schedule 30-day check-in with manager', 'category' => 'orientation', 'due_in_days' => 30],
    ],
];
