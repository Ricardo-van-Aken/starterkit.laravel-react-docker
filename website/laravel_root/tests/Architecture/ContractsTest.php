<?php

use App\Models\OrganisationUnits\Contracts\OrganisationUnitContract;
use App\Models\Resources\Contracts\OrganisationResourceContract;

arch('models in App\Models\OrganisationUnits implement OrganisationUnitContract')
    ->expect('App\Models\OrganisationUnits')
    ->toImplement(OrganisationUnitContract::class);

arch('models in App\Models\Resources implement OrganisationResourceContract')
    ->expect('App\Models\Resources')
    ->toImplement(OrganisationResourceContract::class);
