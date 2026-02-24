<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\InventoryProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AutoPrController extends Controller
{
    public function index()
    {
        // Get totals for quick dashboard at the top
        $products = InventoryProduct::where('is_active', 1)->get();
        $stats = [
            'critical' => 0,
            'warning' => 0,
        ];

        foreach ($products as $p) {
            $status = $this->calculateStockStatus($p->current_stock_qty, $p->min_stock, $p->pcs_per_unit);
            if (isset($stats[$status])) {
                $stats[$status]++;
            }
        }

        $customers = DB::table('customers')->select('id', 'code', 'name')->orderBy('code')->get();

        return view('inventory.auto_pr.index', compact('stats', 'customers'));
    }

    public function data(Request $request)
    {
        $query = InventoryProduct::query()
            ->join('products', 'products.id', '=', 'inv_t_product_detail.product_id')
            ->leftJoin('models', 'models.id', '=', 'products.model_id')
            ->leftJoin('customers', 'customers.id', '=', 'products.customer_id')
            ->leftJoin('inv_m_material_spec', 'inv_m_material_spec.id', '=', 'inv_t_product_detail.material_spec_id')
            ->leftJoin('inv_m_unit', 'inv_m_unit.id', '=', 'inv_t_product_detail.unit_id')
            ->select([
                'inv_t_product_detail.id',
                'products.part_no',
                'products.part_name',
                'inv_t_product_detail.current_stock_qty',
                'inv_t_product_detail.pcs_per_unit',
                'inv_t_product_detail.revision',
                'inv_m_unit.code as unit_code',
                'inv_m_material_spec.spec_name',
                'inv_t_product_detail.thickness',
                'inv_t_product_detail.width',
                'inv_t_product_detail.length',
                'models.name as model_name',
                'customers.code as customer_code',
                'inv_t_product_detail.min_stock',
                'inv_t_product_detail.unit_per_car',
                'inv_t_product_detail.updated_at',
            ])
            ->where('inv_t_product_detail.is_active', 1);

        // Filter for "Auto PR" (Only Warning and Danger)
        $query->where(function($q) {
            $currentPcsSql = "inv_t_product_detail.current_stock_qty * COALESCE(inv_t_product_detail.pcs_per_unit, 1)";
            $minSql = "inv_t_product_detail.min_stock";
            
            // Danger: < Min - 30
            // Warning: >= Min - 30 AND < Min
            $q->whereRaw("{$currentPcsSql} < {$minSql}")
              ->where($minSql, '>', 0);
        });

        // Search Filter
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('products.part_no', 'like', '%' . $search . '%')
                    ->orWhere('products.part_name', 'like', '%' . $search . '%')
                    ->orWhere('models.name', 'like', '%' . $search . '%')
                    ->orWhere('customers.code', 'like', '%' . $search . '%');
            });
        }

        // Customer Filter
        if ($request->has('customer_id') && !empty($request->customer_id)) {
            $query->where('products.customer_id', $request->customer_id);
        }

        $recordsFiltered = $query->count();
        $recordsTotal = InventoryProduct::where('is_active', 1)->count(); // Simplified for now

        $data = $query->orderBy('products.part_no', 'asc')
            ->skip($request->input('start', 0))
            ->take($request->input('length', 10))
            ->get();

        $formattedData = $data->map(function($item) {
            $currentPCS = $item->current_stock_qty * ($item->pcs_per_unit ?? 1);
            $shortage = $item->min_stock - $currentPCS;
            
            return [
                'id' => $item->id,
                'part_no' => $item->part_no . ($item->revision ? ' - ' . $item->revision : ''),
                'part_name' => $item->part_name,
                'customer' => $item->customer_code,
                'model' => $item->model_name,
                'material' => $item->spec_name . ' (' . (float)$item->thickness . 'x' . (float)$item->width . 'x' . (float)$item->length . ')',
                'min_stock' => number_format($shortage, 0), // User wants Shortage to be called Min Stock
                'min_stock_val' => number_format($item->min_stock, 0) . ' PCS', // The actual target
                'current_stock' => number_format($currentPCS, 0),
                'shortage' => number_format($shortage, 0),
                'status' => $this->calculateStockStatus($item->current_stock_qty, $item->min_stock, $item->pcs_per_unit ?? 1),
                'unit_name' => $item->unit_code,
                'pcs_per_unit' => $item->pcs_per_unit ?? 1,
                'unit_per_car' => $item->unit_per_car ?? 1,
                'last_update' => $item->updated_at ? $item->updated_at->format('d M y H:i') : '-'
            ];
        });

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $formattedData
        ]);
    }

    private function calculateStockStatus($current, $min, $pcsPerUnit)
    {
        $min = floatval($min);
        $currentPCS = floatval($current) * $pcsPerUnit;

        if ($min <= 0) return 'safe';
        if ($currentPCS < ($min - 30)) return 'critical';
        if ($currentPCS < $min) return 'warning';

        return 'safe';
    }
}
