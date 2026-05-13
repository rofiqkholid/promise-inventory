<?php

namespace App\Http\Controllers\Inventory\Tool;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\Tool\TolSlowBatch;
use App\Models\InventoryModel\Tool\TolTool;
use App\Models\InventoryModel\Tool\TolLocation;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ToolSlowBatchController extends Controller
{
    /** List semua batch slow moving */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $draw   = (int) $request->input('draw');
            $start  = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = $request->input('search.value');
            $status = $request->input('status', 'active'); // filter by status

            $query = TolSlowBatch::with(['tool.category', 'location']);

            if ($status !== 'all') {
                $query->where('status', $status);
            }

            $recordsTotal = (clone $query)->count();

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('id_number', 'like', "%$search%")
                      ->orWhereHas('tool', fn($t) => $t->where('name', 'like', "%$search%")
                          ->orWhere('brand', 'like', "%$search%")
                          ->orWhere('spec_code', 'like', "%$search%"))
                      ->orWhereHas('location', fn($l) => $l->where('name', 'like', "%$search%")
                          ->orWhere('code', 'like', "%$search%"));
                });
            }

            $recordsFiltered = (clone $query)->count();
            $data = $query->orderBy('purchase_date', 'desc')->skip($start)->take($length)->get();
            $today = Carbon::today();

            $formatted = $data->map(function ($row) use ($today) {
                $tool     = $row->tool;
                $purchase = Carbon::parse($row->purchase_date);
                $ageYears = round($purchase->diffInDays($today) / 365.25, 2);
                $remainYrs = max(0, $row->std_lifetime_yrs - $ageYears);
                
                // Asset Value calculation using depreciation * physical_rate
                $depFactor = $remainYrs / $row->std_lifetime_yrs;
                $physFactor = $row->physical_rate / 100;
                $assetValue = $row->status === 'active'
                    ? round($row->qty_current * $row->purchase_price * $depFactor * $physFactor, 2)
                    : 0;

                return [
                    'id'               => $row->id,
                    'id_number'        => $row->id_number,
                    'tool_id'          => $row->tool_id,
                    'tool_name'        => $tool?->name ?? '-',
                    'brand'            => $tool?->brand ?? '-',
                    'spec_code'        => $tool?->spec_code ?? '-',
                    'category'         => $tool?->category?->name ?? '-',
                    'location'         => $row->location?->name ?? '-',
                    'purchase_date'    => $row->purchase_date->format('d M Y'),
                    'purchase_date_raw'=> $row->purchase_date->format('Y-m-d'),
                    'location_id'      => $row->location_id,
                    'purchase_price'   => $row->purchase_price,
                    'physical_rate'    => $row->physical_rate,
                    'qty_purchased'    => $row->qty_purchased,
                    'qty_current'      => $row->qty_current,
                    'std_lifetime_yrs' => $row->std_lifetime_yrs,
                    'age_years'        => $ageYears,
                    'remaining_yrs'    => round($remainYrs, 2),
                    'current_value'    => $row->current_value,
                    'live_asset_value' => $assetValue,
                    'status'           => $row->status,
                    'nok_date'         => $row->nok_date?->format('d M Y'),
                    'nok_reason'       => $row->nok_reason,
                    'action'           => '',
                ];
            });

            return response()->json([
                'draw'            => $draw,
                'recordsTotal'    => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data'            => $formatted,
            ]);
        }

        $tools     = TolTool::with('category')
                        ->whereHas('category', fn($q) => $q->where('moving_type', 'slow'))
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get();
        $locations = TolLocation::where('is_active', true)->orderBy('code')->get();
        return view('inventory.tool.stock.slow', compact('tools', 'locations'));
    }

    /** Tambah batch baru */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tool_id'          => 'required|exists:tol_m_tools,id',
            'id_number'        => 'required|string|max:50|unique:tol_t_slow_batches,id_number',
            'location_id'      => 'required|exists:tol_m_locations,id',
            'purchase_date'    => 'required|date',
            'purchase_price'   => 'required|numeric|min:0',
            'physical_rate'    => 'required|numeric|min:0|max:100',
            'std_lifetime_yrs' => 'required|integer|min:1',
        ]);

        // Initial value = Price * (Rate/100)
        $initValue = round($validated['purchase_price'] * ($validated['physical_rate'] / 100), 2);

        TolSlowBatch::create([
            ...$validated,
            'qty_purchased' => 1,
            'qty_current'   => 1,
            'current_value' => $initValue,
            'status'        => 'active',
        ]);

        return response()->json(['status' => 'success', 'message' => "Item registered successfully with ID: {$validated['id_number']}"]);
    }

    /** Update data batch (sebelum ada STO) */
    public function update(Request $request, $id)
    {
        $batch = TolSlowBatch::findOrFail($id);

        if ($batch->status !== 'active') {
            return response()->json(['status' => 'error', 'message' => 'Cannot edit a NOK or retired batch.'], 422);
        }

        $validated = $request->validate([
            'id_number'        => 'required|string|max:50|unique:tol_t_slow_batches,id_number,' . $id,
            'purchase_date'    => 'required|date',
            'purchase_price'   => 'required|numeric|min:0',
            'physical_rate'    => 'required|numeric|min:0|max:100',
            'std_lifetime_yrs' => 'required|integer|min:1',
            'location_id'      => 'required|exists:tol_m_locations,id',
        ]);

        $batch->update($validated);
        return response()->json(['status' => 'success', 'message' => 'Item updated successfully.']);
    }

    /** Total nilai aset aktif (untuk laporan/dashboard) */
    public function totalAssetValue()
    {
        $total = TolSlowBatch::where('status', 'active')->sum('current_value');
        return response()->json(['total_asset_value' => $total]);
    }
}
