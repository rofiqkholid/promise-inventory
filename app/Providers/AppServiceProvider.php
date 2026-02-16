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
        if (config('app.url')) {
            URL::forceRootUrl(config('app.url'));
        }

        if (str_contains(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
        View::composer(['layouts.header', 'components.stock-alert-modal'], function ($view) {
            $stockAlerts = DB::table('inv_t_product_detail as p')
                ->join('products as prod', 'prod.id', '=', 'p.product_id')
                ->leftJoin('models as m', 'm.id', '=', 'prod.model_id')
                ->leftJoin('customers as c', 'c.id', '=', 'prod.customer_id')
                ->select('prod.part_no', 'p.revision', 'c.code as customer_code', 'm.name as model_name', 'p.current_stock_qty', 'p.min_stock', 'p.pcs_per_unit')
                ->get()
                ->map(function ($item) {
                    $pcsPerUnit = intval($item->pcs_per_unit);
                    if ($pcsPerUnit <= 0) $pcsPerUnit = 1;

                    $currentPCS = floatval($item->current_stock_qty) * $pcsPerUnit;
                    $minPCS = floatval($item->min_stock);

                    $item->current_stock_qty = $currentPCS;
                    $item->min_stock = $minPCS;

                    if ($minPCS > 0) {
                        if ($currentPCS > $minPCS * 3) $item->status = 'Warning';
                        elseif ($currentPCS < $minPCS) $item->status = 'Critical';
                        else $item->status = 'Safe';
                    } else {
                        $item->status = 'Safe';
                    }
                    return $item;
                })
                ->filter(function ($item) {
                    return $item->status === 'Warning' || $item->status === 'Critical';
                })
                ->values();

            $view->with('stockAlerts', $stockAlerts);

            // Only handle auto-open for the modal component to avoid session flag being set by header
            if (str_contains($view->getName(), 'stock-alert-modal')) {
                $stockAlertAutoOpen = false;
                if (count($stockAlerts) > 0 && !Session::has('stock_alert_auto_shown')) {
                    $stockAlertAutoOpen = true;
                    Session::put('stock_alert_auto_shown', true);
                }
                $view->with('stockAlertAutoOpen', $stockAlertAutoOpen);
            }
        });

        // Sidebar Configuration - Load menus based on user role & specific user permissions
        \Illuminate\Support\Facades\View::composer('layouts.sidebar', function ($view) {
            $userRole = null;
            $sidebarMenus = collect();

            if (auth()->check()) {
                $user = auth()->user();
                $invRole = $user->appRole->role ?? null;
                $userRole = $invRole->code ?? null;
                
                // Get menu IDs from role
                $roleMenuIds = $invRole ? $invRole->menus()->pluck('inv_m_menus.id')->toArray() : [];
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
