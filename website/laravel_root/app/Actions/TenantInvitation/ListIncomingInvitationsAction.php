<?php
 
namespace App\Actions\TenantInvitation;
 
use App\Models\TenantInvitation;
use App\Models\User;
use App\Enums\TenantInvitationStatus;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
 
/**
 * @phpstan-type IncomingInvitationData array{
 *     uuid: string,
 *     tenant: array{name: string, uuid: string, created_at: string},
 *     roles: Collection<int, string>,
 *     permissions: Collection<int|string, mixed>,
 *     status: string,
 *     expires_at: string|null
 * }
 */
class ListIncomingInvitationsAction
{
    /**
     * Execute the action to list incoming invitations for a user.
     *
     * @param array{
     *     status?: string[],
     *     sort?: string,
     *     direction?: 'asc'|'desc',
     *     expires_at?: string|null
     * } $params
     * @return LengthAwarePaginator<int, IncomingInvitationData>
     */
    public function __invoke(User $user, array $params = [], int $pageSize = 10, int $page = 1): LengthAwarePaginator
    {
        $query = TenantInvitation::where('email', $user->email)
            ->with('tenant');
 
        // Status Filtering (defaults to pending)
        $status = $params['status'] ?? ['pending'];
        if (! empty($status)) {
            $enumStatuses = collect((array) $status)
                ->map(fn($s) => TenantInvitationStatus::tryFrom($s))
                ->filter();

            if ($enumStatuses->isNotEmpty()) {
                $query->whereIn('status', $enumStatuses);
            }
        }

        // Expires_at filtering
        $expiresAt = $params['expires_at'] ?? null;
        if ($expiresAt) {
            $now = now();
            switch ($expiresAt) {
                case '24h':
                    $query->where('expires_at', '>', $now)
                          ->where('expires_at', '<=', $now->copy()->addDay());
                    break;
                case '7d':
                    $query->where('expires_at', '>', $now)
                          ->where('expires_at', '<=', $now->copy()->addDays(7));
                    break;
                case '30d':
                    $query->where('expires_at', '>', $now)
                          ->where('expires_at', '<=', $now->copy()->addDays(30));
                    break;
                case 'expired':
                    $query->where('expires_at', '<=', $now);
                    break;
                case 'never':
                    $query->whereNull('expires_at');
                    break;
            }
        }
 
        // Sorting
        $sort = $params['sort'] ?? 'expires_at';
        $direction = $params['direction'] ?? 'asc';
 
        $allowedSorts = ['expires_at', 'status', 'created_at', 'email'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction);
        }
 
        /** @var LengthAwarePaginator<int, TenantInvitation> $paginator */
        $paginator = $query->paginate($pageSize, ['*'], 'inc_page', $page)
            ->withQueryString();
 
        return $paginator->through(function (TenantInvitation $invitation) {
            /** @var IncomingInvitationData $data */
            $data = [
                'uuid' => $invitation->uuid,
                'tenant' => [
                    'name' => $invitation->tenant->name,
                    'uuid' => $invitation->tenant->uuid,
                    'created_at' => $invitation->tenant->created_at->toDateTimeString(),
                ],
                'roles' => $invitation->getRoleNames(),
                'permissions' => $invitation->getDirectPermissions()->pluck('name'),
                'status' => $invitation->status->value,
                'expires_at' => $invitation->expires_at?->toDateTimeString(),
            ];
 
            return $data;
        });
    }
}
