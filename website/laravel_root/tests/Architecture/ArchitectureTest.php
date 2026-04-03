<?php

/**
 * Standard Pest Architecture Presets
 *
 * These presets cover common PHP, Security, and Laravel architectural rules.
 */
arch()->preset()->php();
arch()->preset()->security();
arch()->preset()->laravel()
    ->ignoring([
        'App\Http\Controllers\TenantController',
        'App\Http\Controllers\Auth\AccountDeletionNoticeController',
    ]);

/**
 * Project-Specific Architectural Rules
 */

arch('models')
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model')
    ->ignoring([
        'App\Models\User',
        'App\Models\Pivot',
        'App\Models\Resources\Contracts',
        'App\Models\OrganisationUnits\Contracts',
    ]);

arch('controllers')
    ->expect('App\Http\Controllers')
    ->toExtend('App\Http\Controllers\Controller')
    ->ignoring('App\Http\Controllers\Controller');

arch('enums')
    ->expect('App\Enums')
    ->toBeEnums();

arch('exceptions')
    ->expect('App\Exceptions')
    ->toExtend('Exception')
    ->ignoring('App\Exceptions\Handler');

arch('service providers')
    ->expect('App\Providers')
    ->toExtend('Illuminate\Support\ServiceProvider');
