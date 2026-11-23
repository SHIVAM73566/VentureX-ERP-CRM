<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Layered entitlements (client app)
    |--------------------------------------------------------------------------
    | Defines the planned/available add-on layers. Stripe lookup keys are
    | optional labels resolved on the billing server. Types:
    | monthly | annual | usage | service | waitlist
    |
    | Features/limits are hints for UI and middleware; enforce in code where needed.
    | One-time layers carry a bounded validity_days so an add-on can never become
    | a permanent entitlement by accident.
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
            'validity_days' => 365,
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
            'validity_days' => 365,
        ],
        'reporting_pack_adv' => [
            'key' => 'reporting_pack_adv',
            'name' => 'Advanced Reporting',
            'type' => 'monthly',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_REPORTING_PACK_ADV', null),
            'features' => ['advanced_reports', 'advanced_exports'],
            'limits' => [],
            'status' => 'available',
        ],
        'workflow_automation_plus' => [
            'key' => 'workflow_automation_plus',
            'name' => 'Workflow automation+ pack',
            'type' => 'monthly',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_WORKFLOW_AUTOMATION_PLUS', null),
            'features' => ['extended_automation_rules'],
            'limits' => [],
            'status' => 'available',
        ],
        'integrations_priority' => [
            'key' => 'integrations_priority',
            'name' => 'Priority integrations pack',
            'type' => 'monthly',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_INTEGRATIONS_PRIORITY', null),
            'features' => ['priority_integration_support'],
            'limits' => [],
            'status' => 'available',
        ],
        'multi_branch_ops' => [
            'key' => 'multi_branch_ops',
            'name' => 'Multi-branch / advanced operations',
            'type' => 'monthly',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_MULTI_BRANCH_OPS', null),
            'features' => ['advanced_multi_site_controls'],
            'limits' => [],
            'status' => 'available',
        ],
        'compliance_hardening' => [
            'key' => 'compliance_hardening',
            'name' => 'Compliance & hardening add-on',
            'type' => 'annual',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_COMPLIANCE_HARDENING', null),
            'features' => ['compliance_checklists_packs'],
            'limits' => [],
            'status' => 'available',
        ],
        'granular_rbac' => [
            'key' => 'granular_rbac',
            'name' => 'Granular roles & permissions',
            'type' => 'monthly',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_GRANULAR_RBAC', null),
            'features' => ['granular_role_scopes', 'per_module_permissions'],
            'limits' => [],
            'status' => 'available',
        ],
        'security_patches' => [
            'key' => 'security_patches',
            'name' => 'Security updates & audit tooling',
            'type' => 'annual',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_SECURITY_PATCHES', null),
            'features' => ['priority_security_patches', 'audit_log_exports'],
            'limits' => [],
            'status' => 'available',
        ],
        'capacity_expansion' => [
            'key' => 'capacity_expansion',
            'name' => 'Capacity & module expansion',
            'type' => 'monthly',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_CAPACITY_EXPANSION', null),
            'features' => ['extra_seats', 'additional_modules'],
            'limits' => [],
            'status' => 'available',
        ],
        'tpl_manufacturing_plus' => [
            'key' => 'tpl_manufacturing_plus',
            'name' => 'Industry template: Manufacturing+',
            'type' => 'waitlist',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_TPL_MANUFACTURING_PLUS', null),
            'features' => ['industry_templates_manufacturing'],
            'limits' => [],
            'status' => 'available',
            'validity_days' => 365,
        ],
        'tpl_trading_distribution' => [
            'key' => 'tpl_trading_distribution',
            'name' => 'Industry template: Trading & distribution',
            'type' => 'waitlist',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_TPL_TRADING_DISTRIBUTION', null),
            'features' => ['industry_templates_trading_distribution'],
            'limits' => [],
            'status' => 'available',
            'validity_days' => 365,
        ],
        'tpl_services_projects' => [
            'key' => 'tpl_services_projects',
            'name' => 'Industry template: Services / projects',
            'type' => 'waitlist',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_TPL_SERVICES_PROJECTS', null),
            'features' => ['industry_templates_services_projects'],
            'limits' => [],
            'status' => 'available',
            'validity_days' => 365,
        ],
        'logistics_suite_adv' => [
            'key' => 'logistics_suite_adv',
            'name' => 'Advanced logistics suite',
            'type' => 'monthly',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_LOGISTICS_SUITE_ADV', null),
            'features' => ['advanced_logistics'],
            'limits' => [],
            'status' => 'available',
        ],
        'finance_suite_adv' => [
            'key' => 'finance_suite_adv',
            'name' => 'Advanced finance suite',
            'type' => 'monthly',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_FINANCE_SUITE_ADV', null),
            'features' => ['advanced_finance'],
            'limits' => [],
            'status' => 'available',
        ],
        'partner_white_label' => [
            'key' => 'partner_white_label',
            'name' => 'Partner/white-label enablement (scoped)',
            'type' => 'service',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_PARTNER_WHITE_LABEL', null),
            'features' => ['partner_enablement_scoped'],
            'limits' => [],
            'status' => 'coming_soon',
            'validity_days' => 365,
        ],
        'custom_dev_credits' => [
            'key' => 'custom_dev_credits',
            'name' => 'Custom development credits',
            'type' => 'usage',
            'stripe_lookup_key' => env('STRIPE_LOOKUP_CUSTOM_DEV_CREDITS', null),
            'features' => ['custom_dev_credits'],
            'limits' => ['credits' => 'purchased'],
            'status' => 'available',
            'validity_days' => 365,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Checkout offer copy (server-side only)
    |--------------------------------------------------------------------------
    | Plain-language capabilities only. Prices, taxes and renewal terms are
    | shown by Stripe at checkout — never hardcoded in Blade/JS or faked here.
    | An offer is purchasable only when a matching server-side Stripe price ID
    | exists in config('services.stripe.prices') and the layer is available.
    */
    'checkout_offers' => [
        'updates_support' => [
            'summary' => 'Product updates and a defined support window for this workspace.',
            'capabilities' => ['Version updates', 'Support window', 'Upgrade guidance'],
        ],
        'managed_hosting' => [
            'summary' => 'Assisted installation and maintenance retainer (service, not self-provisioned).',
            'capabilities' => ['Assisted setup', 'Maintenance scope', 'Operational handover'],
        ],
        'ai_credits' => [
            'summary' => 'Optional metered AI credits when you do not want to bring your own provider keys.',
            'capabilities' => ['Metered usage', 'BYOK remains default', 'Local fallback preserved'],
        ],
        'reporting_pack_adv' => [
            'summary' => 'Advanced reporting with scheduled exports and cross-module dashboards.',
            'capabilities' => ['Cross-module dashboards', 'Scheduled CSV/XLSX exports', 'Saved report views'],
        ],
        'workflow_automation_plus' => [
            'summary' => 'Extended automation rules for approvals and recurring operations.',
            'capabilities' => ['More automation rules', 'Approval workflows', 'Operational reminders'],
        ],
        'integrations_priority' => [
            'summary' => 'Priority connector and integration support for teams with more systems to connect.',
            'capabilities' => ['Priority integration queue', 'Connector guidance', 'Compatibility checks'],
        ],
        'multi_branch_ops' => [
            'summary' => 'Advanced controls for multi-site and multi-branch operations.',
            'capabilities' => ['Branch-level controls', 'Consolidated reporting', 'Site-aware workflows'],
        ],
        'compliance_hardening' => [
            'summary' => 'Compliance checklists and evidence-friendly audit exports.',
            'capabilities' => ['Compliance checklists', 'Evidence exports', 'Review reminders'],
        ],
        'granular_rbac' => [
            'summary' => 'Fine-grained role scopes and per-module permissions for larger teams.',
            'capabilities' => ['Per-module permissions', 'Scoped admin roles', 'Permission change audit trail'],
        ],
        'security_patches' => [
            'summary' => 'Priority security patches, hardening checks and exportable audit trails.',
            'capabilities' => ['Priority security fixes', 'Hardening checklists', 'Audit log exports'],
        ],
        'capacity_expansion' => [
            'summary' => 'Add seats and enable additional business modules as your team grows.',
            'capabilities' => ['Additional user seats', 'Extra module capacity', 'Non-destructive upgrades'],
        ],
        'tpl_manufacturing_plus' => [
            'summary' => 'Manufacturing+ industry template: BOM, RFQ and production-oriented workflows.',
            'capabilities' => ['Manufacturing starter records', 'BOM/RFQ workflows', 'Implementation checklist'],
        ],
        'tpl_trading_distribution' => [
            'summary' => 'Trading & distribution template for multi-supplier, multi-warehouse operations.',
            'capabilities' => ['Supplier/warehouse starter records', 'Landed-cost workflows', 'Replenishment views'],
        ],
        'tpl_services_projects' => [
            'summary' => 'Services / projects template for quote-to-delivery professional teams.',
            'capabilities' => ['Project starter records', 'Quote-to-delivery views', 'Utilisation reports'],
        ],
        'logistics_suite_adv' => [
            'summary' => 'Advanced logistics: shipments, containers, demurrage risk and document completeness.',
            'capabilities' => ['Shipment/container control', 'Demurrage risk views', 'Document completeness checks'],
        ],
        'finance_suite_adv' => [
            'summary' => 'Advanced finance: journals, payables, receivables and duplicate-payment controls.',
            'capabilities' => ['Advanced ledger views', 'Payables/receivables controls', 'Duplicate-payment detection'],
        ],
        'custom_dev_credits' => [
            'summary' => 'Prepaid development credits for scoped custom work and integrations.',
            'capabilities' => ['Scoped delivery request', 'Credit balance visibility', 'No automatic data changes'],
        ],
    ],
];
