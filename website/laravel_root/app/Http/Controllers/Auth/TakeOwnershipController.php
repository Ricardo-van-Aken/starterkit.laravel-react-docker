<?php

namespace App\Http\Controllers\Auth;

use App\Actions\User\TakeOwnershipAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\TakeOwnershipRequest;
use App\Models\AccountInvitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TakeOwnershipController extends Controller
{
    /**
     * Show the view for a user to claim their pending account.
     */
    public function edit(string $token): Response|RedirectResponse
    {
        $invitation = AccountInvitation::where('claim_token', $token)->firstOrFail();

        return Inertia::render('auth/take-ownership', [
            'email' => $invitation->email,
            'token' => $token,
        ]);
    }

    /**
     * Store the user's name and password to claim ownership.
     */
    public function update(TakeOwnershipRequest $request, string $token, TakeOwnershipAction $takeOwnershipAction): RedirectResponse
    {
        $invitation = AccountInvitation::where('claim_token', $token)->firstOrFail();

        /** @var array{name: string, password: string} $validated */
        $validated = $request->validated();

        $user = $takeOwnershipAction($invitation, $validated);

        // Sign the user in
        Auth::login($user);

        return redirect()->intended(route('dashboard'));
    }
}
