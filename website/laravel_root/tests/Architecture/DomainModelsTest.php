<?php

arch('front-facing models use HasUuids trait')
    ->expect('App\Models')
    ->classes()
    ->toUseTrait('Illuminate\Database\Eloquent\Concerns\HasUuids')
    ->ignoring([
        'App\Models\OrganisationUnitClosure', // Not front-facing
        'App\Models\OrganisationUnits', // These will all be behind the OrganisationUnit model
        '*\Contracts', // Ignore contracts
    ]);

arch('all models hide the id field')
    ->expect('App\Models')
    ->classes()
    ->toHideParams('id')
    ->ignoring([
        '*\Contracts', // Ignore contracts
    ]);
