<?php
 
namespace App\Policies;
 
use App\Models\Tenant;
use App\Models\User;
use App\Enums\TenantPermissionName;
use App\Enums\TenantRoleName;
use App\Actions\TenantMember\GetAssignableTenantRolesAction;
use App\Policies\Traits\ValidatesTenantRoles;
 
class TenantMemberPolicy
{
    use ValidatesTenantRoles;

    public function __construct(
        protected GetAssignableTenantRolesAction $getAssignableRoles
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

        /** @var array<int, string> $rankAuthoritativeRoles */
        $rankAuthoritativeRoles = $this->getRankAuthoritativeRoles($user, $tenant);

        // RANK AUTHORITY CHECK: Can I update this member based on their current roles?
        /** @var array<int, string> $memberRoles */
        $memberRoles = $member->getTenantRoleNames($tenant);
        if (!$this->isRolesSubset($memberRoles, $rankAuthoritativeRoles)) {
            return false;
        }

        $assignableRoles = ($this->getAssignableRoles)($user, $tenant);

        // ASSIGNMENT CHECK: Can I assign the new roles and permissions to this member?
        if (!$this->canAssignNewRolesAndPermissions($user, $tenant, $newRoles, $newPermissions, $assignableRoles)) {
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

        /** @var array<int, string> $rankAuthoritativeRoles */
        $rankAuthoritativeRoles = $this->getRankAuthoritativeRoles($user, $tenant);

        // RANK AUTHORITY CHECK: Can I delete this member based on their current roles?
        /** @var array<int, string> $memberRoles */
        $memberRoles = $member->getTenantRoleNames($tenant);
        if (!$this->isRolesSubset($memberRoles, $rankAuthoritativeRoles)) {
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
