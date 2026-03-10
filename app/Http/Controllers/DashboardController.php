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
        $selectedStatusUsage = $request->input('status_usage', []); // Assuming same logic for now or we can implement separate if needed

        $inCategories = TransactionCategory::where('effect', 1)->pluck('code');
        $outCategories = TransactionCategory::where('effect', -1)->pluck('code');

        // Helper for Status Filtering
        $applyStatusFilter = function($query, $statuses) {
            if (empty($statuses)) return;
            $query->where(function($q) use ($statuses) {
                 foreach ($statuses as $status) {
                     if ($status === 'Critical') {
                         $q->orWhere(function($w) {
                             $w->whereColumn('p.current_stock_qty', '<', 'p.min_stock')
                               ->where('p.min_stock', '>', 0)
                               ->where(function($sq) {
                                   $sq->where(function($inner) {
                                       $inner->where('ms.project_status', '!=', 'Regular')
                                             ->orWhereNull('ms.project_status');
                                   })
                                   ->where(function($inner) {
                                       $inner->whereNotIn('p.product_status', ['Allsize OK', 'Allsize NG'])
                                             ->orWhereNull('p.product_status');
                                   });
                               });
                         });
                     } elseif ($status === 'Over') {
                         $q->orWhere(function($w) {
                             $w->whereColumn('p.current_stock_qty', '>', DB::raw('p.min_stock * 3'))
                               ->where('p.min_stock', '>', 0);
                         });
                     } elseif ($status === 'Safe') {
                         $q->orWhere(function($w) {
                              $w->where(function($inner) {
                                  // Standard safe range
                                  $inner->where(function($std) {
                                      $std->whereColumn('p.current_stock_qty', '>=', 'p.min_stock')
                                          ->whereColumn('p.current_stock_qty', '<=', DB::raw('p.min_stock * 3'));
                                  })
                                  // OR Safe Overrides (Shortage becomes Safe)
                                  ->orWhere(function($override) {
                                      $override->whereColumn('p.current_stock_qty', '<', 'p.min_stock')
                                               ->where(function($sq) {
                                                   $sq->where('ms.project_status', 'Regular')
                                                      ->orWhereIn('p.product_status', ['Allsize OK', 'Allsize NG']);
                                               });
                                  })
                                  ->orWhere('p.min_stock', '<=', 0)
                                  ->orWhereNull('p.min_stock');
                              });
                         });
                     }
                 }
            });
        };

        // 1. Stock Query (Filtered)
        $stockQuery = DB::table('inv_t_product_detail as p')
            ->join('products as prod', 'prod.id', '=', 'p.product_id')
            ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
            ->leftJoin('customers as c', 'c.id', '=', 'prod.customer_id')
            ->leftJoin('inv_m_model_status as ms', 'ms.model_id', '=', 'p.model_id');

        if (!empty($selectedModels)) $stockQuery->whereIn('p.model_id', $selectedModels);
        if (!empty($selectedCustomers)) $stockQuery->whereIn('prod.customer_id', $selectedCustomers);
        $applyStatusFilter($stockQuery, $selectedStatusBalance);

        $totalStockPcs = $stockQuery->sum(DB::raw('p.current_stock_qty * p.pcs_per_unit'));

        // 2. Base Transaction Query (For Recent History - UNFILTERED except maybe strict scope if needed, but user asked to exclude filters)
        // actually we should probably respect NO filters for "Recent Transactions" to show global activity.
        $recentTransQuery = DB::table('inv_t_inventory_transaction as t')
            ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
            ->join('inv_t_product_detail as p', 'p.id', '=', 't.product_detail_id')
            ->join('products as prod', 'prod.id', '=', 'p.product_id')
            ->leftJoin('inv_m_model_status as ms', 'ms.model_id', '=', 'p.model_id');
        
        // 3. Filtered Transaction Query (For Charts/Stats)
        $queryTrans = clone $recentTransQuery;

        if (!empty($selectedModels)) $queryTrans->whereIn('p.model_id', $selectedModels);
        if (!empty($selectedCustomers)) $queryTrans->whereIn('prod.customer_id', $selectedCustomers);
        if ($monthYear) $queryTrans->where('t.transaction_date', 'like', "$monthYear%");
        $applyStatusFilter($queryTrans, $selectedStatusBalance);

        $materialInSum = (clone $queryTrans)->whereIn('tc.code', $inCategories)->sum(DB::raw('t.qty * p.pcs_per_unit'));
        $materialOutSum = (clone $queryTrans)->whereIn('tc.code', $outCategories)->sum(DB::raw('t.qty * p.pcs_per_unit'));
        $materialOutPPSum = (clone $queryTrans)->where('tc.code', 'OUT-PP')->sum(DB::raw('t.qty * p.pcs_per_unit'));
        $materialOutEventSum = (clone $queryTrans)->where('tc.code', 'OUT-EVENT')->sum(DB::raw('t.qty * p.pcs_per_unit'));
        $materialOutTrialSum = (clone $queryTrans)->where('tc.code', 'OUT-TRIAL')->sum(DB::raw('t.qty * p.pcs_per_unit'));

        $allProducts = $stockQuery->select(
            'm.name as model_name',
            'c.code as customer_code',
            'p.current_stock_qty',
            'p.min_stock',
            'p.product_status',
            'ms.project_status'
        )->get();

        $stockDataGrouped = [];
        foreach ($allProducts as $prd) {
            $key = ($prd->model_name ?? 'N/A') . '|' . ($prd->customer_code ?? 'N/A');
            if (!isset($stockDataGrouped[$key])) {
                $stockDataGrouped[$key] = ['critical' => 0, 'over' => 0, 'safe' => 0];
            }

            $current = floatval($prd->current_stock_qty);
            $min = floatval($prd->min_stock);
            $maxStock = $min * 3;

            if ($min > 0) {
                if ($current > $maxStock) {
                    $stockDataGrouped[$key]['over']++;
                } elseif ($current < $min) {
                    // Exclusion logic for KPIs
                    $safeStatuses = ['Regular', 'Allsize OK', 'Allsize NG'];
                    $isSafeOverride = in_array($prd->project_status, $safeStatuses) || in_array($prd->product_status, $safeStatuses);
                    
                    if ($isSafeOverride) $stockDataGrouped[$key]['safe']++;
                    else $stockDataGrouped[$key]['critical']++;
                } else {
                    $stockDataGrouped[$key]['safe']++;
                }
            } else {
                $stockDataGrouped[$key]['safe']++;
            }
        }

        $usageByModel = (clone $queryTrans)
            ->join('models as m', 'm.id', '=', 'p.model_id')
            ->join('customers as c', 'c.id', '=', 'prod.customer_id')
            ->whereIn('tc.code', $outCategories)
            ->select(
                DB::raw("m.name + '|' + c.code as label"),
                DB::raw('SUM(t.qty * p.pcs_per_unit) as total')
            )
            ->groupBy('m.name', 'c.code')
            ->get();

        // Trend Line (Filtered by Year of selected Month)
        $trendQuery = clone $queryTrans;
        // Reset month filter from $queryTrans if it was exact match, we need YEAR match
        // But $queryTrans has `where(..., like, $monthYear%)`. We need to replace or ensure we match the YEAR.
        // Actually, $queryTrans alrdy restricts to that MONTH. User said "transaction trend sebaiknya perbulan".
        // If user selects "Jan 2024", trend chart showing "Jan" with data is fine, but usually trend shows WHOLE YEAR?
        // "Transaction trend usually per month" implied showing multiple months.
        // If I limit $queryTrans to Jan, trend chart only shows Jan.
        // So I must remove the month filter for the trend query, but keep other filters.
        
        $trendQuery = clone $recentTransQuery; // Start fresh from base (no month filter)
        if (!empty($selectedModels)) $trendQuery->whereIn('p.model_id', $selectedModels);
        if (!empty($selectedCustomers)) $trendQuery->whereIn('prod.customer_id', $selectedCustomers);
        $applyStatusFilter($trendQuery, $selectedStatusBalance);
        
        $trendlineByCat = $trendQuery
            ->whereYear('t.transaction_date', substr($monthYear, 0, 4))
            ->select(
                DB::raw("MONTH(t.transaction_date) as month_num"),
                'tc.code as category',
                DB::raw('SUM(t.qty * p.pcs_per_unit) as total')
            )
            ->groupBy(DB::raw("MONTH(t.transaction_date)"), 'tc.code')
            ->orderBy(DB::raw("MONTH(t.transaction_date)"))
            ->get()
            ->map(function ($item) {
                $dateObj = \DateTime::createFromFormat('!m', $item->month_num);
                $item->transaction_date = $dateObj ? $dateObj->format('M') : '-';
                return $item;
            });

        $usageByMaker = (clone $queryTrans)
            ->join('inv_m_coil_center as cc', 'cc.id', '=', 't.coil_center_id')
            ->whereIn('tc.code', $inCategories)
            ->select('cc.code', DB::raw('SUM(t.qty * p.pcs_per_unit) as total'))
            ->groupBy('cc.code')
            ->get();

        $balanceStatusTable = (clone $stockQuery)
            ->leftJoin('inv_m_revision as r', 'r.id', '=', 'p.revision_id')
            ->select('prod.part_no', 'r.code as revision', 'c.code as customer_code', 'm.name as model_name', 'p.current_stock_qty', 'p.min_stock')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                // Re-calculate status for display
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

        // Recent Transactions (Unfiltered Global)
        $transactionHistory = (clone $recentTransQuery)
            ->leftJoin('inv_m_revision as r', 'r.id', '=', 'p.revision_id')
            ->select('prod.part_no', 'r.code as revision', 't.qty', 'p.pcs_per_unit', 'tc.code as category', 't.transaction_date')
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

        if ($request->ajax()) {
            return response()->json([
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
                ]
            ]);
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
        $customerIds = (array) $request->input('customer_id');

        // Main query: GROUP BY name to remove redundancy
        $query = DB::table('models')
            ->select(DB::raw('MIN(id) as id'), 'name as text')
            ->groupBy('name');

        if (!empty($customerIds)) {
            // Join with products if customer specified
            $query->whereIn('customer_id', $customerIds);
        }

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
            ->select('id', 'code as text');

        if ($term) {
            $query->where(function ($q) use ($term) {
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
    public function getStatuses(Request $request, $type)
    {
        $statuses = [];

        if ($type === 'balance') {
            $statuses = ['Critical', 'Over', 'Safe'];
        } elseif ($type === 'usage') {
            $statuses = ['Over', 'Safe'];
        }

        $formatted = array_map(function ($item) {
            return ['id' => $item, 'text' => $item];
        }, $statuses);

        return response()->json(['results' => $formatted]);
    }
}
