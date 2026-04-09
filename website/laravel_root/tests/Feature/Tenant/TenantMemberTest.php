<?php

use App\Enums\TenantPermissionName;
use App\Enums\TenantRoleName;
use App\Models\Tenant;
use App\Models\User;

/** @var Tenant $tenant */
/** @var User $user */

beforeEach(function () {
    /* --- Setup --- */
    // RequiredDataSeeder is handled in Pest.php
    
    $this->tenant = Tenant::factory()->create();

    // Create Base User
    $this->user = User::factory()->create(['password' => 'password']);
    $this->user->tenants()->attach($this->tenant->id);

    // Ensure session has active tenant
    $this->actingAs($this->user);
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
| Creating Members
|--------------------------------------------------------------------------
*/
describe('Creating Members', function () {
    // Current membership creation is handled through the Invitation flow.
    // Tests for Direct Member Creation can be added here in the future.
});

/*
|--------------------------------------------------------------------------
| Updating Members
|--------------------------------------------------------------------------
*/
describe('Updating Members', function () {
    test('non-admin cannot update an admin member', function () {
        /* --- Setup --- */
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::UpdateTenantMembers);
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::ManageTenantMemberRoles);
        $targetAdmin = createUserWithRole($this->tenant, TenantRoleName::Admin);

        /* --- Request --- */
        $response = $this->from(route('tenant.members'))
            ->patch(route('tenant.members.update', $targetAdmin), [
                'roles' => [TenantRoleName::Manager->value],
                'permissions' => [],
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect($response->getTargetUrl())->toBe(route('tenant.members'));

        /* --- Assert HTTP response message/error --- */
        expect(session('errors')->get('error'))->toContain(__('permissions.unauthorized'));

        /* --- Assert DB state --- */
        expect($targetAdmin->fresh()->hasTenantRole($this->tenant, TenantRoleName::Admin))->toBeTrue();
    });

    test('admin can update another admin member', function () {
        /* --- Setup --- */
        $this->user->assignTenantRole($this->tenant, TenantRoleName::Admin);
        $targetAdmin = createUserWithRole($this->tenant, TenantRoleName::Admin);

        /* --- Request --- */
        $response = $this->from(route('tenant.members'))
            ->patch(route('tenant.members.update', $targetAdmin), [
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
        expect($targetAdmin->fresh()->hasTenantRole($this->tenant, TenantRoleName::Manager))->toBeTrue();
        expect($targetAdmin->fresh()->hasTenantRole($this->tenant, TenantRoleName::Admin))->toBeFalse();
    });

    test('omitting roles and permissions preserves existing ones', function () {
        /* --- Setup --- */
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::UpdateTenantMembers);
        setPermissionsTeamId($this->tenant->id);
        $member = createUserWithRole($this->tenant, TenantRoleName::Manager);
        $member->assignTenantPermission($this->tenant, TenantPermissionName::UpdateTenantDetails);

        /* --- Request --- */
        $response = $this->patch(route('tenant.members.update', $member), []);

        /* --- Assert HTTP response status --- */
        $response->assertRedirect();
        expect(session('errors'))->toBeNull();

        /* --- Assert DB state --- */
        expect($member->fresh()->hasTenantRole($this->tenant, TenantRoleName::Manager))->toBeTrue();
        expect($member->fresh()->hasTenantPermission($this->tenant, TenantPermissionName::UpdateTenantDetails))->toBeTrue();
    });

    test('sending empty arrays clears roles and permissions', function () {
        /* --- Setup --- */
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::UpdateTenantMembers);
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::ManageTenantMemberRoles);
        setPermissionsTeamId($this->tenant->id);
        $member = createUserWithRole($this->tenant, TenantRoleName::Manager);
        $member->assignTenantPermission($this->tenant, TenantPermissionName::UpdateTenantDetails);

        /* --- Request --- */
        $response = $this->patch(route('tenant.members.update', $member), [
                'roles' => [],
                'permissions' => []
            ]);

        /* --- Assert HTTP response status --- */
        $response->assertRedirect();
        expect(session('errors'))->toBeNull();

        /* --- Assert DB state --- */
        expect($member->fresh()->roles()->wherePivot('team_id', $this->tenant->id)->count())->toBe(0);
        expect($member->fresh()->permissions()->wherePivot('team_id', $this->tenant->id)->count())->toBe(0);
    });

    test('can update permissions', function () {
        /* --- Setup --- */
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::UpdateTenantMembers);
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::ManageTenantMemberRoles);
        setPermissionsTeamId($this->tenant->id);
        $member = createUserWithRole($this->tenant, TenantRoleName::Manager);
        // Initially no extra permissions
        expect($member->hasTenantPermission($this->tenant, TenantPermissionName::UpdateTenantDetails))->toBeFalse();

        /* --- Request --- */
        $response = $this->patch(route('tenant.members.update', $member), [
                'permissions' => [TenantPermissionName::UpdateTenantDetails->value]
            ]);

        /* --- Assert HTTP response status --- */
        $response->assertRedirect();
        expect(session('errors'))->toBeNull();

        /* --- Assert DB state --- */
        // Role should be preserved
        expect($member->fresh()->hasTenantRole($this->tenant, TenantRoleName::Manager))->toBeTrue();
        // Permission should be added
        expect($member->fresh()->hasTenantPermission($this->tenant, TenantPermissionName::UpdateTenantDetails))->toBeTrue();
    });

    test('user with UpdateTenantMembers but without ManageTenantMemberRoles can perform a base update if no roles/permissions are sent', function () {
        /* --- Setup --- */
        setPermissionsTeamId($this->tenant->id);
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::UpdateTenantMembers);
        
        $member = createUserWithRole($this->tenant, TenantRoleName::Finance);

        /* --- Request --- */
        $response = $this->patch(route('tenant.members.update', $member), []);

        /* --- Assert --- */
        $response->assertRedirect();
        expect(session('errors'))->toBeNull();
        expect(session('status'))->toBe(__('actions.member_updated'));
    });

    test('user with UpdateTenantMembers but without ManageTenantMemberRoles cannot set roles', function () {
        /* --- Setup --- */
        setPermissionsTeamId($this->tenant->id);
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::UpdateTenantMembers);
        
        $member = createUserWithRole($this->tenant, TenantRoleName::Finance);
        $currentRoles = $member->getRoleNames()->toArray();

        /* --- Request --- */
        $response = $this->from(route('tenant.members'))
            ->patch(route('tenant.members.update', $member), [
                'roles' => $currentRoles,
            ]);

        /* --- Assert --- */
        expect($response->status())->toBe(302);
        expect(session('errors')->get('error'))->toContain(__('permissions.unauthorized'));
        expect($member->fresh()->hasTenantRole($this->tenant, TenantRoleName::Finance))->toBeTrue();
    });
});

