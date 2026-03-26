<?php
 
namespace App\Policies;
 
use App\Models\Tenant;
use App\Models\User;
use App\Enums\TenantPermissionName;
 
class TenantMemberPolicy
{
    /**
     * Determine whether the user can update the tenant member roles and permissions.
     */
    public function update(User $user, User $member, Tenant $tenant): bool
    {
        setPermissionsTeamId($tenant->id);

        if (!$tenant->users()->where('users.id', $member->id)->exists()) {
            return false;
        }

        return $user->hasPermissionTo(TenantPermissionName::UpdateTenantMembers->value, 'tenant');
    }

    /**
     * Determine whether the user can remove the member from the tenant.
     */
    public function delete(User $user, User $member, Tenant $tenant): bool
    {
        setPermissionsTeamId($tenant->id);

        if (!$tenant->users()->where('users.id', $member->id)->exists()) {
            return false;
        }

        return $user->hasPermissionTo(TenantPermissionName::DeleteTenantMembers->value, 'tenant');
    }

    /**
     * Determine whether the user can view the tenant members.
     */
    public function viewAny(User $user, Tenant $tenant): bool
    {
        setPermissionsTeamId($tenant->id);

        return $user->hasPermissionTo(TenantPermissionName::ViewTenantMembers->value, 'tenant');
    }

    /**
     * Determine whether the user can invite new members.
     */
    public function invite(User $user, Tenant $tenant): bool
    {
        setPermissionsTeamId($tenant->id);

        return $user->hasPermissionTo(TenantPermissionName::InviteTenantMembers->value, 'tenant');
    }
}
