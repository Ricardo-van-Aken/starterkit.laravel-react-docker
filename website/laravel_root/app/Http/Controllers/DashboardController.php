<?php

namespace App\Http\Controllers;

use App\Actions\TenantInvitation\ListIncomingInvitationsAction;
use App\Http\Requests\IndexDashboardRequest;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request for the main user dashboard.
     */
    public function __invoke(IndexDashboardRequest $request, ListIncomingInvitationsAction $listIncomingInvitationsAction): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $validated = $request->validated();

        $invitationFilters = [
            'status'     => $validated['status'] ?? ['pending'],
            'sort'       => $validated['sort'] ?? 'expires_at',
            'direction'  => $validated['direction'] ?? 'asc',
            'expires_at' => $validated['expires_at'] ?? null,
        ];

        $pageSize = 5; // Fixed page size as requested

        return Inertia::render('dashboard', [
            'invitations' => $listIncomingInvitationsAction(
                $user,
                $invitationFilters,
                $pageSize,
                $request->integer('inc_page', 1)
            ),
        ]);
    }
}
