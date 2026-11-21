<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Layered entitlements (client app)
    |--------------------------------------------------------------------------
    | Defines all 15 planned layers. Stripe lookup keys are placeholders until
    | configured in the license/billing server and synced to clients.
    | Types: monthly | annual | usage | service | waitlist
    |
    | Features/limits are hints for UI and middleware; enforce in code where needed.
    */

    'layers' => [
        'updates_support' => [
            'key' => 'updates_support',
            'name' => 'Updates & Support (Care)',
            'type' => 'annual',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_UPDATES_SUPPORT', null),
            'features' => ['product_updates', 'support_window_per_offer'],
            'limits' => ['seats' => null],
            'status' => 'available',
        ],
        'managed_hosting' => [
            'key' => 'managed_hosting',
            'name' => 'Managed Install & Maintenance',
            'type' => 'monthly',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_MANAGED_HOSTING', null),
            'features' => ['managed_setup_assist', 'maintenance_retainer_scope'],
            'limits' => [],
            'status' => 'available',
        ],
        'ai_credits' => [
            'key' => 'ai_credits',
            'name' => 'AI Credits / Metered AI Add-On',
            'type' => 'usage',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_AI_CREDITS', null),
            'features' => ['metered_ai_credits_optional'],
            'limits' => ['credits' => 'metered'],
            'status' => 'available',
            'note' => 'Default: bring your own AI API keys (BYOK).',
        ],
        'reporting_pack_adv' => [
            'key' => 'reporting_pack_adv',
            'name' => 'Advanced reporting pack',
            'type' => 'monthly',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_REPORTING_PACK_ADV', null),
            'features' => ['advanced_reports', 'advanced_exports'],
            'limits' => [],
            'status' => 'planned',
        ],
        'workflow_automation_plus' => [
            'key' => 'workflow_automation_plus',
            'name' => 'Workflow automation+ pack',
            'type' => 'monthly',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_WORKFLOW_AUTOMATION_PLUS', null),
            'features' => ['extended_automation_rules'],
            'limits' => [],
            'status' => 'planned',
        ],
        'integrations_priority' => [
            'key' => 'integrations_priority',
            'name' => 'Priority integrations pack',
            'type' => 'monthly',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_INTEGRATIONS_PRIORITY', null),
            'features' => ['priority_integration_support'],
            'limits' => [],
            'status' => 'planned',
        ],
        'multi_branch_ops' => [
            'key' => 'multi_branch_ops',
            'name' => 'Multi-branch / advanced operations',
            'type' => 'monthly',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_MULTI_BRANCH_OPS', null),
            'features' => ['advanced_multi_site_controls'],
            'limits' => [],
            'status' => 'planned',
        ],
        'compliance_hardening' => [
            'key' => 'compliance_hardening',
            'name' => 'Compliance & hardening add-on',
            'type' => 'annual',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_COMPLIANCE_HARDENING', null),
            'features' => ['compliance_checklists_packs'],
            'limits' => [],
            'status' => 'planned',
        ],
        'tpl_manufacturing_plus' => [
            'key' => 'tpl_manufacturing_plus',
            'name' => 'Industry template: Manufacturing+',
            'type' => 'waitlist',
            'stripe_lookup_key' => null,
            'features' => ['industry_templates_manufacturing'],
            'limits' => [],
            'status' => 'coming_soon',
        ],
        'tpl_trading_distribution' => [
            'key' => 'tpl_trading_distribution',
            'name' => 'Industry template: Trading & distribution',
            'type' => 'waitlist',
            'stripe_lookup_key' => null,
            'features' => ['industry_templates_trading_distribution'],
            'limits' => [],
            'status' => 'coming_soon',
        ],
        'tpl_services_projects' => [
            'key' => 'tpl_services_projects',
            'name' => 'Industry template: Services / projects',
            'type' => 'waitlist',
            'stripe_lookup_key' => null,
            'features' => ['industry_templates_services_projects'],
            'limits' => [],
            'status' => 'coming_soon',
        ],
        'logistics_suite_adv' => [
            'key' => 'logistics_suite_adv',
            'name' => 'Advanced logistics suite',
            'type' => 'monthly',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_LOGISTICS_SUITE_ADV', null),
            'features' => ['advanced_logistics'],
            'limits' => [],
            'status' => 'coming_soon',
        ],
        'finance_suite_adv' => [
            'key' => 'finance_suite_adv',
            'name' => 'Advanced finance suite',
            'type' => 'monthly',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_FINANCE_SUITE_ADV', null),
            'features' => ['advanced_finance'],
            'limits' => [],
            'status' => 'coming_soon',
        ],
        'partner_white_label' => [
            'key' => 'partner_white_label',
            'name' => 'Partner/white-label enablement (scoped)',
            'type' => 'service',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_PARTNER_WHITE_LABEL', null),
            'features' => ['partner_enablement_scoped'],
            'limits' => [],
            'status' => 'coming_soon',
        ],
        'custom_dev_credits' => [
            'key' => 'custom_dev_credits',
            'name' => 'Custom development credits',
            'type' => 'usage',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_CUSTOM_DEV_CREDITS', null),
            'features' => ['custom_dev_credits'],
            'limits' => ['credits' => 'purchased'],
            'status' => 'coming_soon',
        ],
    ],
];
