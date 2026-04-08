<?php
 
namespace App\Policies;
 
use App\Models\Tenant;
use App\Models\User;
use App\Enums\TenantPermissionName;
use App\Enums\TenantRoleName;
 
class TenantMemberPolicy
{
    /**
     * Determine whether the user can update the tenant member roles and permissions.
     *
     * @param array<int, string>|null $newRoles
     * @param array<int, string>|null $newPermissions
     */
    public function update(User $user, User $member, Tenant $tenant, ?array $newRoles = null, ?array $newPermissions = null): bool
    {
        setPermissionsTeamId($tenant->id);

        if (!$user->hasPermissionTo(TenantPermissionName::UpdateTenantMembers->value, 'tenant')) {
            return false;
        }

        // Only an admin can update another admin
        $userIsAdmin = $user->hasTenantRole($tenant, TenantRoleName::Admin);
        if ($member->hasTenantRole($tenant, TenantRoleName::Admin) && !$userIsAdmin) {
            return false;
        }

        // Check permissions required for updating roles and permissions
        if ($newRoles !== null || $newPermissions !== null) {
            if (!$user->hasPermissionTo(TenantPermissionName::ManageTenantMemberRoles->value, 'tenant')) {
                return false;
            }

            // Only an admin can give the admin role to a member
            if ($newRoles !== null && in_array(TenantRoleName::Admin->value, $newRoles) && !$userIsAdmin) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine whether the user can remove the member from the tenant.
     */
    public function delete(User $user, User $member, Tenant $tenant): bool
    {
        setPermissionsTeamId($tenant->id);

        if (!$user->hasPermissionTo(TenantPermissionName::DeleteTenantMembers->value, 'tenant')) {
            return false;
        }

        // Only an admin can delete another admin
        if ($member->hasTenantRole($tenant, TenantRoleName::Admin) && 
            !$user->hasTenantRole($tenant, TenantRoleName::Admin)) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can view the tenant members.
     */
    public function viewAny(User $user, Tenant $tenant): bool
    {
        setPermissionsTeamId($tenant->id);

        return $user->hasPermissionTo(TenantPermissionName::ViewTenantMembers->value, 'tenant');
    }
}
