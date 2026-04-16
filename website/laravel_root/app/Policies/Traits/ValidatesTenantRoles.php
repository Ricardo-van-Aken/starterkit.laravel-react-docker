<?php

namespace App\Policies\Traits;

use App\Models\User;
use App\Models\Tenant;
use App\Enums\TenantPermissionName;

trait ValidatesTenantRoles
{
    /**
     * Determine if the user is authorized to manage a member or invitation based on their current roles.
     *
     * @param array<int, string> $currentRoles
     * @param array<int, string> $manageableRoles
     */
    protected function canManageCurrentRoles(array $currentRoles, array $manageableRoles): bool
    {
        return count(array_diff($currentRoles, $manageableRoles)) === 0;
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
            $hasUnmanageableRoles = count(array_diff($rolesToCheck, $manageableRoles)) > 0;
            
            if ($hasUnmanageableRoles) {
                return false;
            }
        }

        return true;
    }
}
