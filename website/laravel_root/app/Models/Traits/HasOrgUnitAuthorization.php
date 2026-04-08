<?php

namespace App\Models\Traits;

use App\Enums\OrgUnitPermissionName;
use App\Enums\OrgUnitRoleName;
use App\Models\OrganisationUnit;
use App\Models\Role;
use App\Models\Permission;

trait HasOrgUnitAuthorization
{
    /**
     * Assign a role to the model within a specific organisation unit context.
     */
    public function assignOrgUnitRole(OrganisationUnit $orgUnit, string|OrgUnitRoleName $role): self
    {
        $roleName = $role instanceof OrgUnitRoleName ? $role->value : $role;
        setPermissionsTeamId($orgUnit->id);

        $this->assignRole(Role::findByName($roleName, 'organisation_unit'));

        return $this;
    }

    /**
     * Assign a permission to the model within a specific organisation unit context.
     */
    public function assignOrgUnitPermission(OrganisationUnit $orgUnit, OrgUnitPermissionName $permission): self
    {
        $permissionName = $permission->value;
        setPermissionsTeamId($orgUnit->id);

        $this->givePermissionTo(Permission::findByName($permissionName, 'organisation_unit'));

        return $this;
    }

    /**
     * Sync roles for the model within a specific organisation unit context.
     *
     * @param array<int, string> $roles
     */
    public function syncOrgUnitRoles(OrganisationUnit $orgUnit, array $roles): self
    {
        setPermissionsTeamId($orgUnit->id);

        $roleModels = Role::whereIn('name', $roles)
            ->where('guard_name', 'organisation_unit')
            ->get();

        $this->syncRoles($roleModels);

        return $this;
    }

    /**
     * Sync permissions for the model within a specific organisation unit context.
     *
     * @param array<int, string> $permissions
     */
    public function syncOrgUnitPermissions(OrganisationUnit $orgUnit, array $permissions): self
    {
        setPermissionsTeamId($orgUnit->id);

        $permissionModels = Permission::whereIn('name', $permissions)
            ->where('guard_name', 'organisation_unit')
            ->get();

        $this->syncPermissions($permissionModels);

        return $this;
    }

    /**
     * Check if the model has a specific role within a specific organisation unit context.
     */
    public function hasOrgUnitRole(OrganisationUnit $orgUnit, string|OrgUnitRoleName $role): bool
    {
        $roleName = $role instanceof OrgUnitRoleName ? $role->value : $role;
        setPermissionsTeamId($orgUnit->id);

        return $this->hasRole($roleName, 'organisation_unit');
    }

    /**
     * Check if the model has a specific permission within a specific organisation unit context.
     */
    public function hasOrgUnitPermission(OrganisationUnit $orgUnit, string|OrgUnitPermissionName $permission): bool
    {
        $permissionName = $permission instanceof OrgUnitPermissionName ? $permission->value : $permission;
        setPermissionsTeamId($orgUnit->id);

        return $this->hasPermissionTo($permissionName, 'organisation_unit');
    }

    /**
     * Get the names of the roles assigned to the model within a specific organisation unit context.
     *
     * @return array<int, string>
     */
    public function getOrgUnitRoleNames(OrganisationUnit $orgUnit): array
    {
        setPermissionsTeamId($orgUnit->id);

        /** @var array<int, string> $names */
        $names = $this->getRoleNames()->toArray();

        return $names;
    }

    /**
     * Get the names of the permissions assigned to the model within a specific organisation unit context.
     *
     * @return array<int, string>
     */
    public function getOrgUnitPermissionNames(OrganisationUnit $orgUnit): array
    {
        setPermissionsTeamId($orgUnit->id);

        /** @var array<int, string> $names */
        $names = $this->getPermissionNames()->toArray();

        return $names;
    }
}
