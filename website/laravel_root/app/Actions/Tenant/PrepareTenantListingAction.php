<?php

namespace App\Actions\Tenant;

use App\Models\Tenant;
use App\Models\User;
use App\Policies\TenantMemberPolicy;
use App\Services\ActiveTenant;
use Illuminate\Database\Eloquent\Collection;

class PrepareTenantListingAction
{
    /**
     * Fetch the user's tenants enriched with contextual roles, abilities,
     * and member previews for the tenants index page.
     *
     * This is intentionally distinct from a generic tenant fetch — it attaches
     * user-specific permission context and eager-loads member previews for display.
     *
     * @return Collection<int, Tenant>
     */
    public function handle(User $user): Collection
    {
        $tenants = $user->tenants()
            ->withCount(['users', 'organisationUnits'])
            ->get();

        $tenants->each(function (Tenant $tenant) use ($user) {
            setPermissionsTeamId($tenant->id);

            // Attach user's roles within this tenant
            $tenant->setAttribute('roles', $user->getRoleNames()->toArray());

            // Attach user's abilities within this tenant
            $tenant->setAttribute('abilities', [
                'view_members' => $user->can('viewAny', [TenantMemberPolicy::class, $tenant]),
            ]);

            /** @var Tenant&object{roles: array<int, string>, abilities: array{view_members: bool}} $tenant */

            // Attach the first 4 members with their roles (if user has permission)
            if ($tenant->abilities['view_members']) {
                $tenant->load(['users' => fn(\Illuminate\Database\Eloquent\Relations\BelongsToMany $q) => $q->take(4)]);
                $tenant->users->each(function (User $member) {
                    $member->setAttribute('tenant_roles', $member->getRoleNames()->toArray());
                });
            }
        });

        // Restore the global active tenant context after looping through team IDs
        if ($activeTenant = app(ActiveTenant::class)->get()) {
            setPermissionsTeamId($activeTenant->id);
        }

        return $tenants;
    }
}
