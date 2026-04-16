<?php

namespace App\Models\Traits;

use App\Enums\TenantPermissionName;
use App\Enums\TenantRoleName;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Tenant;
use App\Support\TeamScopedProxy;

trait HasTenantAuthorization
{
    /**
     * Helper to scope model operations to a specific tenant context.
     * 
     * @return TeamScopedProxy<$this>
     */
    public function forTenant(Tenant $tenant): TeamScopedProxy
    {
        return new TeamScopedProxy($this, $tenant->id);
    }

    /**
     * Assign a role to the model within a specific tenant context.
     */
    public function assignTenantRole(Tenant $tenant, string|TenantRoleName $role): self
    {
        $roleName = $role instanceof TenantRoleName ? $role->value : $role;
        
        $this->forTenant($tenant)->assignRole(Role::findByName($roleName, 'tenant'));

        return $this;
    }

    /**
     * Assign a permission to the model within a specific tenant context.
     */
    public function assignTenantPermission(Tenant $tenant, TenantPermissionName $permission): self
    {
        $this->forTenant($tenant)->givePermissionTo(Permission::findByName($permission->value, 'tenant'));

        return $this;
    }

    /**
     * Sync roles for the model within a specific tenant context.
     *
     * @param array<int, string> $roles
     */
    public function syncTenantRoles(Tenant $tenant, array $roles): self
    {
        $roleModels = Role::whereIn('name', $roles)
            ->where('guard_name', 'tenant')
            ->get();

        $this->forTenant($tenant)->syncRoles($roleModels);

        return $this;
    }

    /**
     * Sync permissions for the model within a specific tenant context.
     *
     * @param array<int, string> $permissions
     */
    public function syncTenantPermissions(Tenant $tenant, array $permissions): self
    {
        $permissionModels = Permission::whereIn('name', $permissions)
            ->where('guard_name', 'tenant')
            ->get();

        $this->forTenant($tenant)->syncPermissions($permissionModels);

        return $this;
    }

    /**
     * Check if the model has a specific role within a specific tenant context.
     */
    public function hasTenantRole(Tenant $tenant, string|TenantRoleName $role): bool
    {
        $roleName = $role instanceof TenantRoleName ? $role->value : $role;

        return $this->forTenant($tenant)->hasRole($roleName, 'tenant');
    }

    /**
     * Check if the model has a specific permission within a specific tenant context.
     */
    public function hasTenantPermission(Tenant $tenant, string|TenantPermissionName $permission): bool
    {
        $permissionName = $permission instanceof TenantPermissionName ? $permission->value : $permission;

        return $this->forTenant($tenant)->hasPermissionTo($permissionName, 'tenant');
    }

    /**
     * Get the names of the roles assigned to the model within a specific tenant context.
     *
     * @return array<int, string>
     */
    public function getTenantRoleNames(Tenant $tenant): array
    {
        /** @var array<int, string> $names */
        $names = $this->forTenant($tenant)->getRoleNames()->toArray();

        return $names;
    }

    /**
     * Get the names of the permissions assigned to the model within a specific tenant context.
     *
     * @return array<int, string>
     */
    public function getTenantPermissionNames(Tenant $tenant): array
    {
        /** @var array<int, string> $names */
        $names = $this->forTenant($tenant)->getPermissionNames()->toArray();

        return $names;
    }
}