/*
|--------------------------------------------------------------------------
| Deleting Members
|--------------------------------------------------------------------------
*/
describe('Deleting Members', function () {
    test('non-admin cannot delete an admin member', function () {
        /* --- Setup --- */
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::DeleteTenantMembers);
        $targetAdmin = createUserWithRole($this->tenant, TenantRoleName::Admin);

        /* --- Request --- */
        $response = $this->from(route('tenant.members'))
            ->delete(route('tenant.members.destroy', $targetAdmin));

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect($response->getTargetUrl())->toBe(route('tenant.members'));

        /* --- Assert HTTP response message/error --- */
        expect(session('errors')->get('error'))->toContain(__('permissions.unauthorized'));

        /* --- Assert DB state --- */
        expect($this->tenant->users()->where('users.id', $targetAdmin->id)->exists())->toBeTrue();
    });

    test('admin can delete another admin member', function () {
        /* --- Setup --- */
        $this->user->assignTenantRole($this->tenant, TenantRoleName::Admin);
        $targetAdmin = createUserWithRole($this->tenant, TenantRoleName::Admin);

        /* --- Request --- */
        $response = $this->from(route('tenant.members'))
            ->delete(route('tenant.members.destroy', $targetAdmin));

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect(session('errors'))->toBeNull();
        expect($response->getTargetUrl())->toBe(route('tenant.members'));
        
        /* --- Assert HTTP response message/error --- */
        expect(session('status'))->toBe(__('actions.member_removed'));

        /* --- Assert DB state --- */
        expect($this->tenant->users()->where('users.id', $targetAdmin->id)->exists())->toBeFalse();
    });

    test('last admin cannot leave the tenant', function () {
        /* --- Setup --- */
        $this->user->assignTenantRole($this->tenant, TenantRoleName::Admin);

        /* --- Request --- */
        $response = $this->from(route('tenant.members'))
            ->post(route('tenants.leave', $this->tenant), [
                'password' => 'password',
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302); // Redirect back with errors
        expect($response->getTargetUrl())->toBe(route('tenant.members'));
        
        /* --- Assert HTTP response message/error --- */
        expect(session('errors')->get('error'))->toContain(__('tenant.last_admin_safeguard'));

        /* --- Assert DB state --- */
        expect($this->tenant->users()->where('users.id', $this->user->id)->exists())->toBeTrue();
    });

    test('admin can leave if another active admin exists', function () {
        /* --- Setup --- */
        $this->user->assignTenantRole($this->tenant, TenantRoleName::Admin);
        createUserWithRole($this->tenant, TenantRoleName::Admin);

        /* --- Request --- */
        $response = $this->from(route('tenant.members'))
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
        expect($this->tenant->users()->where('users.id', $this->user->id)->exists())->toBeFalse();
    });

    test('admin cannot leave if other admins are scheduled for deletion', function () {
        /* --- Setup --- */
        $this->user->assignTenantRole($this->tenant, TenantRoleName::Admin);
        $otherAdmin = createUserWithRole($this->tenant, TenantRoleName::Admin);
        $otherAdmin->forceFill(['scheduled_for_deletion_at' => now()->addDays(10)])->save();

        /* --- Request --- */
        $response = $this->from(route('tenant.members'))
            ->post(route('tenants.leave', $this->tenant), [
                'password' => 'password',
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect($response->getTargetUrl())->toBe(route('tenant.members'));
        
        /* --- Assert HTTP response message/error --- */
        expect(session('errors')->get('error'))->toContain(__('tenant.last_admin_safeguard'));

        /* --- Assert DB state --- */
        expect($this->tenant->users()->where('users.id', $this->user->id)->exists())->toBeTrue();
    });
});
