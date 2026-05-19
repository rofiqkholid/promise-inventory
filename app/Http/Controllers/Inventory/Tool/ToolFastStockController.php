<?php

namespace App\Http\Controllers\Inventory\Tool;

use App\Http\Controllers\Controller;
use App\Models\InventoryModel\Tool\TolFastStock;
use App\Models\InventoryModel\Tool\TolTransaction;
use App\Models\InventoryModel\Tool\TolTool;
use App\Models\InventoryModel\Tool\TolLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ToolFastStockController extends Controller
{
    /** List stok fast moving + DataTables support */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $draw   = (int) $request->input('draw');
            $start  = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = $request->input('search.value');

            $query = TolFastStock::with(['tool.category', 'tool.sketch', 'location']);

            $recordsTotal = (clone $query)->count();

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('tool', fn($t) => $t->where('name', 'like', "%$search%")
                        ->orWhere('brand', 'like', "%$search%")
                        ->orWhere('spec_code', 'like', "%$search%"))
                      ->orWhereHas('location', fn($l) => $l->where('name', 'like', "%$search%")
                        ->orWhere('code', 'like', "%$search%"));
                });
            }

            $recordsFiltered = (clone $query)->count();
            $data = $query->orderBy('created_at', 'desc')->skip($start)->take($length)->get();

            $formatted = $data->map(function ($row) {
                $tool     = $row->tool;
                $location = $row->location;
                $belowLimit = $tool && $row->current_qty <= $tool->qty_min;

                return [
                    'id'           => $row->id,
                    'tool_id'      => $row->tool_id,
                    'tool_name'    => $tool?->name ?? '-',
                    'brand'        => $tool?->brand ?? '-',
                    'spec_code'    => $tool?->spec_code ?? '-',
                    'sketch_image' => $tool?->sketch?->image_path ? asset('storage/'.$tool->sketch->image_path) : null,
                    'category'     => $tool?->category?->name ?? '-',
                    'moving_type'  => $tool?->category?->moving_type ?? '-',
                    'location_id'  => $row->location_id,
                    'location'     => $location?->name ?? '-',
                    'current_qty'  => $row->current_qty,
                    'qty_min'      => $tool?->qty_min ?? 0,
                    'qty_max'      => $tool?->qty_max ?? 0,
                    'uom'          => $tool?->uom ?? '-',
                    'below_limit'  => $belowLimit,
                    'last_updated' => $row->last_updated_at ? Carbon::parse($row->last_updated_at)->format('d M Y H:i') : '-',
                    'action'       => '',
                ];
            });

            return response()->json([
                'draw'            => $draw,
                'recordsTotal'    => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data'            => $formatted,
            ]);
        }

        $tools     = TolTool::with(['category', 'fastStock'])
                        ->whereHas('category', fn($q) => $q->where('moving_type', 'fast'))
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get();
        $locations = TolLocation::where('is_active', true)->orderBy('code')->get();
        
        // Group locations by category for easier selection (only Machine and Subcont for OUT)
        $destinations = TolLocation::where('is_active', true)
                        ->whereIn('category', ['machine', 'subcont'])
                        ->orderBy('category')
                        ->orderBy('name')
                        ->get()
                        ->groupBy('category');

        return view('inventory.tool.stock.fast', compact('tools', 'locations', 'destinations'));
    }

    /** Tambah stok awal (initial stock) */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tool_id'     => 'required|exists:tol_m_tools,id',
            'location_id' => 'nullable|exists:tol_m_locations,id',
            'qty'         => 'required|integer|min:1',
            'ref_doc'     => 'required|string|max:100',
            'note'        => 'nullable|string',
        ]);

        $tool = TolTool::findOrFail($validated['tool_id']);
        if (!$tool->location_id) {
            return response()->json(['status' => 'error', 'message' => 'Tool has no default location. Please set it in Master Tool.'], 422);
        }
        $validated['location_id'] = $tool->location_id;

        DB::transaction(function () use ($validated) {
            $stock = TolFastStock::firstOrCreate(
                ['tool_id' => $validated['tool_id'], 'location_id' => $validated['location_id']],
                ['current_qty' => 0]
            );

            $stock->current_qty   += $validated['qty'];
            $stock->last_updated_at = now();
            $stock->save();

            TolTransaction::create([
                'tool_id'          => $validated['tool_id'],
                'location_id'      => $validated['location_id'],
                'transaction_type' => 'in',
                'qty'              => $validated['qty'],
                'ref_doc'          => $validated['ref_doc'] ?? null,
                'note'             => $validated['note'] ?? 'Initial stock',
                'transacted_by'    => Auth::user()->id,
                'transacted_at'    => now(),
            ]);
        });

        return response()->json(['status' => 'success', 'message' => 'Stock added successfully.']);
    }

    /** Transaksi OUT */
    public function out(Request $request)
    {
        $validated = $request->validate([
            'tool_id'        => 'required|exists:tol_m_tools,id',
            'to_location_id' => 'required|exists:tol_m_locations,id',
            'qty'            => 'required|integer|min:1',
            'note'           => 'nullable|string',
        ]);

        $tool = TolTool::findOrFail($validated['tool_id']);
        if (!$tool->location_id) {
            return response()->json(['status' => 'error', 'message' => 'Tool has no default location. Please set it in Master Tool.'], 422);
        }
        $validated['location_id'] = $tool->location_id;

        $stock = TolFastStock::where('tool_id', $validated['tool_id'])
            ->where('location_id', $validated['location_id'])
            ->firstOrFail();

        if ($stock->current_qty < $validated['qty']) {
            return response()->json([
                'status'  => 'error',
                'message' => "Insufficient stock. Current: {$stock->current_qty}, Requested: {$validated['qty']}",
            ], 422);
        }

        DB::transaction(function () use ($validated, $stock) {
            $stock->current_qty   -= $validated['qty'];
            $stock->last_updated_at = now();
            $stock->save();

            TolTransaction::create([
                'tool_id'          => $validated['tool_id'],
                'location_id'      => $validated['location_id'],
                'to_location_id'   => $validated['to_location_id'],
                'transaction_type' => 'out',
                'qty'              => -$validated['qty'],
                'note'             => $validated['note'] ?? null,
                'transacted_by'    => Auth::user()->id,
                'transacted_at'    => now(),
            ]);
        });

        return response()->json(['status' => 'success', 'message' => 'Stock OUT recorded successfully.']);
    }

    /** Riwayat transaksi per tool+lokasi */
    public function history(Request $request)
    {
        $toolId     = $request->input('tool_id');
        $locationId = $request->input('location_id');
        $dateRange  = $request->input('date_range');
        $dateStart  = $request->input('date_start');
        $dateEnd    = $request->input('date_end');

        if ($dateRange && str_contains($dateRange, ' - ')) {
            [$start, $end] = explode(' - ', $dateRange);
            $dateStart = \Carbon\Carbon::createFromFormat('d-m-Y', $start)->format('Y-m-d');
            $dateEnd   = \Carbon\Carbon::createFromFormat('d-m-Y', $end)->format('Y-m-d');
        }
        $transType  = $request->input('transaction_type');
        $searchTool = $request->input('search_tool');

        $query = TolTransaction::with(['tool', 'location', 'destination', 'operator'])
            ->when($toolId, fn($q) => $q->where('tool_id', $toolId))
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->when($dateStart, fn($q) => $q->whereDate('transacted_at', '>=', $dateStart))
            ->when($dateEnd, fn($q) => $q->whereDate('transacted_at', '<=', $dateEnd))
            ->when($transType, fn($q) => $q->where('transaction_type', $transType))
            ->when($searchTool, fn($q) => $q->whereHas('tool', fn($t) => $t->where('name', 'like', "%$searchTool%")))
            ->orderBy('transacted_at', 'desc');

        if ($request->ajax()) {
            $data = $query->paginate(50);
            
            // Transform to include qty_min and historical running stock balance for display
            $data->getCollection()->transform(function($item) {
                // Calculate historical running stock at the time of this transaction (SUM up to this transaction ID)
                $runningStock = DB::table('tol_t_transactions')
                    ->where('tool_id', $item->tool_id)
                    ->where('location_id', $item->location_id)
                    ->where('id', '<=', $item->id)
                    ->sum('qty');
                
                $item->qty_min = $item->tool?->qty_min ?? 0;
                $item->current_stock = (int) $runningStock;
                return $item;
            });

            return response()->json($data);
        }

        return response()->json(['status' => 'error', 'message' => 'AJAX only.'], 400);
    }
}
