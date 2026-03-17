<?php

namespace App\Models\Domain;

use App\Models\Domain\OrganisationUnits\OrganisationUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
    ];

    protected $hidden = [
        'id',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function organisationUnits(): HasMany
    {
        return $this->hasMany(OrganisationUnit::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
