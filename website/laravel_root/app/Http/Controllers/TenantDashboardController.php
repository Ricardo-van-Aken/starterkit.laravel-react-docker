<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\ActiveTenant;

class TenantDashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        $tenant = app(ActiveTenant::class)->get();

        // Load counts specifically for the dashboard subscription card
        $tenant->loadCount(['users', 'organisationUnits']);

        return Inertia::render('tenants/dashboard', [
            'tenant' => $tenant,
        ]);
    }
}
