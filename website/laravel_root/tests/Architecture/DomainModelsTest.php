<?php

arch('domain models use HasUuids trait')
    ->expect('App\Models\Domain')
    ->classes()
    ->toUseTrait('Illuminate\Database\Eloquent\Concerns\HasUuids')
    ->ignoring('App\Models\Domain\OrganisationUnits');

arch('domain models hide the id field')
    ->expect('App\Models\Domain')
    ->classes()
    ->toHideParams('id')
    ->ignoring('App\Models\Domain\OrganisationUnits');
