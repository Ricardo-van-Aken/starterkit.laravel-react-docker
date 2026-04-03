<?php

namespace App\Actions\Tenant;

use App\Models\Tenant;

class UpdateTenantAction
{
    /**
     * Execute the action to update a tenant.
     *
     * @param array<string, mixed> $data
     */
    public function handle(Tenant $tenant, array $data): bool
    {
        return $tenant->update($data);
    }
}
