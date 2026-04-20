<?php

namespace App\Actions\TenantMember;

use App\Enums\TenantRoleName;
use App\Exceptions\LastAdminSafeGuardException;
use App\Exceptions\TenantMemberNotFoundException;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RemoveTenantMemberAction
{
    /**
     * Execute the action to remove a member from a tenant.
     *
     * @throws LastAdminSafeGuardException if the user to be removed from the tenant is the last admin
     * @throws TenantMemberNotFoundException if the user to be removed from the tenant is not an actual member
     */
    public function __invoke(Tenant $tenant, User $user): void
    {
        // Verify that member to be removed is an actual member of the tenant
        if (!$tenant->users()->where('users.id', $user->id)->exists()) {
            throw new TenantMemberNotFoundException();
        }

        // Check if removing this user would leave the tenant with 0 active admins
        if ($user->hasTenantRole($tenant, TenantRoleName::Admin)) {
            if ($tenant->activeAdminsCount($user) === 0) {
                throw new LastAdminSafeGuardException();
            }
        }

        DB::transaction(function () use ($tenant, $user) {
            setPermissionsTeamId($tenant->id);
            $user->syncRoles([]);
            $user->syncPermissions([]);

            $tenant->users()->detach($user->id);
        });
    }
}
