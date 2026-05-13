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



    {{-- Table --}}
    <x-table id="fastStockTable">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th class="px-4 py-4 w-12 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">No</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Category</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Tool Name</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Brand</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Spec Code</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Location</th>
                <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Stock</th>
                <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Min. Stock</th>
                <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">UOM</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Last Updated</th>
                <th class="px-4 py-4 text-center w-[90px] text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Action</th>
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
                            <option value="{{ $tool->id }}" data-location-id="{{ $tool->location_id }}">{{ $tool->name }} — {{ $tool->brand }} ({{ $tool->spec_code ?? 'No Spec' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Location <span class="text-red-500">*</span></label>
                    <select name="location_id" id="transLocationId" disabled class="bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 cursor-not-allowed">
                        <option value="">-- Auto From Master --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->code }} — {{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4 hidden" id="destinationGroup">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Destination <span class="text-red-500">*</span></label>
                    <select name="destination_id" id="transDestinationId" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3">
                        <option value="">-- Select Destination --</option>
                        @foreach($destinations as $dest)
                            <option value="{{ $dest->id }}">{{ $dest->code }} — {{ $dest->name }}</option>
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
<div id="modal-tool-history" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4">
    <div class="relative w-full max-w-5xl transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-primary-500"></i> Transaction History
            </h3>
            <button class="close-modal text-gray-400 hover:text-gray-500 w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100 dark:hover:bg-gray-800"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <div class="overflow-y-auto px-6 py-6 flex-1 custom-scrollbar">
            <x-table id="historyTable" class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Date</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tool</th>
                        <th class="px-4 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Type</th>
                        <th class="px-4 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Qty</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Ref / Destination</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Operator</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </x-table>
        </div>
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

    let hasLow = false;

    window.fastStockTable = window.defaultDataTable('#fastStockTable', {
        ajax: { url: apiList, type: 'GET' },
        columns: [
            { data: null, orderable: false, searchable: false, render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'category', render: d => `<span class="text-xs text-gray-500">${d}</span>` },
            { data: 'tool_name', render: d => `<span class="font-semibold text-gray-900 dark:text-white">${d}</span>` },
            { data: 'brand' },
            { data: 'spec_code', render: d => d ? `<span class="font-mono text-xs text-primary-600 dark:text-primary-400">${d}</span>` : '-' },
            { data: 'location', render: d => `<span class="text-xs"></span>` },
            {
                data: 'current_qty', className: 'text-center',
                render: (d, t, r) => `<span class="font-bold text-gray-900 dark:text-white">${d}</span>`
            },
            { data: 'qty_min', className: 'text-center', render: d => `<span class="text-xs text-gray-500">${d}</span>` },
            { data: 'uom', className: 'text-center', render: d => `<span class="text-xs font-mono">${d}</span>` },
            { data: 'last_updated', render: d => `<span class="text-xs text-gray-500">${d}</span>` },
            {
                data: null, orderable: false, searchable: false, className: 'text-center',
                render: (d, t, r) => `
                    <div class="flex items-center justify-center gap-1.5">
                        <button class="out-quick-btn h-8 px-2 inline-flex items-center gap-1 text-red-600 rounded-xs bg-red-50 hover:bg-red-100 text-[9px] font-bold uppercase transition-colors"
                            data-tool-id="${r.tool_id}" data-location-id="${r.location_id}" data-tool-name="${r.tool_name}" title="Quick OUT">
                            <i class="fa-solid fa-minus text-xs"></i> OUT
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

    // Handle transaction type toggle UI
    $('input[name="transaction_type"]').on('change', function() {
        const type = $(this).val(); // IN or OUT
        if (type === 'IN') {
            $('#saveTransaction').removeClass('bg-red-600 hover:bg-red-700').addClass('bg-primary-600 hover:bg-primary-700').text('Submit Stock IN');
            $('#labelQty').text('Qty IN *');
            
            // Show Ref Doc, Hide Destination
            $('#refDocGroup').removeClass('hidden');
            $('#transRefDoc').prop('required', true);
            $('#destinationGroup').addClass('hidden');
            $('#transDestinationId').prop('required', false);
        } else {
            $('#saveTransaction').removeClass('bg-primary-600 hover:bg-primary-700').addClass('bg-red-600 hover:bg-red-700').text('Submit Stock OUT');
            $('#labelQty').text('Qty OUT *');
            
            // Hide Ref Doc, Show Destination
            $('#refDocGroup').addClass('hidden');
            $('#transRefDoc').prop('required', false);
            $('#destinationGroup').removeClass('hidden');
            $('#transDestinationId').prop('required', true);
        }
    });

    $('#btnNewTransaction').on('click', () => { 
        $('#formTransaction')[0].reset(); 
        $('input[name="transaction_type"][value="IN"]').prop('checked', true).trigger('change');
        $('select.select2').trigger('change'); // Reset Select2 display
        showMdl('modal-tool-transaction'); 
    });

    // Auto-fill location from tool selection
    $('#transToolId').on('change', function() {
        const locationId = $('option:selected', this).data('location-id');
        if (locationId) {
            $('#transLocationId').val(locationId).trigger('change');
        } else {
            $('#transLocationId').val('').trigger('change');
        }
    });

    $('#saveTransaction').on('click', function() {
        const type = $('input[name="transaction_type"]:checked').val();
        const url = type === 'IN' ? apiIn : apiOut;
        
        $.ajax({
            url: url, 
            method: 'POST', 
            data: $('#formTransaction').serialize(),
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
    $('#btnHistory').on('click', function() {
        showMdl('modal-tool-history');
        if (!historyTable) {
            historyTable = window.defaultDataTable('#historyTable', {
                ajax: { 
                    url: "{{ route('inventory.tool.fast-stock.history') }}", 
                    type: 'GET',
                    dataSrc: 'data' // Controller returns pagination object
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
                    { 
                        data: null, 
                        render: r => {
                            if (r.transaction_type.toLowerCase() === 'in') {
                                return `<span class="text-[10px] font-mono text-gray-600">Ref: ${r.ref_doc || '-'}</span>`;
                            } else {
                                return `<span class="text-[10px] font-bold text-blue-600"><i class="fa-solid fa-truck-arrow-right mr-1 opacity-50"></i>${r.destination?.name || '-'}</span>`;
                            }
                        }
                    },
                    { data: 'operator.name', render: d => `<span class="text-[10px]">${d || '-'}</span>` },
                ],
                order: [[0, 'desc']],
                pageLength: 10,
                lengthChange: false
            });
        } else {
            historyTable.ajax.reload();
        }
    });

    // Quick OUT from table row
    $(document).on('click', '.out-quick-btn', function() {
        const toolId     = $(this).data('tool-id');
        const locationId = $(this).data('location-id');
        $('#formTransaction')[0].reset();
        $('input[name="transaction_type"][value="OUT"]').prop('checked', true).trigger('change');
        
        // Update values and trigger change for Select2
        $('select[name="tool_id"]', '#formTransaction').val(toolId).trigger('change');
        $('select[name="location_id"]', '#formTransaction').val(locationId).trigger('change');
        
        showMdl('modal-tool-transaction');
    });
});
</script>
@endpush
