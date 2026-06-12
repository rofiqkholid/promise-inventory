<?php

namespace App\Http\Controllers\Inventory\Material;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\Material\StoEvent;
use App\Models\InventoryModel\Material\StoDetail;
use App\Models\InventoryModel\Material\InventoryProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoDashboardController extends Controller
{
    /**
     * Main STO Dashboard view — summary across all events.
     */
    public function index(Request $request)
    {
        // Summary KPIs across all CLOSED events
        $totalEvents    = StoEvent::count();
        $closedEvents   = StoEvent::where('status', 'CLOSED')->count();
        $openEvents     = StoEvent::where('status', 'OPEN')->count();
        $lastEvent      = StoEvent::where('status', 'CLOSED')->orderBy('period_end', 'desc')->first();

        // Net & ABS summary per event (last 6 closed events)
        $recentEvents = StoEvent::where('status', 'CLOSED')
            ->orderBy('period_end', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($event) {
                $totals = $this->getEventTotals($event->id);
                $amount = $totals['total_amount'];
                return [
                    'id'         => $event->id,
                    'hash_id'    => $event->hash_id,
                    'code'       => $event->code,
                    'period'     => $event->period_end ? $event->period_end->format('M y') : $event->period_start->format('M y'),
                    'net_amount' => $totals['net_amount'],
                    'abs_amount' => $totals['abs_amount'],
                    'net_pct'    => $amount > 0 ? round(($totals['net_amount'] / $amount) * 100, 2) : 0,
                    'abs_pct'    => $amount > 0 ? round(($totals['abs_amount'] / $amount) * 100, 2) : 0,
                    'total_amount' => $amount,
                ];
            })
            ->reverse()
            ->values();

        // Overall correction log summary (all CLOSED events) — grouped by model
        $correctionByModel = $this->getCorrectionSummaryByModel();

        $stats = [
            'total_events'  => $totalEvents,
            'closed_events' => $closedEvents,
            'open_events'   => $openEvents,
            'last_event'    => $lastEvent ? $lastEvent->code : '-',
            'last_period'   => $lastEvent && $lastEvent->period_end ? $lastEvent->period_end->format('d M Y') : '-',
        ];

        $reasons = \App\Models\InventoryModel\Material\StoReason::where('is_active', 1)->orderBy('id')->get(['id', 'name', 'category']);

        if ($request->ajax()) {
            return response()->json([
                'stats'             => $stats,
                'recent_events'     => $recentEvents,
                'correction_by_model' => $correctionByModel,
            ]);
        }

        return view('inventory.material.sto.dashboard', compact(
            'stats',
            'recentEvents',
            'correctionByModel',
            'reasons'
        ));
    }

    /**
     * API: Pareto deviation per model for a specific STO event.
     * Returns aggregated ABS deviation per model with reason breakdown.
     */
    public function paretoByModel(Request $request, $id)
    {
        if (in_array($id, ['all', '3m', '6m', '12m'])) {
            $query = StoEvent::where('status', 'CLOSED');
            if ($id === '3m') {
                $query->where('period_end', '>=', now()->subMonths(3));
            } elseif ($id === '6m') {
                $query->where('period_end', '>=', now()->subMonths(6));
            } elseif ($id === '12m') {
                $query->where('period_end', '>=', now()->subMonths(12));
            }
            $eventIds = $query->pluck('id')->toArray();
            
            // Fallback: if no events exist in date range, fetch last N events
            if (empty($eventIds)) {
                $limit = $id === '3m' ? 1 : ($id === '6m' ? 3 : 6);
                $eventIds = StoEvent::where('status', 'CLOSED')->orderBy('period_end', 'desc')->limit($limit)->pluck('id')->toArray();
            }
            
            $eventCode = $id === 'all' ? 'All STO Events (Overall)' : ($id === '3m' ? 'Last 3 Months (Overall)' : ($id === '6m' ? 'Last 6 Months (Overall)' : 'Last 1 Year (Overall)'));
        } else {
            $stoEvent = StoEvent::findByHashOrFail($id);
            $eventIds = [$stoEvent->id];
            $eventCode = $stoEvent->code;
        }

        // Step 1: Get deviation aggregated per model
        $modelDeviation = DB::table('inv_t_sto_detail as sd')
            ->join('inv_t_product_detail as pd', 'pd.id', '=', 'sd.product_detail_id')
            ->leftJoin('models as m', 'm.id', '=', 'pd.model_id')
            ->leftJoin('customers as c', 'c.id', '=', 'm.customer_id')
            ->whereIn('sd.event_id', $eventIds)
            ->select(
                DB::raw("ISNULL(m.name, 'No Model') as model_name"),
                DB::raw("ISNULL(c.code, 'Unknown') as customer_code"),
                DB::raw("SUM(sd.system_qty_snapshot * ISNULL(pd.weight_kg, 0) * ISNULL(pd.material_price, 0)) as system_amount"),
                DB::raw("SUM(sd.real_qty_input * ISNULL(pd.weight_kg, 0) * ISNULL(pd.material_price, 0)) as real_amount"),
                DB::raw("SUM(sd.diff_qty * ISNULL(pd.weight_kg, 0) * ISNULL(pd.material_price, 0)) as net_amount"),
                DB::raw("SUM(ABS(sd.diff_qty * ISNULL(pd.weight_kg, 0) * ISNULL(pd.material_price, 0))) as abs_amount"),
                DB::raw("COUNT(DISTINCT sd.product_detail_id) as part_count"),
                DB::raw("SUM(CASE WHEN sd.diff_qty > 0 THEN 1 ELSE 0 END) as excess_count"),
                DB::raw("SUM(CASE WHEN sd.diff_qty < 0 THEN 1 ELSE 0 END) as shortage_count"),
                DB::raw("SUM(CASE WHEN sd.diff_qty = 0 THEN 1 ELSE 0 END) as match_count")
            )
            ->groupBy('m.name', 'c.code')
            ->orderByDesc(DB::raw("SUM(ABS(sd.diff_qty * ISNULL(pd.weight_kg, 0) * ISNULL(pd.material_price, 0)))"))
            ->get();

        // Total ABS for cumulative % calculation
        $totalAbs = $modelDeviation->sum('abs_amount');
        $cumulative = 0;

        $paretoData = $modelDeviation->map(function ($row) use ($totalAbs, &$cumulative) {
            $cumulative += $row->abs_amount;
            return [
                'model_name'     => $row->model_name,
                'customer_code'  => $row->customer_code,
                'system_amount'  => round($row->system_amount, 0),
                'real_amount'    => round($row->real_amount, 0),
                'net_amount'     => round($row->net_amount, 0),
                'abs_amount'     => round($row->abs_amount, 0),
                'abs_pct'        => $totalAbs > 0 ? round(($row->abs_amount / $totalAbs) * 100, 1) : 0,
                'cumulative_pct' => $totalAbs > 0 ? round(($cumulative / $totalAbs) * 100, 1) : 0,
                'part_count'     => $row->part_count,
                'excess_count'   => $row->excess_count,
                'shortage_count' => $row->shortage_count,
                'match_count'    => $row->match_count,
            ];
        });

        // Step 2: Reason breakdown per model
        $reasonBreakdown = DB::table('inv_t_sto_detail as sd')
            ->join('inv_t_product_detail as pd', 'pd.id', '=', 'sd.product_detail_id')
            ->leftJoin('models as m', 'm.id', '=', 'pd.model_id')
            ->leftJoin('customers as c', 'c.id', '=', 'm.customer_id')
            ->leftJoin('inv_m_sto_reasons as r', 'r.id', '=', 'sd.reason_id')
            ->join('products as p', 'p.id', '=', 'pd.product_id')
            ->leftJoin('inv_m_revision as rev', 'rev.id', '=', 'pd.revision_id')
            ->whereIn('sd.event_id', $eventIds)
            ->whereNotNull('sd.reason_id')
            ->select(
                DB::raw("ISNULL(m.name, 'No Model') as model_name"),
                DB::raw("ISNULL(c.code, 'Unknown') as customer_code"),
                'p.part_no',
                'rev.code as revision_code',
                DB::raw("ISNULL(r.name, 'Unknown') as reason_name"),
                DB::raw("ISNULL(r.category, 'OTHERS') as reason_category"),
                DB::raw("SUM(sd.system_qty_snapshot * ISNULL(pd.weight_kg, 0) * ISNULL(pd.material_price, 0)) as system_amount"),
                DB::raw("SUM(sd.real_qty_input * ISNULL(pd.weight_kg, 0) * ISNULL(pd.material_price, 0)) as real_amount"),
                DB::raw("SUM(sd.diff_qty * ISNULL(pd.weight_kg, 0) * ISNULL(pd.material_price, 0)) as net_amount"),
                DB::raw("SUM(ABS(sd.diff_qty * ISNULL(pd.weight_kg, 0) * ISNULL(pd.material_price, 0))) as abs_amount"),
                DB::raw("COUNT(*) as entry_count")
            )
            ->groupBy('m.name', 'c.code', 'p.part_no', 'rev.code', 'r.name', 'r.category')
            ->orderBy('m.name')
            ->orderByDesc(DB::raw("SUM(ABS(sd.diff_qty * ISNULL(pd.weight_kg, 0) * ISNULL(pd.material_price, 0)))"))
            ->get();

        // Reason distribution summary (for mini bar/donut chart)
        $reasonDistribution = DB::table('inv_t_sto_detail as sd')
            ->leftJoin('inv_m_sto_reasons as r', 'r.id', '=', 'sd.reason_id')
            ->whereIn('sd.event_id', $eventIds)
            ->whereNotNull('sd.reason_id')
            ->select(
                DB::raw("ISNULL(r.name, 'Unknown') as reason_name"),
                DB::raw("COUNT(*) as count")
            )
            ->groupBy('r.name')
            ->orderByDesc('count')
            ->get();

        return response()->json([
            'event_code'         => $eventCode,
            'pareto'             => $paretoData,
            'reason_breakdown'   => $reasonBreakdown,
            'reason_distribution' => $reasonDistribution,
            'total_abs'          => round($totalAbs, 0),
        ]);
    }

    /**
     * API: Correction Log grouped by model across active STO events.
     * Shows stock adjustments made per model.
     */
    public function correctionLogByModel(Request $request)
    {
        $limit = $request->input('limit', 10); // top N models
        
        $eventIds = null;
        if ($request->filled('event_id')) {
            $eventIds = $this->resolveEventIds($request->input('event_id'));
        }

        $data = $this->getCorrectionSummaryByModel($limit, $eventIds);

        return response()->json([
            'data' => $data,
        ]);
    }

    /**
     * API: Correction Log detail per model — list of events and adjustments.
     */
    public function correctionLogDetail(Request $request, $modelName)
    {
        $eventIds = null;
        if ($request->filled('event_id')) {
            $eventIds = $this->resolveEventIds($request->input('event_id'));
        }

        $query = DB::table('inv_t_sto_detail as sd')
            ->join('inv_t_sto_event as se', 'se.id', '=', 'sd.event_id')
            ->join('inv_t_product_detail as pd', 'pd.id', '=', 'sd.product_detail_id')
            ->join('products as p', 'p.id', '=', 'pd.product_id')
            ->leftJoin('models as m', 'm.id', '=', 'pd.model_id')
            ->leftJoin('inv_m_sto_reasons as r', 'r.id', '=', 'sd.reason_id')
            ->leftJoin('inv_m_unit as u', 'u.id', '=', 'pd.unit_id')
            ->leftJoin('inv_m_revision as rev', 'rev.id', '=', 'pd.revision_id')
            ->where(DB::raw("ISNULL(m.name, 'No Model')"), $modelName);

        if (!empty($eventIds)) {
            $query->whereIn('sd.event_id', $eventIds);
        } else {
            $query->where('se.status', 'CLOSED');
        }

        $detail = $query->select(
                'se.code as event_code',
                'se.period_end',
                'p.part_no',
                'rev.code as revision_code',
                DB::raw("ISNULL(r.name, '-') as reason_name"),
                'sd.diff_qty',
                DB::raw("sd.diff_qty * ISNULL(pd.weight_kg, 0) * ISNULL(pd.material_price, 0) as diff_amount"),
                'sd.remark',
                'u.code as unit_code',
                'pd.pcs_per_unit',
                'pd.gross_coil'
            )
            ->orderByDesc('se.period_end')
            ->orderBy('p.part_no')
            ->get();

        return response()->json([
            'model_name' => $modelName,
            'detail'     => $detail,
        ]);
    }

    /**
     * API: Net & ABS per event (for trend chart).
     * Used by the dashboard to render the Summary chart (Slide 1 equivalent).
     */
    public function eventTrendData(Request $request)
    {
        $limit = (int) $request->input('limit', 10);

        $events = StoEvent::where('status', 'CLOSED')
            ->orderBy('period_end', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($event) {
                $totals = $this->getEventTotals($event->id);
                $amount = $totals['total_amount'];
                return [
                    'code'       => $event->code,
                    'period'     => $event->period_end ? $event->period_end->format('M y') : $event->period_start->format('M y'),
                    'net_amount' => $totals['net_amount'],
                    'abs_amount' => $totals['abs_amount'],
                    'net_pct'    => $amount > 0 ? round(($totals['net_amount'] / $amount) * 100, 2) : 0,
                    'abs_pct'    => $amount > 0 ? round(($totals['abs_amount'] / $amount) * 100, 2) : 0,
                    'total_amount' => round($amount, 0),
                ];
            })
            ->reverse()
            ->values();

        return response()->json(['events' => $events]);
    }

    // ─── Private Helpers ────────────────────────────────────────────────────────

    /**
     * Aggregate Net Amount, ABS Amount, and Total Amount for a given STO event.
     */
    private function getEventTotals(int $eventId): array
    {
        $row = DB::table('inv_t_sto_detail as sd')
            ->join('inv_t_product_detail as pd', 'pd.id', '=', 'sd.product_detail_id')
            ->where('sd.event_id', $eventId)
            ->selectRaw("
                SUM(sd.diff_qty * ISNULL(pd.weight_kg, 0) * ISNULL(pd.material_price, 0))        AS net_amount,
                SUM(ABS(sd.diff_qty * ISNULL(pd.weight_kg, 0) * ISNULL(pd.material_price, 0)))    AS abs_amount,
                SUM(sd.system_qty_snapshot * ISNULL(pd.weight_kg, 0) * ISNULL(pd.material_price, 0)) AS total_amount
            ")
            ->first();

        return [
            'net_amount'   => round((float) ($row->net_amount ?? 0), 0),
            'abs_amount'   => round((float) ($row->abs_amount ?? 0), 0),
            'total_amount' => round((float) ($row->total_amount ?? 0), 0),
        ];
    }

    /**
     * Build correction summary grouped by model (across active STO events).
     */
    private function getCorrectionSummaryByModel(int $limit = 20, ?array $eventIds = null): \Illuminate\Support\Collection
    {
        $query = DB::table('inv_t_sto_detail as sd')
            ->join('inv_t_sto_event as se', 'se.id', '=', 'sd.event_id')
            ->join('inv_t_product_detail as pd', 'pd.id', '=', 'sd.product_detail_id')
            ->leftJoin('models as m', 'm.id', '=', 'pd.model_id')
            ->leftJoin('customers as c', 'c.id', '=', 'm.customer_id');

        if (!empty($eventIds)) {
            $query->whereIn('sd.event_id', $eventIds);
        } else {
            $query->where('se.status', 'CLOSED');
        }

        return $query->select(
                DB::raw("ISNULL(m.name, 'No Model') as model_name"),
                DB::raw("ISNULL(c.code, 'Unknown') as customer_code"),
                DB::raw("COUNT(DISTINCT se.id) as event_count"),
                DB::raw("COUNT(DISTINCT pd.id) as affected_parts"),
                DB::raw("SUM(CASE WHEN sd.diff_qty > 0 THEN sd.diff_qty * ISNULL(pd.weight_kg, 0) * ISNULL(pd.material_price, 0) ELSE 0 END) as increment_amount"),
                DB::raw("SUM(CASE WHEN sd.diff_qty < 0 THEN ABS(sd.diff_qty * ISNULL(pd.weight_kg, 0) * ISNULL(pd.material_price, 0)) ELSE 0 END) as decrement_amount"),
                DB::raw("SUM(sd.diff_qty * ISNULL(pd.weight_kg, 0) * ISNULL(pd.material_price, 0)) as net_correction"),
                DB::raw("SUM(ABS(sd.diff_qty * ISNULL(pd.weight_kg, 0) * ISNULL(pd.material_price, 0))) as total_correction"),
                // PCS-level
                DB::raw("SUM(CASE WHEN sd.diff_qty > 0 THEN sd.diff_qty * ISNULL(pd.pcs_per_unit, 1) ELSE 0 END) as increment_pcs"),
                DB::raw("SUM(CASE WHEN sd.diff_qty < 0 THEN ABS(sd.diff_qty * ISNULL(pd.pcs_per_unit, 1)) ELSE 0 END) as decrement_pcs")
            )
            ->groupBy('m.name', 'c.code')
            ->orderByDesc(DB::raw("SUM(ABS(sd.diff_qty * ISNULL(pd.weight_kg, 0) * ISNULL(pd.material_price, 0)))"))
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                return [
                    'model_name'       => $row->model_name,
                    'customer_code'    => $row->customer_code,
                    'event_count'      => $row->event_count,
                    'affected_parts'   => $row->affected_parts,
                    'increment_amount' => round((float) $row->increment_amount, 0),
                    'decrement_amount' => round((float) $row->decrement_amount, 0),
                    'net_correction'   => round((float) $row->net_correction, 0),
                    'total_correction' => round((float) $row->total_correction, 0),
                    'increment_pcs'    => round((float) $row->increment_pcs, 0),
                    'decrement_pcs'    => round((float) $row->decrement_pcs, 0),
                ];
            });
    }

    /**
     * Resolve event IDs based on dashboard filter parameter.
     */
    private function resolveEventIds(string $id): array
    {
        if (in_array($id, ['all', '3m', '6m', '12m'])) {
            $query = StoEvent::where('status', 'CLOSED');
            if ($id === '3m') {
                $query->where('period_end', '>=', now()->subMonths(3));
            } elseif ($id === '6m') {
                $query->where('period_end', '>=', now()->subMonths(6));
            } elseif ($id === '12m') {
                $query->where('period_end', '>=', now()->subMonths(12));
            }
            $eventIds = $query->pluck('id')->toArray();
            
            // Fallback: if no events exist in date range, fetch last N events
            if (empty($eventIds)) {
                $limit = $id === '3m' ? 1 : ($id === '6m' ? 3 : 6);
                $eventIds = StoEvent::where('status', 'CLOSED')->orderBy('period_end', 'desc')->limit($limit)->pluck('id')->toArray();
            }
            return $eventIds;
        }

        $stoEvent = StoEvent::findByHash($id);
        if ($stoEvent) {
            return [$stoEvent->id];
        }
        
        // Fallback
        return StoEvent::where('status', 'CLOSED')->orderBy('period_end', 'desc')->limit(6)->pluck('id')->toArray();
    }
}
