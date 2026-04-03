<?php

arch('front-facing models use HasUuids trait, and hide the id field')
    ->expect('App\Models')
    ->classes()
    ->toUseTrait('Illuminate\Database\Eloquent\Concerns\HasUuids')
    ->toHideParams('id')
    ->ignoring([
        'App\Models\OrganisationUnits', // OrganisationUnit is the front-facing model for these
        'App\Models\OrganisationUnitClosure', // Used similar to a pivot, for relational access
    ]);
