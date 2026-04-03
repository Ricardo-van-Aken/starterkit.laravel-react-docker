<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;
use App\Enums\TenantPermissionName;

class TenantRolePolicy
{
    /**
     * Determine whether the user can view any tenant roles.
     */
    public function viewAny(User $user, Tenant $tenant): bool
    {
        setPermissionsTeamId($tenant->id);

        return $user->hasPermissionTo(TenantPermissionName::ViewTenantRoles->value, 'tenant');
    }

    /**
     * Determine whether the user can create tenant roles.
     */
    public function create(User $user, Tenant $tenant): bool
    {
        setPermissionsTeamId($tenant->id);

        return $user->hasPermissionTo(TenantPermissionName::CreateTenantRoles->value, 'tenant');
    }

    /**
     * Determine whether the user can update tenant roles.
     */
    public function update(User $user, Tenant $tenant): bool
    {
        setPermissionsTeamId($tenant->id);

        return $user->hasPermissionTo(TenantPermissionName::EditTenantRoles->value, 'tenant');
    }

    // Since there's no DeleteTenantRoles enum currently, we omit a delete method for now.
}
