<?php

test('models in App\Models\Resources implement OrganisationResource contract')
    ->expect('App\Models\Resources')
    ->toImplement('App\Models\Resources\Contracts\OrganisationResource');
