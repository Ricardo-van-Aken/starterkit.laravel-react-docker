<?php

namespace App\Models\OrganisationUnits\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\OrganisationUnit;

/**
 * @phpstan-require-extends \Illuminate\Database\Eloquent\Model
 */
interface OrganisationUnitContract
{
    /**
     * @return BelongsTo<OrganisationUnit, $this>
     * @phpstan-ignore generics.notSubtype
     */
    public function organisationUnit(): BelongsTo;
}
