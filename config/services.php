<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Stripe (server-side only)
    |--------------------------------------------------------------------------
    | Secret, webhook secret and price IDs must only ever come from the
    | environment on the billing server. Never expose them to Blade/JS or
    | commit real values to version control. A blank value disables checkout
    | and webhook processing instead of failing open.
    */
    'stripe' => [
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'api_base' => rtrim(env('STRIPE_API_BASE', 'https://api.stripe.com'), '/'),
        // The bearer secret is only ever sent to these exact endpoints.
        'api_base_allowed' => ['https://api.stripe.com'],
        // Stripe-hosted session URLs must stay on these exact hosts.
        'redirect_hosts' => ['checkout.stripe.com', 'billing.stripe.com'],
        // Optional extra hosts for checkout/portal return URLs (same app).
        'return_hosts' => [],
        'webhook_tolerance_seconds' => (int) env('STRIPE_WEBHOOK_TOLERANCE_SECONDS', 300),
        // A stale processing claim is safely retried instead of dropping the event.
        'claim_lease_seconds' => (int) env('STRIPE_WEBHOOK_CLAIM_LEASE_SECONDS', 300),
        'grace_days' => (int) env('STRIPE_ENTITLEMENT_GRACE_DAYS', 7),
        'success_url' => env('STRIPE_CHECKOUT_SUCCESS_URL'),
        'cancel_url' => env('STRIPE_CHECKOUT_CANCEL_URL'),
        'portal_return_url' => env('STRIPE_BILLING_PORTAL_RETURN_URL'),
        // Layer key => Stripe Price ID. Resolve these once from the Stripe
        // Dashboard (Price IDs, not publishable keys) and store them in .env.
        'prices' => [
            'updates_support' => env('STRIPE_PRICE_UPDATES_SUPPORT'),
            'managed_hosting' => env('STRIPE_PRICE_MANAGED_HOSTING'),
            'ai_credits' => env('STRIPE_PRICE_AI_CREDITS'),
            'reporting_pack_adv' => env('STRIPE_PRICE_REPORTING_PACK_ADV'),
            'workflow_automation_plus' => env('STRIPE_PRICE_WORKFLOW_AUTOMATION_PLUS'),
            'integrations_priority' => env('STRIPE_PRICE_INTEGRATIONS_PRIORITY'),
            'multi_branch_ops' => env('STRIPE_PRICE_MULTI_BRANCH_OPS'),
            'compliance_hardening' => env('STRIPE_PRICE_COMPLIANCE_HARDENING'),
            'granular_rbac' => env('STRIPE_PRICE_GRANULAR_RBAC'),
            'security_patches' => env('STRIPE_PRICE_SECURITY_PATCHES'),
            'capacity_expansion' => env('STRIPE_PRICE_CAPACITY_EXPANSION'),
            'logistics_suite_adv' => env('STRIPE_PRICE_LOGISTICS_SUITE_ADV'),
            'finance_suite_adv' => env('STRIPE_PRICE_FINANCE_SUITE_ADV'),
            'partner_white_label' => env('STRIPE_PRICE_PARTNER_WHITE_LABEL'),
            'custom_dev_credits' => env('STRIPE_PRICE_CUSTOM_DEV_CREDITS'),
            'tpl_manufacturing_plus' => env('STRIPE_PRICE_TPL_MANUFACTURING_PLUS'),
            'tpl_trading_distribution' => env('STRIPE_PRICE_TPL_TRADING_DISTRIBUTION'),
            'tpl_services_projects' => env('STRIPE_PRICE_TPL_SERVICES_PROJECTS'),
        ],
    ],

];
