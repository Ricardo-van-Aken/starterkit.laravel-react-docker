<?php

namespace App\Actions\Tenant;

use App\Models\Tenant;
use App\Models\User;
use App\Enums\TenantRoleName;
use Illuminate\Support\Facades\DB;

class CreateTenantAction
{
    /**
     * Execute the action to create a new tenant and assign the creator as admin.
     *
     * @param array<string, mixed> $data
     */
    public function __invoke(User $user, array $data): Tenant
    {
        if (!$user->canCreateTenant()) {
            throw new \App\Exceptions\TenantLimitReachedException();
        }

        return DB::transaction(function () use ($user, $data) {
            $tenant = Tenant::create($data);

            // Attach the user to the tenant
            $user->tenants()->attach($tenant->id);

            // Assign Admin role
            $user->assignTenantRole($tenant, TenantRoleName::Admin);

            return $tenant;
        });
    }
}
