<?php

namespace App\Actions\TenantMember;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * @phpstan-type TenantMember array{
 *     id: int,
 *     name: string,
 *     email: string,
 *     avatar: null,
 *     roles: Collection<int, string>,
 *     permissions: Collection<int|string, mixed>
 * }
 */
class ListTenantMembersAction
{
    /**
     * Execute the action to list tenant members.
     *
     * @return Collection<int, TenantMember>
     */
    public function handle(Tenant $tenant): Collection
    {
        setPermissionsTeamId($tenant->id);

        return $tenant->users()->get()->map(function (User $user) {
            /** @var TenantMember $member */
            $member = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => null,
                'roles' => $user->getRoleNames(), // Spatie method
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ];
 
            return $member;
        });
    }
}