<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\VaveBase;
use App\Models\InventoryModel\InventoryProduct;
use App\Models\InventoryModel\MaterialSpec;
use App\Models\InventoryModel\Unit;
use App\Models\InventoryModel\VaveBaseSuffix;
use App\Models\Products;
use App\Exports\VaveAnalysisExport;
use App\Imports\VaveBaseImport;
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
            // Get Baseline
            ->leftJoin('inv_m_vave_base as base', function($join) {
                $join->on('base.product_id', '=', 'p.id')
                     ->where('base.is_active', '=', 1);
            })
            // Get Latest Revision Weight (Highest sort_order in master)
            ->leftJoin(DB::raw('(
                SELECT product_id, weight_kg 
                FROM inv_t_product_detail t1
                JOIN inv_m_revision r1 ON r1.id = t1.revision_id
                WHERE r1.sort_order = (
                    SELECT MAX(r2.sort_order) 
                    FROM inv_t_product_detail t2 
                    JOIN inv_m_revision r2 ON r2.id = t2.revision_id
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
                'base.id as base_id',
                'base.weight_kg as baseline_weight',
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
        
        // Ordering - Align with Blade: 0:No, 1:Part No, 2:Part Name, 3:Customer, 4:Model, 5:Baseline, 6:Latest, 7:Status, 8:Action
        if ($request->has('order')) {
            $sortableColumns = [
                1 => 'p.part_no',
                2 => 'p.part_name',
                3 => 'c.code',
                4 => 'm.name',
                5 => 'base.weight_kg',
                6 => 'latest_weight',
                7 => 'weight_diff', 
            ];
            
            $colIndex = $request->input('order.0.column');
            $dir = $request->input('order.0.dir', 'desc');
            $colName = $sortableColumns[$colIndex] ?? 'p.part_no';

            if ($colName === 'weight_diff') {
                $query->orderByRaw("(base.weight_kg - latest_rev.weight_kg) {$dir}");
            } elseif ($colName === 'latest_weight') {
                $query->orderBy('latest_rev.weight_kg', $dir);
            } else {
                $query->orderBy($colName, $dir);
            }
        } else {
            $query->orderBy('p.part_no', 'asc');
        }
        
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        
        $data = $query->skip($start)->take($length)->get()->map(function($item) {
            $item->hash_id = Products::encodeHash($item->id);
            $item->has_base = $item->base_id ? 1 : 0;
            
            // Calculate Status
            $baseW = (float)($item->baseline_weight ?? 0);
            $actW = (float)($item->latest_weight ?? 0);
            
            if ($baseW > 0 && $actW > 0) {
                $diff = $baseW - $actW;
                
                // Use a larger epsilon (0.001 kg / 1 gram) to handle precision and UI alignment
                if ($diff > 0.001) {
                    $item->status = 'MERIT';
                } elseif ($diff < -0.001) {
                    $item->status = 'LOSS';
                } else {
                    $item->status = 'NO CHANGE';
                }
    
    $item->diff_kg = abs($diff);
    $item->diff_pct = (abs($diff) / $baseW) * 100;
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
    public function showBase($id)
    {
        $product = Products::findByHashOrFail($id);
        
        // Get Latest Base by default
        $base = VaveBase::with(['unit', 'materialSpec', 'suffix'])
            ->where('product_id', $product->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Get History
        $baseHistory = VaveBase::with(['unit', 'materialSpec', 'suffix'])
            ->where('product_id', $product->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'product' => $product,
            'base' => $base,
            'baseHistory' => $baseHistory,
            'baseSuffixes' => VaveBaseSuffix::where('customer_id', $product->customer_id)->where('is_active', 1)->get(),
            'materialSpecs' => MaterialSpec::select('id', 'spec_name')->get(),
            'units' => Unit::all(),
            'revisions' => InventoryProduct::with(['materialSpec', 'unit', 'revision'])
                ->join('inv_m_revision as r', 'r.id', '=', 'inv_t_product_detail.revision_id')
                ->where('product_id', $product->id)
                ->orderBy('r.sort_order', 'desc')
                ->select('inv_t_product_detail.*')
                ->get()
        ]);
    }

    /**
     * Store or Update RFQ data.
     */
    public function storeBase(Request $request)
    {
        $productId = Products::decodeHash($request->product_id);
        $baseId = $request->base_id ? VaveBase::decodeHash($request->base_id) : null;

        // Clean up empty/undefined values
        $data = $request->except(['base_id', 'product_id']);
        $nullableFields = ['material_spec_id', 'unit_id', 'vave_base_suffix_id', 'length', 'length_2', 'pitch', 'remark', 'base_name', 'material_price'];
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
        if (!empty($data['vave_base_suffix_id'])) {
            try { $data['vave_base_suffix_id'] = VaveBaseSuffix::decodeHash($data['vave_base_suffix_id']); } catch (\Exception $e) {}
        }

        $validated = validator($data, [
            'base_name' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'material_spec_id' => 'nullable|exists:inv_m_material_spec,id',
            'unit_id' => 'nullable|integer|exists:inv_m_unit,id',
            'vave_base_suffix_id' => 'nullable|integer|exists:inv_m_vave_base_suffix,id',
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
            VaveBase::where('product_id', $productId)->update(['is_active' => 0]);
            $validated['is_active'] = 1;

            if ($baseId) {
                // Update Existing
                $base = VaveBase::findOrFail($baseId);
                $base->update($validated);
                $message = 'Base updated successfully.';
            } else {
                // Create New
                if (empty($validated['base_name'])) {
                    $validated['base_name'] = 'Base ' . (VaveBase::where('product_id', $productId)->count() + 1);
                }
                VaveBase::create($validated);
                $message = 'New Base created successfully.';
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
        
        // Get All Baselines
        $bases = VaveBase::with(['materialSpec', 'unit', 'suffix'])
            ->where('product_id', $product->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Get All Production Revisions
        $revisions = InventoryProduct::with(['materialSpec', 'unit', 'revision'])
            ->join('inv_m_revision as r', 'r.id', '=', 'inv_t_product_detail.revision_id')
            ->where('product_id', $product->id)
            ->orderBy('r.sort_order', 'desc')
            ->select('inv_t_product_detail.*')
            ->get();
 
        return response()->json([
            'product' => $product,
            'bases' => $bases,
            'revisions' => $revisions
        ]);
    }

    /**
     * Delete an RFQ baseline.
     */
    public function destroyBase($id)
    {
        $base = VaveBase::findByHashOrFail($id);
        $productId = $base->product_id;
        $wasActive = $base->is_active;

        DB::beginTransaction();
        try {
            $base->delete();

            // If we deleted the active one, make the next most recent one active
            if ($wasActive) {
                $next = VaveBase::where('product_id', $productId)
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($next) {
                    $next->update(['is_active' => 1]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Base deleted successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export VAVE Analysis to Excel.
     */
    public function exportExcel(Request $request, $id)
    {
        $product = Products::findByHashOrFail($id);
        
        $bases = VaveBase::with(['materialSpec', 'unit', 'suffix'])
            ->where('product_id', $product->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        $revisions = InventoryProduct::with(['materialSpec', 'unit', 'revision'])
            ->join('inv_m_revision as r', 'r.id', '=', 'inv_t_product_detail.revision_id')
            ->where('product_id', $product->id)
            ->orderBy('r.sort_order', 'desc')
            ->select('inv_t_product_detail.*')
            ->get();
 
        if ($bases->isEmpty() || $revisions->isEmpty()) {
            return back()->with('error', 'Incomplete data for export.');
        }
 
        $fileName = 'VAVE_Analysis_' . $product->part_no . '_' . date('Ymd_His') . '.xlsx';
        
        return Excel::download(new VaveAnalysisExport([
            'product' => $product,
            'rfqs' => $bases,
            'revisions' => $revisions,
            'selected_base_id' => $request->base_id,
            'selected_actual_id' => $request->actual_id,
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

        // If target base names are selected
        $targetBaseNames = [];
        if ($request->has('base_names')) {
            $targetBaseNames = $request->input('base_names', []);
        }

        foreach ($products as $p) {
            // Get All Available Baselines for this product, ordered by name or creation
            $allProductBases = DB::table('inv_m_vave_base as base')
                ->leftJoin('inv_m_material_spec as ms', 'ms.id', '=', 'base.material_spec_id')
                ->leftJoin('inv_m_unit as u', 'u.id', '=', 'base.unit_id')
                ->leftJoin('inv_m_vave_base_suffix as sfx', 'sfx.id', '=', 'base.vave_base_suffix_id')
                ->where('base.product_id', $p->id)
                ->select('base.*', 'ms.spec_name as spec_name', 'u.name as unit_name', 'sfx.name as suffix_name')
                ->orderBy('base.base_name', 'asc') // This serves as versioning order
                ->get();
            
            // Get the list of all unique base names for this specific product (to handle fallback)
            $productBaseNamesList = $allProductBases->pluck('base_name')->unique()->values()->toArray();

            // Get Revisions
            $revisions = DB::table('inv_t_product_detail as rev_table')
                ->leftJoin('inv_m_material_spec as ms', 'ms.id', '=', 'rev_table.material_spec_id')
                ->leftJoin('inv_m_unit as u', 'u.id', '=', 'rev_table.unit_id')
                ->leftJoin('inv_m_revision as r', 'r.id', '=', 'rev_table.revision_id')
                ->where('rev_table.product_id', $p->id)
                ->select('rev_table.*', 'ms.spec_name as spec_name', 'u.name as unit_name', 'r.code as revision_code')
                ->orderBy('r.sort_order', 'asc')
                ->get();

            $p->stages = [];
            $filteredBases = [];

            if (!empty($targetBaseNames)) {
                // FILTERED EXPORT
                foreach ($targetBaseNames as $targetName) {
                    $selectedBase = $allProductBases->where('base_name', $targetName)->first();
                    if (!$selectedBase) {
                        $selectedBase = $allProductBases->where('base_name', '<', $targetName)->sortByDesc('base_name')->first();
                    }
                    if ($selectedBase && !in_array($selectedBase->id, array_column($filteredBases, 'id'))) {
                        $filteredBases[] = $selectedBase;
                    }
                }

                // If filter is used, the FIRST BASE in the resulting stages becomes the Baseline Reference
                if (count($filteredBases) > 0) {
                    $refBase = $filteredBases[0];
                    $sfxStr = ($refBase->suffix_name) ? ' - ' . $refBase->suffix_name : '';
                    $p->baseline_name = $refBase->base_name . $sfxStr;
                    $p->baseline_weight = (float)$refBase->weight_kg;
                    $p->baseline_cost = (float)$refBase->weight_kg * (float)($refBase->material_price ?? 0);
                    
                    // Store EBD Details for header
                    $p->ebd_spec = $refBase->spec_name;
                    $p->ebd_t = $refBase->thickness;
                    $p->ebd_w = $refBase->width;
                    $p->ebd_l1 = $refBase->length;
                    $p->ebd_l2 = $refBase->length_2;
                    $p->ebd_pitch = $refBase->pitch;

                    // Calculate Change Status for the SELECTED EBD vs its previous
                    $predecessor = $allProductBases->where('base_name', '<', $refBase->base_name)
                        ->sortByDesc('base_name')
                        ->first();
                    
                    $p->change_status = 'NEW';
                    if ($predecessor) {
                        $hasDiff = round((float)$refBase->weight_kg, 4) != round((float)$predecessor->weight_kg, 4)
                            || $refBase->material_spec_id != $predecessor->material_spec_id
                            || (float)$refBase->thickness != (float)$predecessor->thickness
                            || (float)$refBase->width != (float)$predecessor->width
                            || (float)$refBase->length != (float)$predecessor->length
                            || (float)$refBase->length_2 != (float)$predecessor->length_2
                            || (float)$refBase->pitch != (float)$predecessor->pitch;
                        
                        $p->change_status = $hasDiff ? 'CHANGE' : 'NO CHANGE';
                    }
                } else {
                    $p->baseline_name = '-';
                    $p->baseline_weight = 0;
                    $p->baseline_cost = 0;
                    $p->change_status = '-';
                }
            } else {
                // Determine Global Active Baseline
                $activeBase = $allProductBases->where('is_active', 1)->first() ?? $allProductBases->last();
                $sfxStr = ($activeBase && $activeBase->suffix_name) ? ' - ' . $activeBase->suffix_name : '';
                $p->baseline_name = $activeBase ? ($activeBase->base_name . $sfxStr) : '-';
                $p->baseline_weight = $activeBase ? (float)$activeBase->weight_kg : 0;
                $p->baseline_cost = $activeBase ? ((float)$activeBase->weight_kg * (float)($activeBase->material_price ?? 0)) : 0;

                // EBD Details
                $p->ebd_spec = $activeBase->spec_name ?? '-';
                $p->ebd_t = $activeBase->thickness ?? 0;
                $p->ebd_w = $activeBase->width ?? 0;
                $p->ebd_l1 = $activeBase->length ?? 0;
                $p->ebd_l2 = $activeBase->length_2 ?? 0;
                $p->ebd_pitch = $activeBase->pitch ?? 0;

                // Change status for active vs its predecessor
                if ($activeBase) {
                    $pIdx = $allProductBases->search(fn($b) => $b->id == $activeBase->id);
                    $predecessor = $pIdx > 0 ? $allProductBases[$pIdx - 1] : null;
                    if ($predecessor) {
                        $hasDiff = round((float)$activeBase->weight_kg, 4) != round((float)$predecessor->weight_kg, 4)
                            || $activeBase->material_spec_id != $predecessor->material_spec_id
                            || (float)$activeBase->thickness != (float)$predecessor->thickness;
                        $p->change_status = $hasDiff ? 'CHANGE' : 'NO CHANGE';
                    } else {
                        $p->change_status = 'NEW';
                    }
                } else {
                    $p->change_status = '-';
                }
            }

            // Map ONLY Revisions to stages
            foreach($revisions as $rev) {
                $p->stages[] = [
                    'source' => 'ACTUAL',
                    'name' => 'Revision ' . ($rev->revision_code ?? '-'),
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

        $fileName = 'VAVE_Summary';
        if ($request->customer_id) {
            $customer = DB::table('customers')->find($request->customer_id);
            if ($customer) $fileName .= '_' . str_replace(' ', '_', $customer->code);
        }
        if ($request->model_id) {
            $model = DB::table('models')->find($request->model_id);
            if ($model) $fileName .= '_' . str_replace(' ', '_', $model->name);
        }
        if ($request->has('base_names')) {
            $baseNames = $request->input('base_names', []);
            if (!empty($baseNames)) {
                $fileName .= '_' . str_replace(' ', '_', implode('_', $baseNames));
            }
        }
        $fileName .= '_' . date('Ymd_His') . '.xlsx';

        return Excel::download(new \App\Exports\VaveSummaryExport($data), $fileName);
    }

    /**
     * Get unique Base Names for a customer.
     */
    public function getBases(Request $request)
    {
        $query = DB::table('inv_m_vave_base as vbase')
            ->join('products as p', 'p.id', '=', 'vbase.product_id')
            ->where('p.is_delete', 0);
            
        if ($request->customer_id) {
            $query->where('p.customer_id', $request->customer_id);
        }
        
        $bases = $query->distinct()
            ->orderBy('vbase.base_name', 'asc')
            ->pluck('vbase.base_name');
            
        return response()->json($bases);
    }
    /**
     * Download Import Template
     */
    public function downloadTemplate()
    {
        $path = app_path('templates/VAVE_Analysis_Import_Template.xlsx');
        if (!file_exists($path)) {
            return back()->with('error', 'Template file not found.');
        }
        return response()->download($path);
    }

    /**
     * Import EBD Data from Excel.
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:51200', // 50MB max
            'sheet_name' => 'required|string'
        ]);

        try {
            $import = new VaveBaseImport($request->sheet_name);
            Excel::import($import, $request->file('file'));

            $errors = $import->getErrors();
            $log = $import->getSuccessLog();

            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Import completed with errors.',
                    'errors' => $errors,
                    'log' => $log
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Import successful.',
                'log' => $log
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Critical Error: ' . $e->getMessage()
            ], 500);
        }
    }
}

