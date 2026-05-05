<?php

namespace App\Http\Controllers\Inventory\Material;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\Material\InventoryProduct;
use App\Exports\StockMonitoringExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Maatwebsite\Excel\Facades\Excel;

class StockMonitoringController extends Controller
{
    public function index()
    {
        // Get all OUT categories for dynamic columns
        $categories = \App\Models\InventoryModel\Material\TransactionCategory::orderBy('effect', 'desc')
            ->orderBy('name')
            ->get();

        $txSubquery = DB::table('inv_t_inventory_transaction as t')
            ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
            ->select('t.product_detail_id', DB::raw("SUM(CASE WHEN tc.code = 'OUT-TRIAL' THEN t.qty ELSE 0 END) as usage_OUT_TRIAL"))
            ->groupBy('t.product_detail_id');

        // Calculate Stats for KPI
        $products = InventoryProduct::where('inv_t_product_detail.is_active', 1)
            ->leftJoin('inv_m_model_status', 'inv_m_model_status.model_id', '=', 'inv_t_product_detail.model_id')
            ->leftJoin('inv_m_unit as u', 'u.id', '=', 'inv_t_product_detail.unit_id')
            ->leftJoin('inv_m_rank', 'inv_m_rank.id', '=', 'inv_t_product_detail.rank_id')
            ->leftJoinSub($txSubquery, 'tx', 'tx.product_detail_id', '=', 'inv_t_product_detail.id')
            ->select('inv_t_product_detail.*', 'inv_m_model_status.project_status', 'u.code as unit_code', 'u.name as unit_name', 'inv_m_rank.limit_value', 'inv_m_rank.code as rank_code', 'inv_m_rank.process_type', 'tx.usage_OUT_TRIAL')
            ->get();
        
        $stats = [
            'balance' => [
                'total' => $products->count(),
                'safe' => 0,
                'warning' => 0,
                'critical' => 0,
                'over' => 0
            ],
            'usage' => [
                'total' => $products->count(),
                'on_budget' => 0,
                'near_loss' => 0,
                'loss' => 0
            ]
        ];

        foreach ($products as $p) {
            $currentPCS = $p->current_stock_pcs;
            $status = InventoryProduct::calculateStockStatus($currentPCS, $p->min_stock, $p->project_status);
            if (isset($stats['balance'][$status])) {
                $stats['balance'][$status]++;
            }

            // Adjusted Rank Value Logic
            $limitValue = $this->calculateAdjustedRank(
                $p->process_type, 
                $p->limit_value, 
                $p->unit_per_car, 
                $p->pcs_per_unit
            );

            $outTrialPcs = $p->trial_usage_pcs;
            $gap = $limitValue - $outTrialPcs;
            
            if ($gap < 0) {
                $stats['usage']['loss']++;
            } elseif ($gap < 50) {
                $stats['usage']['near_loss']++;
            } else {
                $stats['usage']['on_budget']++;
            }
        }

        // Get Customers for Filter (Only those in product master)
        $customers = DB::table('customers as c')
            ->join('products as p', 'p.customer_id', '=', 'c.id')
            ->join('inv_t_product_detail as pd', 'pd.product_id', '=', 'p.id')
            ->where('pd.is_active', 1)
            ->select('c.id', 'c.code', 'c.name')
            ->distinct()
            ->orderBy('c.code')
            ->get();

        // Get Project Statuses for Filter (Only for models in master)
        $project_statuses = DB::table('inv_m_model_status as ms')
            ->join('inv_t_product_detail as pd', 'pd.model_id', '=', 'ms.model_id')
            ->where('pd.is_active', 1)
            ->whereNotNull('ms.project_status')
            ->distinct()
            ->orderBy('ms.project_status')
            ->pluck('ms.project_status');

        return view('inventory.material.stock_monitoring', compact('categories', 'stats', 'customers', 'project_statuses'));
    }

