<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountDeletionNoticeController extends Controller
{
    /**
     * Display the account deletion notice prompt.
     */
    public function show(Request $request): Response|RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (!$user->scheduled_for_deletion_at) {
            return redirect()->route('home');
        }

        return Inertia::render('auth/deletion-notice', [
            'scheduledForDeletionAt' => $user->scheduled_for_deletion_at->toISOString(),
        ]);
    }

    /**
     * Restore the user account by unscheduling the deletion.
     */
    public function restore(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->forceFill([
            'scheduled_for_deletion_at' => null,
        ])->save();

        return redirect()->intended(route('dashboard'))->with('status', 'Account restored successfully.');
    }
}
