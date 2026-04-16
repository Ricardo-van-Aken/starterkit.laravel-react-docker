<?php

namespace App\Http\Controllers;

use App\Actions\TenantMember\InviteTenantMemberAction;
use App\Actions\TenantMember\ListTenantMembersAction;
use App\Actions\TenantMember\RemoveTenantMemberAction;
use App\Actions\TenantMember\UpdateTenantMemberAction;
use App\Actions\TenantMember\GetManageableTenantRolesAction;
use App\Actions\TenantInvitation\ListOutgoingInvitationsAction;
use App\Http\Requests\TenantMember\DestroyTenantMemberRequest;
use App\Http\Requests\TenantMember\InviteTenantMemberRequest;
use App\Http\Requests\TenantMember\UpdateTenantMemberRequest;
use App\Http\Requests\TenantMember\IndexTenantMembersRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use App\Policies\TenantMemberPolicy;
use App\Policies\TenantRolePolicy;
use App\Policies\TenantBillingPolicy;
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
     * Display a listing of the tenant members and outstanding invitations.
     */
    public function index(
        IndexTenantMembersRequest $request, 
        ListTenantMembersAction $listMembersAction, 
        ListOutgoingInvitationsAction $listInvitationsAction,
        GetManageableTenantRolesAction $getManageableRoles
    ): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
 
        $validated = $request->validated();

        /** @var \App\Models\Tenant $tenant */
        $tenant = app(ActiveTenant::class)->get();

        $invitationFilters = [
            'status'     => $validated['inv_status'] ?? ['pending'],
            'sort'       => $validated['inv_sort'] ?? 'expires_at',
            'direction'  => $validated['inv_dir'] ?? 'desc',
            'search'     => $validated['inv_search'] ?? '',
            'expires_at' => $validated['inv_expires_at'] ?? null,
        ];

        $memberFilters = [
            'search'    => $validated['mem_search'] ?? '',
            'roles'     => $validated['mem_roles'] ?? [],
            'sort'      => $validated['mem_sort'] ?? 'name',
            'direction' => $validated['mem_dir'] ?? 'asc',
        ];

        $invPageSize = $request->integer('inv_per_page', 10);
        $invPage = $request->integer('inv_page', 1);

        $memPageSize = $request->integer('mem_per_page', 10);
        $memPage = $request->integer('mem_page', 1);

        return Inertia::render('tenants/settings/members', [
            'members' => $listMembersAction(
                $user,
                $tenant, 
                $memberFilters,
                $memPageSize,
                $memPage
            ),
            'available_roles' => Role::where('guard_name', 'tenant')->pluck('name'),
            'manageable_roles' => $getManageableRoles($user, $tenant),
            'available_permissions' => Permission::where('guard_name', 'tenant')->pluck('name'),
            'invitations' => $listInvitationsAction(
                $user,
                $tenant, 
                $invitationFilters,
                $invPageSize,
                $invPage
            ),
            'invitation_filters' => array_merge($invitationFilters, [
                'page' => $invPage,
                'pageSize' => $invPageSize,
            ]),
            'member_filters' => array_merge($memberFilters, [
                'page' => $memPage,
                'pageSize' => $memPageSize,
            ]),
            'abilities' => [
                'update'        => $user->can('update', $tenant),
                'view_members'  => $user->can('viewAny', [TenantMemberPolicy::class, $tenant]),
                'view_roles'    => $user->can('viewAny', [TenantRolePolicy::class, $tenant]),
                'view_billing'  => $user->can('viewAny', [TenantBillingPolicy::class, $tenant]),
            ],
        ]);
    }

    /**
     * Update an existing tenant member's roles and permissions.
     */
    public function update(UpdateTenantMemberRequest $request, User $user): RedirectResponse
    {
        /** @var \App\Models\Tenant $tenant */
        $tenant = app(ActiveTenant::class)->get();

        $validated = $request->validated();
        $roles = $validated['roles'] ?? null;
        $permissions = $validated['permissions'] ?? null;

        ($this->updateTenantMemberAction)(
            $tenant,
            $user,
            $roles,
            $permissions,
        );

        return redirect()->back()->with('status', __('actions.member_updated'));
    }

    /**
     * Remove the member from the tenant.
     */
    public function destroy(DestroyTenantMemberRequest $request, User $user): RedirectResponse
    {
        /** @var \App\Models\Tenant $tenant */
        $tenant = app(ActiveTenant::class)->get();

        ($this->removeTenantMemberAction)($tenant, $user);

        return redirect()->back()->with('status', __('actions.member_removed'));
    }
}
