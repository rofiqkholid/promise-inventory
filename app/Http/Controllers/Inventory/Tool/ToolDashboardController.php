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
        // 1. Parse Period/Time Filter
        $period = $request->input('period', '30d');
        $startDate = null;
        $endDate = Carbon::now();

        if ($period === '7d') {
            $startDate = Carbon::now()->subDays(7);
        } elseif ($period === '30d') {
            $startDate = Carbon::now()->subDays(30);
        } elseif ($period === '90d') {
            $startDate = Carbon::now()->subDays(90);
        } elseif ($period === 'this_month') {
            $startDate = Carbon::now()->startOfMonth();
        } elseif ($period === 'this_year') {
            $startDate = Carbon::now()->startOfYear();
        } elseif ($period === 'custom') {
            $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->subDays(30);
            $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();
        } else {
            $startDate = Carbon::now()->subDays(30);
        }

        // 2. Fetch Card KPI Data
        // Card 1: Total Value (Fast Stock Value + Slow Batch Value)
        $fastStockValue = TolFastStock::join('tol_m_tools', 'tol_m_tools.id', '=', 'tol_t_fast_stock.tool_id')
            ->sum(DB::raw('tol_t_fast_stock.current_qty * ISNULL(tol_m_tools.price_per_unit, 0)'));
        
        $slowBatchValue = TolSlowBatch::where('status', 'active')
            ->sum('current_value');
        
        $totalValue = $fastStockValue + $slowBatchValue;

        // Card 2: Total Stock (Fast Stock Qty + Active Slow Batch Current Qty)
        $fastStockQty = TolFastStock::sum('current_qty');
        $slowStockQty = TolSlowBatch::where('status', 'active')->sum('qty_current');
        $totalStock = $fastStockQty + $slowStockQty;

        // Card 3: Total In (Incoming transactions in date range)
        $fastIn = TolTransaction::where('transaction_type', 'in')
            ->whereBetween('transacted_at', [$startDate, $endDate])
            ->sum('qty');
        
        $slowIn = TolSlowBatch::whereBetween('purchase_date', [$startDate, $endDate])
            ->sum('qty_purchased');
        
        $totalIn = $fastIn + $slowIn;

        // Card 4: Total Out (Outgoing transactions in date range)
        $fastOut = abs(TolTransaction::where('transaction_type', 'out')
            ->whereBetween('transacted_at', [$startDate, $endDate])
            ->sum('qty'));

        $slowOut = TolSlowBatch::whereIn('status', ['nok', 'retired'])
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->sum('qty_purchased');

        $totalOut = $fastOut + $slowOut;

        // Card 5 & 6: Moving Breakdown
        $totalFastMoving = $fastStockQty;
        $totalSlowMoving = $slowStockQty;


        // 3. Stock Status - Fast Moving - Group by Category (Critical, Warning, Over, Safe)
        $fastStockList = TolFastStock::with(['tool.category'])->get();
        $groupedStockStatus = [];

        // Prepopulate with all existing categories for consistent keys
        $allCategories = TolCategory::where('is_active', true)->pluck('name')->toArray();
        foreach ($allCategories as $catName) {
            $groupedStockStatus[$catName] = [
                'critical' => 0,
                'warning' => 0,
                'over' => 0,
                'safe' => 0,
                'total' => 0,
                'need_action' => 'Stock level healthy. No action required.'
            ];
        }

        foreach ($fastStockList as $stock) {
            $tool = $stock->tool;
            if (!$tool) continue;

            $catName = $tool->category?->name ?? 'Uncategorized';
            if (!isset($groupedStockStatus[$catName])) {
                $groupedStockStatus[$catName] = [
                    'critical' => 0,
                    'warning' => 0,
                    'over' => 0,
                    'safe' => 0,
                    'total' => 0,
                    'need_action' => 'Stock level healthy. No action required.'
                ];
            }

            $qty = $stock->current_qty;
            $qtyMin = $tool->qty_min ?? 0;
            $limitStock = ($qtyMin > 0 ? $qtyMin * 1.5 : 5);
            $qtyMax = $tool->qty_max ?? ($qtyMin > 0 ? $qtyMin * 3 : 20);

            if ($qty < $qtyMin) {
                $status = 'critical';
            } elseif ($qty <= $limitStock) {
                $status = 'warning';
            } elseif ($qty > $qtyMax && $qtyMax > 0) {
                $status = 'over';
            } else {
                $status = 'safe';
            }

            $groupedStockStatus[$catName][$status]++;
            $groupedStockStatus[$catName]['total']++;
        }

        // Generate Action Notes dynamically based on priority (Critical > Warning > Over > Safe)
        foreach ($groupedStockStatus as $catName => &$data) {
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
        unset($data); // Break reference


        // 4. Balance Warnings List
        $balanceWarnings = [];
        foreach ($fastStockList as $stock) {
            $tool = $stock->tool;
            if (!$tool) continue;

            $qty = $stock->current_qty;
            $qtyMin = $tool->qty_min ?? 0;
            $limitStock = ($qtyMin > 0 ? $qtyMin * 1.5 : 5);

            if ($qty <= $limitStock || $qty < $qtyMin) {
                $status = $qty < $qtyMin ? 'Critical' : 'Warning';
                $balanceWarnings[] = [
                    'id' => $stock->id,
                    'tool_name' => $tool->name,
                    'brand' => $tool->brand ?? '-',
                    'spec_code' => $tool->spec_code ?? '-',
                    'category' => $tool->category?->name ?? 'Uncategorized',
                    'location' => $stock->location?->name ?? '-',
                    'current_qty' => $qty,
                    'qty_min' => $qtyMin,
                    'limit_stock' => $limitStock,
                    'status' => $status,
                    'action' => $status === 'Critical' ? 'Restock Immediately' : 'Schedule Restock',
                    'action_status' => $stock->action_status,
                    'action_remark' => $stock->action_remark
                ];
            }
        }

        // Sort warnings by Critical -> Warning
        usort($balanceWarnings, function ($a, $b) {
            if ($a['status'] === $b['status']) {
                return $a['current_qty'] <=> $b['current_qty'];
            }
            return $a['status'] === 'Critical' ? -1 : 1;
        });


        // 5. Transaction Trend (IN vs OUT) Over Time
        $trendData = [];
        $labels = [];
        $ins = [];
        $outs = [];

        // Build a continuous sequence of labels based on period
        if ($period === '7d') {
            for ($i = 6; $i >= 0; $i--) {
                $d = Carbon::now()->subDays($i);
                $labels[] = $d->format('d M');
                $dStart = (clone $d)->startOfDay();
                $dEnd = (clone $d)->endOfDay();

                $fastInQty = TolTransaction::where('transaction_type', 'in')->whereBetween('transacted_at', [$dStart, $dEnd])->sum('qty');
                $slowInQty = TolSlowBatch::whereBetween('purchase_date', [$dStart, $dEnd])->sum('qty_purchased');
                $ins[] = $fastInQty + $slowInQty;

                $fastOutQty = abs(TolTransaction::where('transaction_type', 'out')->whereBetween('transacted_at', [$dStart, $dEnd])->sum('qty'));
                $slowOutQty = TolSlowBatch::whereIn('status', ['nok', 'retired'])->whereBetween('updated_at', [$dStart, $dEnd])->sum('qty_purchased');
                $outs[] = $fastOutQty + $slowOutQty;
            }
        } elseif ($period === 'this_year') {
            for ($i = 1; $i <= 12; $i++) {
                $labels[] = Carbon::create(null, $i, 1)->format('M');
                $dStart = Carbon::now()->month($i)->startOfMonth();
                $dEnd = Carbon::now()->month($i)->endOfMonth();

                $fastInQty = TolTransaction::where('transaction_type', 'in')->whereBetween('transacted_at', [$dStart, $dEnd])->sum('qty');
                $slowInQty = TolSlowBatch::whereBetween('purchase_date', [$dStart, $dEnd])->sum('qty_purchased');
                $ins[] = $fastInQty + $slowInQty;

                $fastOutQty = abs(TolTransaction::where('transaction_type', 'out')->whereBetween('transacted_at', [$dStart, $dEnd])->sum('qty'));
                $slowOutQty = TolSlowBatch::whereIn('status', ['nok', 'retired'])->whereBetween('updated_at', [$dStart, $dEnd])->sum('qty_purchased');
                $outs[] = $fastOutQty + $slowOutQty;
            }
        } else {
            // Default 30 days grouped into weekly chunks or daily if needed
            // For dashboard richness, let's group by daily intervals for the last 30 days
            for ($i = 29; $i >= 0; $i -= 3) {
                $d = Carbon::now()->subDays($i);
                $labels[] = $d->format('d M');
                $dStart = (clone $d)->subDays(2)->startOfDay();
                $dEnd = (clone $d)->endOfDay();

                $fastInQty = TolTransaction::where('transaction_type', 'in')->whereBetween('transacted_at', [$dStart, $dEnd])->sum('qty');
                $slowInQty = TolSlowBatch::whereBetween('purchase_date', [$dStart, $dEnd])->sum('qty_purchased');
                $ins[] = $fastInQty + $slowInQty;

                $fastOutQty = abs(TolTransaction::where('transaction_type', 'out')->whereBetween('transacted_at', [$dStart, $dEnd])->sum('qty'));
                $slowOutQty = TolSlowBatch::whereIn('status', ['nok', 'retired'])->whereBetween('updated_at', [$dStart, $dEnd])->sum('qty_purchased');
                $outs[] = $fastOutQty + $slowOutQty;
            }
        }

        $trendData = [
            'labels' => $labels,
            'ins' => $ins,
            'outs' => $outs
        ];


        // 6. Pareto Diagram berdasarkan Transaksi OUT
        // We get total outbound qty per tool spec/name
        $outboundByTool = DB::table('tol_t_transactions as t')
            ->join('tol_m_tools as tl', 'tl.id', '=', 't.tool_id')
            ->where('t.transaction_type', 'out')
            ->whereBetween('t.transacted_at', [$startDate, $endDate])
            ->select('tl.name as tool_name', DB::raw('SUM(t.qty) as total_qty'))
            ->groupBy('tl.name')
            ->orderBy('total_qty', 'desc')
            ->get();

        // Also mix in slow-moving NOK batches as outbound consumption
        $slowOutboundByTool = DB::table('tol_t_slow_batches as b')
            ->join('tol_m_tools as tl', 'tl.id', '=', 'b.tool_id')
            ->whereIn('b.status', ['nok', 'retired'])
            ->whereBetween('b.updated_at', [$startDate, $endDate])
            ->select('tl.name as tool_name', DB::raw('SUM(b.qty_purchased) as total_qty'))
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

        return view('inventory.tool.dashboard', compact(
            'period', 'startDate', 'endDate',
            'totalValue', 'totalStock', 'totalIn', 'totalOut', 'totalFastMoving', 'totalSlowMoving',
            'groupedStockStatus', 'balanceWarnings', 'trendData', 'paretoData', 'activities', 'latestSlowBatches',
            'fastValFormatted', 'slowValFormatted'
        ));
    }

    public function updateActionStatus(Request $request, $id)
    {
        $stock = TolFastStock::findOrFail($id);
        
        $updateData = [];
        if ($request->has('action_status')) {
            $status = $request->action_status;
            $updateData['action_status'] = ($status === '' || $status === 'NULL') ? null : $status;
        }
        if ($request->has('action_remark')) {
            $updateData['action_remark'] = $request->action_remark;
        }

        if (!empty($updateData)) {
            $stock->update($updateData);
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

        $result = [];
        $title  = '';
        $total  = 0;

        if ($chartType === 'stock') {
            $categoryName = $label;
            $title = "Stock Detail — {$categoryName}";

            $query = TolFastStock::with(['tool.category', 'location']);

            if ($categoryName === 'Uncategorized') {
                $query->whereHas('tool', function($q) {
                    $q->whereNull('category_id');
                });
            } else {
                $query->whereHas('tool.category', function($q) use ($categoryName) {
                    $q->where('name', $categoryName);
                });
            }

            if ($search) {
                $query->whereHas('tool', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('spec_code', 'like', "%{$search}%")
                      ->orWhere('brand', 'like', "%{$search}%");
                });
            }

            $items = $query->get();

            $processed = [];
            foreach ($items as $stock) {
                $tool = $stock->tool;
                if (!$tool) continue;

                $qty = $stock->current_qty;
                $qtyMin = $tool->qty_min ?? 0;
                $limitStock = ($qtyMin > 0 ? $qtyMin * 1.5 : 5);
                $qtyMax = $tool->qty_max ?? ($qtyMin > 0 ? $qtyMin * 3 : 20);

                if ($qty < $qtyMin) {
                    $statusRaw = 'critical';
                } elseif ($qty <= $limitStock) {
                    $statusRaw = 'warning';
                } elseif ($qty > $qtyMax && $qtyMax > 0) {
                    $statusRaw = 'over';
                } else {
                    $statusRaw = 'safe';
                }

                if ($statusFilter && $statusRaw !== $statusFilter) {
                    continue;
                }

                $processed[] = [
                    'id'            => $stock->id,
                    'part_no'       => $tool->name, // Using tool name as primary identifier
                    'spec_code'     => $tool->spec_code ?? '-',
                    'brand'         => $tool->brand ?? '-',
                    'stock'         => number_format($qty) . ' ' . ($tool->uom ?? 'PCS'),
                    'min_stock'     => number_format($qtyMin) . ' ' . ($tool->uom ?? 'PCS'),
                    'location'      => $stock->location?->name ?? '-',
                    'status'        => ucfirst($statusRaw),
                    'action_status' => $stock->action_status,
                    'action_remark' => $stock->action_remark,
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
