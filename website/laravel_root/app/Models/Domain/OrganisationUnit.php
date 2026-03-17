<?php

namespace App\Models\Domain;

use App\Enums\OrganisationUnitType;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganisationUnit extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id',
        'parent_id',
        'name',
        'type',
    ];

    protected $hidden = [
        'id',
    ];

    protected $casts = [
        'type' => OrganisationUnitType::class,
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(OrganisationUnit::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(OrganisationUnit::class, 'parent_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organisation_unit_user');
    }
}
