<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Tenant\StoreTenantRequest;
use App\Http\Requests\Tenant\SwitchTenantRequest;
use App\Http\Requests\Tenant\UpdateTenantRequest;
use App\Http\Requests\Tenant\DeleteTenantRequest;
use App\Models\Tenant;
use App\Enums\TenantRoleName;
use App\Services\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    /**
     * Switch the active tenant in the session.
     */
    public function switch(SwitchTenantRequest $request): RedirectResponse
    {
        $request->session()->put('active_tenant_uuid', $request->uuid);

        return redirect()->back()->with('status', 'Tenant switched successfully.');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('tenants/index', [
            'tenants' => $request->user()->tenants()->get(),
        ]);
    }

    /**
     * Store a newly created tenant in storage.
     */
    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $tenant = DB::transaction(function () use ($request) {
            $tenant = Tenant::create($request->validated());

            /** @var \App\Models\User $user */
            $user = $request->user();

            // Attach the currently authenticated user to the tenant
            $user->tenants()->attach($tenant->id);

            // Assign proper roles dynamically
            $user->assignTenantRole($tenant, TenantRoleName::Admin);

            return $tenant;
        });

        return redirect()->back()->with('status', __('tenant.created'));
    }

    /**
     * Update the specified tenant in storage.
     */
    public function update(UpdateTenantRequest $request): RedirectResponse
    {
        $tenant = app(TenantManager::class)->get();
        $tenant->update($request->validated());

        return redirect()->back()->with('status', __('tenant.updated'));
    }

    /**
     * Remove the specified tenant from storage.
     */
    public function destroy(DeleteTenantRequest $request): RedirectResponse
    {
        $tenant = app(TenantManager::class)->get();
        $tenant->delete();

        return redirect()->route('dashboard')->with('status', __('tenant.deleted'));
    }
}
