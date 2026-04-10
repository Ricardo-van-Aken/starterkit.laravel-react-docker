<?php
 
namespace App\Actions\TenantInvitation;
 
use App\Enums\TenantInvitationStatus;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * @phpstan-type OutgoingInvitationData array{
 *     uuid: string,
 *     email: string,
 *     roles: Collection<int, string>,
 *     permissions: Collection<int|string, mixed>,
 *     status: string,
 *     expires_at: string|null
 * }
 */
class ListOutgoingInvitationsAction
{
    /**
     * Execute the action to list outgoing invitations.
     *
     * @param array{status?: string, sort?: string, direction?: string, search?: string} $params
     * @return LengthAwarePaginator<OutgoingInvitationData>
     */
    public function __invoke(Tenant $tenant, array $params = []): LengthAwarePaginator
    {
        setPermissionsTeamId($tenant->id);
        
        $query = TenantInvitation::where('tenant_id', $tenant->id);

        // Filtering
        if ($search = $params['search'] ?? null) {
            $query->where('email', 'like', "%{$search}%");
        }

        $status = $params['status'] ?? 'pending';
        if ($status !== 'all') {
            $statuses = is_array($status) ? $status : [$status];
            $enumStatuses = collect($statuses)
                ->map(fn($s) => TenantInvitationStatus::tryFrom($s))
                ->filter()
                ->values();

            if ($enumStatuses->isNotEmpty()) {
                $query->whereIn('status', $enumStatuses->toArray());
            }
        }

        // Timeframe filtering
        $expiresAt = $params['expires_at'] ?? 'all';
        if ($expiresAt !== 'all') {
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
        $direction = $params['direction'] ?? 'desc';
        
        $allowedSorts = ['email', 'status', 'expires_at'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction === 'asc' ? 'asc' : 'desc');
        }

        /** @var LengthAwarePaginator<TenantInvitation> $paginator */
        $paginator = $query->paginate(10, ['*'], 'invitations_page')
            ->withQueryString();

        return $paginator->through(function (TenantInvitation $invitation) {
            /** @var OutgoingInvitationData $data */
            $data = [
                'uuid' => $invitation->uuid,
                'email' => $invitation->email,
                'roles' => $invitation->getRoleNames(),
                'permissions' => $invitation->getDirectPermissions()->pluck('name'),
                'status' => $invitation->status->value,
                'expires_at' => $invitation->expires_at?->toDateTimeString(),
            ];

            return $data;
        });
    }
}
