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
                ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
                ->leftJoin('customers as c', 'c.id', '=', 'prod.customer_id')
                ->leftJoin('inv_m_model_status as ms', 'ms.model_id', '=', 'p.model_id')
                ->leftJoin('inv_m_revision as r', 'r.id', '=', 'p.revision_id')
                ->select([
                    'prod.part_no', 
                    'r.code as revision', 
                    'c.code as customer_code', 
                    'm.name as model_name', 
                    'p.current_stock_qty', 
                    'p.min_stock', 
                    'p.pcs_per_unit',
                    'p.product_status',
                    'ms.project_status'
                ])
                ->get()
                ->map(function ($item) {
                    $pcsPerUnit = intval($item->pcs_per_unit);
                    if ($pcsPerUnit <= 0) $pcsPerUnit = 1;

                    $currentPCS = floatval($item->current_stock_qty) * $pcsPerUnit;
                    $minPCS = floatval($item->min_stock);

                    $item->current_stock_qty = $currentPCS;
                    $item->min_stock = $minPCS;

                    if ($minPCS > 0) {
                        if ($currentPCS > $minPCS * 3) {
                            $item->status = 'Warning';
                        } elseif ($currentPCS < $minPCS) {
                            // Suppress Critical for Regular or Allsize status
                            $safeStatuses = ['Regular', 'Allsize OK', 'Allsize NG'];
                            $isSafeOverride = in_array($item->project_status, $safeStatuses) || in_array($item->product_status, $safeStatuses);
                            
                            $item->status = $isSafeOverride ? 'Safe' : 'Critical';
                        } else {
                            $item->status = 'Safe';
                        }
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
