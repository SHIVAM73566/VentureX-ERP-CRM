<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Firebase project settings. Set these values in your
    | .env file. NEVER commit real credentials to version control.
    |
    | To set up Firebase:
    | 1. Go to https://console.firebase.google.com/
    | 2. Create a new project or select existing
    | 3. Go to Project Settings > Service Accounts
    | 4. Click "Generate new private key"
    | 5. Save the JSON file as storage/app/firebase-service-account.json
    | 6. Set the values below in your .env file
    |
    */

    'project_id' => env('FIREBASE_PROJECT_ID', ''),
    'api_key' => env('FIREBASE_API_KEY', ''),
    'auth_domain' => env('FIREBASE_AUTH_DOMAIN', ''),
    'storage_bucket' => env('FIREBASE_STORAGE_BUCKET', ''),
    'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID', ''),
    'app_id' => env('FIREBASE_APP_ID', ''),
    'measurement_id' => env('FIREBASE_MEASUREMENT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Service Account Path
    |--------------------------------------------------------------------------
    |
    | Path to the Firebase service account JSON file.
    | This file is used for server-side operations (Firestore, Storage, etc.)
    |
    */
    'service_account_path' => storage_path('app/firebase-service-account.json'),

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Enable or disable Firebase features.
    |
    */
    'features' => [
        'auth' => env('FIREBASE_AUTH_ENABLED', false),
        'firestore' => env('FIREBASE_FIRESTORE_ENABLED', false),
        'storage' => env('FIREBASE_STORAGE_ENABLED', false),
        'messaging' => env('FIREBASE_MESSAGING_ENABLED', false),
        'analytics' => env('FIREBASE_ANALYTICS_ENABLED', false),
    ],

];
