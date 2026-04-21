<?php

namespace App\Http\Controllers\Inventory\Tool;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\Tool\TolStoFast;
use App\Models\InventoryModel\Tool\TolFastStock;
use App\Models\InventoryModel\Tool\TolTransaction;
use App\Models\InventoryModel\Tool\TolTool;
use App\Models\InventoryModel\Tool\TolLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ToolStoFastController extends Controller
{
    /** List STO records + DataTables */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $draw   = (int) $request->input('draw');
            $start  = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = $request->input('search.value');

            $query = TolStoFast::with(['tool.category', 'location', 'conductor']);
            $recordsTotal = (clone $query)->count();

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('tool', fn($t) => $t->where('name', 'like', "%$search%"))
                      ->orWhereHas('location', fn($l) => $l->where('code', 'like', "%$search%"))
                      ->orWhere('sto_date', 'like', "%$search%")
                      ->orWhere('status', 'like', "%$search%");
                });
            }

            $recordsFiltered = (clone $query)->count();
            $data = $query->orderBy('sto_date', 'desc')->skip($start)->take($length)->get();

            $formatted = $data->map(fn($row) => [
                'id'              => $row->id,
                'sto_date'        => Carbon::parse($row->sto_date)->format('d M Y'),
                'tool_name'       => $row->tool?->name ?? '-',
                'brand'           => $row->tool?->brand ?? '-',
                'location'        => $row->location ? "{$row->location->code} — {$row->location->name}" : '-',
                'system_qty'      => $row->system_qty,
                'physical_qty'    => $row->physical_qty,
                'adjustment_qty'  => $row->adjustment_qty,
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

        $tools     = TolTool::with('category')
                        ->whereHas('category', fn($q) => $q->where('moving_type', 'fast'))
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get();
        $locations = TolLocation::where('is_active', true)->orderBy('code')->get();
        return view('inventory.tool.sto.fast', compact('tools', 'locations'));
    }

    /** Buat draft STO baru */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sto_date'     => 'required|date',
            'tool_id'      => 'required|exists:tol_m_tools,id',
            'location_id'  => 'required|exists:tol_m_locations,id',
            'physical_qty' => 'required|integer|min:0',
            'note'         => 'nullable|string',
        ]);

        // Ambil system_qty dari stok aktual
        $stock = TolFastStock::where('tool_id', $validated['tool_id'])
            ->where('location_id', $validated['location_id'])
            ->first();
        $systemQty = $stock?->current_qty ?? 0;

        $adjustmentQty = $validated['physical_qty'] - $systemQty;

        $sto = TolStoFast::create([
            'sto_date'       => $validated['sto_date'],
            'tool_id'        => $validated['tool_id'],
            'location_id'    => $validated['location_id'],
            'system_qty'     => $systemQty,
            'physical_qty'   => $validated['physical_qty'],
            'adjustment_qty' => $adjustmentQty,
            'note'           => $validated['note'] ?? null,
            'conducted_by'   => Auth::user()->id,
            'status'         => 'draft',
        ]);

        return response()->json(['status' => 'success', 'message' => 'STO draft created.', 'data' => $sto]);
    }

    /** Approve STO → update stok + buat transaksi adjustment */
    public function approve(Request $request, $id)
    {
        $sto = TolStoFast::findOrFail($id);

        if ($sto->status !== 'draft') {
            return response()->json(['status' => 'error', 'message' => 'STO is already approved.'], 422);
        }

        DB::transaction(function () use ($sto) {
            // Update stok ke hasil fisik
            $stock = TolFastStock::firstOrCreate(
                ['tool_id' => $sto->tool_id, 'location_id' => $sto->location_id],
                ['current_qty' => 0]
            );
            $stock->current_qty    = $sto->physical_qty;
            $stock->last_updated_at = now();
            $stock->save();

            // Catat adjustment transaction (jika ada selisih)
            if ($sto->adjustment_qty !== 0) {
                TolTransaction::create([
                    'tool_id'          => $sto->tool_id,
                    'location_id'      => $sto->location_id,
                    'transaction_type' => 'adjustment',
                    'qty'              => $sto->adjustment_qty,
                    'ref_doc'          => "STO-{$sto->id}",
                    'note'             => "STO Adjustment — " . ($sto->note ?? 'No notes'),
                    'transacted_by'    => Auth::user()->id,
                    'transacted_at'    => now(),
                ]);
            }

            $sto->update(['status' => 'approved', 'approved_by' => Auth::user()->id]);
        });

        return response()->json(['status' => 'success', 'message' => 'STO approved and stock updated.']);
    }

    public function destroy($id)
    {
        $sto = TolStoFast::findOrFail($id);
        if ($sto->status === 'approved') {
            return response()->json(['status' => 'error', 'message' => 'Cannot delete an approved STO.'], 422);
        }
        $sto->delete();
        return response()->json(['status' => 'success', 'message' => 'STO draft deleted.']);
    }
}
