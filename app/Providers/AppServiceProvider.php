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

        // Sidebar Configuration - Load menus based on user role
        View::composer('layouts.sidebar', function ($view) {
            $userRole = null;
            $sidebarMenus = collect();

            if (auth()->check()) {
                $invRole = auth()->user()->appRole->role ?? null;
                $userRole = $invRole->code ?? null;

                if ($invRole) {
                    $sidebarMenus = $invRole->menus()
                        ->with(['children' => function ($q) {
                            $q->where('inv_m_menus.is_active', true);
                        }])
                        ->whereNull('inv_m_menus.parent_id')
                        ->where('inv_m_menus.is_active', true)
                        ->get();
                }
            }

            $view->with('sidebarMenus', $sidebarMenus)
                ->with('userRoleCode', $userRole);
        });
    }
}
