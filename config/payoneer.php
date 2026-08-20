<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payoneer Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Payoneer API credentials in your .env file.
    | NEVER commit real credentials to version control.
    |
    | Sign up at https://www.payoneer.com to get your API credentials.
    |
    */

    'client_id' => env('PAYONEER_CLIENT_ID', ''),
    'client_secret' => env('PAYONEER_CLIENT_SECRET', ''),
    'api_url' => env('PAYONEER_API_URL', 'https://api.payoneer.com/v2'),
    'webhook_secret' => env('PAYONEER_WEBHOOK_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Your Payoneer Email (for manual payments)
    |--------------------------------------------------------------------------
    |
    | This is the email address buyers see when sending payment.
    | Set this in your .env file as PAYONEER_EMAIL.
    |
    */

    'email' => env('PAYONEER_EMAIL', ''),

    /*
    |--------------------------------------------------------------------------
    | License Tiers & Pricing
    |--------------------------------------------------------------------------
    |
    | Server-side pricing — NEVER trust client-submitted prices.
    | All prices are in USD cents (integer) to avoid floating-point issues.
    |
    */

    'licenses' => [
        'standard' => [
            'name' => 'Standard License',
            'price' => 2900,
            'currency' => 'USD',
            'description' => 'VentureX ERP & CRM — Standard License (1 company, 5 users)',
        ],
        'professional' => [
            'name' => 'Professional License',
            'price' => 5900,
            'currency' => 'USD',
            'description' => 'VentureX ERP & CRM — Professional License (3 companies, 25 users)',
        ],
        'enterprise' => [
            'name' => 'Enterprise License',
            'price' => 12900,
            'currency' => 'USD',
            'description' => 'VentureX ERP & CRM — Enterprise License (Unlimited companies & users)',
        ],
    ],

];
