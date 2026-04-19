<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureAccountNotDeleting
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If the user is logged in and their account is scheduled for deletion
        if ($user?->scheduled_for_deletion_at) {
            // Check if they are trying to access the deletion recovery route or logging out
            if (! $request->routeIs('deletion.notice', 'deletion.restore', 'logout')) {
                return redirect()->route('deletion.notice');
            }
        }

        return $next($request);
    }
}
