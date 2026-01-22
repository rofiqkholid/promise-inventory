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
            $rowNumber = $start + 1;
            $transformedData = $data->map(function ($event) use (&$rowNumber) {
                $period = $event->period_start->format('d M Y');
                if ($event->period_end && $event->status === 'CLOSED') {
                    $period .= ' - ' . $event->period_end->format('d M Y');
                }

                $statusClass = $event->status === 'OPEN' 
                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' 
                    : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                
                $statusBadge = '<span class="px-2 py-1 text-xs rounded-full whitespace-nowrap ' . $statusClass . '">' . $event->status . '</span>';
                
                $actionBtn = $event->status === 'OPEN' 
                    ? '<a href="' . route('inventory.sto.show', $event->hash_id) . '" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-md text-sm font-medium transition-colors shadow-sm"><i class="fa-solid fa-list-check"></i> Manage</a>'
                    : '<a href="' . route('inventory.sto.show', $event->hash_id) . '" class="inline-flex items-center justify-center gap-2 bg-slate-700 hover:bg-slate-800 text-white px-3 py-1.5 rounded-md text-sm font-medium transition-colors shadow-sm" style="background-color: #334155; color: white;"><i class="fa-solid fa-eye"></i> View</a>';

                return [
                    $rowNumber++,
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

    public function show($id)
    {
        $stoEvent = StoEvent::findByHashOrFail($id);
        
        $statsData = $this->getStoStats($stoEvent);
        $stats = $statsData['stats'];
        $netAdjustment = $statsData['netAdjustment'];
        $progress = $statsData['progress'];

        $countedIds = StoDetail::where('event_id', $stoEvent->id)->pluck('product_detail_id')->toArray();

        $products = InventoryProduct::join('products', 'inv_t_product_detail.product_id', '=', 'products.id')
            ->select('inv_t_product_detail.id', 'products.part_no', 'products.part_name', 'inv_t_product_detail.revision')
            ->where('inv_t_product_detail.is_active', 1)
            ->whereNotIn('inv_t_product_detail.id', $countedIds)
            ->orderBy('products.part_no')
            ->get();

        return view('inventory.sto.show', compact('stoEvent', 'stats', 'products', 'netAdjustment', 'progress'));
    }

    /**
     * Get STO Details for DataTables.
     */
    public function detailsData(Request $request, $id)
    {
        $stoEvent = StoEvent::findByHashOrFail($id);
        
        $query = StoDetail::with(['product', 'product.product', 'product.unit', 'auditor'])
            ->where('event_id', $stoEvent->id);

        // Searching
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('product.product', function($sq) use ($search) {
                    $sq->where('part_no', 'like', "%{$search}%")
                       ->orWhere('part_name', 'like', "%{$search}%");
                })->orWhere('remark', 'like', "%{$search}%");
            });
        }

        // Ordering
        $columns = ['row_number', 'updated_at', 'product_id', 'system_qty_snapshot', 'real_qty_input', 'diff_qty', 'remark', 'action']; 
        $colIndex = $request->input('order.0.column', 0);
        $dir = $request->input('order.0.dir', 'desc');
        $colName = $columns[$colIndex] ?? 'updated_at';

        if ($colName === 'product_id') {
             $query->join('inv_t_product_detail', 'inv_t_sto_detail.product_detail_id', '=', 'inv_t_product_detail.id')
                    ->join('products', 'inv_t_product_detail.product_id', '=', 'products.id')
                    ->orderBy('products.part_no', $dir)
                    ->select('inv_t_sto_detail.*'); // Avoid column collision
        } elseif ($colName !== 'action' && $colName !== 'row_number' && $colName !== 'remark') {
             $query->orderBy($colName, $dir);
        } else {
             $query->orderBy('updated_at', 'desc');
        }

        $recordsTotal = StoDetail::where('event_id', $stoEvent->id)->count();
        $recordsFiltered = $query->count();

        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        $data = $query->skip($start)->take($limit)->get();
        
        // Calculate starting row number for this page
        $rowNumber = $start + 1;

        $transformedData = $data->map(function ($detail) use ($stoEvent, &$rowNumber) {
            $pcsPerUnit = $detail->product->pcs_per_unit ?? 1;
            $unitCode = $detail->product->unit->code ?? 'PCS';

            $formatQty = function($qty, $pcsPerUnit, $unitCode, $prefix = '') {
                // Formatting helper
                $qty = floatval($qty);
                $pcs = $qty * $pcsPerUnit;
                
                $pcsDisplay = number_format($pcs, 0);
                
                if ($pcsPerUnit == 1) {
                    return "<span class='font-bold'>{$prefix}{$pcsDisplay}</span>";
                }
                
                $unitDisplay = number_format($qty, 0); 
                return "
                    <div class='flex flex-col items-center justify-center'>
                        <span class='font-bold'>{$prefix}{$pcsDisplay}</span>
                        <span class='text-[10px] text-gray-400 leading-none mt-0.5'>({$unitDisplay} {$unitCode})</span>
                    </div>
                ";
            };

            $diff = $detail->real_qty_input - $detail->system_qty_snapshot;
            
            $productInfo = '
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200">' . ($detail->product->product->part_no ?? '-') . ' - ' . ($detail->product->revision ?? '') . '</span>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400 leading-tight uppercase">' . ($detail->product->product->part_name ?? '-') . '</span>';
            
            
            if ($detail->auditor) {
                $productInfo .= '
                    <div class="mt-1 flex items-center gap-1 text-[10px] text-blue-500 font-semibold">
                        <i class="fa-solid fa-user-check"></i> ' . $detail->auditor->name . '
                    </div>';
            }
            $productInfo .= '</div>';

            $diffHtml = '';
            
            if ($diff > 0) {
                $diffHtml = '<div class="text-green-600">' . $formatQty(abs($diff), $pcsPerUnit, $unitCode, '+') . '</div>';
            } elseif ($diff < 0) {
                $diffHtml = '<div class="text-red-600">' . $formatQty(abs($diff), $pcsPerUnit, $unitCode, '-') . '</div>';
            } else {
                $diffHtml = '<span class="text-sm font-medium text-gray-300">-</span>';
            }

           // Inline editable QTY field for OPEN status
            $qtyHtml = '';
            if ($stoEvent->status === 'OPEN') {
                $qtyHtml = '
                    <div class="flex items-center justify-center gap-1">
                        <input type="number" step="any" 
                            class="qty-input text-center font-bold text-sm px-2 py-1 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400" 
                            style="width: 80px; min-width: 80px;"
                            data-detail-id="' . $detail->hash_id . '" 
                            data-product-id="' . $detail->product->hash_id . '"
                            value="' . ($detail->real_qty_input + 0) . '" 
                            placeholder="Qty" />
                        <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400">' . $unitCode . '</span>
                    </div>';
            } else {
                $qtyHtml = '<div class="text-blue-600 dark:text-blue-400">' . $formatQty($detail->real_qty_input, $pcsPerUnit, $unitCode) . '</div>';
            }
            
            // Inline editable REMARK field
            $remarkHtml = '';
            if ($stoEvent->status === 'OPEN') {
                $remarkValue = htmlspecialchars($detail->remark ?? '', ENT_QUOTES);
                $remarkHtml = '<input type="text" 
                    class="remark-input text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300" 
                    style="width: 180px; min-width: 180px;"
                    data-detail-id="' . $detail->hash_id . '" 
                    value="' . $remarkValue . '" 
                    placeholder="Add note..." />';
            } else {
                $remarkHtml = '<span class="text-xs text-gray-600 dark:text-gray-400">' . ($detail->remark ?: '-') . '</span>';
            }

            $actionHtml = '';
            if ($stoEvent->status === 'OPEN') {
                $actionHtml = '
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="deleteItem(\'' . $detail->hash_id . '\')" 
                                class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors" title="Delete">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>';
            }
            
            $currentRow = $rowNumber++;

            return [
                'row_number' => $currentRow,
                'updated_at' => $detail->updated_at->format('H:i'),
                'product_info' => $productInfo,
                'system_qty' => $formatQty($detail->system_qty_snapshot, $pcsPerUnit, $unitCode),
                'real_qty' => $qtyHtml,
                'diff' => $diffHtml,
                'remark' => $remarkHtml,
                'action' => $actionHtml
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
                'part_no' => ($product->product->part_no ?? '-') . ' - ' . ($product->revision ?? ''),
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
        
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

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
        
        // diff_qty is a computed column in SQL Server - it auto-calculates
        
        // Attempt to find PIC based on Auth User
        $user = auth()->user();
        if ($user) {
            $pic = PIC::where('name', $user->name)->first();
            if ($pic) {
                $detail->auditor_id = $pic->id;
            }
        }
        
        $detail->save();

        return response()->json([
            'success' => true, 
            'message' => 'Saved',
            'stats' => $this->getStoStats($stoEvent)
        ]);
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

        return response()->json([
            'success' => true, 
            'message' => 'Item deleted successfully.',
            'stats' => $this->getStoStats($stoEvent)
        ]);
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
        $stoEvent = \App\Models\InventoryModel\StoEvent::findByHashOrFail($id);
        $stoEvent->load(['details.product.product', 'details.auditor', 'pic']);

        $filename = "STO_{$stoEvent->code}_" . now()->format('Ymd_His') . ".xlsx";
        
        return Excel::download(new StoExport($stoEvent), $filename);
    }

    private function getStoStats($stoEvent)
    {
        $baseQuery = StoDetail::where('event_id', $stoEvent->id);

        $pcsStats = StoDetail::leftJoin('inv_t_product_detail', 'inv_t_sto_detail.product_detail_id', '=', 'inv_t_product_detail.id')
            ->where('inv_t_sto_detail.event_id', $stoEvent->id)
            ->select(
                DB::raw('SUM(CASE WHEN inv_t_sto_detail.diff_qty > 0 THEN inv_t_sto_detail.diff_qty * inv_t_product_detail.pcs_per_unit ELSE 0 END) as inc_pcs'),
                DB::raw('SUM(CASE WHEN inv_t_sto_detail.diff_qty < 0 THEN inv_t_sto_detail.diff_qty * inv_t_product_detail.pcs_per_unit ELSE 0 END) as dec_pcs'),
                DB::raw('SUM(inv_t_sto_detail.diff_qty * inv_t_product_detail.pcs_per_unit) as net_pcs')
            )
            ->first();

        $stats = [
            'total_items' => (clone $baseQuery)->count(),
            'total_diff' => (clone $baseQuery)->where('diff_qty', '!=', 0)->count(),
            'total_matched' => (clone $baseQuery)->where('diff_qty', 0)->count(),
            'total_increase' => (clone $baseQuery)->where('diff_qty', '>', 0)->sum('diff_qty'),
            'total_decrease' => (clone $baseQuery)->where('diff_qty', '<', 0)->sum('diff_qty'),
            'count_increase' => (clone $baseQuery)->where('diff_qty', '>', 0)->count(),
            'count_decrease' => (clone $baseQuery)->where('diff_qty', '<', 0)->count(),
            
            'total_increase_pcs' => $pcsStats->inc_pcs ?? 0,
            'total_decrease_pcs' => $pcsStats->dec_pcs ?? 0,
            'net_adjustment_pcs' => $pcsStats->net_pcs ?? 0,
        ];

        $netAdjustment = (clone $baseQuery)->sum('diff_qty');
        
        $totalProducts = InventoryProduct::where('is_active', 1)->count();
        $progress = $totalProducts > 0 ? round(($stats['total_items'] / $totalProducts) * 100, 1) : 0;

        return ['stats' => $stats, 'netAdjustment' => $netAdjustment, 'progress' => $progress];
    }
}
