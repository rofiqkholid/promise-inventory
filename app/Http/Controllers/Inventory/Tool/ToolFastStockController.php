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

            $query = TolTool::with(['category', 'sketch', 'fastStock.location'])
                ->whereHas('category', fn($q) => $q->where('moving_type', 'fast'))
                ->where('is_active', true);

            $recordsTotal = (clone $query)->count();

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('brand', 'like', "%$search%")
                      ->orWhere('spec_code', 'like', "%$search%")
                      ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%$search%"))
                      ->orWhereHas('fastStock.location', fn($l) => $l->where('name', 'like', "%$search%")
                        ->orWhere('code', 'like', "%$search%"));
                });
            }

            $recordsFiltered = (clone $query)->count();

            // Handle Datatables sorting
            $orderColumnIndex = $request->input('order.0.column');
            $orderDirection = $request->input('order.0.dir', 'asc');
            
            $columnsMap = [
                1 => 'category_id',
                3 => 'name',
                4 => 'brand',
                5 => 'spec_code',
                8 => 'qty_min',
                9 => 'qty_max',
            ];
            
            $orderBy = $columnsMap[$orderColumnIndex] ?? 'name';
            $data = $query->orderBy($orderBy, $orderDirection)->skip($start)->take($length)->get();

            $formatted = $data->map(function ($row) {
                $category = $row->category;
                $sketch = $row->sketch;
                
                // Get all active stocks (current_qty > 0)
                $activeStocks = $row->fastStock->filter(fn($fs) => $fs->current_qty > 0);
                
                // Sum total current quantity across all active locations
                $totalQty = $activeStocks->sum('current_qty');
                
                // Build a premium visual badge list for locations
                $locationHtml = '';
                if ($activeStocks->isEmpty()) {
                    $locationHtml = '<span class="text-xs text-gray-400 italic font-normal">No Stock</span>';
                } else {
                    $locationHtml = '<div class="flex flex-wrap gap-1.5">';
                    foreach ($activeStocks as $fs) {
                        $locName = $fs->location?->name ?? 'Unknown';
                        $locCode = $fs->location?->code ?? $locName;
                        $locCategory = $fs->location?->category ?? 'storage';
                        
                        // Premium badge color system based on location category
                        $badgeColor = 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800/30';
                        if ($locCategory === 'machine') {
                            $badgeColor = 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-400 dark:border-purple-800/30';
                        } elseif ($locCategory === 'subcont') {
                            $badgeColor = 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-900/20 dark:text-orange-400 dark:border-orange-800/30';
                        }
                        
                        $locationHtml .= sprintf(
                            '<span class="inline-flex items-center px-1.5 py-0.5 rounded-xs text-[10px] font-bold border %s" title="%s">%s: <strong class="ml-1 font-mono text-[10px]">%d</strong></span>',
                            $badgeColor,
                            strtoupper($locCategory),
                            $locCode,
                            $fs->current_qty
                        );
                    }
                    $locationHtml .= '</div>';
                }

                $belowLimit = $totalQty <= ($row->qty_min ?? 0);
                $latestUpdated = $row->fastStock->max('last_updated_at');

                return [
                    'id'           => $row->id,
                    'tool_id'      => $row->id,
                    'tool_name'    => $row->name,
                    'brand'        => $row->brand ?? '-',
                    'spec_code'    => $row->spec_code ?? '-',
                    'sketch_image' => $sketch?->image_path ? asset('storage/'.$sketch->image_path) : null,
                    'category'     => $category?->name ?? '-',
                    'moving_type'  => $category?->moving_type ?? '-',
                    'location'     => $locationHtml,
                    'current_qty'  => $totalQty,
                    'qty_min'      => $row->qty_min ?? 0,
                    'qty_max'      => $row->qty_max ?? 0,
                    'uom'          => $row->uom ?? '-',
                    'below_limit'  => $belowLimit,
                    'last_updated' => $latestUpdated ? Carbon::parse($latestUpdated)->format('d M Y H:i') : '-',
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
        
        // Group locations by category for easier selection (Machine, Subcont, Scrap, and Lost for OUT)
        $destinations = TolLocation::where('is_active', true)
                        ->whereIn('category', ['machine', 'subcont', 'scrap', 'lost'])
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
        
        // Prioritaskan location_id manual dari input user jika dikirimkan
        $selectedLocationId = $validated['location_id'] ?? $tool->location_id;
        
        if (!$selectedLocationId) {
            return response()->json(['status' => 'error', 'message' => 'Please select a location or set a default location in Master Tool.'], 422);
        }
        $validated['location_id'] = $selectedLocationId;

        DB::transaction(function () use ($validated, $tool) {
            // Jika tool di master belum memiliki default location, set otomatis ke lokasi transaksi ini
            if (!$tool->location_id) {
                $tool->location_id = $validated['location_id'];
                $tool->save();
            }

            $stock = TolFastStock::firstOrCreate(
                ['tool_id' => $validated['tool_id'], 'location_id' => $validated['location_id']],
                ['current_qty' => 0]
            );

            $stock->current_qty   += $validated['qty'];
            
            // Auto-cleanup Action Plan if stock goes above warning threshold
            if ($tool) {
                $qtyMin = $tool->qty_min ?? 0;
                $limitStock = ($qtyMin > 0 ? $qtyMin * 1.5 : 5);
                if ($stock->current_qty > $limitStock) {
                    $stock->action_status = null;
                    $stock->action_remark = null;
                }
            }

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
            'location_id'    => 'nullable|exists:tol_m_locations,id', // Source location
            'to_location_id' => 'required|exists:tol_m_locations,id', // Destination
            'qty'            => 'required|integer|min:1',
            'note'           => 'nullable|string',
        ]);

        $tool = TolTool::findOrFail($validated['tool_id']);
        $sourceLocationId = $validated['location_id'] ?? $tool->location_id;
        if (!$sourceLocationId) {
            return response()->json(['status' => 'error', 'message' => 'Tool has no default location. Please set it in Master Tool.'], 422);
        }
        $validated['location_id'] = $sourceLocationId;

        $stock = TolFastStock::where('tool_id', $validated['tool_id'])
            ->where('location_id', $validated['location_id'])
            ->first();

        if (!$stock || $stock->current_qty < $validated['qty']) {
            $currentQty = $stock ? $stock->current_qty : 0;
            return response()->json([
                'status'  => 'error',
                'message' => "Insufficient stock at selected source. Current: {$currentQty}, Requested: {$validated['qty']}",
            ], 422);
        }

        DB::transaction(function () use ($validated, $stock) {
            // 1. Kurangi dari lokasi asal
            $stock->current_qty   -= $validated['qty'];
            $stock->last_updated_at = now();
            $stock->save();

            // 2. Cek kategori lokasi tujuan
            $destination = TolLocation::findOrFail($validated['to_location_id']);

            // Jika tujuan adalah lokasi penyimpanan aktif (storage, machine, subcont), tambahkan stoknya
            if (in_array($destination->category, ['storage', 'machine', 'subcont'])) {
                $destStock = TolFastStock::firstOrCreate(
                    [
                        'tool_id'     => $validated['tool_id'],
                        'location_id' => $validated['to_location_id']
                    ],
                    ['current_qty' => 0]
                );
                $destStock->current_qty   += $validated['qty'];
                $destStock->last_updated_at = now();
                $destStock->save();
            }

            // 3. Catat riwayat transaksi
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
            ->when($searchTool, fn($q) => $q->whereHas('tool', fn($t) => $t->where('name', 'like', "%$searchTool%")));

        if ($request->ajax()) {
            $draw   = (int) $request->input('draw', 1);
            $start  = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = $request->input('search.value');

            $recordsTotal = (clone $query)->count();

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->whereHas('tool', fn($t) => $t->where('name', 'like', "%$search%")->orWhere('brand', 'like', "%$search%"))
                      ->orWhere('ref_doc', 'like', "%$search%")
                      ->orWhere('note', 'like', "%$search%")
                      ->orWhereHas('operator', fn($u) => $u->where('name', 'like', "%$search%"));
                });
            }

            $recordsFiltered = (clone $query)->count();

            $data = $query->orderBy('transacted_at', 'desc')->skip($start)->take($length)->get();
            
            // Transform to include qty_min and historical running stock balance for display
            $data->transform(function($item) {
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

            return response()->json([
                'draw'            => $draw,
                'recordsTotal'    => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data'            => $data,
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'AJAX only.'], 400);
    }
}
