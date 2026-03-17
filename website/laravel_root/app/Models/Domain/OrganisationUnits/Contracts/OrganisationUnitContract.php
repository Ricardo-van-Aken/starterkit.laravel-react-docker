<?php

namespace App\Models\Domain\OrganisationUnits\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface OrganisationUnitContract
{
    public function organisationUnit(): BelongsTo;
}
