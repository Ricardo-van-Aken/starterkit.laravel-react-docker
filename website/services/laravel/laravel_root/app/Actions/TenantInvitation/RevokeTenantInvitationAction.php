<?php

namespace App\Actions\TenantInvitation;

use App\Enums\TenantInvitationStatus;
use App\Models\TenantInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RevokeTenantInvitationAction
{
    /**
     * Revoke a tenant invitation.
     */
    public function __invoke(TenantInvitation $invitation, User $user): void
    {
        DB::transaction(function () use ($invitation) {
            $invitation->update([
                'status' => TenantInvitationStatus::Revoked,
            ]);
        });
    }
}
