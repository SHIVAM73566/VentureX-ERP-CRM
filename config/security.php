<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Emergency Lockdown
    |--------------------------------------------------------------------------
    | When active, all requests except the listed routes (e.g. health checks)
    | are rejected with 503 by the EmergencyLockdown middleware.
    |--------------------------------------------------------------------------
    */
    'lockdown' => [
        'enabled' => (bool) env('SECURITY_LOCKDOWN_ACTIVE', false),
        'except' => ['up', 'login', 'login.attempt', 'logout'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Policy
    |--------------------------------------------------------------------------
    */
    'password' => [
        'min_length' => (int) env('SECURITY_PASSWORD_MIN_LENGTH', 10),
        'require_upper' => true,
        'require_lower' => true,
        'require_digit' => true,
        'require_special' => true,
        'history' => (int) env('SECURITY_PASSWORD_HISTORY', 5),
        'expire_days' => (int) env('SECURITY_PASSWORD_EXPIRE_DAYS', 0),
        'revoke_sessions_on_change' => (bool) env('SECURITY_PASSWORD_REVOKE_SESSIONS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | MFA
    |--------------------------------------------------------------------------
    */
    'mfa' => [
        // Set MFA_ENFORCE=false in .env to disable mandatory MFA (useful for demos/development).
        'enforce' => (bool) env('MFA_ENFORCE', false),
        // Roles that are FORCED to enroll and use MFA (when enforce is true).
        'mandatory_roles' => ['super_admin', 'company_admin', 'ceo', 'cfo', 'finance_manager'],
        'digits' => 6,
        'period' => 30,
        'window' => 1,
        'recovery_codes_count' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Login Protection
    |--------------------------------------------------------------------------
    */
    'login' => [
        'max_attempts' => (int) env('SECURITY_LOGIN_MAX_ATTEMPTS', 5),
        'decay_minutes' => (int) env('SECURITY_LOGIN_DECAY_MINUTES', 5),
        // After this many consecutive failures, progressively lock the account.
        'lockout_threshold' => (int) env('SECURITY_LOGIN_LOCKOUT_THRESHOLD', 8),
        'lockout_minutes' => (int) env('SECURITY_LOGIN_LOCKOUT_MINUTES', 15),
        'alert_after' => (int) env('SECURITY_LOGIN_ALERT_AFTER', 10),
        'suspicious_geo_country' => false, // geolocation requires an external service; left for deployment
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Gateway
    |--------------------------------------------------------------------------
    */
    'ai' => [
        'per_minute' => (int) env('SECURITY_AI_PER_MINUTE', 10),
        'per_hour' => (int) env('SECURITY_AI_PER_HOUR', 60),
        'per_day' => (int) env('SECURITY_AI_PER_DAY', 200),
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Center
    |--------------------------------------------------------------------------
    */
    'export' => [
        'max_per_day' => (int) env('SECURITY_EXPORT_PER_DAY', 20),
        'max_records_per_export' => (int) env('SECURITY_EXPORT_MAX_RECORDS', 25000),
        'signed_url_minutes' => (int) env('SECURITY_EXPORT_URL_MINUTES', 30),
        'max_downloads' => (int) env('SECURITY_EXPORT_MAX_DOWNLOADS', 3),
        // Data types that always require approval (must match ExportService::SOURCES keys).
        'require_approval' => ['customers', 'suppliers', 'leads', 'opportunities', 'offers'],
        'never_export' => ['password', 'api_key', 'token', 'secret', 'recovery_code', 'two_factor'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload / File Scanning
    |--------------------------------------------------------------------------
    */
    'upload' => [
        'max_size_kb' => (int) env('SECURITY_UPLOAD_MAX_KB', 10240),
        'scan_required' => true,
        // Blocked executable/script extensions regardless of MIME.
        'blocked_extensions' => [
            'php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'phar', 'cgi', 'pl', 'py', 'pyc',
            'sh', 'bash', 'bat', 'cmd', 'ps1', 'psm1', 'vbs', 'vbe', 'js', 'jse', 'ws', 'wsf',
            'exe', 'dll', 'so', 'dylib', 'com', 'scr', 'msi', 'msp', 'app', 'jar', 'class',
            'htaccess', 'asis', 'reg', 'scf', 'lnk', 'svg',
        ],
        // Allowed extensions -> magic-byte signature prefixes (hex).
        'allowed_signatures' => [
            'pdf' => ['25504446'],
            'jpg' => ['ffd8ff'],
            'jpeg' => ['ffd8ff'],
            'png' => ['89504e47'],
            'gif' => ['47494638'],
            'xlsx' => ['504b0304'],
            'xls' => ['d0cf11e0'],
            'doc' => ['d0cf11e0'],
            'docx' => ['504b0304'],
            'csv' => [],
            'txt' => [],
            'xlsm' => ['504b0304'],
            'zip' => ['504b0304'],
            'webp' => ['52494646'],
            'tif' => ['49492a00'],
            'tiff' => ['49492a00'],
            'bmp' => ['424d'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Score / Checkpoints
    |--------------------------------------------------------------------------
    */
    'score' => [
        'checkpoints' => [
            'admin_mfa' => ['label' => 'Privileged admins use MFA', 'weight' => 15],
            'https' => ['label' => 'HTTPS in use', 'weight' => 10],
            'debug_off' => ['label' => 'Debug mode disabled', 'weight' => 10],
            'rate_limited' => ['label' => 'Login rate limiting active', 'weight' => 10],
            'audit_active' => ['label' => 'Audit logging active', 'weight' => 10],
            'headers' => ['label' => 'Security headers active', 'weight' => 10],
            'backup_healthy' => ['label' => 'Recent backup exists', 'weight' => 10],
            'db_least_priv' => ['label' => 'Least-privilege DB user', 'weight' => 5],
            'malware_scan' => ['label' => 'Upload scanning configured', 'weight' => 10],
            'ssl_ca' => ['label' => 'TLS CA bundle configured', 'weight' => 5],
            'secrets_env' => ['label' => 'Secrets stored in environment', 'weight' => 5],
        ],
    ],
];
