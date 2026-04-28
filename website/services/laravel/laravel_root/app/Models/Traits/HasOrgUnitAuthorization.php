<?php

namespace App\Models\Traits;

use App\Enums\OrgUnitPermissionName;
use App\Enums\OrgUnitRoleName;
use App\Models\OrganisationUnit;
use App\Models\Role;
use App\Models\Permission;
use App\Support\TeamScopedProxy;

trait HasOrgUnitAuthorization
{
    /**
     * Helper to scope model operations to a specific organisation unit context.
     * 
     * @return TeamScopedProxy<$this>
     */
    public function forOrgUnit(OrganisationUnit $orgUnit): TeamScopedProxy
    {
        return new TeamScopedProxy($this, $orgUnit->id);
    }

    /**
     * Assign a role to the model within a specific organisation unit context.
     */
    public function assignOrgUnitRole(OrganisationUnit $orgUnit, string|OrgUnitRoleName $role): self
    {
        $roleName = $role instanceof OrgUnitRoleName ? $role->value : $role;
        
        $this->forOrgUnit($orgUnit)->assignRole(Role::findByName($roleName, 'organisation_unit'));

        return $this;
    }

    /**
     * Assign a permission to the model within a specific organisation unit context.
     */
    public function assignOrgUnitPermission(OrganisationUnit $orgUnit, OrgUnitPermissionName $permission): self
    {
        $this->forOrgUnit($orgUnit)->givePermissionTo(Permission::findByName($permission->value, 'organisation_unit'));

        return $this;
    }

    /**
     * Sync roles for the model within a specific organisation unit context.
     *
     * @param array<int, string> $roles
     */
    public function syncOrgUnitRoles(OrganisationUnit $orgUnit, array $roles): self
    {
        $roleModels = Role::whereIn('name', $roles)
            ->where('guard_name', 'organisation_unit')
            ->get();

        $this->forOrgUnit($orgUnit)->syncRoles($roleModels);

        return $this;
    }

    /**
     * Sync permissions for the model within a specific organisation unit context.
     *
     * @param array<int, string> $permissions
     */
    public function syncOrgUnitPermissions(OrganisationUnit $orgUnit, array $permissions): self
    {
        $permissionModels = Permission::whereIn('name', $permissions)
            ->where('guard_name', 'organisation_unit')
            ->get();

        $this->forOrgUnit($orgUnit)->syncPermissions($permissionModels);

        return $this;
    }

    /**
     * Check if the model has a specific role within a specific organisation unit context.
     */
    public function hasOrgUnitRole(OrganisationUnit $orgUnit, string|OrgUnitRoleName $role): bool
    {
        $roleName = $role instanceof OrgUnitRoleName ? $role->value : $role;

        return $this->forOrgUnit($orgUnit)->hasRole($roleName, 'organisation_unit');
    }

    /**
     * Check if the model has a specific permission within a specific organisation unit context.
     */
    public function hasOrgUnitPermission(OrganisationUnit $orgUnit, string|OrgUnitPermissionName $permission): bool
    {
        $permissionName = $permission instanceof OrgUnitPermissionName ? $permission->value : $permission;

        return $this->forOrgUnit($orgUnit)->hasPermissionTo($permissionName, 'organisation_unit');
    }

    /**
     * Get the names of the roles assigned to the model within a specific organisation unit context.
     *
     * @return array<int, string>
     */
    public function getOrgUnitRoleNames(OrganisationUnit $orgUnit): array
    {
        /** @var array<int, string> $names */
        $names = $this->forOrgUnit($orgUnit)->getRoleNames()->toArray();

        return $names;
    }

    /**
     * Get the names of the permissions assigned to the model within a specific organisation unit context.
     *
     * @return array<int, string>
     */
    public function getOrgUnitPermissionNames(OrganisationUnit $orgUnit): array
    {
        /** @var array<int, string> $names */
        $names = $this->forOrgUnit($orgUnit)->getPermissionNames()->toArray();

        return $names;
    }
}
