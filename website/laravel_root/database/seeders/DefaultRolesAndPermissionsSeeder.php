<?php

namespace Database\Seeders;

use App\Enums\OrgUnitPermissionName;
use App\Enums\OrgUnitRoleName;
use App\Enums\TenantPermissionName;
use App\Enums\TenantRoleName;
use App\Models\Domain\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class DefaultRolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // ---------------------------------------------------
        // Global / Core defaults (team_id = null)
        // ---------------------------------------------------
        setPermissionsTeamId(null);

        // ===================================================
        // TENANT GUARD
        // ===================================================
        foreach (TenantPermissionName::cases() as $permission) {
            Permission::findOrCreate($permission->value, 'tenant');
        }

        // 1. Tenant Admin (Full Access)
        $tenantAdmin = Role::findOrCreate(TenantRoleName::Admin->value, 'tenant');
        $tenantAdmin->syncPermissions(Permission::where('guard_name', 'tenant')->get());

        // 2. Tenant Manager (Staff & Structure Manager)
        $tenantManager = Role::findOrCreate(TenantRoleName::Manager->value, 'tenant');
        $tenantManager->syncPermissions([
            TenantPermissionName::ViewOrganisationUnits->value,
            TenantPermissionName::CreateOrganisationUnits->value,
            TenantPermissionName::UpdateOrganisationUnits->value,
            
            TenantPermissionName::InviteTenantMembers->value,
            TenantPermissionName::UpdateTenantMembers->value,

            TenantPermissionName::ViewOrgUnitRoles->value,
            TenantPermissionName::ViewTenantRoles->value,
        ]);

        // 3. Tenant Finance
        $tenantFinance = Role::findOrCreate(TenantRoleName::Finance->value, 'tenant');
        $tenantFinance->syncPermissions([
            TenantPermissionName::ViewBillingInformation->value,
            TenantPermissionName::EditBillingInformation->value,
            TenantPermissionName::ViewOrganisationUnits->value,
        ]);


        // ===================================================
        // ORGANISATION UNIT GUARD
        // ===================================================
        foreach (OrgUnitPermissionName::cases() as $permission) {
            Permission::findOrCreate($permission->value, 'organisation_unit');
        }

        // 1. Org Unit Admin (Full Access)
        $orgAdmin = Role::findOrCreate(OrgUnitRoleName::Admin->value, 'organisation_unit');
        $orgAdmin->syncPermissions(Permission::where('guard_name', 'organisation_unit')->get());

        // 2. Org Unit Manager (Operational Lead)
        $orgManager = Role::findOrCreate(OrgUnitRoleName::Manager->value, 'organisation_unit');
        $orgManager->syncPermissions([
            OrgUnitPermissionName::ViewOrgDashboard->value,
            
            // Member Management
            OrgUnitPermissionName::AddTenantMember->value,
            OrgUnitPermissionName::InviteExternalMember->value,
            OrgUnitPermissionName::UpdateMember->value,
            
            // Customer Management
            OrgUnitPermissionName::CreateCustomers->value,
            OrgUnitPermissionName::ViewCustomers->value,
            OrgUnitPermissionName::UpdateCustomers->value,
            
            // IoT Devices
            OrgUnitPermissionName::CreateIoTDevices->value,
            OrgUnitPermissionName::ViewIoTDevices->value,
            OrgUnitPermissionName::UpdateIoTDevices->value,
        ]);

        // 3. Org Unit Technician (Field Worker)
        $orgTechnician = Role::findOrCreate(OrgUnitRoleName::Technician->value, 'organisation_unit');
        $orgTechnician->syncPermissions([
            OrgUnitPermissionName::ViewOrgDashboard->value,
            OrgUnitPermissionName::ViewCustomers->value,
            
            // IoT Device Focus
            OrgUnitPermissionName::CreateIoTDevices->value,
            OrgUnitPermissionName::ViewIoTDevices->value,
            OrgUnitPermissionName::UpdateIoTDevices->value,
        ]);

        // 4. Org Unit Viewer (Read-Only)
        $orgViewer = Role::findOrCreate(OrgUnitRoleName::Viewer->value, 'organisation_unit');
        $orgViewer->syncPermissions([
            OrgUnitPermissionName::ViewOrgDashboard->value,
            OrgUnitPermissionName::ViewCustomers->value,
            OrgUnitPermissionName::ViewIoTDevices->value,
        ]);
    }
}
