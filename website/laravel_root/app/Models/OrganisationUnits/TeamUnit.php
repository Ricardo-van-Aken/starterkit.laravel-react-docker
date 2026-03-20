<?php

namespace App\Models\OrganisationUnits;

use App\Models\OrganisationUnits\Contracts\OrganisationUnitContract;
use App\Models\OrganisationUnit;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamUnit extends Model implements OrganisationUnitContract
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
