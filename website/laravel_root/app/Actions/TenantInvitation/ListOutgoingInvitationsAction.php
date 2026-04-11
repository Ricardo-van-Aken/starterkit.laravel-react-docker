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
     * Execute the action to list tenant members.
     *
     * @param array{status?: string[], sort?: string, direction?: 'asc'|'desc', search?: string, expires_at?: string|null} $params
     * @return LengthAwarePaginator<OutgoingInvitationData>
     */
    public function __invoke(Tenant $tenant, array $params = [], int $pageSize = 10, int $page = 1): LengthAwarePaginator
    {
        setPermissionsTeamId($tenant->id);
        
        $query = TenantInvitation::where('tenant_id', $tenant->id);

        // Email Filtering
        if ($search = $params['search'] ?? null) {
            $query->where('email', 'like', "%{$search}%");
        }

        // Status Filtering
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
        $direction = $params['direction'] ?? 'desc';
        
        $allowedSorts = ['email', 'status', 'expires_at'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction === 'asc' ? 'asc' : 'desc');
        }

        /** @var LengthAwarePaginator<TenantInvitation> $paginator */
        $paginator = $query->paginate($pageSize, ['*'], 'invitations_page', $page)
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
