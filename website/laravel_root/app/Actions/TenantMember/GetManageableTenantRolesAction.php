<?php

namespace App\Actions\TenantMember;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Role;
use App\Enums\TenantRoleName;
use App\Enums\TenantPermissionName;

class GetManageableTenantRolesAction
{
    /**
     * Get the roles a user can assign or manage within a tenant.
     *
     * @return array<int, string>
     */
    public function __invoke(User $user, Tenant $tenant): array
    {
        if (!$user->hasTenantPermission($tenant, TenantPermissionName::ManageTenantMemberRoles)) {
            return [];
        }

        /** @var array<int, string> $allRoles */
        $allRoles = Role::where('guard_name', 'tenant')->pluck('name')->toArray();

        // Only an admin can manage the admin role
        if ($user->hasTenantRole($tenant, TenantRoleName::Admin)) {
            return $allRoles;
        }

        // Filter out the admin role for non-admins
        return array_values(array_filter($allRoles, function ($role) {
            return $role !== TenantRoleName::Admin->value;
        }));
    }
}
