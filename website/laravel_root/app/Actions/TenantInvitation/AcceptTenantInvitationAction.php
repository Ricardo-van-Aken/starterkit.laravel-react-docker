<?php

namespace App\Actions\TenantInvitation;

use App\Actions\TenantMember\CreateTenantMemberAction;
use App\Enums\TenantInvitationStatus;
use App\Models\TenantInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AcceptTenantInvitationAction
{
    public function __construct(
        protected CreateTenantMemberAction $createTenantMemberAction
    ) {}

    /**
     * Accept a tenant invitation and assign roles/permissions.
     */
    public function __invoke(TenantInvitation $invitation, User $user): void
    {
        if ($invitation->isExpired()) {
            throw new \App\Exceptions\TenantInvitationExpiredException;
        }

        if ($invitation->status !== TenantInvitationStatus::Pending) {
            throw new \App\Exceptions\TenantInvitationAlreadyProcessedException;
        }

        DB::transaction(function () use ($invitation, $user) {
            ($this->createTenantMemberAction)(
                $invitation->tenant,
                $user,
                $invitation->getTenantRoleNames($invitation->tenant),
                $invitation->getTenantPermissionNames($invitation->tenant)
            );

            // Set invitation status to 'accepted'
            $invitation->update([
                'status' => TenantInvitationStatus::Accepted,
            ]);
        });
    }
}
