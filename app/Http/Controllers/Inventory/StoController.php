<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\StoEvent;
use App\Models\InventoryModel\StoDetail;
use App\Models\InventoryModel\InventoryProduct;
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

            // Ordering
            if ($request->has('order')) {
                $columns = ['code', 'name', 'period_start', 'status', 'pic_id', 'created_at']; // Map index to col name
                $colIndex = $request->input('order.0.column');
                $colName = $columns[$colIndex] ?? 'created_at';
                $dir = $request->input('order.0.dir', 'desc');
                
                if ($colName === 'pic_id') {
                    // Sort by relationship? Complex, fallback to created_at for now to avoid join complexity unless needed
                    $query->orderBy('created_at', $dir); 
                } else {
                    $query->orderBy($colName, $dir);
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Count Total & Filtered
            $recordsTotal = StoEvent::count();
            $recordsFiltered = $query->count();

            // Pagination
            $limit = $request->input('length', 10);
            $start = $request->input('start', 0);
            $data = $query->skip($start)->take($limit)->get();

            // Transform Data for View
            $transformedData = $data->map(function ($event) {
                $period = $event->period_start->format('d M Y');
                if ($event->period_end && $event->status === 'CLOSED') {
                    $period .= ' - ' . $event->period_end->format('d M Y');
                }

                $statusClass = $event->status === 'OPEN' 
                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' 
                    : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                
                $statusBadge = '<span class="px-2 py-1 text-xs rounded-full whitespace-nowrap ' . $statusClass . '">' . $event->status . '</span>';
                
                $actionBtn = '<a href="' . route('inventory.sto.show', $event->hash_id) . '" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-semibold text-sm">' . ($event->status === 'OPEN' ? 'Manage' : 'View') . '</a>';

                return [
                    $event->code,
                    $event->name,
                    $period,
                    $statusBadge,
                    $event->pic->name ?? '-',
                    $actionBtn // No logic here, just raw HTML or view component could be better but this works for AJAX
                ];
            });

            return response()->json([
                "draw" => intval($request->input('draw')),
                "recordsTotal" => $recordsTotal,
                "recordsFiltered" => $recordsFiltered,
                "data" => $transformedData
            ]);
        }

        $events = StoEvent::with('pic')->orderBy('created_at', 'desc')->get(); // Fallback for initial load if needed, but DataTable will call AJAX
        $pics = PIC::where('is_active', 1)->get();
        return view('inventory.sto.index', compact('events', 'pics'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->decodeHashInputs($request->all(), [
            'pic_id' => PIC::class,
        ]);
        $request->merge($data);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'period_start' => 'required|date',
            'pic_id' => 'required|exists:inv_m_pic,id',
            'description' => 'nullable|string',
        ]);

        // Generate Code: STO-YYYY-MM-{Sequence}
        $date = now();
        $prefix = 'STO-' . $date->format('Y-m');
        $lastCode = StoEvent::where('code', 'like', "$prefix%")->orderBy('id', 'desc')->value('code');
        
        if ($lastCode) {
            $lastSeq = (int) substr($lastCode, -3);
            $newSeq = str_pad($lastSeq + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newSeq = '001';
        }
        
        $validated['code'] = "$prefix-$newSeq";
        $validated['status'] = 'OPEN';

        StoEvent::create($validated);

        return redirect()->route('inventory.sto.index')->with('success', 'STO Event created successfully.');
    }

    /**
     * Display the specified resource (Worksheet).
     */
    public function show($id)
    {
        $stoEvent = StoEvent::findByHashOrFail($id);
        
        // Load Details with Product Info - Fix N+1 query by eager loading
        $details = StoDetail::with(['product.product', 'product.unit', 'auditor'])
            ->where('event_id', $stoEvent->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        $stats = [
            'total_items' => $details->count(),
            'total_diff' => $details->where('diff_qty', '!=', 0)->count(),
            'total_matched' => $details->where('diff_qty', 0)->count(),
        ];

        // Fetch all products for Select2 options (Similar to InventoryTransaction)
        // EXCLUDE products already counted in this event
        $countedIds = $details->pluck('product_detail_id')->toArray();

        $products = InventoryProduct::join('products', 'inv_t_product_detail.product_id', '=', 'products.id')
            ->select('inv_t_product_detail.id', 'products.part_no', 'products.part_name', 'inv_t_product_detail.revision')
            ->where('inv_t_product_detail.is_active', 1)
            ->whereNotIn('inv_t_product_detail.id', $countedIds)
            ->orderBy('products.part_no')
            ->get();

        return view('inventory.sto.show', compact('stoEvent', 'details', 'stats', 'products'));
    }

    /**
     * Process Scan and Return Product Info.
     */
    public function scan(Request $request, $id)
    {
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
             // HashID from JSON
             $productId = InventoryProduct::decodeHash($decodedJson['id']);
        } else {
             // Try HashID direct
             $productId = InventoryProduct::decodeHash($input);
             
             // Fallback logic if needed (e.g. user insists on old scanners, but we removed base64 support per task).
             // But let's assume valid JSON or HashID.
        }

        if (!$productId) {
            return response()->json(['success' => false, 'message' => 'Invalid QR Code.'], 404);
        }

        $product = InventoryProduct::with(['product', 'unit'])->find($productId);
        
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        // Check if already counted
        $existing = StoDetail::where('event_id', $stoEvent->id)
            ->where('product_detail_id', $product->id)
            ->first();

        $systemQty = $existing ? $existing->system_qty_snapshot : $product->current_stock_qty;
        $prevReal = $existing ? $existing->real_qty_input : null;

        return response()->json([
            'success' => true,
            'data' => [
                'product_id_hash' => $product->hash_id,
                'part_no' => $product->product->part_no ?? '-',
                'part_name' => $product->product->part_name ?? '-',
                'unit' => $product->unit->code ?? 'PCS',
                'system_qty' => $systemQty,
                'prev_real_qty' => $prevReal,
                'is_new_snapshot' => !$existing
            ]
        ]);
    }

    /**
     * Save Count Result.
     */
    public function saveCount(Request $request, $id)
    {
        $stoEvent = StoEvent::findByHashOrFail($id);
        
        if ($stoEvent->status !== 'OPEN') {
            return response()->json(['success' => false, 'message' => 'Event is closed.'], 403);
        }

        $request->validate([
            'product_id_hash' => 'required',
            'real_qty' => 'required|numeric|min:0',
            'remark' => 'nullable|string|max:255'
        ]);

        $productId = InventoryProduct::decodeHash($request->product_id_hash);
        $product = InventoryProduct::find($productId);
        
        if (!$product) abort(404, 'Product not found');

        // Upsert Detail
        $detail = StoDetail::where('event_id', $stoEvent->id)
            ->where('product_detail_id', $productId)
            ->first();

        if (!$detail) {
            // New Entry: Snapshot System Qty
            $detail = new StoDetail();
            $detail->event_id = $stoEvent->id;
            $detail->product_detail_id = $productId;
            $detail->system_qty_snapshot = $product->current_stock_qty;
        }

        $detail->real_qty_input = $request->real_qty;
        $detail->remark = $request->remark;
        
        // Calculate diff_qty
        $detail->diff_qty = $request->real_qty - $detail->system_qty_snapshot;
        
        // Attempt to find PIC based on Auth User (assuming User Name matches PIC Name or similar, or just leave null if not linked)
        // Ideally User model should have 'pic_id' or we search by name.
        $user = auth()->user();
        if ($user) {
            $pic = PIC::where('name', $user->name)->first();
            if ($pic) {
                $detail->auditor_id = $pic->id;
            }
        }
        
        $detail->save();

        return response()->json(['success' => true, 'message' => 'Saved']);
    }

    /**
     * Finalize Event and Adjust Stock.
     */
    public function finalize($id)
    {
        $stoEvent = StoEvent::findByHashOrFail($id);
        
        if ($stoEvent->status === 'CLOSED') {
             return redirect()->route('inventory.sto.index')->with('error', 'Event is already closed.');
        }

        // Validation: Check if there are any counted items
        $totalItems = StoDetail::where('event_id', $stoEvent->id)->count();
        if ($totalItems === 0) {
            return back()->with('error', 'Cannot finalize empty STO event. Please count at least one item.');
        }

        try {
            DB::beginTransaction();

            $stoEvent->status = 'CLOSED';
            $stoEvent->period_end = now();
            $stoEvent->save();

            // Process Adjustments - Directly update stock based on diff_qty
            $details = StoDetail::where('event_id', $stoEvent->id)
                ->where('is_adjusted', 0) // Only process unadjusted lines
                ->get(); 

            $count = 0;
            $errors = [];

            foreach ($details as $detail) {
                // Use the already calculated diff_qty from the detail record
                $diff = $detail->diff_qty;
                
                // Skip if no difference
                if ($diff == 0) {
                    $detail->is_adjusted = true;
                    $detail->save();
                    continue;
                }

                // Update Master Stock directly
                $product = InventoryProduct::find($detail->product_detail_id);
                if ($product) {
                    // diff_qty is already signed (positive = add, negative = subtract)
                    $product->current_stock_qty += $diff;
                    $product->save();
                    
                    // Mark as adjusted
                    $detail->is_adjusted = true;
                    $detail->save();
                    
                    $count++;
                } else {
                    // Log error but continue processing
                    $errors[] = "Product ID {$detail->product_detail_id} not found";
                }
            }

            DB::commit();
            
            $message = "STO Event finalized. Stock updated for {$count} items.";
            if (!empty($errors)) {
                $message .= " Warnings: " . implode(', ', $errors);
            }
            
            return redirect()->route('inventory.sto.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error finalizing: ' . $e->getMessage());
        }
    }

    /**
     * Delete a counted item from STO detail.
     */
    public function deleteDetail($id, $detailId)
    {
        $stoEvent = StoEvent::findByHashOrFail($id);
        
        if ($stoEvent->status !== 'OPEN') {
            return response()->json(['success' => false, 'message' => 'Cannot delete from closed event.'], 403);
        }

        $detail = StoDetail::findByHashOrFail($detailId);
        
        if ($detail->event_id !== $stoEvent->id) {
            return response()->json(['success' => false, 'message' => 'Detail does not belong to this event.'], 403);
        }

        $detail->delete();

        return response()->json(['success' => true, 'message' => 'Item deleted successfully.']);
    }

    /**
     * Reopen a closed STO event (Admin only).
     */
    public function reopen($id)
    {
        $stoEvent = StoEvent::findByHashOrFail($id);
        
        if ($stoEvent->status === 'OPEN') {
            return back()->with('error', 'Event is already open.');
        }

        try {
            DB::beginTransaction();

            // Reverse stock adjustments
            $details = StoDetail::where('event_id', $stoEvent->id)
                ->where('is_adjusted', 1)
                ->get();

            foreach ($details as $detail) {
                $diff = $detail->diff_qty;
                
                if ($diff == 0) {
                    $detail->is_adjusted = false;
                    $detail->save();
                    continue;
                }

                $product = InventoryProduct::find($detail->product_detail_id);
                if ($product) {
                    $oldStock = $product->current_stock_qty;
                    
                    // Reverse the adjustment (subtract what was added, add what was subtracted)
                    $product->current_stock_qty -= $diff;
                    $product->save();
                    
                    $detail->is_adjusted = false;
                    $detail->save();
                }
            }

            // Reopen event
            $stoEvent->status = 'OPEN';
            $stoEvent->period_end = null;
            $stoEvent->save();
            
            // Refresh to ensure latest data
            $stoEvent->refresh();

            DB::commit();
            
            return redirect()->route('inventory.sto.show', $stoEvent->hash_id)
                ->with('success', 'STO Event reopened successfully. Stock adjustments have been reversed.');

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
        // Explicitly call the static method on the model class
        $stoEvent = \App\Models\InventoryModel\StoEvent::findByHashOrFail($id);
        $stoEvent->load(['details.product.product', 'details.auditor', 'pic']);

        $filename = "STO_{$stoEvent->code}_" . now()->format('Ymd_His') . ".xlsx";
        
        return Excel::download(new StoExport($stoEvent), $filename);
    }
}
