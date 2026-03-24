<?php

namespace App\Providers;

use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\ServiceProvider;
use App\Services\ActiveTenant;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(ActiveTenant::class, function() {
            return new ActiveTenant();
        });

        // Sandbox Redis securely for parallel and sequential testing runs.
        // We do this in register() so the prefix is bound BEFORE any packages (like Spatie) 
        // boot up and accidentally cache the original connection string.
        if ($this->app->environment('testing')) {
            $token = ParallelTesting::token();
            $prefix = $token ? "test_{$token}_" : "test_";
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
