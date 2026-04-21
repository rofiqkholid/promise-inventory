<?php

namespace App\Http\Controllers\Inventory\Tool;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\Tool\TolStoSlow;
use App\Models\InventoryModel\Tool\TolSlowBatch;
use App\Models\InventoryModel\Tool\TolLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ToolStoSlowController extends Controller
{
    /** List STO slow records */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $draw   = (int) $request->input('draw');
            $start  = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = $request->input('search.value');

            $query = TolStoSlow::with(['batch.tool.category', 'batch.location', 'conductor']);
            $recordsTotal = (clone $query)->count();

            if (!empty($search)) {
                $query->whereHas('batch', function ($q) use ($search) {
                    $q->where('batch_no', 'like', "%$search%")
                      ->orWhereHas('tool', fn($t) => $t->where('name', 'like', "%$search%"))
                      ->orWhereHas('location', fn($l) => $l->where('code', 'like', "%$search%"));
                })->orWhere('physical_check', 'like', "%$search%");
            }

            $recordsFiltered = (clone $query)->count();
            $data = $query->orderBy('sto_date', 'desc')->skip($start)->take($length)->get();

            $formatted = $data->map(fn($row) => [
                'id'              => $row->id,
                'sto_date'        => Carbon::parse($row->sto_date)->format('d M Y'),
                'batch_no'        => $row->batch?->batch_no ?? '-',
                'tool_name'       => $row->batch?->tool?->name ?? '-',
                'brand'           => $row->batch?->tool?->brand ?? '-',
                'location'        => $row->batch?->location
                    ? "{$row->batch->location->code} — {$row->batch->location->name}" : '-',
                'physical_check'  => $row->physical_check,
                'qty_checked'     => $row->qty_checked,
                'qty_ok'          => $row->qty_ok,
                'qty_nok'         => $row->qty_nok,
                'age_years'       => $row->age_years,
                'remaining_value' => $row->remaining_value,
                'note'            => $row->note,
                'conducted_by'    => $row->conductor?->name ?? '-',
                'status'          => $row->status,
                'action'          => '',
            ]);

            return response()->json([
                'draw' => $draw, 'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered, 'data' => $formatted,
            ]);
        }

        // Tampilkan batch aktif untuk dipilih saat STO
        $activeBatches = TolSlowBatch::with(['tool.category', 'location'])
            ->where('status', 'active')
            ->orderBy('batch_no')
            ->get();

        return view('inventory.tool.sto.slow', compact('activeBatches'));
    }

    /** Preview nilai aset sebelum STO disimpan */
    public function preview(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|exists:tol_t_slow_batches,id',
            'sto_date' => 'required|date',
            'qty_ok'   => 'required|integer|min:0',
            'qty_nok'  => 'required|integer|min:0',
        ]);

        $batch    = TolSlowBatch::findOrFail($request->batch_id);
        $stoDate  = Carbon::parse($request->sto_date);
        $purchase = Carbon::parse($batch->purchase_date);
        $ageYears = round($purchase->diffInDays($stoDate) / 365.25, 2);
        $remainYrs = max(0, $batch->std_lifetime_yrs - $ageYears);

        $qtyOk       = (int) $request->qty_ok;
        $assetValue  = ($remainYrs > 0)
            ? round($qtyOk * $batch->purchase_price * ($remainYrs / $batch->std_lifetime_yrs), 2)
            : 0;

        return response()->json([
            'batch_no'        => $batch->batch_no,
            'purchase_date'   => $batch->purchase_date->format('d M Y'),
            'purchase_price'  => $batch->purchase_price,
            'std_lifetime'    => $batch->std_lifetime_yrs,
            'age_years'       => $ageYears,
            'remaining_years' => round($remainYrs, 2),
            'qty_ok'          => $qtyOk,
            'qty_nok'         => (int) $request->qty_nok,
            'asset_value'     => $assetValue,
        ]);
    }

    /** Simpan STO slow (draft) */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sto_date'       => 'required|date',
            'batch_id'       => 'required|exists:tol_t_slow_batches,id',
            'physical_check' => 'required|in:ok,nok',
            'qty_checked'    => 'required|integer|min:1',
            'qty_ok'         => 'required|integer|min:0',
            'qty_nok'        => 'required|integer|min:0',
            'note'           => 'nullable|string',
        ]);

        if ((int)$validated['qty_ok'] + (int)$validated['qty_nok'] !== (int)$validated['qty_checked']) {
            return response()->json([
                'status' => 'error',
                'message' => 'qty_ok + qty_nok must equal qty_checked.',
            ], 422);
        }

        $batch    = TolSlowBatch::findOrFail($validated['batch_id']);
        $stoDate  = Carbon::parse($validated['sto_date']);
        $purchase = Carbon::parse($batch->purchase_date);
        $ageYears = round($purchase->diffInDays($stoDate) / 365.25, 2);
        $remainYrs= max(0, $batch->std_lifetime_yrs - $ageYears);

        $assetValue = ($validated['physical_check'] === 'ok' && $remainYrs > 0)
            ? round($validated['qty_ok'] * $batch->purchase_price * ($remainYrs / $batch->std_lifetime_yrs), 2)
            : 0;

        $sto = TolStoSlow::create([
            'sto_date'        => $validated['sto_date'],
            'batch_id'        => $validated['batch_id'],
            'physical_check'  => $validated['physical_check'],
            'qty_checked'     => $validated['qty_checked'],
            'qty_ok'          => $validated['qty_ok'],
            'qty_nok'         => $validated['qty_nok'],
            'age_years'       => $ageYears,
            'remaining_value' => $assetValue,
            'note'            => $validated['note'] ?? null,
            'conducted_by'    => Auth::user()->id,
            'status'          => 'draft',
        ]);

        return response()->json(['status' => 'success', 'message' => 'STO draft created.', 'data' => $sto]);
    }

    /** Approve STO → update batch asset value & status */
    public function approve(Request $request, $id)
    {
        $sto = TolStoSlow::with('batch')->findOrFail($id);

        if ($sto->status !== 'draft') {
            return response()->json(['status' => 'error', 'message' => 'STO is already approved.'], 422);
        }

        DB::transaction(function () use ($sto) {
            $batch = $sto->batch;

            // Update nilai aset & qty_current di batch
            $batch->current_value = $sto->remaining_value;
            $batch->qty_current   = $sto->qty_ok;

            // Jika semua NOK → ubah status batch menjadi nok
            if ($sto->physical_check === 'nok' || $sto->qty_ok === 0) {
                $batch->status     = 'nok';
                $batch->nok_date   = $sto->sto_date;
                $batch->nok_reason = $sto->note ?? 'Marked NOK via STO';
                $batch->nok_by     = Auth::user()->id;
                $batch->current_value = 0;
                $batch->qty_current   = 0;
            }

            $batch->save();

            $sto->update(['status' => 'approved', 'approved_by' => Auth::user()->id]);
        });

        return response()->json(['status' => 'success', 'message' => 'STO approved. Batch asset value updated.']);
    }

    public function destroy($id)
    {
        $sto = TolStoSlow::findOrFail($id);
        if ($sto->status === 'approved') {
            return response()->json(['status' => 'error', 'message' => 'Cannot delete an approved STO.'], 422);
        }
        $sto->delete();
        return response()->json(['status' => 'success', 'message' => 'STO draft deleted.']);
    }
}
