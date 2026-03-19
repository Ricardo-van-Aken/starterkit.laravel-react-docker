<?php

namespace App\Models\Domain\OrganisationUnits;

use App\Models\Domain\OrganisationUnits\Contracts\OrganisationUnitContract;
use App\Models\Domain\OrganisationUnit;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentUnit extends Model implements OrganisationUnitContract
{
    use HasUuids;

    protected $fillable = [
        'organisation_unit_id',
    ];

    protected $hidden = [
        'id',
        'organisation_unit_id',
    ];

    /** @return BelongsTo<OrganisationUnit, $this> */
    public function organisationUnit(): BelongsTo
    {
        return $this->belongsTo(OrganisationUnit::class);
    }
}
