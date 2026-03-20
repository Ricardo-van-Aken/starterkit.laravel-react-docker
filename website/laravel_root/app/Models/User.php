<?php

namespace App\Models;

use App\Models\Tenant;
use App\Models\OrganisationUnit;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'max_tenants',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'id',
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /** @return BelongsToMany<Tenant, $this> */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class);
    }

    /** @return BelongsToMany<OrganisationUnit, $this> */
    public function organisationUnits(): BelongsToMany
    {
        return $this->belongsToMany(OrganisationUnit::class, 'organisation_unit_user');
    }

    public function forTenant(Tenant $tenant): self
    {
        setPermissionsTeamId($tenant->id);

        return $this;
    }

    public function forOrganisationUnit(OrganisationUnit $organisationUnit): self
    {
        setPermissionsTeamId($organisationUnit->id);

        return $this;
    }

    public function canCreateTenant(): bool
    {
        return $this->tenants()->count() < $this->max_tenants;
    }
}
