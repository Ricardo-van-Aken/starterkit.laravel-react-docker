<?php

namespace App\Actions\TenantInvitation;

use App\Enums\TenantInvitationStatus;
use App\Exceptions\TenantInvitationAlreadyProcessedException;
use App\Models\TenantInvitation;
use Illuminate\Support\Facades\DB;

class UpdateTenantInvitationAction
{
    /**
     * Update a pending tenant invitation and its assigned roles and/or permissions.
     *
     * @param array<int, string>|null $newRoles
     * @param array<int, string>|null $newPermissions
     */
    public function __invoke(TenantInvitation $invitation, ?array $newRoles = null, ?array $newPermissions = null): bool
    {
        if ($invitation->status !== TenantInvitationStatus::Pending) {
            throw new TenantInvitationAlreadyProcessedException;
        }

        return DB::transaction(function () use ($invitation, $newRoles, $newPermissions) {
            if ($newRoles !== null) {
                $invitation->syncTenantRoles($invitation->tenant, $newRoles);
            }

            if ($newPermissions !== null) {
                $invitation->syncTenantPermissions($invitation->tenant, $newPermissions);
            }

            return true;
        });
    }
}