    public function data(Request $request)
    {
        // 1. Get Dynamic Categories (OUT)
        $categories = \App\Models\InventoryModel\Material\TransactionCategory::orderBy('effect', 'desc')
            ->orderBy('name')
            ->get();

        // 2. Build Sub-query Selects for Transactions
        $txSelects = ['t.product_detail_id'];
        
        // Total In Sum (effect 1)
        $txSelects[] = DB::raw("SUM(CASE WHEN tc.effect = 1 THEN t.qty ELSE 0 END) as total_in_sum");
        
        // Total Out Sum (effect -1)
        $txSelects[] = DB::raw("SUM(CASE WHEN tc.effect = -1 THEN t.qty ELSE 0 END) as total_out_sum");

        // Dynamic Usage Columns
        foreach ($categories as $cat) {
            $alias = 'usage_' . preg_replace('/[^a-zA-Z0-9]/', '_', $cat->code);
            $txSelects[] = DB::raw("SUM(CASE WHEN tc.code = '{$cat->code}' THEN t.qty ELSE 0 END) as {$alias}");
        }

        $txSubquery = DB::table('inv_t_inventory_transaction as t')
            ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
            ->select($txSelects)
            ->groupBy('t.product_detail_id');

        // Supplier from latest OUT-TRIAL transaction subquery
        $latestOutTxSubquery = DB::table('inv_t_inventory_transaction as t')
            ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
            ->leftJoin('inv_m_supplier as s', 's.id', '=', 't.supplier_id')
            ->select('t.product_detail_id', 's.code as supplier_name')
            ->where('tc.code', 'OUT-TRIAL')
            ->whereIn('t.id', function($q) {
                $q->select(DB::raw('MAX(t2.id)'))
                  ->from('inv_t_inventory_transaction as t2')
                  ->join('inv_m_transaction_category as tc2', 'tc2.id', '=', 't2.transaction_category_id')
                  ->where('tc2.code', 'OUT-TRIAL')
                  ->groupBy('t2.product_detail_id');
            });

        // 3. Build Query
        $query = InventoryProduct::query()
            ->join('products', 'products.id', '=', 'inv_t_product_detail.product_id')
            ->leftJoin('models', 'models.id', '=', 'inv_t_product_detail.model_id')
            ->leftJoin('inv_m_model_status', 'inv_m_model_status.model_id', '=', 'models.id')
            ->leftJoin('customers', 'customers.id', '=', 'products.customer_id')
            ->leftJoin('inv_m_material_spec', 'inv_m_material_spec.id', '=', 'inv_t_product_detail.material_spec_id')
            ->leftJoin('inv_m_rank as rank', 'rank.id', '=', 'inv_t_product_detail.rank_id')
            ->leftJoin('inv_m_unit as u', 'u.id', '=', 'inv_t_product_detail.unit_id')
            ->leftJoin('inv_m_revision as rev', 'rev.id', '=', 'inv_t_product_detail.revision_id')
            // Join the aggregated transaction summary
            ->leftJoinSub($txSubquery, 'tx', 'tx.product_detail_id', '=', 'inv_t_product_detail.id')
            // Join latest out transaction for supplier name
            ->leftJoinSub($latestOutTxSubquery, 'latest_out', 'latest_out.product_detail_id', '=', 'inv_t_product_detail.id')
            // Latest STO Join
            ->leftJoin(DB::raw("(SELECT sd.product_detail_id, SUM(sd.diff_qty) as sto_gap 
                         FROM inv_t_sto_detail sd 
                         WHERE sd.event_id = (SELECT TOP 1 id FROM inv_t_sto_event ORDER BY created_at DESC)
                         AND sd.is_adjusted = 1
                         GROUP BY sd.product_detail_id
                        ) as latest_sto"), 'latest_sto.product_detail_id', '=', 'inv_t_product_detail.id')
            // Selects
            ->select([
                'inv_t_product_detail.id',
                'products.part_no',
                'products.part_name',
                'inv_t_product_detail.current_stock_qty',
                'inv_t_product_detail.current_stock_pcs',
                'inv_t_product_detail.trial_usage_qty',
                'inv_t_product_detail.trial_usage_pcs',
                'inv_t_product_detail.pcs_per_unit',
                'inv_t_product_detail.remark',
                'u.code as unit_code',
                'u.name as unit_name',
                'inv_m_material_spec.spec_name',
                'inv_m_material_spec.coating_type',
                'inv_t_product_detail.thickness',
                'inv_t_product_detail.width',
                'inv_t_product_detail.length',
                'inv_t_product_detail.length_2',
                'inv_t_product_detail.pitch',
                'inv_t_product_detail.pcs_per_pitch',
                'inv_t_product_detail.top_coil',
                'inv_t_product_detail.end_coil',
                'inv_t_product_detail.gross_coil',
                'inv_t_product_detail.weight_kg',
                'inv_t_product_detail.product_status',
                'inv_t_product_detail.product_status_remark',
                'inv_m_model_status.project_status as model_project_status',
                'models.name as model_name',
                'customers.code as customer_code',
                'rank.code as rank_code',
                'rank.process_type',
                'rank.limit_value',
                'rev.code as revision',
                'inv_t_product_detail.min_stock',
                'inv_t_product_detail.unit_per_car',
                'inv_t_product_detail.updated_at',
                'latest_sto.sto_gap',
                'inv_t_product_detail.material_price',
                'latest_out.supplier_name',
                'tx.*'
            ]);

        $this->applyQueryFilters($query, $request);

        $recordsTotal = InventoryProduct::where('is_active', 1)->count();
        $filteredRecords = $query->count();

        // Sorting - Map frontend index to backend field accurately
        $sortableColumns = [
            0 => 'inv_t_product_detail.id', // No
            1 => 'models.name',           // Model
            2 => 'part_no',              // Part Information
            3 => 'project_status',        // Status
            4 => 'min_stock',             // Min Stock
            5 => 'balance_pcs',           // Current Balance
        ];
        
        $currentIdx = 6;
        foreach ($categories as $cat) {
            $alias = 'usage_' . preg_replace('/[^a-zA-Z0-9]/', '_', $cat->code);
            $sortableColumns[$currentIdx++] = $alias;
        }
        
        $sortableColumns[$currentIdx++] = 'sto_qty'; // STO GAP
        $sortableColumns[$currentIdx++] = 'action';  // Action
        
        $orderColIdx = $request->input('order.0.column', 1);
        $orderDir = $request->input('order.0.dir', 'asc');
        $orderCol = $sortableColumns[$orderColIdx] ?? 'part_no';

        if ($orderCol === 'models.name') {
            $query->orderBy('models.name', $orderDir);
        } elseif ($orderCol === 'part_no') {
            $query->orderBy('products.part_no', $orderDir);
        } elseif ($orderCol === 'project_status') {
            // Sort by product_status override first, then by model's project_status
            $query->orderBy(DB::raw("COALESCE(inv_t_product_detail.product_status, inv_m_model_status.project_status)"), $orderDir);
        } elseif ($orderCol === 'balance_pcs') {
            $query->orderBy('inv_t_product_detail.current_stock_qty', $orderDir);
        } elseif (strpos($orderCol, 'usage_') === 0) {
            $query->orderBy($orderCol, $orderDir);
        } elseif ($orderCol === 'sto_qty') {
            $query->orderBy('latest_sto.sto_gap', $orderDir);
        } else {
            $query->orderBy('products.part_no', 'asc');
        }

        $perPage = $request->input('length', 10);
        $start = $request->input('start', 0);

        $data = $query->skip($start)->take($perPage)->get();

        // Instantiate Hashids for InventoryProduct
        $salt = config('app.key') . InventoryProduct::class;
        $length = config('hashids.connections.main.length', 10);
        $alphabet = config('hashids.connections.main.alphabet', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
        $hashids = new \Hashids\Hashids($salt, $length, $alphabet);

        // Formatting Data
        $formattedData = $data->map(function ($item) use ($categories, $hashids) {
            $pcsPerUnit = $item->pcs_per_unit ?? 1;

            // Generate HashID
            $hashId = $hashids->encode($item->id);

            // Format Size: T x W x L [/ L2] [(P: pitch)]
            $t = floatval($item->thickness);
            $w = floatval($item->width);
            $l = floatval($item->length);
            $l2 = floatval($item->length_2);
            $p = floatval($item->pitch);
            $weight = floatval($item->weight_kg);
            $unitName = strtolower($item->unit_name ?? '');

            $size = $t;
            if ($w > 0) $size .= ' x ' . $w;
            if ($l > 0) $size .= ' x ' . $l;

            if (($unitName === 'trapezoid') && $l2 > 0) {
                $size .= ' / ' . $l2;
            }

            $sizeFormatted = $size;
            if ($p > 0) $sizeFormatted .= ' (P: ' . $p . ')';
            if ($weight > 0) $sizeFormatted .= ' (Wt: ' . $weight . ' kg)';

            $partNoDisplay = $item->part_no . ($item->revision ? '-' . $item->revision : '');

            $partNoDisplay = $item->part_no . ($item->revision ? '-' . $item->revision : '');

            $calculatedQty = (float)$item->current_stock_qty;
            $inQty = (float)($item->total_in_sum ?? 0);
            $outQty = (float)($item->total_out_sum ?? 0);

            $balancePcsVal = (int)$item->current_stock_pcs;
            $inQtyPcsVal = InventoryProduct::calculatePcs($inQty, $item->weight_kg, $pcsPerUnit, $item->unit_name, $item->top_coil, $item->end_coil, $item->pitch, $item->pcs_per_pitch, $item->gross_coil);
            $outQtyPcsVal = InventoryProduct::calculatePcs($outQty, $item->weight_kg, $pcsPerUnit, $item->unit_name, $item->top_coil, $item->end_coil, $item->pitch, $item->pcs_per_pitch, $item->gross_coil);
            $outTrialPcs = (int)$item->trial_usage_pcs;

            $isCoil = str_contains(strtolower($item->unit_name ?? ''), 'coil') && floatval($item->weight_kg ?? 0) > 0;
            $weight = floatval($item->weight_kg ?? 0);

            $row = [
                'id' => $item->id,
                'hash_id' => $hashId,
                'model_name' => $item->model_name ?? '-',
                'part_no' => $item->part_no, 
                'part_name' => $item->part_name,
                'revision' => $item->revision,
                'remark' => $item->remark ?? '-',
                'balance_pcs'  => number_format($balancePcsVal, 0),
                'balance_unit' => $item->unit_code,
                'current_qty' => $calculatedQty,
                'total_in' => number_format($inQtyPcsVal, 0),
                'total_out' => number_format($outQtyPcsVal, 0),
                'min_stock' => number_format((float)$item->min_stock, 0),
                'sto_gap' => $item->sto_gap,
                'sto_gap_display' => $this->formatStoGap($item->sto_gap, $item, $pcsPerUnit, $isCoil, $weight),
                'sto_gap_plain' => $item->sto_gap !== null ? ($item->sto_gap > 0 ? '+' : '') . number_format(InventoryProduct::calculatePcs($item->sto_gap, $item->weight_kg, $pcsPerUnit, $item->unit_name, $item->top_coil, $item->end_coil, $item->pitch, $item->pcs_per_pitch, $item->gross_coil), 0) : '0',
                // Detailed Info for Hover/Popover
                'details' => [
                    'model' => $item->model_name ?? '-',
                    'customer' => $item->customer_code ?? '-',
                    'rank' => $item->rank_code ?? '-',
                    'limit_value' => $item->limit_value ?? '-',
                    'min_stock' => $item->min_stock ?? '-',
                    'spec' => $item->spec_name ?? '-',
                    'thickness' => (float)$item->thickness,
                    'width' => (float)$item->width,
                    'length' => (float)$item->length,
                    'length_2' => (float)$item->length_2,
                    'pitch' => (float)$item->pitch,
                    'weight' => (float)$item->weight_kg,
                    'unit_name' => strtolower($item->unit_name ?? ''),
                    'remark' => $item->remark ?? '-',
                    'coating_type' => $item->coating_type ?? '-',
                    'unit_per_car' => $item->unit_per_car ?? '-',
                    'last_update' => $item->updated_at ? $item->updated_at->format('d M Y, H:i') : '-',
                    'pcs_per_unit' => $pcsPerUnit
                ],
                'total_amount' => $balancePcsVal * floatval($item->material_price),
                'stock_status' => InventoryProduct::calculateStockStatus($balancePcsVal, $item->min_stock, $item->product_status ?: $item->model_project_status),
                'project_status' => $item->product_status ?: ($item->model_project_status ?? 'Project'),
                
                // Material Usage Fields
                'supplier_name' => $item->supplier_name ?? '-',
            ];
            // Gap and Out Trial logic
            $outTrialPcs = (int)$item->trial_usage_pcs;
            
            // Adjusted Rank Value Logic
            $limitValue = $this->calculateAdjustedRank(
                $item->process_type, 
                $item->limit_value, 
                $item->unit_per_car, 
                $item->pcs_per_unit
            );
            
            $gap = $limitValue - $outTrialPcs;
            
            $row['rank_value'] = ($item->rank_code ?? '-') . ' ' . number_format($limitValue, 0);
            $row['out_trial_value'] = number_format($outTrialPcs, 0);
            $row['gap'] = number_format($gap, 0);
            
            if ($gap < 0) {
                $row['material_usage_status'] = 'Loss';
            } elseif ($gap < 50) {
                $row['material_usage_status'] = 'Near Loss';
            } else {
                $row['material_usage_status'] = 'On Budget';
            }

            // Map Dynamic Columns
            foreach ($categories as $cat) {
                $alias = 'usage_' . preg_replace('/[^a-zA-Z0-9]/', '_', $cat->code);
                $catQty = floatval($item->$alias ?? 0);
                
                // Calculate Pcs for this specific category
                $catPcs = 0;
                if ($catQty != 0) {
                    $catPcs = InventoryProduct::calculatePcs(
                        $catQty, 
                        $item->weight_kg, 
                        $pcsPerUnit, 
                        $item->unit_name,  // Use unit_name (e.g., 'COIL') NOT unit_code (e.g., 'KG') 
                        $item->top_coil, 
                        $item->end_coil, 
                        $item->pitch, 
                        $item->pcs_per_pitch, 
                        $item->gross_coil
                    );
                }

                $row[$alias] = $this->formatCalculatedQty($catQty, $catPcs, $isCoil, $pcsPerUnit);

                if ($cat->is_trial) {
                    $trialQty = abs($catQty); // Unit
                    $limitValue = floatval($item->limit_value);
                    $row['trial_status'] = InventoryProduct::calculateTrialStatus($trialQty, $limitValue, $pcsPerUnit);
                }
            }

            $row['stock_status'] = InventoryProduct::calculateStockStatus($balancePcsVal, $item->min_stock, $item->product_status ?: $item->model_project_status);
            $row['project_status'] = $item->product_status ?: ($item->model_project_status ?? 'Project');

            return $row;
        });

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $filteredRecords,
            'data' => $formattedData
        ]);
    }

