<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\ProductRfq;
use App\Models\InventoryModel\InventoryProduct;
use App\Models\InventoryModel\MaterialSpec;
use App\Models\InventoryModel\Unit;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VaveAnalysisController extends Controller
{
    /**
     * Display the VAVE Analysis page.
     */
    public function index()
    {
        return view('inventory.vave.index');
    }

    /**
     * Get data for DataTables.
     */
    public function data(Request $request)
    {
        $query = DB::table('products as p')
            ->join('inv_t_product_detail as inv', 'inv.product_id', '=', 'p.id')
            ->leftJoin('customers as c', 'c.id', '=', 'p.customer_id')
            ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
            ->leftJoin('inv_m_product_rfq as rfq', 'rfq.product_id', '=', 'p.id')
            ->where('p.is_delete', 0)
            ->select([
                'p.id',
                'p.part_no',
                'p.part_name',
                'c.code as customer_code',
                'm.name as model_name',
                DB::raw("MAX(rfq.id) as rfq_id"),
                DB::raw("MAX(rfq.weight_kg) as rfq_weight"),
                DB::raw("MAX(rfq.updated_at) as rfq_updated_at"),
                DB::raw("COUNT(inv.id) as revision_count")
            ])
            ->groupBy('p.id', 'p.part_no', 'p.part_name', 'c.code', 'm.name');

        // Add has_rfq check in the map or as raw
        // Using subquery or raw check

        // Global Search
        $search = $request->input('search.value');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('p.part_no', 'like', "%{$search}%")
                  ->orWhere('p.part_name', 'like', "%{$search}%")
                  ->orWhere('c.code', 'like', "%{$search}%")
                  ->orWhere('m.name', 'like', "%{$search}%");
            });
        }

        $total = $query->count();
        
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        
        $data = $query->skip($start)->take($length)->get()->map(function($item) {
            $item->hash_id = Products::encodeHash($item->id);
            $item->has_rfq = $item->rfq_id ? 1 : 0;
            return $item;
        });

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data
        ]);
    }

    /**
     * Get RFQ detail for a product.
     */
    public function showRfq($id)
    {
        $product = Products::findByHashOrFail($id);
        $rfq = ProductRfq::with('unit')->where('product_id', $product->id)->first();
        
        return response()->json([
            'product' => $product,
            'rfq' => $rfq,
            'materialSpecs' => MaterialSpec::select('id', 'spec_name')->get(),
            'units' => Unit::all(),
        ]);
    }

    /**
     * Store or Update RFQ data.
     */
    public function storeRfq(Request $request)
    {
        $productId = Products::decodeHash($request->product_id);

        // Clean up empty/undefined values and decode hash_id
        $data = $request->all();
        $nullableFields = ['material_spec_id', 'unit_id', 'length', 'length_2', 'pitch', 'remark'];
        foreach ($nullableFields as $field) {
            if (isset($data[$field]) && (empty($data[$field]) || $data[$field] === 'undefined' || $data[$field] === '')) {
                $data[$field] = null;
            }
        }

        // Decode unit hash_id to id before validation
        if (!empty($data['unit_id'])) {
            try {
                $data['unit_id'] = Unit::decodeHash($data['unit_id']);
            } catch (\Exception $e) {
                // If decode fails, keep original value (might already be an ID)
            }
        }

        $validated = validator($data, [
            'product_id' => 'required',
            'material_spec_id' => 'nullable|exists:inv_m_material_spec,id',
            'unit_id' => 'nullable|integer|exists:inv_m_unit,id',
            'thickness' => 'required|numeric|min:0',
            'width' => 'required|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'length_2' => 'nullable|numeric|min:0',
            'pitch' => 'nullable|numeric|min:0',
            'density' => 'required|numeric|min:0',
            'weight_kg' => 'required|numeric|min:0',
            'remark' => 'nullable|string',
        ])->validate();

        $validated['product_id'] = $productId;

        ProductRfq::updateOrCreate(
            ['product_id' => $productId],
            $validated
        );

        return response()->json(['success' => true, 'message' => 'RFQ Data saved successfully.']);
    }

    /**
     * Get Comparison Data.
     */
    public function getComparison($id)
    {
        $product = Products::findByHashOrFail($id);
        
        // Get RFQ (Baseline)
        $rfq = ProductRfq::with(['materialSpec', 'unit'])->where('product_id', $product->id)->first();
        
        // Get All Production Revisions
        $revisions = InventoryProduct::with(['materialSpec', 'unit'])
            ->where('product_id', $product->id)
            ->orderBy('revision', 'asc')
            ->get();

        return response()->json([
            'product' => $product,
            'rfq' => $rfq,
            'revisions' => $revisions
        ]);
    }
}
