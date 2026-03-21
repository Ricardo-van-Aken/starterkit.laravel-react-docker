<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tenant\StoreTenantRequest;
use App\Http\Requests\Tenant\UpdateTenantRequest;
use App\Http\Requests\Tenant\DeleteTenantRequest;
use App\Models\Tenant;
use App\Enums\TenantRoleName;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    /**
     * Store a newly created tenant in storage.
     */
    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $tenant = DB::transaction(function () use ($request) {
            $tenant = Tenant::create($request->validated());

            // Attach the currently authenticated user to the tenant
            $request->user()->tenants()->attach($tenant->id);

            // Assign proper roles using spatie/laravel-permission
            $request->user()->forTenant($tenant)->assignRole(TenantRoleName::Admin->value);

            return $tenant;
        });

        return redirect()->back()->with('status', __('tenant.created'));
    }

    /**
     * Update the specified tenant in storage.
     */
    public function update(UpdateTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $tenant->update($request->validated());

        return redirect()->back()->with('status', __('tenant.updated'));
    }

    /**
     * Remove the specified tenant from storage.
     */
    public function destroy(DeleteTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $tenant->delete();

        return redirect()->route('dashboard')->with('status', __('tenant.deleted'));
    }
}
