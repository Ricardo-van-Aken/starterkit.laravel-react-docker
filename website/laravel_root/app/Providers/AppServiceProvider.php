<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Sandbox Redis securely for parallel and sequential testing runs.
        // We do this in register() so the prefix is bound BEFORE any packages (like Spatie) 
        // boot up and accidentally cache the original connection string.
        if ($this->app->environment('testing')) {
            $prefix = env('TEST_TOKEN') ? "test_" . env('TEST_TOKEN') . "_" : "test_";
            config(['database.redis.options.prefix' => $prefix]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
