<?php

use App\Enums\TenantRoleName;
use App\Models\Tenant;
use App\Models\User;

/** @var Tenant $tenant */
/** @var User $adminUser */
/** @var User $managerUser */
/** @var User $regularUser */

beforeEach(function () {
    /* --- Setup --- */
    // RequiredDataSeeder is handled in Pest.php
    
    $this->tenant = Tenant::factory()->create();

    // Create Base Users
    $this->adminUser   = createUserWithRole($this->tenant, TenantRoleName::Admin, ['password' => 'password']);
    $this->managerUser = createUserWithRole($this->tenant, TenantRoleName::Manager, ['password' => 'password']);
    $this->regularUser = User::factory()->create(['password' => 'password']);
    $this->regularUser->tenants()->attach($this->tenant->id);

    // Ensure session has active tenant
    $this->withSession(['active_tenant_uuid' => $this->tenant->uuid]);
});

/**
 * Helper to create a user with a specific tenant role.
 */
function createUserWithRole(Tenant $tenant, TenantRoleName $role, array $attributes = []): User
{
    $user = User::factory()->create($attributes);
    $user->tenants()->attach($tenant->id);
    $user->assignTenantRole($tenant, $role);

    return $user;
}

/*
|--------------------------------------------------------------------------
| Invitations (WIP)
|--------------------------------------------------------------------------
*/
describe('Invitations', function () {
    test('member can be invited to tenant (wip)', function () {
        /* --- Assertions --- */
        $this->markTestSkipped('Invitation logic is currently WIP.');
    });
});

/*
|--------------------------------------------------------------------------
| Member Management Constraints
|--------------------------------------------------------------------------
*/
describe('Member Management Constraints', function () {
    test('non-admin cannot update an admin member', function () {
        /* --- Setup --- */
        $anotherAdmin = createUserWithRole($this->tenant, TenantRoleName::Admin);

        /* --- Request --- */
        $response = $this->actingAs($this->managerUser)
            ->from(route('tenant.members'))
            ->put(route('tenant.members.update', $anotherAdmin), [
                'roles' => [TenantRoleName::Manager->value],
                'permissions' => [],
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect($response->getTargetUrl())->toBe(route('tenant.members'));

        /* --- Assert HTTP response message/error --- */
        expect(session('errors')->get('error'))->toContain(__('permissions.unauthorized'));

        /* --- Assert DB state --- */
        expect($anotherAdmin->fresh()->hasTenantRole($this->tenant, TenantRoleName::Admin))->toBeTrue();
    });

    test('admin can update another admin member', function () {
        /* --- Setup --- */
        $anotherAdmin = createUserWithRole($this->tenant, TenantRoleName::Admin);

        /* --- Request --- */
        $response = $this->actingAs($this->adminUser)
            ->from(route('tenant.members'))
            ->put(route('tenant.members.update', $anotherAdmin), [
                'roles' => [TenantRoleName::Manager->value],
                'permissions' => [],
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect(session('errors'))->toBeNull();
        expect($response->getTargetUrl())->toBe(route('tenant.members'));

        /* --- Assert HTTP response message/error --- */
        expect(session('status'))->toBe(__('actions.member_updated'));

        /* --- Assert DB state --- */
        expect($anotherAdmin->fresh()->hasTenantRole($this->tenant, TenantRoleName::Manager))->toBeTrue();
        expect($anotherAdmin->fresh()->hasTenantRole($this->tenant, TenantRoleName::Admin))->toBeFalse();
    });

    test('non-admin cannot delete an admin member', function () {
        /* --- Setup --- */
        $anotherAdmin = createUserWithRole($this->tenant, TenantRoleName::Admin);

        /* --- Request --- */
        $response = $this->actingAs($this->managerUser)
            ->from(route('tenant.members'))
            ->delete(route('tenant.members.destroy', $anotherAdmin));

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect($response->getTargetUrl())->toBe(route('tenant.members'));

        /* --- Assert HTTP response message/error --- */
        expect(session('errors')->get('error'))->toContain(__('permissions.unauthorized'));

        /* --- Assert DB state --- */
        expect($this->tenant->users()->where('users.id', $anotherAdmin->id)->exists())->toBeTrue();
    });

    test('admin can delete another admin member', function () {
        /* --- Setup --- */
        $anotherAdmin = createUserWithRole($this->tenant, TenantRoleName::Admin);

        /* --- Request --- */
        $response = $this->actingAs($this->adminUser)
            ->from(route('tenant.members'))
            ->delete(route('tenant.members.destroy', $anotherAdmin));

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect(session('errors'))->toBeNull();
        expect($response->getTargetUrl())->toBe(route('tenant.members'));
        
        /* --- Assert HTTP response message/error --- */
        expect(session('status'))->toBe(__('actions.member_removed'));

        /* --- Assert DB state --- */
        expect($this->tenant->users()->where('users.id', $anotherAdmin->id)->exists())->toBeFalse();
    });
});

/*
|--------------------------------------------------------------------------
| Leave Tenant Safeguards
|--------------------------------------------------------------------------
*/
describe('Leave Tenant Safeguards', function () {
    test('last admin cannot leave the tenant', function () {
        /* --- Setup --- */
        // adminUser is the last admin

        /* --- Request --- */
        $response = $this->actingAs($this->adminUser)
            ->from(route('tenant.members'))
            ->post(route('tenants.leave', $this->tenant), [
                'password' => 'password',
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302); // Redirect back with errors
        expect($response->getTargetUrl())->toBe(route('tenant.members'));
        
        /* --- Assert HTTP response message/error --- */
        expect(session('errors')->get('error'))->toContain(__('tenant.last_admin_safeguard'));

        /* --- Assert DB state --- */
        expect($this->tenant->users()->where('users.id', $this->adminUser->id)->exists())->toBeTrue();
    });

    test('admin can leave if another active admin exists', function () {
        /* --- Setup --- */
        createUserWithRole($this->tenant, TenantRoleName::Admin);

        /* --- Request --- */
        $response = $this->actingAs($this->adminUser)
            ->from(route('tenant.members'))
            ->post(route('tenants.leave', $this->tenant), [
                'password' => 'password',
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect(session('errors'))->toBeNull();
        expect($response->getTargetUrl())->toBe(route('tenants.index'));

        /* --- Assert HTTP response message/error --- */
        expect(session('status'))->toBe(__('tenant.left'));

        /* --- Assert DB state --- */
        expect($this->tenant->users()->where('users.id', $this->adminUser->id)->exists())->toBeFalse();
    });

    test('admin cannot leave if other admins are scheduled for deletion', function () {
        /* --- Setup --- */
        $otherAdmin = createUserWithRole($this->tenant, TenantRoleName::Admin);
        $otherAdmin->forceFill(['scheduled_for_deletion_at' => now()->addDays(10)])->save();

        /* --- Request --- */
        $response = $this->actingAs($this->adminUser)
            ->from(route('tenant.members'))
            ->post(route('tenants.leave', $this->tenant), [
                'password' => 'password',
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect($response->getTargetUrl())->toBe(route('tenant.members'));
        
        /* --- Assert HTTP response message/error --- */
        expect(session('errors')->get('error'))->toContain(__('tenant.last_admin_safeguard'));

        /* --- Assert DB state --- */
        expect($this->tenant->users()->where('users.id', $this->adminUser->id)->exists())->toBeTrue();
    });
});
