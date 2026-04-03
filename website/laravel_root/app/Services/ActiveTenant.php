<?php

namespace App\Services;

use App\Models\Tenant;

class ActiveTenant
{
    /**
     * The active tenant for the current request.
     */
    protected ?Tenant $tenant = null;

    /**
     * Set the active tenant.
     */
    public function set(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    /**
     * Get the active tenant.
     */
    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    /**
     * Get the active tenant or fail.
     *
     * @throws \RuntimeException
     */
    public function getOrFail(): Tenant
    {
        if (!$this->tenant) {
            throw new \RuntimeException('No active tenant set.');
        }

        return $this->tenant;
    }

    /**
     * Check if a tenant is active.
     */
    public function hasActiveTenant(): bool
    {
        return !is_null($this->tenant);
    }
}
