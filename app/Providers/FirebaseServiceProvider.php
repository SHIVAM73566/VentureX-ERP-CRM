<?php

namespace App\Providers;

use App\Services\FirebaseService;
use Illuminate\Support\ServiceProvider;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FirebaseService::class, function ($app) {
            return new FirebaseService;
        });
    }

    public function boot(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../../config/firebase.php',
            'firebase'
        );
    }
}
