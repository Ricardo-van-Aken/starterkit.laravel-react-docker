<?php

namespace App\Actions\TenantInvitation;

use App\Actions\User\CreateAccountInvitationAction;
use App\Enums\TenantInvitationStatus;
use App\Exceptions\TenantInvitationAlreadyExistsException;
use App\Exceptions\UserAlreadyMemberException;
use App\Mail\TenantInvitationMail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteTenantMemberAction
{
    public function __construct(
        protected CreateAccountInvitationAction $createAccountInvitationAction
    ) {}

    /**
     * Invite a new or existing user to the tenant.
     *
     * @param array<int, string> $roles
     * @param array<int, string> $permissions
     */
    public function __invoke(Tenant $tenant, string $email, array $roles, array $permissions): \App\Models\TenantInvitation
    {
        return DB::transaction(function () use ($tenant, $email, $roles, $permissions) {
            $user = User::where('email', $email)->first();

            // check if the user is already a member of the tenant
            if ($user && $tenant->users()->where('users.id', $user->id)->exists()) {
                throw new UserAlreadyMemberException;
            }

            // Check if a pending invitation already exists for this email
            $existingInvitation = \App\Models\TenantInvitation::where('tenant_id', $tenant->id)
                ->where('email', $email)
                ->where('status', TenantInvitationStatus::Pending)
                ->first();

            if ($existingInvitation) {
                throw new TenantInvitationAlreadyExistsException;
            }

            // If the user does not exist, create a temporary account invitation
            if (! $user) {
                ($this->createAccountInvitationAction)($email);
            }

            // Create new invitation
            $invitation = $tenant->invitations()->make([
                'email' => $email,
            ]);

            // Safely assign all attributes including non-fillable tokens
            $invitation->forceFill([
                'roles'         => $roles,
                'permissions'   => $permissions,
                'accept_token'  => Str::random(64),
                'decline_token' => Str::random(64),
                'expires_at'    => now()->addDays(7),
            ])->save();

            // Send invitation email
            Mail::to($email)->send(new TenantInvitationMail($invitation));

            return $invitation;
        });
    }
}
