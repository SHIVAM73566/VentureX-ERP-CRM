<?php

use App\Http\Controllers\LicenseVerifyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| License Server API
|--------------------------------------------------------------------------
*/

Route::post('/license/verify', [LicenseVerifyController::class, 'verify'])
    ->middleware('throttle:60,1')
    ->name('license.verify');
