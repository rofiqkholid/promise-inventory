<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\InventoryProduct;
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
        $categories = \App\Models\InventoryModel\TransactionCategory::orderBy('effect', 'desc')
            ->orderBy('name')
            ->get();

        // Calculate Stats for KPI
        $products = InventoryProduct::where('inv_t_product_detail.is_active', 1)
            ->leftJoin('inv_m_model_status', 'inv_m_model_status.model_id', '=', 'inv_t_product_detail.model_id')
            ->leftJoin('inv_m_unit as u', 'u.id', '=', 'inv_t_product_detail.unit_id')
            ->select('inv_t_product_detail.*', 'inv_m_model_status.project_status', 'u.code as unit_code')
            ->get();
        
        $stats = [
            'total' => $products->count(),
            'safe' => 0,
            'warning' => 0,
            'critical' => 0,
            'over' => 0
        ];

        foreach ($products as $p) {
            $currentPCS = InventoryProduct::calculatePcs($p->current_stock_qty, $p->weight_kg, $p->pcs_per_unit, $p->unit_code);
            $status = InventoryProduct::calculateStockStatus($currentPCS, $p->min_stock, $p->project_status);
            if (isset($stats[$status])) {
                $stats[$status]++;
            }
        }

        // Get Customers for Filter
        $customers = DB::table('customers')->select('id', 'code', 'name')->orderBy('code')->get();

        // Get Project Statuses for Filter
        $project_statuses = DB::table('inv_m_model_status')
            ->whereNotNull('project_status')
            ->distinct()
            ->pluck('project_status');

        return view('inventory.stock_monitoring', compact('categories', 'stats', 'customers', 'project_statuses'));
    }

    public function data(Request $request)
    {
        // 1. Get Dynamic Categories (OUT)
        $categories = \App\Models\InventoryModel\TransactionCategory::orderBy('effect', 'desc')
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

        // 3. Build Query
        $query = InventoryProduct::query()
            ->join('products', 'products.id', '=', 'inv_t_product_detail.product_id')
            ->leftJoin('models', 'models.id', '=', 'inv_t_product_detail.model_id')
            ->leftJoin('inv_m_model_status', 'inv_m_model_status.model_id', '=', 'models.id')
            ->leftJoin('customers', 'customers.id', '=', 'products.customer_id')
            ->leftJoin('inv_m_material_spec', 'inv_m_material_spec.id', '=', 'inv_t_product_detail.material_spec_id')
            ->leftJoin('inv_m_rank', 'inv_m_rank.id', '=', 'inv_t_product_detail.rank_id')
            ->leftJoin('inv_m_unit', 'inv_m_unit.id', '=', 'inv_t_product_detail.unit_id')
            ->leftJoin('inv_m_revision as r', 'r.id', '=', 'inv_t_product_detail.revision_id')
            // Join the aggregated transaction summary
            ->leftJoinSub($txSubquery, 'tx', 'tx.product_detail_id', '=', 'inv_t_product_detail.id')
            // Latest STO Join
            ->leftJoin(DB::raw("(SELECT sd.product_detail_id, sd.diff_qty as sto_gap 
                         FROM inv_t_sto_detail sd 
                         WHERE sd.event_id = (SELECT TOP 1 id FROM inv_t_sto_event ORDER BY created_at DESC)
                         AND sd.is_adjusted = 1
                        ) as latest_sto"), 'latest_sto.product_detail_id', '=', 'inv_t_product_detail.id')
            // Selects
            ->select([
                'inv_t_product_detail.id',
                'products.part_no',
                'products.part_name',
                'inv_t_product_detail.current_stock_qty',
                'inv_t_product_detail.pcs_per_unit',
                'r.code as revision',
                'inv_t_product_detail.remark',
                'inv_m_unit.code as unit_code',
                'inv_m_unit.name as unit_name',
                'inv_m_material_spec.spec_name',
                'inv_m_material_spec.coating_type',
                'inv_t_product_detail.thickness',
                'inv_t_product_detail.width',
                'inv_t_product_detail.length',
                'inv_t_product_detail.length_2',
                'inv_t_product_detail.pitch',
                'inv_t_product_detail.weight_kg',
                'inv_t_product_detail.product_status',
                'inv_t_product_detail.product_status_remark',
                'inv_m_model_status.project_status as model_project_status',
                'models.name as model_name',
                'customers.code as customer_code',
                'inv_m_rank.code as rank_code',
                'inv_m_rank.limit_value',
                'inv_t_product_detail.min_stock',
                'inv_t_product_detail.unit_per_car',
                'inv_t_product_detail.updated_at',
                'latest_sto.sto_gap',
                'inv_t_product_detail.material_price',
                'tx.*' // All transaction sums from subquery
            ]);

        // Global Search
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('products.part_no', 'like', '%' . $search . '%')
                    ->orWhere('inv_m_material_spec.spec_name', 'like', '%' . $search . '%')
                    ->orWhere('models.name', 'like', '%' . $search . '%')
                    ->orWhereRaw("(products.part_no + ' - ' + ISNULL(r.code, '')) LIKE ?", ['%' . $search . '%']);
            });
        }

        // Stock Status Filter
        if ($request->has('stock_status') && !empty($request->stock_status)) {
            $status = $request->stock_status;
            
            $query->where(function($q) use ($status) {
                // currentPCS = current_stock_qty * pcs_per_unit
                // maxStock = min_stock * 3
                
                $currentPcsSql = \App\Models\InventoryModel\InventoryProduct::getPcsCalculationSql('inv_t_product_detail.current_stock_qty', 'inv_t_product_detail', 'inv_m_unit.name');
                $minSql = "inv_t_product_detail.min_stock";
                
                if ($status === 'over') {
                    $q->whereRaw("{$currentPcsSql} > ({$minSql} * 3)")
                      ->where($minSql, '>', 0);
                } elseif ($status === 'danger') {
                    $q->whereRaw("{$currentPcsSql} < ({$minSql} - 30)")
                      ->where($minSql, '>', 0)
                      ->where(function($sq) {
                          $sq->where('inv_m_model_status.project_status', '!=', 'Regular')
                            ->whereNotIn('inv_t_product_detail.product_status', ['Oldstock OK', 'Oldstock NG'])
                            ->orWhereNull('inv_m_model_status.project_status');
                      });
                } elseif ($status === 'warning') {
                    $q->whereRaw("{$currentPcsSql} >= ({$minSql} - 30)")
                      ->whereRaw("{$currentPcsSql} < {$minSql}")
                      ->where($minSql, '>', 0)
                      ->where(function($sq) {
                          $sq->where('inv_m_model_status.project_status', '!=', 'Regular')
                            ->whereNotIn('inv_t_product_detail.product_status', ['Oldstock OK', 'Oldstock NG'])
                            ->orWhereNull('inv_m_model_status.project_status');
                      });
                } elseif ($status === 'safe') {
                    $q->where(function($sq) use ($currentPcsSql, $minSql) {
                        // Standard safe range
                        $sq->where(function($standard) use ($currentPcsSql, $minSql) {
                            $standard->whereRaw("{$currentPcsSql} >= {$minSql}")
                                     ->whereRaw("{$currentPcsSql} <= ({$minSql} * 3)")
                                     ->where($minSql, '>', 0);
                        })
                        // OR Regular is always safe if not over
                        ->orWhere(function($regular) use ($currentPcsSql, $minSql) {
                            $regular->where(function($sq) {
                                $sq->where('inv_m_model_status.project_status', 'Regular')
                                   ->orWhereIn('inv_t_product_detail.product_status', ['Oldstock OK', 'Oldstock NG']);
                            })
                            ->whereRaw("{$currentPcsSql} <= ({$minSql} * 3)")
                            ->where($minSql, '>', 0);
                        })
                        // OR no min stock
                        ->orWhere($minSql, '<=', 0);
                    });
                }
            });
        }

        // Customer Filter
        if ($request->has('customer_id') && !empty($request->customer_id)) {
            $query->where('products.customer_id', $request->customer_id);
        }

        // Model Filter
        if ($request->has('model_id') && !empty($request->model_id)) {
            $query->where('inv_t_product_detail.model_id', $request->model_id);
        }

        // Project Status Filter
        if ($request->has('project_status') && !empty($request->project_status)) {
            $status = $request->project_status;
            if ($status === 'Project') {
                $query->where(function($q) {
                    $q->where('inv_m_model_status.project_status', 'Project')
                      ->orWhereNull('inv_m_model_status.project_status');
                });
            } else {
                $query->where('inv_m_model_status.project_status', $status);
            }
        }

        $recordsTotal = InventoryProduct::where('is_active', 1)->count();
        $filteredRecords = $query->count();

        // Sorting - Map frontend index to backend field accurately
        $sortableColumns = [
            0 => 'inv_t_product_detail.id', // No
            1 => 'part_no',              // Part Information
            2 => 'project_status',        // Status
            3 => 'min_stock',             // Min Stock
            4 => 'balance_pcs',           // Current Balance
        ];
        
        $currentIdx = 5;
        foreach ($categories as $cat) {
            $alias = 'usage_' . preg_replace('/[^a-zA-Z0-9]/', '_', $cat->code);
            $sortableColumns[$currentIdx++] = $alias;
        }
        
        $sortableColumns[$currentIdx++] = 'sto_qty'; // STO GAP
        $sortableColumns[$currentIdx++] = 'action';  // Action
        
        $orderColIdx = $request->input('order.0.column', 1);
        $orderDir = $request->input('order.0.dir', 'asc');
        $orderCol = $sortableColumns[$orderColIdx] ?? 'part_no';

        if ($orderCol === 'part_no') {
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

            $partNoDisplay = $item->part_no . ($item->revision ? ' - ' . $item->revision : '');

            $partNoDisplay = $item->part_no . ($item->revision ? ' - ' . $item->revision : '');

            $calculatedQty = (float)$item->current_stock_qty;
            $inQty = (float)($item->total_in_sum ?? 0);
            $outQty = (float)($item->total_out_sum ?? 0);

            $balancePcsVal = InventoryProduct::calculatePcs($calculatedQty, $item->weight_kg, $pcsPerUnit, $item->unit_code);
            $inQtyPcsVal = InventoryProduct::calculatePcs($inQty, $item->weight_kg, $pcsPerUnit, $item->unit_code);
            $outQtyPcsVal = InventoryProduct::calculatePcs($outQty, $item->weight_kg, $pcsPerUnit, $item->unit_code);

            $isCoil = str_contains(strtolower($item->unit_code ?? ''), 'coil') && floatval($item->weight_kg ?? 0) > 0;
            $weight = floatval($item->weight_kg ?? 0);

            $row = [
                'id' => $item->id,
                'hash_id' => $hashId,
                'part_no' => $item->part_no, // Send raw part_no
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
                'sto_gap_display' => $this->formatStoGap($item->sto_gap, $pcsPerUnit, $isCoil, $weight),
                'sto_gap_plain' => $item->sto_gap !== null ? ($item->sto_gap > 0 ? '+' : '') . number_format(InventoryProduct::calculatePcs($item->sto_gap, $weight, $pcsPerUnit, $item->unit_code), 0) : '0',
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
                'project_status' => $item->product_status ?: ($item->model_project_status ?? 'Project')
            ];

            // Map Dynamic Columns
            $trialStatus = 'safe'; // Default

            foreach ($categories as $cat) {
                $alias = 'usage_' . preg_replace('/[^a-zA-Z0-9]/', '_', $cat->code);
                $row[$alias] = $this->formatQty($item->$alias, $pcsPerUnit, $isCoil, $weight);

                if ($cat->is_trial) {
                    $trialQty = abs(floatval($item->$alias)); // Unit
                    $limitValue = floatval($item->limit_value);
                    $trialStatus = InventoryProduct::calculateTrialStatus($trialQty, $limitValue, $pcsPerUnit);

                    $row['trial_status'] = $trialStatus;
                }
            }

            $pPerUnit = (float)($item->pcs_per_unit ?? 1);
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

    private function formatStoGap($gap, $pcsPerUnit, $isCoil = false, $weightKg = 0)
    {
        if ($gap === null) return '-';
        $gap = floatval($gap);
        if ($gap == 0) return '0';

        $sign = $gap > 0 ? '+' : '';
        $pcs = InventoryProduct::calculatePcs($gap, $weightKg, $pcsPerUnit, $isCoil ? 'coil' : 'pcs');
        $pcsDisplay = $sign . number_format($pcs, 0);

        if ($pcsPerUnit == 1 && !$isCoil) {
            return "<span class='font-bold'>$pcsDisplay</span>";
        }

        $unitDisplay = $sign . number_format($gap, $isCoil ? 2 : 0);
        return "<span class='font-bold'>$pcsDisplay</span> <span class='text-xs text-gray-400 font-normal'>($unitDisplay)</span>";
    }

    private function formatQty($qty, $pcsPerUnit, $isCoil = false, $weightKg = 0)
    {
        if ($qty === null) return '-';
        $qty = floatval($qty);
        if ($qty == 0) return '-';
        
        $pcs = InventoryProduct::calculatePcs($qty, $weightKg, $pcsPerUnit, $isCoil ? 'coil' : 'pcs');
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
                'p.current_stock_qty',
                'p.pcs_per_unit',
                'u.code as unit_code'
            ])
            ->first();

        if (!$data) abort(404);
        
        $balancePcs = (str_contains(strtolower($data->unit_code ?? ''), 'coil') && floatval($data->weight_kg ?? 0) > 0) ? floor((float)$data->current_stock_qty / (float)$data->weight_kg) * (int)($data->pcs_per_unit ?? 1) : (float)$data->current_stock_qty * (int)($data->pcs_per_unit ?? 1);

        $dimVal = [];
        $dimLbl = [];
        if ((float)$data->thickness > 0) { $dimVal[] = (float)$data->thickness; $dimLbl[] = 'T'; }
        if ((float)$data->width > 0) { $dimVal[] = (float)$data->width; $dimLbl[] = 'W'; }
        if ((float)$data->length > 0) { $dimVal[] = (float)$data->length; $dimLbl[] = 'L'; }
        if ((float)$data->length_2 > 0) { $dimVal[] = (float)$data->length_2; $dimLbl[] = 'L2'; }
        if ((float)$data->pitch > 0) { $dimVal[] = (float)$data->pitch; $dimLbl[] = 'P'; }

        $product = (object) [
            'qrcode' => QrCode::size(250)->errorCorrection('M')->margin(1)->generate(route('inventory.scanInfo', $inventoryProduct->hash_id)),
            'item_no' => $data->part_no . ($data->revision ? ' - ' . $data->revision : ''),
            'item_name' => $data->part_name,
            'model_name' => $data->model_name ?? '-',
            'partner_code' => $data->customer_code ?? '-',
            'dimension' => implode(' x ', $dimVal),
            'dimension_label' => !empty($dimLbl) ? '(' . implode(' x ', $dimLbl) . ')' : '',
            'material' => $data->material_spec . ($data->coating_type ? " ($data->coating_type)" : '')
        ];

        $products = [$product];

        return view('inventory.qrcode', compact('products'));
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

        $pcsPerUnit = $data->pcs_per_unit ?? 1;
        $balancePcs = (str_contains(strtolower($data->unit_code ?? ''), 'coil') && floatval($data->weight_kg ?? 0) > 0) ? floor((float)$data->current_stock_qty / (float)$data->weight_kg) * $pcsPerUnit : (float)$data->current_stock_qty * $pcsPerUnit;

        $status = $this->calculateStockStatus(
            $data->current_stock_qty, 
            $data->min_stock, 
            $pcsPerUnit, 
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
        $activeSto = \App\Models\InventoryModel\StoEvent::where('status', 'OPEN')
            ->orderBy('created_at', 'desc')
            ->first();
        
        $activeStoHashId = $activeSto ? $activeSto->hash_id : null;

        return view('inventory.scan_info', compact('product', 'activeStoHashId'));
    }

    public function getStoLog($id)
    {
        $logs = \App\Models\InventoryModel\StoDetail::with(['event', 'auditor'])
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
        $categories = \App\Models\InventoryModel\TransactionCategory::orderBy('effect', 'desc')
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
            ->leftJoin('inv_m_rank as r', 'r.id', '=', 'inv_t_product_detail.rank_id')
            ->leftJoin('inv_m_unit as u', 'u.id', '=', 'inv_t_product_detail.unit_id')
            ->leftJoin('inv_m_revision as rev', 'rev.id', '=', 'inv_t_product_detail.revision_id')
            ->leftJoinSub($txSubquery, 'tx', 'tx.product_detail_id', '=', 'inv_t_product_detail.id')
            ->leftJoin(DB::raw("(SELECT sd.product_detail_id, sd.diff_qty as sto_gap 
                         FROM inv_t_sto_detail sd 
                         WHERE sd.event_id = (SELECT TOP 1 id FROM inv_t_sto_event ORDER BY created_at DESC)
                         AND sd.is_adjusted = 1
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
                'r.code as rank_code',
                'rev.code as revision',
                'inv_m_model_status.project_status as model_project_status',
                'inv_t_product_detail.material_price',
                'latest_sto.sto_gap',
                'tx.*'
            ]);

        // Apply same filters as DataTables
        if ($request->has('stock_status') && !empty($request->stock_status)) {
            $status = $request->stock_status;
            $currentPcsSql = \App\Models\InventoryModel\InventoryProduct::getPcsCalculationSql('inv_t_product_detail.current_stock_qty', 'inv_t_product_detail', 'inv_m_unit.name');
            $minSql = "inv_t_product_detail.min_stock";
            
            if ($status === 'over') { $query->whereRaw("{$currentPcsSql} > ({$minSql} * 3)")->where($minSql, '>', 0); }
            elseif ($status === 'danger') { 
                $query->whereRaw("{$currentPcsSql} < ({$minSql} - 30)")->where($minSql, '>', 0)
                      ->where(function($q) {
                          $q->where('inv_m_model_status.project_status', '!=', 'Regular')
                            ->whereNotIn('inv_t_product_detail.product_status', ['Oldstock OK', 'Oldstock NG'])
                            ->orWhereNull('inv_m_model_status.project_status');
                      });
            }
            elseif ($status === 'warning') { 
                $query->whereRaw("{$currentPcsSql} >= ({$minSql} - 30)")->whereRaw("{$currentPcsSql} < {$minSql}")->where($minSql, '>', 0)
                      ->where(function($q) {
                          $q->where('inv_m_model_status.project_status', '!=', 'Regular')
                            ->whereNotIn('inv_t_product_detail.product_status', ['Oldstock OK', 'Oldstock NG'])
                            ->orWhereNull('inv_m_model_status.project_status');
                      });
            }
            elseif ($status === 'safe') { 
                $query->where(function($sq) use ($currentPcsSql, $minSql) {
                    $sq->where(fn($standard) => $standard->whereRaw("{$currentPcsSql} >= {$minSql}")->whereRaw("{$currentPcsSql} <= ({$minSql} * 3)")->where($minSql, '>', 0))
                       ->orWhere(function($regular) use ($currentPcsSql, $minSql) {
                           $regular->where(function($sq2) {
                               $sq2->where('inv_m_model_status.project_status', 'Regular')
                                   ->orWhereIn('inv_t_product_detail.product_status', ['Oldstock OK', 'Oldstock NG']);
                           })
                           ->whereRaw("{$currentPcsSql} <= ({$minSql} * 3)")
                           ->where($minSql, '>', 0);
                       })
                       ->orWhere($minSql, '<=', 0);
                });
            }
        }

        if ($request->customer_id) $query->where('products.customer_id', $request->customer_id);
        if ($request->model_id) $query->where('inv_t_product_detail.model_id', $request->model_id);
        if ($request->project_status) {
            $pStatus = $request->project_status;
            if ($pStatus === 'Project') {
                $query->where(function($q) {
                    $q->where('inv_m_model_status.project_status', 'Project')
                      ->orWhereNull('inv_m_model_status.project_status');
                });
            } else {
                $query->where('inv_m_model_status.project_status', $pStatus);
            }
        }

        // Apply Search (if any)
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('products.part_no', 'like', '%' . $search . '%')
                    ->orWhere('inv_m_material_spec.spec_name', 'like', '%' . $search . '%')
                    ->orWhere('models.name', 'like', '%' . $search . '%')
                    ->orWhereRaw("(products.part_no + ' - ' + ISNULL(rev.code, '')) LIKE ?", ['%' . $search . '%']);
            });
        }

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
}
