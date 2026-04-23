<?php

namespace App\Http\Controllers\Inventory\Material;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VaveDashboardController extends Controller
{
    /**
     * Render the VAVE Analysis Dashboard view.
     */
    public function index()
    {
        return view('inventory.material.vave.dashboard');
    }

    /**
     * Get monthly Gap Benefit chart data.
     *
     * Formula: Gap Benefit (IDR) = (Plan_Kg - Act_Kg) × IDR/Kg × Qty_Usage
     *   Plan    = inv_m_vave_base.weight_kg  (EBD / Budget Yearly, is_active = 1)
     *   Actual  = inv_t_product_detail.weight_kg (revision aktif per item)
     *   IDR/Kg  = inv_m_vave_base.material_price (from EBD baseline)
     *   Qty     = SUM of transaction IN qty (per month)
     */
    public function chartData(Request $request)
    {
        $year       = $request->input('year', date('Y'));
        $customerId = $request->input('customer_id');
        $modelId    = $request->input('model_id');

        $query = DB::table('inv_t_inventory_transaction as t')
            ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
            ->join('inv_t_product_detail as pd', 'pd.id', '=', 't.product_detail_id')
            ->join('products as p', 'p.id', '=', 'pd.product_id')
            ->join('models as m', 'm.id', '=', 'pd.model_id')
            ->join('customers as c', 'c.id', '=', 'p.customer_id')
            // Active EBD Baseline
            ->leftJoin('inv_m_vave_base as vb', function ($join) {
                $join->on('vb.product_id', '=', 'p.id')
                     ->where('vb.is_active', '=', 1);
            })
            ->where('tc.effect', 1)                       // Transaction IN only
            ->whereYear('t.transaction_date', $year)
            ->where('p.is_delete', 0)
            ->whereNotNull('vb.id')                        // Must have EBD data
            ->select([
                'p.part_no',
                'p.part_name',
                'm.name as model_name',
                'c.code as customer_code',
                DB::raw('MONTH(t.transaction_date) as month_num'),
                DB::raw('ISNULL(vb.weight_kg, 0) as plan_kg'),
                DB::raw('ISNULL(pd.weight_kg, 0) as actual_kg'),
                DB::raw('ISNULL(vb.material_price, 0) as idr_per_kg'),
                DB::raw('SUM(t.qty) as qty_usage'),
                // Gap per unit (Kg)
                DB::raw('ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0) as gap_kg_per_unit'),
                // Gap Benefit IDR = (Plan - Actual) × IDR/Kg × Qty
                DB::raw('((ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) * ISNULL(vb.material_price, 0)) * SUM(t.qty) as gap_benefit_idr'),
                // Gap Kg total = gap_per_unit × qty
                DB::raw('(ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) * SUM(t.qty) as gap_kg_total'),
            ])
            ->groupBy(
                'p.part_no', 'p.part_name', 'm.name', 'c.code',
                DB::raw('MONTH(t.transaction_date)'),
                'vb.weight_kg', 'pd.weight_kg', 'vb.material_price'
            );

        if ($customerId) {
            $query->where('p.customer_id', $customerId);
        }
        if ($modelId) {
            $query->where('pd.model_id', $modelId);
        }

        $rawData = $query->orderBy(DB::raw('MONTH(t.transaction_date)'))->get();

        // --- Aggregate by month ---
        $monthlyAgg = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyAgg[$i] = [
                'gap_kg_total'      => 0,
                'gap_benefit_idr'   => 0,
                'qty_usage'         => 0,
                'merit_count'       => 0,
                'loss_count'        => 0,
            ];
        }

        // --- Per-item breakdown for table & pareto ---
        $itemData = [];
        $kpiTotals = [
            'gap_benefit_idr' => 0,
            'gap_kg_total'    => 0,
            'qty_usage'       => 0,
            'merit_count'     => 0,
            'loss_count'      => 0,
        ];

        foreach ($rawData as $row) {
            $month    = (int) $row->month_num;
            $gapKg    = (float) $row->gap_kg_total;
            $gapIdr   = (float) $row->gap_benefit_idr;
            $qty      = (float) $row->qty_usage;

            $monthlyAgg[$month]['gap_kg_total']    += $gapKg;
            $monthlyAgg[$month]['gap_benefit_idr'] += $gapIdr;
            $monthlyAgg[$month]['qty_usage']       += $qty;

            if ($gapKg > 0) $monthlyAgg[$month]['merit_count']++;
            elseif ($gapKg < 0) $monthlyAgg[$month]['loss_count']++;

            $kpiTotals['gap_benefit_idr'] += $gapIdr;
            $kpiTotals['gap_kg_total']    += $gapKg;
            $kpiTotals['qty_usage']       += $qty;
            if ($gapKg > 0.001) $kpiTotals['merit_count']++;
            elseif ($gapKg < -0.001) $kpiTotals['loss_count']++;

            $itemKey = $row->part_no . '|' . $row->model_name;
            if (!isset($itemData[$itemKey])) {
                $itemData[$itemKey] = [
                    'part_no'         => $row->part_no,
                    'part_name'       => $row->part_name,
                    'model_name'      => $row->model_name,
                    'customer_code'   => $row->customer_code,
                    'plan_kg'         => (float) $row->plan_kg,
                    'actual_kg'       => (float) $row->actual_kg,
                    'idr_per_kg'      => (float) $row->idr_per_kg,
                    'gap_kg_total'    => 0,
                    'gap_benefit_idr' => 0,
                    'qty_usage'       => 0,
                    'monthly'         => array_fill(1, 12, ['gap_kg' => 0, 'gap_idr' => 0, 'qty' => 0]),
                ];
            }

            $itemData[$itemKey]['gap_kg_total']    += $gapKg;
            $itemData[$itemKey]['gap_benefit_idr'] += $gapIdr;
            $itemData[$itemKey]['qty_usage']       += $qty;
            $itemData[$itemKey]['monthly'][$month]['gap_kg']  += $gapKg;
            $itemData[$itemKey]['monthly'][$month]['gap_idr'] += $gapIdr;
            $itemData[$itemKey]['monthly'][$month]['qty']     += $qty;
        }

