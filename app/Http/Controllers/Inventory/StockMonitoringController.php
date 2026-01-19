<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\InventoryProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StockMonitoringController extends Controller
{
    public function index()
    {
        // Get all OUT categories for dynamic columns
        $categories = \App\Models\InventoryModel\TransactionCategory::orderBy('effect', 'desc')
            ->orderBy('name')
            ->get();
        return view('inventory.stock_monitoring', compact('categories'));
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
            // Joins for Product Details
            ->join('products', 'products.id', '=', 'inv_t_product_detail.product_id')
            ->leftJoin('models', 'models.id', '=', 'products.model_id')
            ->leftJoin('customers', 'customers.id', '=', 'products.customer_id')
            ->leftJoin('inv_m_material_spec', 'inv_m_material_spec.id', '=', 'inv_t_product_detail.material_spec_id')
            ->leftJoin('inv_m_rank', 'inv_m_rank.id', '=', 'inv_t_product_detail.rank_id')
            ->leftJoin('inv_m_unit', 'inv_m_unit.id', '=', 'inv_t_product_detail.unit_id')
            // Join the aggregated transaction summary
            ->leftJoinSub($txSubquery, 'tx', 'tx.product_detail_id', '=', 'inv_t_product_detail.id')
            // Latest STO Join
            ->leftJoin(DB::raw("(SELECT sd.product_detail_id, sd.diff_qty as sto_gap 
                         FROM inv_t_sto_detail sd 
                         WHERE sd.event_id = (SELECT TOP 1 id FROM inv_t_sto_event ORDER BY created_at DESC)
                        ) as latest_sto"), 'latest_sto.product_detail_id', '=', 'inv_t_product_detail.id')
            // Selects
            ->select([
                'inv_t_product_detail.id',
                'products.part_no',
                'products.part_name',
                'inv_t_product_detail.current_stock_qty',
                'inv_t_product_detail.pcs_per_unit',
                'inv_t_product_detail.revision',
                'inv_t_product_detail.remark',
                'inv_m_unit.code as unit_code',
                'inv_m_material_spec.spec_name',
                'inv_m_material_spec.coating_type',
                'inv_t_product_detail.thickness',
                'inv_t_product_detail.width',
                'inv_t_product_detail.length',
                'models.name as model_name',
                'customers.code as customer_code',
                'inv_m_rank.code as rank_code',
                'inv_m_rank.limit_value',
                'inv_t_product_detail.min_stock',
                'inv_t_product_detail.unit_per_car',
                'inv_t_product_detail.updated_at',
                'latest_sto.sto_gap',
                'tx.*' // All transaction sums from subquery
            ]);

        // Global Search
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('products.part_no', 'like', '%' . $search . '%')
                    ->orWhere('inv_m_material_spec.spec_name', 'like', '%' . $search . '%')
                    ->orWhere('models.name', 'like', '%' . $search . '%')
                    ->orWhereRaw("(products.part_no + CASE WHEN inv_t_product_detail.revision IS NOT NULL AND inv_t_product_detail.revision != '' THEN ' - ' + inv_t_product_detail.revision ELSE '' END) LIKE ?", ['%' . $search . '%'])
                    ->orWhereRaw("(products.part_no + CASE WHEN inv_t_product_detail.revision IS NOT NULL AND inv_t_product_detail.revision != '' THEN '-' + inv_t_product_detail.revision ELSE '' END) LIKE ?", ['%' . $search . '%']);
            });
        }

        $recordsTotal = InventoryProduct::where('is_active', 1)->count();
        $filteredRecords = $query->count();

        // Sorting - Map frontend index to backend field
        $sortableColumns = ['id', 'part_no', 'spec_size', 'remark', 'balance_pcs'];
        
        // Add indices for categories (they aren't really sortable easily here, so we skip or map to a safe default)
        // However, we need to skip them to find the STO index
        foreach ($categories as $cat) {
            $sortableColumns[] = 'usage'; // Placeholder
        }
        
        $sortableColumns[] = 'sto_qty'; // STO is now after categories
        $sortableColumns[] = 'action';

        $orderColIdx = $request->input('order.0.column', 1);
        $orderDir = $request->input('order.0.dir', 'asc');
        $orderCol = $sortableColumns[$orderColIdx] ?? 'products.part_no';

        if ($orderCol === 'part_no') {
            $query->orderBy('products.part_no', $orderDir);
        } elseif ($orderCol === 'spec_size') {
            $query->orderBy('inv_m_material_spec.spec_name', $orderDir);
        } elseif ($orderCol === 'balance_pcs') {
            $query->orderBy('inv_t_product_detail.current_stock_qty', $orderDir);
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

            // Format Size: T x W x L (or just T x W if L is 0/null)
            $size = floatval($item->thickness);
            if (floatval($item->width) > 0) $size .= ' x ' . floatval($item->width);
            if (floatval($item->length) > 0) $size .= ' x ' . floatval($item->length);

            $specSize = ($item->spec_name ?? '-') . ' <br><span class="text-xs text-gray-500">' . $size . '</span>';

            $partNoDisplay = $item->part_no . ($item->revision ? ' - ' . $item->revision : '');

            // Source of truth is now the synchronized current_stock_qty column
            $calculatedQty = (float)$item->current_stock_qty;
            $inQty = (float)($item->total_in_sum ?? 0);
            $outQty = (float)($item->total_out_sum ?? 0);

            $row = [
                'hash_id' => $hashId,
                'part_no' => $partNoDisplay,
                'spec_size' => $specSize,
                'remark' => $item->remark ?? '-',
                'balance_pcs'  => number_format($calculatedQty * $pcsPerUnit, 0),
                'balance_unit' => $item->unit_code,
                'current_qty' => $calculatedQty,
                'total_in' => number_format($inQty * $pcsPerUnit, 0),
                'total_out' => number_format($outQty * $pcsPerUnit, 0),
                'sto_gap' => $item->sto_gap,
                'sto_gap_display' => $this->formatStoGap($item->sto_gap, $pcsPerUnit),
                'sto_gap_plain' => $item->sto_gap !== null ? ($item->sto_gap > 0 ? '+' : '') . number_format($item->sto_gap * $pcsPerUnit, 0) : '0',
                // Detailed Info for Hover/Popover
                'details' => [
                    'model' => $item->model_name ?? '-',
                    'customer' => $item->customer_code ?? '-',
                    'rank' => $item->rank_code ?? '-',
                    'limit_value' => $item->limit_value ?? '-',
                    'min_stock' => $item->min_stock ?? '-',
                    'coating_type' => $item->coating_type ?? '-',
                    'unit_per_car' => $item->unit_per_car ?? '-',
                    'last_update' => $item->updated_at ? $item->updated_at->format('d M Y, H:i') : '-',
                    'pcs_per_unit' => $pcsPerUnit
                ],
                'stock_status' => $this->calculateStockStatus($item->current_stock_qty, $item->min_stock, $pcsPerUnit)
            ];

            // Map Dynamic Columns
            $trialStatus = 'safe'; // Default

            foreach ($categories as $cat) {
                $alias = 'usage_' . preg_replace('/[^a-zA-Z0-9]/', '_', $cat->code);
                $row[$alias] = $this->formatQty($item->$alias, $pcsPerUnit);

                // Detect Trial Category via Flag
                if ($cat->is_trial) {
                    $trialQty = abs(floatval($item->$alias)); // Unit
                    $limitValue = floatval($item->limit_value);
                    $trialStatus = $this->calculateTrialStatus($trialQty, $limitValue, $pcsPerUnit);

                    // Append status to the specific cell data if needed, or row
                    $row['trial_status'] = $trialStatus;
                }
            }

            $row['stock_status'] = $this->calculateStockStatus($item->current_stock_qty, $item->min_stock, $pcsPerUnit);

            return $row;
        });

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $filteredRecords,
            'data' => $formattedData
        ]);
    }

    private function formatStoGap($gap, $pcsPerUnit)
    {
        if ($gap === null) return '-';
        $gap = floatval($gap);
        if ($gap == 0) return '0';

        $sign = $gap > 0 ? '+' : '';
        $pcs = $gap * $pcsPerUnit;
        $pcsDisplay = $sign . number_format($pcs, 0);

        if ($pcsPerUnit == 1) {
            return "<span class='font-bold'>$pcsDisplay</span>";
        }

        $unitDisplay = $sign . number_format($gap, 0);
        return "<span class='font-bold'>$pcsDisplay</span> <span class='text-xs text-gray-400 font-normal'>($unitDisplay)</span>";
    }

    private function formatQty($qty, $pcsPerUnit)
    {
        $qty = floatval($qty);
        if ($qty == 0) return '-';
        $pcs = $qty * $pcsPerUnit;

        $pcsDisplay = number_format($pcs, 0);

        // If 1 PCS = 1 Unit, showing both is redundant.
        if ($pcsPerUnit == 1) {
            return "<span class='font-bold'>$pcsDisplay</span>";
        }

        // Remove decimals from Unit display as well
        $unitDisplay = number_format($qty, 0);

        return "<span class='font-bold'>$pcsDisplay</span> <span class='text-xs text-gray-500'>($unitDisplay)</span>";
    }

    private function calculateStockStatus($current, $min, $pcsPerUnit)
    {
        $min = floatval($min); // Assumed in PCS
        $currentPCS = floatval($current) * $pcsPerUnit; // Convert Unit to PCS

        if ($min <= 0) return 'safe';

        $maxStock = $min * 3;

        if ($currentPCS > $maxStock) return 'over'; // Blue
        if ($currentPCS < ($min - 30)) return 'danger'; // < Min - 30
        if ($currentPCS < $min) return 'warning'; // Min - 30 to Min - 1

        return 'safe'; // Min to Max
    }

    private function calculateTrialStatus($usage, $limit, $pcsPerUnit)
    {
        $limit = floatval($limit);
        $usage = floatval($usage);

        if ($limit <= 0) return 'safe';

        $usagePCS = $usage * $pcsPerUnit;

        // Legacy Logic:
        // Danger if > Limit
        // Warning if > Limit - 50 (and <= Limit)

        if ($usagePCS > $limit) return 'danger';
        if ($usagePCS > ($limit - 50)) return 'warning';

        return 'safe';
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
            ->where('p.id', $inventoryProduct->id)
            ->select([
                'prod.part_no',
                'prod.part_name',
                'cust.code as customer_code',
                'model.name as model_name',
                'p.revision',
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
        
        $qrData = json_encode([
            'id' => $inventoryProduct->hash_id,
            'pn' => $data->part_no,
            'rev' => $data->revision,
            'dim' => (float)$data->thickness . 'x' . (float)$data->width . 'x' . (float)$data->length . ($data->length_2 > 0 ? 'x' . (float)$data->length_2 : '') . ($data->pitch > 0 ? 'x' . (float)$data->pitch : '')
        ]);

        $balancePcs = (float)$data->current_stock_qty * (int)($data->pcs_per_unit ?? 1);

        $product = (object) [
            'qrcode' => QrCode::size(250)->errorCorrection('M')->margin(1)->generate($qrData),
            'item_no' => $data->part_no . ($data->revision ? ' - ' . $data->revision : ''),
            'item_name' => $data->part_name,
            'model_name' => $data->model_name ?? '-',
            'partner_code' => $data->customer_code ?? '-',
            'dimension' => (float)$data->thickness . ' x ' . (float)$data->width . ' x ' . (float)$data->length . ($data->length_2 > 0 ? ' x ' . (float)$data->length_2 : '') . ($data->pitch > 0 ? ' x ' . (float)$data->pitch : ''),
            'material' => $data->material_spec . ($data->coating_type ? " ($data->coating_type)" : ''),
            'balance' => number_format($balancePcs, 0),
            'unit' => 'PCS'
        ];

        $products = [$product];

        return view('inventory.qrcode_balance', compact('products'));
    }
}
