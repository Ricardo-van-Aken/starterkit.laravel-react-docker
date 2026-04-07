<?php

namespace App\Actions\TenantInvitation;

use App\Enums\TenantInvitationStatus;
use App\Exceptions\TenantInvitationAlreadyProcessedException;
use App\Models\TenantInvitation;
use Illuminate\Support\Facades\DB;

class UpdateTenantInvitationAction
{
    /**
     * Update the roles and permissions on a pending tenant invitation.
     *
     * @param array<int, string> $roles
     * @param array<int, string> $permissions
     */
    public function __invoke(TenantInvitation $invitation, array $roles, array $permissions): void
    {
        if ($invitation->status !== TenantInvitationStatus::Pending) {
            throw new TenantInvitationAlreadyProcessedException;
        }

        DB::transaction(function () use ($invitation, $roles, $permissions) {
            $invitation->update([
                'roles'       => $roles,
                'permissions' => $permissions,
            ]);
        });
    }
}
