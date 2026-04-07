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
     * @param array<int, string> $newRoles
     */
    public function update(User $user, User $member, Tenant $tenant, array $newRoles = []): bool
    {
        setPermissionsTeamId($tenant->id);

        if (!$user->hasPermissionTo(TenantPermissionName::UpdateTenantMemberRoles->value, 'tenant')) {
            return false;
        }

        $userIsAdmin = $user->hasTenantRole($tenant, TenantRoleName::Admin);

        // Only an admin can update another admin
        if ($member->hasTenantRole($tenant, TenantRoleName::Admin) && !$userIsAdmin) {
            return false;
        }

        // Only an admin can give the admin role to a member
        if (in_array(TenantRoleName::Admin->value, $newRoles) && !$userIsAdmin) {
            return false;
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
