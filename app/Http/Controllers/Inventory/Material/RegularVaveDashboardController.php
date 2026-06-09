<?php

namespace App\Http\Controllers\Inventory\Material;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegularVaveDashboardController extends Controller
{
    /**
     * Render the Regular Model VAVE Analysis Dashboard view.
     */
    public function index()
    {
        $versions = DB::table('inv_m_vave_base')->where('base_name', 'like', 'SQ%')->distinct()->pluck('base_name');
        return view('inventory.material.vave.regular-dashboard', compact('versions'));
    }

    /**
     * Get monthly Gap Benefit chart data specifically for Regular Models.
     */
    public function chartData(Request $request)
    {
        $mode       = $request->input('mode', 'monthly');
        $year       = (int) $request->input('year', date('Y'));
        $month      = $request->input('month'); 
        $customerId = $request->input('customer_id');
        $modelId    = $request->input('model_id');
        $sqVersion  = $request->input('sq_version');

        $startYear = ($mode === 'comparison') ? $year - 4 : $year;
        $endYear   = $year;

        // 1. Fetch ALL shipments for the range in ONE query for performance and consistency
        $epicorShipmentsQuery = DB::connection('second_db')->table('erp.ShipDtl as a')
            ->join('erp.ShipHead as b', 'b.PackNum', '=', 'a.PackNum')
            ->whereBetween(DB::raw('YEAR(b.ShipDate)'), [$startYear, $endYear])
            ->select([
                'a.PartNum',
                DB::raw('YEAR(b.ShipDate) as ship_year'),
                DB::raw('MONTH(b.ShipDate) as ship_month'),
                DB::raw('SUM(a.OurInventoryShipQty) as total_qty')
            ])
            ->groupBy('a.PartNum', DB::raw('YEAR(b.ShipDate)'), DB::raw('MONTH(b.ShipDate)'));

        $epicorRawData = $epicorShipmentsQuery->get();
        
        // Organize Epicor data: [PartNum][Year][Month] = Qty
        $shipMap = [];
        $shipYearTotal = []; // [PartNum][Year] = Total Qty
        foreach ($epicorRawData as $row) {
            $pn = trim($row->PartNum);
            $y  = (int) $row->ship_year;
            $m  = (int) $row->ship_month;
            $q  = (float) $row->total_qty;

            $shipMap[$pn][$y][$m] = ($shipMap[$pn][$y][$m] ?? 0) + $q;
            $shipYearTotal[$pn][$y] = ($shipYearTotal[$pn][$y] ?? 0) + $q;
        }

        $comparisonTrend = [];
        if ($mode === 'comparison') {
            for ($y = $startYear; $y <= $endYear; $y++) {
                // Fetch baselines for THIS year. 
                // FALLBACK: If no baseline is active for $y, use the oldest one (to show theoretical benefit for historical shipments)
                $yearlyBaselines = DB::table('inv_m_vave_base as vb')
                    ->join('products as p', 'p.id', '=', 'vb.product_id')
                    ->join('inv_t_product_detail as pd', 'pd.product_id', '=', 'p.id')
                    ->join('models as m', 'm.id', '=', 'pd.model_id')
                    ->join('inv_m_model_status as ms', 'm.id', '=', 'ms.model_id')
                    ->join(DB::raw("(
                        SELECT product_id, 
                               COALESCE(
                                   (SELECT MAX(id) FROM inv_m_vave_base WHERE product_id = b1.product_id AND ((effective_from <= $y AND (effective_to IS NULL OR effective_to >= $y)) OR (effective_from IS NULL AND effective_to IS NULL))" . ($sqVersion ? " AND base_name = '$sqVersion'" : " AND base_name LIKE 'SQ%'") . "),
                                   (SELECT MIN(id) FROM inv_m_vave_base WHERE product_id = b1.product_id" . ($sqVersion ? " AND base_name = '$sqVersion'" : " AND base_name LIKE 'SQ%'") . ")
                               ) as matched_id
                        FROM inv_m_vave_base b1
                        GROUP BY product_id
                    ) as latest_ebd"), 'latest_ebd.matched_id', '=', 'vb.id')
                    ->where('p.is_delete', 0)
                    ->where('pd.is_active', 1)
                    ->where('ms.project_status', 'Regular');

                if ($customerId) $yearlyBaselines->where('p.customer_id', $customerId);
                if ($modelId)    $yearlyBaselines->where('pd.model_id', $modelId);

                $baselines = $yearlyBaselines->select([
                    'p.part_no', 'pd.partno_epicor', 'vb.weight_kg as plan_kg', 'pd.weight_kg as actual_kg', 'vb.material_price as idr_per_kg'
                ])->get();

                $totalBenefit = 0;
                $totalKg = 0;

                foreach ($baselines as $row) {
                    $qty = 0;
                    if ($row->partno_epicor) {
                        $epicorPart = trim($row->partno_epicor);
                        $qty = (float) ($shipYearTotal[$epicorPart][$y] ?? 0);
                    }
                    if ($qty <= 0) {
                        $pn = trim($row->part_no);
                        $qty = (float) ($shipYearTotal[$pn][$y] ?? $shipYearTotal[$pn . '-R'][$y] ?? 0);
                    }
                    
                    $weightGap = (float) $row->plan_kg - (float) $row->actual_kg;
                    if ($weightGap > 0 && $qty > 0) {
                        $totalBenefit += $weightGap * (float) $row->idr_per_kg * $qty;
                        $totalKg += $weightGap * $qty;
                    }
                }

                $comparisonTrend[] = [
                    'year' => $y,
                    'gap_benefit_idr' => $totalBenefit,
                    'gap_kg_total' => $totalKg,
                ];
            }
        }

        // Base Baseline Query for the selected YEAR
        $baselinesQuery = DB::table('inv_m_vave_base as vb')
            ->join('products as p', 'p.id', '=', 'vb.product_id')
            ->join('inv_t_product_detail as pd', 'pd.product_id', '=', 'p.id')
            ->join('models as m', 'm.id', '=', 'pd.model_id')
            ->join('inv_m_model_status as ms', 'm.id', '=', 'ms.model_id')
            ->join('customers as c', 'c.id', '=', 'p.customer_id')
            ->join(DB::raw("(
                SELECT product_id, 
                       COALESCE(
                           (SELECT MAX(id) FROM inv_m_vave_base WHERE product_id = b2.product_id AND ((effective_from <= $year AND (effective_to IS NULL OR effective_to >= $year)) OR (effective_from IS NULL AND effective_to IS NULL))" . ($sqVersion ? " AND base_name = '$sqVersion'" : " AND base_name LIKE 'SQ%'") . "),
                           (SELECT MIN(id) FROM inv_m_vave_base WHERE product_id = b2.product_id" . ($sqVersion ? " AND base_name = '$sqVersion'" : " AND base_name LIKE 'SQ%'") . ")
                       ) as matched_id
                FROM inv_m_vave_base b2
                GROUP BY product_id
            ) as latest_ebd"), 'latest_ebd.matched_id', '=', 'vb.id')
            ->where('p.is_delete', 0)
            ->where('pd.is_active', 1)
            ->where('ms.project_status', 'Regular');

        if ($customerId) $baselinesQuery->where('p.customer_id', $customerId);
        if ($modelId)    $baselinesQuery->where('pd.model_id', $modelId);

        $baselines = $baselinesQuery->select([
            'p.part_no', 'pd.partno_epicor', 'p.part_name', 'm.name as model_name', 'c.code as customer_code', 'vb.base_name as ebd_version',
            'vb.weight_kg as plan_kg', 'pd.weight_kg as actual_kg', 'vb.material_price as idr_per_kg'
        ])->get();

        $allPartNums = [];
        foreach ($baselines as $row) {
            $allPartNums[] = $row->part_no;
            $allPartNums[] = $row->part_no . '-R';
            if ($row->partno_epicor) {
                $allPartNums[] = trim($row->partno_epicor);
            }
        }
        $allPartNums = array_unique(array_filter($allPartNums));
        $epicorPrices = $this->fetchEpicorPrices($allPartNums);

        $kpiTotals = [
            'gap_benefit_idr' => 0, 'gap_kg_total' => 0, 'qty_usage' => 0,
            'merit_count' => 0, 'loss_count' => 0, 'plan_total_cost' => 0,
        ];
        $itemData = [];
        $modelAgg = [];
        $monthlyTrend = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyTrend[$i] = ['month_num' => $i, 'gap_benefit_idr' => 0, 'gap_kg_total' => 0, 'qty_usage' => 0];
        }

        foreach ($baselines as $row) {
            $monthlyQtys = [];
            if ($row->partno_epicor) {
                $epicorPart = trim($row->partno_epicor);
                $monthlyQtys = $shipMap[$epicorPart][$year] ?? [];
            }
            if (empty($monthlyQtys)) {
                $pn = trim($row->part_no);
                $monthlyQtys = $shipMap[$pn][$year] ?? $shipMap[$pn . '-R'][$year] ?? [];
            }
            
            $weightGap = (float) $row->plan_kg - (float) $row->actual_kg;
            
            $price = null;
            if ($row->partno_epicor) {
                $price = $this->getEpicorPriceForPart($row->partno_epicor, $epicorPrices);
            }
            if ($price === null) {
                $price = $this->getEpicorPriceForPart($row->part_no, $epicorPrices);
            }
            if ($price === null) {
                $price = (float) $row->idr_per_kg;
            }
            
            $totalPartQty = 0;
            $totalPartBenefit = 0;
            $totalPartKg = 0;

            foreach ($monthlyQtys as $m => $qty) {
                if ($weightGap > 0) {
                    $benefit = $weightGap * $price * $qty;
                    $kgGap = $weightGap * $qty;

                    // Trend always shows everything in the map for the year
                    $monthlyTrend[$m]['gap_benefit_idr'] += $benefit;
                    $monthlyTrend[$m]['gap_kg_total'] += $kgGap;
                    $monthlyTrend[$m]['qty_usage'] += $qty;

                    // KPI and Part totals only if it matches selected month (or no month selected)
                    if (!$month || $m == $month) {
                        $totalPartQty += $qty;
                        $totalPartBenefit += $benefit;
                        $totalPartKg += $kgGap;
                    }
                }
            }

            if ($totalPartQty > 0 || $totalPartBenefit != 0) {
                $kpiTotals['gap_benefit_idr'] += $totalPartBenefit;
                $kpiTotals['gap_kg_total']    += $totalPartKg;
                $kpiTotals['qty_usage']       += $totalPartQty;
                $kpiTotals['plan_total_cost'] += ($row->plan_kg * $price * $totalPartQty);
                
                if ($weightGap > 0) $kpiTotals['merit_count']++;
                else if ($weightGap < 0) $kpiTotals['loss_count']++;

                if (!isset($modelAgg[$row->model_name])) {
                    $modelAgg[$row->model_name] = ['kg' => 0, 'idr' => 0, 'merit' => 0, 'loss' => 0, 'plan_cost' => 0];
                }
                $modelAgg[$row->model_name]['kg']    += $totalPartKg;
                $modelAgg[$row->model_name]['idr']   += $totalPartBenefit;
                $modelAgg[$row->model_name]['plan_cost'] += ($row->plan_kg * $price * $totalPartQty);
                if ($weightGap > 0) $modelAgg[$row->model_name]['merit']++;
                else if ($weightGap < 0) $modelAgg[$row->model_name]['loss']++;

                $itemData[] = [
                    'part_no'         => $row->part_no,
                    'part_name'       => $row->part_name,
                    'model_name'      => $row->model_name,
                    'customer_code'   => $row->customer_code,
                    'plan_kg'         => (float) $row->plan_kg,
                    'actual_kg'       => (float) $row->actual_kg,
                    'idr_per_kg'      => $price,
                    'gap_kg_total'    => $totalPartKg,
                    'gap_benefit_idr' => $totalPartBenefit,
                    'qty_usage'       => $totalPartQty,
                    'sq_version'      => $row->ebd_version,
                ];
            }
        }

        $trendData = array_values($monthlyTrend);

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
        $sqVersion  = $request->input('sq_version');
        $limit      = (int) $request->input('limit', 20);

        // Fetch Ship Quantities from Epicor for this year
        $epicorData = DB::connection('second_db')->table('erp.ShipDtl as a')
            ->join('erp.ShipHead as b', 'b.PackNum', '=', 'a.PackNum')
            ->where(DB::raw('YEAR(b.ShipDate)'), $year)
            ->when($month, fn($q) => $q->where(DB::raw('MONTH(b.ShipDate)'), $month))
            ->select([
                'a.PartNum',
                DB::raw('SUM(a.OurInventoryShipQty) as total_qty')
            ])
            ->groupBy('a.PartNum')
            ->get()
            ->pluck('total_qty', 'PartNum')
            ->toArray();

        // Base Baselines Query with fallback logic
        $baselinesQuery = DB::table('inv_m_vave_base as vb')
            ->join('products as p', 'p.id', '=', 'vb.product_id')
            ->join('inv_t_product_detail as pd', 'pd.product_id', '=', 'p.id')
            ->join('models as m', 'm.id', '=', 'pd.model_id')
            ->join('inv_m_model_status as ms', 'm.id', '=', 'ms.model_id')
            ->join(DB::raw("(
                SELECT product_id, 
                       COALESCE(
                           (SELECT MAX(id) FROM inv_m_vave_base WHERE product_id = b3.product_id AND ((effective_from <= $year AND (effective_to IS NULL OR effective_to >= $year)) OR (effective_from IS NULL AND effective_to IS NULL))" . ($sqVersion ? " AND base_name = '$sqVersion'" : " AND base_name LIKE 'SQ%'") . "),
                           (SELECT MIN(id) FROM inv_m_vave_base WHERE product_id = b3.product_id" . ($sqVersion ? " AND base_name = '$sqVersion'" : " AND base_name LIKE 'SQ%'") . ")
                       ) as matched_id
                FROM inv_m_vave_base b3
                GROUP BY product_id
            ) as latest_ebd"), 'latest_ebd.matched_id', '=', 'vb.id')
            ->where('p.is_delete', 0)
            ->where('pd.is_active', 1)
            ->where('ms.project_status', 'Regular');

        if ($customerId) $baselinesQuery->where('p.customer_id', $customerId);
        if ($modelId)    $baselinesQuery->where('pd.model_id', $modelId);

        $by = $request->input('by', 'part');
        $labelColumn = $by === 'part' ? 'p.part_no' : 'm.name';
        $baselines = $baselinesQuery->select([
                DB::raw("$labelColumn as label_name"),
                'p.part_no',
                'pd.partno_epicor',
                'vb.weight_kg as plan_kg',
                'pd.weight_kg as actual_kg',
                'vb.material_price as idr_per_kg'
            ])->get();

        $allPartNums = [];
        foreach ($baselines as $row) {
            $allPartNums[] = $row->part_no;
            $allPartNums[] = $row->part_no . '-R';
            if ($row->partno_epicor) {
                $allPartNums[] = trim($row->partno_epicor);
            }
        }
        $allPartNums = array_unique(array_filter($allPartNums));
        $epicorPrices = $this->fetchEpicorPrices($allPartNums);

        $aggData = [];
        foreach ($baselines as $row) {
            $qty = 0;
            if ($row->partno_epicor) {
                $epicorPart = trim($row->partno_epicor);
                $qty = (float) ($epicorData[$epicorPart] ?? 0);
            }
            if ($qty <= 0) {
                $pn = trim($row->part_no);
                $qty = (float) ($epicorData[$pn] ?? $epicorData[$pn . '-R'] ?? 0);
            }
            
            $weightGap = (float) $row->plan_kg - (float) $row->actual_kg;
            
            $price = null;
            if ($row->partno_epicor) {
                $price = $this->getEpicorPriceForPart($row->partno_epicor, $epicorPrices);
            }
            if ($price === null) {
                $price = $this->getEpicorPriceForPart($row->part_no, $epicorPrices);
            }
            if ($price === null) {
                $price = (float) $row->idr_per_kg;
            }
            
            if ($weightGap > 0 && $qty > 0) {
                $benefit = $weightGap * $price * $qty;
                $kgGap = $weightGap * $qty;

                if (!isset($aggData[$row->label_name])) {
                    $aggData[$row->label_name] = ['label_name' => $row->label_name, 'gap_benefit_idr' => 0, 'gap_kg_total' => 0];
                }
                $aggData[$row->label_name]['gap_benefit_idr'] += $benefit;
                $aggData[$row->label_name]['gap_kg_total'] += $kgGap;
            }
        }

        $data = collect(array_values($aggData))->sortByDesc('gap_benefit_idr')->take($limit)->values();

        $totalAbs = $data->sum(fn($r) => abs((float)$r['gap_benefit_idr']));
        $cumulative = 0;
        $result = $data->map(function ($row) use (&$cumulative, $totalAbs) {
            $val = (float) $row['gap_benefit_idr'];
            $cumulative += abs($val);
            return [
                'label'           => $row['label_name'],
                'gap_kg_total'    => (float) $row['gap_kg_total'],
                'gap_benefit_idr' => $val,
                'cumulative_pct'  => $totalAbs > 0 ? round(($cumulative / $totalAbs) * 100, 1) : 0,
            ];
        });

        return response()->json(['pareto' => $result->values()]);
    }

    private function fetchEpicorPrices($partNumbers = [])
    {
        try {
            $queryStr = "
                WITH PriceLatest AS (
                    select 
                        a.PartNum, 
                        a.BaseUnitPrice, 
                        a.PUM, 
                        e.ConvFactor,
                        ROW_NUMBER() OVER (PARTITION BY a.PartNum ORDER BY a.EffectiveDate DESC) as RowNum
                    from erp.VendPart a
                    left join erp.part c on c.PartNum = a.PartNum
                    left join erp.UOMClass d on d.UOMClassID = c.UOMClassID
                    left join erp.UOMConv e on e.UOMClassID = d.UOMClassID and e.UOMCode = a.PUM
            ";

            if (!empty($partNumbers)) {
                $placeholders = implode(',', array_fill(0, count($partNumbers), '?'));
                $queryStr .= " where a.PartNum IN ($placeholders) ";
                $params = $partNumbers;
            } else {
                $queryStr .= " where a.PartNum like '%-R%' ";
                $params = [];
            }

            $queryStr .= "
                )
                SELECT * FROM PriceLatest WHERE RowNum = 1
            ";

            $epicorDataRaw = DB::connection('second_db')->select($queryStr, $params);
            $epicorData = [];
            foreach ($epicorDataRaw as $row) {
                $epicorData[trim($row->PartNum)] = $row;
            }
            return $epicorData;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getEpicorPriceForPart($partNo, $epicorData)
    {
        $partNoTrim = trim($partNo);
        $epi = $epicorData[$partNoTrim] ?? null;

        if (!$epi) {
            $lookupBase = $partNoTrim . '-R';
            $epi = $epicorData[$lookupBase] ?? null;
            
            if (!$epi) {
                $pattern = '/^' . preg_quote($lookupBase, '/') . '\d*$/';
                foreach ($epicorData as $epiPn => $epiRow) {
                    if (preg_match($pattern, $epiPn)) {
                        $epi = $epiRow;
                        break;
                    }
                }
            }
        }

        if ($epi) {
            $rawPrice = (float) $epi->BaseUnitPrice;
            $convFactor = $epi->ConvFactor ? round((float) $epi->ConvFactor, 3) : 0;
            $pum = trim($epi->PUM);
            
            if ($pum === 'SHEET' && $convFactor > 0) {
                return ceil($rawPrice / $convFactor);
            } elseif ($pum === 'KG') {
                return $rawPrice;
            }
        }
        return null;
    }
}
