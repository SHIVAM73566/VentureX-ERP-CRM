<?php

return [
    /*
    |--------------------------------------------------------------------------
    | License / entitlement verification (client)
    |--------------------------------------------------------------------------
    | Public key only. Never store private keys in the client app or ZIPs.
    */
    'server_url' => env('LICENSE_SERVER_URL', null),
    'public_key' => env('LICENSE_ED25519_PUBLIC_KEY', null),
    'offline_grace_days' => (int) env('LICENSE_OFFLINE_GRACE_DAYS', 14),
    // Optional installation/customer identifiers sent to the license server
    // and checked against signed token claims.
    'installation_id' => env('LICENSE_INSTALLATION_ID', null),
    'customer_key' => env('LICENSE_CUSTOMER_KEY', null),
    // Unbounded tokens must be an explicit operator decision.
    'allow_unbounded_tokens' => (bool) env('LICENSE_ALLOW_UNBOUNDED_TOKENS', false),
];
