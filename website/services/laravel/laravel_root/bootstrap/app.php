<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Auth\Access\AuthorizationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            \App\Http\Middleware\SetActiveTenant::class,
            \App\Http\Middleware\EnsureAccountNotDeleting::class,

            // Framework middleware
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'ensure.tenant' => \App\Http\Middleware\EnsureTenant::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReport([
            \DomainException::class,
        ]);

        $exceptions->render(function (\DomainException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors([
                'error' => $e->getMessage(),
            ]);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, \Illuminate\Http\Request $request) {
            // Error message fallback
            $fallback = __('permissions.access_denied');
            if ($e->getPrevious() instanceof AuthorizationException) {
                $fallback = __('permissions.unauthorized');
            }

            // Replace the default error message with our own localised one
            $message = $e->getMessage();
            if (empty($message) || $message === "This action is unauthorized.") {
                $message = $fallback;
            }

            // Self-Healing UI: If the user hits a 403 on a GET request within a tenant context,
            // we redirect them to the dashboard instead of showing an error page.
            if ($request->isMethod('GET') && $request->is('tenant/*')) {
                return redirect()->route('tenant.dashboard')->with('status', [
                    'type' => 'error',
                    'message' => $message . ' ' . __('permissions.redirected_to_dashboard'),
                ]);
            }

            // We only want to return a specific error for actions that are not GET requests,
            // so we can show them in a toast
            if ($request->isMethod('GET')) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], 403);
            }

            return back()->withErrors([
                'error' => $message,
            ]);
        });
    })->create();
