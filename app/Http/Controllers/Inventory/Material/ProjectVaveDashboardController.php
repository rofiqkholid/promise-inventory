<?php

namespace App\Http\Controllers\Inventory\Material;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectVaveDashboardController extends Controller
{
    /**
     * Render the Project Model VAVE Analysis Dashboard view.
     */
    public function index()
    {
        $versions = DB::table('inv_m_vave_base')->where('base_name', 'like', 'EBD%')->distinct()->pluck('base_name');
        return view('inventory.material.vave.project-dashboard', compact('versions'));
    }

    /**
     * Get monthly Gap Benefit chart data specifically for Project Models.
     */
    public function chartData(Request $request)
    {
        $mode       = $request->input('mode', 'monthly');
        $year       = (int) $request->input('year', date('Y'));
        $month      = $request->input('month'); 
        $customerId = $request->input('customer_id');
        $modelId    = $request->input('model_id');
        $ebdVersion = $request->input('ebd_version');

        $comparisonTrend = [];
        if ($mode === 'comparison') {
            $startYear = $year - 4;
            $endYear = $year;
            
            for ($y = $startYear; $y <= $endYear; $y++) {
                $yearlyBenefit = DB::table('inv_t_inventory_transaction as t')
                    ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
                    ->join('inv_t_product_detail as pd', 'pd.id', '=', 't.product_detail_id')
                    ->join('products as p', 'p.id', '=', 'pd.product_id')
                    ->join('models as m', 'm.id', '=', 'pd.model_id')
                    ->leftJoin('inv_m_model_status as ms', 'm.id', '=', 'ms.model_id') // Left join status table
                    ->leftJoin(DB::raw('(
                        SELECT product_id, MAX(id) as latest_id 
                        FROM inv_m_vave_base 
                        WHERE ((effective_from <= ' . $y . ' AND (effective_to IS NULL OR effective_to >= ' . $y . '))
                           OR (effective_from IS NULL AND effective_to IS NULL))' . ($ebdVersion ? " AND base_name = '" . $ebdVersion . "'" : " AND base_name LIKE 'EBD%'") . '
                        GROUP BY product_id
                    ) as latest_ebd'), 'latest_ebd.product_id', '=', 'p.id')
                    ->leftJoin('inv_m_vave_base as vb', 'vb.id', '=', 'latest_ebd.latest_id')
                    ->where('tc.effect', 1)
                    ->whereYear('t.transaction_date', $y)
                    ->where('p.is_delete', 0)
                    ->where(function($q) {
                        $q->where('ms.project_status', 'Project')
                          ->orWhereNull('ms.project_status');
                    })
                    ->whereNotNull('vb.id');

                if ($customerId) $yearlyBenefit->where('p.customer_id', $customerId);
                if ($modelId)    $yearlyBenefit->where('pd.model_id', $modelId);

                $res = $yearlyBenefit->select([
                    DB::raw('SUM(CASE WHEN (ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) > 0 THEN ((ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) * ISNULL(vb.material_price, 0)) * t.qty ELSE 0 END) as gap_benefit_idr'),
                    DB::raw('SUM(CASE WHEN (ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) > 0 THEN (ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) * t.qty ELSE 0 END) as gap_kg_total'),
                ])->first();

                $comparisonTrend[] = [
                    'year' => $y,
                    'gap_benefit_idr' => (float) ($res->gap_benefit_idr ?? 0),
                    'gap_kg_total' => (float) ($res->gap_kg_total ?? 0),
                ];
            }
        }

        // Base Query
        $baseQuery = DB::table('inv_t_inventory_transaction as t')
            ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
            ->join('inv_t_product_detail as pd', 'pd.id', '=', 't.product_detail_id')
            ->join('products as p', 'p.id', '=', 'pd.product_id')
            ->join('models as m', 'm.id', '=', 'pd.model_id')
            ->leftJoin('inv_m_model_status as ms', 'm.id', '=', 'ms.model_id') // Left join status table
            ->join('customers as c', 'c.id', '=', 'p.customer_id')
            ->leftJoin(DB::raw('(
                SELECT product_id, MAX(id) as latest_id 
                FROM inv_m_vave_base 
                WHERE ((effective_from <= ' . $year . ' AND (effective_to IS NULL OR effective_to >= ' . $year . '))
                   OR (effective_from IS NULL AND effective_to IS NULL))' . ($ebdVersion ? " AND base_name = '" . $ebdVersion . "'" : " AND base_name LIKE 'EBD%'") . '
                GROUP BY product_id
            ) as latest_ebd'), 'latest_ebd.product_id', '=', 'p.id')
            ->leftJoin('inv_m_vave_base as vb', 'vb.id', '=', 'latest_ebd.latest_id')
            ->where('tc.effect', 1)
            ->whereYear('t.transaction_date', $year)
            ->where('p.is_delete', 0)
            ->where(function($q) {
                $q->where('ms.project_status', 'Project')
                  ->orWhereNull('ms.project_status');
            })
            ->whereNotNull('vb.id');

        if ($customerId) $baseQuery->where('p.customer_id', $customerId);
        if ($modelId)    $baseQuery->where('pd.model_id', $modelId);

        // 1. DATA FOR TREND
        $trendData = (clone $baseQuery)
            ->select([
                DB::raw('MONTH(t.transaction_date) as month_num'),
                DB::raw('SUM(CASE WHEN (ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) > 0 THEN ((ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) * ISNULL(vb.material_price, 0)) * t.qty ELSE 0 END) as gap_benefit_idr'),
                DB::raw('SUM(CASE WHEN (ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) > 0 THEN (ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) * t.qty ELSE 0 END) as gap_kg_total'),
                DB::raw('SUM(t.qty) as qty_usage'),
            ])
            ->groupBy(DB::raw('MONTH(t.transaction_date)'))
            ->get();

        // DATA FOR MODELS
        $periodQuery = clone $baseQuery;
        if ($month) {
            $periodQuery->whereMonth('t.transaction_date', $month);
        }

        $rawData = $periodQuery->select([
                'p.part_no',
                'p.part_name',
                'm.name as model_name',
                'c.code as customer_code',
                'vb.base_name as ebd_version',
                DB::raw('ISNULL(vb.weight_kg, 0) as plan_kg'),
                DB::raw('ISNULL(pd.weight_kg, 0) as actual_kg'),
                DB::raw('ISNULL(vb.material_price, 0) as idr_per_kg'),
                DB::raw('SUM(t.qty) as qty_usage'),
                DB::raw('SUM(CASE WHEN (ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) > 0 THEN ((ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) * ISNULL(vb.material_price, 0)) * t.qty ELSE 0 END) as gap_benefit_idr'),
                DB::raw('SUM(CASE WHEN (ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) > 0 THEN (ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) * t.qty ELSE 0 END) as gap_kg_total'),
                DB::raw('SUM(ISNULL(vb.weight_kg, 0) * ISNULL(vb.material_price, 0) * t.qty) as plan_total_cost'),
                DB::raw('COUNT(CASE WHEN (ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) > 0 THEN 1 END) as merit_count'),
                DB::raw('COUNT(CASE WHEN (ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) < 0 THEN 1 END) as loss_count')
            ])
            ->groupBy(
                'p.part_no', 'p.part_name', 'm.name', 'c.code', 'vb.base_name',
                'vb.weight_kg', 'pd.weight_kg', 'vb.material_price'
            )
            ->whereRaw('(ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) > 0')
            ->get();

        $kpiTotals = [
            'gap_benefit_idr' => 0,
            'gap_kg_total'    => 0,
            'qty_usage'       => 0,
            'merit_count'     => 0,
            'loss_count'      => 0,
            'plan_total_cost' => 0,
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
            $kpiTotals['merit_count']     += (int) $row->merit_count;
            $kpiTotals['loss_count']      += (int) $row->loss_count;
            $kpiTotals['plan_total_cost'] += (float) $row->plan_total_cost;

            if (!isset($modelAgg[$row->model_name])) {
                $modelAgg[$row->model_name] = ['kg' => 0, 'idr' => 0, 'merit' => 0, 'loss' => 0, 'plan_cost' => 0];
            }
            $modelAgg[$row->model_name]['kg']    += $gapKg;
            $modelAgg[$row->model_name]['idr']   += $gapIdr;
            $modelAgg[$row->model_name]['merit'] += (int) $row->merit_count;
            $modelAgg[$row->model_name]['loss']  += (int) $row->loss_count;
            $modelAgg[$row->model_name]['plan_cost'] += (float) $row->plan_total_cost;

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
                'ebd_version'     => $row->ebd_version,
            ];
        }

        $chartModels = [
            'labels' => array_keys($modelAgg),
            'idr'    => array_column(array_values($modelAgg), 'idr'),
            'kg'     => array_column(array_values($modelAgg), 'kg'),
            'merit'  => array_column(array_values($modelAgg), 'merit'),
            'loss'   => array_column(array_values($modelAgg), 'loss'),
            'plan_cost' => array_column(array_values($modelAgg), 'plan_cost'),
        ];
        
        $kpiTotals['saving_rate'] = $kpiTotals['plan_total_cost'] > 0 
            ? ($kpiTotals['gap_benefit_idr'] / $kpiTotals['plan_total_cost']) * 100 
            : 0;

        return response()->json([
            'kpi'        => $kpiTotals,
            'models'     => $chartModels,
            'items'      => $itemData,
            'trend'      => $trendData,
            'comparison' => $comparisonTrend
        ]);
    }

    public function paretoData(Request $request)
    {
        $year       = $request->input('year', date('Y'));
        $month      = $request->input('month');
        $customerId = $request->input('customer_id');
        $modelId    = $request->input('model_id');
        $ebdVersion = $request->input('ebd_version');
        $limit      = (int) $request->input('limit', 20);

        $query = DB::table('inv_t_inventory_transaction as t')
            ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
            ->join('inv_t_product_detail as pd', 'pd.id', '=', 't.product_detail_id')
            ->join('products as p', 'p.id', '=', 'pd.product_id')
            ->join('models as m', 'm.id', '=', 'pd.model_id')
            ->leftJoin('inv_m_model_status as ms', 'm.id', '=', 'ms.model_id') // Left join status table
            ->join('customers as c', 'c.id', '=', 'p.customer_id')
            ->leftJoin(DB::raw('(
                SELECT product_id, MAX(id) as latest_id 
                FROM inv_m_vave_base 
                WHERE ((effective_from <= ' . (int)$year . ' AND (effective_to IS NULL OR effective_to >= ' . (int)$year . '))
                   OR (effective_from IS NULL AND effective_to IS NULL))' . ($ebdVersion ? " AND base_name = '" . $ebdVersion . "'" : " AND base_name LIKE 'EBD%'") . '
                GROUP BY product_id
            ) as latest_ebd'), 'latest_ebd.product_id', '=', 'p.id')
            ->leftJoin('inv_m_vave_base as vb', 'vb.id', '=', 'latest_ebd.latest_id')
            ->where('tc.effect', 1)
            ->whereYear('t.transaction_date', $year)
            ->where('p.is_delete', 0)
            ->where(function($q) {
                $q->where('ms.project_status', 'Project')
                  ->orWhereNull('ms.project_status');
            })
            ->whereNotNull('vb.id');

        if ($month)      $query->whereMonth('t.transaction_date', $month);
        if ($customerId) $query->where('p.customer_id', $customerId);
        if ($modelId)    $query->where('pd.model_id', $modelId);

        $labelColumn = empty($modelId) ? 'm.name' : 'p.part_no';

        $data = $query->select([
                DB::raw("$labelColumn as label_name"),
                DB::raw('SUM(CASE WHEN (ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) > 0 THEN ((ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) * ISNULL(vb.material_price, 0)) * t.qty ELSE 0 END) as gap_benefit_idr'),
                DB::raw('SUM(CASE WHEN (ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) > 0 THEN (ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) * t.qty ELSE 0 END) as gap_kg_total'),
            ])
            ->groupBy(DB::raw($labelColumn))
            ->whereRaw('(ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) > 0')
            ->orderBy('gap_benefit_idr', 'desc')
            ->limit($limit)
            ->get();

        $totalAbs = $data->sum(fn($r) => abs((float)$r->gap_benefit_idr));
        $cumulative = 0;
        $result = $data->map(function ($row) use (&$cumulative, $totalAbs) {
            $val = (float) $row->gap_benefit_idr;
            $cumulative += abs($val);
            return [
                'label'           => $row->label_name,
                'gap_kg_total'    => (float) $row->gap_kg_total,
                'gap_benefit_idr' => $val,
                'cumulative_pct'  => $totalAbs > 0 ? round(($cumulative / $totalAbs) * 100, 1) : 0,
            ];
        });

        return response()->json(['pareto' => $result]);
    }
}
