<?php

namespace App\Models;

use App\Enums\OrganisationUnitType;
use App\Models\User;
use App\Models\OrganisationUnits\BranchUnit;
use App\Models\OrganisationUnits\DepartmentUnit;
use App\Models\OrganisationUnits\TeamUnit;
use App\Models\OrganisationUnits\OtherUnit;
use App\Models\OrganisationUnits\Contracts\OrganisationUnitContract;
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

    /** @var array<string, string> */
    protected static array $typeMap = [
        OrganisationUnitType::BRANCH->value => 'branchUnit',
        OrganisationUnitType::DEPARTMENT->value => 'departmentUnit',
        OrganisationUnitType::TEAM->value => 'teamUnit',
        OrganisationUnitType::OTHER->value => 'otherUnit',
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

    /** @return array<int, string> */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** @return BelongsTo<self, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<self, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organisation_unit_user');
    }

    /** @return HasOne<BranchUnit, $this> */
    public function branchUnit(): HasOne
    {
        return $this->hasOne(BranchUnit::class);
    }

    /** @return HasOne<DepartmentUnit, $this> */
    public function departmentUnit(): HasOne
    {
        return $this->hasOne(DepartmentUnit::class);
    }

    /** @return HasOne<TeamUnit, $this> */
    public function teamUnit(): HasOne
    {
        return $this->hasOne(TeamUnit::class);
    }

    /** @return HasOne<OtherUnit, $this> */
    public function otherUnit(): HasOne
    {
        return $this->hasOne(OtherUnit::class);
    }

    /** @return OrganisationUnitContract */
    public function getSubUnit(): OrganisationUnitContract
    {
        $relation = self::$typeMap[$this->type->value] ?? null;

        if (!$relation) {
            throw new \LogicException("Unknown organisation unit type: {$this->type->value}");
        }

        $subUnit = $this->{$relation};

        if (!$subUnit instanceof OrganisationUnitContract) {
            throw new \LogicException("OrganisationUnit {$this->id} has no associated subtype for type {$this->type->value}");
        }

        return $subUnit;
    }
}
