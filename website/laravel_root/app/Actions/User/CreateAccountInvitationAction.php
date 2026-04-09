<?php

namespace App\Actions\User;

use App\Exceptions\UserAlreadyExistsException;
use App\Mail\ClaimAccountMail;
use App\Models\AccountInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateAccountInvitationAction
{
    /**
     * Create a new account invitation record for the given email.
     */
    public function __invoke(string $email): AccountInvitation
    {
        // If a user already exists, we cannot invite them to create an account
        if (User::where('email', $email)->exists()) {
            throw new UserAlreadyExistsException;
        }

        $invitation = AccountInvitation::updateOrCreate(
            ['email' => $email],
            [
                'claim_token'   => Str::random(60),
                'expires_at'    => now()->addDays(7),
            ]
        );

        // Send the claim account email
        Mail::to($email)->send(new ClaimAccountMail($invitation));

        return $invitation;
    }
}
