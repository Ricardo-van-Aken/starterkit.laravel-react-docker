<?php

namespace App\Actions\Tenant;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class DeleteTenantAction
{
    /**
     * Execute the action to delete a tenant and clean up related roles and permissions.
     */
    public function __invoke(Tenant $tenant): ?bool
    {
        return DB::transaction(function () use ($tenant) {
            $teamId = $tenant->id;

            // Clean up Spatie roles and permissions associated with this tenant
            // These tables don't have FK constraints with cascade delete in the current migrations
            $modelHasRoles = config('permission.table_names.model_has_roles');
            $modelHasPermissions = config('permission.table_names.model_has_permissions');
            $rolesTable = config('permission.table_names.roles');
            $teamForeignKey = config('permission.column_names.team_foreign_key');

            if (!is_string($modelHasRoles) || !is_string($modelHasPermissions) || !is_string($rolesTable) || !is_string($teamForeignKey)) {
                throw new \RuntimeException('Permission configuration is missing required table or column names.');
            }

            DB::table($modelHasRoles)
                ->where($teamForeignKey, $teamId)
                ->delete();

            DB::table($modelHasPermissions)
                ->where($teamForeignKey, $teamId)
                ->delete();

            DB::table($rolesTable)
                ->where($teamForeignKey, $teamId)
                ->delete();

            // Delete the tenant itself
            // Other related data like organisation_units, tenant_user, etc.
            // are handled by database-level cascade deletes.
            return $tenant->delete();
        });
    }
}
