<?php

namespace App\Actions\TenantInvitation;

use App\Enums\TenantInvitationStatus;
use App\Models\TenantInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeclineTenantInvitationAction
{
    /**
     * Decline a tenant invitation.
     */
    public function __invoke(TenantInvitation $invitation, User $user): void
    {
        if ($invitation->status !== TenantInvitationStatus::Pending) {
            throw new \App\Exceptions\TenantInvitationAlreadyProcessedException;
        }

        DB::transaction(function () use ($invitation) {
            $invitation->update([
                'status' => TenantInvitationStatus::Declined,
            ]);
        });
    }
}
