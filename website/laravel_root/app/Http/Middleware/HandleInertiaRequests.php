<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Services\ActiveTenant;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        /** @var string $quote */
        $quote = Inspiring::quotes()->random();
        /** @var array{0: string, 1: string} $parts */
        $parts = str($quote)->explode('-')->pad(2, '')->toArray();
        [$message, $author] = $parts;

        /** @var \App\Models\User|null $user */
        $user = $request->user();
        $activeTenant = app(ActiveTenant::class)->get();
        
        $roles = [];
        if ($user && $activeTenant) {
            setPermissionsTeamId($activeTenant->id);
            $roles = $user->getRoleNames()->toArray();
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $user,
                'tenant' => $activeTenant,
                'tenants' => $user ? $user->tenants()->get() : [],
                'roles' => $roles,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
