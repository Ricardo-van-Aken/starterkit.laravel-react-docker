<?php

namespace App\Actions\User;

use App\Models\AccountInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TakeOwnershipAction
{
    /**
     * Create a new user from an account invitation.
     *
     * @param array{name: string, password: string} $data
     */
    public function __invoke(AccountInvitation $invitation, array $data): User
    {
        // If the user already exists, we cannot take ownership
        if (User::where('email', $invitation->email)->exists()) {
            // Delete the account invitation
            $invitation->delete();

            throw new \App\Exceptions\UserAlreadyMemberException;
        }

        return DB::transaction(function () use ($invitation, $data) {
            $user = User::create([
                'name'              => $data['name'],
                'email'             => $invitation->email,
                'password'          => $data['password'],
            ]);

            $user->markEmailAsVerified();

            // Delete the account invitation
            $invitation->delete();

            return $user;
        });
    }
}
