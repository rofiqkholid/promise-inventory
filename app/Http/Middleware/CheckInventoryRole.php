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
        if (!empty($roles)) {
            $routeName = $request->route()->getName() ?? '';

            // STEP 1: Fast-pass — if user's hardcoded role code is in the middleware role list,
            // allow immediately. This covers ALL routes in the group, including AJAX helpers
            // like getSheetNames, importExcel, etc., without needing DB menu entries.
            $userRoleCodes = $user->roles->pluck('code')->toArray();
            if (!empty(array_intersect($userRoleCodes, $roles))) {
                return $next($request);
            }

            // STEP 2: DB Menu check — for users who may not have a matching hardcoded role
            // but have been granted specific menu access via User-Specific Menus or Role Menus.
            if ($routeName) {
                $segments = explode('.', $routeName);
                $possibleRoutes = [];
                $temp = '';
                foreach ($segments as $segment) {
                    $temp = $temp === '' ? $segment : $temp . '.' . $segment;
                    $possibleRoutes[] = $temp;
                }

                // Also check parent routes with '.index' suffix
                $indexedPossibleRoutes = array_map(fn($r) => $r . '.index', $possibleRoutes);

                // Helper to expand menu routes to include their parents
                $expandMenuRoutes = function ($menuRoutes) {
                    $expanded = [];
                    foreach ($menuRoutes as $route) {
                        $expanded[] = $route;
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

                if (in_array($routeName, $allowedSpecificRoutes) ||
                    array_intersect($possibleRoutes, $specificMenuRoutes) ||
                    array_intersect($indexedPossibleRoutes, $specificMenuRoutes)) {
                    return $next($request);
                }

                // Special case for Master Data
                $isMasterSubRoute = str_starts_with($routeName, 'inventory.master.');
                if ($isMasterSubRoute && in_array('inventory.master.master.index', $specificMenuRoutes)) {
                    return $next($request);
                }

                // Check role-based menus in database
                foreach ($user->roles as $role) {
                    $roleMenuRoutes = $role->menus()->pluck('route')->toArray();
                    $allowedRoleRoutes = $expandMenuRoutes($roleMenuRoutes);

                    if (in_array($routeName, $allowedRoleRoutes) ||
                        array_intersect($possibleRoutes, $roleMenuRoutes) ||
                        array_intersect($indexedPossibleRoutes, $roleMenuRoutes)) {
                        return $next($request);
                    }

                    if ($isMasterSubRoute && in_array('inventory.master.master.index', $roleMenuRoutes)) {
                        return $next($request);
                    }
                }
            }

            // Log for debugging production issues
            \Log::warning('CheckInventoryRole: 403 Access Denied', [
                'user_id'        => $user->id,
                'route'          => $routeName,
                'user_roles'     => $userRoleCodes,
                'required_roles' => $roles,
            ]);

            abort(403, 'Unauthorized. Your assigned roles do not allow this action.');
        }

        return $next($request);
    }
}
