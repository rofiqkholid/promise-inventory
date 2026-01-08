<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\InventoryProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMonitoringController extends Controller
{
    public function index()
    {
        // Get all OUT categories for dynamic columns
        $categories = \App\Models\InventoryModel\TransactionCategory::where('effect', -1)->orderBy('name')->get();
        return view('inventory.stock_monitoring', compact('categories'));
    }

    public function data(Request $request)
    {
        // 1. Get Dynamic Categories (OUT)
        $categories = \App\Models\InventoryModel\TransactionCategory::where('effect', -1)->orderBy('name')->get();

        // 2. Build Select Clause Dynamically
        $selects = [
            'inv_t_product_detail.id',
            'products.part_no',
            'products.part_name',
            'inv_t_product_detail.current_stock_qty',
            'inv_t_product_detail.pcs_per_unit',
            'inv_t_product_detail.revision',
            'inv_t_product_detail.remark',
            'inv_m_unit.code as unit_code',
        ];

        // Dynamic Usage Columns and Total Out
        if ($categories->count() > 0) {
            foreach ($categories as $cat) {
                // Sanitize code to be safe for alias (remove special chars if any, mostly alphanumeric expected)
                $alias = 'usage_' . preg_replace('/[^a-zA-Z0-9]/', '_', $cat->code);
                $selects[] = DB::raw("CAST(SUM(CASE WHEN t.category = '{$cat->code}' THEN t.qty ELSE 0 END) AS DECIMAL(10,2)) as {$alias}");
            }

            // Total Out (safe list because categories exist)
            $codesList = $categories->pluck('code')->map(fn($c)=> "'$c'")->join(',');
            $selects[] = DB::raw("CAST(SUM(CASE WHEN t.category IN ($codesList) THEN t.qty ELSE 0 END) AS DECIMAL(10,2)) as total_out_sum");
        } else {
            // No OUT categories defined — avoid IN () SQL syntax error by using 0
            $selects[] = DB::raw("0 as total_out_sum");
        }
        
        // Total In (Assuming effect 1)
        $inCategories = \App\Models\InventoryModel\TransactionCategory::where('effect', 1)->pluck('code');
        if ($inCategories->count() > 0) {
            $inStr = $inCategories->map(fn($c)=> "'$c'")->join(',');
            $selects[] = DB::raw("CAST(SUM(CASE WHEN t.category IN ($inStr) THEN t.qty ELSE 0 END) AS DECIMAL(10,2)) as total_in_sum");
        } else {
            $selects[] = DB::raw("0 as total_in_sum");
        }

        // 3. Build Query
        $query = InventoryProduct::query()
            // Joins for Product Details
            ->join('products', 'products.id', '=', 'inv_t_product_detail.product_id')
            ->leftJoin('models', 'models.id', '=', 'products.model_id')
            ->leftJoin('customers', 'customers.id', '=', 'products.customer_id')
            ->leftJoin('inv_m_material_spec', 'inv_m_material_spec.id', '=', 'inv_t_product_detail.material_spec_id')
            ->leftJoin('inv_m_rank', 'inv_m_rank.id', '=', 'inv_t_product_detail.rank_id')
            ->leftJoin('inv_m_unit', 'inv_m_unit.id', '=', 'inv_t_product_detail.unit_id')
            ->leftJoin('inv_t_inventory_transaction as t', 't.product_detail_id', '=', 'inv_t_product_detail.id')
            // Selects
            ->select(array_merge($selects, [
                'inv_m_material_spec.spec_name',
                'inv_m_material_spec.coating_type', // Add Coating Type
                'inv_t_product_detail.thickness',
                'inv_t_product_detail.width',
                'inv_t_product_detail.length',
                'models.name as model_name', 
                'customers.code as customer_code',    
                'inv_m_rank.code as rank_code',
                'inv_m_rank.limit_value', // Add Limit Value
                'inv_t_product_detail.min_stock',
                'inv_t_product_detail.unit_per_car',
                'inv_t_product_detail.updated_at' 
            ]))
            ->groupBy(
                'inv_t_product_detail.id',
                'products.part_no',
                'products.part_name', 
                'inv_m_material_spec.spec_name',
                'inv_m_material_spec.coating_type', // Group by Coating Type
                'inv_t_product_detail.thickness',
                'inv_t_product_detail.width',
                'inv_t_product_detail.length',
                'inv_t_product_detail.current_stock_qty',
                'inv_t_product_detail.pcs_per_unit',
                'inv_t_product_detail.revision',
                'inv_t_product_detail.remark',
                'inv_m_unit.code',
                'models.name',
                'customers.code',
                'inv_m_rank.code',
                'inv_m_rank.limit_value', // Group by Limit Value
                'inv_t_product_detail.min_stock',
                'inv_t_product_detail.unit_per_car',
                'inv_t_product_detail.updated_at'
            );

        // Global Search
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('products.part_no', 'like', '%' . $search . '%')
                  ->orWhere('inv_m_material_spec.spec_name', 'like', '%' . $search . '%')
                  ->orWhere('models.name', 'like', '%' . $search . '%')
                  ->orWhereRaw("(products.part_no + CASE WHEN inv_t_product_detail.revision IS NOT NULL AND inv_t_product_detail.revision != '' THEN ' - ' + inv_t_product_detail.revision ELSE '' END) LIKE ?", ['%' . $search . '%'])
                  ->orWhereRaw("(products.part_no + CASE WHEN inv_t_product_detail.revision IS NOT NULL AND inv_t_product_detail.revision != '' THEN '-' + inv_t_product_detail.revision ELSE '' END) LIKE ?", ['%' . $search . '%']);
            });
        }

        // Sorting
        $query->orderBy('products.part_no', 'asc');

        // Pagination
        $totalRecords = InventoryProduct::count();
        $filteredRecords = $query->get()->count(); 
        
        $perPage = $request->input('length', 10);
        $start = $request->input('start', 0);
        
        $data = $query->skip($start)->take($perPage)->get();

        // Formatting Data
        $formattedData = $data->map(function($item) use ($categories) {
            $pcsPerUnit = $item->pcs_per_unit ?? 1;
            
            // Format Size: T x W x L (or just T x W if L is 0/null)
            $size = floatval($item->thickness);
            if(floatval($item->width) > 0) $size .= ' x ' . floatval($item->width);
            if(floatval($item->length) > 0) $size .= ' x ' . floatval($item->length);

            $specSize = ($item->spec_name ?? '-') . ' <br><span class="text-xs text-gray-500">' . $size . '</span>';
            
            $partNoDisplay = $item->part_no . ($item->revision ? ' - ' . $item->revision : '');

            $row = [
                'part_no' => $partNoDisplay,
                'spec_size' => $specSize,
                'remark' => $item->remark ?? '-',
                'balance_unit' => $item->current_stock_qty . ' ' . $item->unit_code,
                'balance_pcs'  => number_format($item->current_stock_qty * $pcsPerUnit, 0),
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
                'stock_status' => $this->calculateStockStatus($item->current_stock_qty, $item->min_stock)
            ];
            
            // Map Dynamic Columns
            $trialStatus = 'safe'; // Default
            
            foreach ($categories as $cat) {
                $alias = 'usage_' . preg_replace('/[^a-zA-Z0-9]/', '_', $cat->code);
                $row[$alias] = $this->formatQty($item->$alias, $pcsPerUnit);

                // Detect Trial Category (Assuming code contains 'TRIAL')
                if (stripos($cat->code, 'OUT-TRIAL') !== false) {
                    $trialQty = abs(floatval($item->$alias)); // Unit
                    $limitValue = floatval($item->limit_value);
                    $trialStatus = $this->calculateTrialStatus($trialQty, $limitValue, $pcsPerUnit);
                    
                    // Append status to the specific cell data if needed, or row
                    $row['trial_status'] = $trialStatus;
                }
            }
            
            $row['stock_status'] = $this->calculateStockStatus($item->current_stock_qty, $item->min_stock);

            return $row;
        });

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $formattedData
        ]);
    }

    private function formatQty($qty, $pcsPerUnit) {
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

    private function calculateStockStatus($current, $min) {
        $min = floatval($min);
        $current = floatval($current);
        
        if ($min <= 0) return 'safe'; 
        
        $maxStock = $min * 3;

        if ($current > $maxStock) return 'over'; // Blue
        if ($current < ($min - 30)) return 'danger'; // < Min - 30
        if ($current < $min) return 'warning'; // Min - 30 to Min - 1

        return 'safe'; // Min to Max
    }

    private function calculateTrialStatus($usage, $limit, $pcsPerUnit) {
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
}
