<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;
use App\Services\ActiveTenant;

class SetActiveTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return $next($request);
        }

        $tenantUuid = $request->session()->get('active_tenant_uuid');
        $tenant = null;

        // Use tenant from session
        if ($tenantUuid) {
            $tenant = $request->user()->tenants()->where('uuid', $tenantUuid)->first();
        }

        if ($tenant) {
            app(ActiveTenant::class)->set($tenant);
        }

        return $next($request);
    }
}
