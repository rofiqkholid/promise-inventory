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

        if (!$user) {
            abort(403, 'Access Denied. Please log in first.');
        }

        // 1. Base Role Check
        // Ensure user has at least one role. We no longer restrict strictly by the hardcoded 
        // middleware parameter roles to allow for fully DB-driven dynamic access control.
        if ($user->roles->isEmpty()) {
            abort(403, 'Unauthorized. You do not have any assigned roles.');
        }

        // 2. Menu Access Check
        $routeName = $request->route()->getName();
        
        if ($routeName) {
            // Exceptions - always allowed for any user with ANY inventory access
            $exceptions = [
                'dashboard', 
                'profile.index', 
                'profile.update', 
                'profile.updatePassword', 
                'logout',
                'inventory.scanInfo' // Public scan is allowed but often falls under inventory prefix
            ];
            
            if (in_array($routeName, $exceptions)) {
                return $next($request);
            }

            // Check if the current route is allowed using scope-based check
            if ($user->hasMenuPermission($routeName, 'view')) {
                return $next($request);
            }

            // Log denial for debugging
            \Log::warning('CheckInventoryRole: Access Denied', [
                'user_id' => $user->id,
                'route'   => $routeName,
            ]);

            abort(403, 'Unauthorized. Access to this menu has been revoked or is not assigned.');
        }

        return $next($request);
    }

    /**
     * Helper to verify if route matches allowed menus or their sub-routes
     */
    private function hasAccess($routeName, array $allowedRoutes): bool
    {
        foreach ($allowedRoutes as $allowed) {
            // Exact match
            if ($routeName === $allowed) return true;

            // Handle resource-like sub-routes
            if (str_starts_with($routeName, $allowed . '.')) return true;

            // Handle .index suffix in allowed route
            if (str_ends_with($allowed, '.index')) {
                $base = substr($allowed, 0, -6);
                if ($routeName === $base || str_starts_with($routeName, $base . '.')) {
                    return true;
                }

                // Special case for User Access menu which has multiple distinct route prefixes
                if ($allowed === 'inventory.userAccess.index') {
                    $userAccessPrefixes = [
                        'inventory.roles.',
                        'inventory.menus.',
                        'inventory.users.',
                        'inventory.roleMenus.',
                        'inventory.userMenus.'
                    ];
                    
                    foreach ($userAccessPrefixes as $prefix) {
                        if (str_starts_with($routeName, $prefix)) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }
}
