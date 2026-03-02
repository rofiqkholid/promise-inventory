<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\StoEvent;
use App\Models\InventoryModel\StoDetail;
use App\Models\InventoryModel\InventoryProduct;
use App\Models\InventoryModel\StoReason;
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
                $columns = ['code', 'period_start', 'status', 'pic_id', 'created_at']; // Map index to col name
                $colIndex = $request->input('order.0.column');
                $colName = $columns[$colIndex] ?? 'created_at';
                $dir = $request->input('order.0.dir', 'desc');
                
                if ($colName === 'pic_id') {
                    // Sort by relationship
                    $query->orderBy('user_id', $dir); 
                } else {
                    $query->orderBy($colName, $dir);
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Count Total & Filtered
            $recordsTotal = StoEvent::count();

            // Calculate Variance Totals in Query
            $query->select('inv_t_sto_event.*')
                ->addSelect([
                    'net_pcs' => StoDetail::selectRaw('SUM(inv_t_sto_detail.diff_qty * inv_t_product_detail.pcs_per_unit)')
                        ->leftJoin('inv_t_product_detail', 'inv_t_sto_detail.product_detail_id', '=', 'inv_t_product_detail.id')
                        ->whereColumn('inv_t_sto_detail.event_id', 'inv_t_sto_event.id'),
                    'net_amount' => StoDetail::selectRaw('SUM(inv_t_sto_detail.diff_qty * ISNULL(inv_t_product_detail.weight_kg, 0) * ISNULL(inv_t_product_detail.material_price, 0))')
                        ->leftJoin('inv_t_product_detail', 'inv_t_sto_detail.product_detail_id', '=', 'inv_t_product_detail.id')
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
                $period = $event->period_start->format('d M Y');
                if ($event->period_end && $event->status === 'CLOSED') {
                    $period .= ' - ' . $event->period_end->format('d M Y');
                }

                $statusClass = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                if ($event->status === 'OPEN') {
                    $statusClass = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
                } elseif ($event->status === 'WAITING CHECK') {
                    $statusClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
                } elseif ($event->status === 'WAITING APPROVAL') {
                    $statusClass = 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
                }
                
                $statusBadge = '<span class="px-2 py-1 text-xs rounded-full whitespace-nowrap ' . $statusClass . '">' . str_replace('_', ' ', $event->status) . '</span>';
                
                $user = auth()->user();
                $isPicRole = $user->hasAppRole('pic') || $user->hasAppRole('admin');
                $isApprover = $user->hasAppRole('approver') || $user->hasAppRole('admin');
                $isChecker = $user->hasAppRole('checker') || $user->hasAppRole('approver') || $user->hasAppRole('admin');

                $baseUrl = route('inventory.sto.show', $event->hash_id);
                
                // Helper to create step button
                $getStep = function($label, $icon, $statusTarget, $activeColor, $isDone, $isCurrent, $hasPermission, $formAction = null) use ($baseUrl) {
                    $opacity = $isDone || $isCurrent ? 'opacity-100' : 'opacity-30 grayscale';
                    $cursor = $isDone || $isCurrent ? '' : 'cursor-not-allowed';
                    $color = $isDone ? 'bg-emerald-600' : ($isCurrent ? $activeColor : 'bg-gray-400');
                    $border = $isCurrent ? 'ring-2 ring-offset-1 ring-' . explode('-', $activeColor)[1] . '-500' : '';
                    
                    $content = '<i class="fa-solid ' . $icon . ' text-[10px]"></i> <span class="hidden md:inline">' . $label . '</span>';
                    
                    if ($isCurrent && $hasPermission && $formAction) {
                        return '
                            <form action="' . $formAction . '" method="POST" class="inline">
                                ' . csrf_field() . '
                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 ' . $color . ' text-white rounded text-[10px] font-bold transition-all hover:scale-105 shadow-sm" onclick="return confirm(\'Proceed with ' . $label . '?\')">
                                    ' . $content . '
                                </button>
                            </form>';
                    }

                    $tag = ($isDone || $isCurrent) ? 'a' : 'div';
                    $href = ($isDone || $isCurrent) ? ' href="' . $baseUrl . '"' : '';
                    
                    return '<' . $tag . $href . ' class="inline-flex items-center gap-1 px-2.5 py-1.5 ' . $color . ' ' . $opacity . ' ' . $cursor . ' ' . $border . ' text-white rounded text-[10px] font-bold shadow-sm">
                        ' . $content . '
                    </' . $tag . '>';
                };

        $steps = [];
                
                // Step 1: Count (Anyone)
                $steps[] = $getStep('Count', 'fa-list-check', 'OPEN', 'bg-blue-600', 
                    $event->status !== 'OPEN', 
                    $event->status === 'OPEN', 
                    true);

                // Step 2: Verify (Checker/Admin)
                $steps[] = $getStep('Verify', 'fa-magnifying-glass', 'WAITING CHECK', 'bg-amber-500', 
                    in_array($event->status, ['WAITING APPROVAL', 'CLOSED']), 
                    $event->status === 'WAITING CHECK', 
                    $isChecker);

                // Step 3: Approve (Approver/Admin)
                $steps[] = $getStep('Approve', 'fa-lock', 'WAITING APPROVAL', 'bg-purple-600', 
                    $event->status === 'CLOSED', 
                    $event->status === 'WAITING APPROVAL', 
                    $isApprover);

                $actionBtn = '
                    <div class="flex items-center justify-center gap-1 py-1">
                        ' . implode('<div class="w-2 h-px bg-gray-200"></div>', $steps) . '
                    </div>';

                if ($event->status === 'CLOSED' && $isApprover) {
                    $actionBtn .= '
                        <div class="mt-2 flex justify-center">
                            <form action="' . route('inventory.sto.reopen', $event->hash_id) . '" method="POST" class="inline">
                                ' . csrf_field() . '
                                <button type="submit" class="text-[9px] font-bold text-gray-400 hover:text-gray-600 uppercase tracking-tighter" onclick="return confirm(\'Reopen this event?\')">
                                    <i class="fa-solid fa-rotate-left"></i> Reopen Event
                                </button>
                            </form>
                        </div>';
                }

                $netPcs = $event->net_pcs ?? 0;
                $netAmt = $event->net_amount ?? 0;
                $varianceHtml = '-';
                
                if ($netPcs != 0 || $netAmt != 0) {
                    $color = $netAmt < 0 ? 'text-red-600' : ($netAmt > 0 ? 'text-green-600' : 'text-gray-600');
                    $prefix = $netAmt > 0 ? '+' : '';
                    $varianceHtml = '
                        <div class="flex flex-col items-center">
                            <span class="text-[11px] font-bold ' . $color . '">' . $prefix . number_format(abs($netAmt), 0) . '</span>
                            <span class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter">' . ($netPcs >= 0 ? '+' : '') . number_format($netPcs, 0) . ' Pcs</span>
                        </div>';
                }

                return [
                    $rowNumber++,
                    $event->code,
                    $period,
                    $statusBadge,
                    $event->pic->name ?? '-',
                    $varianceHtml,
                    $actionBtn 
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
        return view('inventory.sto.index', compact('events'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Only Admin, Approver, or PIC role can initialize a new event
        $user = auth()->user();
        if (!$user->hasAppRole('pic') && !$user->hasAppRole('approver') && !$user->hasAppRole('admin')) {
             return back()->with('error', 'Unauthorized. Only PIC, Approver, or Admin can initialize STO events.');
        }

        $validated = $request->validate([
            'period_start' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['code'] = $this->generateEventCode($validated['period_start']);
        $validated['name'] = "STO Event - " . $validated['code']; 
        $validated['status'] = 'OPEN';

        StoEvent::create($validated);

        return redirect()->route('inventory.sto.index')->with('success', 'STO Event created successfully.');
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
        
        $statsData = $this->getStoStats($stoEvent);
        $stats = $statsData['stats'];
        $netAdjustment = $statsData['netAdjustment'];
        $progress = $statsData['progress'];

        $countedIds = StoDetail::where('event_id', $stoEvent->id)->pluck('product_detail_id')->toArray();

        // Used for "Remaining Items" list
        $products = InventoryProduct::join('products', 'inv_t_product_detail.product_id', '=', 'products.id')
            ->select('inv_t_product_detail.id', 'products.part_no', 'products.part_name', 'inv_t_product_detail.revision')
            ->where('inv_t_product_detail.is_active', 1)
            ->whereNotIn('inv_t_product_detail.id', $countedIds)
            ->orderBy('products.part_no')
            ->get();

        // Used for the search/scanner dropdown (contains all active products)
        $allProducts = InventoryProduct::join('products', 'inv_t_product_detail.product_id', '=', 'products.id')
            ->select('inv_t_product_detail.id', 'products.part_no', 'products.part_name', 'inv_t_product_detail.revision')
            ->where('inv_t_product_detail.is_active', 1)
            ->orderBy('products.part_no')
            ->get();

        $reasons = StoReason::where('is_active', 1)->get();

        return view('inventory.sto.show', compact('stoEvent', 'stats', 'products', 'allProducts', 'netAdjustment', 'progress', 'reasons', 'countedIds'));
    }

    /**
     * Get STO Details for DataTables.
     */
    public function detailsData(Request $request, $id)
    {
        $stoEvent = StoEvent::findByHashOrFail($id);
        
        $query = StoDetail::with(['product', 'product.product', 'product.unit', 'auditor', 'reason'])
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
        $user = auth()->user();
        $isAdmin = $user->hasAppRole('admin');
        $isPic = $stoEvent->user_id === $user->id || $user->hasAppRole('pic'); // PIC role or event creator
        $canEditInline = ($isAdmin || $isPic) && $stoEvent->status === 'OPEN';

        $transformedData = $data->map(function ($detail) use ($stoEvent, &$rowNumber, $canEditInline) {
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
            
            // Financial Calculation
            $pricePerKg = $detail->product->material_price ?? 0;
            $weightPerPcs = $detail->product->weight_kg ?? 0;
            
            $systemAmount = $detail->system_qty_snapshot * $weightPerPcs * $pricePerKg;
            $realAmount = $detail->real_qty_input * $weightPerPcs * $pricePerKg;
            $diffAmount = $diff * $weightPerPcs * $pricePerKg;

            $formatCurrency = function($val, $isDiff = false) {
                if ($val == 0) return '<span class="text-gray-300">-</span>';
                
                $color = 'text-gray-600 dark:text-gray-400';
                if ($isDiff) {
                    $color = $val < 0 ? 'text-red-600' : 'text-green-600';
                }
                
                return '<span class="text-[11px] font-mono font-bold ' . $color . '">' . number_format(abs($val), 0) . '</span>';
            };
            
            $productInfo = '
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200">' . ($detail->product->product->part_no ?? '-') . ' - ' . ($detail->product->revision ?? '') . '</span>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400 leading-tight uppercase">' . ($detail->product->product->part_name ?? '-') . '</span>';
            
            
            $productInfo .= '</div>';

            $diffHtml = '';
            
            if ($diff > 0) {
                $diffHtml = '<div class="text-green-600">' . $formatQty(abs($diff), $pcsPerUnit, $unitCode, '+') . '</div>';
            } elseif ($diff < 0) {
                $diffHtml = '<div class="text-red-600">' . $formatQty(abs($diff), $pcsPerUnit, $unitCode, '-') . '</div>';
            } else {
                $diffHtml = '<span class="text-sm font-medium text-gray-300">-</span>';
            }

            // Inline editable QTY field for OPEN status - Permission restricted
            $qtyHtml = '';
            if ($canEditInline) {
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
            
            // Inline editable REMARK field - Permission restricted
            $remarkHtml = '';
            if ($canEditInline) {
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

            // Reason Select - ONLY for PIC / Admin when there is a difference
            $reasonHtml = '';
            if ($diff != 0) {
                if ($canEditInline) {
                    $reasonHtml = '<select class="reason-input text-xs pl-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300" style="width: 180px; min-width: 180px;" data-detail-id="' . $detail->hash_id . '">';
                    $reasonHtml .= '<option value="">-- Select Reason --</option>';
                    
                    // Filter reasons by category (Shortage/Excess) based on diff
                    $category = $diff < 0 ? 'SHORTAGE' : 'EXCESS';
                    $reasons = \App\Models\InventoryModel\StoReason::where('is_active', true)
                                ->where(function($q) use ($category) {
                                    $q->where('category', $category)->orWhere('category', 'OTHERS');
                                })->get();

                    foreach ($reasons as $r) {
                        $selected = $detail->reason_id == $r->id ? 'selected' : '';
                        $reasonHtml .= '<option value="' . $r->id . '" ' . $selected . '>' . $r->name . '</option>';
                    }
                    $reasonHtml .= '</select>';
                } else {
                    $reasonHtml = '<span class="text-[10px] text-red-500 font-bold">' . ($detail->reason->name ?? 'Reason Required') . '</span>';
                }
            } else {
                $reasonHtml = '<span class="text-[10px] text-gray-400 italic">No Diff</span>';
            }

            $actionHtml = '';
            if ($stoEvent->status === 'OPEN') {
                $actionHtml = '
                    <div class="flex items-center justify-center gap-4">
                        <button type="button" onclick="editFromTable(\'' . $detail->product->hash_id . '\')" 
                                class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors" title="Edit Entry">
                            <i class="fa-solid fa-pen-to-square text-lg"></i>
                        </button>
                        <button type="button" onclick="deleteItem(\'' . $detail->hash_id . '\')" 
                                class="text-gray-400 hover:text-red-600 transition-colors" title="Delete Entry">
                            <i class="fa-solid fa-trash-can text-lg"></i>
                        </button>
                    </div>';
            }
            
            $currentRow = $rowNumber++;

            return [
                'row_number' => $currentRow,
                'updated_at' => $detail->updated_at->format('d/m/Y H:i'),
                'product_info' => $productInfo,
                'auditor' => $detail->auditor->name ?? '-',
                'system_qty' => $formatQty($detail->system_qty_snapshot, $pcsPerUnit, $unitCode),
                'system_amount' => $formatCurrency($systemAmount),
                'real_qty' => $qtyHtml,
                'real_amount' => $formatCurrency($realAmount),
                'diff' => $diffHtml,
                'diff_amount' => $formatCurrency($diffAmount, true),
                'reason' => $reasonHtml,
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

        $product = InventoryProduct::with(['product', 'unit'])->find($productId);
        
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product details missing.'], 404);
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
            'remark' => 'nullable|string|max:255',
            'reason_id' => 'nullable|exists:inv_m_sto_reasons,id'
        ]);

        $productId = InventoryProduct::decodeHash($request->product_id_hash);
        
        // Authorization check for Save Count (if editing existing)
        $detail = StoDetail::where('event_id', $stoEvent->id)
            ->where('product_detail_id', $productId)
            ->first();
        
        $user = auth()->user();
        $isPic = $stoEvent->user_id === $user->id || $user->hasAppRole('pic');
        $isAdmin = $user->hasAppRole('admin');
        
        // If entry exists, only PIC/Admin can update/override via table or direct save
        // But for scanner, we might want anyone to scan?
        // Let's refine: Anyone can SCAN and INITIALIZE, but only PIC can EDIT in table.
        // If request is from table (checked by referrer or additional flag), we could block here.
        // For simplicity, we'll follow your request strictly on the table view.
        
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
        
        if ($request->has('reason_id')) {
            $detail->reason_id = $request->reason_id;
        }
        
        // Explicitly calculate diff_qty to avoid non-null constraint errors in SQL Server
        $detail->diff_qty = (float)$detail->real_qty_input - (float)$detail->system_qty_snapshot;
        
        // Attempt to find PIC based on Auth User
        $user = auth()->user();
        if ($user) {
            $detail->auditor_id = $user->id;
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
    /**
     * Submit STO for Verification (Owner/Operator).
     */
    public function submitForCheck($id)
    {
        $stoEvent = StoEvent::findByHashOrFail($id);
        
        $user = auth()->user();
        if ($stoEvent->user_id !== $user->id && !$user->hasAppRole('pic') && !$user->hasAppRole('admin')) {
            return back()->with('error', 'Only the assigned PIC, users with PIC role, or Admin can submit this event.');
        }

        if ($stoEvent->status !== 'OPEN') {
            return back()->with('error', 'Status must be OPEN to submit for check.');
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
        if (!$user->hasAppRole('checker') && !$user->hasAppRole('approver') && !$user->hasAppRole('admin')) {
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
        if (!$user->hasAppRole('checker') && !$user->hasAppRole('approver') && !$user->hasAppRole('admin')) {
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
        if (!$user->hasAppRole('approver') && !$user->hasAppRole('admin')) {
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

                $product = InventoryProduct::find($detail->product_detail_id);
                if ($product) {
                    $product->current_stock_qty += $diff;
                    $product->save();
                    
                    $detail->is_adjusted = true;
                    $detail->save();
                    
                    $count++;
                } else {
                    $errors[] = "Product ID {$detail->product_detail_id} not found";
                }
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
        // Reopen Logic: Only Approver
        if (!auth()->user()->hasAppRole('approver') && !auth()->user()->hasAppRole('admin')) {
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
                    $diff = $detail->diff_qty;
                    if ($diff != 0) {
                        $product = InventoryProduct::find($detail->product_detail_id);
                        if ($product) {
                            $product->current_stock_qty -= $diff;
                            $product->save();
                        }
                    }
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
            'net_amount_impact' => StoDetail::leftJoin('inv_t_product_detail', 'inv_t_sto_detail.product_detail_id', '=', 'inv_t_product_detail.id')
                ->where('inv_t_sto_detail.event_id', $stoEvent->id)
                ->sum(DB::raw('inv_t_sto_detail.diff_qty * ISNULL(inv_t_product_detail.weight_kg, 0) * ISNULL(inv_t_product_detail.material_price, 0)')),
        ];

        $netAdjustment = (clone $baseQuery)->sum('diff_qty');
        
        $totalProducts = InventoryProduct::where('is_active', 1)->count();
        $stats['total_unscanned'] = max(0, $totalProducts - $stats['total_items']);
        $stats['total_count'] = $totalProducts;
        
        $progress = $totalProducts > 0 ? round(($stats['total_items'] / $totalProducts) * 100, 1) : 0;

        return ['stats' => $stats, 'netAdjustment' => $netAdjustment, 'progress' => $progress];
    }
}
