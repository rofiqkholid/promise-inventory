<?php

namespace App\Http\Controllers;

use App\Models\InventoryModel\TransactionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $monthYear = $request->input('month_year', date('Y-m'));
        $selectedModels = $request->input('model', []);
        $selectedCustomers = $request->input('customer', []);
        $selectedStatusBalance = $request->input('status_balance', []);
        $selectedStatusUsage = $request->input('status_usage', []);

        $inCategories = TransactionCategory::where('effect', 1)->pluck('code');
        $outCategories = TransactionCategory::where('effect', -1)->pluck('code');

        $stockQuery = DB::table('inv_t_product_detail as p')
            ->join('products as prod', 'prod.id', '=', 'p.product_id')
            ->leftJoin('models as m', 'm.id', '=', 'prod.model_id')
            ->leftJoin('customers as c', 'c.id', '=', 'prod.customer_id');

        if (!empty($selectedModels)) $stockQuery->whereIn('prod.model_id', $selectedModels);
        if (!empty($selectedCustomers)) $stockQuery->whereIn('prod.customer_id', $selectedCustomers);

        $totalStockPcs = $stockQuery->sum(DB::raw('p.current_stock_qty * p.pcs_per_unit'));

        $queryTrans = DB::table('inv_t_inventory_transaction as t')
            ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
            ->join('inv_t_product_detail as p', 'p.id', '=', 't.product_detail_id')
            ->join('products as prod', 'prod.id', '=', 'p.product_id');

        if (!empty($selectedModels)) $queryTrans->whereIn('prod.model_id', $selectedModels);
        if (!empty($selectedCustomers)) $queryTrans->whereIn('prod.customer_id', $selectedCustomers);
        if ($monthYear) $queryTrans->where('t.transaction_date', 'like', "$monthYear%");

        $materialInSum = (clone $queryTrans)->whereIn('tc.code', $inCategories)->sum(DB::raw('t.qty * p.pcs_per_unit'));
        $materialOutSum = (clone $queryTrans)->whereIn('tc.code', $outCategories)->sum(DB::raw('t.qty * p.pcs_per_unit'));
        $materialOutPPSum = (clone $queryTrans)->where('tc.code', 'OUT-PP')->sum(DB::raw('t.qty * p.pcs_per_unit'));
        $materialOutEventSum = (clone $queryTrans)->where('tc.code', 'OUT-EVENT')->sum(DB::raw('t.qty * p.pcs_per_unit'));
        $materialOutTrialSum = (clone $queryTrans)->where('tc.code', 'OUT-TRIAL')->sum(DB::raw('t.qty * p.pcs_per_unit'));

        $allProducts = $stockQuery->select(
            'm.name as model_name', 
            'c.code as customer_code', 
            'p.current_stock_qty', 
            'p.min_stock'
        )->get();

        $stockDataGrouped = [];
        foreach ($allProducts as $prd) {
            $key = ($prd->model_name ?? 'N/A') . ' ' . ($prd->customer_code ?? 'N/A');
            if (!isset($stockDataGrouped[$key])) {
                $stockDataGrouped[$key] = ['critical' => 0, 'over' => 0, 'safe' => 0];
            }
            
            $current = floatval($prd->current_stock_qty);
            $min = floatval($prd->min_stock);
            $maxStock = $min * 3;
            
            if ($min > 0) {
                if ($current > $maxStock) $stockDataGrouped[$key]['over']++;
                elseif ($current < $min) $stockDataGrouped[$key]['critical']++;
                else $stockDataGrouped[$key]['safe']++;
            } else {
                $stockDataGrouped[$key]['safe']++;
            }
        }

        $usageByModel = (clone $queryTrans)
            ->join('models as m', 'm.id', '=', 'prod.model_id')
            ->join('customers as c', 'c.id', '=', 'prod.customer_id')
            ->whereIn('tc.code', $outCategories)
            ->select(
                DB::raw("m.name + ' ' + c.code as label"), 
                DB::raw('SUM(t.qty * p.pcs_per_unit) as total')
            )
            ->groupBy('m.name', 'c.code')
            ->get();

        $trendlineByCat = (clone $queryTrans)
            ->select(
                't.transaction_date', 
                'tc.code as category',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('t.transaction_date', 'tc.code')
            ->orderBy('t.transaction_date')
            ->get();

        $usageByMaker = (clone $queryTrans)
            ->join('inv_m_coil_center as cc', 'cc.id', '=', 'p.coil_center_id')
            ->whereIn('tc.code', $outCategories)
            ->select('cc.code', DB::raw('SUM(t.qty * p.pcs_per_unit) as total'))
            ->groupBy('cc.code')
            ->get();

        $balanceStatusTable = (clone $stockQuery)
            ->select('prod.part_no', 'p.revision', 'c.code as customer_code', 'm.name as model_name', 'p.current_stock_qty', 'p.min_stock')
            ->limit(10)
            ->get()
            ->map(function($item) {
                $current = floatval($item->current_stock_qty);
                $min = floatval($item->min_stock);
                if ($min > 0) {
                    if ($current > $min * 3) $item->status = 'Over';
                    elseif ($current < $min) $item->status = 'Critical';
                    else $item->status = 'Safe';
                } else {
                    $item->status = 'Safe';
                }
                return $item;
            });

        $usageStatusTable = (clone $balanceStatusTable);

        $transactionHistory = (clone $queryTrans)
            ->select('prod.part_no', 'p.revision', 't.qty', 'p.pcs_per_unit', 'tc.code as category', 't.transaction_date')
            ->orderByDesc('t.transaction_date')
            ->limit(10)
            ->get();

        $initialModels = [];
        if (!empty($selectedModels)) {
            $initialModels = DB::table('models')
                ->whereIn('id', $selectedModels)
                ->select('id', 'name')
                ->get();
        }

        $initialCustomers = [];
        if (!empty($selectedCustomers)) {
            $initialCustomers = DB::table('customers')
                ->whereIn('id', $selectedCustomers)
                ->select('id', 'code', 'name')
                ->get();
        }

        return view('dashboard', [
            'stats' => [
                'total_stock' => $totalStockPcs,
                'material_in' => $materialInSum,
                'material_out' => $materialOutSum,
                'out_pp' => $materialOutPPSum,
                'out_event' => $materialOutEventSum,
                'out_trial' => $materialOutTrialSum,
            ],
            'charts' => [
                'stock_grouped' => $stockDataGrouped,
                'usage_model' => $usageByModel,
                'trendline' => $trendlineByCat,
                'maker' => $usageByMaker
            ],
            'tables' => [
                'balance' => $balanceStatusTable,
                'usage' => $usageStatusTable,
                'history' => $transactionHistory
            ],
            'filters' => [
                'initial_models' => $initialModels, 
                'initial_customers' => $initialCustomers,
                'selected_models' => $selectedModels,
                'selected_customers' => $selectedCustomers,
                'selected_status_balance' => $selectedStatusBalance,
                'selected_status_usage' => $selectedStatusUsage,
                'month_year' => $monthYear
            ]
        ]);
    }

    public function getModels(Request $request)
    {
        $term = $request->term;
        $query = DB::table('models')->select('id', 'name as text');

        if ($term) {
            $query->where('name', 'like', '%' . $term . '%');
        }

        $data = $query->orderBy('name')->simplePaginate(20);

        return response()->json([
            'results' => $data->items(),
            'pagination' => ['more' => $data->hasMorePages()]
        ]);
    }

    public function getCustomers(Request $request)
    {
        $term = $request->term;
        $query = DB::table('customers')
            ->select('id', DB::raw("CONCAT(code, ' - ', name) as text"));

        if ($term) {
            $query->where(function($q) use ($term) {
                $q->where('code', 'like', '%' . $term . '%')
                  ->orWhere('name', 'like', '%' . $term . '%');
            });
        }

        $data = $query->orderBy('code')->simplePaginate(20);

        return response()->json([
            'results' => $data->items(),
            'pagination' => ['more' => $data->hasMorePages()]
        ]);
    }
}