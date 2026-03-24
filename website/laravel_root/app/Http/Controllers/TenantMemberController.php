<?php

namespace App\Http\Controllers;

use App\Actions\TenantMember\ListMembersAction;
use App\Http\Requests\TenantMember\ViewMembersRequest;
use App\Services\ActiveTenant;
use Inertia\Inertia;
use Inertia\Response;

class TenantMemberController extends Controller
{
    /**
     * Display a listing of the tenant members.
     */
    public function index(ViewMembersRequest $request, ListMembersAction $listMembersAction): Response
    {
        /** @var \App\Models\Tenant $tenant */
        $tenant = app(ActiveTenant::class)->get();
        
        return Inertia::render('tenants/settings/members', [
            'tenant' => $tenant,
            'members' => $listMembersAction->handle($tenant),
            'invitations' => [
                [
                    'email' => 'placeholder@example.com',
                    'role' => 'Guest',
                    'status' => 'Pending',
                    'expires_at' => now()->addDays(7)->toDateTimeString(),
                ]
            ],
        ]);
    }
}
