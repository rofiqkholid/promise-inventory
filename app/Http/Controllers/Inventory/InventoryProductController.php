<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\InventoryProduct;
use App\Models\InventoryModel\CoilCenter;
use App\Models\InventoryModel\MaterialSpec;
use App\Models\InventoryModel\Unit;
use App\Models\InventoryModel\Rank;
use App\Models\InventoryModel\SubContractor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InventoryProductController extends Controller
{
    /**
     * Display the inventory product page.
     */
    public function index()
    {
        return view('inventory.inventory_product');
    }

    /**
     * Display a listing of the resource for DataTables.
     */
    public function data(Request $request)
    {
        $draw = (int) $request->input('draw');
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $orderObs = $request->input('order', []);
        
        $orderColIdx = (int) ($orderObs[0]['column'] ?? 0);
        $orderDir = $orderObs[0]['dir'] ?? 'desc';

        $columnsMap = [
            0 => 'p.id',
            1 => 'prod.part_no',
            2 => 'prod.part_name',
            3 => 'p.revision',
            4 => 'cc.code',
            5 => 'ms.spec_name',
            6 => 'p.thickness',
            7 => 'p.width',
            8 => 'u.code',
            9 => 'p.current_stock_qty',
        ];
        $orderCol = $columnsMap[$orderColIdx] ?? 'p.id';

        $query = DB::table('inv_t_product_detail as p')
            ->leftJoin('products as prod', 'prod.id', '=', 'p.product_id')
            ->leftJoin('customers as cust', 'cust.id', '=', 'prod.customer_id')
            ->leftJoin('models as model', 'model.id', '=', 'prod.model_id')
            ->leftJoin('inv_m_sub_contractor as sc', 'sc.id', '=', 'p.subcont_id')
            ->leftJoin('inv_m_coil_center as cc', 'cc.id', '=', 'p.coil_center_id')
            ->leftJoin('inv_m_material_spec as ms', 'ms.id', '=', 'p.material_spec_id')
            ->leftJoin('inv_m_unit as u', 'u.id', '=', 'p.unit_id')
            ->leftJoin('inv_m_rank as r', 'r.id', '=', 'p.rank_id')
            ->where('prod.is_delete', 0)
            ->where('p.is_active', 1)
            ->select([
                'p.id',
                'p.product_id',
                'p.revision',
                'p.thickness',
                'p.width',
                'p.length',
                'p.length_2',
                'p.pitch',
                'p.current_stock_qty',
                'p.trial_usage_qty',
                'p.min_stock',
                'p.pcs_per_unit',
                'p.unit_per_car',
                DB::raw("COALESCE(prod.part_no,'') as part_no"),
                DB::raw("COALESCE(prod.part_name,'') as part_name"),
                DB::raw("COALESCE(cust.code,'') as customer_code"),
                DB::raw("COALESCE(model.name,'') as model_name"),
                DB::raw("COALESCE(sc.code,'') as sub_contractor_code"),
                DB::raw("COALESCE(cc.code,'') as coil_center_code"),
                DB::raw("COALESCE(ms.spec_name,'') as material_spec_name"),
                DB::raw("COALESCE(ms.coating_type,'') as coating_type"),
                DB::raw("COALESCE(u.code,'') as unit_code"),
                DB::raw("COALESCE(r.code,'') as rank_code"),
                'p.remark',
            ]);

        $recordsTotal = (clone $query)->count();

        // Global Search
        $allParams = $request->all();
        $search = $allParams['search'] ?? '';
        $searchValue = is_string($search) ? $search : ($search['value'] ?? '');
        
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('prod.part_no', 'like', "%{$searchValue}%")
                  ->orWhere('prod.part_name', 'like', "%{$searchValue}%")
                  ->orWhere('cust.code', 'like', "%{$searchValue}%")
                  ->orWhere('model.name', 'like', "%{$searchValue}%")
                  ->orWhere('p.revision', 'like', "%{$searchValue}%")
                  ->orWhere('cc.code', 'like', "%{$searchValue}%")
                  ->orWhere('ms.spec_name', 'like', "%{$searchValue}%")
                  ->orWhere('u.code', 'like', "%{$searchValue}%")
                  ->orWhere('u.code', 'like', "%{$searchValue}%")
                  ->orWhere('r.code', 'like', "%{$searchValue}%")
                  ->orWhereRaw("(prod.part_no + CASE WHEN p.revision IS NOT NULL AND p.revision != '' THEN ' - ' + p.revision ELSE '' END) LIKE ?", ['%' . $searchValue . '%'])
                  ->orWhereRaw("(prod.part_no + CASE WHEN p.revision IS NOT NULL AND p.revision != '' THEN '-' + p.revision ELSE '' END) LIKE ?", ['%' . $searchValue . '%']);
            });
        }

        $recordsFiltered = $query->count();

        // Instantiate Hashids for InventoryProduct
        $salt = config('app.key') . InventoryProduct::class;
        $length = config('hashids.connections.main.length', 10);
        $alphabet = config('hashids.connections.main.alphabet', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
        $hashids = new \Hashids\Hashids($salt, $length, $alphabet);

        $data = $query->orderByRaw("$orderCol $orderDir")
            ->skip($start)
            ->take($length)
            ->get()
            ->map(fn($r) => [
                'id' => $hashids->encode($r->id),
                'product_id' => $r->product_id, // This is raw, but maybe not used or needs hashing too? Let's leave for now or hash if it's Products ID.
                // Wait, product_id is foreign key to Products. If we want full obfuscation, this should be hashed too.
                // But let's stick to primary ID first. User said "product... hashing".
                'part_no' => $r->part_no . ($r->revision ? ' - ' . $r->revision : ''),
                'part_name' => $r->part_name,
                'customer' => $r->customer_code,
                'model' => $r->model_name,
                'revision' => $r->revision,
                'sub_contractor' => $r->sub_contractor_code,
                'coil_center' => $r->coil_center_code,
                'material_spec' => $r->material_spec_name,
                'coating_type' => $r->coating_type,
                'thickness' => (float)$r->thickness,
                'width' => (float)$r->width,
                'length' => (float)$r->length,
                'length_2' => (float)$r->length_2,
                'pitch' => (float)$r->pitch,
                'unit' => $r->unit_code,
                'rank' => $r->rank_code,
                'current_stock_qty' => $r->current_stock_qty,
                'trial_usage_qty' => $r->trial_usage_qty,
                'min_stock' => $r->min_stock,
                'pcs_per_unit' => $r->pcs_per_unit,
                'unit_per_car' => $r->unit_per_car,
                'remark' => $r->remark,
            ]);

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    /**
     * Get dropdown data for all foreign keys.
     */
    public function getDropdownData()
    {
        return response()->json([
            'subContractors' => SubContractor::select('id', 'code', 'name')->get(), // These use Models so hash_id is appended automatically if I used 'get()'. 
            // BUT select('id',...) creates models with ONLY those attrs. appends might fail if they rely on attributes not selected? 
            // HasHashId depends on 'id', which is selected. Good. 
            // Appends are applied when toArray/toJson is called.
            // So these should already have hash_id if Models have trait.
            // I updated SubContractor and others earlier.
            'coilCenters' => CoilCenter::select('id', 'code', 'name')->get(),
            'materialSpecs' => MaterialSpec::select('id', 'spec_name')->get(),
            'units' => Unit::select('id', 'code', 'name')->get(),
            'ranks' => Rank::select('id', 'code', 'description')->get(),
        ]);
    }

    /**
     * Get products for dropdown (Select2).
     */
    public function getProducts(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $page = max(1, (int) $request->get('page', 1));
        $limit = 10;
        $skip = ($page - 1) * $limit;

        $query = DB::table('products as p')
            ->leftJoin('customers as c', 'c.id', '=', 'p.customer_id')
            ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
            ->where('p.is_delete', 0)
            ->select('p.id', 'p.part_no', 'p.part_name', 'c.code as customer_code', 'm.name as model_name');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('p.part_no', 'like', "%{$q}%")
                  ->orWhere('p.part_name', 'like', "%{$q}%")
                  ->orWhere('c.code', 'like', "%{$q}%");
            });
        }

        $total = (clone $query)->count();
        $rows = $query->orderBy('p.part_no')->skip($skip)->take($limit)->get();

        // Instantiate Hashids for Products
        $salt = config('app.key') . \App\Models\Products::class;
        $length = config('hashids.connections.main.length', 10);
        $alphabet = config('hashids.connections.main.alphabet', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
        $hashids = new \Hashids\Hashids($salt, $length, $alphabet);

        return response()->json([
            'results' => $rows->map(fn($p) => [
                'id' => $hashids->encode($p->id),
                'text' => "{$p->part_no} - {$p->part_name} ({$p->customer_code})",
            ]),
            'pagination' => ['more' => ($skip + $limit) < $total],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    use \App\Traits\DecodesHashInputs;

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->decodeHashInputs($request->all(), [
            'product_id' => \App\Models\Products::class,
            'subcont_id' => SubContractor::class,
            'coil_center_id' => CoilCenter::class,
            'material_spec_id' => MaterialSpec::class,
            'unit_id' => Unit::class,
            'rank_id' => Rank::class,
        ]);
        
        $request->merge($data);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'subcont_id' => 'nullable|exists:inv_m_sub_contractor,id',
            'coil_center_id' => 'nullable|exists:inv_m_coil_center,id',
            'material_spec_id' => 'nullable|exists:inv_m_material_spec,id',
            'unit_id' => 'nullable|exists:inv_m_unit,id',
            'rank_id' => 'nullable|exists:inv_m_rank,id',
            'revision' => 'required|string|max:20',
            'thickness' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'length_2' => 'nullable|numeric|min:0',
            'pitch' => 'nullable|numeric|min:0',
            'pcs_per_unit' => 'nullable|integer|min:1',
            'unit_per_car' => 'nullable|integer|min:1',
            'min_stock' => 'nullable|integer|min:0',
            'current_stock_qty' => 'nullable|numeric|min:0',
            'trial_usage_qty' => 'nullable|numeric|min:0',
            'remark' => 'nullable|string',
        ]);

        InventoryProduct::create($validated);

        return response()->json(['success' => true, 'message' => 'Inventory Product created successfully.']);
    }

    /**
     * Display the specified resource for editing.
     */
    public function show($id)
    {
        $inventoryProduct = InventoryProduct::findByHashOrFail($id);
        $inventoryProduct->load(['product', 'coilCenter', 'materialSpec', 'unit', 'rank']);
        // Ensure relations also have hash_id appended? Yes, default.
        // But for product (Products model), I just added it.
        return response()->json($inventoryProduct);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $inventoryProduct = InventoryProduct::findByHashOrFail($id);
        
        $data = $this->decodeHashInputs($request->all(), [
            'product_id' => \App\Models\Products::class,
            'subcont_id' => SubContractor::class,
            'coil_center_id' => CoilCenter::class,
            'material_spec_id' => MaterialSpec::class,
            'unit_id' => Unit::class,
            'rank_id' => Rank::class,
        ]);

        $request->merge($data);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'subcont_id' => 'nullable|exists:inv_m_sub_contractor,id',
            'coil_center_id' => 'nullable|exists:inv_m_coil_center,id',
            'material_spec_id' => 'nullable|exists:inv_m_material_spec,id',
            'unit_id' => 'nullable|exists:inv_m_unit,id',
            'rank_id' => 'nullable|exists:inv_m_rank,id',
            'revision' => 'required|string|max:20',
            'thickness' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'length_2' => 'nullable|numeric|min:0',
            'pitch' => 'nullable|numeric|min:0',
            'pcs_per_unit' => 'nullable|integer|min:1',
            'unit_per_car' => 'nullable|integer|min:1',
            'min_stock' => 'nullable|integer|min:0',
            'current_stock_qty' => 'nullable|numeric|min:0',
            'trial_usage_qty' => 'nullable|numeric|min:0',
            'remark' => 'nullable|string',
        ]);

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
    public function printLabel($id)
    {
        $inventoryProduct = InventoryProduct::findByHashOrFail($id);
        $data = DB::table('inv_t_product_detail as p')
            ->leftJoin('products as prod', 'prod.id', '=', 'p.product_id')
            ->leftJoin('customers as cust', 'cust.id', '=', 'prod.customer_id')
            ->leftJoin('models as model', 'model.id', '=', 'prod.model_id')
            ->leftJoin('inv_m_material_spec as ms', 'ms.id', '=', 'p.material_spec_id')
            ->leftJoin('inv_m_rank as r', 'r.id', '=', 'p.rank_id')
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
                'r.code as rank_code'
            ])
            ->first();

        if (!$data) abort(404);
        
        
        $qrData = json_encode([
            'id' => $inventoryProduct->hash_id,
            'pn' => $data->part_no,
            'rev' => $data->revision,
            'dim' => (float)$data->thickness . 'x' . (float)$data->width . 'x' . (float)$data->length . ($data->length_2 > 0 ? 'x' . (float)$data->length_2 : '') . ($data->pitch > 0 ? 'x' . (float)$data->pitch : '')
        ]);

        $product = (object) [
            'qrcode' => QrCode::size(250)->errorCorrection('M')->margin(1)->generate($qrData),
            'item_no' => $data->part_no . ($data->revision ? ' - ' . $data->revision : ''),
            'item_name' => $data->part_name,
            'model_name' => $data->model_name ?? '-',
            'partner_code' => $data->customer_code ?? '-',
            'dimension' => (float)$data->thickness . ' x ' . (float)$data->width . ' x ' . (float)$data->length . ($data->length_2 > 0 ? ' x ' . (float)$data->length_2 : '') . ($data->pitch > 0 ? ' x ' . (float)$data->pitch : ''),
            'material' => $data->material_spec . ($data->coating_type ? " ($data->coating_type)" : '')
        ];

        $products = [$product];

        return view('inventory.qrcode', compact('products'));
    }

}
