<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\StoEvent;
use App\Models\InventoryModel\StoDetail;
use App\Models\InventoryModel\InventoryProduct;
use App\Models\InventoryModel\PIC;
use App\Models\InventoryModel\InventoryTransaction;
use App\Models\InventoryModel\TransactionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\DecodesHashInputs;

class StoController extends Controller
{
    use DecodesHashInputs;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $events = StoEvent::with('pic')->orderBy('created_at', 'desc')->paginate(10);
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
        
        // Load Details with Product Info
        // Using DB query for performance if needed, but Eloquent is fine for start
        $details = StoDetail::with(['product.product']) // Nested: StoDetail -> InvProduct -> MasterProduct
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

        try {
            DB::beginTransaction();

            $stoEvent->status = 'CLOSED';
            $stoEvent->period_end = now();
            $stoEvent->save();

            // Process Adjustments
            $details = StoDetail::where('event_id', $stoEvent->id)
                ->where('is_adjusted', 0) // Only process unadjusted lines
                ->get(); 
            
            // 1. Resolve Categories (Case Insensitive & Flexible)
            $catIn = TransactionCategory::where('code', 'STO-IN')->orWhere('name', 'like', '%STO%IN%')->first();
            if (!$catIn) {
                $catIn = TransactionCategory::create(['code' => 'STO-IN', 'name' => 'STO Adjustment IN', 'effect' => 1]);
            }
            
            $catOut = TransactionCategory::where('code', 'STO-OUT')->orWhere('name', 'like', '%STO%OUT%')->first();
            if (!$catOut) {
                $catOut = TransactionCategory::create(['code' => 'STO-OUT', 'name' => 'STO Adjustment OUT', 'effect' => -1]);
            }

            $count = 0;

            foreach ($details as $detail) {
                $diff = $detail->real_qty_input - $detail->system_qty_snapshot;
                
                if ($diff == 0) continue;

                $detail->is_adjusted = true;
                $detail->save();

                $categoryId = ($diff > 0) ? $catIn->id : $catOut->id;
                $absDiff = abs($diff);

                // Create Transaction
                InventoryTransaction::create([
                    'transaction_category_id' => $categoryId,
                    'product_detail_id' => $detail->product_detail_id,
                    'pic_id' => $detail->auditor_id ?? $stoEvent->pic_id,
                    'qty' => $absDiff,
                    'date' => now(),
                    'remark' => "STO Adj (" . ($detail->remark ? $detail->remark . " - " : "") . "Event: {$stoEvent->code})"
                ]);

                // Update Master Stock
                $product = InventoryProduct::find($detail->product_detail_id);
                if ($diff > 0) {
                    $product->current_stock_qty += $absDiff;
                } else {
                    $product->current_stock_qty -= $absDiff;
                }
                $product->save();
                $count++;
            }

            DB::commit();
            return redirect()->route('inventory.sto.index')->with('success', "STO Event finalized. Stock updated for {$count} items.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error finalizing: ' . $e->getMessage());
        }
    }
}
