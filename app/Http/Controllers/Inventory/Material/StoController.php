<?php

namespace App\Http\Controllers\Inventory\Material;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\Material\StoEvent;
use App\Models\InventoryModel\Material\StoDetail;
use App\Models\InventoryModel\Material\InventoryProduct;
use App\Models\InventoryModel\Material\StoReason;
use App\Models\InventoryModel\PIC;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\DecodesHashInputs;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StoExport;

class StoController extends Controller
{
    use DecodesHashInputs;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = StoEvent::with('pic');

            // Searching
            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('status', 'like', "%{$search}%")
                      ->orWhereHas('pic', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Ordering - Map index to Database field
            if ($request->has('order')) {
                $sortableColumns = [
                    0 => 'inv_t_sto_event.id',
                    1 => 'code',
                    2 => 'period_start',
                    3 => 'pic_name',
                    4 => 'net_amount',
                    5 => 'net_pcs',
                    6 => 'status',
                ];
                
                $colIndex = $request->input('order.0.column');
                $colName = $sortableColumns[$colIndex] ?? 'inv_t_sto_event.created_at';
                $dir = $request->input('order.0.dir', 'desc');
                
                if ($colName === 'pic_name') {
                    $query->join('users', 'users.id', '=', 'inv_t_sto_event.user_id')
                          ->orderBy('users.name', $dir);
                } elseif (in_array($colName, ['net_amount', 'net_pcs'])) {
                    $query->orderBy($colName, $dir);
                } else {
                    $query->orderBy($colName, $dir);
                }
            } else {
                $query->orderBy('inv_t_sto_event.created_at', 'desc');
            }

            // Count Total & Filtered
            $recordsTotal = StoEvent::count();

            // Calculate Variance Totals in Query
            $query->select('inv_t_sto_event.*')
                ->addSelect([
                    'net_pcs' => StoDetail::selectRaw("SUM(" . \App\Models\InventoryModel\Material\InventoryProduct::getPcsCalculationSql('inv_t_sto_detail.diff_qty', 'inv_t_product_detail', 'u.name') . ")")
                        ->leftJoin('inv_t_product_detail', 'inv_t_sto_detail.product_detail_id', '=', 'inv_t_product_detail.id')
                        ->leftJoin('inv_m_unit as u', 'u.id', '=', 'inv_t_product_detail.unit_id')
                        ->whereColumn('inv_t_sto_detail.event_id', 'inv_t_sto_event.id'),
                    'net_amount' => StoDetail::selectRaw("SUM(" . \App\Models\InventoryModel\Material\InventoryProduct::getAmountCalculationSql('inv_t_sto_detail.diff_qty', 'inv_t_product_detail', 'u.name') . ")")
                        ->leftJoin('inv_t_product_detail', 'inv_t_sto_detail.product_detail_id', '=', 'inv_t_product_detail.id')
                        ->leftJoin('inv_m_unit as u', 'u.id', '=', 'inv_t_product_detail.unit_id')
                        ->whereColumn('inv_t_sto_detail.event_id', 'inv_t_sto_event.id'),
                ]);

            $recordsFiltered = $query->count();

            // Pagination
            $limit = $request->input('length', 10);
            $start = $request->input('start', 0);
            $data = $query->skip($start)->take($limit)->get();

            // Transform Data for View
            $rowNumber = $start + 1;
            $transformedData = $data->map(function ($event) use (&$rowNumber) {
                // Determine step statuses
                return [
                    'row_no' => $rowNumber++,
                    'hash_id' => $event->hash_id,
                    'code' => $event->code,
                    'period_start' => $event->period_start->format('d M Y'),
                    'period_end' => $event->period_end ? $event->period_end->format('d M Y') : null,
                    'status' => $event->status,
                    'pic_name' => $event->pic->name ?? '-',
                    'net_pcs' => $event->net_pcs ?? 0,
                    'net_amount' => $event->net_amount ?? 0,
                    // Minimal flags for UI logic in Blade/JS
                    'can_manage' => auth()->user()->hasMenuPermission('inventory.sto.index', 'create') || auth()->user()->hasMenuPermission('inventory.sto.index', 'edit'),
                    'is_approver' => auth()->user()->hasMenuPermission('inventory.sto.index', 'edit'),
                    'is_checker' => auth()->user()->hasMenuPermission('inventory.sto.index', 'edit'),
                ];
            });

            return response()->json([
                "draw" => intval($request->input('draw')),
                "recordsTotal" => $recordsTotal,
                "recordsFiltered" => $recordsFiltered,
                "data" => $transformedData
            ]);
        }

