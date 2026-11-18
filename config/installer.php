<?php

declare(strict_types=1);

return [
    /*
     * The web installer writes to the real .env by default. Tests and hosted
     * deployments can override these paths via environment variables.
     */
    'env_path' => env('INSTALLER_ENV_PATH', base_path('.env')),

    'flag_path' => env('INSTALLER_FLAG_PATH', base_path('storage/.installed')),

    'logo_disk' => env('INSTALLER_LOGO_DISK', 'local'),
];