        // Sort itemData by gap_benefit_idr descending for pareto
        uasort($itemData, fn($a, $b) => $b['gap_benefit_idr'] <=> $a['gap_benefit_idr']);

        // Build chart series: monthly aggregate
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $chartMonthly = [
            'labels'          => $monthNames,
            'gap_kg_series'   => [],
            'gap_idr_series'  => [],
            'qty_series'      => [],
            'merit_series'    => [],
            'loss_series'     => [],
        ];

        for ($i = 1; $i <= 12; $i++) {
            $chartMonthly['gap_kg_series'][]  = round($monthlyAgg[$i]['gap_kg_total'], 3);
            $chartMonthly['gap_idr_series'][] = round($monthlyAgg[$i]['gap_benefit_idr'], 0);
            $chartMonthly['qty_series'][]     = round($monthlyAgg[$i]['qty_usage'], 0);
            $chartMonthly['merit_series'][]   = $monthlyAgg[$i]['merit_count'];
            $chartMonthly['loss_series'][]    = $monthlyAgg[$i]['loss_count'];
        }

        return response()->json([
            'kpi'     => $kpiTotals,
            'monthly' => $chartMonthly,
            'items'   => array_values($itemData),
        ]);
    }

    /**
     * Get Pareto-sorted data for Gap Benefit analysis.
     */
    public function paretoData(Request $request)
    {
        $year       = $request->input('year', date('Y'));
        $customerId = $request->input('customer_id');
        $modelId    = $request->input('model_id');
        $limit      = (int) $request->input('limit', 20);

        $query = DB::table('inv_t_inventory_transaction as t')
            ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
            ->join('inv_t_product_detail as pd', 'pd.id', '=', 't.product_detail_id')
            ->join('products as p', 'p.id', '=', 'pd.product_id')
            ->join('models as m', 'm.id', '=', 'pd.model_id')
            ->join('customers as c', 'c.id', '=', 'p.customer_id')
            ->leftJoin('inv_m_vave_base as vb', function ($join) {
                $join->on('vb.product_id', '=', 'p.id')
                     ->where('vb.is_active', '=', 1);
            })
            ->where('tc.effect', 1)
            ->whereYear('t.transaction_date', $year)
            ->where('p.is_delete', 0)
            ->whereNotNull('vb.id')
            ->select([
                'p.part_no',
                'p.part_name',
                'm.name as model_name',
                'c.code as customer_code',
                DB::raw('ISNULL(vb.weight_kg, 0) as plan_kg'),
                DB::raw('ISNULL(pd.weight_kg, 0) as actual_kg'),
                DB::raw('ISNULL(vb.material_price, 0) as idr_per_kg'),
                DB::raw('SUM(t.qty) as qty_usage'),
                DB::raw('((ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) * ISNULL(vb.material_price, 0)) * SUM(t.qty) as gap_benefit_idr'),
                DB::raw('(ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) * SUM(t.qty) as gap_kg_total'),
            ])
            ->groupBy(
                'p.part_no', 'p.part_name', 'm.name', 'c.code',
                'vb.weight_kg', 'pd.weight_kg', 'vb.material_price'
            );

        if ($customerId) $query->where('p.customer_id', $customerId);
        if ($modelId)    $query->where('pd.model_id', $modelId);

        $data = $query
            ->orderByRaw('gap_benefit_idr DESC')
            ->limit($limit)
            ->get();

        // Calculate cumulative percentage
        $totalAbs = $data->sum(fn($r) => abs((float)$r->gap_benefit_idr));
        $cumulative = 0;
        $result = $data->map(function ($row) use (&$cumulative, $totalAbs) {
            $val = (float) $row->gap_benefit_idr;
            $cumulative += abs($val);
            $cumPct = $totalAbs > 0 ? round(($cumulative / $totalAbs) * 100, 1) : 0;
            return [
                'label'           => $row->part_no . ' (' . $row->model_name . ')',
                'part_no'         => $row->part_no,
                'part_name'       => $row->part_name,
                'model_name'      => $row->model_name,
                'customer_code'   => $row->customer_code,
                'plan_kg'         => (float) $row->plan_kg,
                'actual_kg'       => (float) $row->actual_kg,
                'gap_kg_total'    => (float) $row->gap_kg_total,
                'idr_per_kg'      => (float) $row->idr_per_kg,
                'qty_usage'       => (float) $row->qty_usage,
                'gap_benefit_idr' => $val,
                'cumulative_pct'  => $cumPct,
                'status'          => $val > 0.001 ? 'MERIT' : ($val < -0.001 ? 'LOSS' : 'NO CHANGE'),
            ];
        });

        return response()->json([
            'pareto' => $result,
            'total_gap_idr' => $data->sum(fn($r) => (float)$r->gap_benefit_idr),
        ]);
    }
}
