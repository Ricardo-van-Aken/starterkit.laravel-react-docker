<?php

namespace App\Policies;

use App\Enums\TenantPermissionName;
use App\Enums\TenantRoleName;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;

class TenantInvitationPolicy
{
    /**
     * Determine whether the user can create (send) an invitation.
     *
     * @param array<int, string> $roles
     * @param array<int, string> $permissions
     */
    public function create(User $user, Tenant $tenant, array $roles = [], array $permissions = []): bool
    {
        setPermissionsTeamId($tenant->id);

        if (! $user->hasPermissionTo(TenantPermissionName::InviteTenantMembers->value, 'tenant')) {
            return false;
        }

        if (count($roles) > 0 || count($permissions) > 0) {
            // Only a user with the updateTenantMembers permission can assign roles and/or permissions
            if (! $user->hasPermissionTo(TenantPermissionName::UpdateTenantMemberRoles->value, 'tenant')) {
                return false;
            }
            // Only an admin can give the admin role
            if (in_array(TenantRoleName::Admin->value, $roles) && !$user->hasTenantRole($tenant, TenantRoleName::Admin)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine whether the user can update a pending invitation's roles and permissions.
     *
     * @param array<int, string> $newRoles
     */
    public function update(User $user, TenantInvitation $invitation, array $newRoles = []): bool
    {
        $tenant = $invitation->tenant;

        setPermissionsTeamId($tenant->id);

        if (!$user->hasPermissionTo(TenantPermissionName::UpdateTenantMemberRoles->value, 'tenant')) {
            return false;
        }

        // Only an admin can assign the admin role
        if (in_array(TenantRoleName::Admin->value, $newRoles) && !$user->hasTenantRole($tenant, TenantRoleName::Admin)) {
            return false;
        }

        return true;
    }

    /**
     * The invited user (matched by email) can accept their own invitation.
     */
    public function accept(User $user, TenantInvitation $invitation): bool
    {
        return $user->email === $invitation->email;
    }

    /**
     * The invited user can decline their own invitation.
     */
    public function decline(User $user, TenantInvitation $invitation): bool
    {
        return $user->email === $invitation->email;
    }

    /**
     * A tenant member with InviteTenantMembers permission can revoke (destroy) invitations.
     */
    public function destroy(User $user, TenantInvitation $invitation): bool
    {
        $tenant = $invitation->tenant;
        setPermissionsTeamId($tenant->id);

        return $user->hasPermissionTo(TenantPermissionName::InviteTenantMembers->value, 'tenant');
    }
}
