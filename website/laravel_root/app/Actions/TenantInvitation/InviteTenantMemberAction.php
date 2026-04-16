<?php

namespace App\Actions\TenantInvitation;

use App\Actions\User\SendAccountInvitationAction;
use App\Enums\TenantInvitationStatus;
use App\Exceptions\TenantInvitationAlreadyExistsException;
use App\Exceptions\UserAlreadyMemberException;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\TenantInvitationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class InviteTenantMemberAction
{
    public function __construct(
        protected SendAccountInvitationAction $sendAccountInvitationAction,
    ) {}

    /**
     * Invite a new or existing user to the tenant.
     *
     * @param array<int, string> $roles
     * @param array<int, string> $permissions
     */
    public function __invoke(Tenant $tenant, string $email, array $roles, array $permissions): \App\Models\TenantInvitation
    {
        $user = User::where('email', $email)->first();

        // Fail if the user is already a member of the tenant, or a pending invitation already exists
        if ($user && $tenant->users()->where('users.id', $user->id)->exists()) {
            throw new UserAlreadyMemberException;
        }

        $existingInvitation = \App\Models\TenantInvitation::where('tenant_id', $tenant->id)
            ->where('email', $email)
            ->where('status', TenantInvitationStatus::Pending)
            ->first();

        if ($existingInvitation) {
            throw new TenantInvitationAlreadyExistsException;
        }

        $invitation = DB::transaction(function () use ($tenant, $email, $roles, $permissions, $user) {
            // If the user does not exist, and is not yet invited for an account, create a temporary account invitation
            if (! $user && ! \App\Models\AccountInvitation::where('email', $email)->exists()) {
                ($this->sendAccountInvitationAction)($email);
            }

            // Create new tenant invitation
            $invitation = $tenant->invitations()->make([
                'email' => $email,
            ]);

            $invitation->forceFill([
                'accept_token'  => Str::random(64),
                'decline_token' => Str::random(64),
                'expires_at'    => now()->addDays(7),
            ])->save();

            // Add roles and permissions via helpers to ensure they exist for the tenant
            $invitation->syncTenantRoles($tenant, $roles);
            $invitation->syncTenantPermissions($tenant, $permissions);

            return $invitation;
        });

        // Send the tenant-specific notification
        Notification::route('mail', $email)
            ->notify(new TenantInvitationNotification($invitation));

        return $invitation;
    }
}
