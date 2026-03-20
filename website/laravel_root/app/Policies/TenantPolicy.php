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
}
