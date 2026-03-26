<?php

namespace App\Actions\TenantMember;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RemoveTenantMemberAction
{
    /**
     * Execute the action to remove a member from a tenant.
     */
    public function handle(Tenant $tenant, User $user): void
    {
        DB::transaction(function () use ($tenant, $user) {
            setPermissionsTeamId($tenant->id);
            $user->syncRoles([]);
            $user->syncPermissions([]);

            $tenant->users()->detach($user->id);
        });
    }
}
