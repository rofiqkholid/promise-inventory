<?php

namespace App\Http\Controllers\Inventory\Tool;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\Tool\TolTool;
use App\Models\InventoryModel\Tool\TolCategory;
use App\Models\InventoryModel\Tool\TolFastStock;
use App\Models\InventoryModel\Tool\TolSlowBatch;
use App\Models\InventoryModel\Tool\TolTransaction;
use App\Models\InventoryModel\Tool\TolLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ToolDashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Parse Period/Time Filter (Month Year and Accumulate Mode)
        $monthYear = $request->input('month_year', date('Y-m'));
        $accumulate = $request->input('accumulate', 'ytd');
        
        if ($accumulate === 'single') {
            $startDate = Carbon::parse($monthYear . '-01')->startOfMonth()->startOfDay();
            $endDate = Carbon::parse($monthYear . '-01')->endOfMonth()->endOfDay();
        } elseif ($accumulate === 'ytd') {
            $startDate = Carbon::parse($monthYear . '-01')->startOfYear()->startOfDay();
            $endDate = Carbon::parse($monthYear . '-01')->endOfMonth()->endOfDay();
        } else { // 'all'
            $startDate = null;
            $endDate = Carbon::parse($monthYear . '-01')->endOfMonth()->endOfDay();
        }

        // Retrieve Fast Tools & Reconstruct historical stock at $endDate
        $fastTools = TolTool::with(['category', 'fastStock.location'])
            ->whereHas('category', fn($q) => $q->where('moving_type', 'fast'))
            ->where('is_active', true)
            ->get();

        foreach ($fastTools as $tool) {
            $qtyAfter = TolTransaction::leftJoin('tol_m_locations as l', 'l.id', '=', 'tol_t_transactions.to_location_id')
                ->where('tol_t_transactions.tool_id', $tool->id)
                ->where('tol_t_transactions.transacted_at', '>', $endDate)
                ->where(function($q) {
                    $q->where('tol_t_transactions.transaction_type', 'in')
                      ->orWhereIn('l.category', ['scrap', 'lost']);
                })
                ->sum('tol_t_transactions.qty') ?? 0;
            $tool->historical_qty = $tool->total_qty - $qtyAfter;
        }

        // Retrieve Slow Tools & Reconstruct historical stock at $endDate
        $slowTools = TolTool::with(['category', 'fastStock.location'])
            ->whereHas('category', fn($q) => $q->where('moving_type', 'slow'))
            ->where('is_active', true)
            ->get();

        foreach ($slowTools as $tool) {
            $qtyAfter = TolTransaction::leftJoin('tol_m_locations as l', 'l.id', '=', 'tol_t_transactions.to_location_id')
                ->where('tol_t_transactions.tool_id', $tool->id)
                ->where('tol_t_transactions.transacted_at', '>', $endDate)
                ->where(function($q) {
                    $q->where('tol_t_transactions.transaction_type', 'in')
                      ->orWhereIn('l.category', ['scrap', 'lost']);
                })
                ->sum('tol_t_transactions.qty') ?? 0;
            $tool->historical_qty = $tool->total_qty - $qtyAfter;
        }

        // 2. Fetch Card KPI Data dynamically based on the filtered end date
        // Card 1: Total Value (Fast Stock Value + Slow Batch Value as of $endDate)
        $fastStockValue = 0;
        $fastStockQty = 0;
        foreach ($fastTools as $tool) {
            $fastStockQty += $tool->historical_qty;
            $fastStockValue += ($tool->historical_qty * ($tool->price_per_unit ?? 0));
        }
        
        $slowBatchesAtEndDate = TolSlowBatch::where('purchase_date', '<=', $endDate)
            ->where(function($q) use ($endDate) {
                $q->where('status', 'active')
                  ->orWhere('updated_at', '>', $endDate);
            })
            ->get();
        $slowStockQty = $slowBatchesAtEndDate->sum('qty_current');
        $slowBatchValue = $slowBatchesAtEndDate->sum('current_value');
        
        $totalValue = $fastStockValue + $slowBatchValue;

        // Card 2: Total Stock (Fast Stock Qty + Active Slow Batch Current Qty as of $endDate)
        $totalStock = $fastStockQty + $slowStockQty;

        // Card 3: Total In (Incoming transactions in date range)
        $fastInQuery = TolTransaction::where('transaction_type', 'in')
            ->where('transacted_at', '<=', $endDate);
        if ($startDate) {
            $fastInQuery->where('transacted_at', '>=', $startDate);
        }
        $fastIn = $fastInQuery->sum('qty');
        
        $slowInQuery = TolSlowBatch::where('purchase_date', '<=', $endDate);
        if ($startDate) {
            $slowInQuery->where('purchase_date', '>=', $startDate);
        }
        $slowIn = $slowInQuery->sum('qty_purchased');
        
        $totalIn = $fastIn + $slowIn;

        // Card 4: Total Out (Outgoing transactions in date range)
        $fastOutQuery = TolTransaction::where('transaction_type', 'out')
            ->where('transacted_at', '<=', $endDate);
        if ($startDate) {
            $fastOutQuery->where('transacted_at', '>=', $startDate);
        }
        $fastOut = abs($fastOutQuery->sum('qty'));

        $slowOutQuery = TolSlowBatch::whereIn('status', ['nok', 'retired'])
            ->where('updated_at', '<=', $endDate);
        if ($startDate) {
            $slowOutQuery->where('updated_at', '>=', $startDate);
        }
        $slowOut = $slowOutQuery->sum('qty_purchased');

        $totalOut = $fastOut + $slowOut;

        // Card 5 & 6: Moving Breakdown
        $totalFastMoving = $fastStockQty;
        $totalSlowMoving = $slowStockQty;

        // 3. Stock Status - Group by Category
        // Fast Stock Status
        $groupedStockStatusFast = [];
        $allFastCategories = TolCategory::where('is_active', true)
            ->where('moving_type', 'fast')
            ->pluck('name')
            ->toArray();
        foreach ($allFastCategories as $catName) {
            $groupedStockStatusFast[$catName] = [
                'critical' => 0, 'warning' => 0, 'over' => 0, 'safe' => 0, 'total' => 0,
                'need_action' => 'Stock level healthy. No action required.'
            ];
        }
        foreach ($fastTools as $tool) {
            $catName = $tool->category?->name ?? 'Uncategorized';
            if (!isset($groupedStockStatusFast[$catName])) {
                $groupedStockStatusFast[$catName] = [
                    'critical' => 0, 'warning' => 0, 'over' => 0, 'safe' => 0, 'total' => 0,
                    'need_action' => 'Stock level healthy. No action required.'
                ];
            }
            $qty = $tool->historical_qty;
            $qtyMin = $tool->qty_min ?? 0;
            $qtyMax = $tool->qty_max ?? 0;

            if ($qty < $qtyMin) {
                $status = 'critical';
            } elseif ($qty == $qtyMin) {
                $status = 'warning';
            } elseif ($qtyMax > 0 && $qty > $qtyMax) {
                $status = 'over';
            } else {
                $status = 'safe';
            }
            $groupedStockStatusFast[$catName][$status]++;
            $groupedStockStatusFast[$catName]['total']++;
        }
        foreach ($groupedStockStatusFast as $catName => &$data) {
            if ($data['critical'] > 0) {
                $data['need_action'] = "🚨 CRITICAL REORDER REQUIRED! {$data['critical']} items in {$catName} are depleted. Place procurement order immediately.";
            } elseif ($data['warning'] > 0) {
                $data['need_action'] = "⚠️ SAFETY LIMIT BREACHED. {$data['warning']} items in {$catName} are below safety stock. Schedule replenishment.";
            } elseif ($data['over'] > 0) {
                $data['need_action'] = "📦 OVERSTOCK NOTICE. {$data['over']} items in {$catName} exceed maximum limits. Pause purchasing.";
            } elseif ($data['total'] > 0) {
                $data['need_action'] = "✅ NOMINAL STOCK. All {$data['total']} items in {$catName} are at healthy and safe levels.";
            } else {
                $data['need_action'] = "ℹ️ NO STOCK RECORDS. No items registered in this category.";
            }
        }
        unset($data);

        // Slow Stock Status
        $groupedStockStatusSlow = [];
        $allSlowCategories = TolCategory::where('is_active', true)
            ->where('moving_type', 'slow')
            ->pluck('name')
            ->toArray();
        foreach ($allSlowCategories as $catName) {
            $groupedStockStatusSlow[$catName] = [
                'retired' => 0, 'warning' => 0, 'still_good' => 0, 'good' => 0, 'ok' => 0, 'total' => 0,
                'need_action' => 'Stock level healthy. No action required.'
            ];
        }
        $slowBatches = TolSlowBatch::with(['tool.category'])
            ->where('purchase_date', '<=', $endDate)
            ->where(function($q) use ($endDate) {
                $q->where('status', 'active')
                  ->orWhere('updated_at', '>', $endDate);
            })
            ->get();
        foreach ($slowBatches as $batch) {
            $catName = $batch->tool?->category?->name ?? 'Uncategorized';
            if (!isset($groupedStockStatusSlow[$catName])) {
                $groupedStockStatusSlow[$catName] = [
                    'retired' => 0, 'warning' => 0, 'still_good' => 0, 'good' => 0, 'ok' => 0, 'total' => 0,
                    'need_action' => 'Stock level healthy. No action required.'
                ];
            }
            $rate = (int)$batch->physical_rate;
            if ($rate === 100) {
                $status = 'ok';
            } elseif ($rate === 75) {
                $status = 'good';
            } elseif ($rate === 50) {
                $status = 'still_good';
            } elseif ($rate === 25 || $rate === 20) {
                $status = 'warning';
            } else {
                $status = 'retired';
            }
            $groupedStockStatusSlow[$catName][$status]++;
            $groupedStockStatusSlow[$catName]['total']++;
        }
        foreach ($groupedStockStatusSlow as $catName => &$data) {
            if ($data['retired'] > 0) {
                $data['need_action'] = "🚨 RETIRED ASSETS DETECTED! {$data['retired']} items in {$catName} are retired. Consider replacement.";
            } elseif ($data['warning'] > 0) {
                $data['need_action'] = "⚠️ CONDITION WARNING. {$data['warning']} items in {$catName} are at warning condition limit.";
            } elseif ($data['total'] > 0) {
                $data['need_action'] = "✅ STABLE ASSETS. All {$data['total']} items in {$catName} are in usable condition.";
            } else {
                $data['need_action'] = "ℹ️ NO RECORDS. No items registered in this category.";
            }
        }
        unset($data);

        // 4. Balance Warnings Lists
        // Fast Balance Warnings
        $balanceWarningsFast = [];
        foreach ($fastTools as $tool) {
            $qty = $tool->historical_qty;
            $qtyMin = $tool->qty_min ?? 0;
            $qtyMax = $tool->qty_max ?? 0;

            if ($qty <= $qtyMin) {
                $status = $qty < $qtyMin ? 'Critical' : 'Warning';
                $activeStocks = $tool->fastStock->filter(fn($fs) => $fs->current_qty > 0);
                if ($activeStocks->isEmpty()) {
                    $locStr = $tool->location?->code ?? '-';
                } else {
                    $locStr = $activeStocks->map(fn($fs) => $fs->location?->code ?? 'Unknown')->implode(', ');
                }

                $balanceWarningsFast[] = [
                    'id' => $tool->id, 'tool_name' => $tool->name, 'brand' => $tool->brand ?? '-', 'spec_code' => $tool->spec_code ?? '-',
                    'category' => $tool->category?->name ?? 'Uncategorized', 'location' => $locStr, 'current_qty' => $qty, 'qty_min' => $qtyMin,
                    'limit_stock' => $qtyMin, 'status' => $status, 'action' => $status === 'Critical' ? 'Restock Immediately' : 'Schedule Restock',
                    'action_status' => $tool->action_status, 'action_remark' => $tool->action_remark
                ];
            }
        }
        usort($balanceWarningsFast, function ($a, $b) {
            if ($a['status'] === $b['status']) return $a['current_qty'] <=> $b['current_qty'];
            return $a['status'] === 'Critical' ? -1 : 1;
        });

        // Slow Balance Warnings
        $balanceWarningsSlow = [];
        foreach ($slowTools as $tool) {
            $qty = $tool->historical_qty;
            $qtyMin = $tool->qty_min ?? 0;
            $qtyMax = $tool->qty_max ?? 0;

            if ($qty <= $qtyMin) {
                $status = $qty < $qtyMin ? 'Critical' : 'Warning';
                $activeStocks = $tool->fastStock->filter(fn($fs) => $fs->current_qty > 0);
                if ($activeStocks->isEmpty()) {
                    $locStr = $tool->location?->code ?? '-';
                } else {
                    $locStr = $activeStocks->map(fn($fs) => $fs->location?->code ?? 'Unknown')->implode(', ');
                }

                $balanceWarningsSlow[] = [
                    'id' => $tool->id, 'tool_name' => $tool->name, 'brand' => $tool->brand ?? '-', 'spec_code' => $tool->spec_code ?? '-',
                    'category' => $tool->category?->name ?? 'Uncategorized', 'location' => $locStr, 'current_qty' => $qty, 'qty_min' => $qtyMin,
                    'limit_stock' => $qtyMin, 'status' => $status, 'action' => $status === 'Critical' ? 'Restock Immediately' : 'Schedule Restock',
                    'action_status' => $tool->action_status, 'action_remark' => $tool->action_remark
                ];
            }
        }
        usort($balanceWarningsSlow, function ($a, $b) {
            if ($a['status'] === $b['status']) return $a['current_qty'] <=> $b['current_qty'];
            return $a['status'] === 'Critical' ? -1 : 1;
        });

        // 5. Transaction Trend (IN vs OUT) Over Time - Grouped by Month for selected Year
        $trendYear = substr($monthYear, 0, 4) ?: date('Y');
        
        // Fast IN grouped by month
        $fastInRaw = TolTransaction::where('transaction_type', 'in')
            ->whereYear('transacted_at', $trendYear)
            ->select(DB::raw('MONTH(transacted_at) as month_num'), DB::raw('SUM(qty) as total'))
            ->groupBy(DB::raw('MONTH(transacted_at)'))
            ->pluck('total', 'month_num')
            ->toArray();
            
        // Slow IN grouped by month
        $slowInRaw = TolSlowBatch::whereYear('purchase_date', $trendYear)
            ->select(DB::raw('MONTH(purchase_date) as month_num'), DB::raw('SUM(qty_purchased) as total'))
            ->groupBy(DB::raw('MONTH(purchase_date)'))
            ->pluck('total', 'month_num')
            ->toArray();
            
        // Fast OUT grouped by month
        $fastOutRaw = TolTransaction::where('transaction_type', 'out')
            ->whereYear('transacted_at', $trendYear)
            ->select(DB::raw('MONTH(transacted_at) as month_num'), DB::raw('SUM(qty) as total'))
            ->groupBy(DB::raw('MONTH(transacted_at)'))
            ->pluck('total', 'month_num')
            ->toArray();
            
        // Slow OUT grouped by month
        $slowOutRaw = TolSlowBatch::whereIn('status', ['nok', 'retired'])
            ->whereYear('updated_at', $trendYear)
            ->select(DB::raw('MONTH(updated_at) as month_num'), DB::raw('SUM(qty_purchased) as total'))
            ->groupBy(DB::raw('MONTH(updated_at)'))
            ->pluck('total', 'month_num')
            ->toArray();

        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $ins = [];
        $outs = [];
        for ($m = 1; $m <= 12; $m++) {
            $fin = $fastInRaw[$m] ?? 0;
            $sin = $slowInRaw[$m] ?? 0;
            $ins[] = $fin + $sin;
            
            $fout = abs($fastOutRaw[$m] ?? 0);
            $sout = $slowOutRaw[$m] ?? 0;
            $outs[] = $fout + $sout;
        }

        $trendData = [
            'labels' => $labels,
            'ins' => $ins,
            'outs' => $outs
        ];

        // 6. Pareto Diagram berdasarkan Transaksi OUT
        $outboundByToolQuery = DB::table('tol_t_transactions as t')
            ->join('tol_m_tools as tl', 'tl.id', '=', 't.tool_id')
            ->where('t.transaction_type', 'out')
            ->where('t.transacted_at', '<=', $endDate);
        if ($startDate) {
            $outboundByToolQuery->where('t.transacted_at', '>=', $startDate);
        }
        $outboundByTool = $outboundByToolQuery->select('tl.name as tool_name', DB::raw('SUM(t.qty) as total_qty'))
            ->groupBy('tl.name')
            ->orderBy('total_qty', 'desc')
            ->get();

        // Also mix in slow-moving NOK batches as outbound consumption
        $slowOutboundByToolQuery = DB::table('tol_t_slow_batches as b')
            ->join('tol_m_tools as tl', 'tl.id', '=', 'b.tool_id')
            ->whereIn('b.status', ['nok', 'retired'])
            ->where('b.updated_at', '<=', $endDate);
        if ($startDate) {
            $slowOutboundByToolQuery->where('b.updated_at', '>=', $startDate);
        }
        $slowOutboundByTool = $slowOutboundByToolQuery->select('tl.name as tool_name', DB::raw('SUM(b.qty_purchased) as total_qty'))
            ->groupBy('tl.name')
            ->get();

        // Merge the two sources
        $paretoRaw = [];
        foreach ($outboundByTool as $row) {
            $paretoRaw[$row->tool_name] = abs((int)$row->total_qty);
        }
        foreach ($slowOutboundByTool as $row) {
            if (isset($paretoRaw[$row->tool_name])) {
                $paretoRaw[$row->tool_name] += abs((int)$row->total_qty);
            } else {
                $paretoRaw[$row->tool_name] = abs((int)$row->total_qty);
            }
        }

        arsort($paretoRaw); // Sort descending

        $paretoLabels = [];
        $paretoQuantities = [];
        $paretoCumulative = [];
        $paretoTotalSum = array_sum($paretoRaw);
        $runningSum = 0;

        foreach ($paretoRaw as $toolName => $qty) {
            $paretoLabels[] = strlen($toolName) > 20 ? substr($toolName, 0, 18) . '..' : $toolName;
            $paretoQuantities[] = $qty;
            $runningSum += $qty;
            $paretoCumulative[] = $paretoTotalSum > 0 ? round(($runningSum / $paretoTotalSum) * 100, 1) : 0;
        }

        $paretoData = [
            'labels' => $paretoLabels,
            'quantities' => $paretoQuantities,
            'cumulative' => $paretoCumulative
        ];

        // 7. Recent Activity Timeline (Fast Transactions only)
        $activities = [];

        // Fast moving transactions (IN & OUT only)
        $fastTrans = TolTransaction::with(['tool', 'location', 'destination'])
            ->whereIn('transaction_type', ['in', 'out'])
            ->latest('transacted_at')
            ->take(10)
            ->get();
        
        foreach ($fastTrans as $t) {
            $type = $t->transaction_type === 'in' ? 'IN' : 'OUT';
            $color = $t->transaction_type === 'in' ? 'emerald' : 'rose';
            $icon = $t->transaction_type === 'in' ? 'fa-solid fa-arrow-trend-up' : 'fa-solid fa-arrow-trend-down';

            $activities[] = [
                'type' => $type,
                'tool_name' => $t->tool?->name ?? '-',
                'spec_code' => $t->tool?->spec_code ?? '-',
                'qty' => $t->qty,
                'uom' => $t->tool?->uom ?? 'PCS',
                'icon' => $icon,
                'color' => $color,
                'timestamp' => $t->transacted_at ? $t->transacted_at->toIso8601String() : $t->created_at->toIso8601String(),
                'display_time' => $t->transacted_at ? $t->transacted_at->format('d-m-Y') : $t->created_at->format('d-m-Y')
            ];
        }

        // Sort combined activities by timestamp desc
        usort($activities, function ($a, $b) {
            return strcmp($b['timestamp'], $a['timestamp']);
        });

        // Limit to top 10 recent activities
        $activities = array_slice($activities, 0, 10);

        $latestSlowBatches = TolSlowBatch::with(['tool', 'location'])
            ->where('status', 'active')
            ->get()
            ->sortByDesc(function ($batch) {
                return $batch->std_lifetime_yrs > 0 ? ($batch->age_years / $batch->std_lifetime_yrs) : 0;
            })
            ->take(8)
            ->values();

        $fastValFormatted = $fastStockValue >= 1000000 
            ? number_format($fastStockValue / 1000000, 1, ',', '.') . 'M' 
            : number_format($fastStockValue, 0, ',', '.');
        $slowValFormatted = $slowBatchValue >= 1000000 
            ? number_format($slowBatchValue / 1000000, 1, ',', '.') . 'M' 
            : number_format($slowBatchValue, 0, ',', '.');

        $filters = [
            'month_year' => $monthYear,
            'accumulate' => $accumulate,
        ];

        return view('inventory.tool.dashboard', compact(
            'filters', 'startDate', 'endDate',
            'totalValue', 'totalStock', 'totalIn', 'totalOut', 'totalFastMoving', 'totalSlowMoving',
            'groupedStockStatusFast', 'groupedStockStatusSlow',
            'balanceWarningsFast', 'balanceWarningsSlow',
            'trendData', 'paretoData', 'activities', 'latestSlowBatches',
            'fastValFormatted', 'slowValFormatted'
        ));
    }

    public function updateActionStatus(Request $request, $id)
    {
        $tool = TolTool::findOrFail($id);
        
        $updateData = [];
        if ($request->has('action_status')) {
            $status = $request->action_status;
            $updateData['action_status'] = ($status === '' || $status === 'NULL') ? null : $status;
        }
        if ($request->has('action_remark')) {
            $updateData['action_remark'] = $request->action_remark;
        }

        if (!empty($updateData)) {
            $tool->update($updateData);
        }

        return response()->json(['success' => true, 'message' => 'Action information updated.']);
    }

    public function chartDrilldown(Request $request)
    {
        $chartType  = $request->input('chart');
        $label      = $request->input('label');
        $statusFilter = strtolower($request->input('status', ''));
        $search     = $request->input('search');
        $pageSize   = $request->input('pageSize', 10);
        $page       = $request->input('page', 1);
        $offset     = ($page - 1) * $pageSize;
        $stockMoving = $request->input('stock_moving', 'fast');
        if ($stockMoving !== 'slow') {
            $stockMoving = 'fast';
        }

        $result = [];
        $title  = '';
        $total  = 0;

        if ($chartType === 'stock') {
            $categoryName = $label;
            $title = "Stock Detail — {$categoryName}";

            $query = TolTool::with(['category', 'fastStock.location', 'location'])
                ->whereHas('category', fn($q) => $q->where('moving_type', $stockMoving))
                ->where('is_active', true);

            if ($categoryName === 'Uncategorized') {
                $query->whereNull('category_id');
            } else {
                $query->whereHas('category', function($q) use ($categoryName) {
                    $q->where('name', $categoryName);
                });
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('spec_code', 'like', "%{$search}%")
                      ->orWhere('brand', 'like', "%{$search}%");
                });
            }

            $tools = $query->get();

            $processed = [];
            foreach ($tools as $tool) {
                $qty = $tool->total_qty;
                $qtyMin = $tool->qty_min ?? 0;
                $qtyMax = $tool->qty_max ?? 0;

                if ($qty < $qtyMin) {
                    $statusRaw = 'critical';
                } elseif ($qty == $qtyMin) {
                    $statusRaw = 'warning';
                } elseif ($qty > $qtyMax && $qtyMax > 0) {
                    $statusRaw = 'over';
                } else {
                    $statusRaw = 'safe';
                }

                if ($statusFilter && $statusRaw !== $statusFilter) {
                    continue;
                }

                // Build consolidated active Location HTML
                $activeStocks = $tool->fastStock->filter(fn($fs) => $fs->current_qty > 0);
                $locationHtml = '<div class="flex flex-col"><span class="text-[10px] text-gray-400 font-medium">0 PCS</span><span class="text-[8px] text-gray-400">-</span></div>';
                
                if ($activeStocks->isNotEmpty()) {
                    if ($activeStocks->count() === 1) {
                        $fs = $activeStocks->first();
                        $locCode = $fs->location?->code ?? $fs->location?->name ?? 'Unknown';
                        $locationHtml = sprintf(
                            '<div class="flex flex-col"><span class="font-bold text-gray-900 dark:text-white text-[10px]">%d %s</span><span class="text-[8px] text-gray-500 font-medium">%s</span></div>',
                            $fs->current_qty,
                            $tool->uom ?? 'PCS',
                            $locCode
                        );
                    } else {
                        $details = [];
                        foreach ($activeStocks as $fs) {
                            $details[] = [
                                'code' => $fs->location?->code ?? '?',
                                'name' => $fs->location?->name ?? '?',
                                'category' => $fs->location?->category ?? 'storage',
                                'qty' => $fs->current_qty
                            ];
                        }
                        $locationHtml = sprintf(
                            '<div class="flex flex-col"><span class="font-bold text-gray-900 dark:text-white text-[10px]">%d %s</span><button class="location-click-trigger text-[8px] text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 font-bold flex items-center gap-0.5 cursor-pointer bg-transparent border-0 p-0 active:scale-95 transition-all text-left" data-locations="%s" data-popup-title="Tool Locations" data-popup-icon="fa-map-location-dot">%d Locations <i class="fa-solid fa-chevron-down text-[7px] opacity-70"></i></button></div>',
                            $qty,
                            $tool->uom ?? 'PCS',
                            htmlspecialchars(json_encode($details), ENT_QUOTES, 'UTF-8'),
                            $activeStocks->count()
                        );
                    }
                }

                $processed[] = [
                    'id'               => $tool->id,
                    'part_no'          => $tool->name,
                    'spec_code'        => $tool->spec_code ?? '-',
                    'brand'            => $tool->brand ?? '-',
                    'stock'            => number_format($qty) . ' ' . ($tool->uom ?? 'PCS'),
                    'min_stock'        => number_format($qtyMin) . ' ' . ($tool->uom ?? 'PCS'),
                    'max_stock'        => $qtyMax > 0 ? (number_format($qtyMax) . ' ' . ($tool->uom ?? 'PCS')) : '-',
                    'location'         => $locationHtml,
                    'status'           => ucfirst($statusRaw),
                    'action_status'    => $tool->action_status,
                    'action_remark'    => $tool->action_remark,
                ];
            }

            // Sort by Status Priority then Tool Name
            usort($processed, function($a, $b) {
                $order = ['Critical' => 1, 'Warning' => 2, 'Over' => 3, 'Safe' => 4];
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
