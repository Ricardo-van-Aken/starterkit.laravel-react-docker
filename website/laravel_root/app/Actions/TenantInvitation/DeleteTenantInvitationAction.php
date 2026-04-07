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
        $invitation->delete();
    }
}
