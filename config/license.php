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
];
