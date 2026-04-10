<?php
 
namespace App\Actions\TenantInvitation;
 
use App\Models\TenantInvitation;
use App\Models\User;
use App\Enums\TenantInvitationStatus;
use Illuminate\Support\Collection;
 
/**
 * @phpstan-type IncomingInvitationData array{
 *     uuid: string,
 *     tenant: array{name: string, slug: string, uuid: string, created_at: string},
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
     * @return Collection<int, IncomingInvitationData>
     */
    public function __invoke(User $user): Collection
    {
        return TenantInvitation::where('email', $user->email)
            ->where('status', TenantInvitationStatus::Pending)
            ->with('tenant')
            ->get()
            ->filter(fn (TenantInvitation $invitation) => !$invitation->isExpired())
            ->map(function (TenantInvitation $invitation) {
                /** @var IncomingInvitationData $data */
                $data = [
                    'uuid' => $invitation->uuid,
                    'tenant' => [
                        'name' => $invitation->tenant->name,
                        'slug' => $invitation->tenant->slug,
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
