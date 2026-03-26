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
use App\Services\ActiveTenant;
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

        return redirect()->back()->with('status', 'Tenant switched successfully.');
    }

    /**
     * Remove the authenticated user from the tenant.
     */
    public function leave(LeaveTenantRequest $request, Tenant $tenant, LeaveTenantAction $action): RedirectResponse
    {
        $action->handle($request->user(), $tenant);

        return redirect()->route('dashboard')->with('status', __('tenant.left'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return Inertia::render('tenants/index', [
            'tenants' => $user->tenants()->get(),
        ]);
    }

    /**
     * Show the form for editing the specified tenant.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('tenants/settings/edit', [
            'tenant' => app(ActiveTenant::class)->getOrFail(),
        ]);
    }

    /**
     * Store a newly created tenant in storage.
     */
    public function store(StoreTenantRequest $request, CreateTenantAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $action->handle($user, $request->validated());

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

        return redirect()->route('dashboard')->with('status', __('tenant.deleted'));
    }
}
