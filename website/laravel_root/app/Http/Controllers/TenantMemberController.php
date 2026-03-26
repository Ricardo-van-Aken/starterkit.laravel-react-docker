<?php

namespace App\Http\Controllers;

use App\Actions\TenantMember\ListTenantMembersAction;
use App\Actions\TenantMember\RemoveTenantMemberAction;
use App\Actions\TenantMember\UpdateTenantMemberAction;
use App\Http\Requests\TenantMember\DestroyTenantMemberRequest;
use App\Http\Requests\TenantMember\UpdateTenantMemberRequest;
use App\Http\Requests\TenantMember\IndexTenantMembersRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\TenantMemberPolicy;
use App\Services\ActiveTenant;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TenantMemberController extends Controller
{
    public function __construct(
        protected UpdateTenantMemberAction $updateTenantMemberAction,
        protected RemoveTenantMemberAction $removeTenantMemberAction
    ) {}

    /**
     * Display a listing of the tenant members.
     */
    public function index(IndexTenantMembersRequest $request, ListTenantMembersAction $listMembersAction): Response
    {
        /** @var \App\Models\Tenant $tenant */
        $tenant = app(ActiveTenant::class)->get();
        
        return Inertia::render('tenants/settings/members', [
            'tenant' => $tenant,
            'members' => $listMembersAction->handle($tenant),
            'available_roles' => Role::where('guard_name', 'tenant')->pluck('name'),
            'available_permissions' => Permission::where('guard_name', 'tenant')->pluck('name'),
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

    /**
     * Update the specified tenant member.
     */
    public function update(UpdateTenantMemberRequest $request, User $user): RedirectResponse
    {
        /** @var \App\Models\Tenant $tenant */
        $tenant = app(ActiveTenant::class)->get();

        /** @var array<int, string> $roles */
        $roles = $request->validated()['roles'];
        /** @var array<int, string> $permissions */
        $permissions = $request->validated()['permissions'];

        $this->updateTenantMemberAction->handle(
            $tenant,
            $user,
            $roles,
            $permissions
        );

        return redirect()->back()->with('success', 'Member updated.');
    }

    /**
     * Remove the member from the tenant.
     */
    public function destroy(DestroyTenantMemberRequest $request, User $user): RedirectResponse
    {
        /** @var \App\Models\Tenant $tenant */
        $tenant = app(ActiveTenant::class)->get();

        $this->removeTenantMemberAction->handle($tenant, $user);

        return redirect()->back()->with('success', 'Member removed from tenant.');
    }
}
