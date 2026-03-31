<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Tenant\LeaveTenantRequest;
use App\Actions\Tenant\LeaveTenantAction;
use App\Http\Requests\Tenant\StoreTenantRequest;
use App\Http\Requests\Tenant\SwitchTenantRequest;
use App\Http\Requests\Tenant\UpdateTenantRequest;
use App\Http\Requests\Tenant\DestroyTenantRequest;
use App\Models\Tenant;
use App\Policies\TenantMemberPolicy;
use App\Policies\TenantRolePolicy;
use App\Policies\TenantBillingPolicy;
use App\Services\ActiveTenant;
use App\Actions\Tenant\PrepareTenantListingAction;
use App\Actions\Tenant\CreateTenantAction;
use App\Actions\Tenant\UpdateTenantAction;
use App\Actions\Tenant\DeleteTenantAction;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    /**
     * Switch the active tenant in the session.
     */
    public function switch(SwitchTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $request->session()->put('active_tenant_uuid', $tenant->uuid);

        if ($request->has('redirect_to')) {
            return redirect($request->query('redirect_to'))->with('status', 'Tenant switched successfully.');
        }

        return redirect()->back()->with('status', 'Tenant switched successfully.');
    }

    /**
     * Remove the authenticated user from the tenant.
     */
    public function leave(LeaveTenantRequest $request, Tenant $tenant, LeaveTenantAction $action): RedirectResponse
    {
        $action->handle($request->user(), $tenant);

        $request->session()->forget('active_tenant_uuid');

        return redirect()->route('tenants.index')->with('status', __('tenant.left'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, PrepareTenantListingAction $action): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return Inertia::render('tenants/index', [
            'tenants' => $action->handle($user),
        ]);
    }

    /**
     * Show the form for editing the specified tenant.
     */
    public function edit(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        /** @var \App\Models\Tenant $tenant */
        $tenant = app(ActiveTenant::class)->get();

        return Inertia::render('tenants/settings/edit', [
            'abilities' => [
                'update'        => $user->can('update', $tenant),
                'delete'        => $user->can('delete', $tenant),
                'view_members'  => $user->can('viewAny', [TenantMemberPolicy::class, $tenant]),
                'view_roles'    => $user->can('viewAny', [TenantRolePolicy::class, $tenant]),
                'view_billing'  => $user->can('viewAny', [TenantBillingPolicy::class, $tenant]),
            ],
        ]);
    }

    /**
     * Store a newly created tenant in storage.
     */
    public function store(StoreTenantRequest $request, CreateTenantAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $tenant = $action->handle($user, $request->validated());

        $request->session()->put('active_tenant_uuid', $tenant->uuid);

        return redirect()->back()->with('status', __('tenant.created'));
    }

    /**
     * Update the specified tenant in storage.
     */
    public function update(UpdateTenantRequest $request, UpdateTenantAction $action): RedirectResponse
    {
        /** @var \App\Models\Tenant $tenant */
        $tenant = app(ActiveTenant::class)->get();

        $action->handle($tenant, $request->validated());

        return redirect()->back()->with('status', __('tenant.updated'));
    }

    /**
     * Remove the specified tenant from storage.
     */
    public function destroy(DestroyTenantRequest $request, DeleteTenantAction $action): RedirectResponse
    {
        /** @var \App\Models\Tenant $tenant */
        $tenant = app(ActiveTenant::class)->get();

        $action->handle($tenant);

        $request->session()->forget('active_tenant_uuid');

        return redirect()->route('tenants.index')->with('status', __('tenant.deleted'));
    }
}
