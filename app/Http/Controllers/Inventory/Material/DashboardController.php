<?php

namespace App\Http\Controllers\Inventory\Material;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\Material\InventoryProduct;
use App\Models\InventoryModel\Material\TransactionCategory;
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
                                       $inner->whereNotIn('p.product_status', ['Oldstock OK', 'Oldstock NG'])
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
                                  $inner->where(function($std) {
                                      $std->whereColumn('p.current_stock_qty', '>=', 'p.min_stock')
                                          ->whereColumn('p.current_stock_qty', '<=', DB::raw('p.min_stock * 3'));
                                  })
                                  ->orWhere(function($override) {
                                      $override->whereColumn('p.current_stock_qty', '<', 'p.min_stock')
                                               ->where(function($sq) {
                                                   $sq->where('ms.project_status', 'Regular')
                                                      ->orWhereIn('p.product_status', ['Oldstock OK', 'Oldstock NG']);
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

        // 1. Stock Query
        $stockQuery = DB::table('inv_t_product_detail as p')
            ->join('products as prod', 'prod.id', '=', 'p.product_id')
            ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
            ->leftJoin('customers as c', 'c.id', '=', 'prod.customer_id')
            ->leftJoin('inv_m_unit as u', 'u.id', '=', 'p.unit_id')
            ->leftJoin('inv_m_model_status as ms', 'ms.model_id', '=', 'p.model_id')
            ->where('p.is_active', 1);

        if (!empty($selectedModels)) $stockQuery->whereIn('p.model_id', $selectedModels);
        if (!empty($selectedCustomers)) $stockQuery->whereIn('prod.customer_id', $selectedCustomers);
        $applyStatusFilter($stockQuery, $selectedStatusBalance);

        $pcsSql = \App\Models\InventoryModel\Material\InventoryProduct::getPcsCalculationSql('p.current_stock_qty', 'p', 'u.name');
        $amountSql = \App\Models\InventoryModel\Material\InventoryProduct::getAmountCalculationSql('p.current_stock_qty', 'p', 'u.name');
        
        $totalStockPcs = (clone $stockQuery)->selectRaw("SUM({$pcsSql}) as total")->value('total') ?? 0;
        $totalStockAmount = (clone $stockQuery)->selectRaw("SUM({$amountSql}) as total")->value('total') ?? 0;

        // 2. Transaction Query Base
        $recentTransQuery = DB::table('inv_t_inventory_transaction as t')
            ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
            ->join('inv_t_product_detail as p', 'p.id', '=', 't.product_detail_id')
            ->leftJoin('inv_m_unit as u', 'u.id', '=', 'p.unit_id')
            ->join('products as prod', 'prod.id', '=', 'p.product_id')
            ->leftJoin('customers as c', 'c.id', '=', 'prod.customer_id')
            ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
            ->leftJoin('inv_m_model_status as ms', 'ms.model_id', '=', 'p.model_id');

        $queryTrans = clone $recentTransQuery;
        if (!empty($selectedModels)) $queryTrans->whereIn('p.model_id', $selectedModels);
        if (!empty($selectedCustomers)) $queryTrans->whereIn('prod.customer_id', $selectedCustomers);
        if ($monthYear) $queryTrans->where('t.transaction_date', 'like', "$monthYear%");
        $applyStatusFilter($queryTrans, $selectedStatusBalance);

        // Stats (Item Part)
        $materialInCount = (clone $queryTrans)->whereIn('tc.code', $inCategories)->distinct()->count('p.id');
        $materialOutEventCount = (clone $queryTrans)->where('tc.code', 'OUT-EVENT')->distinct()->count('p.id');
        $materialOutPPCount = (clone $queryTrans)->where('tc.code', 'OUT-PP')->distinct()->count('p.id');
        $materialOutTrialCount = (clone $queryTrans)->where('tc.code', 'OUT-TRIAL')->distinct()->count('p.id');

        $stats = [
            'total_stock' => $totalStockPcs,
            'total_stock_value' => $totalStockAmount,
            'material_in' => $materialInCount,
            'out_event' => $materialOutEventCount,
            'out_pp' => $materialOutPPCount,
            'out_trial' => $materialOutTrialCount,
        ];

        // Stock Data for Bar Chart (Item Count per Status)
        $allProducts = $stockQuery->select('m.name as model_name', 'c.code as customer_code', 'p.current_stock_qty', 'p.min_stock', 'p.product_status', 'ms.project_status')->get();
        $stockDataGrouped = [];
        foreach ($allProducts as $prd) {
            $key = ($prd->model_name ?? 'N/A') . '|' . ($prd->customer_code ?? 'N/A');
            if (!isset($stockDataGrouped[$key])) $stockDataGrouped[$key] = ['critical' => 0, 'warning' => 0, 'over' => 0, 'safe' => 0];
            $current = floatval($prd->current_stock_qty);
            $min = floatval($prd->min_stock);
            if ($min > 0) {
                if ($current > $min * 3) {
                    $stockDataGrouped[$key]['over']++;
                } elseif ($current < $min) {
                    $safeStatuses = ['Regular', 'Oldstock OK', 'Oldstock NG'];
                    $isSafeOverride = in_array($prd->project_status, $safeStatuses) || in_array($prd->product_status, $safeStatuses);
                    
                    if ($isSafeOverride) {
                        $stockDataGrouped[$key]['safe']++;
                    } else {
                        if ($current < ($min - 30)) {
                            $stockDataGrouped[$key]['critical']++;
                        } else {
                            $stockDataGrouped[$key]['warning']++;
                        }
                    }
                } else {
                    $stockDataGrouped[$key]['safe']++;
                }
            } else {
                $stockDataGrouped[$key]['safe']++;
            }
        }

        // Usage by Model (Item Part) - Grouped for Stacked Chart
        $usageByModelData = (clone $queryTrans)
            ->whereIn('tc.code', $outCategories)
            ->select(
                DB::raw("m.name + '|' + c.code as label"), 
                'tc.code as category',
                DB::raw("COUNT(DISTINCT p.id) as total")
            )
            ->groupBy('m.name', 'c.code', 'tc.code')
            ->get();

        $usageByModel = [];
        $groupedUsage = [];
        foreach ($usageByModelData as $item) {
            if (!isset($groupedUsage[$item->label])) {
                $groupedUsage[$item->label] = ['OUT-EVENT' => 0, 'OUT-PP' => 0, 'OUT-TRIAL' => 0];
            }
            $groupedUsage[$item->label][$item->category] = $item->total;
        }

        foreach ($groupedUsage as $label => $counts) {
            $usageByModel[] = [
                'label' => $label,
                'event' => $counts['OUT-EVENT'],
                'pp' => $counts['OUT-PP'],
                'trial' => $counts['OUT-TRIAL']
            ];
        }

        // Trendline (Item Part) - Always 12 months
        $trendYear = substr($monthYear, 0, 4) ?: date('Y');
        $trendQuery = clone $recentTransQuery;
        if (!empty($selectedModels)) $trendQuery->whereIn('p.model_id', $selectedModels);
        if (!empty($selectedCustomers)) $trendQuery->whereIn('prod.customer_id', $selectedCustomers);
        
        $trendDataRaw = $trendQuery
            ->whereYear('t.transaction_date', $trendYear)
            ->select(
                DB::raw("MONTH(t.transaction_date) as month_num"), 
                'tc.code as category', 
                DB::raw("SUM(t.qty * ISNULL(p.pcs_per_unit, 1)) as total")
            )
            ->groupBy(DB::raw("MONTH(t.transaction_date)"), 'tc.code')
            ->get();

        $trendlineByCat = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $categories = ['IN', 'OUT-EVENT', 'OUT-PP', 'OUT-TRIAL']; // Define standard categories
        
        foreach ($months as $mIdx => $mName) {
            $monthNum = $mIdx + 1;
            foreach ($categories as $cat) {
                $found = $trendDataRaw->where('month_num', $monthNum)->where('category', $cat)->first();
                $trendlineByCat[] = [
                    'transaction_date' => $mName,
                    'category' => $cat,
                    'total' => $found ? $found->total : 0
                ];
            }
        }

        // Usage Status by Maker (Supplier)
        $makerUsageQuery = (clone $queryTrans)
            ->join('inv_m_supplier as s', 's.id', '=', 't.supplier_id')
            ->join('inv_m_rank as r', 'r.id', '=', 'p.rank_id')
            ->leftJoin('inv_m_revision as rev', 'rev.id', '=', 'p.revision_id')
            ->where('tc.code', 'OUT-TRIAL')
            ->select([
                's.code as maker',
                'prod.part_no',
                'rev.code as revision',
                'p.id as product_id',
                'r.code as rank_code',
                'r.process_type',
                'r.limit_value',
                'p.unit_per_car',
                'p.pcs_per_unit',
                'p.gross_coil',
                'u.name as unit_name',
                DB::raw("SUM(t.qty) as usage_qty")
            ])
            ->groupBy('s.code', 'prod.part_no', 'rev.code', 'p.id', 'r.code', 'r.process_type', 'r.limit_value', 'p.unit_per_car', 'p.pcs_per_unit', 'p.gross_coil', 'u.name')
            ->get();

        $makerData = [];
        foreach ($makerUsageQuery as $item) {
            $limit = $this->calculateAdjustedRank($item->process_type, $item->limit_value, $item->unit_per_car, $item->pcs_per_unit);
            $usagePcs = \App\Models\InventoryModel\Material\InventoryProduct::calculatePcs($item->usage_qty, 0, $item->pcs_per_unit, $item->unit_name, 0, 0, 0, 1, $item->gross_coil);
            $gap = $limit - $usagePcs;
            $status = ($gap < 0) ? 'loss' : (($gap < 50) ? 'near_loss' : 'on_budget');
            $maker = $item->maker ?: 'Unknown';
            if (!isset($makerData[$maker])) $makerData[$maker] = ['on_budget' => 0, 'near_loss' => 0, 'loss' => 0];
            $makerData[$maker][$status]++;
        }
        $usageByMaker = [];
        $usageTable = [];
        foreach ($makerData as $maker => $counts) {
            $usageByMaker[] = ['maker' => $maker, 'on_budget' => $counts['on_budget'], 'near_loss' => $counts['near_loss'], 'loss' => $counts['loss']];
        }

        // Detailed Usage Table
        foreach ($makerUsageQuery as $item) {
            $limit = $this->calculateAdjustedRank($item->process_type, $item->limit_value, $item->unit_per_car, $item->pcs_per_unit);
            $usagePcs = \App\Models\InventoryModel\Material\InventoryProduct::calculatePcs($item->usage_qty, 0, $item->pcs_per_unit, $item->unit_name, 0, 0, 0, 1, $item->gross_coil);
            $gap = $limit - $usagePcs;
            $status = ($gap < 0) ? 'Loss' : (($gap < 50) ? 'Near Loss' : 'On Budget');
            
            $usageTable[] = [
                'part_no' => $item->part_no,
                'revision' => $item->revision,
                'supplier_name' => $item->maker,
                'rank_display' => ($item->rank_code ?? '-') . ' ' . number_format($limit),
                'out_trial' => $usagePcs,
                'gap' => $gap,
                'status' => $status
            ];
        }

        // Tables
        $balanceStatusTable = (clone $stockQuery)
            ->leftJoin('inv_m_revision as r', 'r.id', '=', 'p.revision_id')
            ->select('prod.part_no', 'r.code as revision', 'c.code as customer_code', 'm.name as model_name', 'p.current_stock_qty', 'p.min_stock')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                 $current = floatval($item->current_stock_qty);
                 $min = floatval($item->min_stock);
                 if ($min > 0) {
                     if ($current > $min * 3) $item->status = 'Over';
                     elseif ($current < ($min - 30)) $item->status = 'Critical';
                     elseif ($current < $min) $item->status = 'Warning';
                     else $item->status = 'Safe';
                 } else {
                     $item->status = 'Safe';
                 }
                 return $item;
            });

        $usageStatusTable = (clone $balanceStatusTable);

        $transactionHistory = (clone $recentTransQuery)
            ->leftJoin('inv_m_revision as r', 'r.id', '=', 'p.revision_id')
            ->select('prod.part_no', 'r.code as revision', 't.qty', 'p.pcs_per_unit', 'p.weight_kg', 'p.gross_coil', 'tc.code as category', 't.transaction_date', 't.created_at', 'u.name as unit_name', 'm.name as model_name', 'c.code as customer_code')
            ->orderByDesc('t.transaction_date')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $item->qty_pcs = \App\Models\InventoryModel\Material\InventoryProduct::calculatePcs($item->qty, $item->weight_kg, $item->pcs_per_unit, $item->unit_name, 0, 0, 0, 1, $item->gross_coil);
                return $item;
            });

        $initialModels = [];
        if (!empty($selectedModels)) {
            $initialModels = DB::table('models')->whereIn('id', $selectedModels)->select('id', 'name')->get();
        }

        $initialCustomers = [];
        if (!empty($selectedCustomers)) {
            $initialCustomers = DB::table('customers')->whereIn('id', $selectedCustomers)->select('id', 'code', 'name')->get();
        }

        $responseData = [
            'stats' => $stats,
            'charts' => [
                'stock_grouped' => $stockDataGrouped,
                'usage_model' => $usageByModel,
                'trendline' => $trendlineByCat,
                'maker' => $usageByMaker
            ],
            'tables' => [
                'balance' => $balanceStatusTable,
                'usage' => $usageTable,
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
        ];

        if ($request->ajax()) {
            return response()->json($responseData);
        }

        return view('inventory.material.dashboard', $responseData);
    }

    private function calculateAdjustedRank($processType, $limitValue, $unitPerCar, $pcsPerUnit)
    {
        $limitValue = (float)$limitValue;
        $unitPerCar = (float)($unitPerCar ?: 1);
        $pcsPerUnit = (float)($pcsPerUnit ?: 1);
        if ($processType === 'Draw' || $processType === 'Blank') {
            return $limitValue * $unitPerCar;
        } elseif ($processType === 'Full Progressive') {
            return $limitValue * $unitPerCar * $pcsPerUnit;
        }
        return $limitValue;
    }

    public function getModels(Request $request)
    {
        $term = $request->term;
        $customerIds = (array) $request->input('customer_id');
        $query = DB::table('models')->select(DB::raw('MIN(id) as id'), 'name as text')->groupBy('name');
        if (!empty($customerIds)) $query->whereIn('customer_id', $customerIds);
        if ($term) $query->where('name', 'like', '%' . $term . '%');
        $data = $query->orderBy('name')->simplePaginate(20);
        return response()->json(['results' => $data->items(), 'pagination' => ['more' => $data->hasMorePages()]]);
    }

    public function getCustomers(Request $request)
    {
        $term = $request->term;
        $query = DB::table('customers')->select('id', 'code as text');
        if ($term) $query->where(fn($q) => $q->where('code', 'like', '%' . $term . '%')->orWhere('name', 'like', '%' . $term . '%'));
        $data = $query->orderBy('code')->simplePaginate(20);
        return response()->json(['results' => $data->items(), 'pagination' => ['more' => $data->hasMorePages()]]);
    }

    public function getStatuses(Request $request, $type)
    {
        $statuses = ($type === 'balance') ? ['Critical', 'Warning', 'Over', 'Safe'] : ['Over', 'Safe'];
        $formatted = array_map(fn($item) => ['id' => $item, 'text' => $item], $statuses);
        return response()->json(['results' => $formatted]);
    }
}
