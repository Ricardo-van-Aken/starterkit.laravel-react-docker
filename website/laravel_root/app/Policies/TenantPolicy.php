<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;
use App\Enums\TenantPermissionName;

class TenantPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->canCreateTenant();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Tenant $tenant): bool
    {
        // Use spatie/laravel-permission to check if user has permission in this tenant
        setPermissionsTeamId($tenant->id);
        
        return $user->hasPermissionTo(TenantPermissionName::UpdateTenantDetails->value, 'tenant');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tenant $tenant): bool
    {
        setPermissionsTeamId($tenant->id);
        
        return $user->hasPermissionTo(TenantPermissionName::DeleteTenant->value, 'tenant');
    }

    /**
     * Determine whether the user can switch to the model.
     */
    public function switch(User $user, Tenant $tenant): bool
    {
        return $user->tenants()->where('tenants.id', $tenant->id)->exists();
    }

    /**
     * Determine whether the user can view the tenant members.
     */
    public function viewMembers(User $user, Tenant $tenant): bool
    {
        setPermissionsTeamId($tenant->id);
        
        return $user->hasPermissionTo(TenantPermissionName::ViewTenantMembers->value, 'tenant');
    }

    /**
     * Determine whether the user can invite new members.
     */
    public function inviteMember(User $user, Tenant $tenant): bool
    {
        setPermissionsTeamId($tenant->id);
        
        return $user->hasPermissionTo(TenantPermissionName::InviteTenantMembers->value, 'tenant');
    }
}
