<?php

namespace App\Providers;

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
        \Illuminate\Support\Facades\View::composer(['layouts.header', 'components.stock-alert-modal'], function ($view) {
            $stockAlerts = \Illuminate\Support\Facades\DB::table('inv_t_product_detail as p')
                ->join('products as prod', 'prod.id', '=', 'p.product_id')
                ->leftJoin('models as m', 'm.id', '=', 'prod.model_id')
                ->leftJoin('customers as c', 'c.id', '=', 'prod.customer_id')
                ->select('prod.part_no', 'p.revision', 'c.code as customer_code', 'm.name as model_name', 'p.current_stock_qty', 'p.min_stock')
                ->get()
                ->map(function ($item) {
                    $current = floatval($item->current_stock_qty);
                    $min = floatval($item->min_stock);
                    if ($min > 0) {
                        if ($current > $min * 3) $item->status = 'Warning';
                        elseif ($current < $min) $item->status = 'Critical';
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
        });
    }
}
