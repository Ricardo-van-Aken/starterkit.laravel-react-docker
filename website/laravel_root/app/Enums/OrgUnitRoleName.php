<?php

namespace App\Enums;

enum OrgUnitRoleName: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Technician = 'technician';
    case Viewer = 'viewer';
}
