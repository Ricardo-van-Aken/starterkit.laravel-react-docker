<?php

namespace Tests\Feature\Tenant;

use App\Enums\TenantInvitationStatus;
use App\Enums\TenantPermissionName;
use App\Enums\TenantRoleName;
use App\Models\AccountInvitation;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use App\Mail\ClaimAccountMail;
use App\Mail\TenantInvitationMail;
use Illuminate\Support\Facades\Mail;

/** 
 * @var \Tests\TestCase $this
 * @var User $user 
 * @var Tenant $tenant 
 * @var TenantInvitation $invitation
 * @var User $invitedUser
 */

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create();
    $this->user->tenants()->attach($this->tenant->id);
    $this->actingAs($this->user);
    $this->withSession(['active_tenant_uuid' => $this->tenant->uuid]);
});

/*
|--------------------------------------------------------------------------
| Creating Invitations
|--------------------------------------------------------------------------
*/
describe('Creating Invitations', function () {
    test('user with permission can invite an existing user', function () {
        /* --- Setup --- */
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::InviteTenantMembers);
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::UpdateTenantMemberRoles);
        Mail::fake();
        $existingUser = User::factory()->create();

        /* --- Request --- */
        $response = $this->from(route('tenant.members'))
            ->post(route('tenant.invitations.store'), [
                'email' => $existingUser->email,
                'roles' => [TenantRoleName::Finance->value],
                'permissions' => [],
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect(session('errors'))->toBeNull();
        expect($response->getTargetUrl())->toBe(route('tenant.members'));

        /* --- Assert HTTP response message/error --- */
        expect(session('status'))->toBe(__('invitations.created'));

        /* --- Assert DB State --- */
        $this->assertDatabaseHas('tenant_invitations', [
            'tenant_id' => $this->tenant->id,
            'email' => $existingUser->email,
            'status' => TenantInvitationStatus::Pending->value,
        ]);

        $invitation = TenantInvitation::where('email', $existingUser->email)->first();
        expect($invitation->roles)->toContain(TenantRoleName::Finance->value);

        Mail::assertSent(TenantInvitationMail::class, function ($mail) use ($existingUser) {
            return $mail->hasTo($existingUser->email);
        });
        Mail::assertNotSent(ClaimAccountMail::class);
    });

    test('user with permission can invite a non-existing user', function () {
        /* --- Setup --- */
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::InviteTenantMembers);
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::UpdateTenantMemberRoles);
        Mail::fake();
        $newEmail = 'new-user@example.com';

        /* --- Request --- */
        $response = $this->from(route('tenant.members'))
            ->post(route('tenant.invitations.store'), [
                'email' => $newEmail,
                'roles' => [TenantRoleName::Finance->value],
                'permissions' => [],
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect(session('errors'))->toBeNull();

        /* --- Assert DB State --- */
        $this->assertDatabaseHas('account_invitations', [
            'email' => $newEmail,
        ]);
        $this->assertDatabaseHas('tenant_invitations', [
            'tenant_id' => $this->tenant->id,
            'email' => $newEmail,
            'status' => TenantInvitationStatus::Pending->value,
        ]);

        Mail::assertSent(ClaimAccountMail::class, function ($mail) use ($newEmail) {
            return $mail->hasTo($newEmail);
        });
        Mail::assertSent(TenantInvitationMail::class, function ($mail) use ($newEmail) {
            return $mail->hasTo($newEmail);
        });
    });

    test('cannot invite a user who is already a member', function () {
        /* --- Setup --- */
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::InviteTenantMembers);
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::UpdateTenantMemberRoles);
        $member = User::factory()->create();
        $member->tenants()->attach($this->tenant->id);

        /* --- Request --- */
        $response = $this->from(route('tenant.members'))
            ->post(route('tenant.invitations.store'), [
                'email' => $member->email,
                'roles' => [TenantRoleName::Finance->value],
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);

        /* --- Assert HTTP response message/error --- */
        expect(session('errors')->get('error'))->toBe(__('invitations.user_already_member'));
    });

    test('cannot invite a user if a pending invitation already exists', function () {
        /* --- Setup --- */
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::InviteTenantMembers);
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::UpdateTenantMemberRoles);
        $email = 'someone@example.com';
        TenantInvitation::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => $email,
            'status' => TenantInvitationStatus::Pending,
        ]);

        /* --- Request --- */
        $response = $this->from(route('tenant.members'))
            ->post(route('tenant.invitations.store'), [
                'email' => $email,
                'roles' => [TenantRoleName::Finance->value],
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);

        /* --- Assert HTTP response message/error --- */
        expect(session('errors')->get('error'))->toBe(__('invitations.already_exists'));
    });

    test('user without permission cannot invite a user', function () {
        /* --- Setup --- */
        // Finance has no member management permissions
        setPermissionsTeamId($this->tenant->id);

        /* --- Request --- */
        $response = $this->from(route('tenant.members'))
            ->post(route('tenant.invitations.store'), [
                'email' => 'hacker@example.com',
                'roles' => [TenantRoleName::Finance->value],
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);

        /* --- Assert HTTP response message/error --- */
        expect(session('errors')->get('error'))->toContain(__('permissions.unauthorized'));

        /* --- Assert DB State --- */
        $this->assertDatabaseMissing('tenant_invitations', [
            'email' => 'hacker@example.com',
        ]);
    });

    test('admin can assign the admin role in an invitation', function () {
        /* --- Setup --- */
        $this->user->assignTenantRole($this->tenant, TenantRoleName::Admin);

        /* --- Request --- */
        $response = $this->from(route('tenant.members'))
            ->post(route('tenant.invitations.store'), [
                'email' => 'newadmin@example.com',
                'roles' => [TenantRoleName::Admin->value],
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect(session('errors'))->toBeNull();

        /* --- Assert DB State --- */
        $invitation = TenantInvitation::where('email', 'newadmin@example.com')->first();
        expect($invitation->roles)->toContain(TenantRoleName::Admin->value);
    });

    test('non-admin cannot assign the admin role in an invitation', function () {
        /* --- Setup --- */
        // Manager role has invite and update permissions, but is not an Admin
        setPermissionsTeamId($this->tenant->id);
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::InviteTenantMembers);
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::UpdateTenantMemberRoles);

        /* --- Request --- */
        $response = $this->from(route('tenant.members'))
            ->post(route('tenant.invitations.store'), [
                'email' => 'newadmin@example.com',
                'roles' => [TenantRoleName::Admin->value],
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);

        /* --- Assert HTTP response message/error --- */
        expect(session('errors')->get('error'))->toContain(__('permissions.unauthorized'));

        /* --- Assert DB State --- */
        $this->assertDatabaseMissing('tenant_invitations', [
            'email' => 'newadmin@example.com',
        ]);
    });

    test('user with only invite permission can invite without roles', function () {
        /* --- Setup --- */
        setPermissionsTeamId($this->tenant->id);
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::InviteTenantMembers);

        /* --- Request --- */
        $response = $this->from(route('tenant.members'))
            ->post(route('tenant.invitations.store'), [
                'email' => 'onlyinvite@example.com',
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect(session('status'))->toBe(__('invitations.created'));
    });

    test('user with only invite permission cannot assign roles', function () {
        /* --- Setup --- */
        setPermissionsTeamId($this->tenant->id);
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::InviteTenantMembers);

        /* --- Request --- */
        $response = $this->from(route('tenant.members'))
            ->post(route('tenant.invitations.store'), [
                'email' => 'tryroles@example.com',
                'roles' => [TenantRoleName::Finance->value],
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect(session('errors')->get('error'))->toContain(__('permissions.unauthorized'));

        /* --- Assert DB State --- */
        $this->assertDatabaseMissing('tenant_invitations', [
            'email' => 'tryroles@example.com',
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| Updating Invitations
|--------------------------------------------------------------------------
*/
describe('Updating Invitations', function () {
    beforeEach(function () {
        $this->invitation = TenantInvitation::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => TenantInvitationStatus::Pending,
            'roles' => [TenantRoleName::Manager->value],
        ]);
    });

    test('user with permission can update an invitation', function () {
        /* --- Setup --- */
        setPermissionsTeamId($this->tenant->id);
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::UpdateTenantMemberRoles);

        /* --- Request --- */
        $response = $this->from(route('tenant.members'))
            ->put(route('tenant.invitations.update', $this->invitation), [
                'roles' => [TenantRoleName::Finance->value],
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect(session('errors'))->toBeNull();
        expect($response->getTargetUrl())->toBe(route('tenant.members'));

        /* --- Assert HTTP response message/error --- */
        expect(session('status'))->toBe(__('invitations.updated'));

        /* --- Assert DB State --- */
        expect($this->invitation->fresh()->roles)->toContain(TenantRoleName::Finance->value);
        expect($this->invitation->fresh()->roles)->not->toContain(TenantRoleName::Manager->value);
    });

    test('admin can assign the admin role when updating an invitation', function () {
        /* --- Setup --- */
        $this->user->assignTenantRole($this->tenant, TenantRoleName::Admin);

        /* --- Request --- */
        $response = $this->from(route('tenant.members'))
            ->put(route('tenant.invitations.update', $this->invitation), [
                'roles' => [TenantRoleName::Admin->value],
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect(session('errors'))->toBeNull();

        /* --- Assert DB State --- */
        expect($this->invitation->fresh()->roles)->toContain(TenantRoleName::Admin->value);
    });

    test('non-admin cannot assign the admin role when updating an invitation', function () {
        /* --- Setup --- */
        setPermissionsTeamId($this->tenant->id);
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::UpdateTenantMemberRoles);

        /* --- Request --- */
        $response = $this->from(route('tenant.members'))
            ->put(route('tenant.invitations.update', $this->invitation), [
                'roles' => [TenantRoleName::Admin->value],
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);

        /* --- Assert HTTP response message/error --- */
        expect(session('errors')->get('error'))->toContain(__('permissions.unauthorized'));

        /* --- Assert DB State --- */
        expect($this->invitation->fresh()->roles)->not->toContain(TenantRoleName::Admin->value);
    });

    test('user without proper update roles permission cannot update an invitation', function () {
        /* --- Setup --- */
        setPermissionsTeamId($this->tenant->id);

        /* --- Request --- */
        $response = $this->from(route('tenant.members'))
            ->put(route('tenant.invitations.update', $this->invitation), [
                'roles' => [TenantRoleName::Finance->value],
            ]);

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);

        /* --- Assert HTTP response message/error --- */
        expect(session('errors')->get('error'))->toContain(__('permissions.unauthorized'));
    });
});

/*
|--------------------------------------------------------------------------
| Deleting Invitations
|--------------------------------------------------------------------------
*/
describe('Deleting Invitations', function () {
    beforeEach(function () {
        $this->invitation = TenantInvitation::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => TenantInvitationStatus::Pending,
        ]);
    });

    test('user with permission can delete a pending invitation', function () {
        /* --- Setup --- */
        setPermissionsTeamId($this->tenant->id);
        $this->user->assignTenantPermission($this->tenant, TenantPermissionName::InviteTenantMembers);

        /* --- Request --- */
        $response = $this->from(route('tenant.members'))
            ->delete(route('tenant.invitations.destroy', $this->invitation));

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);
        expect(session('errors'))->toBeNull();
        expect($response->getTargetUrl())->toBe(route('tenant.members'));

        /* --- Assert HTTP response message/error --- */
        expect(session('status'))->toBe(__('invitations.deleted'));

        /* --- Assert DB State --- */
        $this->assertDatabaseMissing('tenant_invitations', [
            'id' => $this->invitation->id,
        ]);
    });

    test('user without permission cannot delete a pending invitation', function () {
        /* --- Setup --- */
        setPermissionsTeamId($this->tenant->id);

        /* --- Request --- */
        $response = $this->from(route('tenant.members'))
            ->delete(route('tenant.invitations.destroy', $this->invitation));

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);

        /* --- Assert HTTP response message/error --- */
        expect(session('errors')->get('error'))->toContain(__('permissions.unauthorized'));

        /* --- Assert DB State --- */
        $this->assertDatabaseHas('tenant_invitations', [
            'id' => $this->invitation->id,
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| Accepting Invitations
|--------------------------------------------------------------------------
*/
describe('Accepting Invitations', function () {
    beforeEach(function () {
        $this->invitedUser = User::factory()->create();
        $this->invitation = TenantInvitation::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => $this->invitedUser->email,
            'roles' => [TenantRoleName::Finance->value],
            'status' => TenantInvitationStatus::Pending,
        ]);
    });

    test('invited user can accept their invitation', function () {
        /* --- Setup --- */
        $this->actingAs($this->invitedUser);

        /* --- Request --- */
        $response = $this->post(route('tenant-invitations.accept', $this->invitation));

        /* --- Assert HTTP response status --- */
        $response->assertRedirect(route('dashboard'));

        /* --- Assert DB State --- */
        expect($this->invitation->fresh()->status)->toBe(TenantInvitationStatus::Accepted);
        expect($this->tenant->users->contains($this->invitedUser))->toBeTrue();
        expect($this->invitedUser->fresh()->hasTenantRole($this->tenant, TenantRoleName::Finance))->toBeTrue();
    });

    test('user cannot accept an invitation that is already accepted or declined', function () {
        /* --- Setup --- */
        $this->actingAs($this->invitedUser);
        $this->invitation->update(['status' => TenantInvitationStatus::Accepted]);

        /* --- Request --- */
        $response = $this->post(route('tenant-invitations.accept', $this->invitation));

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);

        /* --- Assert HTTP response message/error --- */
        expect(session('errors')->get('error'))->toBe(__('invitations.already_processed'));
    });

    test('user cannot accept another person\'s invitation', function () {
        /* --- Setup --- */
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        /* --- Request --- */
        $response = $this->post(route('tenant-invitations.accept', $this->invitation));

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);

        /* --- Assert HTTP response message/error --- */
        expect(session('errors')->get('error'))->toContain(__('permissions.unauthorized'));
    });
});

/*
|--------------------------------------------------------------------------
| Declining Invitations
|--------------------------------------------------------------------------
*/
describe('Declining Invitations', function () {
    beforeEach(function () {
        $this->invitedUser = User::factory()->create();
        $this->invitation = TenantInvitation::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => $this->invitedUser->email,
            'status' => TenantInvitationStatus::Pending,
        ]);
    });

    test('invited user can decline their invitation', function () {
        /* --- Setup --- */
        $this->actingAs($this->invitedUser);

        /* --- Request --- */
        $response = $this->post(route('tenant-invitations.decline', $this->invitation));

        /* --- Assert HTTP response status --- */
        $response->assertRedirect(route('dashboard'));

        /* --- Assert DB State --- */
        expect($this->invitation->fresh()->status)->toBe(TenantInvitationStatus::Declined);
    });

    test('user cannot decline an invitation that is already processed', function () {
        /* --- Setup --- */
        $this->actingAs($this->invitedUser);
        $this->invitation->update(['status' => TenantInvitationStatus::Declined]);

        /* --- Request --- */
        $response = $this->post(route('tenant-invitations.decline', $this->invitation));

        /* --- Assert HTTP response status --- */
        expect($response->status())->toBe(302);

        /* --- Assert HTTP response message/error --- */
        expect(session('errors')->get('error'))->toBe(__('invitations.already_processed'));
    });
});
