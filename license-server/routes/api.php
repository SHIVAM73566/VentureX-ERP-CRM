<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LicenseVerifyController;

/*
|--------------------------------------------------------------------------
| License Server API
|--------------------------------------------------------------------------
*/

Route::post('/license/verify', [LicenseVerifyController::class, 'verify'])
    ->middleware('throttle:60,1')
    ->name('license.verify');
