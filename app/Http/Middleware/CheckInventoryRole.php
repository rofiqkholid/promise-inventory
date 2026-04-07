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

        // If user has no roles, check if they at least have some specific menu permissions
        if ($user->roles->isEmpty()) {
            if (!$user->specificMenus()->exists()) {
                abort(403, 'Access Denied. You do not have permission to access the Inventory System.');
            }
        }

        // If roles are specified in middleware, they act as a base restriction.
        // But we now allow overrides via User-Specific Menus and Role-Menu assignments in the database.
        if (!empty($roles)) {
            $routeName = $request->route()->getName();
            
            if ($routeName) {
                // Check if user has specific menu access OR their role has menu access in the database
                $segments = explode('.', $routeName);
                $possibleRoutes = [];
                $temp = '';
                foreach ($segments as $segment) {
                    $temp = $temp === '' ? $segment : $temp . '.' . $segment;
                    $possibleRoutes[] = $temp;
                }

                // Function to get all related routes for a set of menu routes
                $expandMenuRoutes = function($menuRoutes) {
                    $expanded = [];
                    foreach ($menuRoutes as $route) {
                        $expanded[] = $route;
                        // If route is 'a.b.c', also add 'a.b' and 'a'
                        $parts = explode('.', $route);
                        while (count($parts) > 1) {
                            array_pop($parts);
                            $expanded[] = implode('.', $parts);
                        }
                    }
                    return array_unique($expanded);
                };

                // Check user-specific menus
                $specificMenuRoutes = $user->specificMenus()->pluck('route')->toArray();
                $allowedSpecificRoutes = $expandMenuRoutes($specificMenuRoutes);
                
                if (in_array($routeName, $allowedSpecificRoutes) || array_intersect($possibleRoutes, $specificMenuRoutes)) {
                    return $next($request);
                }

                // Special case for Master Data: if you have 'inventory.master.master.index', you get its sub-routes
                $isMasterSubRoute = str_starts_with($routeName, 'inventory.master.');
                
                if ($isMasterSubRoute) {
                    if (in_array('inventory.master.master.index', $specificMenuRoutes)) {
                        return $next($request);
                    }
                }

                // Check role-based menus in database
                foreach ($user->roles as $role) {
                    $roleMenuRoutes = $role->menus()->pluck('route')->toArray();
                    $allowedRoleRoutes = $expandMenuRoutes($roleMenuRoutes);
                    
                    if (in_array($routeName, $allowedRoleRoutes) || array_intersect($possibleRoutes, $roleMenuRoutes)) {
                        return $next($request);
                    }

                    if ($isMasterSubRoute && in_array('inventory.master.master.index', $roleMenuRoutes)) {
                        return $next($request);
                    }
                }
            }

            // Fallback to the hardcoded role check in the middleware parameters
            $userRoleCodes = $user->roles->pluck('code')->toArray();
            if (empty(array_intersect($userRoleCodes, $roles))) {
                abort(403, 'Unauthorized. Your assigned roles do not allow this action.');
            }
        }

        return $next($request);
    }
}
