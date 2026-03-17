<?php

namespace App\Models\Domain;

use App\Enums\OrganisationUnitType;
use App\Models\User;
use App\Models\Domain\OrganisationUnits\BranchUnit;
use App\Models\Domain\OrganisationUnits\DepartmentUnit;
use App\Models\Domain\OrganisationUnits\TeamUnit;
use App\Models\Domain\OrganisationUnits\OtherUnit;
use App\Models\Domain\OrganisationUnits\Contracts\OrganisationUnitContract;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrganisationUnit extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id',
        'parent_id',
        'name',
        'description',
        'type',
    ];

    protected $hidden = [
        'id',
    ];

    protected $casts = [
        'type' => OrganisationUnitType::class,
    ];

    protected static array $typeMap = [
        OrganisationUnitType::BRANCH => 'branchUnit',
        OrganisationUnitType::DEPARTMENT => 'departmentUnit',
        OrganisationUnitType::TEAM => 'teamUnit',
        OrganisationUnitType::OTHER => 'otherUnit',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Always eager load sub-models
        $this->with = array_values(self::$typeMap);
    }

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
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organisation_unit_user');
    }

    public function branchUnit(): HasOne
    {
        return $this->hasOne(BranchUnit::class);
    }

    public function departmentUnit(): HasOne
    {
        return $this->hasOne(DepartmentUnit::class);
    }

    public function teamUnit(): HasOne
    {
        return $this->hasOne(TeamUnit::class);
    }

    public function otherUnit(): HasOne
    {
        return $this->hasOne(OtherUnit::class);
    }

    public function getSubUnit(): OrganisationUnitContract
    {
        $relation = self::$typeMap[$this->type->value] ?? null;

        if (!$relation) {
            throw new \LogicException("Unknown organisation unit type: {$this->type->value}");
        }

        $subUnit = $this->{$relation};

        if (!$subUnit) {
            throw new \LogicException("OrganisationUnit {$this->id} has no associated subtype for type {$this->type}");
        }

        return $subUnit;
    }
}
