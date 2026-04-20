<?php

namespace App\Actions\TenantMember;

use App\Exceptions\UserAlreadyMemberException;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateTenantMemberAction
{
    /**
     * Add a user to a tenant and assign initial roles and permissions.
     *
     * @param array<int, string> $roles
     * @param array<int, string> $permissions
     * @throws UserAlreadyMemberException
     */
    public function __invoke(Tenant $tenant, User $user, array $roles = [], array $permissions = []): void
    {
        if ($tenant->users()->where('user_id', $user->id)->exists()) {
            throw new UserAlreadyMemberException;
        }

        DB::transaction(function () use ($tenant, $user, $roles, $permissions) {
            $tenant->users()->attach([$user->id]);

            $user->syncTenantRoles($tenant, $roles);
            $user->syncTenantPermissions($tenant, $permissions);
        });
    }
}
