<?php

namespace App\Models\Domain\Resources;

use App\Models\Domain\Resources\Contracts\OrganisationResource;
use App\Models\Domain\OrganisationUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IotDeviceExample extends Model implements OrganisationResource
{
    use HasUuids;

    protected $fillable = [
        'organisation_unit_id',
        'type',
        'device_id',
        'device_secret',
        'created_by',
    ];

    protected $hidden = [
        'id',
        'device_secret',
    ];

    protected function casts(): array
    {
        return [
            'device_secret' => 'encrypted',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
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
