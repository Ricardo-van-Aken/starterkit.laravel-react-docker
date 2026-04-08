<?php

namespace App\Models\Traits;

use App\Enums\TenantPermissionName;
use App\Enums\TenantRoleName;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Tenant;

trait HasTenantAuthorization
{
    /**
     * Assign a role to the model within a specific tenant context.
     */
    public function assignTenantRole(Tenant $tenant, string|TenantRoleName $role): self
    {
        $roleName = $role instanceof TenantRoleName ? $role->value : $role;
        setPermissionsTeamId($tenant->id);

        $this->assignRole(Role::findByName($roleName, 'tenant'));

        return $this;
    }

    /**
     * Assign a permission to the model within a specific tenant context.
     */
    public function assignTenantPermission(Tenant $tenant, TenantPermissionName $permission): self
    {
        $permissionName = $permission->value;
        setPermissionsTeamId($tenant->id);

        $this->givePermissionTo(Permission::findByName($permissionName, 'tenant'));

        return $this;
    }

    /**
     * Sync roles for the model within a specific tenant context.
     *
     * @param array<int, string> $roles
     */
    public function syncTenantRoles(Tenant $tenant, array $roles): self
    {
        setPermissionsTeamId($tenant->id);

        $roleModels = Role::whereIn('name', $roles)
            ->where('guard_name', 'tenant')
            ->get();

        $this->syncRoles($roleModels);

        return $this;
    }

    /**
     * Sync permissions for the model within a specific tenant context.
     *
     * @param array<int, string> $permissions
     */
    public function syncTenantPermissions(Tenant $tenant, array $permissions): self
    {
        setPermissionsTeamId($tenant->id);

        $permissionModels = Permission::whereIn('name', $permissions)
            ->where('guard_name', 'tenant')
            ->get();

        $this->syncPermissions($permissionModels);

        return $this;
    }

    /**
     * Check if the model has a specific role within a specific tenant context.
     */
    public function hasTenantRole(Tenant $tenant, string|TenantRoleName $role): bool
    {
        $roleName = $role instanceof TenantRoleName ? $role->value : $role;
        setPermissionsTeamId($tenant->id);

        return $this->hasRole($roleName, 'tenant');
    }

    /**
     * Check if the model has a specific permission within a specific tenant context.
     */
    public function hasTenantPermission(Tenant $tenant, string|TenantPermissionName $permission): bool
    {
        $permissionName = $permission instanceof TenantPermissionName ? $permission->value : $permission;
        setPermissionsTeamId($tenant->id);

        return $this->hasPermissionTo($permissionName, 'tenant');
    }

    /**
     * Get the names of the roles assigned to the model within a specific tenant context.
     *
     * @return array<int, string>
     */
    public function getTenantRoleNames(Tenant $tenant): array
    {
        setPermissionsTeamId($tenant->id);

        /** @var array<int, string> $names */
        $names = $this->getRoleNames()->toArray();

        return $names;
    }

    /**
     * Get the names of the permissions assigned to the model within a specific tenant context.
     *
     * @return array<int, string>
     */
    public function getTenantPermissionNames(Tenant $tenant): array
    {
        setPermissionsTeamId($tenant->id);

        /** @var array<int, string> $names */
        $names = $this->getPermissionNames()->toArray();

        return $names;
    }
}
