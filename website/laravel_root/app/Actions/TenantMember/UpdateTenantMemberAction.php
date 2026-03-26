<?php

namespace App\Actions\TenantMember;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateTenantMemberAction
{
    /**
     * Execute the action to update tenant member roles and permissions.
     *
     * @param array<int, string> $roles
     * @param array<int, string> $permissions
     */
    public function handle(Tenant $tenant, User $user, array $roles, array $permissions): void
    {
        DB::transaction(function () use ($tenant, $user, $roles, $permissions) {
            setPermissionsTeamId($tenant->id);

            $user->syncRoles($roles);
            $user->syncPermissions($permissions);
        });
    }
}