    private function formatStoGap($gap, $item, $pcsPerUnit, $isCoil = false, $weightKg = 0)
    {
        if ($gap === null) return '-';
        $gap = floatval($gap);
        if ($gap == 0) return '0';

        $sign = $gap > 0 ? '+' : '';
        $pcs = InventoryProduct::calculatePcs($gap, $weightKg, $pcsPerUnit, $item->unit_name, $item->top_coil, $item->end_coil, $item->pitch, $item->pcs_per_pitch, $item->gross_coil);
        $pcsDisplay = $sign . number_format($pcs, 0);

        if ($pcsPerUnit == 1 && !$isCoil) {
            return "<span class='font-bold'>$pcsDisplay</span>";
        }

        $unitDisplay = $sign . number_format($gap, $isCoil ? 2 : 0);
        return "<span class='font-bold'>$pcsDisplay</span> <span class='text-xs text-gray-400 font-normal'>($unitDisplay)</span>";
    }

    private function formatCalculatedQty($qty, $pcs, $isCoil, $pcsPerUnit)
    {
        if ($qty == 0) return '-';
        
        $pcsDisplay = number_format($pcs, 0);

        if ($pcsPerUnit == 1 && !$isCoil) {
            return "<span class='font-bold'>$pcsDisplay</span>";
        }

        $unitDisplay = number_format($qty, $isCoil ? 2 : 0);
        return "<span class='font-bold'>$pcsDisplay</span> <span class='text-xs text-gray-500'>($unitDisplay)</span>";
    }

