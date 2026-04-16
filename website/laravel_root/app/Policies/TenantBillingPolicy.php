<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;
use App\Enums\TenantPermissionName;

class TenantBillingPolicy
{
    /**
     * Determine whether the user can view billing information.
     */
    public function viewAny(User $user, Tenant $tenant): bool
    {
        return $user->hasTenantPermission($tenant, TenantPermissionName::ViewBillingInformation);
    }

    /**
     * Determine whether the user can update billing information.
     */
    public function update(User $user, Tenant $tenant): bool
    {
        return $user->hasTenantPermission($tenant, TenantPermissionName::EditBillingInformation);
    }
}
