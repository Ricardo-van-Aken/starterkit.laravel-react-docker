<?php

namespace App\Actions\TenantMember;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * @phpstan-type TenantMember array{
 *     uuid: string,
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
     * @return LengthAwarePaginator<TenantMember>
     */
    public function __invoke(Tenant $tenant): LengthAwarePaginator
    {
        setPermissionsTeamId($tenant->id);

        /** @var LengthAwarePaginator<User> $paginator */
        $paginator = $tenant->users()->paginate(10, ['*'], 'members_page')->withQueryString();

        return $paginator->through(function (User $user) {
            /** @var TenantMember $member */
            $member = [
                'uuid' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => null,
                'roles' => $user->getRoleNames(), // Spatie method
                'permissions' => $user->getDirectPermissions()->pluck('name'),
            ];

            return $member;
        });
    }
}