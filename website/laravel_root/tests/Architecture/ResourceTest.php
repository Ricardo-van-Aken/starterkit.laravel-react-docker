<?php

test('models in Domain/Resources implement OrganisationResource contract')
    ->expect('App\Models\Domain\Resources')
    ->toImplement('App\Models\Domain\Resources\Contracts\OrganisationResource');
