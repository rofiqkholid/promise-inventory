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
            $routeName = $request->route()->getName();
            $userRoleCodes = array_map('strtolower', $user->roles->pluck('code')->toArray());
            $requiredRoles = array_map('strtolower', $roles);
            
            if ($routeName) {
                // Check if user has specific menu access OR their role has menu access in the database
                $segments = explode('.', $routeName);
                $possibleRoutes = [];
                $temp = '';
                foreach ($segments as $segment) {
                    $temp = $temp === '' ? $segment : $temp . '.' . $segment;
                    $possibleRoutes[] = $temp;
                }

                $expandMenuRoutes = function($menuRoutes) {
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

                // 1. Check user-specific menus
                $specificMenuRoutes = $user->specificMenus()->pluck('route')->toArray();
                $allowedSpecificRoutes = $expandMenuRoutes($specificMenuRoutes);
                
                if (in_array($routeName, $allowedSpecificRoutes) || array_intersect($possibleRoutes, $allowedSpecificRoutes)) {
                    return $next($request);
                }

                // 2. Check role-based menus in database
                foreach ($user->roles as $role) {
                    $roleMenuRoutes = $role->menus()->pluck('route')->toArray();
                    $allowedRoleRoutes = $expandMenuRoutes($roleMenuRoutes);
                    
                    if (in_array($routeName, $allowedRoleRoutes) || array_intersect($possibleRoutes, $allowedRoleRoutes)) {
                        return $next($request);
                    }
                }
            }

            // 3. Fallback to the hardcoded role check
            if (empty(array_intersect($userRoleCodes, $requiredRoles))) {
                $msg = 'Unauthorized. Required: ' . implode(',', $requiredRoles) . '. User has: ' . implode(',', $userRoleCodes);
                \Illuminate\Support\Facades\Log::warning('[InventoryRole] Access Denied', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'roles_found' => $userRoleCodes,
                    'roles_required' => $requiredRoles,
                    'route_name' => $routeName,
                    'url' => $request->fullUrl(),
                    'method' => $request->method()
                ]);

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 403);
                }
                abort(403, $msg);
            }
        }

        return $next($request);
    }
}
