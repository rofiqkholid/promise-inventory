@extends('layouts.app')

@section('title', 'Fast Moving Stock')

@section('content')
<div class="text-gray-900 dark:text-gray-100">

    {{-- Header --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">Fast Moving Stock</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Stock monitoring and IN/OUT transactions for fast moving tools (e.g. Endmill, Drill).</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-2">
            <button type="button" id="btnHistory" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
                <i class="fa-solid fa-clock-rotate-left"></i> History
            </button>
            <button type="button" id="btnNewTransaction" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest active:scale-[0.98] transition-all">
                <i class="fa-solid fa-plus"></i> New Transaction
            </button>
        </div>
    </div>

    {{-- Status Legend --}}
    <div class="bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 rounded-xs p-4 mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-circle-info text-slate-400 text-xs"></i>
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Stock Status Legend:</span>
        </div>
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-1.5">
                <span class="px-2 py-0.5 rounded-xs text-[9px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400">CRITICAL</span>
                <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">Stock &lt; Min</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="px-2 py-0.5 rounded-xs text-[9px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">WARNING</span>
                <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">Stock = Min</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="px-2 py-0.5 rounded-xs text-[9px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">SAFE</span>
                <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">Min &lt; Stock &le; Max</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="px-2 py-0.5 rounded-xs text-[9px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">OVER</span>
                <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">Stock &gt; Max</span>
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
                <th class="px-4 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700" style="min-width: 180px;">Tool Name</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Brand</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700" style="min-width: 250px;">Spec Code</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Location</th>
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
                    <div class="grid grid-cols-2 gap-2">
                        <label class="relative flex items-center justify-center p-3 border border-gray-200 dark:border-gray-700 rounded-xs cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-all has-[:checked]:border-primary-600 has-[:checked]:bg-primary-50/50 dark:has-[:checked]:bg-primary-900/20">
                            <input type="radio" name="transaction_type" value="IN" class="hidden peer" checked>
                            <span class="text-xs font-bold text-gray-500 peer-checked:text-primary-600 dark:peer-checked:text-primary-400 uppercase tracking-wide">Stock IN</span>
                        </label>
                        <label class="relative flex items-center justify-center p-3 border border-gray-200 dark:border-gray-700 rounded-xs cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-all has-[:checked]:border-red-600 has-[:checked]:bg-red-50/50 dark:has-[:checked]:bg-red-900/20">
                            <input type="radio" name="transaction_type" value="OUT" class="hidden peer">
                            <span class="text-xs font-bold text-gray-500 peer-checked:text-red-600 dark:peer-checked:text-red-400 uppercase tracking-wide">Stock OUT</span>
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Tool <span class="text-red-500">*</span></label>
                    <select name="tool_id" id="transToolId" required class="select2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3">
                        <option value="">-- Select Tool --</option>
                        @foreach($tools as $tool)
                            <option value="{{ $tool->id }}" data-location-id="{{ $tool->location_id }}">
                                {{ $tool->name }} — {{ $tool->brand }} ({{ $tool->spec_code ?? 'No Spec' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Location <span class="text-red-500">*</span></label>
                    <select name="location_id" id="transLocationId" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                        <option value="">-- Select Location --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->code }} — {{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4 hidden" id="destinationGroup">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Destination <span class="text-red-500">*</span></label>
                    <select name="to_location_id" id="to_location_id" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3">
                        <option value="">-- Select Destination --</option>
                        @foreach($destinations as $category => $locs)
                            <optgroup label="{{ strtoupper($category) }}">
                                @foreach($locs as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->code }} — {{ $loc->name }}</option>
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
                            placeholder="Select Date Range">
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
                        <th class="px-4 py-3 text-center text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400">Type</th>
                        <th class="px-4 py-3 text-center text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400">Qty</th>
                        <th class="px-4 py-3 text-center text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400">Min Stock</th>
                        <th class="px-4 py-3 text-center text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400">Current Stock</th>
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
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
        ajax: { url: apiList, type: 'GET' },
        order: [[3, 'asc']],
        columns: [
            { data: null, orderable: false, searchable: false, render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'category', render: d => `<span class="text-xs text-gray-500">${d}</span>` },
            { 
                data: 'sketch_image', 
                className: 'text-center',
                render: d => d ? `<img src="${d}" class="h-8 w-8 object-cover mx-auto rounded-xs border border-gray-200 cursor-pointer hover:scale-150 transition-all" onclick="window.previewImg('${d}')">` : `<div class="h-8 w-8 flex items-center justify-center mx-auto bg-gray-50 border border-gray-100 text-gray-300 rounded-xs"><i class="fa-solid fa-image text-[8px]"></i></div>`
            },
            { data: 'tool_name', render: d => `<span class="font-semibold text-gray-900 dark:text-white">${d}</span>` },
            { data: 'brand' },
            { data: 'spec_code', render: d => d ? `<span class="font-mono text-xs text-primary-600 dark:text-primary-400">${d}</span>` : '-' },
            { data: 'location', render: d => `<span class="text-xs font-bold text-gray-700 dark:text-gray-300">${d}</span>` },
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

    // Handle transaction type toggle UI
    $('input[name="transaction_type"]').on('change', function() {
        const type = $(this).val(); // IN or OUT
        const isOut = (type === 'OUT');
        if (type === 'IN') {
            $('#saveTransaction').removeClass('bg-red-600 hover:bg-red-700').addClass('bg-primary-600 hover:bg-primary-700').text('Submit Stock IN');
            $('#labelQty').text('Qty IN *');
            
            // Show Ref Doc, Hide Destination
            $('#refDocGroup').removeClass('hidden');
            $('#transRefDoc').prop('required', true);
            $('#destinationGroup').addClass('hidden');
            $('#to_location_id').prop('required', false);
        } else {
            $('#saveTransaction').removeClass('bg-primary-600 hover:bg-primary-700').addClass('bg-red-600 hover:bg-red-700').text('Submit Stock OUT');
            $('#labelQty').text('Qty OUT *');
            
            // Hide Ref Doc, Show Destination
            $('#refDocGroup').addClass('hidden');
            $('#transRefDoc').prop('required', false);
            $('#destinationGroup').removeClass('hidden');
            $('#to_location_id').prop('required', isOut);
        }
    });

    // Auto-select location from selected Tool (read-only from Master data)
    $('#transToolId').on('change', function() {
        const selected = $('option:selected', this);
        const locId = selected.data('location-id');
        
        if (locId) {
            $('#transLocationId').val(locId).trigger('change');
            $('#transLocationId').addClass('bg-slate-50 dark:bg-gray-800/80 cursor-not-allowed opacity-75').prop('disabled', true);
        } else {
            $('#transLocationId').val('').trigger('change');
            $('#transLocationId').removeClass('bg-slate-50 dark:bg-gray-800/80 cursor-not-allowed opacity-75').prop('disabled', false);
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
    if (preselectedToolId) {
        $('#formTransaction')[0].reset(); 
        $('input[name="transaction_type"][value="IN"]').prop('checked', true).trigger('change');
        $('#transToolId').val(preselectedToolId).trigger('change');
        showMdl('modal-tool-transaction'); 
    }

    $('#saveTransaction').on('click', function() {
        const type = $('input[name="transaction_type"]:checked').val();
        const url = type === 'IN' ? apiIn : apiOut;
        
        // Temporarily enable location_id so it gets serialized correctly
        $('#transLocationId').prop('disabled', false);
        const formData = $('#formTransaction').serialize();
        if ($('#transToolId').val() && $('option:selected', '#transToolId').data('location-id')) {
            $('#transLocationId').prop('disabled', true);
        }
        
        $.ajax({
            url: url, 
            method: 'POST', 
            data: formData,
            success: (res) => { 
                toast('success', `Stock ${type}`, res.message); 
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
            histDateRangePicker = new Litepicker({
                element: document.getElementById('filter_date_range'),
                singleMode: false,
                autoApply: true,
                format: 'DD-MM-YYYY',
                delimiter: ' - ',
                setup: (picker) => {
                    picker.on('selected', (date1, date2) => {
                        if (historyTable) historyTable.ajax.reload();
                    });
                }
            });
        }

        if (!historyTable) {
            historyTable = window.defaultDataTable('#historyTable', {
                ajax: { 
                    url: "{{ route('inventory.tool.fast-stock.history') }}", 
                    type: 'GET',
                    data: function(d) {
                        d.date_range = $('#filter_date_range').val();
                        d.tool_id = $('#filterHistToolId').val();
                        d.transaction_type = $('#filterHistType').val();
                    },
                    dataSrc: 'data'
                },
                columns: [
                    { 
                        data: 'transacted_at', 
                        render: (d, type) => {
                            if (type === 'sort' || type === 'type') return d;
                            const date = new Date(d);
                            return `<span class="text-[11px] text-gray-500 font-mono">${date.toLocaleDateString('id-ID')} ${date.toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})}</span>`;
                        } 
                    },
                    { 
                        data: 'tool', 
                        render: d => `
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-gray-900 dark:text-white">${d.name}</span>
                                <span class="text-[10px] text-gray-500">${d.brand}</span>
                            </div>` 
                    },
                    { 
                        data: 'transaction_type', className: 'text-center',
                        render: d => {
                            const isIn = d.toLowerCase() === 'in';
                            const cls = isIn ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700';
                            return `<span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase ${cls}">${d}</span>`;
                        }
                    },
                    { 
                        data: 'qty', className: 'text-center',
                        render: (d, t, r) => {
                            const isIn = r.transaction_type.toLowerCase() === 'in';
                            const color = isIn ? 'text-emerald-600' : 'text-red-600';
                            return `<span class="font-bold font-mono ${color}">${d > 0 ? '+' : ''}${d}</span>`;
                        }
                    },
                    { data: 'qty_min', className: 'text-center', render: d => `<span class="text-xs font-bold text-gray-500">${d}</span>` },
                    { data: 'current_stock', className: 'text-center', render: d => `<span class="text-xs font-bold text-primary-600">${d}</span>` },
                    { 
                        data: null, 
                        render: r => {
                            if (r.transaction_type.toLowerCase() === 'in') {
                                return `<span class="text-[10px] font-mono text-gray-600">Ref: ${r.ref_doc || '-'}</span>`;
                            } else {
                                const loc = r.destination;
                                if (!loc) return '-';
                                return `
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-bold text-blue-600"><i class="fa-solid fa-location-dot mr-1 opacity-50"></i>${loc.name}</span>
                                        <span class="text-[8px] uppercase text-gray-400 font-bold tracking-tighter">${loc.category}</span>
                                    </div>`;
                            }
                        }
                    },
                    { data: 'operator.name', render: d => `<span class="text-[10px]">${d || '-'}</span>` },
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
});
</script>
@endpush
