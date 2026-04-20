<?php

namespace App\Policies\Traits;

use App\Models\User;
use App\Models\Tenant;
use App\Enums\TenantPermissionName;

trait ValidatesTenantRoles
{
    /**
     * Get the subset of roles that the user has rank-based authority over.
     * Logic: Admins have authority over all roles. Non-admins have authority over all roles except Admin.
     *
     * @return array<int, string>
     */
    protected function getRankAuthoritativeRoles(User $user, Tenant $tenant): array
    {
        /** @var array<int, string> $allRoles */
        $allRoles = \App\Models\Role::where('guard_name', 'tenant')->pluck('name')->toArray();

        if ($user->hasTenantRole($tenant, \App\Enums\TenantRoleName::Admin)) {
            return $allRoles;
        }

        return array_values(array_filter($allRoles, function ($role) {
            return $role !== \App\Enums\TenantRoleName::Admin->value;
        }));
    }

    /**
     * Determine if a set of roles is a subset of an allowed set of roles.
     *
     * @param array<int, string> $roles
     * @param array<int, string> $allowedRoles
     */
    protected function isRolesSubset(array $roles, array $allowedRoles): bool
    {
        return count(array_diff($roles, $allowedRoles)) === 0;
    }

    /**
     * Determine if the user is authorized to assign the new array of roles and permissions.
     *
     * @param User $user
     * @param Tenant $tenant
     * @param array<int, string>|null $newRoles
     * @param array<int, string>|null $newPermissions
     * @param array<int, string> $manageableRoles
     * @param bool $isCreation
     */
    protected function canAssignNewRolesAndPermissions(
        User $user, 
        Tenant $tenant, 
        ?array $newRoles, 
        ?array $newPermissions, 
        array $manageableRoles,
        bool $isCreation = false
    ): bool {
        $isUpdatingRoles = false;
        $isUpdatingPermissions = false;
        $rolesToCheck = [];

        if ($isCreation) {
            // During creation, an empty array indicates no roles/permissions are assigned
            $isUpdatingRoles = !empty($newRoles);
            $isUpdatingPermissions = !empty($newPermissions);
            $rolesToCheck = $newRoles ?? [];
        } else {
            // During updates, null indicates that the existing roles/permissions should be left unchanged
            $isUpdatingRoles = $newRoles !== null;
            $isUpdatingPermissions = $newPermissions !== null;
            $rolesToCheck = $newRoles ?? [];
        }

        // If no new roles or permissions are being assigned, there is nothing to validate
        if (!$isUpdatingRoles && !$isUpdatingPermissions) {
            return true;
        }

        // The generic management permission is required to update roles or permissions
        if (!$user->hasTenantPermission($tenant, TenantPermissionName::ManageTenantMemberRoles)) {
            return false;
        }

        // If specific roles are being assigned, they must exist within the user's manageable subset
        if ($isUpdatingRoles) {
            if (!$this->isRolesSubset($rolesToCheck, $manageableRoles)) {
                return false;
            }
        }

        return true;
    }
}
