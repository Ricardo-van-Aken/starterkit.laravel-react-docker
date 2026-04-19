<?php

namespace App\Enums;

enum TenantRoleName: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Support = 'support';
    case Finance = 'finance';
    case Auditor = 'auditor';
}
