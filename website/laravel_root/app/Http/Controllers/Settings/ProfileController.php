<?php

namespace App\Http\Controllers\Settings;

use App\Actions\User\ScheduleUserDeletionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        /** @var \App\Models\User $user - User is always present as this controller should be behind auth middleware */
        $user = $request->user();

        return Inertia::render('settings/profile', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail, // @phpstan-ignore-line
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile settings.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        # User is always present as this controller is behind auth middleware
        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return to_route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request, ScheduleUserDeletionAction $action): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
            'force_delete_tenants' => ['nullable', 'boolean'],
        ]);

        /** @var \App\Models\User $user - User is always present as this controller should be behind auth middleware */
        $user = $request->user();

        $forceDelete = $request->boolean('force_delete_tenants');

        $action->handle($user, $forceDelete);

        return redirect()->route('deletion.notice');
    }
}
