<?php

namespace App\Enums;

enum TenantPermissionName: string
{
    // Tenant Properties
    case UpdateTenantDetails = 'update_tenant_details';
    case DeleteTenant = 'delete_tenant';

    // Organisation Unit Management
    case CreateOrganisationUnits = 'create_organisation_units';
    case ViewOrganisationUnits = 'view_organisation_units';
    case UpdateOrganisationUnits = 'update_organisation_units';
    case DeleteOrganisationUnits = 'delete_organisation_units';

    // Org Unit Role Management
    case CreateOrgUnitRoles = 'create_org_unit_roles';
    case ViewOrgUnitRoles = 'view_org_unit_roles';
    case UpdateOrgUnitRoles = 'update_org_unit_roles';
    case DeleteOrgUnitRoles = 'delete_org_unit_roles';

    // Tenant Role Management
    case CreateTenantRoles = 'create_tenant_roles';
    case ViewTenantRoles = 'view_tenant_roles';
    case EditTenantRoles = 'edit_tenant_roles';

    // Tenant Member Management
    case ViewTenantMembers = 'view_tenant_members';
    case InviteTenantMembers = 'invite_tenant_members';
    case UpdateTenantMembers = 'update_tenant_members';
    case DeleteTenantMembers = 'delete_tenant_members';

    // Billing Management
    case ViewBillingInformation = 'view_billing_information';
    case EditBillingInformation = 'edit_billing_information';
}
