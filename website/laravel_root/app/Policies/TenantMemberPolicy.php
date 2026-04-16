<?php
 
namespace App\Policies;
 
use App\Models\Tenant;
use App\Models\User;
use App\Enums\TenantPermissionName;
use App\Enums\TenantRoleName;
use App\Actions\TenantMember\GetManageableTenantRolesAction;
use App\Policies\Traits\ValidatesTenantRoles;
 
class TenantMemberPolicy
{
    use ValidatesTenantRoles;

    public function __construct(
        protected GetManageableTenantRolesAction $getManageableRoles
    ) {}

    /**
     * Determine whether the user can update the tenant member roles and permissions.
     *
     * @param array<int, string>|null $newRoles
     * @param array<int, string>|null $newPermissions
     */
    public function update(User $user, User $member, Tenant $tenant, ?array $newRoles = null, ?array $newPermissions = null): bool
    {
        if (!$user->hasTenantPermission($tenant, TenantPermissionName::UpdateTenantMembers)) {
            return false;
        }

        $manageableRoles = ($this->getManageableRoles)($user, $tenant);

        // Only enforce the "can manage current roles" gate when roles or permissions are being changed
        if ($newRoles !== null || $newPermissions !== null) {
            if (!$this->canManageCurrentRoles($member->getTenantRoleNames($tenant), $manageableRoles)) {
                return false;
            }
        }

        if (!$this->canAssignNewRolesAndPermissions($user, $tenant, $newRoles, $newPermissions, $manageableRoles)) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can remove the member from the tenant.
     */
    public function delete(User $user, User $member, Tenant $tenant): bool
    {
        if (!$user->hasTenantPermission($tenant, TenantPermissionName::DeleteTenantMembers)) {
            return false;
        }

        $manageableRoles = ($this->getManageableRoles)($user, $tenant);

        if (!$this->canManageCurrentRoles($member->getTenantRoleNames($tenant), $manageableRoles)) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can view the tenant members.
     */
    public function viewAny(User $user, Tenant $tenant): bool
    {
        return $user->hasTenantPermission($tenant, TenantPermissionName::ViewTenantMembers);
    }
}
