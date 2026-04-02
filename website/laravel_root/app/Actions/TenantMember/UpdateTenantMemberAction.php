<?php

namespace App\Actions\TenantMember;

use App\Enums\TenantRoleName;
use App\Exceptions\LastAdminSafeGuardException;
use App\Exceptions\TenantMemberNotFoundException;
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
     * @throws LastAdminSafeGuardException if the user whose roles are being updated is the last admin being demoted
     * @throws TenantMemberNotFoundException if the user whose roles are being updated is not an actual member of the tenant
     */
    public function handle(Tenant $tenant, User $user, array $roles, array $permissions): void
    {
        // Verify that member to be updated is an actual member of the tenant
        if (!$tenant->users()->where('users.id', $user->id)->exists()) {
            throw new TenantMemberNotFoundException();
        }
 
        // Check if the user who is getting their roles updated is the last admin being demoted
        $isCurrentAdmin = $user->hasTenantRole($tenant, TenantRoleName::Admin);
        $willBeAdmin = in_array(TenantRoleName::Admin->value, $roles);

        if ($isCurrentAdmin && ! $willBeAdmin) {
            if ($tenant->activeAdminsCount() <= 1) {
                throw new LastAdminSafeGuardException();
            }
        }

        DB::transaction(function () use ($tenant, $user, $roles, $permissions) {
            setPermissionsTeamId($tenant->id);

            $roleModels = \App\Models\Role::whereIn('name', $roles)
                ->where('guard_name', 'tenant')
                ->where(function ($q) use ($tenant) {
                    $q->where('team_id', $tenant->id)
                      ->orWhereNull('team_id');
                })
                ->get();

            $permissionModels = \App\Models\Permission::whereIn('name', $permissions)
                ->where('guard_name', 'tenant')
                ->get();

            $user->syncRoles($roleModels);
            $user->syncPermissions($permissionModels);
        });
    }
}
