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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enforce Redis isolation during Parallel Testing
        if (app()->runningUnitTests() && env('TEST_TOKEN')) {
            config(['database.redis.options.prefix' => "test_" . env('TEST_TOKEN') . "_"]);
        }
    }
}
