<?php

namespace App\Actions\TenantMember;

use App\Enums\TenantRoleName;
use App\Exceptions\LastAdminSafeGuardException;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RemoveTenantMemberAction
{
    /**
     * Execute the action to remove a member from a tenant.
     *
     * @throws LastAdminSafeGuardException if the user to be removed from the tenant is the last admin
     * @throws NotFoundHttpException if the user to be removed from the tenant is not an actual member
     */
    public function handle(Tenant $tenant, User $user): void
    {
        // Verify that member to be removed is an actual member of the tenant
        if (!$tenant->users()->where('users.id', $user->id)->exists()) {
            throw new NotFoundHttpException(__('tenant.not_a_member'));
        }

        // Check if the user to be removed from the tenant is the last admin
        if ($user->hasTenantRole($tenant, TenantRoleName::Admin)) {
            $adminCount = $tenant->users()
                ->whereHas('roles', function ($query) use ($tenant) {
                    $query->where('name', TenantRoleName::Admin->value)
                        ->where('team_id', $tenant->id);
                })
                ->count();

            if ($adminCount <= 1) {
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
