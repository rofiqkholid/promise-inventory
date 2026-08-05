<?php

namespace App\Http\Controllers\Inventory\Material;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\Material\InventoryProduct;
use App\Models\InventoryModel\Material\MaterialSpec;
use App\Models\InventoryModel\Material\Rank;
use App\Models\InventoryModel\Material\Unit;
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
        // For filters: Only show customers and models that are actually in inv_t_product_detail
        $filterCustomers = DB::table('customers as c')
            ->join('products as p', 'p.customer_id', '=', 'c.id')
            ->join('inv_t_product_detail as pd', 'pd.product_id', '=', 'p.id')
            ->where('pd.is_active', 1)
            ->select('c.id', 'c.code')
            ->distinct()
            ->orderBy('c.code')
            ->get();

        $filterModels = DB::table('models as m')
            ->join('inv_t_product_detail as pd', 'pd.model_id', '=', 'm.id')
            ->where('pd.is_active', 1)
            ->select(DB::raw('MIN(m.id) as id'), 'm.name')
            ->groupBy('m.name')
            ->orderBy('m.name')
            ->get();

        // For Import & Add: We need ALL customers
        $customers = DB::table('customers')->select('id', 'code')->orderBy('code')->get();

        return view('inventory.material.master-data.product', compact('customers', 'filterCustomers', 'filterModels'));
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
            $query->where('part_no', 'like', "%{$request->part_no}%");
        }
        if ($request->filled('incomplete_only')) {
            $query->where(function($q) {
                $q->whereRaw("LOWER(COALESCE(u.name, '')) LIKE '%coil%'")
                  ->where(function($qq) {
                      $qq->whereRaw("ISNULL(p.gross_coil, 0) <= 0")
                         ->orWhereRaw("ISNULL(p.top_coil, 0) <= 0")
                         ->orWhereRaw("ISNULL(p.end_coil, 0) <= 0")
                         ->orWhereRaw("ISNULL(p.pitch, 0) <= 0");
                  });
            });
        }
        if ($request->filled('project_status')) {
            $query->where('ms_model.project_status', $request->project_status);
        }
        if ($request->filled('product_status')) {
            $query->where('p.product_status', $request->product_status);
        }

        // Global Search
        $searchValue = $request->input('search.value');
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('prod.part_no', 'like', "%{$searchValue}%")
                  ->orWhere('p.partno_epicor', 'like', "%{$searchValue}%")
                  ->orWhere('prod.part_name', 'like', "%{$searchValue}%")
                  ->orWhere('cust.code', 'like', "%{$searchValue}%")
                  ->orWhere('model.name', 'like', "%{$searchValue}%")
                  ->orWhere('ms.spec_name', 'like', "%{$searchValue}%")
                  ->orWhere('r.code', 'like', "%{$searchValue}%")
                  ->orWhereRaw("(prod.part_no + '-' + ISNULL(r_master.code, '')) LIKE ?", ['%' . $searchValue . '%']);
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

        if ($length !== -1) {
            $query->skip($start)->take($length);
        }

        $data = $query->get()
            ->map(fn($r) => [
                'id' => InventoryProduct::encodeHash($r->id),
                'product_id' => $r->product_id,
                'part_no' => $r->part_no . ($r->revision_code ? '-' . $r->revision_code : ''),
                'partno_epicor' => $r->partno_epicor ?? '-',
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
                'pcs_per_pitch' => (int)$r->pcs_per_pitch,
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
                'is_incomplete_coil' => str_contains(strtolower($r->unit_name), 'coil') && ($r->gross_coil <= 0 || $r->top_coil <= 0 || $r->end_coil <= 0 || $r->pitch <= 0),
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
        if ($request->filled('project_status')) {
            $query->where('ms_model.project_status', $request->project_status);
        }
        if ($request->filled('product_status')) {
            $query->where('p.product_status', $request->product_status);
        }

        // Global Search
        $searchValue = $request->input('search');
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('prod.part_no', 'like', "%{$searchValue}%")
                  ->orWhere('p.partno_epicor', 'like', "%{$searchValue}%")
                  ->orWhere('prod.part_name', 'like', "%{$searchValue}%")
                  ->orWhere('cust.code', 'like', "%{$searchValue}%")
                  ->orWhere('model.name', 'like', "%{$searchValue}%")
                  ->orWhere('ms.spec_name', 'like', "%{$searchValue}%")
                  ->orWhere('r.code', 'like', "%{$searchValue}%");
            });
        }

        $data = $query->orderBy('prod.part_no', 'asc')->get();

        $fileNameBase = 'All Inventory Product Master';
        $customerCode = '';
        $modelName = '';

        if ($request->filled('customer_id')) {
            $customer = DB::table('customers')->where('id', $request->customer_id)->first();
            if ($customer) {
                $customerCode = $customer->code;
            }
        }

        if ($request->filled('model_id')) {
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
            $fileNameBase = implode(' ', $parts) . ' Inventory Product';
        }

        $fileNameClean = preg_replace('/[^A-Za-z0-9 _-]/', '_', $fileNameBase);
        $fileName = $fileNameClean . '_' . date('Ymd') . '.xlsx';

        return Excel::download(new InventoryProductExport($data), $fileName);
    }

    /**
     * Download Excel template for import.
     */
    public function downloadTemplate()
    {
        $path = base_path('app/templates/Inventory_Product_Import_Template.xlsx');
        
        // If user has uploaded their manual file, prioritize it
        if (file_exists($path)) {
            return response()->download($path, 'Inventory_Product_Import_Template.xlsx');
        }

        // Fallback to auto-generated one if static file not found
        return Excel::download(new \App\Exports\InventoryProductTemplateExport, 'Inventory_Product_Template_Standard.xlsx');
    }

    /**
     * Import data from Excel.
     */
    public function importExcel(Request $request)
    {
        if (!auth()->user()->hasMenuPermission('inventory.master.product.index', 'create')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'customer_id' => 'required',
            'model_id' => 'required',
            'sheet_name' => 'required|string|max:50',
        ]);

        $fileToImport = null;
        $tmpPath = null;

        if ($request->has('chunk_index')) {
            $chunkIndex = $request->input('chunk_index');
            $totalChunks = $request->input('total_chunks');
            $uploadId = $request->input('upload_id');
            $chunkData = $request->input('file_base64_chunk');
            
            $chunksPath = storage_path('app/chunks');
            if (!file_exists($chunksPath)) {
                mkdir($chunksPath, 0777, true);
            }
            $tmpTxtPath = $chunksPath . '/upload_' . $uploadId . '.txt';
            
            // Append chunk with exclusive lock to prevent any race condition
            file_put_contents($tmpTxtPath, $chunkData, FILE_APPEND | LOCK_EX);
            
            if ($chunkIndex < $totalChunks - 1) {
                return response()->json(['success' => true, 'message' => 'Chunk processed']);
            }
            
            // All chunks received
            $fullBase64 = file_get_contents($tmpTxtPath);
            @unlink($tmpTxtPath);
            
            $fileContent = base64_decode($fullBase64);
            
            $tmpPath = $chunksPath . '/' . uniqid('import_') . '.xlsx';
            file_put_contents($tmpPath, $fileContent);
            $fileToImport = $tmpPath;
        } else {
            // Fallback for regular upload if they bypass our JS
            $request->validate(['file' => 'required|mimes:xlsx,xls,csv|max:51200']); // 50MB max
            $fileToImport = $request->file('file');
        }

        $import = new \App\Imports\InventoryProductImport(
            $request->customer_id, 
            $request->model_id, 
            $request->sheet_name
        );
        Excel::import($import, $fileToImport);

        if ($tmpPath && file_exists($tmpPath)) {
            @unlink($tmpPath);
        }

        if (!empty($import->getErrors())) {
            $errorCount = count($import->getErrors());
            $errorMsg = "<div class='text-rose-600 font-bold mb-2 uppercase text-[9px]'><i class='fa-solid fa-triangle-exclamation mr-1'></i> Import blocked by {$errorCount} errors:</div>";
            $errorMsg .= "<ul class='list-inside space-y-1 text-gray-600 font-medium'>";
            foreach (array_slice($import->getErrors(), 0, 15) as $err) {
                $errorMsg .= "<li>• {$err}</li>";
            }
            $errorMsg .= "</ul>";
            if ($errorCount > 15) {
                $errorMsg .= "<div class='mt-2 font-bold text-gray-400 italic'>... and " . ($errorCount - 15) . " more errors.</div>";
            }
            
            return response()->json([
                'success' => false,
                'message' => $errorMsg
            ], 422);
        }
        $log = $import->getSuccessLog();
        $totalCreated = count($log['created']);
        $totalUpdated = count($log['updated']);
        $unchanged = $log['unchangedCount'];
        $totalProcessed = $totalCreated + $totalUpdated + $unchanged;
        
        $successMsg = "<div class='mb-3 font-bold text-emerald-700 uppercase drop-shadow-sm text-[11px]'><i class='fa-solid fa-circle-check mr-1.5'></i> Processed {$totalProcessed} revisions!</div>";
        
        $summaryLines = [];
        if ($totalCreated > 0) {
            $summaryLines[] = "<div class='text-emerald-600 font-bold text-[10px]'><i class='fa-solid fa-plus-circle mr-1'></i> {$totalCreated} new revisions created</div>";
        }
        
        if ($totalUpdated > 0) {
            $summaryLines[] = "<div class='text-amber-600 font-bold text-[10px]'><i class='fa-solid fa-pen-to-square mr-1'></i> {$totalUpdated} revisions updated</div>";
            // Show first 10 updates as details
            $updateDetails = array_slice($log['updated'], 0, 10);
            $updateList = "<ul class='mt-1 text-[9px] text-gray-500 pl-4 list-disc space-y-0.5'>";
            foreach ($updateDetails as $item) {
                $updateList .= "<li>{$item}</li>";
            }
            if (count($log['updated']) > 10) $updateList .= "<li class='italic'>... and " . (count($log['updated']) - 10) . " more.</li>";
            $updateList .= "</ul>";
            $summaryLines[] = $updateList;
        }

        if ($unchanged > 0) {
            $summaryLines[] = "<div class='text-gray-500 italic text-[10px]'><i class='fa-solid fa-check-double mr-1'></i> {$unchanged} other revisions already up-to-date (no changes needed)</div>";
        }

        if (!empty($summaryLines)) {
            $successMsg .= "<div class='space-y-1 border-t border-emerald-100 pt-2 mt-2'>" . implode('', $summaryLines) . "</div>";
        }

        return response()->json([
            'success' => true,
            'message' => $successMsg,
        ]);
    }

    /**
     * Get sheet names from uploaded file.
     */
    public function getSheetNames(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:51200' // 50MB max
        ]);

        try {
            $filePath = $request->file('file')->getRealPath();
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
            $sheetNames = $reader->listWorksheetNames($filePath);

            return response()->json([
                'success' => true,
                'sheets' => $sheetNames
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasMenuPermission('inventory.master.product.index', 'create')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $data = $this->decodeHashInputs($request->all(), [
            'product_id' => \App\Models\Products::class,
            'material_spec_id' => MaterialSpec::class,
            'unit_id' => Unit::class,
            'rank_id' => Rank::class,
            'revision_id' => \App\Models\InventoryModel\Material\Revision::class,
        ]);
        
        $request->merge($data);
        $validated = $request->validate($this->getValidationRules());

        $product = InventoryProduct::create($validated);

        $warning = null;
        if ($product->isCoil()) {
            if ($product->gross_coil <= 0 || $product->top_coil <= 0 || $product->end_coil <= 0 || $product->pitch <= 0) {
                $warning = 'Important: This is a Coil product, but parameters like Gross Coil, Top/End Coil, or Pitch are incomplete. Please complete this data to avoid transaction errors.';
            }
        }

        return response()->json(['success' => true, 'message' => 'Inventory Product created successfully.', 'warning' => $warning]);
    }

    /**
     * Display the specified resource for editing.
     */
    public function show($id)
    {
        $inventoryProduct = InventoryProduct::findByHashOrFail($id);
        $inventoryProduct->load(['product', 'model', 'materialSpec', 'unit', 'rank', 'revision']);
        
        // Get model status
        $modelStatus = \App\Models\InventoryModel\Material\ModelStatus::where('model_id', $inventoryProduct->model_id)->first();
        
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
        if (!auth()->user()->hasMenuPermission('inventory.master.product.index', 'edit')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $inventoryProduct = InventoryProduct::findByHashOrFail($id);
        
        $data = $this->decodeHashInputs($request->all(), [
            'product_id' => \App\Models\Products::class,
            'material_spec_id' => MaterialSpec::class,
            'unit_id' => Unit::class,
            'rank_id' => Rank::class,
            'revision_id' => \App\Models\InventoryModel\Material\Revision::class,
        ]);

        $request->merge($data);
        $validated = $request->validate($this->getValidationRules());

        $inventoryProduct->update($validated);

        $warning = null;
        if ($inventoryProduct->isCoil()) {
            if ($inventoryProduct->gross_coil <= 0 || $inventoryProduct->top_coil <= 0 || $inventoryProduct->end_coil <= 0 || $inventoryProduct->pitch <= 0) {
                $warning = 'Important: This is a Coil product, but parameters like Gross Coil, Top/End Coil, or Pitch are incomplete. Please complete this data to avoid transaction errors.';
            }
        }

        return response()->json(['success' => true, 'message' => 'Inventory Product updated successfully.', 'warning' => $warning]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (!auth()->user()->hasMenuPermission('inventory.master.product.index', 'delete')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $inventoryProduct = InventoryProduct::findByHashOrFail($id);

        // Check for transactions
        $hasTransactions = \App\Models\InventoryModel\Material\InventoryTransaction::where('product_detail_id', $inventoryProduct->id)->exists();
        if ($hasTransactions) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete this product revision. It has transaction history records.'
            ], 422);
        }

        // Check for STO records
        $hasSto = \App\Models\InventoryModel\Material\StoDetail::where('product_detail_id', $inventoryProduct->id)->exists();
        if ($hasSto) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete this product revision. It is linked to Stock Opname records.'
            ], 422);
        }

        $inventoryProduct->delete();
        return response()->json(['success' => true, 'message' => 'Inventory Product deleted successfully.']);
    }

    /**
     * Update the action status of a product (for dashboard follow-up).
     */
    public function updateActionStatus(Request $request, $id)
    {
        $product = InventoryProduct::findByHashOrFail($id);
        
        $updateData = [];
        if ($request->has('action_status')) {
            $status = $request->action_status;
            $updateData['action_status'] = ($status === '' || $status === 'NULL') ? null : $status;
        }
        if ($request->has('action_remark')) {
            $updateData['action_remark'] = $request->action_remark;
        }
        if ($request->has('maker_action_status')) {
            $status = $request->maker_action_status;
            $updateData['maker_action_status'] = ($status === '' || $status === 'NULL') ? null : $status;
        }
        if ($request->has('maker_action_remark')) {
            $updateData['maker_action_remark'] = $request->maker_action_remark;
        }

        if (!empty($updateData)) {
            $product->update($updateData);
        }

        return response()->json(['success' => true, 'message' => 'Action information updated.']);
    }

    /**
     * Get all old revisions of products.
     */
    public function getOldRevisions(Request $request)
    {
        $query = DB::table('inv_t_product_detail as p')
            ->join('products as prod', 'prod.id', '=', 'p.product_id')
            ->leftJoin('customers as cust', 'cust.id', '=', 'prod.customer_id')
            ->leftJoin('models as model', 'model.id', '=', 'p.model_id')
            ->leftJoin('inv_m_revision as r_master', 'r_master.id', '=', 'p.revision_id')
            ->where('p.is_active', 1)
            ->where('prod.is_delete', 0)
            ->whereRaw("p.revision_id != (
                SELECT TOP 1 sub_p.revision_id
                FROM inv_t_product_detail sub_p
                JOIN inv_m_revision sub_r ON sub_p.revision_id = sub_r.id
                WHERE sub_p.product_id = p.product_id
                  AND sub_p.model_id = p.model_id
                  AND sub_p.is_active = 1
                ORDER BY 
                  CASE WHEN sub_r.group_name = 'RC' THEN 2 ELSE 1 END DESC,
                  sub_r.sort_order DESC
            )");

        if ($request->filled('customer_id')) {
            $query->where('prod.customer_id', $request->customer_id);
        }
        if ($request->filled('model_id')) {
            $selectedModel = DB::table('models')->where('id', $request->model_id)->first();
            if ($selectedModel) {
                 $query->where('model.name', $selectedModel->name);
            }
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('prod.part_no', 'like', "%{$search}%")
                  ->orWhere('prod.part_name', 'like', "%{$search}%")
                  ->orWhere('model.name', 'like', "%{$search}%");
            });
        }

        $items = $query->select([
            'p.id',
            'prod.part_no',
            'prod.part_name',
            'cust.code as customer_code',
            'model.name as model_name',
            'r_master.code as revision_code',
            'p.product_status',
            'p.product_status_remark'
        ])
        ->orderBy('prod.part_no')
        ->orderBy('model.name')
        ->get()
        ->map(fn($r) => [
            'id' => InventoryProduct::encodeHash($r->id),
            'part_no' => $r->part_no . ($r->revision_code ? '-' . $r->revision_code : ''),
            'part_name' => $r->part_name,
            'customer' => $r->customer_code,
            'model' => $r->model_name,
            'product_status' => $r->product_status,
            'product_status_remark' => $r->product_status_remark
        ]);

        return response()->json(['success' => true, 'data' => $items]);
    }

    /**
     * Update product status and status remark.
     */
    public function updateProductStatus(Request $request, $id)
    {
        $product = InventoryProduct::findByHashOrFail($id);
        $product->update([
            'product_status' => $request->product_status ?: null,
            'product_status_remark' => $request->product_status_remark ?: null,
        ]);
        return response()->json(['success' => true, 'message' => 'Product status updated successfully.']);
    }

    /**
     * Print Label for the specified resource.
     */
    public function printLabel($id, ProductService $productService)
    {
        $ids = explode(',', $id);
        $products = $productService->generateLabelData($ids);
        
        if (empty($products)) abort(404);

        return view('inventory.material.qrcode', compact('products'));
    }

    /**
     * AJAX HELPERS
     */

    public function getDropdownData()
    {
        return response()->json([
            'customers' => $this->getCustomers(),
            'materialSpecs' => MaterialSpec::select('id', 'spec_name')->get(),
            'units' => Unit::select('id', 'code', 'name')->get(),
            'ranks' => Rank::select('id', 'code', 'description')->get(),
            'revisions' => \App\Models\InventoryModel\Material\Revision::where('is_active', 1)->orderBy('group_name')->orderBy('sort_order')->get(),
        ]);
    }

    public function getCustomers()
    {
        return DB::table('customers as c')
            ->select('c.id', 'c.code')
            ->orderBy('c.code')
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
            // Hilangkan join paksa ke p.model_id karena satu part bisa di banyak model inventory
            $query->addSelect('p.model_id'); 
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
            $firstRev = \App\Models\InventoryModel\Material\Revision::where('is_active', 1)->orderBy('group_name')->orderBy('sort_order')->first();
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
            $nextRev = \App\Models\InventoryModel\Material\Revision::where('group_name', $currentRev->group_name)
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

    public function getEpicorParts(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if ($q === '') {
            return response()->json(['results' => []]);
        }

        try {
            $results = DB::connection('second_db')->select("
                WITH PriceLatest AS (
                    select 
                        b.VendorID, 
                        a.PartNum, 
                        a.BaseUnitPrice, 
                        a.PUM, 
                        a.EffectiveDate, 
                        a.ExpirationDate, 
                        e.ConvFactor,
                        ROW_NUMBER() OVER (PARTITION BY a.PartNum ORDER BY a.EffectiveDate DESC) as RowNum
                    from erp.VendPart a
                    left join erp.Vendor b on b.VendorNum = a.VendorNum
                    left join erp.part c on c.PartNum = a.PartNum
                    left join erp.UOMClass d on d.UOMClassID = c.UOMClassID
                    left join erp.UOMConv e on e.UOMClassID = d.UOMClassID and e.UOMCode = a.PUM
                    where a.PartNum like ?
                )
                select top 30 * from PriceLatest where RowNum = 1
            ", ["%{$q}%"]);

            $formatted = collect($results)->map(function($row) {
                $effDate = $row->EffectiveDate ? date('Y-m-d', strtotime($row->EffectiveDate)) : '-';
                $expDate = $row->ExpirationDate ? date('Y-m-d', strtotime($row->ExpirationDate)) : '-';
                
                $rawPrice = (float)$row->BaseUnitPrice;
                $convFactor = $row->ConvFactor ? round((float)$row->ConvFactor, 3) : 0;
                $pum = trim($row->PUM);
                
                $calculatedPrice = $rawPrice;
                if ($pum === 'SHEET' && $convFactor > 0) {
                    $calculatedPrice = ceil($rawPrice / $convFactor);
                }
                
                $price = number_format($calculatedPrice, 2);
                
                $text = "{$row->PartNum} | Vendor: {$row->VendorID} | Price: {$price} {$row->PUM} | Eff: {$effDate}";
                
                return [
                    'id' => trim($row->PartNum),
                    'text' => trim($row->PartNum),
                    'detail' => $text,
                    'vendor_id' => $row->VendorID,
                    'price' => $calculatedPrice,
                    'pum' => $pum,
                    'effective_date' => $effDate,
                    'expiration_date' => $expDate,
                    'conv_factor' => $row->ConvFactor
                ];
            });

            return response()->json(['results' => $formatted]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * PRIVATE METHODS
     */

    private function getValidationRules()
    {
        return [
            'product_id' => 'required|exists:products,id',
            'partno_epicor' => 'nullable|string|max:20',
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
            'pcs_per_pitch' => 'nullable|integer|min:0',
            'density' => 'nullable|numeric|min:0',
            'gross_coil' => 'nullable|numeric|min:0',
            'top_coil' => 'nullable|numeric|min:0',
            'end_coil' => 'nullable|numeric|min:0',
            'net_coil' => 'nullable|numeric|min:0',
            'weight_kg' => 'nullable|numeric|min:0',
            'net_weight' => 'nullable|numeric|min:0',
            'material_price' => 'nullable|numeric|min:0',
            'pcs_per_unit' => 'nullable|integer|min:1',
            'unit_per_car' => 'nullable|integer|min:1',
            'min_stock' => 'nullable|integer|min:0',
            'current_stock_qty' => 'nullable|numeric|min:0',
            'trial_usage_qty' => 'nullable|numeric|min:0',
            'remark' => 'nullable|string',
            'product_status' => 'nullable|string|in:Oldstock OK,Oldstock NG',
            'product_status_remark' => 'nullable|string|in:Drawing Change,Damage,Under,Other',
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