        $events = StoEvent::with('pic')->orderBy('created_at', 'desc')->get();
        return view('inventory.material.sto.index', compact('events'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Only users with create permission can initialize a new event
        $user = auth()->user();
        if (!$user->hasMenuPermission('inventory.sto.index', 'create')) {
             return back()->with('error', 'Unauthorized. You do not have permission to initialize STO events.');
        }

        $validated = $request->validate([
            'period_start' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->user()->id;
        $validated['code'] = $this->generateEventCode($validated['period_start']);
        $validated['name'] = "STO Event - " . $validated['code']; 
        $validated['status'] = 'OPEN';

        // Check for any active STO event
        $activeEvent = StoEvent::whereIn('status', ['OPEN', 'WAITING CHECK', 'WAITING APPROVAL'])->first();
        
        if ($activeEvent) {
            return back()->with('error', "Cannot create a new STO Event because event {$activeEvent->code} is still active. Please close it first.");
        }

        StoEvent::create($validated);

        return redirect()->route('inventory.sto.index')->with('success', 'STO Event created successfully.');
    }

    /**
     * Get Edit Data
     */
    public function edit($id)
    {
        $event = StoEvent::findByHashOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'hash_id' => $event->hash_id,
                'code' => $event->code,
                'period_start' => $event->period_start->format('Y-m-d'),
                'description' => $event->description,
                'status' => $event->status
            ]
        ]);
    }

    /**
     * Update STO Event
     */
    public function update(Request $request, $id)
    {
        $event = StoEvent::findByHashOrFail($id);
        
        $request->validate([
            'period_start' => 'required|date',
        ]);

        $event->update([
            'period_start' => $request->period_start,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'STO Event updated successfully.'
        ]);
    }

