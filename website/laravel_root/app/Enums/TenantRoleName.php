<?php

namespace App\Enums;

enum TenantRoleName: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Finance = 'finance';
}
