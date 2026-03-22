<?php

use App\Models\OrganisationUnits\Contracts\OrganisationUnitContract;

arch('models in App\Models\OrganisationUnits implement OrganisationUnitContract')
    ->expect('App\Models\OrganisationUnits')
    ->toImplement(OrganisationUnitContract::class);
