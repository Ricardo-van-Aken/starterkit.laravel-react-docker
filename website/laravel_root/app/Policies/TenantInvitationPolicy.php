<?php

namespace App\Policies;

use App\Enums\TenantPermissionName;
use App\Enums\TenantRoleName;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use App\Actions\TenantMember\GetManageableTenantRolesAction;
use App\Policies\Traits\ValidatesTenantRoles;
 
class TenantInvitationPolicy
{
    use ValidatesTenantRoles;

    public function __construct(
        protected GetManageableTenantRolesAction $getManageableRoles
    ) {}

    /**
     * Determine whether the user can create (send) an invitation.
     *
     * @param array<int, string> $roles
     * @param array<int, string> $permissions
     */
    public function create(User $user, Tenant $tenant, array $roles, array $permissions): bool
    {
        if (! $user->hasTenantPermission($tenant, TenantPermissionName::InviteTenantMembers)) {
            return false;
        }

        $manageableRoles = ($this->getManageableRoles)($user, $tenant);

        if (!$this->canAssignNewRolesAndPermissions($user, $tenant, $roles, $permissions, $manageableRoles, true)) {
            return false;
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

        if (!$user->hasTenantPermission($tenant, TenantPermissionName::UpdateTenantMembers)) {
            return false;
        }

        $manageableRoles = ($this->getManageableRoles)($user, $tenant);

        // Only enforce the "can manage current roles" gate when roles or permissions are being changed
        if ($newRoles !== null || $newPermissions !== null) {
            if (!$this->canManageCurrentRoles($invitation->getTenantRoleNames($tenant), $manageableRoles)) {
                return false;
            }
        }

        if (!$this->canAssignNewRolesAndPermissions($user, $tenant, $newRoles, $newPermissions, $manageableRoles)) {
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
     * A tenant member with InviteTenantMembers permission can revoke invitations.
     */
    public function revoke(User $user, TenantInvitation $invitation): bool
    {
        $tenant = $invitation->tenant;

        if (!$user->hasTenantPermission($tenant, TenantPermissionName::InviteTenantMembers)) {
            return false;
        }

        $manageableRoles = ($this->getManageableRoles)($user, $tenant);

        if (!$this->canManageCurrentRoles($invitation->getTenantRoleNames($tenant), $manageableRoles)) {
            return false;
        }

        return true;
    }

    /**
     * Deleting an invitation has the same requirements as revoking an invitation for now.
     */
    public function destroy(User $user, TenantInvitation $invitation): bool
    {
        return $this->revoke($user, $invitation);
    }
}
