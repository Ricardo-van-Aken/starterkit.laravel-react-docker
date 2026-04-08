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
    public function create(User $user, Tenant $tenant, array $roles, array $permissions): bool
    {
        setPermissionsTeamId($tenant->id);

        if (! $user->hasPermissionTo(TenantPermissionName::InviteTenantMembers->value, 'tenant')) {
            return false;
        }

        // Check permissions required for updating roles and permissions
        if (count($roles) > 0 || count($permissions) > 0) {
            if (! $user->hasPermissionTo(TenantPermissionName::ManageTenantMemberRoles->value, 'tenant')) {
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
     * @param array<int, string>|null $newRoles
     * @param array<int, string>|null $newPermissions
     */
    public function update(User $user, TenantInvitation $invitation, ?array $newRoles = null, ?array $newPermissions = null): bool
    {
        $tenant = $invitation->tenant;
        setPermissionsTeamId($tenant->id);

        if (!$user->hasPermissionTo(TenantPermissionName::UpdateTenantMembers->value, 'tenant')) {
            return false;
        }

        // Only an admin can update an invitation that includes an admin role
        $userIsAdmin = $user->hasTenantRole($tenant, TenantRoleName::Admin);
        if ($invitation->hasTenantRole($tenant, TenantRoleName::Admin) && !$userIsAdmin) {
            return false;
        }

        // Check permissions required for updating roles and permissions
        if ($newRoles !== null || $newPermissions !== null) {
            if (!$user->hasPermissionTo(TenantPermissionName::ManageTenantMemberRoles->value, 'tenant')) {
                return false;
            }

            // Only an admin can assign the admin role
            if ($newRoles !== null && in_array(TenantRoleName::Admin->value, $newRoles) && !$userIsAdmin) {
                return false;
            }
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

        if (!$user->hasPermissionTo(TenantPermissionName::InviteTenantMembers->value, 'tenant')) {
            return false;
        }

        // Only an admin can delete an invitation that includes an admin role
        $userIsAdmin = $user->hasTenantRole($tenant, TenantRoleName::Admin);
        if ($invitation->hasTenantRole($tenant, TenantRoleName::Admin) && !$userIsAdmin) {
            return false;
        }

        return true;
    }
}
