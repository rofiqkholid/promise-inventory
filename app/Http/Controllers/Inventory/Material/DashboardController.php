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
        $selectedProjectStatus = $request->input('project_status');

        $inCategories = TransactionCategory::where('effect', 1)->pluck('code');
        $outCategories = TransactionCategory::where('effect', -1)->pluck('code');

        // Helper for Status Filtering
        $applyStatusFilter = function($query, $statuses, $pcsSql) {
            if (empty($statuses)) return;
            $query->where(function($q) use ($statuses, $pcsSql) {
                 foreach ($statuses as $status) {
                     $status = strtolower($status);
                     if ($status === 'critical') {
                         $q->orWhere(function($w) use ($pcsSql) {
                             $w->where(DB::raw($pcsSql), '<', DB::raw('p.min_stock - 30'))
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
                     } elseif ($status === 'warning') {
                         $q->orWhere(function($w) use ($pcsSql) {
                             $w->where(DB::raw($pcsSql), '>=', DB::raw('p.min_stock - 30'))
                               ->where(DB::raw($pcsSql), '<', DB::raw('p.min_stock'))
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
                     } elseif ($status === 'over') {
                         $q->orWhere(function($w) use ($pcsSql) {
                             $w->where(DB::raw($pcsSql), '>', DB::raw('p.min_stock * 3'))
                               ->where('p.min_stock', '>', 0);
                         });
                     } elseif ($status === 'safe') {
                         $q->orWhere(function($w) use ($pcsSql) {
                             $w->where(function($inner) use ($pcsSql) {
                                 // Normal Safe: [min, min*3]
                                 $inner->where(function($std) use ($pcsSql) {
                                     $std->where(DB::raw($pcsSql), '>=', DB::raw('p.min_stock'))
                                         ->where(DB::raw($pcsSql), '<=', DB::raw('p.min_stock * 3'));
                                 })
                                 // Override Safe: [0, min] but is Regular/Oldstock
                                 ->orWhere(function($override) use ($pcsSql) {
                                     $override->where(DB::raw($pcsSql), '<', DB::raw('p.min_stock'))
                                              ->where(function($sq) {
                                                  $sq->where('ms.project_status', 'Regular')
                                                     ->orWhereIn('p.product_status', ['Oldstock OK', 'Oldstock NG']);
                                              });
                                 })
                                 // Edge case Safe: min <= 0
                                 ->orWhere('p.min_stock', '<=', 0)
                                 ->orWhereNull('p.min_stock');
                             });
                         });
                     }
                 }
            });
        };

        // Historical Stock Calculation Logic
        $lastDayOfMonth = date('Y-m-t', strtotime($monthYear . '-01'));
        $today = date('Y-m-d');
        $isHistorical = $lastDayOfMonth < $today;

        $applyProjectStatusFilter = function($query, $status) {
            if (empty($status)) return;
            if (is_array($status)) {
                $query->whereIn('ms.project_status', $status);
            } else {
                $query->where('ms.project_status', $status);
            }
        };

        // 1. Stock Query
        $stockQuery = DB::table('inv_t_product_detail as p')
            ->join('products as prod', 'prod.id', '=', 'p.product_id')
            ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
            ->leftJoin('customers as c', 'c.id', '=', 'prod.customer_id')
            ->leftJoin('inv_m_unit as u', 'u.id', '=', 'p.unit_id')
            ->leftJoin('inv_m_model_status as ms', 'ms.model_id', '=', 'p.model_id')
            ->where('p.is_active', 1);

        if ($isHistorical) {
            $netChangeSubquery = DB::table('inv_t_inventory_transaction as t')
                ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
                ->where('t.transaction_date', '>', $lastDayOfMonth)
                ->select('t.product_detail_id', DB::raw('SUM(t.qty * tc.effect) as net_change_qty'))
                ->groupBy('t.product_detail_id');
            
            $stockQuery->leftJoinSub($netChangeSubquery, 'adj', 'adj.product_detail_id', '=', 'p.id');
        }

        if (!empty($selectedModels)) $stockQuery->whereIn('p.model_id', $selectedModels);
        if (!empty($selectedCustomers)) $stockQuery->whereIn('prod.customer_id', $selectedCustomers);
        $applyProjectStatusFilter($stockQuery, $selectedProjectStatus);
        $histQtySql = $isHistorical ? "(p.current_stock_qty - ISNULL(adj.net_change_qty, 0))" : "p.current_stock_qty";
        $pcsSql = \App\Models\InventoryModel\Material\InventoryProduct::getPcsCalculationSql($histQtySql, 'p', 'u.name');
        $amountSql = \App\Models\InventoryModel\Material\InventoryProduct::getAmountCalculationSql($histQtySql, 'p', 'u.name');

        $applyStatusFilter($stockQuery, $selectedStatusBalance, $pcsSql);
        
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
        $applyProjectStatusFilter($queryTrans, $selectedProjectStatus);
        $applyStatusFilter($queryTrans, $selectedStatusBalance, $pcsSql);

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
        $allProducts = $stockQuery->select(
            'p.id',
            'm.name as model_name', 
            'c.code as customer_code', 
            DB::raw("{$histQtySql} as current_stock_qty"), 
            DB::raw("CAST({$pcsSql} AS INT) as current_stock_pcs"),
            'p.min_stock', 
            'p.product_status', 
            'ms.project_status',
            'p.pcs_per_unit',
            'p.weight_kg',
            'p.gross_coil',
            'u.name as unit_name'
        )->get();

        $stockDataGrouped = [];
        foreach ($allProducts as $prd) {
            $key = ($prd->model_name ?? 'N/A') . '|' . ($prd->customer_code ?? 'N/A');
            if (!isset($stockDataGrouped[$key])) {
                $stockDataGrouped[$key] = ['critical' => 0, 'warning' => 0, 'over' => 0, 'safe' => 0];
            }

            // Use pre-calculated PCS for accurate comparison
            $currentPcs = (int)$prd->current_stock_pcs;

            $status = \App\Models\InventoryModel\Material\InventoryProduct::calculateStockStatus(
                $currentPcs, $prd->min_stock, $prd->project_status ?: $prd->product_status
            );

            if (isset($stockDataGrouped[$key][$status])) {
                $stockDataGrouped[$key][$status]++;
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
                DB::raw("COUNT(DISTINCT CONCAT(p.id, '-', t.transaction_date, '-', tc.code)) as total")
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
                'm.name as model_name',
                'c.code as customer_code',
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
            ->groupBy('s.code', 'prod.part_no', 'rev.code', 'm.name', 'c.code', 'p.id', 'r.code', 'r.process_type', 'r.limit_value', 'p.unit_per_car', 'p.pcs_per_unit', 'p.gross_coil', 'u.name')
            ->get();

        $makerData = [];
        $usageTable = [];

        foreach ($makerUsageQuery as $item) {
            $limit = $this->calculateAdjustedRank($item->process_type, $item->limit_value, $item->unit_per_car, $item->pcs_per_unit);
            $usagePcs = \App\Models\InventoryModel\Material\InventoryProduct::calculatePcs($item->usage_qty, 0, $item->pcs_per_unit, $item->unit_name, 0, 0, 0, 1, $item->gross_coil);
            $gap = $limit - $usagePcs;
            
            $statusRaw = ($gap < 0) ? 'Loss' : (($gap < 50) ? 'Near Loss' : 'On Budget');
            
            // Apply Status Usage Filter
            if (!empty($selectedStatusUsage) && !in_array($statusRaw, $selectedStatusUsage)) {
                continue;
            }

            // For Chart
            $statusKey = strtolower(str_replace(' ', '_', $statusRaw));
            $maker = $item->maker ?: 'Unknown';
            if (!isset($makerData[$maker])) $makerData[$maker] = ['on_budget' => 0, 'near_loss' => 0, 'loss' => 0];
            $makerData[$maker][$statusKey]++;

            // For Table
            $usageTable[] = [
                'part_no' => $item->part_no,
                'revision' => $item->revision,
                'model_name' => $item->model_name,
                'customer_code' => $item->customer_code,
                'supplier_name' => $item->maker,
                'rank_display' => ($item->rank_code ?? '-') . ' ' . number_format($limit),
                'out_trial' => $usagePcs,
                'gap' => $gap,
                'status' => $statusRaw
            ];
        }

        $usageByMaker = [];
        foreach ($makerData as $maker => $counts) {
            $usageByMaker[] = ['maker' => $maker, 'on_budget' => $counts['on_budget'], 'near_loss' => $counts['near_loss'], 'loss' => $counts['loss']];
        }

        // Sort Usage Table by Status (Loss -> Near Loss -> On Budget)
        usort($usageTable, function($a, $b) {
            $order = ['Loss' => 1, 'Near Loss' => 2, 'On Budget' => 3];
            return ($order[$a['status']] ?? 99) <=> ($order[$b['status']] ?? 99);
        });

        // Tables
        $balanceStatusTable = (clone $stockQuery)
            ->leftJoin('inv_m_revision as r', 'r.id', '=', 'p.revision_id')
            ->select(
                'p.id', 'prod.part_no', 'r.code as revision', 'c.code as customer_code', 
                'm.name as model_name', 
                DB::raw("{$histQtySql} as current_stock_qty"), 
                DB::raw("CAST({$pcsSql} AS INT) as current_stock_pcs"), 
                'p.min_stock',
                'p.pcs_per_unit', 'p.weight_kg', 'p.gross_coil', 'u.name as unit_name',
                'ms.project_status', 'p.product_status', 'p.action_status', 'p.action_remark'
            )
            ->get()
            ->map(function ($item) {
                  $currentPcs = (int)$item->current_stock_pcs;
                 
                  $status = \App\Models\InventoryModel\Material\InventoryProduct::calculateStockStatus(
                     $currentPcs, $item->min_stock, $item->project_status ?: $item->product_status
                  );

                 $item->status = ucfirst($status);
                 return $item;
            })
            ->filter(function($item) {
                // Only show Critical and Warning in the Balance Warnings table
                return in_array($item->status, ['Critical', 'Warning']);
            })
            ->values()
            ->take(15);

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
        $statuses = ($type === 'balance') ? ['Critical', 'Warning', 'Over', 'Safe'] : ['Loss', 'Near Loss', 'On Budget'];
        $formatted = array_map(fn($item) => ['id' => $item, 'text' => $item], $statuses);
        return response()->json(['results' => $formatted]);
    }

    public function chartDrilldown(Request $request)
    {
        $chartType  = $request->input('chart');
        $label      = $request->input('label');
        $monthYear  = $request->input('month_year', date('Y-m'));
        $statusFilter = $request->input('status');
        $search     = $request->input('search');
        $pageSize   = $request->input('pageSize', 10);
        $page       = $request->input('page', 1);
        $offset     = ($page - 1) * $pageSize;
        $selectedProjectStatus = $request->input('project_status');

        $outCategories = \App\Models\InventoryModel\Material\TransactionCategory::where('effect', -1)->pluck('code');

        $lastDayOfMonth = date('Y-m-t', strtotime($monthYear . '-01'));
        $today = date('Y-m-d');
        $isHistorical = $lastDayOfMonth < $today;

        $applyProjectStatusFilter = function($query, $status) {
            if (empty($status)) return;
            if (is_array($status)) {
                $query->whereIn('ms.project_status', $status);
            } else {
                $query->where('ms.project_status', $status);
            }
        };

        $baseProduct = DB::table('inv_t_product_detail as p')
            ->join('products as prod', 'prod.id', '=', 'p.product_id')
            ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
            ->leftJoin('customers as c', 'c.id', '=', 'prod.customer_id')
            ->leftJoin('inv_m_unit as u', 'u.id', '=', 'p.unit_id')
            ->leftJoin('inv_m_revision as rev', 'rev.id', '=', 'p.revision_id')
            ->leftJoin('inv_m_model_status as ms', 'ms.model_id', '=', 'p.model_id')
            ->where('p.is_active', 1);

        if ($isHistorical) {
            $netChangeSubquery = DB::table('inv_t_inventory_transaction as t')
                ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
                ->where('t.transaction_date', '>', $lastDayOfMonth)
                ->select('t.product_detail_id', DB::raw('SUM(t.qty * tc.effect) as net_change_qty'))
                ->groupBy('t.product_detail_id');
            $baseProduct->leftJoinSub($netChangeSubquery, 'adj', 'adj.product_detail_id', '=', 'p.id');
        }

        $histQtySql = $isHistorical ? "(p.current_stock_qty - ISNULL(adj.net_change_qty, 0))" : "p.current_stock_qty";
        $pcsSql = \App\Models\InventoryModel\Material\InventoryProduct::getPcsCalculationSql($histQtySql, 'p', 'u.name');

        $baseTrans = DB::table('inv_t_inventory_transaction as t')
            ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
            ->join('inv_t_product_detail as p', 'p.id', '=', 't.product_detail_id')
            ->join('products as prod', 'prod.id', '=', 'p.product_id')
            ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
            ->leftJoin('customers as c', 'c.id', '=', 'prod.customer_id')
            ->leftJoin('inv_m_unit as u', 'u.id', '=', 'p.unit_id')
            ->leftJoin('inv_m_revision as rev', 'rev.id', '=', 'p.revision_id')
            ->leftJoin('inv_m_supplier as s', 's.id', '=', 't.supplier_id')
            ->leftJoin('inv_m_model_status as ms', 'ms.model_id', '=', 'p.model_id');

        $result = [];
        $title  = '';
        $total  = 0;

        if ($chartType === 'stock') {
            $parts = explode('|', $label);
            $modelName = $parts[0] ?? '';
            $custCode  = $parts[1] ?? '';

            $query = (clone $baseProduct)
                ->where(DB::raw('ISNULL(m.name, \'N/A\')'), $modelName)
                ->where(DB::raw('ISNULL(c.code, \'N/A\')'), $custCode);
            
            $applyProjectStatusFilter($query, $selectedProjectStatus);

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('prod.part_no', 'like', "%{$search}%")
                      ->orWhere('m.name', 'like', "%{$search}%");
                });
            }

            $items = $query->select(
                    'p.id', 'prod.part_no', 'rev.code as revision',
                    'm.name as model_name', 'c.code as customer_code',
                    'p.min_stock',
                    'u.name as unit_name', 'p.pcs_per_unit',
                    'p.weight_kg', 'p.gross_coil',
                    'p.product_status', 'ms.project_status', 'p.action_status', 'p.action_remark',
                    DB::raw("{$histQtySql} as current_stock_qty"), 
                    DB::raw("CAST({$pcsSql} AS INT) as current_stock_pcs")
                )
                ->orderBy('prod.part_no')
                ->get();

            $statusText = $items->first()->project_status ?? '';
            $projTag = $statusText ? ($statusText === 'Regular' ? ' [Regular]' : ' [Project]') : '';
            $title = "Stock Detail — {$modelName} / {$custCode}{$projTag}";

            $processed = [];
            foreach ($items as $row) {
                // Use the calculated historical PCS
                $currentPcs = (int)$row->current_stock_pcs;

                $statusRaw = \App\Models\InventoryModel\Material\InventoryProduct::calculateStockStatus(
                    $currentPcs, $row->min_stock, $row->project_status ?: $row->product_status
                );

                if ($statusFilter && strcasecmp($statusRaw, $statusFilter) !== 0) {
                    continue;
                }

                $processed[] = [
                    'id'            => \App\Models\InventoryModel\Material\InventoryProduct::encodeHash($row->id),
                    'part_no'       => $row->part_no . ($row->revision ? '-' . $row->revision : ''),
                    'model'         => $row->model_name ?? '-',
                    'customer'      => $row->customer_code ?? '-',
                    'stock'         => number_format($currentPcs) . ' PCS',
                    'min_stock'     => number_format($row->min_stock) . ' PCS',
                    'unit'          => $row->unit_name ?? '-',
                    'status'        => ucfirst($statusRaw),
                    'action_status' => $row->action_status,
                    'action_remark' => $row->action_remark,
                ];
            }

            // Sort by Status Priority (Critical -> Warning -> Over -> Safe) then Part No
            usort($processed, function($a, $b) {
                $order = ['Critical' => 1, 'Warning' => 2, 'Over' => 3, 'Safe' => 4];
                $oA = $order[$a['status']] ?? 99;
                $oB = $order[$b['status']] ?? 99;
                if ($oA === $oB) return strcasecmp($a['part_no'], $b['part_no']);
                return $oA <=> $oB;
            });

            $total = count($processed);
            $result = array_slice($processed, $offset, $pageSize);

        } elseif ($chartType === 'trendline') {
            $monthName = $label;
            $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $monthIdx = array_search($monthName, $monthNames);
            $monthNum = ($monthIdx !== false) ? $monthIdx + 1 : 1;
            $year = substr($monthYear, 0, 4) ?: date('Y');
            
            $title = "Transaction Detail — {$monthName} {$year} ({$statusFilter})";

            $query = (clone $baseTrans)
                ->whereYear('t.transaction_date', $year)
                ->whereMonth('t.transaction_date', $monthNum);
            
            $applyProjectStatusFilter($query, $selectedProjectStatus);

            if ($statusFilter) {
                // Map display label back to OUT-EVENT etc. if needed
                $catMap = ['Event' => 'OUT-EVENT', 'PP' => 'OUT-PP', 'Trial' => 'OUT-TRIAL', 'In' => 'IN'];
                $dbStatus = $catMap[$statusFilter] ?? $statusFilter;
                $query->where('tc.code', $dbStatus);
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('prod.part_no', 'like', "%{$search}%");
                });
            }

            $totalQuery = clone $query;
            $total = $totalQuery->distinct()->count(DB::raw("CONCAT(prod.part_no, ISNULL(rev.code,''), tc.code, CAST(t.transaction_date AS NVARCHAR))"));
            
            $itemsQuery = clone $query;
            $items = $itemsQuery->leftJoin('inv_m_coil_center as cc', 'cc.id', '=', 't.coil_center_id')
                ->leftJoin('inv_m_supplier as dest', 'dest.id', '=', 't.destination_id')
                ->select(
                    'prod.part_no', 'rev.code as revision',
                    'tc.code as category',
                    DB::raw('SUM(t.qty) as qty'),
                    'u.name as unit_name',
                    DB::raw('SUM(t.qty * ISNULL(p.pcs_per_unit, 1)) as qty_pcs'),
                    't.transaction_date',
                    DB::raw("LTRIM(RTRIM(ISNULL(cc.code, '') + ' ' + ISNULL(s.code, '') + ' ' + CASE WHEN dest.code IS NOT NULL THEN '(To: ' + dest.code + ')' ELSE '' END)) as origin_destination")
                )
                ->groupBy('prod.part_no', 'rev.code', 'tc.code', 't.transaction_date', 'cc.code', 's.code', 'dest.code', 'u.name')
                ->orderBy('t.transaction_date', 'desc')
                ->offset($offset)->limit($pageSize)
                ->get();

            foreach ($items as $row) {
                $result[] = [
                    'part_no'   => $row->part_no . ($row->revision ? '-' . $row->revision : ''),
                    'category'  => $row->category,
                    'qty'       => (float)$row->qty,
                    'unit'      => $row->unit_name,
                    'qty_pcs'   => (float)$row->qty_pcs,
                    'date'      => $row->transaction_date,
                    'origin_destination' => $row->origin_destination ?: '-',
                ];
            }

        } elseif ($chartType === 'usage_model') {
            $parts = explode('|', $label);
            $modelName = $parts[0] ?? '';
            $custCode  = $parts[1] ?? '';

            $query = (clone $baseTrans)
                ->where('t.transaction_date', 'like', "{$monthYear}%")
                ->whereIn('tc.code', $outCategories)
                ->where(DB::raw('ISNULL(m.name, \'N/A\')'), $modelName)
                ->where(DB::raw('ISNULL(c.code, \'N/A\')'), $custCode);
            
            $applyProjectStatusFilter($query, $selectedProjectStatus);

            if ($statusFilter) $query->where('tc.code', $statusFilter);
            if ($search) $query->where('prod.part_no', 'like', "%{$search}%");

            $total = (clone $query)->distinct()->count(DB::raw("CONCAT(prod.part_no, ISNULL(rev.code,''), tc.code, CAST(t.transaction_date AS NVARCHAR))"));
            
            $items = $query->select(
                    'prod.part_no', 'rev.code as revision',
                    'tc.code as category',
                    DB::raw('SUM(t.qty) as qty'),
                    'u.name as unit_name',
                    DB::raw('SUM(t.qty * ISNULL(p.pcs_per_unit, 1)) as qty_pcs'),
                    't.transaction_date',
                    'ms.project_status'
                )
                ->groupBy('prod.part_no', 'rev.code', 'tc.code', 't.transaction_date', 'u.name', 'ms.project_status')
                ->orderBy('t.transaction_date', 'desc')
                ->offset($offset)->limit($pageSize)
                ->get();

            $statusText = $items->first()->project_status ?? '';
            $projTag = $statusText ? ($statusText === 'Regular' ? ' [Regular]' : ' [Project]') : '';
            $title = "Usage Detail — {$modelName} / {$custCode}{$projTag}";

            foreach ($items as $row) {
                $result[] = [
                    'part_no'   => $row->part_no . ($row->revision ? '-' . $row->revision : ''),
                    'category'  => $row->category,
                    'qty'       => (float)$row->qty,
                    'unit'      => $row->unit_name,
                    'qty_pcs'   => (float)$row->qty_pcs,
                    'date'      => $row->transaction_date,
                ];
            }

        } elseif ($chartType === 'maker') {
            $title = "Maker Detail — {$label}";

            $query = (clone $baseTrans)
                ->where('t.transaction_date', 'like', "{$monthYear}%")
                ->where('tc.code', 'OUT-TRIAL')
                ->where('s.code', $label);
            
            $applyProjectStatusFilter($query, $selectedProjectStatus);

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('prod.part_no', 'like', "%{$search}%")
                      ->orWhere('m.name', 'like', "%{$search}%");
                });
            }

            $items = $query->leftJoin('inv_m_rank as r', 'r.id', '=', 'p.rank_id')
                ->select(
                    'prod.part_no', 'rev.code as revision',
                    'm.name as model_name', 'c.code as customer_code',
                    's.code as maker',
                    'r.code as rank_code', 'r.process_type', 'r.limit_value',
                    'p.unit_per_car', 'p.pcs_per_unit', 'p.gross_coil',
                    'u.name as unit_name',
                    DB::raw('SUM(t.qty) as usage_qty')
                )
                ->groupBy(
                    'prod.part_no', 'rev.code', 'm.name', 'c.code',
                    's.code', 'r.code', 'r.process_type', 'r.limit_value',
                    'p.unit_per_car', 'p.pcs_per_unit', 'p.gross_coil', 'u.name'
                )
                ->get();

            $processed = [];
            foreach ($items as $row) {
                $limit    = $this->calculateAdjustedRank($row->process_type, $row->limit_value, $row->unit_per_car, $row->pcs_per_unit);
                $usagePcs = \App\Models\InventoryModel\Material\InventoryProduct::calculatePcs($row->usage_qty, 0, $row->pcs_per_unit, $row->unit_name, 0, 0, 0, 1, $row->gross_coil);
                $gap      = $limit - $usagePcs;
                $status   = ($gap < 0) ? 'Loss' : (($gap < 50) ? 'Near Loss' : 'On Budget');

                if ($statusFilter && $status !== $statusFilter) continue;

                $processed[] = [
                    'part_no'   => $row->part_no . ($row->revision ? '-' . $row->revision : ''),
                    'model'     => $row->model_name ?? '-',
                    'customer'  => $row->customer_code ?? '-',
                    'qty'       => (float)$row->usage_qty,
                    'unit'      => $row->unit_name,
                    'qty_pcs'   => (float)$usagePcs,
                    'gap'       => (float)$gap,
                    'status'    => $status,
                ];
            }

            // Sort by Status Priority (Loss -> Near Loss -> On Budget)
            usort($processed, function($a, $b) {
                $order = ['Loss' => 1, 'Near Loss' => 2, 'On Budget' => 3];
                $oA = $order[$a['status']] ?? 99;
                $oB = $order[$b['status']] ?? 99;
                if ($oA === $oB) return strcasecmp($a['part_no'], $b['part_no']);
                return $oA <=> $oB;
            });

            $total = count($processed);
            $result = array_slice($processed, $offset, $pageSize);
        }

        return response()->json([
            'title'  => $title,
            'chart'  => $chartType,
            'data'   => $result,
            'total'  => $total
        ]);
    }
}