    /**
     * Print Label with Balance for the specified resource.
     */
    public function printBalanceLabel($id)
    {
        $inventoryProduct = InventoryProduct::findByHashOrFail($id);
        $data = DB::table('inv_t_product_detail as p')
            ->leftJoin('products as prod', 'prod.id', '=', 'p.product_id')
            ->leftJoin('customers as cust', 'cust.id', '=', 'prod.customer_id')
            ->leftJoin('models as model', 'model.id', '=', 'prod.model_id')
            ->leftJoin('inv_m_material_spec as ms', 'ms.id', '=', 'p.material_spec_id')
            ->leftJoin('inv_m_rank as r', 'r.id', '=', 'p.rank_id')
            ->leftJoin('inv_m_unit as u', 'u.id', '=', 'p.unit_id')
            ->leftJoin('inv_m_revision as rev', 'rev.id', '=', 'p.revision_id')
            ->where('p.id', $inventoryProduct->id)
            ->select([
                'prod.part_no',
                'prod.part_name',
                'cust.code as customer_code',
                'model.name as model_name',
                'rev.code as revision',
                'p.thickness',
                'p.width',
                'p.length',
                'p.length_2',
                'p.pitch',
                'ms.spec_name as material_spec',
                'ms.coating_type',
                'r.code as rank_code',
                'p.weight_kg',
                'p.top_coil',
                'p.end_coil',
                'p.pitch',
                'p.pcs_per_pitch',
                'p.gross_coil',
                'p.current_stock_qty',
                'p.pcs_per_unit',
                'u.code as unit_code',
                'u.name as unit_name'
            ])
            ->first();

        if (!$data) abort(404);
        
        $balancePcs = InventoryProduct::calculatePcs(
            $data->current_stock_qty,
            $data->weight_kg,
            $data->pcs_per_unit,
            $data->unit_name, // Use unit_name (e.g., 'COIL') NOT unit_code (e.g., 'KG')
            $data->top_coil,
            $data->end_coil,
            $data->pitch,
            $data->pcs_per_pitch,
            $data->gross_coil
        );

        $dimVal = [];
        $dimLbl = [];
        if ((float)$data->thickness > 0) { $dimVal[] = (float)$data->thickness; $dimLbl[] = 'T'; }
        if ((float)$data->width > 0) { $dimVal[] = (float)$data->width; $dimLbl[] = 'W'; }
        if ((float)$data->length > 0) { $dimVal[] = (float)$data->length; $dimLbl[] = 'L'; }
        if ((float)$data->length_2 > 0) { $dimVal[] = (float)$data->length_2; $dimLbl[] = 'L2'; }
        if ((float)$data->pitch > 0) { $dimVal[] = (float)$data->pitch; $dimLbl[] = 'P'; }

        $product = (object) [
            'qrcode' => QrCode::size(250)->errorCorrection('M')->margin(1)->generate(route('inventory.scanInfo', $inventoryProduct->hash_id)),
            'item_no' => $data->part_no . ($data->revision ? '-' . $data->revision : ''),
            'item_name' => $data->part_name,
            'model_name' => $data->model_name ?? '-',
            'partner_code' => $data->customer_code ?? '-',
            'dimension' => implode(' x ', $dimVal),
            'dimension_label' => !empty($dimLbl) ? '(' . implode(' x ', $dimLbl) . ')' : '',
            'material' => $data->material_spec . ($data->coating_type ? " ($data->coating_type)" : '')
        ];

        $products = [$product];

        return view('inventory.material.qrcode', compact('products'));
    }
    public function scanInfo($id)
    {
        $inventoryProduct = InventoryProduct::findByHashOrFail($id);
        
        $data = DB::table('inv_t_product_detail as p')
            ->leftJoin('products as prod', 'prod.id', '=', 'p.product_id')
            ->leftJoin('customers as cust', 'cust.id', '=', 'prod.customer_id')
            ->leftJoin('models as model', 'model.id', '=', 'p.model_id')
            ->leftJoin('inv_m_material_spec as ms', 'ms.id', '=', 'p.material_spec_id')
            ->leftJoin('inv_m_unit as u', 'u.id', '=', 'p.unit_id')
            ->leftJoin('inv_m_revision as rev', 'rev.id', '=', 'p.revision_id')
            ->leftJoin('inv_m_model_status as ms_model', 'ms_model.model_id', '=', 'p.model_id')
            ->where('p.id', $inventoryProduct->id)
            ->select([
                'prod.part_no',
                'prod.part_name',
                'cust.code as customer_code',
                'model.name as model_name',
                'rev.code as revision',
                'p.thickness',
                'p.width',
                'p.length',
                'p.length_2',
                'p.pitch',
                'ms.spec_name as material_spec',
                'ms.coating_type',
                'p.weight_kg',
                'p.top_coil',
                'p.end_coil',
                'p.pcs_per_pitch',
                'p.gross_coil',
                'p.current_stock_qty',
                'p.pcs_per_unit',
                'u.code as unit_code',
                'u.name as unit_name',
                'p.min_stock',
                'p.product_status',
                'p.product_status_remark',
                'ms_model.project_status as model_project_status'
            ])
            ->first();

        if (!$data) abort(404);

        $balancePcs = InventoryProduct::calculatePcs(
            $data->current_stock_qty,
            $data->weight_kg,
            $data->pcs_per_unit,
            $data->unit_name, // Use unit_name (e.g., 'COIL') NOT unit_code (e.g., 'KG')
            $data->top_coil,
            $data->end_coil,
            $data->pitch,
            $data->pcs_per_pitch,
            $data->gross_coil
        );

        $status = InventoryProduct::calculateStockStatus(
            $balancePcs, 
            $data->min_stock, 
            $data->product_status ?: ($data->model_project_status ?? 'Project')
        );

        // Format dimension dynamically
        $dimVal = [];
        $dimLbl = [];
        if ((float)$data->thickness > 0) { $dimVal[] = (float)$data->thickness; $dimLbl[] = 'T'; }
        if ((float)$data->width > 0) { $dimVal[] = (float)$data->width; $dimLbl[] = 'W'; }
        if ((float)$data->length > 0) { $dimVal[] = (float)$data->length; $dimLbl[] = 'L'; }
        if ((float)$data->length_2 > 0) { $dimVal[] = (float)$data->length_2; $dimLbl[] = 'L2'; }
        if ((float)$data->pitch > 0) { $dimVal[] = (float)$data->pitch; $dimLbl[] = 'P'; }

        $product = (object) [
            'hash_id' => $id,
            'part_no' => $data->part_no,
            'revision' => $data->revision,
            'part_name' => $data->part_name,
            'model_name' => $data->model_name ?? '-',
            'customer_code' => $data->customer_code ?? '-',
            'dimension' => implode(' x ', $dimVal),
            'dimension_label' => !empty($dimLbl) ? '(' . implode(' x ', $dimLbl) . ')' : '',
            'material' => $data->material_spec . ($data->coating_type ? " ($data->coating_type)" : ''),
            'balance_pcs' => number_format($balancePcs, 0),
            'balance_unit' => number_format($data->current_stock_qty, 0) . ' ' . ($data->unit_code ?? 'PCS'),
            'status' => $status,
            'min_stock' => number_format($data->min_stock, 0),
            'product_status' => $data->product_status,
            'model_project_status' => $data->model_project_status ?: 'Project',
            'product_status_remark' => $data->product_status_remark,
        ];

        // Find active STO
        $activeSto = \App\Models\InventoryModel\Material\StoEvent::where('status', 'OPEN')
            ->orderBy('created_at', 'desc')
            ->first();
        
        $activeStoHashId = $activeSto ? $activeSto->hash_id : null;

        return view('inventory.material.scan_info', compact('product', 'activeStoHashId'));
    }

