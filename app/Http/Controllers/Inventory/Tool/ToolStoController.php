<?php

namespace App\Http\Controllers\Inventory\Tool;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\Tool\TolStoEvent;
use App\Models\InventoryModel\Tool\TolStoFast;
use App\Models\InventoryModel\Tool\TolStoSlow;
use App\Models\InventoryModel\Tool\TolFastStock;
use App\Models\InventoryModel\Tool\TolSlowBatch;
use App\Models\InventoryModel\Tool\TolTransaction;
use App\Models\InventoryModel\Tool\TolTool;
use App\Models\InventoryModel\Tool\TolLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ToolStoController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $draw   = (int) $request->input('draw');
            $start  = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = $request->input('search.value');

            $query = TolStoEvent::with(['creator', 'approver'])
                ->withCount(['fastDetails', 'slowDetails']);
            
            $recordsTotal = (clone $query)->count();

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('code', 'like', "%$search%")
                      ->orWhere('name', 'like', "%$search%")
                      ->orWhere('status', 'like', "%$search%");
                });
            }

            $recordsFiltered = (clone $query)->count();
            $data = $query->orderBy('created_at', 'desc')->skip($start)->take($length)->get();

            $formatted = $data->map(fn($r) => [
                'DT_RowIndex' => 0, // Placeholder for DataTables.js
                'id'          => $r->id,
                'code'        => "<span class='font-mono font-bold text-primary-600'>{$r->code}</span>",
                'name'        => $r->name,
                'period'      => Carbon::parse($r->period_start)->format('d M Y') . ($r->period_end ? ' — ' . Carbon::parse($r->period_end)->format('d M Y') : ''),
                'status'      => (function($s) {
                    $cls = match($s) {
                        'draft'     => 'bg-gray-100 text-gray-700',
                        'submitted' => 'bg-blue-100 text-blue-700',
                        'approved'  => 'bg-emerald-100 text-emerald-700',
                        'rejected'  => 'bg-red-100 text-red-700',
                        default     => 'bg-gray-100 text-gray-700'
                    };
                    return "<span class='px-2 py-0.5 rounded-full text-[9px] font-bold uppercase {$cls}'>{$s}</span>";
                })($r->status),
                'items_count' => ($r->fast_details_count + $r->slow_details_count) . " Items",
                'creator'     => ['name' => $r->creator?->name ?? '-'],
                'action'      => '
                    <div class="flex justify-center gap-1">
                        <a href="'.route('inventory.tool.sto.show', $r->id).'" class="w-8 h-8 flex items-center justify-center rounded-xs bg-primary-50 text-primary-600 hover:bg-primary-600 hover:text-white transition-all"><i class="fa-solid fa-eye text-xs"></i></a>
                    </div>'
            ]);

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $formatted
            ]);
        }

        return view('inventory.tool.sto.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:150',
            'period_start' => 'required|date',
            'description'  => 'nullable|string',
        ]);

        // Generate Automated Code: SAI/STO-TOOL/DDMMYYYY/XXXX
        $dateStr = date('dmY');
        $count   = TolStoEvent::count() + 1;
        $incStr  = str_pad($count, 4, '0', STR_PAD_LEFT);
        $code    = "SAI/STO-TOOL/{$dateStr}/{$incStr}";

        $event = TolStoEvent::create([
            'code'         => $code,
            'name'         => $validated['name'],
            'period_start' => $validated['period_start'],
            'status'       => 'draft',
            'user_id'      => Auth::user()->id,
            'description'  => $validated['description'],
        ]);

        return response()->json(['status' => 'success', 'message' => 'STO Event created.', 'redirect' => route('inventory.tool.sto.show', $event->id)]);
    }

    public function show($id)
    {
        $event = TolStoEvent::with(['creator', 'approver', 'fastDetails.tool', 'fastDetails.location', 'slowDetails.batch.tool'])->findOrFail($id);
        
        $fastTools = TolTool::whereHas('category', fn($q) => $q->where('moving_type', 'fast'))->orderBy('name')->get();
        $slowBatches = TolSlowBatch::with('tool')->where('status', 'active')->get();
        $locations = TolLocation::orderBy('code')->get();

        return view('inventory.tool.sto.show', compact('event', 'fastTools', 'slowBatches', 'locations'));
    }

    public function addItemFast(Request $request, $id)
    {
        $event = TolStoEvent::findOrFail($id);
        if ($event->status !== 'draft') return response()->json(['status' => 'error', 'message' => 'Cannot modify a non-draft STO.'], 422);

        $validated = $request->validate([
            'tool_id'      => 'required|exists:tol_m_tools,id',
            'location_id'  => 'required|exists:tol_m_locations,id',
            'physical_qty' => 'required|integer|min:0',
            'note'         => 'nullable|string',
        ]);

        $stock = TolFastStock::where('tool_id', $validated['tool_id'])
            ->where('location_id', $validated['location_id'])
            ->first();
        $systemQty = $stock?->current_qty ?? 0;

        TolStoFast::create([
            'event_id'       => $event->id,
            'tool_id'        => $validated['tool_id'],
            'location_id'    => $validated['location_id'],
            'system_qty'     => $systemQty,
            'physical_qty'   => $validated['physical_qty'],
            'adjustment_qty' => $validated['physical_qty'] - $systemQty,
            'note'           => $validated['note'] ?? null,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Item added.']);
    }

    public function addItemSlow(Request $request, $id)
    {
        $event = TolStoEvent::findOrFail($id);
        if ($event->status !== 'draft') return response()->json(['status' => 'error', 'message' => 'Cannot modify a non-draft STO.'], 422);

        $validated = $request->validate([
            'batch_id'       => 'required|exists:tol_t_slow_batches,id',
            'physical_check' => 'required|in:ok,nok',
            'qty_ok'         => 'required|integer|min:0',
            'qty_nok'        => 'required|integer|min:0',
            'note'           => 'nullable|string',
        ]);

        $batch = TolSlowBatch::findOrFail($validated['batch_id']);
        
        // Simple age calculation
        $ageYears = Carbon::parse($batch->purchase_date)->diffInDays(now()) / 365;
        // Simple remaining value (linear depreciation for now)
        $remainingValue = max(0, $batch->purchase_price * (1 - ($ageYears / max(1, $batch->std_lifetime_yrs))));

        TolStoSlow::create([
            'event_id'        => $event->id,
            'batch_id'        => $validated['batch_id'],
            'physical_check'  => $validated['physical_check'],
            'qty_checked'     => $validated['qty_ok'] + $validated['qty_nok'],
            'qty_ok'          => $validated['qty_ok'],
            'qty_nok'         => $validated['qty_nok'],
            'age_years'       => round($ageYears, 2),
            'remaining_value' => round($remainingValue, 2),
            'note'            => $validated['note'] ?? null,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Batch added.']);
    }

    public function submit($id)
    {
        $event = TolStoEvent::findOrFail($id);
        $event->update(['status' => 'submitted']);
        return response()->json(['status' => 'success', 'message' => 'STO submitted for approval.']);
    }

    public function approve($id)
    {
        $event = TolStoEvent::with(['fastDetails', 'slowDetails'])->findOrFail($id);
        if ($event->status !== 'submitted') return response()->json(['status' => 'error', 'message' => 'Only submitted STOs can be approved.'], 422);

        DB::transaction(function() use ($event) {
            // Process Fast Details
            foreach ($event->fastDetails as $detail) {
                $stock = TolFastStock::firstOrCreate(
                    ['tool_id' => $detail->tool_id, 'location_id' => $detail->location_id],
                    ['current_qty' => 0]
                );
                $stock->current_qty = $detail->physical_qty;
                $stock->last_updated_at = now();
                $stock->save();

                if ($detail->adjustment_qty != 0) {
                    TolTransaction::create([
                        'tool_id'          => $detail->tool_id,
                        'location_id'      => $detail->location_id,
                        'transaction_type' => 'adjustment',
                        'qty'              => $detail->adjustment_qty,
                        'ref_doc'          => $event->code,
                        'note'             => "STO Adjustment: " . $detail->note,
                        'transacted_by'    => Auth::user()->id,
                        'transacted_at'    => now(),
                    ]);
                }
            }

            // Process Slow Details
            foreach ($event->slowDetails as $detail) {
                $batch = $detail->batch;
                $batch->qty_current = $detail->qty_ok;
                if ($detail->physical_check === 'nok') {
                    $batch->status = 'nok';
                    $batch->nok_date = now();
                    $batch->nok_reason = $detail->note;
                    $batch->nok_by = Auth::user()->id;
                }
                $batch->current_value = $detail->remaining_value;
                $batch->save();
            }

            $event->update([
                'status' => 'approved',
                'approved_by' => Auth::user()->id,
                'approved_at' => now(),
                'period_end' => now(),
            ]);
        });

        return response()->json(['status' => 'success', 'message' => 'STO approved and stock updated.']);
    }

    public function reject(Request $request, $id)
    {
        $event = TolStoEvent::findOrFail($id);
        $event->update([
            'status' => 'rejected',
            'rejection_note' => $request->input('note')
        ]);
        return response()->json(['status' => 'success', 'message' => 'STO rejected.']);
    }

    public function previewCode(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date'))->format('dmY') : date('dmY');
        $count = TolStoEvent::count() + 1;
        $incStr = str_pad($count, 4, '0', STR_PAD_LEFT);
        return response()->json(['code' => "SAI/STO-TOOL/{$date}/{$incStr}"]);
    }
}
