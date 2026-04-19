<?php

namespace App\Models;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Traits\HasTenantAuthorization;

/**
 * @property string $uuid
 * @property string $email
 * @property \App\Enums\TenantInvitationStatus $status
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \App\Models\Tenant $tenant
 */
class TenantInvitation extends Model
{
    /** @use HasFactory<\Database\Factories\TenantInvitationFactory> */
    use HasUuids, HasFactory, HasRoles, HasTenantAuthorization;

    protected string $guard_name = 'tenant';

    protected $fillable = [
        'tenant_id',
        'email',
        'status',
        'expires_at',
    ];

    protected $hidden = [
        'id',
        'accept_token',
        'decline_token',
    ];

    protected function casts(): array
    {
        return [
            'status' => \App\Enums\TenantInvitationStatus::class,
            'expires_at' => 'datetime',
        ];
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

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
