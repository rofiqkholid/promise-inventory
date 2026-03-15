<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\InventoryProduct;
use App\Models\InventoryModel\MaterialSpec;
use App\Models\InventoryModel\Rank;
use App\Models\InventoryModel\Unit;
use App\Models\Products;
use App\Services\Inventory\ProductService;
use App\Traits\DecodesHashInputs;
use App\Exports\InventoryProductExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InventoryProductController extends Controller
{
    use DecodesHashInputs;
    /**
     * Display the inventory product page.
     */
    public function index()
    {
        // Only show customers that have products in the master data
        $customers = DB::table('customers as c')
            ->join('products as p', 'p.customer_id', '=', 'c.id')
            ->join('inv_t_product_detail as pd', 'pd.product_id', '=', 'p.id')
            ->select('c.id', 'c.code')
            ->where('p.is_delete', 0)
            ->where('pd.is_active', 1)
            ->distinct()
            ->orderBy('c.code')
            ->get();

        // Only show models that have products in the master data
        $models = DB::table('models as m')
            ->join('inv_t_product_detail as pd', 'pd.model_id', '=', 'm.id')
            ->select(DB::raw('MIN(m.id) as id'), 'm.name')
            ->where('pd.is_active', 1)
            ->groupBy('m.name')
            ->orderBy('m.name')
            ->get();

        return view('inventory.master-data.product', compact('customers', 'models'));
    }

    /**
     * Display a listing of the resource for DataTables.
     */
    public function data(Request $request)
    {
        $draw = (int) $request->input('draw');
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $query = $this->buildBaseProductQuery();
        $recordsTotal = (clone $query)->count();

        // Filters
        if ($request->filled('customer_id')) {
            $query->where('prod.customer_id', $request->customer_id);
        }
        if ($request->filled('model_id')) {
            $selectedModel = DB::table('models')->where('id', $request->model_id)->first();
            if ($selectedModel) {
                 $query->where('model.name', $selectedModel->name);
            }
        }
        if ($request->filled('part_no')) {
            $query->where('prod.part_no', 'like', "%{$request->part_no}%");
        }

        // Global Search
        $searchValue = $request->input('search.value');
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('prod.part_no', 'like', "%{$searchValue}%")
                  ->orWhere('prod.part_name', 'like', "%{$searchValue}%")
                  ->orWhere('cust.code', 'like', "%{$searchValue}%")
                  ->orWhere('model.name', 'like', "%{$searchValue}%")
                  ->orWhere('ms.spec_name', 'like', "%{$searchValue}%")
                  ->orWhere('r.code', 'like', "%{$searchValue}%")
                  ->orWhereRaw("(prod.part_no + ' - ' + ISNULL(r_master.code, '')) LIKE ?", ['%' . $searchValue . '%']);
            });
        }

        $recordsFiltered = $query->count();

        // Sorting Map
        $columnsMap = [
            0 => 'p.id',
            1 => 'prod.part_no',
            2 => 'cust.code',
            3 => 'model.name',
            4 => 'ms_model.project_status',
            5 => 'ms.spec_name',
            6 => 'p.thickness',
            7 => 'p.pcs_per_unit',
            8 => 'p.weight_kg',
            9 => 'p.unit_per_car',
            10 => 'r.code',
            11 => 'p.remark',
            12 => 'p.updated_at',
        ];

        $colIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        $orderCol = $columnsMap[$colIndex] ?? 'p.updated_at';

        if ($orderCol === 'ms_model.project_status') {
            $query->orderByRaw("COALESCE(p.product_status, ms_model.project_status) {$orderDir}");
        } else {
            $query->orderBy($orderCol, $orderDir);
        }

        $data = $query->skip($start)
            ->take($length)
            ->get()
            ->map(fn($r) => [
                'id' => InventoryProduct::encodeHash($r->id),
                'product_id' => $r->product_id,
                'part_no' => $r->part_no . ($r->revision_code ? ' - ' . $r->revision_code : ''),
                'part_name' => $r->part_name,
                'customer' => $r->customer_code,
                'model' => $r->model_name,
                'model_project_status' => $r->model_project_status,
                'product_status' => $r->product_status,
                'revision' => $r->revision_code,
                'material_spec' => $r->material_spec_name,
                'coating_type' => $r->coating_type,
                'thickness' => (float)$r->thickness,
                'width' => (float)$r->width,
                'length' => (float)$r->length,
                'length_2' => (float)$r->length_2,
                'pitch' => (float)$r->pitch,
                'density' => (float)$r->density,
                'weight_kg' => (float)$r->weight_kg,
                'net_weight' => (float)$r->net_weight,
                'unit' => $r->unit_code,
                'unit_name' => $r->unit_name,
                'rank' => $r->rank_code,
                'current_stock_qty' => $r->current_stock_qty,
                'trial_usage_qty' => $r->trial_usage_qty,
                'min_stock' => $r->min_stock,
                'pcs_per_unit' => $r->pcs_per_unit,
                'unit_per_car' => $r->unit_per_car,
                'remark' => $r->remark,
                'updated_at' => $r->updated_at ? \Carbon\Carbon::parse($r->updated_at)->format('d M y, H:i') : '-',
            ]);

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    /**
     * Export data to Excel.
     */
    public function exportExcel(Request $request)
    {
        $query = $this->buildBaseProductQuery();

        // Filters (Same as data method)
        if ($request->filled('customer_id')) {
            $query->where('prod.customer_id', $request->customer_id);
        }
        if ($request->filled('model_id')) {
            $selectedModel = DB::table('models')->where('id', $request->model_id)->first();
            if ($selectedModel) {
                 $query->where('model.name', $selectedModel->name);
            }
        }
        if ($request->filled('part_no')) {
            $query->where('prod.part_no', 'like', "%{$request->part_no}%");
        }

        // Global Search
        $searchValue = $request->input('search');
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('prod.part_no', 'like', "%{$searchValue}%")
                  ->orWhere('prod.part_name', 'like', "%{$searchValue}%")
                  ->orWhere('cust.code', 'like', "%{$searchValue}%")
                  ->orWhere('model.name', 'like', "%{$searchValue}%")
                  ->orWhere('ms.spec_name', 'like', "%{$searchValue}%")
                  ->orWhere('r.code', 'like', "%{$searchValue}%");
            });
        }

        $data = $query->orderBy('prod.part_no', 'asc')->get();

        return Excel::download(new InventoryProductExport($data), 'Inventory_Product_Master_' . date('YmdHis') . '.xlsx');
    }

    /**
     * Download Excel template for import.
     */
    public function downloadTemplate()
    {
        return Excel::download(new \App\Exports\InventoryProductTemplateExport, 'Inventory_Product_Import_Template.xlsx');
    }

    /**
     * Import data from Excel.
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        $import = new \App\Imports\InventoryProductImport();
        Excel::import($import, $request->file('file'));

        if (!empty($import->getErrors())) {
            $errorMsg = implode('<br>', array_slice($import->getErrors(), 0, 10)); // return top 10 errors
            if (count($import->getErrors()) > 10) {
                $errorMsg .= '<br>... and ' . (count($import->getErrors()) - 10) . ' more errors.';
            }
            
            return response()->json([
                'success' => false,
                'message' => "Import processed with errors.<br><br><b>Success:</b> {$import->getSuccessCount()} rows.<br><b>Errors:</b><br>{$errorMsg}"
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "Import completed successfully. {$import->getSuccessCount()} rows imported.",
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->decodeHashInputs($request->all(), [
            'product_id' => \App\Models\Products::class,
            'material_spec_id' => MaterialSpec::class,
            'unit_id' => Unit::class,
            'rank_id' => Rank::class,
            'revision_id' => \App\Models\InventoryModel\Revision::class,
        ]);
        
        $request->merge($data);
        $validated = $request->validate($this->getValidationRules());

        InventoryProduct::create($validated);

        return response()->json(['success' => true, 'message' => 'Inventory Product created successfully.']);
    }

    /**
     * Display the specified resource for editing.
     */
    public function show($id)
    {
        $inventoryProduct = InventoryProduct::findByHashOrFail($id);
        $inventoryProduct->load(['product', 'model', 'materialSpec', 'unit', 'rank', 'revision']);
        
        // Get model status
        $modelStatus = \App\Models\InventoryModel\ModelStatus::where('model_id', $inventoryProduct->model_id)->first();
        
        $data = $inventoryProduct->toArray();
        $data['project_status_model'] = $modelStatus ? $modelStatus->project_status : 'Project';
        $data['product_status'] = $inventoryProduct->product_status;
        $data['product_status_remark'] = $inventoryProduct->product_status_remark;

        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $inventoryProduct = InventoryProduct::findByHashOrFail($id);
        
        $data = $this->decodeHashInputs($request->all(), [
            'product_id' => \App\Models\Products::class,
            'material_spec_id' => MaterialSpec::class,
            'unit_id' => Unit::class,
            'rank_id' => Rank::class,
            'revision_id' => \App\Models\InventoryModel\Revision::class,
        ]);

        $request->merge($data);
        $validated = $request->validate($this->getValidationRules());

        $inventoryProduct->update($validated);

        return response()->json(['success' => true, 'message' => 'Inventory Product updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $inventoryProduct = InventoryProduct::findByHashOrFail($id);
        $inventoryProduct->delete();
        return response()->json(['success' => true, 'message' => 'Inventory Product deleted successfully.']);
    }

    /**
     * Print Label for the specified resource.
     */
    public function printLabel($id, ProductService $productService)
    {
        $products = $productService->generateLabelData($id);
        
        if (empty($products)) abort(404);

        return view('inventory.qrcode', compact('products'));
    }

    /**
     * AJAX HELPERS
     */

    public function getDropdownData()
    {
        return response()->json([
            'customers' => DB::table('customers')->select('id', 'code')->orderBy('code')->get(),
            'materialSpecs' => MaterialSpec::select('id', 'spec_name')->get(),
            'units' => Unit::select('id', 'code', 'name')->get(),
            'ranks' => Rank::select('id', 'code', 'description')->get(),
            'revisions' => \App\Models\InventoryModel\Revision::where('is_active', 1)->orderBy('group_name')->orderBy('sort_order')->get(),
        ]);
    }

    public function getCustomers()
    {
        return DB::table('customers')
            ->select('id', 'code')
            ->orderBy('code')
            ->get();
    }

    public function getModels(Request $request)
    {
        $query = DB::table('models as m')
            ->select(DB::raw('MIN(m.id) as id'), 'm.name')
            ->groupBy('m.name')
            ->orderBy('m.name');

        if ($request->for_filter) {
            $query->join('inv_t_product_detail as pd', 'pd.model_id', '=', 'm.id')
                  ->where('pd.is_active', 1);
        }
        
        if ($request->customer_id) {
            $query->where('m.customer_id', $request->customer_id);
        }

        return $query->get();
    }

    public function getProducts(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $page = max(1, (int) $request->get('page', 1));
        $limit = 10;
        $skip = ($page - 1) * $limit;

        // Base query mulai dari Part (products)
        $query = DB::table('products as p')
            ->leftJoin('customers as c', 'c.id', '=', 'p.customer_id')
            ->where('p.is_delete', 0)
            ->select('p.id', 'p.part_no', 'p.part_name', 'c.code as customer_code', 'p.customer_id');

        // Jika dipanggil dari Filter Dashboard (for_filter)
        if ($request->for_filter) {
            $query->join('inv_t_product_detail as pd', 'pd.product_id', '=', 'p.id')
                  ->leftJoin('models as m', 'm.id', '=', 'pd.model_id') // Join ke model melalui tabel DETAIL
                  ->where('pd.is_active', 1)
                  ->addSelect('m.name as model_name', 'pd.model_id')
                  ->distinct();

            // Filter Customer (di detail/induk)
            if ($request->filled('customer_id')) {
                $query->where('p.customer_id', $request->customer_id);
            }

            // Filter Model (di detail)
            if ($request->filled('model_id')) {
                $selectedModel = DB::table('models')->find($request->model_id);
                if ($selectedModel) {
                    $query->where('m.name', $selectedModel->name); // Mendukung gouping name
                }
            }
        } else {
            // Jika dipanggil dari Form Add/Edit (bukan filter)
            $query->leftJoin('models as m', 'm.id', '=', 'p.model_id')
                  ->addSelect('m.name as model_name', 'p.model_id');
                  
            if ($request->filled('customer_id')) {
                $query->where('p.customer_id', $request->customer_id);
            }
            if ($request->filled('model_id')) {
                $query->where('p.model_id', $request->model_id);
            }
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('p.part_no', 'like', "%{$q}%")
                  ->orWhere('p.part_name', 'like', "%{$q}%")
                  ->orWhere('c.code', 'like', "%{$q}%");
            });
        }

        $total = (clone $query)->count();
        $rows = $query->orderBy('p.part_no')->skip($skip)->take($limit)->get();

        return response()->json([
            'results' => $rows->map(fn($p) => [
                'id' => \App\Models\Products::encodeHash($p->id),
                'part_no' => $p->part_no,
                'part_name' => $p->part_name,
                'text' => "{$p->part_no} - {$p->part_name}",
                'customer_id' => $p->customer_id,
                'model_id' => $p->model_id,
            ]),
            'pagination' => ['more' => ($skip + $limit) < $total],
        ]);
    }

    public function getLatestRevision($productId)
    {
        $id = \App\Models\Products::decodeHash($productId) ?? $productId;

        $latest = InventoryProduct::where('product_id', $id)
            ->with(['materialSpec', 'unit', 'rank', 'revision'])
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$latest) {
            $firstRev = \App\Models\InventoryModel\Revision::where('is_active', 1)->orderBy('group_name')->orderBy('sort_order')->first();
            return response()->json([
                'exists' => false, 
                'next_revision' => $firstRev ? $firstRev->code : 'R',
                'next_revision_id' => $firstRev ? $firstRev->hash_id : null
            ]);
        }

        // Find current revision in master data
        $currentRev = $latest->revision;
        
        $nextRevisionCode = '-'; 
        $nextRevisionId = null;

        if ($currentRev) {
            // Find next revision in the SAME group with higher sort_order
            $nextRev = \App\Models\InventoryModel\Revision::where('group_name', $currentRev->group_name)
                ->where('is_active', 1)
                ->where('sort_order', '>', $currentRev->sort_order)
                ->orderBy('sort_order', 'asc')
                ->first();

            if ($nextRev) {
                $nextRevisionCode = $nextRev->code;
                $nextRevisionId = $nextRev->hash_id;
            }
        }

        return response()->json([
            'exists' => true,
            'data' => $latest,
            'next_revision' => $nextRevisionCode,
            'next_revision_id' => $nextRevisionId,
            'material_spec_hash' => $latest->materialSpec ? $latest->materialSpec->hash_id : null,
            'unit_hash' => $latest->unit ? $latest->unit->hash_id : null,
            'rank_hash' => $latest->rank ? $latest->rank->hash_id : null,
        ]);
    }

    /**
     * PRIVATE METHODS
     */

    private function getValidationRules()
    {
        return [
            'product_id' => 'required|exists:products,id',
            'model_id' => 'required|exists:models,id',
            'material_spec_id' => 'nullable|exists:inv_m_material_spec,id',
            'unit_id' => 'nullable|exists:inv_m_unit,id',
            'rank_id' => 'nullable|exists:inv_m_rank,id',
            'revision_id' => 'required|exists:inv_m_revision,id',
            'thickness' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'length_2' => 'nullable|numeric|min:0',
            'pitch' => 'nullable|numeric|min:0',
            'density' => 'nullable|numeric|min:0',
            'weight_kg' => 'nullable|numeric|min:0',
            'net_weight' => 'nullable|numeric|min:0',
            'material_price' => 'nullable|numeric|min:0',
            'pcs_per_unit' => 'nullable|integer|min:1',
            'unit_per_car' => 'nullable|integer|min:1',
            'min_stock' => 'nullable|integer|min:0',
            'current_stock_qty' => 'nullable|numeric|min:0',
            'trial_usage_qty' => 'nullable|numeric|min:0',
            'remark' => 'nullable|string',
            'product_status' => 'nullable|string|in:Allsize OK,Allsize NG',
            'product_status_remark' => 'nullable|string|in:Damage,Other',
        ];
    }

    private function buildBaseProductQuery()
    {
        return DB::table('inv_t_product_detail as p')
            ->join('products as prod', 'prod.id', '=', 'p.product_id')
            ->leftJoin('customers as cust', 'cust.id', '=', 'prod.customer_id')
        ->leftJoin('models as model', 'model.id', '=', 'p.model_id')
        ->leftJoin('inv_m_material_spec as ms', 'ms.id', '=', 'p.material_spec_id')
        ->leftJoin('inv_m_unit as u', 'u.id', '=', 'p.unit_id')
        ->leftJoin('inv_m_rank as r', 'r.id', '=', 'p.rank_id')
        ->leftJoin('inv_m_revision as r_master', 'r_master.id', '=', 'p.revision_id')
        ->leftJoin('inv_m_model_status as ms_model', 'ms_model.model_id', '=', 'p.model_id')
        ->where('p.is_active', 1)
        ->where('prod.is_delete', 0)
        ->select([
            'p.*',
            'prod.part_no',
            'prod.part_name',
            'cust.code as customer_code',
            'model.name as model_name',
            'ms.spec_name as material_spec_name',
            'ms.coating_type',
            'u.code as unit_code',
            'u.name as unit_name',
            'r.code as rank_code',
            'r_master.code as revision_code',
            'ms_model.project_status as model_project_status'
        ]);
}
}
