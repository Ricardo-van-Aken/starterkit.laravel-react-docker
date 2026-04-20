<?php

namespace App\Support;

use Closure;

class RunInTeamScope
{
    /**
     * Execute a callback within the context of a specific tenant's/team's permissions.
     *
     * Temporarily sets the Spatie permissions team ID, executes the callback,
     * and guarantees the original team ID is restored afterwards to prevent
     * cross-tenant pollution during long-running tasks or loops.
     *
     * @template TReturn
     * @param int|string $tenantId
     * @param Closure(): TReturn $callback
     * @return TReturn
     */
    public static function run(int|string $tenantId, Closure $callback): mixed
    {
        $original = getPermissionsTeamId();

        setPermissionsTeamId($tenantId);

        try {
            return $callback();
        } finally {
            setPermissionsTeamId($original);
        }
    }
}
