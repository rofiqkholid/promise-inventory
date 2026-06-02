<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;


use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\InventoryModel\Tool\TolFastStock::observe(\App\Observers\TolFastStockObserver::class);

        if (config('app.url')) {
            URL::forceRootUrl(config('app.url'));
        }

        if (str_contains(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
        View::composer(['layouts.header', 'components.stock-alert-modal'], function ($view) {
            if (!auth()->check()) {
                $view->with('stockAlerts', collect());
                if (str_contains($view->getName(), 'stock-alert-modal')) {
                    $view->with('stockAlertAutoOpen', false);
                }
                return;
            }

            // Use request-level caching to avoid duplicate queries within the same request
            static $cachedStockAlerts = null;

            if ($cachedStockAlerts === null) {
                $cachedStockAlerts = DB::table('inv_t_product_detail as p')
                    ->join('products as prod', 'prod.id', '=', 'p.product_id')
                    ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
                    ->leftJoin('customers as c', 'c.id', '=', 'prod.customer_id')
                    ->leftJoin('inv_m_model_status as ms', 'ms.model_id', '=', 'p.model_id')
                    ->leftJoin('inv_m_revision as r', 'r.id', '=', 'p.revision_id')
                    ->leftJoin('inv_m_unit as u', 'u.id', '=', 'p.unit_id')
                    ->select([
                        'prod.part_no', 
                        'r.code as revision', 
                        'c.code as customer_code', 
                        'm.name as model_name', 
                        'p.current_stock_qty', 
                        'p.min_stock', 
                        'p.pcs_per_unit',
                        'p.product_status',
                        'ms.project_status',
                        'p.weight_kg',
                        'p.gross_coil',
                        'u.name as unit_name'
                    ])
                    ->where('p.is_active', 1)
                    ->get()
                    ->map(function ($item) {
                        $currentPCS = \App\Models\InventoryModel\Material\InventoryProduct::calculatePcs(
                            $item->current_stock_qty, 
                            $item->weight_kg, 
                            $item->pcs_per_unit, 
                            $item->unit_name,
                            0, 0, 0, 1, // Defaults for top, end, pitch, pcsPerPitch
                            $item->gross_coil
                        );

                        $item->status = ucfirst(\App\Models\InventoryModel\Material\InventoryProduct::calculateStockStatus(
                            $currentPCS, 
                            $item->min_stock, 
                            $item->project_status,
                            $item->product_status
                        ));

                        // Update values for display in the modal
                        $item->current_stock_qty = $currentPCS;
                        $item->min_stock = floatval($item->min_stock);

                        return $item;
                    })
                    ->filter(function ($item) {
                        return in_array($item->status, ['Warning', 'Critical', 'Over']);
                    })
                    ->values();
            }

            $view->with('stockAlerts', $cachedStockAlerts);

            // Only handle auto-open logic for the modal component
            if (str_contains($view->getName(), 'stock-alert-modal')) {
                $stockAlertAutoOpen = false;
                
                // Only auto-open if alerts exist AND the flag isn't set in the session yet
                if (count($cachedStockAlerts) > 0 && !Session::has('stock_alert_auto_shown')) {
                    $stockAlertAutoOpen = true;
                    Session::put('stock_alert_auto_shown', true);
                    
                    // Explicitly save session because View::composers can run late in the request lifecycle
                    // where auto-saving might not reliably pick up changes for the next request.
                    Session::save();
                }
                
                $view->with('stockAlertAutoOpen', $stockAlertAutoOpen);
            }
        });

        // Sidebar Configuration - Load menus based on user role & specific user permissions
        View::composer('layouts.sidebar', function ($view) {
            $userRole = null;
            $sidebarMenus = collect();

            if (auth()->check()) {
                $user = auth()->user();
                $roles = $user->roles;
                $userRole = $roles->pluck('code')->first(); // Just for compatibility if needed elsewhere
                
                // Get menu IDs from all roles
                $roleMenuIds = $roles->pluck('menus')->flatten()->pluck('id')->unique()->toArray();
                
                // Get menu IDs from specific user permissions
                $specificMenuIds = $user->specificMenus()->pluck('inv_m_menus.id')->toArray();
                
                $allowedMenuIds = array_unique(array_merge($roleMenuIds, $specificMenuIds));

                if (!empty($allowedMenuIds)) {
                    $sidebarMenus = \App\Models\InventoryModel\Menu::where('is_active', true)
                        ->whereNull('parent_id')
                        ->where(function($query) use ($allowedMenuIds) {
                            $query->whereIn('id', $allowedMenuIds)
                                  ->orWhereHas('children', function($q) use ($allowedMenuIds) {
                                      $q->whereIn('id', $allowedMenuIds)->where('is_active', true);
                                  });
                        })
                        ->with(['children' => function($q) use ($allowedMenuIds) {
                            $q->whereIn('id', $allowedMenuIds)->where('is_active', true);
                        }])
                        ->orderBy('order')
                        ->get();
                }
            }

            $view->with('sidebarMenus', $sidebarMenus)
                ->with('userRoleCode', $userRole);
        });
    }
}
