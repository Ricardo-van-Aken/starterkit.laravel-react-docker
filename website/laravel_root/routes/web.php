<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

use App\Services\ActiveTenant;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        /** @var \App\Models\User $user */
        $user = request()->user();

        $invitations = \App\Models\TenantInvitation::with('tenant')
            ->where('email', $user->email)
            ->get();
            
        return Inertia::render('dashboard', [
            'invitations' => $invitations,
        ]);
    })->name('dashboard');

    // Tenant Invitations
    Route::post('tenant-invitations/{tenantInvitation}/accept', [\App\Http\Controllers\TenantInvitationController::class, 'accept'])->name('tenant-invitations.accept');
    Route::post('tenant-invitations/{tenantInvitation}/decline', [\App\Http\Controllers\TenantInvitationController::class, 'decline'])->name('tenant-invitations.decline');

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
        Route::delete('tenant', [\App\Http\Controllers\TenantController::class, 'destroy'])->name('tenant.destroy');

        Route::get('tenant/settings', [\App\Http\Controllers\TenantController::class, 'edit'])->name('tenant.edit');

        Route::get('tenant/members', [\App\Http\Controllers\TenantMemberController::class, 'index'])->name('tenant.members');
        Route::put('tenant/members/{user}', [\App\Http\Controllers\TenantMemberController::class, 'update'])->name('tenant.members.update');
        Route::delete('tenant/members/{user}', [\App\Http\Controllers\TenantMemberController::class, 'destroy'])->name('tenant.members.destroy');

        Route::post('tenant/invitations', [\App\Http\Controllers\TenantInvitationController::class, 'store'])->name('tenant.invitations.store');
        Route::put('tenant/invitations/{tenantInvitation}', [\App\Http\Controllers\TenantInvitationController::class, 'update'])->name('tenant.invitations.update');
        Route::delete('tenant/invitations/{tenantInvitation}', [\App\Http\Controllers\TenantInvitationController::class, 'destroy'])->name('tenant.invitations.destroy');
    });

    // Account Deletion Hold Area
    Route::get('deletion-notice', [\App\Http\Controllers\Auth\AccountDeletionNoticeController::class, 'show'])->name('deletion.notice');
    Route::post('deletion-restore', [\App\Http\Controllers\Auth\AccountDeletionNoticeController::class, 'restore'])->name('deletion.restore');

});

require __DIR__.'/settings.php';

// Pending Account Ownership
Route::middleware(['signed'])->group(function () {
    Route::get('invitation/{token}', [\App\Http\Controllers\Auth\TakeOwnershipController::class, 'edit'])->name('invitation.edit');
    Route::post('invitation/{token}', [\App\Http\Controllers\Auth\TakeOwnershipController::class, 'update'])->name('invitation.update');
});

// Email-based Invitation Actions
Route::get('invitations/email-accept/{token}', [\App\Http\Controllers\TenantInvitationController::class, 'acceptByToken'])->name('invitations.email-accept');
Route::get('invitations/email-decline/{token}', [\App\Http\Controllers\TenantInvitationController::class, 'declineByToken'])->name('invitations.email-decline');
