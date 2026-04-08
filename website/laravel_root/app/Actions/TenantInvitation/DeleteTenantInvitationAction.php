<?php

namespace App\Actions\TenantInvitation;

use App\Models\TenantInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteTenantInvitationAction
{
    /**
     * Delete a tenant invitation.
     */
    public function __invoke(TenantInvitation $invitation, User $user): void
    {
        DB::transaction(function () use ($invitation) {
            $invitation->syncTenantRoles($invitation->tenant, []);
            $invitation->syncTenantPermissions($invitation->tenant, []);

            $invitation->delete();
        });
    }
}
