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
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::post('tenants', [\App\Http\Controllers\TenantController::class, 'store'])->name('tenants.store');
    Route::put('tenants/{tenant}', [\App\Http\Controllers\TenantController::class, 'update'])->name('tenants.update');
    Route::delete('tenants/{tenant}', [\App\Http\Controllers\TenantController::class, 'destroy'])
        ->middleware('password.confirm')
        ->name('tenants.destroy');
});

require __DIR__.'/settings.php';
