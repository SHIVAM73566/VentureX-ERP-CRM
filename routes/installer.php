<?php

use App\Http\Controllers\Installer\InstallerController;
use Illuminate\Support\Facades\Route;

Route::get('/install', [InstallerController::class, 'welcome'])->name('installer.welcome');
Route::get('/install/database', [InstallerController::class, 'database'])->name('installer.database');
Route::post('/install/database', [InstallerController::class, 'storeDatabase'])->name('installer.database.store');
Route::get('/install/environment', [InstallerController::class, 'environment'])->name('installer.environment');
Route::post('/install/environment', [InstallerController::class, 'storeEnvironment'])->name('installer.environment.store');
Route::get('/install/admin', [InstallerController::class, 'admin'])->name('installer.admin');
Route::post('/install/admin', [InstallerController::class, 'storeAdmin'])->name('installer.admin.store');
Route::get('/install/run', [InstallerController::class, 'run'])->name('installer.run');
Route::post('/install/execute', [InstallerController::class, 'execute'])->name('installer.execute');
Route::get('/install/complete', [InstallerController::class, 'complete'])->name('installer.complete');
