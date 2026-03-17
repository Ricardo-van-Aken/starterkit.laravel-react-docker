<?php

namespace App\Enums;

enum OrganisationUnitType: string
{
    case DEPARTMENT = 'department';
    case TEAM = 'team';
    case SUB_TEAM = 'sub_team';
    case OTHER = 'other';
}
