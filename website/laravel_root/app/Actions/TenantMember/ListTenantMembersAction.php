<?php

namespace App\Actions\TenantMember;

use App\Models\Tenant;
use App\Models\User;
use App\Policies\TenantMemberPolicy;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * @phpstan-type TenantMember array{
 *     uuid: string,
 *     name: string,
 *     email: string,
 *     avatar: null,
 *     roles: Collection<int, string>,
 *     permissions: Collection<int|string, mixed>,
 *     abilities: array{update: bool, delete: bool}
 * }
 */
class ListTenantMembersAction
{
    /**
     * Execute the action to list tenant members.
     *
     * @param array{
     *     search?: string,
     *     roles?: list<string>,
     *     sort?: string,
     *     direction?: 'asc'|'desc',
     * } $params
     * @return LengthAwarePaginator<int, TenantMember>
     */
    public function __invoke(?User $actor, Tenant $tenant, array $params = [], int $pageSize = 10, int $page = 1): LengthAwarePaginator
    {
        setPermissionsTeamId($tenant->id);

        $query = $tenant->users();

        // Search Filtering (Name/Email)
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role Filtering
        if (!empty($params['roles'])) {
            $roles = collect((array) $params['roles'])->filter(fn($r) => $r !== 'all');

            if ($roles->isNotEmpty()) {
                $query->whereHas('roles', function($q) use ($roles) {
                    $q->whereIn('name', $roles->toArray())
                      ->where('guard_name', 'tenant');
                });
            }
        }

        // Sorting
        $sort = $params['sort'] ?? 'name';
        $direction = $params['direction'] ?? 'asc';

        $allowedSorts = ['name', 'email', 'created_at', 'roles', 'permissions'];
        if (in_array($sort, $allowedSorts)) {
            if ($sort === 'roles') {
                $query->withMin('roles', 'name')
                      ->orderBy('roles_min_name', $direction);
            } elseif ($sort === 'permissions') {
                $query->withMin('permissions', 'name')
                      ->orderBy('permissions_min_name', $direction);
            } else {
                $query->orderBy($sort, $direction);
            }
        } else {
            $query->orderBy('name', 'asc');
        }

        // Pagination
        /** @var LengthAwarePaginator<int, User> $paginator */
        $paginator = $query->paginate($pageSize, ['*'], 'mem_page', $page)->withQueryString();

        return $paginator->through(function (User $user) use ($tenant, $actor) {
            /** @var TenantMember $member */
            $member = [
                'uuid' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => null,
                'roles' => $user->getRoleNames(), // Spatie method
                'permissions' => $user->getDirectPermissions()->pluck('name'),
                'abilities' => [
                    'update' => $actor ? $actor->can('update', [TenantMemberPolicy::class, $user, $tenant]) : false,
                    'delete' => $actor ? $actor->can('delete', [TenantMemberPolicy::class, $user, $tenant]) : false,
                ],
            ];

            return $member;
        });
    }
}