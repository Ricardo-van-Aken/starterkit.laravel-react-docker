<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware(['ensure.tenant'])->group(function () {
        
        Route::get('dashboard', function () {
            return Inertia::render('dashboard');
        })->name('dashboard');

        Route::put('tenant', [\App\Http\Controllers\TenantController::class, 'update'])->name('tenant.update');
        Route::delete('tenant', [\App\Http\Controllers\TenantController::class, 'destroy'])
            ->middleware('password.confirm')
            ->name('tenant.destroy');
    });

    Route::get('tenants', [\App\Http\Controllers\TenantController::class, 'index'])->name('tenants.index');
    Route::post('tenants', [\App\Http\Controllers\TenantController::class, 'store'])->name('tenants.store');

    Route::post('tenants/switch', [\App\Http\Controllers\TenantController::class, 'switch'])->name('tenants.switch');
});

require __DIR__.'/settings.php';
