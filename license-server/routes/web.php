<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeWebhookController;

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook');
