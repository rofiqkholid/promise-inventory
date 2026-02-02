<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInventoryRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !$user->appRole) {
            abort(403, 'Access Denied. You do not have permission to access the Inventory System.');
        }

        // If specific roles are required, check them
        if (!empty($roles)) {
            if (!in_array($user->appRole->role->code, $roles)) {
                abort(403, 'Unauthorized. Your role (' . ucfirst($user->appRole->role->name) . ') does not allow this action.');
            }
        }

        return $next($request);
    }
}
