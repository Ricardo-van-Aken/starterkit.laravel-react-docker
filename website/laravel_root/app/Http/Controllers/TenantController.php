<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tenant\StoreTenantRequest;
use App\Http\Requests\Tenant\UpdateTenantRequest;
use App\Http\Requests\Tenant\DeleteTenantRequest;
use App\Models\Tenant;
use App\Enums\TenantRoleName;
use Illuminate\Http\RedirectResponse;

class TenantController extends Controller
{
    /**
     * Store a newly created tenant in storage.
     */
    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $tenant = Tenant::create($request->validated());

        // Attach the currently authenticated user to the tenant
        $request->user()->tenants()->attach($tenant->id);

        // Assign proper roles using spatie/laravel-permission
        setPermissionsTeamId($tenant->id);
        $request->user()->assignRole(TenantRoleName::Admin->value);

        return redirect()->back()->with('status', 'tenant-created');
    }

    /**
     * Update the specified tenant in storage.
     */
    public function update(UpdateTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $tenant->update($request->validated());

        return redirect()->back()->with('status', 'tenant-updated');
    }

    /**
     * Remove the specified tenant from storage.
     */
    public function destroy(DeleteTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $tenant->delete();

        return redirect()->route('dashboard')->with('status', 'tenant-deleted');
    }
}
