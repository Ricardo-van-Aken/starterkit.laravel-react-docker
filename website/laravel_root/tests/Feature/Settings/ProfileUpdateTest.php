<?php

use App\Enums\TenantRoleName;
use App\Models\Tenant;
use App\Models\User;

/**
 * @property \App\Models\Tenant $tenant
 * @property \App\Models\User $user
 */

beforeEach(function () {
    /* --- Setup --- */
    $this->user = User::factory()->create([
        'name' => 'Test User Name',
    ]);
});

/**
 * Helper to create another admin for the tenant.
 */
function createAnotherAdmin(Tenant $tenant): User
{
    $user = User::factory()->create();
    $user->tenants()->attach($tenant->id);
    $user->assignTenantRole($tenant, TenantRoleName::Admin);

    return $user;
}

/*
|--------------------------------------------------------------------------
| Profile Information
|--------------------------------------------------------------------------
*/
describe('Profile Information', function () {
    test('profile page is displayed', function () {
        /* --- Request --- */
        $response = $this
            ->actingAs($this->user)
            ->get(route('profile.edit'));

        /* --- Assert HTTP response status --- */
        $response->assertOk();
    });

    test('profile information can be updated', function () {
        /* --- Request --- */
        $response = $this
            ->actingAs($this->user)
            ->from(route('profile.edit'))
            ->patch(route('profile.update'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect(session('errors'))->toBeNull();
        expect($response->getTargetUrl())->toBe(route('profile.edit'));

        /* --- Assert DB state --- */
        $this->user->refresh();
        expect($this->user->name)->toBe('Test User');
        expect($this->user->email)->toBe('test@example.com');
        expect($this->user->email_verified_at)->toBeNull();
    });

    test('email verification status is unchanged when the email address is unchanged', function () {
        /* --- Setup --- */
        $this->user->forceFill(['email_verified_at' => now()])->save();

        /* --- Request --- */
        $response = $this
            ->actingAs($this->user)
            ->from(route('profile.edit'))
            ->patch(route('profile.update'), [
                'name' => 'New Name',
                'email' => $this->user->email,
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect(session('errors'))->toBeNull();
        expect($response->getTargetUrl())->toBe(route('profile.edit'));

        /* --- Assert DB state --- */
        expect($this->user->refresh()->email_verified_at)->not->toBeNull();
    });
});

/*
|--------------------------------------------------------------------------
| Account Deletion
|--------------------------------------------------------------------------
*/
describe('Account Deletion', function () {
    test('user can schedule their account for deletion', function () {
        /* --- Request --- */
        $response = $this
            ->actingAs($this->user)
            ->from(route('profile.edit'))
            ->delete(route('profile.destroy'), [
                'password' => 'password',
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect(session('errors'))->toBeNull();
        expect($response->getTargetUrl())->toBe(route('deletion.notice'));

        /* --- Assert DB state --- */
        expect($this->user->fresh()->scheduled_for_deletion_at)->not->toBeNull();
    });

    test('correct password must be provided to delete account', function () {
        /* --- Request --- */
        $response = $this
            ->actingAs($this->user)
            ->from(route('profile.edit'))
            ->delete(route('profile.destroy'), [
                'password' => 'wrong-password',
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        $response->assertSessionHasErrors('password');
        expect($response->getTargetUrl())->toBe(route('profile.edit'));

        /* --- Assert DB state --- */
        expect($this->user->fresh()->scheduled_for_deletion_at)->toBeNull();
    });
});

/*
|--------------------------------------------------------------------------
| Account Deletion Safeguards (Last Admin)
|--------------------------------------------------------------------------
*/
describe('Account Deletion Safeguards', function () {
    beforeEach(function () {
        $this->tenant = Tenant::factory()->create();
        $this->user->tenants()->attach($this->tenant->id);
        $this->user->assignTenantRole($this->tenant, TenantRoleName::Admin);
    });

    test('last admin cannot delete their account without forcing tenant removal', function () {
        /* --- Request --- */
        $response = $this->actingAs($this->user)
            ->from(route('profile.edit'))
            ->delete(route('profile.destroy'), [
                'password' => 'password',
                'force_delete_tenants' => false,
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302); // Redirect back with errors
        expect($response->getTargetUrl())->toBe(route('profile.edit'));
        
        /* --- Assert HTTP response message/error --- */
        expect(session('errors')->get('error'))->toContain(__('tenant.last_admin_safeguard'));

        /* --- Assert DB state --- */
        expect($this->user->fresh()->scheduled_for_deletion_at)->toBeNull();
    });

    test('admin can schedule deletion if another active admin exists', function () {
        /* --- Setup --- */
        createAnotherAdmin($this->tenant);

        /* --- Request --- */
        $response = $this->actingAs($this->user)
            ->from(route('profile.edit'))
            ->delete(route('profile.destroy'), [
                'password' => 'password',
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect(session('errors'))->toBeNull();
        expect($response->getTargetUrl())->toBe(route('deletion.notice'));

        /* --- Assert DB state --- */
        expect($this->user->fresh()->scheduled_for_deletion_at)->not->toBeNull();
    });

    test('admin cannot schedule deletion if other admins are already scheduled for deletion', function () {
        /* --- Setup --- */
        $otherAdmin = createAnotherAdmin($this->tenant);
        $otherAdmin->forceFill(['scheduled_for_deletion_at' => now()->addDays(10)])->save();

        /* --- Request --- */
        $response = $this->actingAs($this->user)
            ->from(route('profile.edit'))
            ->delete(route('profile.destroy'), [
                'password' => 'password',
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302); // Redirect back with errors
        expect($response->getTargetUrl())->toBe(route('profile.edit'));
        
        /* --- Assert HTTP response message/error --- */
        expect(session('errors')->get('error'))->toContain(__('tenant.last_admin_safeguard'));

        /* --- Assert DB state --- */
        expect($this->user->fresh()->scheduled_for_deletion_at)->toBeNull();
    });

    test('admin can force delete account even if last admin', function () {
        /* --- Request --- */
        $response = $this->actingAs($this->user)
            ->from(route('profile.edit'))
            ->delete(route('profile.destroy'), [
                'password' => 'password',
                'force_delete_tenants' => true,
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect(session('errors'))->toBeNull();
        expect($response->getTargetUrl())->toBe(route('deletion.notice'));

        /* --- Assert DB state --- */
        expect($this->user->fresh()->scheduled_for_deletion_at)->not->toBeNull();
    });
});

/*
|--------------------------------------------------------------------------
| Account Restoration
|--------------------------------------------------------------------------
*/
describe('Account Restoration', function () {
    test('user can undo account deletion', function () {
        /* --- Setup --- */
        $this->user->forceFill(['scheduled_for_deletion_at' => now()->addDays(30)])->save();

        /* --- Request --- */
        $response = $this
            ->actingAs($this->user)
            ->post(route('deletion.restore'));

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect(session('errors'))->toBeNull();
        expect($response->getTargetUrl())->toBe(route('dashboard'));

        /* --- Assert HTTP response message/error --- */
        expect(session('status'))->toBe('Account restored successfully.');

        /* --- Assert DB state --- */
        expect($this->user->fresh()->scheduled_for_deletion_at)->toBeNull();
    });
});