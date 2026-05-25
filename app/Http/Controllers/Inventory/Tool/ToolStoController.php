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

            $formatted = $data->map(fn($r, $key) => [
                'DT_RowIndex' => $start + $key + 1,
                'id'          => $r->id,
                'code'        => "<span class='font-mono font-bold text-primary-600'>{$r->code}</span>",
                'name'        => $r->name,
                'period'      => Carbon::parse($r->period_start)->format('d M Y') . ($r->period_end ? ' — ' . Carbon::parse($r->period_end)->format('d M Y') : ''),
                'status'      => (function($s) {
                    $step1 = '<span class="w-1.5 h-1.5 rounded-full bg-slate-350 dark:bg-gray-700"></span><span class="text-[10px] font-medium text-slate-400 dark:text-slate-500">Draft</span>';
                    $step2 = '<span class="w-1.5 h-1.5 rounded-full bg-slate-350 dark:bg-gray-700"></span><span class="text-[10px] font-medium text-slate-400 dark:text-slate-500">Pending</span>';
                    $step3 = '<span class="w-1.5 h-1.5 rounded-full bg-slate-350 dark:bg-gray-700"></span><span class="text-[10px] font-medium text-slate-400 dark:text-slate-500">Approved</span>';
                    $sep = '<i class="fa-solid fa-chevron-right text-[7px] text-slate-300 dark:text-gray-800 mx-1"></i>';

                    if ($s === 'draft') {
                        $step1 = '<span class="w-1.5 h-1.5 rounded-full bg-primary-500 animate-pulse"></span><span class="text-[10px] font-bold text-primary-600 dark:text-primary-400 uppercase tracking-tight">Draft</span>';
                    } elseif ($s === 'submitted') {
                        $step1 = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span><span class="text-[10px] font-medium text-slate-700 dark:text-slate-300">Draft</span>';
                        $step2 = '<span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span><span class="text-[10px] font-bold text-blue-600 dark:text-blue-450 uppercase tracking-tight">Pending</span>';
                    } elseif ($s === 'approved') {
                        $step1 = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span><span class="text-[10px] font-medium text-slate-700 dark:text-slate-300">Draft</span>';
                        $step2 = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span><span class="text-[10px] font-medium text-slate-700 dark:text-slate-300">Pending</span>';
                        $step3 = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span><span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-tight">Approved</span>';
                    } elseif ($s === 'rejected') {
                        $step1 = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span><span class="text-[10px] font-medium text-slate-700 dark:text-slate-300">Draft</span>';
                        $step2 = '<span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span><span class="text-[10px] font-bold text-rose-600 dark:text-rose-400 uppercase tracking-tight">Rejected</span>';
                        return "<div class='flex items-center justify-center gap-1.5 py-1 px-3.5 bg-rose-50/50 dark:bg-rose-950/10 border border-rose-100 dark:border-rose-900/30 rounded-full max-w-max mx-auto shadow-3xs'>{$step1}{$sep}{$step2}</div>";
                    }

                    return "<div class='flex items-center justify-center gap-1.5 py-1 px-3.5 bg-slate-50/50 dark:bg-gray-800/10 border border-slate-100 dark:border-gray-800/80 rounded-full max-w-max mx-auto shadow-3xs'>{$step1}{$sep}{$step2}{$sep}{$step3}</div>";
                })($r->status),
                'items_count' => ($r->fast_details_count + $r->slow_details_count) . " Items",
                'creator'     => '
                    <div class="font-bold text-gray-900 dark:text-white">' . ($r->creator?->name ?? '-') . '</div>
                    <div class="text-[10px] text-gray-400 font-medium">' . Carbon::parse($r->created_at)->format('d M Y H:i') . '</div>
                ',
                'action'      => (function($eventRow) {
                    if ($eventRow->status === 'draft') {
                        return '
                            <div class="flex justify-center gap-1.5 action-cell">
                                <button type="button" 
                                    class="edit-event w-7 h-7 flex items-center justify-center rounded-xs bg-amber-50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/40 text-amber-600 dark:text-amber-400 hover:bg-amber-600 dark:hover:bg-amber-600 hover:text-white dark:hover:text-white transition-all shadow-3xs cursor-pointer" 
                                    data-id="'.$eventRow->id.'"
                                    data-code="'.e($eventRow->code).'"
                                    data-period="'.($eventRow->period_start ? Carbon::parse($eventRow->period_start)->format('Y-m-d') : '').'"
                                    data-description="'.e($eventRow->description).'"
                                    title="Edit STO Event"><i class="fa-solid fa-pencil text-[10px]"></i></button>
                                <button type="button" 
                                    class="delete-event w-7 h-7 flex items-center justify-center rounded-xs bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/40 text-rose-600 dark:text-rose-400 hover:bg-rose-600 dark:hover:bg-rose-600 hover:text-white dark:hover:text-white transition-all shadow-3xs cursor-pointer" 
                                    data-id="'.$eventRow->id.'"
                                    title="Hapus STO Event"><i class="fa-solid fa-trash-can text-[10px]"></i></button>
                            </div>';
                    } else {
                        return '
                            <div class="flex justify-center gap-1.5 action-cell">
                                <button type="button" 
                                    class="w-7 h-7 flex items-center justify-center rounded-xs bg-slate-50 dark:bg-gray-800/40 border border-slate-200 dark:border-gray-850 text-slate-350 dark:text-gray-650 opacity-60 cursor-not-allowed" 
                                    disabled
                                    title="STO Event locked"><i class="fa-solid fa-pencil text-[10px]"></i></button>
                                <button type="button" 
                                    class="w-7 h-7 flex items-center justify-center rounded-xs bg-slate-50 dark:bg-gray-800/40 border border-slate-200 dark:border-gray-850 text-slate-350 dark:text-gray-650 opacity-60 cursor-not-allowed" 
                                    disabled
                                    title="STO Event locked"><i class="fa-solid fa-trash-can text-[10px]"></i></button>
                            </div>';
                    }
                })($r)
            ]);

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $formatted
            ]);
        }

        $stats = [
            'total'     => TolStoEvent::count(),
            'draft'     => TolStoEvent::where('status', 'draft')->count(),
            'submitted' => TolStoEvent::where('status', 'submitted')->count(),
            'approved'  => TolStoEvent::where('status', 'approved')->count(),
        ];

        return view('inventory.tool.sto.index', compact('stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
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
            'name'         => $code,
            'period_start' => $validated['period_start'],
            'status'       => 'draft',
            'user_id'      => Auth::user()->id,
            'description'  => $validated['description'] ?? null,
        ]);

        return response()->json(['status' => 'success', 'message' => 'STO Event created.', 'redirect' => route('inventory.tool.sto.show', $event->id)]);
    }

    public function updateEvent(Request $request, $id)
    {
        $event = TolStoEvent::findOrFail($id);
        if ($event->status !== 'draft') {
            return response()->json(['status' => 'error', 'message' => 'Hanya STO Event berstatus Draft yang dapat diubah.'], 422);
        }

        $validated = $request->validate([
            'period_start' => 'required|date',
            'description'  => 'nullable|string',
        ]);

        $event->update([
            'period_start' => $validated['period_start'],
            'description'  => $validated['description'] ?? null,
        ]);

        return response()->json(['status' => 'success', 'message' => 'STO Event header berhasil diperbarui.']);
    }

    public function deleteEvent($id)
    {
        $event = TolStoEvent::findOrFail($id);
        if ($event->status !== 'draft') {
            return response()->json(['status' => 'error', 'message' => 'Hanya STO Event berstatus Draft yang dapat dihapus.'], 422);
        }

        $event->fastDetails()->delete();
        $event->slowDetails()->delete();
        $event->delete();

        return response()->json(['status' => 'success', 'message' => 'STO Event beserta seluruh data detailnya berhasil dihapus.']);
    }

    public function show($id)
    {
        $event = TolStoEvent::with(['creator', 'approver', 'fastDetails.tool', 'fastDetails.location', 'slowDetails.batch.tool'])->findOrFail($id);
        
        $fastTools = TolTool::with(['category', 'fastStock'])
            ->whereHas('category', fn($q) => $q->where('moving_type', 'fast'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $slowBatches = TolSlowBatch::with(['tool', 'location'])->whereIn('status', ['active', 'nok'])->get();
        $locations = TolLocation::where('is_active', true)->orderBy('code')->get();

        $countedFastKeys = $event->fastDetails->map(function($detail) {
            return $detail->tool_id . '-' . $detail->location_id;
        })->toArray();

        $countedSlowBatchIds = $event->slowDetails->pluck('batch_id')->toArray();

        return view('inventory.tool.sto.show', compact('event', 'fastTools', 'slowBatches', 'locations', 'countedFastKeys', 'countedSlowBatchIds'));
    }

    public function addItemFast(Request $request, $id)
    {
        $event = TolStoEvent::findOrFail($id);
        if ($event->status !== 'draft') return response()->json(['status' => 'error', 'message' => 'Cannot modify a non-draft STO.'], 422);

        $validated = $request->validate([
            'item_id'      => 'nullable|exists:tol_t_sto_fast,id',
            'tool_id'      => 'required|exists:tol_m_tools,id',
            'location_id'  => 'required|exists:tol_m_locations,id',
            'physical_qty' => 'required|integer|min:0',
            'note'         => 'nullable|string',
        ]);

        $stock = TolFastStock::where('tool_id', $validated['tool_id'])
            ->where('location_id', $validated['location_id'])
            ->first();
        $systemQty = $stock?->current_qty ?? 0;

        // Check duplicate
        $duplicateQuery = TolStoFast::where('event_id', $event->id)
            ->where('tool_id', $validated['tool_id'])
            ->where('location_id', $validated['location_id']);

        if (!empty($validated['item_id'])) {
            $duplicateQuery->where('id', '!=', $validated['item_id']);
        }

        if ($duplicateQuery->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item Fast Moving dengan lokasi yang sama sudah terdaftar di STO ini!'
            ], 422);
        }

        if (!empty($validated['item_id'])) {
            $item = TolStoFast::findOrFail($validated['item_id']);
            $item->update([
                'tool_id'        => $validated['tool_id'],
                'location_id'    => $validated['location_id'],
                'system_qty'     => $systemQty,
                'physical_qty'   => $validated['physical_qty'],
                'adjustment_qty' => $validated['physical_qty'] - $systemQty,
                'note'           => $validated['note'] ?? null,
            ]);
            return response()->json(['status' => 'success', 'message' => 'Item updated.']);
        }

        TolStoFast::create([
            'event_id'       => $event->id,
            'tool_id'        => $validated['tool_id'],
            'location_id'    => $validated['location_id'],
            'system_qty'     => $systemQty,
            'physical_qty'   => $validated['physical_qty'],
            'adjustment_qty' => $validated['physical_qty'] - $systemQty,
            'note'           => $validated['note'] ?? null,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Item added to STO list.']);
    }

    public function addItemSlow(Request $request, $id)
    {
        $event = TolStoEvent::findOrFail($id);
        if ($event->status !== 'draft') return response()->json(['status' => 'error', 'message' => 'Cannot modify a non-draft STO.'], 422);

        $validated = $request->validate([
            'item_id'        => 'nullable|exists:tol_t_sto_slow,id',
            'batch_id'       => 'required|exists:tol_t_slow_batches,id',
            'physical_check' => 'required|in:ok,nok',
            'physical_rate'  => 'required|numeric|min:0|max:100',
            'note'           => 'nullable|string',
        ]);

        $batch = TolSlowBatch::findOrFail($validated['batch_id']);
        
        // Accurate age calculation
        $purchase = Carbon::parse($batch->purchase_date);
        $ageYears = round($purchase->diffInDays(now()) / 365.25, 2);
        $remainYrs = max(0, $batch->std_lifetime_yrs - $ageYears);
        
        // Live Value calculation: Price * (Remain/Total) * (Rate/100)
        $depFactor = $remainYrs / max(1, $batch->std_lifetime_yrs);
        $physFactor = $validated['physical_rate'] / 100;
        $remainingValue = round($batch->purchase_price * $depFactor * $physFactor, 2);

        // Check duplicate
        $duplicateQuery = TolStoSlow::where('event_id', $event->id)
            ->where('batch_id', $validated['batch_id']);

        if (!empty($validated['item_id'])) {
            $duplicateQuery->where('id', '!=', $validated['item_id']);
        }

        if ($duplicateQuery->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Asset (Batch) Slow Moving ini sudah terdaftar di STO ini!'
            ], 422);
        }

        if (!empty($validated['item_id'])) {
            $item = TolStoSlow::findOrFail($validated['item_id']);
            $item->update([
                'batch_id'        => $validated['batch_id'],
                'physical_check'  => $validated['physical_check'],
                'physical_rate'   => $validated['physical_rate'],
                'age_years'       => $ageYears,
                'remaining_value' => $remainingValue,
                'note'            => $validated['note'] ?? null,
            ]);
            return response()->json(['status' => 'success', 'message' => 'Asset details updated.']);
        }

        TolStoSlow::create([
            'event_id'        => $event->id,
            'batch_id'        => $validated['batch_id'],
            'physical_check'  => $validated['physical_check'],
            'physical_rate'   => $validated['physical_rate'],
            'age_years'       => $ageYears,
            'remaining_value' => $remainingValue,
            'note'            => $validated['note'] ?? null,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Asset added to STO list.']);
    }

    public function deleteItemFast(Request $request, $id, $itemId)
    {
        $event = TolStoEvent::findOrFail($id);
        if ($event->status !== 'draft') return response()->json(['status' => 'error', 'message' => 'Cannot modify a non-draft STO.'], 422);

        $item = TolStoFast::where('event_id', $event->id)->where('id', $itemId)->firstOrFail();
        $item->delete();

        return response()->json(['status' => 'success', 'message' => 'Item deleted from STO list.']);
    }

    public function deleteItemSlow(Request $request, $id, $itemId)
    {
        $event = TolStoEvent::findOrFail($id);
        if ($event->status !== 'draft') return response()->json(['status' => 'error', 'message' => 'Cannot modify a non-draft STO.'], 422);

        $item = TolStoSlow::where('event_id', $event->id)->where('id', $itemId)->firstOrFail();
        $item->delete();

        return response()->json(['status' => 'success', 'message' => 'Asset deleted from STO list.']);
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
                $batch->physical_rate = $detail->physical_rate;
                
                if ($detail->physical_check === 'nok') {
                    $batch->status = 'nok';
                    $batch->nok_date = now();
                    $batch->nok_reason = $detail->note;
                    $batch->nok_by = Auth::user()->id;
                } else {
                    $batch->status = 'active'; // Reset to active if OK during STO
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

    public function reopen($id)
    {
        $event = TolStoEvent::with(['fastDetails', 'slowDetails'])->findOrFail($id);
        
        if (!in_array($event->status, ['approved', 'submitted'])) {
            return response()->json(['status' => 'error', 'message' => 'Hanya STO berstatus Approved atau Submitted yang dapat di-reopen.'], 422);
        }

        DB::transaction(function() use ($event) {
            // Only rollback database mutations if it was Approved
            if ($event->status === 'approved') {
                // Rollback Fast Details
                foreach ($event->fastDetails as $detail) {
                    $stock = TolFastStock::where('tool_id', $detail->tool_id)
                        ->where('location_id', $detail->location_id)
                        ->first();
                    if ($stock) {
                        $stock->current_qty = $detail->system_qty;
                        $stock->last_updated_at = now();
                        $stock->save();
                    }

                    // Delete the adjustment transaction log created during approval
                    TolTransaction::where('ref_doc', $event->code)
                        ->where('tool_id', $detail->tool_id)
                        ->where('location_id', $detail->location_id)
                        ->where('transaction_type', 'adjustment')
                        ->delete();
                }

                // Rollback Slow Details (Batches)
                foreach ($event->slowDetails as $detail) {
                    $batch = $detail->batch;
                    if ($batch) {
                        $batch->status = 'active';
                        $batch->nok_date = null;
                        $batch->nok_reason = null;
                        $batch->nok_by = null;
                        $batch->save();
                    }
                }
            }

            // Reset STO Event status back to draft
            $event->update([
                'status'         => 'draft',
                'approved_by'    => null,
                'approved_at'    => null,
                'rejection_note' => null,
                'period_end'     => null,
            ]);
        });

        return response()->json(['status' => 'success', 'message' => 'STO berhasil di-rollback ke status Draft.']);
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

    public function getCurrentStock(Request $request)
    {
        $validated = $request->validate([
            'tool_id'     => 'required|exists:tol_m_tools,id',
            'location_id' => 'required|exists:tol_m_locations,id',
        ]);

        $stock = TolFastStock::where('tool_id', $validated['tool_id'])
            ->where('location_id', $validated['location_id'])
            ->first();

        return response()->json([
            'status'     => 'success',
            'system_qty' => $stock?->current_qty ?? 0
        ]);
    }

    public function previewCode(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date'))->format('dmY') : date('dmY');
        $count = TolStoEvent::count() + 1;
        $incStr = str_pad($count, 4, '0', STR_PAD_LEFT);
        return response()->json(['code' => "SAI/STO-TOOL/{$date}/{$incStr}"]);
    }
}
