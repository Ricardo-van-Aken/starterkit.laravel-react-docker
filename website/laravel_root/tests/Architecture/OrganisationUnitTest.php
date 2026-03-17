<?php

use App\Models\Domain\OrganisationUnits\Contracts\OrganisationUnitContract;

arch('models in OrganisationUnits implement OrganisationUnitContract')
    ->expect('App\Models\Domain\OrganisationUnits')
    ->toImplement(OrganisationUnitContract::class);
