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

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->input('category_id'));
            }

            if ($request->filled('stock_status')) {
                $status = $request->input('stock_status');
                if ($status === 'critical') {
                    $query->whereColumn('total_qty', '<', 'qty_min');
                } elseif ($status === 'warning') {
                    $query->whereColumn('total_qty', '=', 'qty_min');
                } elseif ($status === 'safe') {
                    $query->whereColumn('total_qty', '>', 'qty_min')
                          ->where(function($q) {
                              $q->whereNull('qty_max')
                                ->orWhere('qty_max', 0)
                                ->orWhereColumn('total_qty', '<=', 'qty_max');
                          });
                } elseif ($status === 'over') {
                    $query->where('qty_max', '>', 0)
                          ->whereColumn('total_qty', '>', 'qty_max');
                }
            }

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
                7 => 'total_qty',
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
                $totalQty = $row->total_qty;
                
                // --- 1. STORAGE / RACK ---
                $storageStocks = $activeStocks->filter(fn($fs) => $fs->location?->category === 'storage');
                $storageQty = $storageStocks->sum('current_qty');
                $locationStorageHtml = '<div class="flex flex-col"><span class="text-xs text-gray-400 font-medium">0 PCS</span><span class="text-[10px] text-gray-400">-</span></div>';
                if ($storageStocks->isNotEmpty()) {
                    if ($storageStocks->count() === 1) {
                        $fs = $storageStocks->first();
                        $locCode = $fs->location?->code ?? $fs->location?->name ?? 'Unknown';
                        $locationStorageHtml = sprintf(
                            '<div class="flex flex-col"><span class="font-bold text-gray-900 dark:text-white text-xs">%d %s</span><span class="text-[10px] text-gray-500 font-medium">%s</span></div>',
                            $fs->current_qty,
                            $row->uom ?? 'PCS',
                            $locCode
                        );
                    } else {
                        $details = [];
                        foreach ($storageStocks as $fs) {
                            $details[] = [
                                'code' => $fs->location?->code ?? '?',
                                'name' => $fs->location?->name ?? '?',
                                'category' => 'storage',
                                'qty' => $fs->current_qty
                            ];
                        }
                        $locationStorageHtml = sprintf(
                            '<div class="flex flex-col"><span class="font-bold text-gray-900 dark:text-white text-xs">%d %s</span><button class="location-click-trigger text-[10px] text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 font-bold flex items-center gap-0.5 cursor-pointer bg-transparent border-0 p-0 active:scale-95 transition-all text-left" data-locations="%s" data-popup-title="Storage / Rack Locations" data-popup-icon="fa-boxes-stacked">%d Locations <i class="fa-solid fa-chevron-down text-[8px] opacity-70"></i></button></div>',
                            $storageQty,
                            $row->uom ?? 'PCS',
                            htmlspecialchars(json_encode($details), ENT_QUOTES, 'UTF-8'),
                            $storageStocks->count()
                        );
                    }
                }

                // --- 2. IN USE ---
                $useStocks = $activeStocks->filter(fn($fs) => in_array($fs->location?->category, ['machine', 'subcont', 'borrow', 'return']));
                $useQty = $useStocks->sum('current_qty');
                $locationUseHtml = '<div class="flex flex-col"><span class="text-xs text-gray-400 font-medium">0 PCS</span><span class="text-[10px] text-gray-400">-</span></div>';
                if ($useStocks->isNotEmpty()) {
                    if ($useStocks->count() === 1) {
                        $fs = $useStocks->first();
                        $locCode = $fs->location?->code ?? $fs->location?->name ?? 'Unknown';
                        $locationUseHtml = sprintf(
                            '<div class="flex flex-col"><span class="font-bold text-gray-900 dark:text-white text-xs">%d %s</span><span class="text-[10px] text-gray-500 font-medium">%s</span></div>',
                            $fs->current_qty,
                            $row->uom ?? 'PCS',
                            $locCode
                        );
                    } else {
                        $details = [];
                        foreach ($useStocks as $fs) {
                            $details[] = [
                                'code' => $fs->location?->code ?? '?',
                                'name' => $fs->location?->name ?? '?',
                                'category' => $fs->location?->category ?? 'machine',
                                'qty' => $fs->current_qty
                            ];
                        }
                        $locationUseHtml = sprintf(
                            '<div class="flex flex-col"><span class="font-bold text-gray-900 dark:text-white text-xs">%d %s</span><button class="location-click-trigger text-[10px] text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 font-bold flex items-center gap-0.5 cursor-pointer bg-transparent border-0 p-0 active:scale-95 transition-all text-left" data-locations="%s" data-popup-title="In Use / Borrowed Locations" data-popup-icon="fa-gears">%d Locations <i class="fa-solid fa-chevron-down text-[8px] opacity-70"></i></button></div>',
                            $useQty,
                            $row->uom ?? 'PCS',
                            htmlspecialchars(json_encode($details), ENT_QUOTES, 'UTF-8'),
                            $useStocks->count()
                        );
                    }
                }

                // --- 3. OUT (SCRAP & LOST) ---
                $outLocations = DB::table('tol_t_transactions as t')
                    ->join('tol_m_locations as l', 'l.id', '=', 't.to_location_id')
                    ->where('t.tool_id', $row->id)
                    ->whereIn('l.category', ['scrap', 'lost'])
                    ->select('l.code', 'l.name', 'l.category', DB::raw('ABS(SUM(t.qty)) as qty'))
                    ->groupBy('l.code', 'l.name', 'l.category')
                    ->get();

                $outQty = $outLocations->sum('qty');
                $locationOutHtml = '<div class="flex flex-col"><span class="text-xs text-gray-400 font-medium">0 PCS</span><span class="text-[10px] text-gray-400">-</span></div>';
                if ($outLocations->isNotEmpty()) {
                    if ($outLocations->count() === 1) {
                        $loc = $outLocations->first();
                        $locCode = $loc->code ?? $loc->name ?? 'Unknown';
                        $locationOutHtml = sprintf(
                            '<div class="flex flex-col"><span class="font-bold text-gray-900 dark:text-white text-xs">%d %s</span><span class="text-[10px] text-gray-500 font-medium">%s</span></div>',
                            $loc->qty,
                            $row->uom ?? 'PCS',
                            $locCode
                        );
                    } else {
                        $details = [];
                        foreach ($outLocations as $loc) {
                            $details[] = [
                                'code' => $loc->code,
                                'name' => $loc->name,
                                'category' => $loc->category,
                                'qty' => $loc->qty
                            ];
                        }
                        $locationOutHtml = sprintf(
                            '<div class="flex flex-col"><span class="font-bold text-gray-900 dark:text-white text-xs">%d %s</span><button class="location-click-trigger text-[10px] text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 font-bold flex items-center gap-0.5 cursor-pointer bg-transparent border-0 p-0 active:scale-95 transition-all text-left" data-locations="%s" data-popup-title="Scrap & Lost History" data-popup-icon="fa-trash-can">%d Locations <i class="fa-solid fa-chevron-down text-[8px] opacity-70"></i></button></div>',
                            $outQty,
                            $row->uom ?? 'PCS',
                            htmlspecialchars(json_encode($details), ENT_QUOTES, 'UTF-8'),
                            $outLocations->count()
                        );
                    }
                }

                $belowLimit = $totalQty <= ($row->qty_min ?? 0);
                $latestUpdated = $row->fastStock->max('last_updated_at');

                return [
                    'id'               => $row->id,
                    'tool_id'          => $row->id,
                    'tool_name'        => $row->name,
                    'brand'            => $row->brand ?? '-',
                    'spec_code'        => $row->spec_code ?? '-',
                    'sketch_image'     => $sketch?->image_path ? asset('storage/'.$sketch->image_path) : null,
                    'category'         => $category?->name ?? '-',
                    'moving_type'      => $category?->moving_type ?? '-',
                    'location_storage' => $locationStorageHtml,
                    'location_use'     => $locationUseHtml,
                    'location_out'     => $locationOutHtml,
                    'current_qty'      => $totalQty,
                    'qty_min'          => $row->qty_min ?? 0,
                    'qty_max'          => $row->qty_max ?? 0,
                    'uom'              => $row->uom ?? '-',
                    'below_limit'      => $belowLimit,
                    'last_updated'     => $latestUpdated ? Carbon::parse($latestUpdated)->format('d M Y H:i') : '-',
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

        $tools     = TolTool::with(['category', 'fastStock'])
                        ->whereHas('category', fn($q) => $q->where('moving_type', 'fast'))
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get();
        $locations = TolLocation::where('is_active', true)->orderBy('code')->get();
        
        // Group locations by category for easier selection (Machine, Subcont, Scrap, Lost, Storage, Return, and Borrow for OUT)
        $destinations = TolLocation::where('is_active', true)
                        ->whereIn('category', ['storage', 'machine', 'subcont', 'scrap', 'lost', 'borrow', 'return'])
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

    /** Transaksi OUT (termasuk Borrow dan Return) */
    public function out(Request $request)
    {
        $validated = $request->validate([
            'tool_id'          => 'required|exists:tol_m_tools,id',
            'location_id'      => 'nullable|exists:tol_m_locations,id', // Source location
            'to_location_id'   => 'required|exists:tol_m_locations,id', // Destination
            'qty'              => 'required|integer|min:1',
            'transaction_type' => 'nullable|string|in:out,borrow,return',
            'note'             => 'nullable|string',
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

            // Jika tujuan adalah lokasi penyimpanan aktif (storage, machine, subcont, borrow, return), tambahkan stoknya
            if (in_array($destination->category, ['storage', 'machine', 'subcont', 'borrow', 'return'])) {
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
            $transType = $validated['transaction_type'] ?? 'out';
            
            TolTransaction::create([
                'tool_id'          => $validated['tool_id'],
                'location_id'      => $validated['location_id'],
                'to_location_id'   => $validated['to_location_id'],
                'transaction_type' => $transType,
                'qty'              => -$validated['qty'],
                'note'             => $validated['note'] ?? null,
                'transacted_by'    => Auth::user()->id,
                'transacted_at'    => now(),
            ]);
        });

        $message = 'Stock OUT recorded successfully.';
        if (($validated['transaction_type'] ?? '') === 'borrow') {
            $message = 'Tool borrowing recorded successfully.';
        } elseif (($validated['transaction_type'] ?? '') === 'return') {
            $message = 'Tool return recorded successfully.';
        }

        return response()->json(['status' => 'success', 'message' => $message]);
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

            // Handle Datatables sorting dynamically
            $orderColumnIndex = $request->input('order.0.column');
            $orderDirection = $request->input('order.0.dir', 'desc');
            
            $columnsMap = [
                0 => 'transacted_at',
                2 => 'transaction_type',
                3 => 'qty',
            ];
            
            $orderBy = $columnsMap[$orderColumnIndex] ?? 'transacted_at';

            $data = $query->orderBy($orderBy, $orderDirection)->skip($start)->take($length)->get();
            
            // Transform to include qty_min and historical running stock balance for display
            $data->transform(function($item) {
                // Calculate historical global active stock at the time of this transaction.
                // Stock is only globally added on 'IN' transactions, and globally reduced on 'OUT' to 'scrap' or 'lost' locations.
                // Movements between other active locations (borrow, return, storage transfers) do not change the global active stock.
                $runningStock = DB::table('tol_t_transactions as t')
                    ->leftJoin('tol_m_locations as l', 'l.id', '=', 't.to_location_id')
                    ->where('t.tool_id', $item->tool_id)
                    ->where('t.id', '<=', $item->id)
                    ->where(function($q) {
                        $q->where('t.transaction_type', 'in')
                          ->orWhereIn('l.category', ['scrap', 'lost']);
                    })
                    ->sum('t.qty');
                
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

    /** Update an existing transaction and adjust stock accordingly */
    public function updateHistory(Request $request, $id)
    {
        if (!Auth::user()->hasMenuPermission('inventory.tool.fast-stock.index', 'edit')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'tool_id'          => 'required|exists:tol_m_tools,id',
            'location_id'      => 'nullable|exists:tol_m_locations,id',
            'to_location_id'   => 'nullable|required_if:transaction_type,out,borrow,return|exists:tol_m_locations,id',
            'qty'              => 'required|integer|min:1',
            'transaction_type' => 'required|string|in:IN,out,borrow,return',
            'ref_doc'          => 'required_if:transaction_type,IN|nullable|string|max:100',
            'note'             => 'nullable|string',
        ]);

        $transaction = TolTransaction::findOrFail($id);

        try {
            DB::transaction(function () use ($validated, $transaction) {
                // 1. SIMULATE OLD TRANSACTION STOCK REVERSAL
                $oldToolId = $transaction->tool_id;
                $oldLocationId = $transaction->location_id;
                $oldToLocationId = $transaction->to_location_id;
                $oldType = strtolower($transaction->transaction_type);
                $oldQtyVal = abs($transaction->qty);

                $affectedStocks = [];

                if ($oldType === 'in') {
                    $oldStock = TolFastStock::where('tool_id', $oldToolId)
                        ->where('location_id', $oldLocationId)
                        ->first();
                    if ($oldStock) {
                        $oldStock->current_qty -= $oldQtyVal;
                        $affectedStocks[] = $oldStock;
                    }
                } else {
                    $oldStock = TolFastStock::firstOrCreate(
                        ['tool_id' => $oldToolId, 'location_id' => $oldLocationId],
                        ['current_qty' => 0]
                    );
                    $oldStock->current_qty += $oldQtyVal;
                    $affectedStocks[] = $oldStock;

                    if ($oldToLocationId) {
                        $oldDest = TolLocation::find($oldToLocationId);
                        if ($oldDest && in_array($oldDest->category, ['storage', 'machine', 'subcont', 'borrow', 'return'])) {
                            $oldDestStock = TolFastStock::where('tool_id', $oldToolId)
                                ->where('location_id', $oldToLocationId)
                                ->first();
                            if ($oldDestStock) {
                                $oldDestStock->current_qty -= $oldQtyVal;
                                $affectedStocks[] = $oldDestStock;
                            }
                        }
                    }
                }

                // 2. SIMULATE NEW TRANSACTION STOCK APPLICATION
                $newToolId = $validated['tool_id'];
                $tool = TolTool::findOrFail($newToolId);
                $newLocationId = $validated['location_id'] ?? $tool->location_id;
                if (!$newLocationId) {
                    throw new \Exception("Tool has no default location. Please set it first.");
                }

                $newType = strtolower($validated['transaction_type']);
                $newQtyVal = (int) $validated['qty'];

                if ($newType === 'in') {
                    $newStock = null;
                    foreach ($affectedStocks as $stock) {
                        if ($stock->tool_id == $newToolId && $stock->location_id == $newLocationId) {
                            $newStock = $stock;
                            break;
                        }
                    }
                    if (!$newStock) {
                        $newStock = TolFastStock::firstOrCreate(
                            ['tool_id' => $newToolId, 'location_id' => $newLocationId],
                            ['current_qty' => 0]
                        );
                        $affectedStocks[] = $newStock;
                    }
                    $newStock->current_qty += $newQtyVal;
                    $newStock->last_updated_at = now();
                } else {
                    $newToLocationId = $validated['to_location_id'];
                    $newStock = null;
                    foreach ($affectedStocks as $stock) {
                        if ($stock->tool_id == $newToolId && $stock->location_id == $newLocationId) {
                            $newStock = $stock;
                            break;
                        }
                    }
                    if (!$newStock) {
                        $newStock = TolFastStock::firstOrCreate(
                            ['tool_id' => $newToolId, 'location_id' => $newLocationId],
                            ['current_qty' => 0]
                        );
                        $affectedStocks[] = $newStock;
                    }
                    $newStock->current_qty -= $newQtyVal;
                    $newStock->last_updated_at = now();

                    $destination = TolLocation::findOrFail($newToLocationId);
                    if (in_array($destination->category, ['storage', 'machine', 'subcont', 'borrow', 'return'])) {
                        $destStock = null;
                        foreach ($affectedStocks as $stock) {
                            if ($stock->tool_id == $newToolId && $stock->location_id == $newToLocationId) {
                                $destStock = $stock;
                                break;
                            }
                        }
                        if (!$destStock) {
                            $destStock = TolFastStock::firstOrCreate(
                                ['tool_id' => $newToolId, 'location_id' => $newToLocationId],
                                ['current_qty' => 0]
                            );
                            $affectedStocks[] = $destStock;
                        }
                        $destStock->current_qty += $newQtyVal;
                        $destStock->last_updated_at = now();
                    }
                }

                // 3. VALIDATE FINAL STOCKS ARE NOT NEGATIVE
                foreach ($affectedStocks as $stock) {
                    if ($stock->current_qty < 0) {
                        $locCode = $stock->location?->code ?? 'Unknown';
                        throw new \Exception("Cannot edit transaction. This modification would result in negative stock ({$stock->current_qty}) at location ({$locCode}).");
                    }
                }

                // 4. SAVE ALL AFFECTED STOCKS
                foreach ($affectedStocks as $stock) {
                    $stock->save();
                }

                // 5. UPDATE TRANSACTION RECORD
                $transaction->update([
                    'tool_id'          => $newToolId,
                    'location_id'      => $newLocationId,
                    'to_location_id'   => ($newType === 'in' ? null : $validated['to_location_id']),
                    'transaction_type' => $newType,
                    'qty'              => ($newType === 'in' ? $newQtyVal : -$newQtyVal),
                    'ref_doc'          => ($newType === 'in' ? $validated['ref_doc'] : null),
                    'note'             => $validated['note'] ?? null,
                    'transacted_by'    => Auth::user()->id,
                    'transacted_at'    => now(),
                ]);
            });

            return response()->json(['status' => 'success', 'message' => 'Transaction updated and stock synchronized successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    /** Delete an existing transaction and revert stock accordingly */
    public function destroyHistory($id)
    {
        if (!Auth::user()->hasMenuPermission('inventory.tool.fast-stock.index', 'delete')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $transaction = TolTransaction::findOrFail($id);

        try {
            DB::transaction(function () use ($transaction) {
                // REVERSE OLD TRANSACTION STOCK
                $toolId = $transaction->tool_id;
                $locationId = $transaction->location_id;
                $toLocationId = $transaction->to_location_id;
                $type = strtolower($transaction->transaction_type);
                $qtyVal = abs($transaction->qty);

                if ($type === 'in') {
                    // Reversed: subtract from location_id
                    $stock = TolFastStock::where('tool_id', $toolId)
                        ->where('location_id', $locationId)
                        ->first();
                    if ($stock) {
                        $stock->current_qty -= $qtyVal;
                        if ($stock->current_qty < 0) {
                            $locCode = $stock->location?->code ?? 'Unknown';
                            throw new \Exception("Cannot delete transaction. Reversing it would make stock at location ({$locCode}) negative ({$stock->current_qty}).");
                        }
                        $stock->save();
                    }
                } else {
                    // Reversed: add back to source location_id, subtract from to_location_id
                    $stock = TolFastStock::firstOrCreate(
                        ['tool_id' => $toolId, 'location_id' => $locationId],
                        ['current_qty' => 0]
                    );
                    $stock->current_qty += $qtyVal;
                    $stock->save();

                    if ($toLocationId) {
                        $dest = TolLocation::find($toLocationId);
                        if ($dest && in_array($dest->category, ['storage', 'machine', 'subcont', 'borrow', 'return'])) {
                            $destStock = TolFastStock::where('tool_id', $toolId)
                                ->where('location_id', $toLocationId)
                                ->first();
                            if ($destStock) {
                                $destStock->current_qty -= $qtyVal;
                                if ($destStock->current_qty < 0) {
                                    throw new \Exception("Cannot delete transaction. Reversing it would make stock at destination location ({$dest->code}) negative ({$destStock->current_qty}).");
                                }
                                $destStock->save();
                            }
                        }
                    }
                }

                // Delete the record
                $transaction->delete();
            });

            return response()->json(['status' => 'success', 'message' => 'Transaction deleted and stock synchronized successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}