    /**
     * Delete STO Event & Details
     */
    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user->hasMenuPermission('inventory.sto.index', 'delete')) {
             return response()->json([
                 'success' => false,
                 'message' => 'Unauthorized.'
             ], 403);
        }

        $event = StoEvent::findByHashOrFail($id);
        
        if ($event->status === 'CLOSED') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a CLOSED STO event. You must REOPEN it first to revert the stock adjustments before deleting the event.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Delete all details first
            StoDetail::where('event_id', $event->id)->delete();
            // Delete the event
            $event->delete();
            
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'STO Event and all related records deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete STO Event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview the generated code via AJAX.
     */
    public function previewCode(Request $request)
    {
        $date = $request->input('date') ?: now()->format('Y-m-d');
        return response()->json(['code' => $this->generateEventCode($date)]);
    }

    /**
     * Generate STO Code: SAI/STO/DDMMYYYY/0001
     */
    private function generateEventCode($dateInput = null)
    {
        $date = $dateInput ? \Carbon\Carbon::parse($dateInput) : now();
        $prefix = 'SAI/STO/' . $date->format('dmY');
        
        // Find the absolute last created STO code to continue sequence
        $lastRecord = StoEvent::orderBy('id', 'desc')->first();
        
        if ($lastRecord && $lastRecord->code) {
            $parts = explode('/', $lastRecord->code);
            $lastSeq = (int) end($parts);
            $newSeq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newSeq = '0001';
        }
        
        return "$prefix/$newSeq";
    }

    public function show($id)
    {
        $stoEvent = StoEvent::findByHashOrFail($id);
        
        $stats = $this->getStoStats($stoEvent);
        $netAdjustment = $stats['net_adjustment'];
        $progress = $stats['progress'];

        $countedIds = StoDetail::where('event_id', $stoEvent->id)->pluck('product_detail_id')->toArray();

        // Used for "Remaining Items" list
        $products = InventoryProduct::join('products', 'inv_t_product_detail.product_id', '=', 'products.id')
            ->leftJoin('models as m', 'm.id', '=', 'inv_t_product_detail.model_id')
            ->leftJoin('inv_m_revision as r', 'r.id', '=', 'inv_t_product_detail.revision_id')
            ->select('inv_t_product_detail.id', 'products.part_no', 'products.part_name', 'r.code as revision', 'm.name as model_name')
            ->where('inv_t_product_detail.is_active', 1)
            ->whereNotIn('inv_t_product_detail.id', $countedIds)
            ->orderBy('products.part_no')
            ->get();

        // Used for the search/scanner dropdown (contains all active products)
        $allProducts = InventoryProduct::join('products', 'inv_t_product_detail.product_id', '=', 'products.id')
            ->leftJoin('models as m', 'm.id', '=', 'inv_t_product_detail.model_id')
            ->leftJoin('inv_m_revision as r', 'r.id', '=', 'inv_t_product_detail.revision_id')
            ->select('inv_t_product_detail.id', 'products.part_no', 'products.part_name', 'r.code as revision', 'm.name as model_name')
            ->where('inv_t_product_detail.is_active', 1)
            ->orderBy('products.part_no')
            ->get();

        $reasons = StoReason::where('is_active', 1)->get();
        $locations = \App\Models\InventoryModel\Material\Location::where('is_active', 1)->orderBy('name')->get();

        return view('inventory.material.sto.show', compact('stoEvent', 'stats', 'products', 'allProducts', 'netAdjustment', 'progress', 'reasons', 'countedIds', 'locations'));
    }

    /**
     * Get STO Details for DataTables.
     */
    public function detailsData(Request $request, $id)
    {
        $stoEvent = StoEvent::findByHashOrFail($id);
        
        $query = StoDetail::with(['product', 'product.product', 'product.unit', 'product.revision', 'auditor', 'reason', 'location'])
            ->where('event_id', $stoEvent->id);

        // Searching
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('product.product', function($sq) use ($search) {
                    $sq->where('part_no', 'like', "%{$search}%")
                       ->orWhere('part_name', 'like', "%{$search}%");
                })->orWhere('inv_t_sto_detail.remark', 'like', "%{$search}%");
            });
        }

        // Ordering - Align with Blade table structure
        $sortableColumns = [
            0 => 'inv_t_sto_detail.id',      // No
            1 => 'updated_at',               // Timestamp
            2 => 'model_name',               // Model
            3 => 'part_no',                  // Material Info
            4 => 'auditor_name',             // Auditor
            5 => 'system_qty_snapshot',      // System Qty
            6 => 'system_amount',            // System Amount
            7 => 'real_qty_input',           // Real Qty
            8 => 'real_amount',              // Real Amount
            9 => 'diff_qty',                 // Variance Qty
            10 => 'diff_amount',              // Variance Amount
            11 => 'location_name',           // Location
            12 => 'reason_name',             // Reason
            13 => 'remark',                  // Remark
            14 => 'action'                   // Action
        ];
        
        $colIndex = $request->input('order.0.column', 1);
        $dir = $request->input('order.0.dir', 'desc');
        $colName = $sortableColumns[$colIndex] ?? 'updated_at';

        // Base Query with necessary calculated fields for sorting
        $query->addSelect([
            'auditor_name' => \App\Models\User::select('name')->whereColumn('id', 'inv_t_sto_detail.auditor_id')->limit(1),
            'location_name' => \App\Models\InventoryModel\Material\Location::select('name')->whereColumn('id', 'inv_t_sto_detail.location_id')->limit(1),
            'reason_name' => \App\Models\InventoryModel\Material\StoReason::select('name')->whereColumn('id', 'inv_t_sto_detail.reason_id')->limit(1),
            'model_name' => \App\Models\Models::select('name')
                ->join('inv_t_product_detail', 'inv_t_product_detail.model_id', '=', 'models.id')
                ->whereColumn('inv_t_product_detail.id', 'inv_t_sto_detail.product_detail_id')
                ->limit(1),
            // Material Info sort logic
            'part_no' => \App\Models\Products::select('part_no')
                ->join('inv_t_product_detail', 'inv_t_product_detail.product_id', '=', 'products.id')
                ->whereColumn('inv_t_product_detail.id', 'inv_t_sto_detail.product_detail_id')
                ->limit(1),
            // Prices/Amounts for sorting
            'system_amount' => DB::raw("(inv_t_sto_detail.system_qty_snapshot * ISNULL(pd.weight_kg, 0) * ISNULL(pd.material_price, 0))"),
            'real_amount' => DB::raw("(inv_t_sto_detail.real_qty_input * ISNULL(pd.weight_kg, 0) * ISNULL(pd.material_price, 0))"),
            'diff_amount' => DB::raw("((inv_t_sto_detail.real_qty_input - inv_t_sto_detail.system_qty_snapshot) * ISNULL(pd.weight_kg, 0) * ISNULL(pd.material_price, 0))"),
        ])
        ->leftJoin('inv_t_product_detail as pd', 'pd.id', '=', 'inv_t_sto_detail.product_detail_id');

        if ($colName === 'part_no' || $colName === 'model_name') {
            $query->orderBy($colName, $dir);
        } elseif (in_array($colName, ['auditor_name', 'location_name', 'reason_name', 'system_amount', 'real_amount', 'diff_amount'])) {
            $query->orderBy($colName, $dir);
        } elseif (in_array($colName, ['updated_at', 'system_qty_snapshot', 'real_qty_input', 'diff_qty', 'remark'])) {
            $query->orderBy('inv_t_sto_detail.' . $colName, $dir);
        } else {
            $query->orderBy('inv_t_sto_detail.updated_at', 'desc');
        }

        // Add deterministic secondary sort to prevent sub-rows from swapping positions on update
        $query->orderBy('inv_t_sto_detail.id', 'asc');

        $recordsTotal = StoDetail::where('event_id', $stoEvent->id)->count();
        $recordsFiltered = $query->count();

        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        $data = $query->skip($start)->take($limit)->get();
        
        // Calculate starting row number for this page
        $rowNumber = $start + 1;
        $user = auth()->user();
        $isAdmin = $user->hasAppRole('admin') || $user->hasAppRole('Inv Admin');
        $isPic = $stoEvent->user_id === $user->id || $user->hasAppRole('pic') || $user->hasAppRole('Inv PIC'); // PIC role or event creator
        $isOperator = $user->hasAppRole('operator') || $user->hasAppRole('Inv Operator');
        $canEditInline = ($isAdmin || $isPic || $isOperator) && $stoEvent->status === 'OPEN';

        // 1. Get the list of product IDs on current page
        $productIds = $data->pluck('product_detail_id')->unique();
        
        // 2. Identify the "Primary" record for each product group (oldest ID)
        $primaryEntries = DB::table('inv_t_sto_detail')
            ->where('event_id', $stoEvent->id)
            ->whereIn('product_detail_id', $productIds)
            ->select('product_detail_id', DB::raw('MIN(id) as min_id'))
            ->groupBy('product_detail_id')
            ->pluck('min_id', 'product_detail_id');

        // 3. Get the group-level reason and remark
        $groupReasons = DB::table('inv_t_sto_detail')
            ->where('event_id', $stoEvent->id)
            ->whereIn('product_detail_id', $productIds)
            ->whereNotNull('reason_id')
            ->select('product_detail_id', DB::raw('MAX(reason_id) as reason_id'))
            ->groupBy('product_detail_id')
            ->pluck('reason_id', 'product_detail_id')
            ->toArray();

        $groupRemarks = DB::table('inv_t_sto_detail')
            ->where('event_id', $stoEvent->id)
            ->whereIn('product_detail_id', $productIds)
            ->whereNotNull('remark')
            ->where('remark', '!=', '')
            ->select('product_detail_id', DB::raw('MAX(remark) as remark'))
            ->groupBy('product_detail_id')
            ->pluck('remark', 'product_detail_id')
            ->toArray();

        $transformedData = $data->map(function ($detail) use ($stoEvent, &$rowNumber, $canEditInline, $primaryEntries, $groupReasons, $groupRemarks) {
            $pcsPerUnit = $detail->product->pcs_per_unit ?? 1;
            $unitCode = $detail->product->unit->name ?? 'PCS';
            $unitDisplayCode = $detail->product->unit->code ?? 'PCS';
            
            // Get aggregates for this product in this event
            $productAggregates = DB::table('inv_t_sto_detail')
                ->where('event_id', $stoEvent->id)
                ->where('product_detail_id', $detail->product_detail_id)
                ->select(
                    DB::raw('SUM(real_qty_input) as total_real'),
                    DB::raw('SUM(system_qty_snapshot) as total_system')
                )
                ->first();

            $totalReal = (float)($productAggregates->total_real ?? 0);
            $totalSystem = (float)($productAggregates->total_system ?? 0);
            $totalDiff = $totalReal - $totalSystem;

            $diff = $detail->real_qty_input - $detail->system_qty_snapshot;
            
            // Financial Calculation raw values
            $pricePerKg = $detail->product->material_price ?? 0;
            $weightPerPcs = $detail->product->weight_kg ?? 0;
            
            $systemAmount = $detail->system_qty_snapshot * $weightPerPcs * $pricePerKg;
            $realAmount = $detail->real_qty_input * $weightPerPcs * $pricePerKg;
            $diffAmount = $diff * $weightPerPcs * $pricePerKg;

            $totalDiffAmount = $totalDiff * $weightPerPcs * $pricePerKg;
            $totalSystemAmount = $totalSystem * $weightPerPcs * $pricePerKg;
            $totalRealAmount = $totalReal * $weightPerPcs * $pricePerKg;

            $currentRow = $rowNumber++;

            return [
                'row_number' => $currentRow,
                'hash_id' => $detail->hash_id,
                'updated_at' => $detail->updated_at->format('d/m/Y H:i'),
                'part_no' => $detail->product->product->part_no ?? '-',
                'model_name' => $detail->product->model->name ?? 'No Model',
                'revision' => $detail->product->revision->code ?? '',
                'part_name' => $detail->product->product->part_name ?? '-',
                'auditor' => $detail->auditor->name ?? '-',
                'system_qty' => (float)$detail->system_qty_snapshot,
                'system_amount' => (float)$systemAmount,
                'real_qty_input' => (float)$detail->real_qty_input,
                'real_amount' => (float)$realAmount,
                'diff_qty' => (float)$diff,
                'diff_amount' => (float)$diffAmount,
                'pcs_per_unit' => (float)$pcsPerUnit,
                'weight_kg' => (float)$weightPerPcs,
                'gross_coil' => (float)($detail->product->gross_coil ?? 0),
                'unit_code' => $unitCode,
                'unit_display' => $unitDisplayCode,
                'location_name' => $detail->location_name,
                'reason_id' => $groupReasons[$detail->product_detail_id] ?? $detail->reason_id,
                'reason_name' => $detail->reason_name,
                'category' => $totalDiff < 0 ? 'SHORTAGE' : 'EXCESS',
                'remark' => $groupRemarks[$detail->product_detail_id] ?? $detail->remark,
                'can_edit_inline' => $canEditInline,
                'status' => $stoEvent->status,
                'product_hash_id' => $detail->product->hash_id,
                'is_primary' => $detail->id == ($primaryEntries[$detail->product_detail_id] ?? null),
                'group_has_reason' => isset($groupReasons[$detail->product_detail_id]),
                // Aggregates for grouping
                'total_real_qty' => $totalReal,
                'total_system_qty' => $totalSystem,
                'total_diff_qty' => $totalDiff,
                'total_real_amount' => $totalRealAmount,
                'total_system_amount' => $totalSystemAmount,
                'total_diff_amount' => $totalDiffAmount,
            ];
        });

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $transformedData
        ]);
    }

    /**
     * Process Scan and Return Product Info.
     */
    public function scan(Request $request, $id)
    {
        $user = auth()->user();
        $isOperator = $user->hasAppRole('Inv Operator') || $user->hasAppRole('operator') || $user->hasAppRole('pic') || $user->hasAppRole('Inv PIC') || $user->hasAppRole('admin') || $user->hasAppRole('Inv Admin');
        if (!$user->hasMenuPermission('inventory.sto.index', 'edit') || !$isOperator) {
             return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $stoEvent = StoEvent::findByHashOrFail($id);
        
        if ($stoEvent->status !== 'OPEN') {
            return response()->json(['success' => false, 'message' => 'Event is closed.'], 403);
        }

        $input = $request->input('qr_code');
        
        // Handle JSON or Base64 or Plain ID
        $productId = null;
        
        // Attempt JSON Parse
        $decodedJson = json_decode($input, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($decodedJson['id'])) {
             $productId = InventoryProduct::decodeHash($decodedJson['id']);
        } else {
             $productId = InventoryProduct::decodeHash($input);
        }

        if (!$productId) {
            // Fallback: Check if it's a raw part_no or barcode
            $found = InventoryProduct::whereHas('product', function($q) use ($input) {
                $q->where('part_no', $input);
            })->first();
            
            if ($found) {
                $productId = $found->id;
            } else {
                return response()->json(['success' => false, 'message' => 'Product not found or invalid QR format.'], 404);
            }
        }

        $product = InventoryProduct::with(['product', 'unit', 'revision'])->find($productId);
        
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product details missing.'], 404);
        }

        // Check all existing entries for this product in this event
        $existingEntries = StoDetail::with(['location', 'auditor'])
            ->where('event_id', $stoEvent->id)
            ->where('product_detail_id', $product->id)
            ->get();

        $existingData = $existingEntries->map(function($entry) {
            return [
                'detail_id_hash' => $entry->hash_id,
                'location_id' => $entry->location_id,
                'location_name' => $entry->location->name ?? 'No Location',
                'real_qty' => $entry->real_qty_input,
                'remark' => $entry->remark,
                'auditor_name' => $entry->auditor->name ?? 'Unknown'
            ];
        });

        // Current system qty snapshot logic
        $systemQty = $existingEntries->isNotEmpty() ? $existingEntries->first()->system_qty_snapshot : $product->current_stock_qty;

        return response()->json([
            'success' => true,
            'data' => [
                'product_id_hash' => $product->hash_id,
                'part_no' => '[' . ($product->model->name ?? 'No Model') . '] ' . ($product->product->part_no ?? '-') . ($product->revision ? '-' . $product->revision->code : ''),
                'part_name' => $product->product->part_name ?? '-',
                'unit' => $product->unit->name ?? 'PCS',
                'unit_display' => $product->unit->code ?? 'PCS',
                'system_qty' => $systemQty,
                'gross_coil' => (float)($product->gross_coil ?? 0),
                'pcs_per_unit' => (float)($product->pcs_per_unit ?? 1),
                'existing_entries' => $existingData,
                'is_new_snapshot' => $existingEntries->isEmpty()
            ]
        ]);
    }

    /**
     * Save Count Result.
     */
    public function saveCount(Request $request, $id)
    {
        try {
            $user = auth()->user();
            $isOperator = $user->hasAppRole('Inv Operator') || $user->hasAppRole('operator') || $user->hasAppRole('pic') || $user->hasAppRole('Inv PIC') || $user->hasAppRole('admin') || $user->hasAppRole('Inv Admin');
            if (!$user->hasMenuPermission('inventory.sto.index', 'edit') || !$isOperator) {
                 return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            // Sanitize inputs that might be sent as JS strings "undefined" or "null"
            if ($request->location_id === 'undefined' || $request->location_id === 'null' || $request->location_id === '') {
                $request->merge(['location_id' => null]);
            }
            if ($request->reason_id === 'undefined' || $request->reason_id === 'null' || $request->reason_id === '') {
                $request->merge(['reason_id' => null]);
            }

            $stoEvent = StoEvent::findByHashOrFail($id);
            
            if ($stoEvent->status !== 'OPEN') {
                return response()->json(['success' => false, 'message' => 'Event is closed.'], 403);
            }

            $request->validate([
                'product_id_hash' => 'required',
                'detail_id_hash' => 'nullable', 
                'location_id' => 'sometimes|nullable|exists:inv_m_locations,id',
                'real_qty' => 'sometimes|required|numeric|min:0',
                'remark' => 'sometimes|nullable|string|max:255',
                'reason_id' => 'sometimes|nullable|exists:inv_m_sto_reasons,id'
            ]);

            $productId = InventoryProduct::decodeHash($request->product_id_hash);
            $detailId = $request->detail_id_hash ? StoDetail::decodeHash($request->detail_id_hash) : null;
            
            $product = InventoryProduct::find($productId);
            
            if (!$product) {
                return response()->json(['success' => false, 'message' => 'Product not found'], 404);
            }

            // Upsert Detail Logic
            $detail = null;

            if ($detailId) {
                $detail = StoDetail::find($detailId);
            } 

            if (!$detail) {
                // ALWAYS create a NEW record if no specific detail ID is provided
                // This ensures every click on 'Save' creates a separate row for traceability
                $detail = new StoDetail();
                $detail->event_id = $stoEvent->id;
                $detail->product_detail_id = $productId;
                $detail->location_id = $request->location_id;
                
                // Snapshot Logic: Check if ANY non-zero snapshot exists for this product in this event
                // Only the VERY FIRST record for this product gets the system stock quantity
                $hasSnapshot = StoDetail::where('event_id', $stoEvent->id)
                    ->where('product_detail_id', $productId)
                    ->where('system_qty_snapshot', '>', 0)
                    ->exists();
                    
                if ($hasSnapshot) {
                    $detail->system_qty_snapshot = 0;
                } else {
                    $detail->system_qty_snapshot = $product->current_stock_qty ?? 0;
                }
            }

            // Standard Update Logic (Used for both new and existing)
            if ($request->has('real_qty')) {
                $detail->real_qty_input = $request->real_qty;
            }
            if ($request->has('location_id')) {
                $detail->location_id = $request->location_id;
            }
            
            // Apply reason and remark to ALL records of this product in this event
            $updateGlobal = [];
            if ($request->has('remark')) {
                $detail->remark = $request->remark;
                $updateGlobal['remark'] = $request->remark;
            }
            if ($request->has('reason_id')) {
                $detail->reason_id = $request->reason_id;
                $updateGlobal['reason_id'] = $request->reason_id;
            }
            
            $detail->diff_qty = (float)$detail->real_qty_input - (float)$detail->system_qty_snapshot;
            
            $user = auth()->user();
            if ($user) {
                $detail->auditor_id = $user->id;
            }
            
            $detail->save();

            // Synchronize reason & remark across same product group
            if (!empty($updateGlobal) && $detailId) {
                StoDetail::where('event_id', $stoEvent->id)
                    ->where('product_detail_id', $productId)
                    ->where('id', '!=', $detail->id)
                    ->update($updateGlobal);
            }

            return response()->json([
                'success' => true, 
                'message' => 'New entry recorded',
                'detail_id_hash' => $detail->hash_id,
                'stats' => $this->getStoStats($stoEvent)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Delete an STO Detail record.
     */
    public function deleteDetail($id, $detailId)
    {
        $user = auth()->user();
        $isOperator = $user->hasAppRole('Inv Operator') || $user->hasAppRole('operator') || $user->hasAppRole('pic') || $user->hasAppRole('Inv PIC') || $user->hasAppRole('admin') || $user->hasAppRole('Inv Admin');
        if (!$user->hasMenuPermission('inventory.sto.index', 'delete') || !$isOperator) {
             return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $stoEvent = StoEvent::findByHashOrFail($id);
        
        if ($stoEvent->status !== 'OPEN') {
            return response()->json(['success' => false, 'message' => 'Event is not in OPEN status.']);
        }

        $detail = StoDetail::findByHashOrFail($detailId);
        
        if ((int)$detail->event_id !== (int)$stoEvent->id) {
            return response()->json(['success' => false, 'message' => 'Detail does not belong to this event.']);
        }

        $detail->delete();

        return response()->json([
            'success' => true,
            'message' => 'Entry deleted successfully.',
            'stats' => $this->getStoStats($stoEvent)
        ]);
    }

    /**
     * Submit STO for Verification (Owner/Operator).
     */
    public function submitForCheck($id)
    {
        $stoEvent = StoEvent::findByHashOrFail($id);
        
        $user = auth()->user();
        $isPicUser = $stoEvent->user_id === $user->id || $user->hasAppRole('pic') || $user->hasAppRole('Inv PIC') || $user->hasAppRole('admin') || $user->hasAppRole('Inv Admin');
        if (!$isPicUser) {
            return back()->with('error', 'Only the assigned PIC can submit this event.');
        }

        if ($stoEvent->status !== 'OPEN') {
            return back()->with('error', 'Status must be OPEN to submit for check.');
        }

        // Validate that all product-level mismatches have at least one reason
        // If a Part Number has multiple locations but the total variance is not zero, 
        // at least one of those locations must have a reason selected.
        $mismatchedProducts = StoDetail::where('event_id', $stoEvent->id)
            ->groupBy('product_detail_id')
            ->havingRaw('SUM(diff_qty) != 0')
            ->pluck('product_detail_id');

        $missingReasonsCount = 0;
        foreach ($mismatchedProducts as $productId) {
            $hasReason = StoDetail::where('event_id', $stoEvent->id)
                ->where('product_detail_id', $productId)
                ->whereNotNull('reason_id')
                ->exists();
            
            if (!$hasReason) {
                $missingReasonsCount++;
            }
        }
        
        if ($missingReasonsCount > 0) {
            return back()->with('error', "Cannot submit: {$missingReasonsCount} Part Number(s) with stock mismatch are missing a 'Reason'. Only one reason per Part Number is required to satisfy Pareto analysis requirements.");
        }

        $stoEvent->status = 'WAITING CHECK';
        $stoEvent->rejection_note = null; // Clear old rejection reason
        $stoEvent->save();

        return redirect()->route('inventory.sto.index')->with('success', 'STO Event submitted for verification.');
    }

    /**
     * Verify STO Data (Checker).
     */
    public function verify($id)
    {
        $stoEvent = StoEvent::findByHashOrFail($id);

        if ($stoEvent->status !== 'WAITING CHECK') {
            return back()->with('error', 'Event is not waiting for check.');
        }

        // Authorization Check
        $user = auth()->user();
        $isChecker = $user->hasAppRole('checker') || $user->hasAppRole('Inv Checker') || $user->hasAppRole('admin') || $user->hasAppRole('Inv Admin');
        if (!$user->hasMenuPermission('inventory.sto.index', 'edit') || !$isChecker) {
             return back()->with('error', 'Unauthorized. Only Checkers or Admins can verify.');
        }

        $stoEvent->status = 'WAITING APPROVAL';
        $stoEvent->checked_by = $user->id;
        $stoEvent->checked_at = now();
        $stoEvent->save();

        return redirect()->route('inventory.sto.index')->with('success', 'STO Event verified. Waiting for approval.');
    }

    /**
     * Reject STO Data (Checker/Approver).
     */
    public function reject(Request $request, $id)
    {
        $stoEvent = StoEvent::findByHashOrFail($id);

        if (!in_array($stoEvent->status, ['WAITING CHECK', 'WAITING APPROVAL'])) {
            return back()->with('error', 'Only events waiting for check or approval can be rejected.');
        }

        $request->validate([
            'rejection_note' => 'required|string|max:500'
        ]);

        $user = auth()->user();
        $isAuthorized = $user->hasAppRole('checker') || $user->hasAppRole('Inv Checker') || $user->hasAppRole('approver') || $user->hasAppRole('Inv Approver') || $user->hasAppRole('admin') || $user->hasAppRole('Inv Admin');
        if (!$user->hasMenuPermission('inventory.sto.index', 'edit') || !$isAuthorized) {
             return back()->with('error', 'Unauthorized.');
        }
        
        $stoEvent->status = 'OPEN';
        $stoEvent->rejection_note = $request->rejection_note;
        
        // Reset approval timestamps if they were set
        if ($stoEvent->status === 'WAITING APPROVAL') {
             $stoEvent->checked_by = null;
             $stoEvent->checked_at = null;
        }
        
        $stoEvent->save();

        return redirect()->route('inventory.sto.index')->with('success', 'STO Event rejected and sent back to OPEN status.');
    }

    /**
     * Finalize Event and Adjust Stock (Approver).
     */
    public function finalize($id)
    {
        $stoEvent = StoEvent::findByHashOrFail($id);
        
        if ($stoEvent->status !== 'WAITING APPROVAL') {
             return back()->with('error', 'Event is not waiting for approval.');
        }

        // Authorization Check
        $user = auth()->user();
        $isApprover = $user->hasAppRole('approver') || $user->hasAppRole('Inv Approver') || $user->hasAppRole('admin') || $user->hasAppRole('Inv Admin');
        if (!$user->hasMenuPermission('inventory.sto.index', 'edit') || !$isApprover) {
             return back()->with('error', 'Unauthorized. Only Approvers or Admins can finalize.');
        }

        // Validation: Check if there are any counted items
        $totalItems = StoDetail::where('event_id', $stoEvent->id)->count();
        if ($totalItems === 0) {
            return back()->with('error', 'Cannot finalize empty STO event.');
        }

        try {
            DB::beginTransaction();

            $stoEvent->status = 'CLOSED';
            $stoEvent->approved_by = $user->id;
            $stoEvent->approved_at = now();
            $stoEvent->period_end = now();
            $stoEvent->save();

            // Process Adjustments
            $details = StoDetail::where('event_id', $stoEvent->id)
                ->where('is_adjusted', 0)
                ->get(); 

            $count = 0;
            $errors = [];

            foreach ($details as $detail) {
                $diff = $detail->diff_qty;
                
                if ($diff == 0) {
                    $detail->is_adjusted = true;
                    $detail->save();
                    continue;
                }

                $detail->is_adjusted = true;
                $detail->save();
                $count++;
            }

            DB::commit();
            
            return redirect()->route('inventory.sto.index')->with('success', "STO Event finalized by {$user->name}. Stock updated for {$count} items.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error finalizing: ' . $e->getMessage());
        }
    }

    /**
     * Reopen a closed STO event (Admin only).
     */
    public function reopen(Request $request, $id)
    {
        // Reopen Logic: Requires edit permission and Approver/Admin role
        $user = auth()->user();
        $isApprover = $user->hasAppRole('approver') || $user->hasAppRole('Inv Approver') || $user->hasAppRole('admin') || $user->hasAppRole('Inv Admin');
        if (!$user->hasMenuPermission('inventory.sto.index', 'edit') || !$isApprover) {
            return back()->with('error', 'Unauthorized.');
        }

        $stoEvent = StoEvent::findByHashOrFail($id);
        
        if ($stoEvent->status === 'OPEN') {
            return back()->with('error', 'Event is already open.');
        }

        try {
            DB::beginTransaction();

            // Reverse stock adjustments if CLOSED
            if ($stoEvent->status === 'CLOSED') {
                $details = StoDetail::where('event_id', $stoEvent->id)
                    ->where('is_adjusted', 1)
                    ->get();

                foreach ($details as $detail) {
                    $detail->is_adjusted = false;
                    $detail->save();
                }
            }

            // Reset Status and Approval Fields
            $stoEvent->status = 'OPEN';
            $stoEvent->period_end = null;
            $stoEvent->checked_by = null;
            $stoEvent->checked_at = null;
            $stoEvent->approved_by = null;
            $stoEvent->approved_at = null;
            $stoEvent->save();
            
            DB::commit();
            
            return redirect()->route('inventory.sto.show', $stoEvent->hash_id)
                ->with('success', 'STO Event reopened. Approvals reset.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error reopening: ' . $e->getMessage());
        }
    }

    /**
     * Export STO event to Excel.
     */
    public function exportExcel($id)
    {
        $stoEvent = \App\Models\InventoryModel\Material\StoEvent::findByHashOrFail($id);
        $stoEvent->load(['details.product.product', 'details.auditor', 'pic']);

        $filename = "STO_{$stoEvent->code}_" . now()->format('Ymd_His') . ".xlsx";
        
        return Excel::download(new StoExport($stoEvent), $filename);
    }

    private function getStoStats($stoEvent)
    {
        // Aggregate totals per product for this event - Group strictly by product_detail_id 
        // to handle multiple locations correctly.
        $productStats = DB::table('inv_t_sto_detail as sd')
            ->leftJoin('inv_t_product_detail as pd', 'sd.product_detail_id', '=', 'pd.id')
            ->leftJoin('inv_m_unit as u', 'u.id', '=', 'pd.unit_id')
            ->where('sd.event_id', $stoEvent->id)
            ->select(
                'sd.product_detail_id', 
                DB::raw('MAX(pd.pcs_per_unit) as pcs_per_unit'),
                DB::raw('MAX(pd.gross_coil) as gross_coil'),
                DB::raw('MAX(u.name) as unit_name'),
                DB::raw('SUM(ISNULL(sd.real_qty_input, 0)) as total_real'),
                DB::raw('SUM(ISNULL(sd.system_qty_snapshot, 0)) as total_system')
            )
            ->groupBy('sd.product_detail_id')
            ->get();

        $totalItems = $productStats->count();
        $totalMatched = 0;
        $totalDiff = 0;
        
        $countIncrease = 0;
        $countDecrease = 0;
        
        $totalIncrease = 0; // Total positive diff (Base Unit: Kg/Pcs/Sheet)
        $totalDecrease = 0; // Total negative diff (Base Unit: Kg/Pcs/Sheet)
        
        $incPcs = 0;
        $decPcs = 0;
        $netPcs = 0;
        $totalRecordedPcs = 0;
        $hasCoils = false;

        foreach ($productStats as $p) {
            $diff = (float)$p->total_real - (float)$p->total_system;
            
            $pcsPerUnit = (float)($p->pcs_per_unit ?? 1);
            $grossCoil = (float)($p->gross_coil ?? 0);
            $unitLower = strtolower((string)($p->unit_name ?? 'pcs'));
            
            if (strpos($unitLower, 'coil') !== false) $hasCoils = true;

            // Calculate PCS conversions
            $diffPcs = 0;
            $recordedPcs = 0;
            
            // If gross_coil is specified (> 0), use the coil-weight formula
            if ($grossCoil > 0 && (strpos($unitLower, 'coil') !== false || strpos($unitLower, 'kg') !== false)) {
                $diffPcs = ($diff / $grossCoil) * $pcsPerUnit;
                $recordedPcs = ((float)$p->total_real / $grossCoil) * $pcsPerUnit;
            } else {
                $diffPcs = $diff * $pcsPerUnit;
                $recordedPcs = (float)$p->total_real * $pcsPerUnit;
            }

            // Round PCS to Integer - Standard Inventory Logic
            $diffPcs = (float)floor(abs($diffPcs)) * ($diffPcs >= 0 ? 1 : -1);
            $recordedPcs = (float)floor($recordedPcs);

            $totalRecordedPcs += $recordedPcs;
            $netPcs += $diffPcs;

            // Strict matching check
            if (abs($diff) < 0.00001) {
                $totalMatched++;
            } else {
                $totalDiff++;
                if ($diff > 0) {
                    $countIncrease++;
                    $totalIncrease += $diff;
                    $incPcs += $diffPcs;
                } else {
                    $countDecrease++;
                    $totalDecrease += abs($diff); 
                    $decPcs += abs($diffPcs);
                }
            }
        }

        // Financial Impact
        $financialStats = DB::table('inv_t_sto_detail as sd')
            ->leftJoin('inv_t_product_detail as pd', 'sd.product_detail_id', '=', 'pd.id')
            ->where('sd.event_id', $stoEvent->id)
            ->select(DB::raw('SUM((sd.real_qty_input - sd.system_qty_snapshot) * ISNULL(pd.weight_kg, 0) * ISNULL(pd.material_price, 0)) as impact'))
            ->first();

        // Calculate net adjustment properly based on Product Aggregate
        $netAdjustment = $totalIncrease - $totalDecrease;

        $totalProducts = \App\Models\InventoryModel\Material\InventoryProduct::where('is_active', 1)->count();

        return [
            'total_recorded_pcs' => (float)$totalRecordedPcs,
            'total_items' => (int)$totalItems,
            'total_diff' => (int)$totalDiff,
            'total_matched' => (int)$totalMatched,
            
            'count_increase' => (int)$countIncrease,
            'count_decrease' => (int)$countDecrease,
            'total_increase' => (float)$totalIncrease,
            'total_decrease' => (float)$totalDecrease,
            
            'total_increase_pcs' => (float)$incPcs,
            'total_decrease_pcs' => (float)$decPcs,
            'net_adjustment_pcs' => (float)$netPcs,
            'net_amount_impact' => (float)($financialStats->impact ?? 0),
            
            'total_count' => (int)$totalProducts,
            'total_missing_items' => (int)max(0, $totalProducts - $totalItems),
            'progress' => (float)($totalProducts > 0 ? round(($totalItems / $totalProducts) * 100, 1) : 0),
            'net_adjustment' => (float)$netAdjustment
        ];
    }
}
