<?php

namespace App\Actions\User;

use App\Exceptions\UserAlreadyExistsException;
use App\Models\AccountInvitation;
use App\Models\User;
use App\Notifications\AccountInvitationNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class SendAccountInvitationAction
{
    /**
     * Create a new account invitation and notify the user.
     */
    public function __invoke(string $email): AccountInvitation
    {
        // Only invite users who don't have an account
        if (User::where('email', $email)->exists()) {
            throw new UserAlreadyExistsException;
        }

        // Create or refresh the invitation
        $invitation = AccountInvitation::updateOrCreate(
            ['email' => $email],
            [
                'claim_token'   => Str::random(60),
                'expires_at'    => now()->addDays(7),
            ]
        );

        // Send the notification
        Notification::route('mail', $email)
            ->notify(new AccountInvitationNotification($invitation));

        return $invitation;
    }
}
