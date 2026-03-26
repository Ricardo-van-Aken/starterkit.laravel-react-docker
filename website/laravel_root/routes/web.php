<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

use App\Services\ActiveTenant;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        abort(404);
        return Inertia::render('dashboard');
    })->name('dashboard');

    // Tenant management
    Route::get('tenants', [\App\Http\Controllers\TenantController::class, 'index'])->name('tenants.index');
    Route::post('tenants', [\App\Http\Controllers\TenantController::class, 'store'])->name('tenants.store');

    // Tenant actions
    Route::post('tenants/switch/{tenant}', [\App\Http\Controllers\TenantController::class, 'switch'])->name('tenants.switch');
    Route::post('tenants/{tenant}/leave', [\App\Http\Controllers\TenantController::class, 'leave'])->name('tenants.leave');

    // Ensure an active tenant in the session
    Route::middleware(['ensure.tenant'])->group(function () {

        Route::get('/', function () {
            return redirect()->route('tenant.dashboard');
        })->name('home');

        Route::get('tenant/dashboard', \App\Http\Controllers\TenantDashboardController::class)->name('tenant.dashboard');

        Route::put('tenant', [\App\Http\Controllers\TenantController::class, 'update'])->name('tenant.update');
        Route::delete('tenant', [\App\Http\Controllers\TenantController::class, 'destroy'])
            ->middleware('password.confirm')
            ->name('tenant.destroy');

        Route::get('tenant/settings', [\App\Http\Controllers\TenantController::class, 'edit'])->name('tenant.edit');

        Route::get('tenant/members', [\App\Http\Controllers\TenantMemberController::class, 'index'])->name('tenant.members');
        Route::put('tenant/members/{user}', [\App\Http\Controllers\TenantMemberController::class, 'update'])->name('tenant.members.update');
        Route::delete('tenant/members/{user}', [\App\Http\Controllers\TenantMemberController::class, 'destroy'])->name('tenant.members.destroy');

    });

});

require __DIR__.'/settings.php';
