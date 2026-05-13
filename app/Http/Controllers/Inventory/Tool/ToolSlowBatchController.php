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
                    $q->where('batch_no', 'like', "%$search%")
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
                $assetValue = $row->status === 'active'
                    ? round($row->qty_current * $row->purchase_price * ($remainYrs / $row->std_lifetime_yrs), 2)
                    : 0;

                return [
                    'id'               => $row->id,
                    'batch_no'         => $row->batch_no,
                    'tool_id'          => $row->tool_id,
                    'tool_name'        => $tool?->name ?? '-',
                    'brand'            => $tool?->brand ?? '-',
                    'spec_code'        => $tool?->spec_code ?? '-',
                    'category'         => $tool?->category?->name ?? '-',
                    'location'         => '',
                    'purchase_date'    => $row->purchase_date->format('d M Y'),
                    'purchase_date_raw'=> $row->purchase_date->format('Y-m-d'),
                    'location_id'      => $row->location_id,
                    'purchase_price'   => $row->purchase_price,
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
            'location_id'      => 'nullable|exists:tol_m_locations,id',
            'purchase_date'    => 'required|date',
            'purchase_price'   => 'required|numeric|min:0',
            'qty_purchased'    => 'required|integer|min:1',
            'std_lifetime_yrs' => 'required|integer|min:1',
        ]);

        if (empty($validated['location_id'])) {
            $tool = TolTool::find($validated['tool_id']);
            if (!$tool->location_id) {
                return response()->json(['status' => 'error', 'message' => 'Tool has no default location. Please set it in Master Tool.'], 422);
            }
            $validated['location_id'] = $tool->location_id;
        }

        // Auto-generate batch_no
        $tool     = TolTool::find($validated['tool_id']);
        $category = $tool->category;
        $prefix   = $category ? strtoupper(substr(str_replace(' ', '', $category->name), 0, 3)) : 'TOL';
        $year     = Carbon::parse($validated['purchase_date'])->year;
        $count    = TolSlowBatch::whereYear('purchase_date', $year)->count() + 1;
        $batchNo  = "{$prefix}-{$year}-" . str_pad($count, 3, '0', STR_PAD_LEFT);

        // Nilai aset awal = full purchase price × qty
        $initValue = $validated['purchase_price'] * $validated['qty_purchased'];

        TolSlowBatch::create([
            ...$validated,
            'batch_no'    => $batchNo,
            'qty_current' => $validated['qty_purchased'],
            'current_value' => $initValue,
            'status'      => 'active',
        ]);

        return response()->json(['status' => 'success', 'message' => "Batch {$batchNo} registered successfully."]);
    }

    /** Update data batch (sebelum ada STO) */
    public function update(Request $request, $id)
    {
        $batch = TolSlowBatch::findOrFail($id);

        if ($batch->status !== 'active') {
            return response()->json(['status' => 'error', 'message' => 'Cannot edit a NOK or retired batch.'], 422);
        }

        $validated = $request->validate([
            'purchase_date'    => 'required|date',
            'purchase_price'   => 'required|numeric|min:0',
            'std_lifetime_yrs' => 'required|integer|min:1',
            'location_id'      => 'nullable|exists:tol_m_locations,id',
        ]);

        if (empty($validated['location_id'])) {
            $toolMaster = $batch->tool;
            if ($toolMaster && $toolMaster->location_id) {
                $validated['location_id'] = $toolMaster->location_id;
            } else {
                 return response()->json(['status' => 'error', 'message' => 'Tool has no default location. Please set it in Master Tool.'], 422);
            }
        }

        $batch->update($validated);
        return response()->json(['status' => 'success', 'message' => 'Batch updated successfully.']);
    }

    /** Total nilai aset aktif (untuk laporan/dashboard) */
    public function totalAssetValue()
    {
        $total = TolSlowBatch::where('status', 'active')->sum('current_value');
        return response()->json(['total_asset_value' => $total]);
    }
}
