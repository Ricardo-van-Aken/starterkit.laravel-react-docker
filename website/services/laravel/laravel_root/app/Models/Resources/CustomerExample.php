<?php

namespace App\Models\Resources;

use App\Models\Resources\Contracts\OrganisationResourceContract;
use App\Models\OrganisationUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerExample extends Model implements OrganisationResourceContract
{
    use HasUuids;

    protected $fillable = [
        'organisation_unit_id',
        'type',
        'first_name',
        'last_name',
        'email',
        'phone',
        'notes',
        'created_by',
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

    /** @return BelongsTo<OrganisationUnit, $this> */
    public function organisationUnit(): BelongsTo
    {
        return $this->belongsTo(OrganisationUnit::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
