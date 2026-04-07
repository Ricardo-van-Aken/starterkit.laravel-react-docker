<?php

namespace App\Actions\TenantInvitation;

use App\Enums\TenantInvitationStatus;
use App\Models\TenantInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AcceptTenantInvitationAction
{
    /**
     * Accept a tenant invitation and assign roles/permissions.
     */
    public function __invoke(TenantInvitation $invitation, User $user): void
    {
        if ($invitation->status !== TenantInvitationStatus::Pending) {
            throw new \App\Exceptions\TenantInvitationAlreadyProcessedException;
        }

        DB::transaction(function () use ($invitation, $user) {
            $tenant = $invitation->tenant;

            $tenant->users()->attach([$user->id]);

            // Set the users roles
            setPermissionsTeamId($tenant->id);

            // TODO: We currently just ignore roles and permissions that dont exist, re-evaluate this later
            $roleModels = \App\Models\Role::whereIn('name', $invitation->roles ?? [])
                ->where('guard_name', 'tenant')
                ->where(function ($q) use ($tenant) {
                    $q->where('team_id', $tenant->id)
                      ->orWhereNull('team_id');
                })
                ->get();
            $permissionModels = \App\Models\Permission::whereIn('name', $invitation->permissions ?? [])
                ->where('guard_name', 'tenant')
                ->get();

            // Assign roles and permissions
            $user->assignRole($roleModels);
            $user->givePermissionTo($permissionModels);

            // Set invitation status to 'accepted'
            $invitation->update([
                'status' => TenantInvitationStatus::Accepted,
            ]);
        });
    }
}
