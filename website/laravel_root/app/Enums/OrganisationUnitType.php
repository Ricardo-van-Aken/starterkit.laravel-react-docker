<?php

namespace App\Enums;

enum OrganisationUnitType: string
{
    case BRANCH = 'branch';
    case DEPARTMENT = 'department';
    case TEAM = 'team';
    case OTHER = 'other';
}
