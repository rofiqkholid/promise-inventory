<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\ProductRfq;
use App\Models\InventoryModel\InventoryProduct;
use App\Models\InventoryModel\MaterialSpec;
use App\Models\InventoryModel\Unit;
use App\Models\Products;
use App\Exports\VaveAnalysisExport;
use Maatwebsite\Excel\Facades\Excel;
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
            ->leftJoin('customers as c', 'c.id', '=', 'p.customer_id')
            ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
            // Get Baseline (Active RFQ)
            ->leftJoin('inv_m_product_rfq as rfq', function($join) {
                $join->on('rfq.product_id', '=', 'p.id')
                     ->where('rfq.is_active', '=', 1);
            })
            // Get Latest Revision Weight
            ->leftJoin(DB::raw('(
                SELECT product_id, weight_kg 
                FROM inv_t_product_detail t1
                WHERE revision = (
                    SELECT MAX(revision) 
                    FROM inv_t_product_detail t2 
                    WHERE t2.product_id = t1.product_id
                )
            ) as latest_rev'), 'latest_rev.product_id', '=', 'p.id')
            ->where('p.is_delete', 0)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('inv_t_product_detail')
                  ->whereColumn('inv_t_product_detail.product_id', 'p.id')
                  ->where('inv_t_product_detail.is_active', 1);
            })
            ->select([
                'p.id',
                'p.part_no',
                'p.part_name',
                'c.code as customer_code',
                'm.name as model_name',
                'rfq.id as rfq_id',
                'rfq.weight_kg as baseline_weight',
                'latest_rev.weight_kg as latest_weight'
            ]);

        $recordsTotal = (clone $query)->count();

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

        // Customer & Model Filters
        if ($request->customer_id) {
            $query->where('p.customer_id', $request->customer_id);
        }
        if ($request->model_id) {
            $query->where('p.model_id', $request->model_id);
        }

        $recordsFiltered = $query->count();
        
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        
        $data = $query->skip($start)->take($length)->get()->map(function($item) {
            $item->hash_id = Products::encodeHash($item->id);
            $item->has_rfq = $item->rfq_id ? 1 : 0;
            
            // Calculate Status
            $baseW = (float)($item->baseline_weight ?? 0);
            $actW = (float)($item->latest_weight ?? 0);
            
            if ($baseW > 0 && $actW > 0) {
                $diff = $baseW - $actW;
                $item->status = $diff >= 0 ? 'MERIT' : 'LOSS';
                $item->diff_kg = abs($diff);
                $item->diff_pct = (abs($diff) / $baseW) * 100;
            } else {
                $item->status = $item->rfq_id ? 'WAITING ACTUAL' : 'NO BASELINE';
                $item->diff_kg = 0;
                $item->diff_pct = 0;
            }
            
            return $item;
        });

        return response()->json([
            'draw' => (int)$request->input('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }

    /**
     * Get RFQ detail for a product.
     */
    public function showRfq($id)
    {
        $product = Products::findByHashOrFail($id);
        
        // Get Latest RFQ by default (user request)
        $rfq = ProductRfq::with(['unit', 'materialSpec'])
            ->where('product_id', $product->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Get History
        $rfqHistory = ProductRfq::with(['unit', 'materialSpec'])
            ->where('product_id', $product->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'product' => $product,
            'rfq' => $rfq,
            'rfqHistory' => $rfqHistory,
            'materialSpecs' => MaterialSpec::select('id', 'spec_name')->get(),
            'units' => Unit::all(),
            'revisions' => InventoryProduct::with(['materialSpec', 'unit'])
                ->where('product_id', $product->id)
                ->orderBy('revision', 'desc')
                ->get()
        ]);
    }

    /**
     * Store or Update RFQ data.
     */
    public function storeRfq(Request $request)
    {
        $productId = Products::decodeHash($request->product_id);
        $rfqId = $request->rfq_id ? ProductRfq::decodeHash($request->rfq_id) : null;

        // Clean up empty/undefined values
        $data = $request->except(['rfq_id', 'product_id']);
        $nullableFields = ['material_spec_id', 'unit_id', 'length', 'length_2', 'pitch', 'remark', 'rfq_name', 'material_price'];
        foreach ($nullableFields as $field) {
            if (isset($data[$field]) && (empty($data[$field]) || $data[$field] === 'undefined' || $data[$field] === '')) {
                $data[$field] = null;
            }
        }

        // Decode relations
        if (!empty($data['unit_id'])) {
            try { $data['unit_id'] = Unit::decodeHash($data['unit_id']); } catch (\Exception $e) {}
        }
        if (!empty($data['material_spec_id'])) {
            try { $data['material_spec_id'] = MaterialSpec::decodeHash($data['material_spec_id']); } catch (\Exception $e) {}
        }

        $validated = validator($data, [
            'rfq_name' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'material_spec_id' => 'nullable|exists:inv_m_material_spec,id',
            'unit_id' => 'nullable|integer|exists:inv_m_unit,id',
            'thickness' => 'required|numeric|min:0',
            'width' => 'required|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'length_2' => 'nullable|numeric|min:0',
            'pitch' => 'nullable|numeric|min:0',
            'density' => 'required|numeric|min:0',
            'weight_kg' => 'required|numeric|min:0',
            'net_weight' => 'nullable|numeric|min:0',
            'material_price' => 'nullable|numeric|min:0',
            'remark' => 'nullable|string',
        ])->validate();

        $validated['product_id'] = $productId;

        DB::beginTransaction();
        try {
            // Automatic Active Logic: The latest saved/updated baseline always becomes active
            ProductRfq::where('product_id', $productId)->update(['is_active' => 0]);
            $validated['is_active'] = 1;

            if ($rfqId) {
                // Update Existing
                $rfq = ProductRfq::findOrFail($rfqId);
                $rfq->update($validated);
                $message = 'Baseline updated successfully.';
            } else {
                // Create New
                if (empty($validated['rfq_name'])) {
                    $validated['rfq_name'] = 'Baseline ' . (ProductRfq::where('product_id', $productId)->count() + 1);
                }
                ProductRfq::create($validated);
                $message = 'New Baseline created successfully.';
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => $message]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get Comparison Data.
     */
    public function getComparison($id)
    {
        $product = Products::findByHashOrFail($id);
        
        // Get All Baselines (RFQ History)
        $rfqs = ProductRfq::with(['materialSpec', 'unit'])
            ->where('product_id', $product->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Get All Production Revisions
        $revisions = InventoryProduct::with(['materialSpec', 'unit'])
            ->where('product_id', $product->id)
            ->orderBy('revision', 'desc')
            ->get();

        return response()->json([
            'product' => $product,
            'rfqs' => $rfqs,
            'revisions' => $revisions
        ]);
    }

    /**
     * Delete an RFQ baseline.
     */
    public function destroyRfq($id)
    {
        $rfq = ProductRfq::findByHashOrFail($id);
        $productId = $rfq->product_id;
        $wasActive = $rfq->is_active;

        DB::beginTransaction();
        try {
            $rfq->delete();

            // If we deleted the active one, make the next most recent one active
            if ($wasActive) {
                $next = ProductRfq::where('product_id', $productId)
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($next) {
                    $next->update(['is_active' => 1]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Baseline deleted successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export VAVE Analysis to Excel.
     */
    public function exportExcel($id)
    {
        $product = Products::findByHashOrFail($id);
        
        $rfqs = ProductRfq::with(['materialSpec', 'unit'])
            ->where('product_id', $product->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        $revisions = InventoryProduct::with(['materialSpec', 'unit'])
            ->where('product_id', $product->id)
            ->orderBy('revision', 'desc')
            ->get();

        if ($rfqs->isEmpty() || $revisions->isEmpty()) {
            return back()->with('error', 'Incomplete data for export.');
        }

        $fileName = 'VAVE_Analysis_' . $product->part_no . '_' . date('Ymd_His') . '.xlsx';
        
        return Excel::download(new VaveAnalysisExport([
            'product' => $product,
            'rfqs' => $rfqs,
            'revisions' => $revisions
        ]), $fileName);
    }

    /**
     * Export Summary Report for Customer/Model.
     */
    public function exportSummary(Request $request)
    {
        $query = DB::table('products as p')
            ->leftJoin('customers as c', 'c.id', '=', 'p.customer_id')
            ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
            ->where('p.is_delete', 0)
            ->select('p.id', 'p.part_no', 'p.part_name', 'c.code as customer_code', 'm.name as model_name')
            ->orderBy('c.code')
            ->orderBy('m.name')
            ->orderBy('p.part_no');

        if ($request->customer_id) $query->where('p.customer_id', $request->customer_id);
        if ($request->model_id) $query->where('p.model_id', $request->model_id);

        $products = $query->get();
        $data = [];

        foreach ($products as $p) {
            // Get RFQs
            $rfqs = DB::table('inv_m_product_rfq as rfq')
                ->leftJoin('inv_m_material_spec as ms', 'ms.id', '=', 'rfq.material_spec_id')
                ->leftJoin('inv_m_unit as u', 'u.id', '=', 'rfq.unit_id')
                ->where('rfq.product_id', $p->id)
                ->select('rfq.*', 'ms.spec_name as spec_name', 'u.name as unit_name')
                ->orderBy('rfq.created_at', 'asc')
                ->get();

            // Get Revisions
            $revisions = DB::table('inv_t_product_detail as rev')
                ->leftJoin('inv_m_material_spec as ms', 'ms.id', '=', 'rev.material_spec_id')
                ->leftJoin('inv_m_unit as u', 'u.id', '=', 'rev.unit_id')
                ->where('rev.product_id', $p->id)
                // Remove ->where('rev.is_active', 1) to show full history
                ->select('rev.*', 'ms.spec_name as spec_name', 'u.name as unit_name')
                ->orderBy('rev.revision', 'asc')
                ->get();

            $p->stages = [];
            
            // Identify Baseline (Active RFQ)
            $activeRfq = $rfqs->where('is_active', 1)->first() ?? $rfqs->last();
            $p->baseline_name = $activeRfq ? ($activeRfq->rfq_name ?? 'Baseline') : '-';
            $p->baseline_weight = $activeRfq ? (float)$activeRfq->weight_kg : 0;
            $p->baseline_cost = $activeRfq ? ((float)$activeRfq->weight_kg * (float)($activeRfq->material_price ?? 0)) : 0;

            // Map RFQs to stages (excluding the active baseline from "Stages" if preferred, 
            // but let's keep all for now and handle "Baseline" visibility in blade as requested)
            foreach($rfqs as $rfq) {
                $p->stages[] = [
                    'source' => 'RFQ',
                    'name' => $rfq->rfq_name ?? 'Baseline',
                    'spec' => $rfq->spec_name,
                    'unit' => $rfq->unit_name,
                    't' => $rfq->thickness,
                    'w' => $rfq->width,
                    'l1' => $rfq->length,
                    'l2' => $rfq->length_2,
                    'pitch' => $rfq->pitch,
                    'theoretical_weight' => $rfq->weight_kg,
                    'net_weight' => $rfq->net_weight,
                    'material_price' => $rfq->material_price,
                    'cost' => $rfq->weight_kg * ($rfq->material_price ?? 0),
                    'budomari' => $rfq->weight_kg > 0 ? ($rfq->net_weight / $rfq->weight_kg) * 100 : 0,
                    'is_baseline' => ($activeRfq && $rfq->id == $activeRfq->id)
                ];
            }

            // Map Revisions to stages
            foreach($revisions as $rev) {
                $p->stages[] = [
                    'source' => 'ACTUAL',
                    'name' => 'Revision ' . $rev->revision,
                    'spec' => $rev->spec_name,
                    'unit' => $rev->unit_name,
                    't' => $rev->thickness,
                    'w' => $rev->width,
                    'l1' => $rev->length,
                    'l2' => $rev->length_2,
                    'pitch' => $rev->pitch,
                    'theoretical_weight' => $rev->weight_kg,
                    'net_weight' => $rev->net_weight,
                    'material_price' => $rev->material_price,
                    'cost' => $rev->weight_kg * ($rev->material_price ?? 0),
                    'budomari' => $rev->weight_kg > 0 ? ($rev->net_weight / $rev->weight_kg) * 100 : 0,
                    'is_baseline' => false
                ];
            }

            if (count($p->stages) > 0) {
                $data[] = $p;
            }
        }

        $fileName = 'VAVE_Summary_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new \App\Exports\VaveSummaryExport($data), $fileName);
    }
}
