<?php

use App\Http\Controllers\Installer\InstallerController;
use Illuminate\Support\Facades\Route;

/*
 * All installer endpoints run inside the web middleware group (registered in
 * bootstrap/app.php) with the installer lockout guard and a strict rate limit.
 */
Route::middleware(['installer', 'throttle:6,1'])->group(function () {
    // 1. Requirements
    Route::get('/install', [InstallerController::class, 'welcome'])->name('installer.welcome');

    // 2. Mode
    Route::get('/install/mode', [InstallerController::class, 'mode'])->name('installer.mode');
    Route::post('/install/mode', [InstallerController::class, 'storeMode'])->name('installer.mode.store');

    // 3. Database
    Route::get('/install/database', [InstallerController::class, 'database'])->name('installer.database');
    Route::post('/install/database', [InstallerController::class, 'storeDatabase'])->name('installer.database.store');

    // 4. Environment
    Route::get('/install/environment', [InstallerController::class, 'environment'])->name('installer.environment');
    Route::post('/install/environment', [InstallerController::class, 'storeEnvironment'])->name('installer.environment.store');

    // 5. Company & Super Admin
    Route::get('/install/company', [InstallerController::class, 'company'])->name('installer.company');
    Route::post('/install/company', [InstallerController::class, 'storeCompany'])->name('installer.company.store');

    // Backwards-compatible names for existing links/bookmarks.
    Route::get('/install/admin', [InstallerController::class, 'company'])->name('installer.admin');
    Route::post('/install/admin', [InstallerController::class, 'storeCompany'])->name('installer.admin.store');

    // 6. Install
    Route::get('/install/run', [InstallerController::class, 'run'])->name('installer.run');
    Route::post('/install/execute', [InstallerController::class, 'execute'])->name('installer.execute');
    Route::get('/install/complete', [InstallerController::class, 'complete'])->name('installer.complete');
});
