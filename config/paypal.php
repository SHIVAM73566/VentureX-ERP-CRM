<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PayPal Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your PayPal integration settings. Set these values in your
    | .env file. NEVER commit real credentials to version control.
    |
    */

    'client_id' => env('PAYPAL_CLIENT_ID', ''),
    'client_secret' => env('PAYPAL_CLIENT_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | Set to 'sandbox' for testing, 'live' for production.
    |
    */
    'mode' => env('PAYPAL_MODE', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Webhook ID
    |--------------------------------------------------------------------------
    |
    | The webhook ID from your PayPal dashboard. Used to verify webhook
    | signatures and prevent spoofed payment notifications.
    |
    */
    'webhook_id' => env('PAYPAL_WEBHOOK_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    */
    'currency' => env('PAYPAL_CURRENCY', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Return / Cancel URLs
    |--------------------------------------------------------------------------
    */
    'return_url' => env('PAYPAL_RETURN_URL', '/payment/success'),
    'cancel_url' => env('PAYPAL_CANCEL_URL', '/payment/cancel'),

    /*
    |--------------------------------------------------------------------------
    | Plans & Pricing
    |--------------------------------------------------------------------------
    |
    | Server-side pricing â€” NEVER trust client-submitted prices.
    | All prices are in cents (integer) to avoid floating-point issues.
    |
    */
    'plans' => [
        'starter' => [
            'name' => 'Starter Plan',
            'price_cents' => 4900,      // $49.00
            'currency' => 'USD',
            'billing_cycle' => 'one_time',
            'description' => 'VentureX ERP & CRM Starter â€” Single site license with 6 months support',
        ],
        'professional' => [
            'name' => 'Professional Plan',
            'price_cents' => 14900,     // $149.00
            'currency' => 'USD',
            'billing_cycle' => 'one_time',
            'description' => 'VentureX ERP & CRM Professional â€” Single site license with 12 months support',
        ],
        'enterprise' => [
            'name' => 'Enterprise Plan',
            'price_cents' => 49900,     // $499.00
            'currency' => 'USD',
            'billing_cycle' => 'one_time',
            'description' => 'VentureX ERP & CRM Enterprise â€” Priority support with source code access',
        ],
        'custom_donation' => [
            'name' => 'Custom Payment / Feature Request',
            'price_cents' => 0,         // User-defined amount
            'currency' => 'USD',
            'billing_cycle' => 'one_time',
            'description' => 'Custom payment for feature requests or donations',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Request Pricing
    |--------------------------------------------------------------------------
    |
    | Minimum and maximum amounts for custom feature request payments.
    | Prices in cents.
    |
    */
    'feature_request' => [
        'min_amount_cents' => 1000,     // $10.00 minimum
        'max_amount_cents' => 50000,    // $500.00 maximum
    ],

];
