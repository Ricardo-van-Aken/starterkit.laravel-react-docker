<?php

namespace App\Models;

use App\Models\OrganisationUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $uuid
 * @property string $name
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Tenant extends Model
{
    /** @use HasFactory<\Database\Factories\TenantFactory> */
    use HasUuids, HasFactory;

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

    /** @return array<int, string> */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * @return HasMany<OrganisationUnit, $this>
     */
    public function organisationUnits(): HasMany
    {
        return $this->hasMany(OrganisationUnit::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get the count of active administrators, optionally excluding a specific user.
     */
    public function activeAdminsCount(?User $excludeUser = null): int
    {
        $query = $this->users()
            ->whereNull('users.scheduled_for_deletion_at')
            ->whereHas('roles', function ($q) {
                /** @var string $tableName */
                $tableName = config('permission.table_names.model_has_roles');

                $q->where('roles.name', \App\Enums\TenantRoleName::Admin->value)
                  ->where($tableName . '.team_id', $this->id);
            });

        if ($excludeUser) {
            $query->where('users.id', '!=', $excludeUser->id);
        }

        return $query->count();
    }
    /**
     * @return HasMany<TenantInvitation, $this>
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(TenantInvitation::class);
    }
}
