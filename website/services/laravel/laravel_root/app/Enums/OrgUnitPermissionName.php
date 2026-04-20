<?php

namespace App\Enums;

enum OrgUnitPermissionName: string
{
    case ViewOrgDashboard = 'view_org_dashboard';

    // Member Management
    case AddTenantMember = 'add_tenant_member';
    case InviteExternalMember = 'invite_external_member';
    case UpdateMember = 'update_member';
    case RemoveMember = 'remove_member';

    // Customer Management
    case CreateCustomers = 'create_customers';
    case ViewCustomers = 'view_customers';
    case UpdateCustomers = 'update_customers';
    case DeleteCustomers = 'delete_customers';

    // IoT Device Management
    case CreateIoTDevices = 'create_iot_devices';
    case ViewIoTDevices = 'view_iot_devices';
    case UpdateIoTDevices = 'update_iot_devices';
    case DeleteIoTDevices = 'delete_iot_devices';
}