    public function getStoLog($id)
    {
        $logs = \App\Models\InventoryModel\Material\StoDetail::with(['event', 'auditor'])
            ->where('product_detail_id', $id)
            ->where('is_adjusted', 1)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($log) {
                return [
                    'date' => $log->created_at->format('d M y H:i'),
                    'event' => $log->event->code ?? '-',
                    'system' => (float)$log->system_qty_snapshot,
                    'actual' => (float)$log->real_qty_input,
                    'diff' => (float)$log->diff_qty,
                    'auditor' => $log->auditor ? $log->auditor->name : '-',
                    'remark' => $log->remark ?? '-'
                ];
            });
            
        return response()->json($logs);
    }

    public function exportExcel(Request $request)
    {
        $categories = \App\Models\InventoryModel\Material\TransactionCategory::orderBy('effect', 'desc')
            ->orderBy('name')
            ->get();

        $txSelects = ['t.product_detail_id'];
        $txSelects[] = DB::raw("SUM(CASE WHEN tc.effect = 1 THEN t.qty ELSE 0 END) as total_in_sum");
        $txSelects[] = DB::raw("SUM(CASE WHEN tc.effect = -1 THEN t.qty ELSE 0 END) as total_out_sum");

        foreach ($categories as $cat) {
            $alias = 'usage_' . preg_replace('/[^a-zA-Z0-9]/', '_', $cat->code);
            $txSelects[] = DB::raw("SUM(CASE WHEN tc.code = '{$cat->code}' THEN t.qty ELSE 0 END) as {$alias}");
        }

        $txSubquery = DB::table('inv_t_inventory_transaction as t')
            ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
            ->select($txSelects)
            ->groupBy('t.product_detail_id');

        $query = InventoryProduct::query()
            ->join('products', 'products.id', '=', 'inv_t_product_detail.product_id')
            ->leftJoin('models', 'models.id', '=', 'inv_t_product_detail.model_id')
            ->leftJoin('inv_m_model_status', 'inv_m_model_status.model_id', '=', 'models.id')
            ->leftJoin('customers', 'customers.id', '=', 'products.customer_id')
            ->leftJoin('inv_m_material_spec', 'inv_m_material_spec.id', '=', 'inv_t_product_detail.material_spec_id')
            ->leftJoin('inv_m_rank as rank', 'rank.id', '=', 'inv_t_product_detail.rank_id')
            ->leftJoin('inv_m_unit as u', 'u.id', '=', 'inv_t_product_detail.unit_id')
            ->leftJoin('inv_m_revision as rev', 'rev.id', '=', 'inv_t_product_detail.revision_id')
            ->leftJoinSub($txSubquery, 'tx', 'tx.product_detail_id', '=', 'inv_t_product_detail.id')
            ->leftJoinSub($latestOutTxSubquery, 'latest_out', 'latest_out.product_detail_id', '=', 'inv_t_product_detail.id')
            ->leftJoin(DB::raw("(SELECT sd.product_detail_id, SUM(sd.diff_qty) as sto_gap 
                         FROM inv_t_sto_detail sd 
                         WHERE sd.event_id = (SELECT TOP 1 id FROM inv_t_sto_event ORDER BY created_at DESC)
                         AND sd.is_adjusted = 1
                         GROUP BY sd.product_detail_id
                        ) as latest_sto"), 'latest_sto.product_detail_id', '=', 'inv_t_product_detail.id')
            ->select([
                'inv_t_product_detail.*',
                'products.part_no',
                'products.part_name',
                'models.name as model_name',
                'customers.code as customer_code',
                'u.code as unit_code',
                'u.name as unit_name',
                'inv_m_material_spec.spec_name',
                'inv_m_material_spec.coating_type',
                'rank.code as rank_code',
                'rev.code as revision',
                'inv_m_model_status.project_status as model_project_status',
                'inv_t_product_detail.material_price',
                'latest_sto.sto_gap',
                'latest_out.supplier_name',
                'tx.*'
            ]);

        $this->applyQueryFilters($query, $request);



        $data = $query->orderBy('products.part_no', 'asc')->get();

        $fileNameBase = 'All Stock Monitoring';
        $customerCode = '';
        $modelName = '';

        if ($request->customer_id) {
            $customer = DB::table('customers')->where('id', $request->customer_id)->first();
            if ($customer) {
                $customerCode = $customer->code;
            }
        }

        if ($request->model_id) {
            $model = DB::table('models')->where('id', $request->model_id)->first();
            if ($model) {
                $modelName = $model->name;
                
                // If customer is not filtered, fetch it from model association
                if (!$customerCode) {
                    $customer = DB::table('customers')->where('id', $model->customer_id)->first();
                    if ($customer) {
                        $customerCode = $customer->code;
                    }
                }
            }
        }

        if ($customerCode || $modelName) {
            $parts = array_filter([$customerCode, $modelName]);
            $fileNameBase = implode(' ', $parts) . ' Stock Monitoring';
        }

        $fileNameClean = preg_replace('/[^A-Za-z0-9 _-]/', '_', $fileNameBase);
        $fileName = $fileNameClean . '_' . date('Ymd') . '.xlsx';

        return Excel::download(new StockMonitoringExport($data, $categories), $fileName);
    }

    /**
     * Calculates the final adjusted Rank Value based on Base Rank categories:
     * - Base 1-4: material qty with process start from draw
     * - Base 5-8: material qty with process start from blank
     * - Base 9-12: material qty with process full progressive
     * 
     * Formula:
     * - Base 1-8: Value Rank * Unit/Car
     * - Base 9-12: Value Rank * Unit/Car * Pcs/Unit
     */
    private function applyQueryFilters($query, Request $request)
    {
        // Global Search
        $searchValue = null;
        if ($request->has('search')) {
            if (is_array($request->search) && !empty($request->search['value'])) {
                $searchValue = $request->search['value'];
            } elseif (is_string($request->search) && !empty($request->search)) {
                $searchValue = $request->search;
            }
        }

        if ($searchValue) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('products.part_no', 'like', '%' . $searchValue . '%')
                    ->orWhere('inv_m_material_spec.spec_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('models.name', 'like', '%' . $searchValue . '%')
                    ->orWhereRaw("(products.part_no + '-' + ISNULL(rev.code, '')) LIKE ?", ['%' . $searchValue . '%']);
            });
        }

        // Stock Status Filter
        if ($request->has('stock_status') && !empty($request->stock_status)) {
            $status = $request->stock_status;
            $query->where(function($q) use ($status) {
                $currentPcsSql = \App\Models\InventoryModel\Material\InventoryProduct::getPcsCalculationSql('inv_t_product_detail.current_stock_qty', 'inv_t_product_detail', 'u.name');
                $minSql = "inv_t_product_detail.min_stock";
                
                if ($status === 'over') {
                    $q->whereRaw("{$currentPcsSql} > ({$minSql} * 3)")->where($minSql, '>', 0);
                } elseif ($status === 'critical') {
                    $q->whereRaw("{$currentPcsSql} < ({$minSql} - 30)")->where($minSql, '>', 0);
                } elseif ($status === 'warning') {
                    $q->whereRaw("{$currentPcsSql} >= ({$minSql} - 30)")->whereRaw("{$currentPcsSql} < {$minSql}")->where($minSql, '>', 0);
                } elseif ($status === 'safe') {
                    $q->where(function($sq) use ($currentPcsSql, $minSql) {
                        $sq->whereRaw("{$currentPcsSql} >= {$minSql}")->whereRaw("{$currentPcsSql} <= ({$minSql} * 3)")->orWhere($minSql, '<=', 0);
                    });
                }
            });
        }

        // Usage Status Filter
        if ($request->has('usage_status') && !empty($request->usage_status)) {
            $status = $request->usage_status;
            $limitSql = "
                CASE 
                    WHEN rank.process_type IN ('Draw', 'Blank') THEN CAST(rank.limit_value AS FLOAT) * CAST(ISNULL(inv_t_product_detail.unit_per_car, 1) AS FLOAT)
                    WHEN rank.process_type = 'Full Progressive' THEN CAST(rank.limit_value AS FLOAT) * CAST(ISNULL(inv_t_product_detail.unit_per_car, 1) AS FLOAT) * CAST(ISNULL(inv_t_product_detail.pcs_per_unit, 1) AS FLOAT)
                    ELSE CAST(rank.limit_value AS FLOAT)
                END";
            
            $usagePcsSql = "
                CASE 
                    WHEN LOWER(u.name) LIKE '%coil%' AND ISNULL(inv_t_product_detail.gross_coil, 0) > 0 
                    THEN (ABS(ISNULL(tx.usage_OUT_TRIAL, 0)) / inv_t_product_detail.gross_coil) * COALESCE(inv_t_product_detail.pcs_per_unit, 1)
                    ELSE ABS(ISNULL(tx.usage_OUT_TRIAL, 0)) * COALESCE(inv_t_product_detail.pcs_per_unit, 1)
                END";

            $gapSql = "({$limitSql}) - ({$usagePcsSql})";

            if ($status === 'loss') {
                $query->whereRaw("({$gapSql}) < 0");
            } elseif ($status === 'near_loss') {
                $query->whereRaw("({$gapSql}) >= 0")->whereRaw("({$gapSql}) < 50");
            } elseif ($status === 'on_budget') {
                $query->whereRaw("({$gapSql}) >= 50");
            }
        }

        // Customer, Model, Project Status Filters
        if ($request->filled('customer_id')) $query->where('products.customer_id', $request->customer_id);
        if ($request->filled('model_id')) $query->where('inv_t_product_detail.model_id', $request->model_id);
        
        if ($request->filled('project_status')) {
            $ps = $request->project_status;
            if ($ps === 'Project') {
                $query->where(fn($q) => $q->where('inv_m_model_status.project_status', 'Project')->orWhereNull('inv_m_model_status.project_status'));
            } else {
                $query->where('inv_m_model_status.project_status', $ps);
            }
        }
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
        
        return $limitValue; // Fallback to raw value
    }
}
