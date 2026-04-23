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
        $month      = $request->input('month'); // New filter
        $customerId = $request->input('customer_id');
        $modelId    = $request->input('model_id');

        // Base Query
        $baseQuery = DB::table('inv_t_inventory_transaction as t')
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
            ->whereNotNull('vb.id');

        if ($customerId) $baseQuery->where('p.customer_id', $customerId);
        if ($modelId)    $baseQuery->where('pd.model_id', $modelId);

        // 1. DATA FOR TREND (Whole Year)
        $trendData = (clone $baseQuery)
            ->select([
                DB::raw('MONTH(t.transaction_date) as month_num'),
                DB::raw('SUM(((ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) * ISNULL(vb.material_price, 0)) * t.qty) as gap_benefit_idr'),
                DB::raw('SUM((ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) * t.qty) as gap_kg_total'),
                DB::raw('SUM(t.qty) as qty_usage'),
            ])
            ->groupBy(DB::raw('MONTH(t.transaction_date)'))
            ->get();

        // DATA FOR MODELS (KPIs, Table, & Charts)
        $periodQuery = clone $baseQuery;
        if ($month) {
            $periodQuery->whereMonth('t.transaction_date', $month);
        }

        $rawData = $periodQuery->select([
                'p.part_no',
                'p.part_name',
                'm.name as model_name',
                'c.code as customer_code',
                DB::raw('ISNULL(vb.weight_kg, 0) as plan_kg'),
                DB::raw('ISNULL(pd.weight_kg, 0) as actual_kg'),
                DB::raw('ISNULL(vb.material_price, 0) as idr_per_kg'),
                DB::raw('SUM(t.qty) as qty_usage'),
                DB::raw('SUM(((ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) * ISNULL(vb.material_price, 0)) * t.qty) as gap_benefit_idr'),
                DB::raw('SUM((ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) * t.qty) as gap_kg_total'),
                DB::raw('SUM(CASE WHEN (ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) > 0.001 THEN 1 ELSE 0 END) as merit_count'),
                DB::raw('SUM(CASE WHEN (ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) < -0.001 THEN 1 ELSE 0 END) as loss_count'),
            ])
            ->groupBy(
                'p.part_no', 'p.part_name', 'm.name', 'c.code',
                'vb.weight_kg', 'pd.weight_kg', 'vb.material_price'
            )
            ->get();

        $kpiTotals = [
            'gap_benefit_idr' => 0,
            'gap_kg_total'    => 0,
            'qty_usage'       => 0,
            'merit_count'     => 0,
            'loss_count'      => 0,
        ];
        $itemData = [];
        $modelAgg = [];

        foreach ($rawData as $row) {
            $gapKg    = (float) $row->gap_kg_total;
            $gapIdr   = (float) $row->gap_benefit_idr;
            $qty      = (float) $row->qty_usage;

            $kpiTotals['gap_benefit_idr'] += $gapIdr;
            $kpiTotals['gap_kg_total']    += $gapKg;
            $kpiTotals['qty_usage']       += $qty;
            if ($gapKg > 0.001) $kpiTotals['merit_count']++;
            elseif ($gapKg < -0.001) $kpiTotals['loss_count']++;

            // Aggregate by Model
            if (!isset($modelAgg[$row->model_name])) {
                $modelAgg[$row->model_name] = [
                    'kg'    => 0,
                    'idr'   => 0,
                    'merit' => 0,
                    'loss'  => 0
                ];
            }
            $modelAgg[$row->model_name]['kg']    += $gapKg;
            $modelAgg[$row->model_name]['idr']   += $gapIdr;
            $modelAgg[$row->model_name]['merit'] += (int) $row->merit_count;
            $modelAgg[$row->model_name]['loss']  += (int) $row->loss_count;

            $itemData[] = [
                'part_no'         => $row->part_no,
                'part_name'       => $row->part_name,
                'model_name'      => $row->model_name,
                'customer_code'   => $row->customer_code,
                'plan_kg'         => (float) $row->plan_kg,
                'actual_kg'       => (float) $row->actual_kg,
                'idr_per_kg'      => (float) $row->idr_per_kg,
                'gap_kg_total'    => $gapKg,
                'gap_benefit_idr' => $gapIdr,
                'qty_usage'       => $qty,
            ];
        }

        // Prepare chart data structure
        $chartModels = [
            'labels' => array_keys($modelAgg),
            'idr'    => array_column(array_values($modelAgg), 'idr'),
            'kg'     => array_column(array_values($modelAgg), 'kg'),
            'merit'  => array_column(array_values($modelAgg), 'merit'),
            'loss'   => array_column(array_values($modelAgg), 'loss'),
        ];

        return response()->json([
            'kpi'     => $kpiTotals,
            'models'  => $chartModels,
            'items'   => $itemData,
        ]);
    }

    public function paretoData(Request $request)
    {
        $year       = $request->input('year', date('Y'));
        $month      = $request->input('month');
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
            ->whereNotNull('vb.id');

        if ($month)      $query->whereMonth('t.transaction_date', $month);
        if ($customerId) $query->where('p.customer_id', $customerId);
        if ($modelId)    $query->where('pd.model_id', $modelId);

        $data = $query->select([
                'p.part_no',
                'p.part_name',
                'm.name as model_name',
                'c.code as customer_code',
                DB::raw('ISNULL(vb.weight_kg, 0) as plan_kg'),
                DB::raw('ISNULL(pd.weight_kg, 0) as actual_kg'),
                DB::raw('ISNULL(vb.material_price, 0) as idr_per_kg'),
                DB::raw('SUM(t.qty) as qty_usage'),
                DB::raw('SUM(((ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) * ISNULL(vb.material_price, 0)) * t.qty) as gap_benefit_idr'),
                DB::raw('SUM((ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) * t.qty) as gap_kg_total'),
            ])
            ->groupBy(
                'p.part_no', 'p.part_name', 'm.name', 'c.code',
                'vb.weight_kg', 'pd.weight_kg', 'vb.material_price'
            )
            ->orderByRaw('gap_benefit_idr DESC')
            ->limit($limit)
            ->get();

        $totalAbs = $data->sum(fn($r) => abs((float)$r->gap_benefit_idr));
        $cumulative = 0;
        $result = $data->map(function ($row) use (&$cumulative, $totalAbs) {
            $val = (float) $row->gap_benefit_idr;
            $cumulative += abs($val);
            $cumPct = $totalAbs > 0 ? round(($cumulative / $totalAbs) * 100, 1) : 0;
            return [
                'label'           => $row->part_no . ' (' . $row->model_name . ')',
                'part_no'         => $row->part_no,
                'model_name'      => $row->model_name,
                'gap_kg_total'    => (float) $row->gap_kg_total,
                'gap_benefit_idr' => $val,
                'cumulative_pct'  => $cumPct,
            ];
        });

        return response()->json(['pareto' => $result]);
    }
}
