<?php

arch()->preset()->php();
arch()->preset()->security();
arch()->preset()->laravel();

arch('domain models use HasUuids trait')
    ->expect('App\Models\Domain')
    ->toUseTrait('Illuminate\Database\Eloquent\Concerns\HasUuids');

arch('domain models hide the id field')
    ->expect('App\Models\Domain')
    ->toHideId();
