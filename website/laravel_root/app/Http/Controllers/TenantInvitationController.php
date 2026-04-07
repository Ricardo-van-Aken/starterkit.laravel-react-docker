<?php

namespace App\Http\Controllers;

use App\Actions\TenantInvitation\AcceptTenantInvitationAction;
use App\Actions\TenantInvitation\DeclineTenantInvitationAction;
use App\Actions\TenantInvitation\InviteTenantMemberAction;
use App\Actions\TenantInvitation\UpdateTenantInvitationAction;
use App\Models\TenantInvitation;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\TenantInvitation\AcceptTenantInvitationRequest;
use App\Http\Requests\TenantInvitation\DeclineTenantInvitationRequest;
use App\Http\Requests\TenantInvitation\InviteTenantMemberRequest;
use App\Http\Requests\TenantInvitation\UpdateTenantInvitationRequest;
use App\Services\ActiveTenant;

class TenantInvitationController extends Controller
{
    /**
     * Create the invitation.
     */
    public function store(InviteTenantMemberRequest $request, InviteTenantMemberAction $inviteAction): RedirectResponse
    {
        /** @var \App\Models\Tenant $tenant */
        $tenant = app(ActiveTenant::class)->get();

        /** @var array{email: string, roles?: array<int, string>, permissions?: array<int, string>} $validated */
        $validated = $request->validated();

        $inviteAction(
            $tenant,
            $validated['email'],
            $validated['roles'] ?? [],
            $validated['permissions'] ?? []
        );

        return redirect()->back()->with('status', __('invitations.created'));
    }

    /**
     * Update the invitation roles and permissions.
     */
    public function update(TenantInvitation $tenantInvitation, UpdateTenantInvitationRequest $request, UpdateTenantInvitationAction $updateAction): RedirectResponse
    {
        /** @var array{roles: array<int, string>, permissions: array<int, string>} $validated */
        $validated = $request->validated();

        $updateAction(
            $tenantInvitation,
            $validated['roles'],
            $validated['permissions']
        );

        return redirect()->back()->with('status', __('invitations.updated'));
    }

    /**
     * Destroy the invitation.
     */
    public function destroy(TenantInvitation $tenantInvitation, DeclineTenantInvitationRequest $request, DeclineTenantInvitationAction $declineAction): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $declineAction($tenantInvitation, $user);

        return redirect()->back()->with('status', __('invitations.declined'));
    }

    /**
     * Accept the invitation.
     */
    public function accept(TenantInvitation $tenantInvitation, AcceptTenantInvitationRequest $request, AcceptTenantInvitationAction $acceptAction): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $acceptAction($tenantInvitation, $user);

        return redirect()->back()->with('status', __('invitations.accepted'));
    }

    /**
     * Decline the invitation.
     */
    public function decline(TenantInvitation $tenantInvitation, DeclineTenantInvitationRequest $request, DeclineTenantInvitationAction $declineAction): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $declineAction($tenantInvitation, $user);

        return redirect()->back()->with('status', __('invitations.declined'));
    }

    /**
     * Accept a tenant invitation via an email link.
     */
    public function acceptByToken(string $token, AcceptTenantInvitationAction $action): RedirectResponse
    {
        $invitation = TenantInvitation::where('accept_token', $token)->firstOrFail();
        
        $user = \App\Models\User::where('email', $invitation->email)->firstOrFail();
        $action($invitation, $user);

        return redirect()->route('dashboard')->with('status', __('invitations.accepted'));
    }

    /**
     * Decline a tenant invitation via an email link.
     */
    public function declineByToken(string $token, DeclineTenantInvitationAction $action): RedirectResponse
    {
        $invitation = TenantInvitation::where('decline_token', $token)->firstOrFail();
        
        $user = \App\Models\User::where('email', $invitation->email)->firstOrFail();

        $action($invitation, $user);

        return redirect()->route('dashboard')->with('status', __('invitations.declined'));
    }
}
