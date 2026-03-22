<?php

arch('front-facing models use HasUuids trait')
    ->expect('App\Models')
    ->classes()
    ->toUseTrait('Illuminate\Database\Eloquent\Concerns\HasUuids')
    ->ignoring(['App\Models\OrganisationUnits', 'App\Models\OrganisationUnitClosure']);

arch('front-facing models hide the id field')
    ->expect('App\Models')
    ->classes()
    ->toHideParams('id')
    ->ignoring(['App\Models\OrganisationUnits', 'App\Models\OrganisationUnitClosure']);
