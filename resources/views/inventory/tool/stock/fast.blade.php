@extends('layouts.app')

@section('title', 'Fast Moving Stock')

@section('content')
@php
    $totalTools = $tools->count();
    $safeCount = 0;
    $warningCount = 0;
    $criticalCount = 0;
    $overCount = 0;

    foreach ($tools as $tool) {
        $qty = $tool->fastStock->sum('current_qty');
        $min = $tool->qty_min ?? 0;
        $max = $tool->qty_max ?? 0;

        if ($qty < $min) {
            $criticalCount++;
        } elseif ($qty == $min) {
            $warningCount++;
        } elseif ($max > 0 && $qty > $max) {
            $overCount++;
        } else {
            $safeCount++;
        }
    }

    $categories = $tools->map(fn($t) => $t->category)->filter()->unique('id');
@endphp
<div class="text-gray-900 dark:text-gray-100">

    {{-- Header --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">Fast Moving Stock</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Stock monitoring and IN/OUT transactions for fast moving tools (e.g. Endmill, Drill).</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-2 relative items-center">
            <button type="button" id="btnHistory" class="h-9 inline-flex items-center justify-center gap-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white border border-transparent rounded-xs text-[10px] font-bold uppercase tracking-widest transition-all font-sans shadow-sm active:scale-95">
                <i class="fa-solid fa-clock-rotate-left"></i> History
            </button>

            <button type="button" id="btnToggleFilter" class="h-9 inline-flex items-center justify-center gap-2 px-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-all font-sans" title="Toggle Filters">
                <i class="fa-solid fa-filter"></i> Filters
            </button>
            
            <div class="relative">
                <button type="button" id="toggleLegend" class="h-9 inline-flex items-center justify-center gap-2 px-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-all font-sans" title="Legend & Help">
                    <i class="fa-solid fa-circle-question"></i> Legend
                </button>

                {{-- Legend Popover Content --}}
                <div id="legendPopover" class="hidden absolute right-0 top-full mt-2 w-72 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 p-6 z-50 shadow-xl">
                    <h4 class="font-bold text-gray-900 dark:text-gray-100 text-xs tracking-wider mb-3 uppercase">Stock Status</h4>
                    <div class="space-y-2.5">
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 mr-2 flex-shrink-0"></span>
                            <div class="text-gray-600 dark:text-gray-300 font-medium">Critical <span class="text-gray-400 text-[10px] tracking-tighter">(Stock &lt; Min Stock)</span></div>
                        </div>
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 mr-2 flex-shrink-0"></span>
                            <div class="text-gray-600 dark:text-gray-300 font-medium">Warning <span class="text-gray-400 text-[10px] tracking-tighter">(Stock = Min Stock)</span></div>
                        </div>
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-2 flex-shrink-0"></span>
                            <div class="text-gray-600 dark:text-gray-300 font-medium">Safe <span class="text-gray-400 text-[10px] tracking-tighter">(Min Stock &lt; Stock &le; Max Stock)</span></div>
                        </div>
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 mr-2 flex-shrink-0"></span>
                            <div class="text-gray-600 dark:text-gray-300 font-medium">Over <span class="text-gray-400 text-[10px] tracking-tighter">(Stock &gt; Max Stock)</span></div>
                        </div>
                    </div>
                </div>
            </div>

            @if(Auth::user()->hasMenuPermission('inventory.tool.fast-stock.index', 'can_create'))
            <button type="button" id="btnNewTransaction" class="h-9 inline-flex items-center justify-center gap-2 px-4 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest active:scale-[0.98] transition-all font-sans">
                <i class="fa-solid fa-plus"></i> New Transaction
            </button>
            @endif
        </div>
    </div>

    {{-- Collapsible Filter Card --}}
    <div id="filterCard" class="hidden bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 p-4 mb-6 relative">
        <div class="absolute -top-2 right-44 w-0 h-0 border-l-[10px] border-l-transparent border-r-[10px] border-r-transparent border-b-[10px] border-b-slate-200 dark:border-b-gray-700"></div>
        <div class="absolute -top-[7px] right-44 w-0 h-0 border-l-[10px] border-l-transparent border-r-[10px] border-r-transparent border-b-[10px] border-b-white dark:border-b-gray-800"></div>
        
        <div class="flex flex-col xl:flex-row gap-3 xl:items-end">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 flex-1">
                <!-- Category -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Category</label>
                    <div class="w-full">
                        <select id="filter_category" class="select2 w-full">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Stock Status -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Stock Status</label>
                    <div class="w-full">
                        <select id="filter_status" class="select2 w-full">
                            <option value="">All Status</option>
                            <option value="safe">Safe</option>
                            <option value="warning">Warning</option>
                            <option value="critical">Critical</option>
                            <option value="over">Over</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex gap-2 pt-2 xl:pt-0">
                <button type="button" id="reset_filters" class="h-9 px-4 flex items-center justify-center gap-2 bg-white dark:bg-gray-800 text-slate-500 hover:text-primary-600 border border-slate-200 dark:border-gray-700 rounded-xs transition-all text-xs font-medium active:scale-95 shadow-xs">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </button>
            </div>
        </div>
    </div>

    {{-- Individual KPI Cards - Forced Single Row --}}
    <div class="flex flex-nowrap gap-3 mb-6 overflow-x-auto scrollbar-hide">
        <!-- Total -->
        <div class="flex-none w-[180px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all hover:bg-slate-50/50 dark:hover:bg-gray-700/50">
            <div class="w-10 h-10 rounded-xs bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 flex items-center justify-center text-slate-500 text-lg">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 dark:text-gray-500 tracking-tight mb-1">Total Tools</div>
                <div class="text-sm font-bold text-slate-800 dark:text-white leading-none tracking-tighter">{{ number_format($totalTools) }}</div>
            </div>
        </div>

        <!-- Safe -->
        <div class="flex-none w-[180px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all hover:bg-slate-50/50 dark:hover:bg-gray-700/50">
            <div class="w-10 h-10 rounded-xs bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-lg">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-emerald-600 dark:text-emerald-500 tracking-tight mb-1">Safe stock</div>
                <div class="text-sm font-bold text-slate-800 dark:text-white leading-none tracking-tighter">{{ number_format($safeCount) }}</div>
            </div>
        </div>

        <!-- Warning -->
        <div class="flex-none w-[180px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all hover:bg-slate-50/50 dark:hover:bg-gray-700/50">
            <div class="w-10 h-10 rounded-xs bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800/50 flex items-center justify-center text-amber-600 dark:text-amber-400 text-lg">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-amber-600 dark:text-amber-500 tracking-tight mb-1">Warning</div>
                <div class="text-sm font-bold text-slate-800 dark:text-white leading-none tracking-tighter">{{ number_format($warningCount) }}</div>
            </div>
        </div>

        <!-- Critical -->
        <div class="flex-none w-[180px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all hover:bg-slate-50/50 dark:hover:bg-gray-700/50">
            <div class="w-10 h-10 rounded-xs bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800/50 flex items-center justify-center text-red-600 dark:text-red-400 text-lg">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-red-600 dark:text-red-500 tracking-tight mb-1">Critical</div>
                <div class="text-sm font-bold text-slate-800 dark:text-white leading-none tracking-tighter">{{ number_format($criticalCount) }}</div>
            </div>
        </div>

        <!-- Over Stock -->
        <div class="flex-none w-[180px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all hover:bg-slate-50/50 dark:hover:bg-gray-700/50">
            <div class="w-10 h-10 rounded-xs bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-lg">
                <i class="fa-solid fa-arrow-trend-up"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-indigo-600 dark:text-indigo-500 tracking-tight mb-1">Over stock</div>
                <div class="text-sm font-bold text-slate-800 dark:text-white leading-none tracking-tighter">{{ number_format($overCount) }}</div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <x-table id="fastStockTable">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th class="px-4 py-4 w-12 text-center text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">No</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Category</th>
                <th class="px-4 py-4 text-center w-16 text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Sketch</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700" style="min-width: 260px;">Tool Information</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700" style="min-width: 140px;">Storage / Rack</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700" style="min-width: 140px;">In Use</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700" style="min-width: 140px;">Out (Scrap/Lost)</th>
                <th class="px-4 py-4 text-center text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Stock</th>
                <th class="px-4 py-4 text-center text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Min. Stock</th>
                <th class="px-4 py-4 text-center text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Max. Stock</th>
                <th class="px-4 py-4 text-center text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Status</th>
                <th class="px-4 py-4 text-center text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">UOM</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Last Updated</th>
                <th class="px-4 py-4 text-center w-[90px] text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

{{-- Modal: Tool Transaction (Combined IN/OUT) --}}
<div id="modal-tool-transaction" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50">
    <div class="relative w-full max-w-md transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-widest flex items-center gap-2" id="transaction-modal-title">
                <i class="fa-solid fa-right-left text-primary-500" id="transaction-icon"></i> Tool Transaction
            </h3>
            <button class="close-modal text-gray-400 hover:text-gray-500 w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100 dark:hover:bg-gray-800"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <div class="overflow-y-auto px-6 py-6 flex-1">
            <form id="formTransaction">
                @csrf
                <div class="mb-6">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Transaction Type <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <label class="relative flex items-center justify-center p-2 border border-gray-200 dark:border-gray-700 rounded-xs cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-all has-[:checked]:border-primary-600 has-[:checked]:bg-primary-50/50 dark:has-[:checked]:bg-primary-900/20">
                            <input type="radio" name="transaction_type" value="IN" class="hidden peer" checked>
                            <span class="text-[10px] font-bold text-gray-500 peer-checked:text-primary-600 dark:peer-checked:text-primary-400 uppercase tracking-wide text-center">Stock IN</span>
                        </label>
                        <label class="relative flex items-center justify-center p-2 border border-gray-200 dark:border-gray-700 rounded-xs cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-all has-[:checked]:border-red-600 has-[:checked]:bg-red-50/50 dark:has-[:checked]:bg-red-900/20">
                            <input type="radio" name="transaction_type" value="OUT" class="hidden peer">
                            <span class="text-[10px] font-bold text-gray-500 peer-checked:text-red-600 dark:peer-checked:text-red-400 uppercase tracking-wide text-center">Stock OUT</span>
                        </label>
                        <label class="relative flex items-center justify-center p-2 border border-gray-200 dark:border-gray-700 rounded-xs cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-all has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50 dark:has-[:checked]:bg-indigo-900/20">
                            <input type="radio" name="transaction_type" value="borrow" class="hidden peer">
                            <span class="text-[10px] font-bold text-gray-500 peer-checked:text-indigo-600 dark:peer-checked:text-indigo-400 uppercase tracking-wide text-center">Borrow</span>
                        </label>
                        <label class="relative flex items-center justify-center p-2 border border-gray-200 dark:border-gray-700 rounded-xs cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-all has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/50 dark:has-[:checked]:bg-emerald-900/20">
                            <input type="radio" name="transaction_type" value="return" class="hidden peer">
                            <span class="text-[10px] font-bold text-gray-500 peer-checked:text-emerald-600 dark:peer-checked:text-emerald-400 uppercase tracking-wide text-center">Return</span>
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Tool <span class="text-red-500">*</span></label>
                    <select name="tool_id" id="transToolId" required class="select2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3">
                        <option value="">-- Select Tool --</option>
                        @foreach($tools as $tool)
                            @php
                                $activeStocks = $tool->fastStock->filter(fn($fs) => $fs->current_qty > 0)->map(fn($fs) => [
                                    'location_id' => $fs->location_id,
                                    'location_code' => $fs->location?->code ?? '?',
                                    'location_name' => $fs->location?->name ?? '?',
                                    'current_qty' => $fs->current_qty
                                ])->values()->toArray();
                            @endphp
                            <option value="{{ $tool->id }}" 
                                    data-location-id="{{ $tool->location_id }}"
                                    data-stocks="{{ json_encode($activeStocks) }}">
                                {{ $tool->name }} — {{ $tool->brand }} ({{ $tool->spec_code ?? 'No Spec' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Location <span class="text-red-500">*</span></label>
                    <select name="location_id" id="transLocationId" required class="select2-modal bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                        <option value="">-- Select Location --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" data-category="{{ $loc->category }}">{{ $loc->code }} — {{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4 hidden" id="destinationGroup">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Destination <span class="text-red-500">*</span></label>
                    <select name="to_location_id" id="to_location_id" class="select2-modal bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3">
                        <option value="">-- Select Destination --</option>
                        @foreach($destinations as $category => $locs)
                            <optgroup label="{{ strtoupper($category) }}">
                                @foreach($locs as $loc)
                                    <option value="{{ $loc->id }}" data-category="{{ $loc->category }}">{{ $loc->code }} — {{ $loc->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider" id="labelQty">Quantity <span class="text-red-500">*</span></label>
                    <input type="number" name="qty" min="1" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3" placeholder="0">
                </div>
                <div class="mb-4" id="refDocGroup">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Reference Doc <span class="text-red-500">*</span></label>
                    <input type="text" name="ref_doc" id="transRefDoc" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3" placeholder="e.g. PO-2024-001">
                </div>
                <div class="mb-2">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Note</label>
                    <textarea name="note" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3" rows="2"></textarea>
                </div>
            </form>
        </div>
        <div class="border-t border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 flex gap-3">
            <button type="button" class="close-modal flex-1 px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-[10px] font-bold text-gray-600 uppercase tracking-widest hover:bg-gray-50 transition-colors">Cancel</button>
            <button type="button" id="saveTransaction" class="flex-1 px-4 py-3 bg-primary-600 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest hover:bg-primary-700 transition-all">Submit Transaction</button>
        </div>
    </div>
</div>

{{-- Modal: History --}}
<div id="modal-tool-history" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-0 md:p-4">
    <div class="relative w-full h-full md:h-[95vh] md:w-[95vw] transform overflow-hidden md:rounded-xs bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 flex flex-col shadow-2xl transition-all">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-primary-500"></i> Transaction History
            </h3>
            <button class="close-modal text-gray-400 hover:text-gray-500 w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100 dark:hover:bg-gray-800"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <div class="overflow-y-auto px-6 py-6 flex-1 custom-scrollbar">
            {{-- History Filters --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 p-4 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-xs">
                <div>
                    <label class="block mb-1 text-[9px] font-bold text-gray-500 uppercase tracking-widest">Timeline</label>
                    <div class="relative group">
                        <i class="fa-regular fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary-500 text-[10px] pointer-events-none transition-colors z-10"></i>
                        <input type="text" id="filter_date_range" readonly 
                            class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xs h-9 text-[10px] text-gray-600 dark:text-white focus:ring-0 focus:border-primary-500 cursor-pointer w-full pl-10 transition-all font-medium" 
                            placeholder="Select Date Range"
                            value="{{ date('01-m-Y') . ' - ' . date('t-m-Y') }}">
                    </div>
                </div>
                <div>
                    <label class="block mb-1 text-[9px] font-bold text-gray-500 uppercase tracking-widest">Tool Name</label>
                    <select id="filterHistToolId" class="select2-hist-filter w-full">
                        <option value="">All Tools</option>
                        @foreach($tools as $tool)
                            <option value="{{ $tool->id }}">{{ $tool->name }} — {{ $tool->brand }} ({{ $tool->spec_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-[9px] font-bold text-gray-500 uppercase tracking-widest">Type</label>
                    <select id="filterHistType" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-[10px] rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2">
                        <option value="">All Types</option>
                        <option value="in">IN (Restock)</option>
                        <option value="out">OUT (Usage)</option>
                    </select>
                </div>
            </div>

            <x-table id="historyTable" class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400">Date</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400">Tool</th>
                        <th class="px-4 py-3 text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400" style="text-align: center !important;">Type</th>
                        <th class="px-4 py-3 text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400" style="text-align: center !important;">Qty</th>
                        <th class="px-4 py-3 text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400" style="text-align: center !important;">Min Stock</th>
                        <th class="px-4 py-3 text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400" style="text-align: center !important;">Current Stock</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400">Ref / Destination</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400">Operator</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </x-table>
        </div>
    </div>
</div>

{{-- Modal: Image Preview --}}
<div id="modal-preview" class="modal-container hidden fixed inset-0 z-[110] flex items-center justify-center bg-slate-900/60 p-4">
    <div class="relative max-w-4xl w-full h-full flex items-center justify-center p-4">
        <img id="img-full" src="" class="max-w-full max-h-[90vh] object-contain rounded-xs shadow-2xl transition-all duration-300">
        <button class="close-preview absolute top-4 right-4 text-white text-3xl hover:text-red-400 hover:scale-110 active:scale-95 transition-all drop-shadow-lg" title="Close"><i class="fa-solid fa-xmark"></i></button>
    </div>
</div>
{{-- Location Tooltip Portal --}}
<div id="location-tooltip-portal" class="fixed z-[9999] bg-white dark:bg-gray-800 rounded-xs shadow-2xl border border-slate-200 dark:border-gray-700 p-3.5 w-60 text-left hidden font-sans scale-in"></div>
@endsection

@push('style')
<style>
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    
    /* Select2 Single Selection Styling Overrides for Badges */
    .select2-container .select2-selection--single {
        height: auto !important;
        min-height: 38px !important;
        display: flex !important;
        align-items: center !important;
        padding-top: 4px !important;
        padding-bottom: 4px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: normal !important;
        padding-left: 8px !important;
        padding-right: 20px !important;
        width: 100% !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        display: flex !important;
        align-items: center !important;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Location Click Popover Listener
    $(document).on('click', '.location-click-trigger', function(e) {
        e.stopPropagation();
        e.preventDefault();
        
        const el = $(this);
        let details = el.attr('data-locations');
        if (!details) return;

        try {
            if (typeof details === 'string') {
                details = JSON.parse(details);
            }
        } catch (err) {
            console.error("Failed to parse locations data:", err);
            return;
        }

        if (!Array.isArray(details) || details.length === 0) return;

        const portal = $('#location-tooltip-portal');
        // Dynamic portal settings
        const title = el.attr('data-popup-title') || 'Location Details';
        const icon = el.attr('data-popup-icon') || 'fa-map-location-dot';

        let content = `
            <h4 class="font-bold text-slate-900 dark:text-white mb-3 border-b border-slate-100 dark:border-gray-700 pb-2 text-[10px] uppercase tracking-widest flex items-center gap-1.5">
                <i class="fa-solid ${icon} text-primary-500"></i> ${title}
            </h4>
            <div class="space-y-1 max-h-[250px] overflow-y-auto custom-scrollbar">`;

        details.forEach(item => {
            let badgeColor = 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800/30';
            if (item.category === 'machine') {
                badgeColor = 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-400 dark:border-purple-800/30';
            } else if (item.category === 'subcont') {
                badgeColor = 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-900/20 dark:text-orange-400 dark:border-orange-800/30';
            } else if (item.category === 'scrap' || item.category === 'lost') {
                badgeColor = 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800/30';
            }
            
            content += `
                <div class="flex items-center justify-between py-1 border-b border-slate-50 dark:border-gray-800/40 last:border-0 gap-4">
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-xs text-[9px] font-bold border ${badgeColor}" title="${item.category.toUpperCase()}">${item.code}</span>
                    <span class="text-slate-500 dark:text-slate-400 font-medium text-[11px] truncate max-w-[120px]" title="${item.name}">${item.name}</span>
                    <span class="font-mono font-bold text-slate-800 dark:text-white text-[11px]">${item.qty} PCS</span>
                </div>`;
        });

        content += `</div>`;

        portal.html(content).removeClass('hidden').data('trigger-el', this).show();
        
        const rect = this.getBoundingClientRect();
        const tipWidth = portal.outerWidth();
        const tipHeight = portal.outerHeight();
        
        let top = rect.bottom + 5;
        let left = rect.left;

        if (top + tipHeight > window.innerHeight) top = rect.top - tipHeight - 5;
        if (left + tipWidth > window.innerWidth) left = window.innerWidth - tipWidth - 10;
        if (left < 10) left = 10;

        portal.css({
            top: top + 'px',
            left: left + 'px',
            position: 'fixed'
        });
    });

    // Close when clicking anywhere outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.location-click-trigger, #location-tooltip-portal').length) {
            $('#location-tooltip-portal').addClass('hidden').hide();
        }
    });
    const csrf    = $('meta[name="csrf-token"]').attr('content');
    const baseUrl = "{{ url('/') }}";
    const apiIn   = "{{ route('inventory.tool.fast-stock.store') }}";
    const apiOut  = "{{ route('inventory.tool.fast-stock.out') }}";
    const apiList = "{{ route('inventory.tool.fast-stock.index') }}";

    const idr = (v) => 'Rp ' + parseFloat(v).toLocaleString('id-ID');

    window.previewImg = (src) => {
        $('#img-full').attr('src', src);
        $('#modal-preview').removeClass('hidden');
    };

    let hasLow = false;

    window.fastStockTable = window.defaultDataTable('#fastStockTable', {
        serverSide: true,
        ajax: { 
            url: apiList, 
            type: 'GET',
            data: function(d) {
                d.category_id = $('#filter_category').val();
                d.stock_status = $('#filter_status').val();
            }
        },
        order: [[3, 'asc']],
        columns: [
            { data: null, orderable: false, searchable: false, render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'category', render: d => `<span class="text-xs text-gray-500">${d}</span>` },
            { 
                data: 'sketch_image', 
                className: 'text-center',
                render: d => d ? `<img src="${d}" class="h-8 w-8 object-cover mx-auto rounded-xs border border-gray-200 cursor-pointer hover:scale-150 transition-all" onclick="window.previewImg('${d}')">` : `<div class="h-8 w-8 flex items-center justify-center mx-auto bg-gray-50 border border-gray-100 text-gray-300 rounded-xs"><i class="fa-solid fa-image text-[8px]"></i></div>`
            },
            { 
                data: null,
                render: (d, t, r) => {
                    const brandStr = r.brand && r.brand !== '-' ? r.brand : 'No Brand';
                    const specStr = r.spec_code && r.spec_code !== '-' ? r.spec_code : '';
                    
                    const subText = specStr ? `${brandStr} — ${specStr}` : brandStr;
                    
                    return `
                        <div class="flex flex-col gap-0.5">
                            <span class="font-bold text-gray-900 dark:text-white text-xs">${r.tool_name}</span>
                            <span class="text-[10px] text-slate-500 dark:text-gray-400 font-medium">${subText}</span>
                        </div>`;
                }
            },
            { data: 'location_storage', render: d => d },
            { data: 'location_use', render: d => d },
            { data: 'location_out', render: d => d },
            {
                data: 'current_qty', className: 'text-center',
                render: (d, t, r) => `<span class="font-bold text-gray-900 dark:text-white">${d}</span>`
            },
            { data: 'qty_min', className: 'text-center', render: d => `<span class="text-xs text-gray-500 font-semibold">${d}</span>` },
            { data: 'qty_max', className: 'text-center', render: d => `<span class="text-xs text-gray-500 font-semibold">${d || '-'}</span>` },
            {
                data: null, className: 'text-center',
                render: (d, t, r) => {
                    const qty = parseFloat(r.current_qty || 0);
                    const min = parseFloat(r.qty_min || 0);
                    const max = parseFloat(r.qty_max || 0);
                    
                    let label = 'SAFE';
                    let cls = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400';
                    
                    if (qty < min) {
                        label = 'CRITICAL';
                        cls = 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400';
                    } else if (qty === min) {
                        label = 'WARNING';
                        cls = 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400';
                    } else if (max > 0 && qty > max) {
                        label = 'OVER';
                        cls = 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400';
                    }
                    
                    return `<span class="px-2 py-0.5 rounded-xs text-[9px] font-bold uppercase tracking-wider ${cls}">${label}</span>`;
                }
            },
            { data: 'uom', className: 'text-center', render: d => `<span class="text-xs font-mono">${d}</span>` },
            { data: 'last_updated', render: d => `<span class="text-xs text-gray-500">${d}</span>` },
            {
                data: null, orderable: false, searchable: false, className: 'text-center',
                render: (d, t, r) => `
                    <div class="flex items-center justify-center gap-1.5">
                        <button class="print-qr-btn w-8 h-8 inline-flex items-center justify-center text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 hover:bg-primary-100 dark:hover:bg-primary-900/30 rounded-xs border border-primary-100/50 dark:border-primary-800/30 transition-all active:scale-95"
                            data-tool-id="${r.tool_id}" title="Print QR Code">
                            <i class="fa-solid fa-print text-sm"></i>
                        </button>
                    </div>`
            }
        ],
        drawCallback: function() {
            // Reorder check removed as per request
        }
    });

    const showMdl = (id) => { $('.modal-container').addClass('hidden'); $(`#${id}`).removeClass('hidden'); };
    const hideMdl = (id) => { $(`#${id}`).addClass('hidden'); };
    $('.close-modal').on('click', function() { $(this).closest('.modal-container').addClass('hidden'); });
    $(document).on('click', '#modal-preview', function(e) {
        if ($(e.target).closest('#img-full').length === 0) {
            $('#modal-preview').addClass('hidden');
        }
    });
    $(document).on('click', '#modal-preview .close-preview', function(e) {
        e.stopPropagation();
        $('#modal-preview').addClass('hidden');
    });

    // Save original full locations options
    const originalLocationsHTML = $('#transLocationId').html();
    const originalDestinationsHTML = $('#to_location_id').html();

    function filterDestinations(allowedCategories, excludeLocationId) {
        const tempDiv = $('<div>').html(originalDestinationsHTML);
        tempDiv.find('option').each(function() {
            const cat = $(this).data('category');
            const val = $(this).val();
            if ((cat && !allowedCategories.includes(cat)) || (excludeLocationId && val == excludeLocationId)) {
                $(this).remove();
            }
        });
        // Clean up empty optgroups
        tempDiv.find('optgroup').each(function() {
            if ($(this).find('option').length === 0) {
                $(this).remove();
            }
        });
        
        // Destroy select2, update HTML, and re-initialize select2 to ensure it is redrawn properly
        $('#to_location_id').select2('destroy');
        $('#to_location_id').html(tempDiv.html());
        $('#to_location_id').select2({
            dropdownParent: $('#modal-tool-transaction'),
            width: '100%',
            templateResult: formatLocationState,
            templateSelection: formatLocationState
        }).trigger('change');
    }

    function updateLocationInputState() {
        const type = $('input[name="transaction_type"]:checked').val();
        const selectedTool = $('#transToolId option:selected');
        const defaultLocId = selectedTool.data('location-id');
        const stocks = selectedTool.data('stocks') || [];
        
        if (type === 'IN') {
            // Restore only storage locations
            const tempDiv = $('<div>').html(originalLocationsHTML);
            tempDiv.find('option').each(function() {
                const cat = $(this).data('category');
                if (cat && cat !== 'storage') {
                    $(this).remove();
                }
            });
            $('#transLocationId').select2('destroy');
            $('#transLocationId').html(tempDiv.html());
            $('#transLocationId').select2({
                dropdownParent: $('#modal-tool-transaction'),
                width: '100%',
                templateResult: formatLocationState,
                templateSelection: formatLocationState
            });
            
            // Untuk IN, wajib masuk ke default Storage location
            if (defaultLocId) {
                $('#transLocationId').val(defaultLocId).trigger('change');
                $('#transLocationId').addClass('bg-slate-50 dark:bg-gray-800/80 cursor-not-allowed opacity-75').prop('disabled', true);
            } else {
                $('#transLocationId').val('').trigger('change');
                $('#transLocationId').removeClass('bg-slate-50 dark:bg-gray-800/80 cursor-not-allowed opacity-75').prop('disabled', false);
            }
        } else {
            $('#transLocationId').removeClass('bg-slate-50 dark:bg-gray-800/80 cursor-not-allowed opacity-75').prop('disabled', false);
            
            let sourceOptionsHTML = '<option value="">-- Select Source Location --</option>';
            let filteredStocks = [];
            
            // Get category for each stock item based on the original locations html
            const tempLocs = $('<div>').html(originalLocationsHTML);
            stocks.forEach(item => {
                const locOpt = tempLocs.find(`option[value="${item.location_id}"]`);
                const category = locOpt.data('category') || 'storage';
                item.category = category;
            });
            
            if (type === 'OUT') {
                // Source can be any active stock location where tool exists
                filteredStocks = stocks.filter(item => ['storage', 'machine', 'subcont', 'borrow', 'return'].includes(item.category));
                
                // Destination can be machine, subcont, scrap, or lost (excluding storage, borrow, return)
                filterDestinations(['machine', 'subcont', 'scrap', 'lost']);
            } else if (type === 'borrow') {
                // Source can be any active stock location where tool exists
                filteredStocks = stocks.filter(item => ['storage', 'machine', 'subcont', 'borrow', 'return'].includes(item.category));
                
                // Destination must be borrow category
                filterDestinations(['borrow']);
            } else if (type === 'return') {
                // Source must be where the tool is borrowed (category: borrow only)
                filteredStocks = stocks.filter(item => item.category === 'borrow');
                
                // Destination must be Return
                filterDestinations(['return']);
            }
            
            if (filteredStocks.length === 0) {
                sourceOptionsHTML += '<option value="" disabled>No stock available in allowed locations</option>';
            } else {
                filteredStocks.forEach(item => {
                    sourceOptionsHTML += `<option value="${item.location_id}" data-category="${item.category}">${item.location_code} — ${item.location_name} (${item.current_qty} pcs available)</option>`;
                });
            }
            $('#transLocationId').select2('destroy');
            $('#transLocationId').html(sourceOptionsHTML);
            $('#transLocationId').select2({
                dropdownParent: $('#modal-tool-transaction'),
                width: '100%',
                templateResult: formatLocationState,
                templateSelection: formatLocationState
            }).trigger('change');
            
            // Auto-select
            if (filteredStocks.length > 0) {
                const hasDefaultInStocks = filteredStocks.some(item => item.location_id == defaultLocId);
                if (hasDefaultInStocks) {
                    $('#transLocationId').val(defaultLocId).trigger('change');
                } else {
                    $('#transLocationId').val(filteredStocks[0].location_id).trigger('change');
                }
            } else {
                $('#transLocationId').val('').trigger('change');
            }
        }
    }

    // Handle transaction type toggle UI
    $('input[name="transaction_type"]').on('change', function() {
        const type = $(this).val(); // IN, OUT, borrow, return
        
        if (type === 'IN') {
            $('#saveTransaction').removeClass('bg-red-600 hover:bg-red-700 bg-indigo-600 hover:bg-indigo-700 bg-emerald-600 hover:bg-emerald-700').addClass('bg-primary-600 hover:bg-primary-700').text('Submit Stock IN');
            $('#labelQty').text('Qty IN *');
            
            // Show Ref Doc, Hide Destination
            $('#refDocGroup').removeClass('hidden');
            $('#transRefDoc').prop('required', true);
            $('#destinationGroup').addClass('hidden');
            $('#to_location_id').prop('required', false);
        } else if (type === 'OUT') {
            $('#saveTransaction').removeClass('bg-primary-600 hover:bg-primary-700 bg-indigo-600 hover:bg-indigo-700 bg-emerald-600 hover:bg-emerald-700').addClass('bg-red-600 hover:bg-red-700').text('Submit Stock OUT');
            $('#labelQty').text('Qty OUT *');
            
            // Hide Ref Doc, Show Destination
            $('#refDocGroup').addClass('hidden');
            $('#transRefDoc').prop('required', false);
            $('#destinationGroup').removeClass('hidden');
            $('#to_location_id').prop('required', true);
        } else if (type === 'borrow') {
            $('#saveTransaction').removeClass('bg-primary-600 hover:bg-primary-700 bg-red-600 hover:bg-red-700 bg-emerald-600 hover:bg-emerald-700').addClass('bg-indigo-600 hover:bg-indigo-700').text('Submit Borrow');
            $('#labelQty').text('Qty Borrow *');
            
            // Hide Ref Doc, Show Destination
            $('#refDocGroup').addClass('hidden');
            $('#transRefDoc').prop('required', false);
            $('#destinationGroup').removeClass('hidden');
            $('#to_location_id').prop('required', true);
        } else if (type === 'return') {
            $('#saveTransaction').removeClass('bg-primary-600 hover:bg-primary-700 bg-red-600 hover:bg-red-700 bg-indigo-600 hover:bg-indigo-700').addClass('bg-emerald-600 hover:bg-emerald-700').text('Submit Return');
            $('#labelQty').text('Qty Return *');
            
            // Hide Ref Doc, Show Destination
            $('#refDocGroup').addClass('hidden');
            $('#transRefDoc').prop('required', false);
            $('#destinationGroup').removeClass('hidden');
            $('#to_location_id').prop('required', true);
        }
        updateLocationInputState();
    });

    // Auto-select location from selected Tool (read-only from Master data)
    $('#transToolId').on('change', function() {
        updateLocationInputState();
    });

    // Update destination choices when source location is chosen (prevent selecting the same location as destination)
    $('#transLocationId').on('change', function() {
        const type = $('input[name="transaction_type"]:checked').val();
        if (type !== 'IN') {
            const excludeId = $(this).val();
            if (type === 'OUT') {
                filterDestinations(['machine', 'subcont', 'scrap', 'lost'], excludeId);
            } else if (type === 'borrow') {
                filterDestinations(['borrow'], excludeId);
            } else if (type === 'return') {
                filterDestinations(['return'], excludeId);
            }
        }
    });

    $('#btnNewTransaction').on('click', () => { 
        $('#formTransaction')[0].reset(); 
        $('input[name="transaction_type"][value="IN"]').prop('checked', true).trigger('change');
        $('select.select2').trigger('change'); // Reset Select2 display
        showMdl('modal-tool-transaction'); 
    });

    // Auto-trigger transaction modal if tool_id is passed in query string
    const urlParams = new URLSearchParams(window.location.search);
    const preselectedToolId = urlParams.get('tool_id');
    const preselectedAction = urlParams.get('action');
    if (preselectedToolId) {
        $('#formTransaction')[0].reset(); 
        if (preselectedAction === 'out') {
            $('input[name="transaction_type"][value="OUT"]').prop('checked', true).trigger('change');
        } else if (preselectedAction === 'borrow') {
            $('input[name="transaction_type"][value="borrow"]').prop('checked', true).trigger('change');
        } else if (preselectedAction === 'return') {
            $('input[name="transaction_type"][value="return"]').prop('checked', true).trigger('change');
        } else {
            $('input[name="transaction_type"][value="IN"]').prop('checked', true).trigger('change');
        }
        $('#transToolId').val(preselectedToolId).trigger('change');
        showMdl('modal-tool-transaction'); 
    }

    $('#saveTransaction').on('click', function() {
        const type = $('input[name="transaction_type"]:checked').val();
        const url = type === 'IN' ? apiIn : apiOut;
        
        // Temporarily enable location_id so it gets serialized correctly
        $('#transLocationId').prop('disabled', false);
        const formData = $('#formTransaction').serialize();
        updateLocationInputState();
        
        $.ajax({
            url: url, 
            method: 'POST', 
            data: formData,
            success: (res) => { 
                toast('success', `Stock ${type.toUpperCase()}`, res.message); 
                hideMdl('modal-tool-transaction'); 
                fastStockTable.ajax.reload(); 
            },
            error: (xhr) => { 
                toast('error', 'Error', xhr.responseJSON?.message || 'Failed'); 
            }
        });
    });

    // History Logic
    let historyTable = null;
    let histDateRangePicker = null;

    $('#btnHistory').on('click', function() {
        showMdl('modal-tool-history');

        // Init Select2 for filter
        $('.select2-hist-filter').select2({
            dropdownParent: $('#modal-tool-history'),
            width: '100%'
        });
        
        // Init Litepicker if not exists
        if (!histDateRangePicker) {
            const firstDay = new Date(new Date().getFullYear(), new Date().getMonth(), 1);
            const lastDay = new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0);

            histDateRangePicker = new Litepicker({
                element: document.getElementById('filter_date_range'),
                singleMode: false,
                autoApply: true,
                format: 'DD-MM-YYYY',
                delimiter: ' - ',
                startDate: firstDay,
                endDate: lastDay,
                setup: (picker) => {
                    picker.on('selected', (date1, date2) => {
                        if (historyTable) historyTable.ajax.reload();
                    });
                }
            });
        }

        if (!historyTable) {
            historyTable = window.defaultDataTable('#historyTable', {
                processing: true,
                serverSide: true,
                ajax: { 
                    url: "{{ route('inventory.tool.fast-stock.history') }}", 
                    type: 'GET',
                    data: function(d) {
                        d.date_range = $('#filter_date_range').val();
                        d.tool_id = $('#filterHistToolId').val();
                        d.transaction_type = $('#filterHistType').val();
                    }
                },
                columns: [
                    { 
                        data: 'transacted_at', 
                        render: (d, type) => {
                            if (type === 'sort' || type === 'type') return d;
                            const date = new Date(d);
                            return `<span class="text-xs text-gray-500 font-mono">${date.toLocaleDateString('id-ID')} ${date.toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})}</span>`;
                        } 
                    },
                    { 
                        data: 'tool', 
                        render: d => `
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs font-bold text-gray-900 dark:text-white">${d.name}</span>
                                <span class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">${d.brand}</span>
                            </div>` 
                    },
                    { 
                        data: 'transaction_type', className: 'text-center',
                        render: d => {
                            const type = d.toLowerCase();
                            let cls = 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
                            let label = d.toUpperCase();
                            if (type === 'in') {
                                cls = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400';
                            } else if (type === 'borrow') {
                                cls = 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400';
                                label = 'BORROW';
                            } else if (type === 'return') {
                                cls = 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400';
                                label = 'RETURN';
                            }
                            return `<div class="flex justify-center w-full"><span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider ${cls}">${label}</span></div>`;
                        }
                    },
                    { 
                        data: 'qty', className: 'text-center',
                        render: (d, t, r) => {
                            const type = r.transaction_type.toLowerCase();
                            const isPlus = type === 'in' || type === 'return';
                            const color = isPlus ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400';
                            return `<div class="flex justify-center w-full"><span class="text-xs font-bold font-mono ${color}">${d > 0 ? '+' : ''}${d}</span></div>`;
                        }
                    },
                    { data: 'qty_min', className: 'text-center', render: d => `<div class="flex justify-center w-full"><span class="text-xs font-bold text-gray-500 dark:text-gray-400">${d}</span></div>` },
                    { data: 'current_stock', className: 'text-center', render: d => `<div class="flex justify-center w-full"><span class="text-xs font-bold text-primary-600 dark:text-primary-400">${d}</span></div>` },
                    { 
                        data: null, 
                        render: r => {
                            const type = r.transaction_type.toLowerCase();
                            if (type === 'in') {
                                return `
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-xs font-bold text-gray-900 dark:text-white">Restock / Inward</span>
                                        <span class="text-[10px] font-mono text-gray-500 dark:text-gray-400">Ref: ${r.ref_doc || '-'}</span>
                                    </div>`;
                            } else {
                                const loc = r.destination;
                                if (!loc) return '-';
                                let textPrefix = type === 'borrow' ? 'To' : (type === 'return' ? 'From' : 'To');
                                return `
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-xs font-bold text-blue-600 dark:text-blue-400">${loc.code}</span>
                                        <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">${loc.name} (${loc.category.toUpperCase()})</span>
                                    </div>`;
                            }
                        }
                    },
                    { data: 'operator.name', render: d => `<span class="text-xs text-gray-700 dark:text-gray-300 font-medium">${d || '-'}</span>` },
                ],
                order: [[0, 'desc']],
                pageLength: 10,
                lengthChange: false
            });

            // Reactive Filters
            $('#filterHistToolId, #filterHistType').on('change', function() {
                historyTable.ajax.reload();
            });
        } else {
            historyTable.ajax.reload();
        }
    });

    // Print QR Code from table row
    $(document).on('click', '.print-qr-btn', function() {
        const toolId = $(this).data('tool-id');
        window.open(`{{ url('inventory/tool/fast-stock/print-qr') }}/${toolId}`, '_blank');
    });

    // Toggle Legend Popover
    $('#toggleLegend').click(function(e) {
        e.stopPropagation();
        $('#legendPopover').toggleClass('hidden');
    });

    $(document).click(function() {
        $('#legendPopover').addClass('hidden');
    });

    $('#legendPopover').click(function(e) {
        e.stopPropagation();
    });

    // Custom Select2 Formatter for segmented modal options
    function formatLocationState(state) {
        if (!state.id) {
            return state.text;
        }
        const element = $(state.element);
        const category = element.data('category') || 'storage';
        
        let badgeColor = 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800/30';
        if (category === 'machine') {
            badgeColor = 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-400 dark:border-purple-800/30';
        } else if (category === 'subcont') {
            badgeColor = 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-900/20 dark:text-orange-400 dark:border-orange-800/30';
        } else if (category === 'borrow') {
            badgeColor = 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/20 dark:text-indigo-400 dark:border-indigo-800/30';
        } else if (category === 'return') {
            badgeColor = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/30';
        } else if (category === 'scrap' || category === 'lost') {
            badgeColor = 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800/30';
        }

        const badgeText = category.toUpperCase();

        return $(
            `<div class="flex items-center">
                <div class="flex items-center h-5 mr-2">
                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-xs text-[9px] font-bold border ${badgeColor} leading-none">${badgeText}</span>
                </div>
                <span class="text-xs font-medium text-slate-700 dark:text-gray-300 truncate">${state.text}</span>
            </div>`
        );
    }

    $('.select2-modal').select2({
        dropdownParent: $('#modal-tool-transaction'),
        width: '100%',
        templateResult: formatLocationState,
        templateSelection: formatLocationState
    });

    // Filter Toggle Logic
    $('#btnToggleFilter').on('click', function(e) {
        e.stopPropagation();
        const btn = $(this);
        const card = $('#filterCard');
        
        card.slideToggle(200);
        btn.toggleClass('bg-primary-50 text-primary-600 ring-2 ring-primary-500/50');
        
        // Close Legend if open
        if (!$('#legendPopover').hasClass('hidden')) {
            $('#legendPopover').addClass('hidden');
        }
    });
    
    // Close Filter Card when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('#filterCard, #btnToggleFilter, .select2-container').length) {
            $('#filterCard').slideUp(200);
            $('#btnToggleFilter').removeClass('bg-primary-50 text-primary-600 ring-2 ring-primary-500/50');
        }
    });

    // Reactive Filters
    $('#filter_category, #filter_status').on('change', function() {
        fastStockTable.ajax.reload();
    });

    // Reset Filters
    $('#reset_filters').on('click', function() {
        $('#filter_category').val('').trigger('change');
        $('#filter_status').val('').trigger('change');
        fastStockTable.ajax.reload();
    });
});
</script>
@endpush


