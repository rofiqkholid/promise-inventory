@extends('layouts.app')

@section('title', 'Slow Moving Asset Register')

@section('content')
<div class="text-gray-900 dark:text-gray-100">

    {{-- Header --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">Slow Moving Assets</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Asset register for slow moving tools (Arbor, Collet, Holder). Tracked per purchase batch with depreciation.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-2">
            <button type="button" id="btnAddBatch" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest transition-all">
                <i class="fa-solid fa-plus"></i> Register Asset
            </button>
        </div>
    </div>

    {{-- Total Asset Value Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-900 rounded-xs border border-gray-200 dark:border-gray-800 p-5">
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Total Active Items</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="statTotalBatches">—</p>
        </div>
        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xs border border-emerald-200 dark:border-emerald-800 p-5">
            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 mb-1">Total Asset Value</p>
            <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400" id="statTotalValue">—</p>
        </div>
    </div>

    {{-- Status Filter --}}
    <div class="flex items-center gap-2 mb-4">
        <span class="text-[10px] font-bold uppercase text-gray-500 tracking-wider">Filter:</span>
        <button data-status="active"  class="status-filter-btn px-3 py-1.5 rounded-xs text-[10px] font-bold uppercase tracking-wider bg-emerald-600 text-white">Active</button>
        <button data-status="nok"     class="status-filter-btn px-3 py-1.5 rounded-xs text-[10px] font-bold uppercase tracking-wider bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50">NOK</button>
        <button data-status="retired" class="status-filter-btn px-3 py-1.5 rounded-xs text-[10px] font-bold uppercase tracking-wider bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50">Retired</button>
        <button data-status="all"     class="status-filter-btn px-3 py-1.5 rounded-xs text-[10px] font-bold uppercase tracking-wider bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50">All</button>
    </div>

    {{-- Table --}}
    <x-table id="slowBatchTable">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th class="px-4 py-4 w-12 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">No</th>
                <th scope="col" class="px-6 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">ID Number</th>
                <th scope="col" class="px-6 py-4 text-center w-16 text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Sketch</th>
                <th scope="col" class="px-6 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Tool Name</th>
                <th scope="col" class="px-6 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Location</th>
                <th scope="col" class="px-6 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Purchase Date</th>
                <th scope="col" class="px-6 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Age / Life</th>
                <th scope="col" class="px-6 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Rate (%)</th>
                <th scope="col" class="px-6 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Condition Status</th>
                <th scope="col" class="px-6 py-4 text-right text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Asset Value (IDR)</th>
                <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Status</th>
                <th class="px-4 py-4 text-center w-[90px] text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

{{-- Modal: Register Batch --}}
<div id="modal-batch-form" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50">
    <div class="relative w-full max-w-lg transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-widest" id="batchModalTitle">Register New Asset</h3>
            <button class="close-modal text-gray-400 hover:text-gray-500 w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <div class="overflow-y-auto px-6 py-6 flex-1 custom-scrollbar">
            <form id="batchForm">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <input type="hidden" id="batchId">
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Tool <span class="text-red-500">*</span></label>
                    <select name="tool_id" required class="select2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3">
                        <option value="">-- Select Tool --</option>
                        @foreach($tools as $tool)
                            @if($tool->category && $tool->category->moving_type === 'slow')
                            <option value="{{ $tool->id }}" data-lifetime="{{ $tool->std_lifetime_yrs }}">
                                {{ $tool->name }} — {{ $tool->brand }} ({{ $tool->spec_code ?? 'No Spec' }})
                            </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">ID Number <span class="text-red-500">*</span></label>
                        <input type="text" name="id_number" required class="uppercase bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3" placeholder="e.g. TOL-2024-001">
                    </div>
                    <div>
                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Purchase Date <span class="text-red-500">*</span></label>
                        <input type="date" name="purchase_date" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Price (IDR) <span class="text-red-500">*</span></label>
                        <input type="number" name="purchase_price" min="0" step="1000" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3" placeholder="0">
                    </div>
                    <div>
                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Physical Rate (%) <span class="text-red-500">*</span></label>
                        <select name="physical_rate" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3">
                            <option value="100">100% — Ok</option>
                            <option value="75">75% — Good</option>
                            <option value="50">50% — Still good</option>
                            <option value="25">25% — Warning</option>
                            <option value="0">0% — Broken</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Std. Lifetime (Yrs) <span class="text-red-500">*</span></label>
                        <input type="number" name="std_lifetime_yrs" id="batchLifetime" min="1" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3" placeholder="e.g. 5">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Current Location <span class="text-red-500">*</span></label>
                    <select name="location_id" id="batchLocationId" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3">
                        <option value="">-- Select Location --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->code }} — {{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Status <span class="text-red-500">*</span></label>
                    <select name="status" id="batchStatus" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3">
                        <option value="active">ACTIVE</option>
                        <option value="nok">NON-ACTIVE (NOK)</option>
                        <option value="retired">RETIRED</option>
                    </select>
                </div>
                {{-- Preview asset value --}}
                <div class="mt-5 p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-xs border border-emerald-200 dark:border-emerald-800">
                    <p class="text-[10px] font-bold uppercase text-emerald-700 dark:text-emerald-400 tracking-wider mb-1">Asset Value Preview</p>
                    <p class="text-lg font-bold text-emerald-700 dark:text-emerald-400" id="previewInitialValue">—</p>
                    <p class="text-[10px] text-gray-500 mt-1">= Price × (Physical Rate / 100)</p>
                </div>
            </form>
        </div>
        <div class="border-t border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 flex gap-3">
            <button type="button" class="close-modal flex-1 px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-[10px] font-bold text-gray-600 uppercase tracking-widest hover:bg-gray-50 transition-colors">Cancel</button>
            <button type="button" id="saveBatchBtn" class="flex-1 px-4 py-3 bg-primary-600 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest hover:bg-primary-700 transition-all">Save Asset</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const apiBase = "{{ route('inventory.tool.slow-batch.index') }}";
    const idr = (v) => 'Rp ' + parseFloat(v || 0).toLocaleString('id-ID');

    window.previewImg = (src) => {
        Swal.fire({
            imageUrl: src,
            imageAlt: 'Tool Sketch',
            showConfirmButton: false,
            width: 'auto',
            padding: '0',
            background: 'transparent',
            backdrop: 'rgba(0,0,0,0.8)'
        });
    };

    let currentStatus = 'active';

    window.slowTable = window.defaultDataTable('#slowBatchTable', {
        ajax: {
            url: apiBase, type: 'GET',
            data: (d) => { d.status = currentStatus; }
        },
        columns: [
            { data: null, orderable: false, searchable: false, render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'id_number', render: d => `<span class="font-mono font-semibold text-primary-600 dark:text-primary-400 text-xs">${d}</span>` },
            { 
                data: 'sketch_image', 
                className: 'text-center',
                render: d => d ? `<img src="${d}" class="h-8 w-8 object-cover mx-auto rounded-xs border border-gray-200 cursor-pointer hover:scale-150 transition-all" onclick="window.previewImg('${d}')">` : `<div class="h-8 w-8 flex items-center justify-center mx-auto bg-gray-50 border border-gray-100 text-gray-300 rounded-xs"><i class="fa-solid fa-image text-[8px]"></i></div>`
            },
            {
                data: null, render: (d, t, r) =>
                    `<div><span class="font-semibold text-xs text-gray-900 dark:text-white">${r.tool_name}</span><br>
                    <span class="text-[10px] text-gray-500 uppercase font-bold tracking-tighter">${r.brand} · ${r.spec_code || 'No Spec'}</span></div>`
            },
            { data: 'location', render: d => `<span class="text-xs font-bold text-gray-700 dark:text-gray-300">${d}</span>` },
            { data: 'purchase_date', className: 'text-center', render: d => `<span class="text-xs">${d}</span>` },
            { 
                data: 'age_years', className: 'text-center', 
                render: (d, t, r) => `<span class="text-xs font-semibold ${d >= r.std_lifetime_yrs ? 'text-orange-600' : 'text-gray-600'}">${d} / ${r.std_lifetime_yrs}</span>` 
            },
            { 
                data: 'physical_rate', className: 'text-center', 
                render: d => `<span class="text-xs font-bold ${d < 50 ? 'text-red-500' : 'text-emerald-600'}">${d}%</span>` 
            },
            {
                data: 'physical_rate', className: 'text-center',
                render: (d) => {
                    const val = Math.round(d);
                    let label = 'UNKNOWN';
                    let cls = 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                    if (val === 100) {
                        label = 'OK';
                        cls = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400';
                    } else if (val === 75) {
                        label = 'GOOD';
                        cls = 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
                    } else if (val === 50) {
                        label = 'STILL GOOD';
                        cls = 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400';
                    } else if (val === 25 || val === 20) {
                        label = 'WARNING';
                        cls = 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400';
                    } else if (val === 0) {
                        label = 'BROKEN';
                        cls = 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
                    }
                    return `<span class="px-2 py-0.5 rounded-xs text-[9px] font-bold uppercase tracking-wider ${cls}">${label}</span>`;
                }
            },
            {
                data: null, className: 'text-right font-mono',
                render: (d, t, r) => {
                    const cls = r.status === 'active' ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-gray-400 line-through';
                    return `<span class="${cls} text-xs">${idr(r.live_asset_value)}</span>`;
                }
            },
            {
                data: 'status', className: 'text-center',
                render: (d) => {
                    const badges = {
                        active:  'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                        nok:     'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                        retired: 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
                    };
                    return `<span class="px-2 py-0.5 rounded-xs text-[9px] font-bold uppercase tracking-wider ${badges[d] || ''}">${d}</span>`;
                }
            },
            { 
                data: null, orderable: false, searchable: false, className: 'text-center',
                render: (d, t, r) => `
                    <button class="edit-batch-btn h-8 w-8 inline-flex items-center justify-center text-primary-600 rounded-xs bg-primary-50 hover:bg-primary-100 transition-colors"
                        data-id="${r.id}" 
                        data-id-number="${r.id_number}"
                        data-tool-id="${r.tool_id}"
                        data-location-id="${r.location_id}"
                        data-purchase-date="${r.purchase_date_raw}" 
                        data-purchase-price="${r.purchase_price}" 
                        data-physical-rate="${r.physical_rate}"
                        data-lifetime="${r.std_lifetime_yrs}" 
                        data-status="${r.status}"
                        title="Edit">
                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                    </button>`
            }
        ],
        drawCallback: function() {
            const api = this.api();
            let totalItems = 0, totalValue = 0;
            api.rows().every(function() {
                const d = this.data();
                if (d.status === 'active') { 
                    totalItems++; 
                    totalValue += parseFloat(d.live_asset_value || 0); 
                }
            });
            $('#statTotalBatches').text(totalItems);
            $('#statTotalValue').text(idr(totalValue));
        }
    });

    // Status filter
    $('.status-filter-btn').on('click', function() {
        currentStatus = $(this).data('status');
        $('.status-filter-btn').removeClass('bg-emerald-600 text-white').addClass('bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50');
        $(this).addClass('bg-emerald-600 text-white').removeClass('bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50');
        slowTable.ajax.reload();
    });

    const showMdl = (id) => { $('.modal-container').addClass('hidden'); $(`#${id}`).removeClass('hidden'); };
    const hideMdl = (id) => { $(`#${id}`).addClass('hidden'); };
    $('.close-modal').on('click', function() { $(this).closest('.modal-container').addClass('hidden'); });

    // Preview asset value (Sync with controller logic: Price * Depreciation * Physical Rate)
    function updatePreview() {
        const price = parseFloat($('input[name="purchase_price"]').val()) || 0;
        const rate  = parseFloat($('[name="physical_rate"]').val()) || 0;
        const purchaseDateStr = $('input[name="purchase_date"]').val();
        const lifetime = parseFloat($('input[name="std_lifetime_yrs"]').val()) || 1;

        let depFactor = 1;
        if (purchaseDateStr) {
            const purchaseDate = new Date(purchaseDateStr);
            const today = new Date();
            const ageDays = (today - purchaseDate) / (1000 * 60 * 60 * 24);
            const ageYears = ageDays / 365.25;
            const remainYrs = Math.max(0, lifetime - ageYears);
            depFactor = remainYrs / lifetime;
        }

        const total = price * depFactor * (rate / 100);
        $('#previewInitialValue').text(total > 0 ? idr(total) : (total === 0 && purchaseDateStr ? idr(0) : '—'));
    }
    $('input[name="purchase_price"], select[name="physical_rate"], input[name="purchase_date"], input[name="std_lifetime_yrs"]').on('input change', updatePreview);

    // Auto-fill lifetime and ID prefix from tool selection
    $('select[name="tool_id"]', '#modal-batch-form').on('change', function() {
        const selected = $('option:selected', this);
        const lifetime = selected.data('lifetime');
        if (lifetime) $('#batchLifetime').val(lifetime);

        const toolId = $(this).val();
        const idInput = $('input[name="id_number"]', '#batchForm');
        
        // Only generate ID number for new registrations (where batchId is empty)
        if (toolId && !$('#batchId').val()) {
            idInput.attr('placeholder', 'Generating...');
            $.ajax({
                url: apiBase + '/next-id',
                method: 'GET',
                data: { tool_id: toolId },
                success: function(res) {
                    if (res.next_id) {
                        idInput.val(res.next_id);
                    } else {
                        idInput.val('');
                        idInput.attr('placeholder', 'e.g. TOL-2024-001');
                    }
                },
                error: function() {
                    idInput.val('');
                    idInput.attr('placeholder', 'e.g. TOL-2024-001');
                }
            });
        }
    });

    // Auto-transform ID number to uppercase as typed
    $('input[name="id_number"]', '#batchForm').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });

    // Automatically set status to NOK and disable active option when physical rate is 0%
    $('select[name="physical_rate"]', '#modal-batch-form').on('change', function() {
        const rate = $(this).val();
        const statusSelect = $('select[name="status"]', '#modal-batch-form');
        if (rate === '0') {
            statusSelect.val('nok').trigger('change');
            statusSelect.find('option[value="active"]').attr('disabled', true);
        } else {
            statusSelect.find('option[value="active"]').removeAttr('disabled');
        }
    });

    $('#btnAddBatch').on('click', () => {
        $('#batchForm')[0].reset();
        $('#batchId').val('');
        $('input[name="_method"]').val('POST');
        $('#batchModalTitle').text('Register New Batch');
        $('#previewInitialValue').text('—');
        $('select.select2').trigger('change');
        showMdl('modal-batch-form');
    });

    $(document).on('click', '.edit-batch-btn', function() {
        const btn = $(this);
        $('#batchForm')[0].reset();
        $('#batchId').val(btn.data('id'));
        $('input[name="id_number"]', '#batchForm').val(btn.data('id-number'));
        $('select[name="tool_id"]', '#batchForm').val(btn.data('tool-id')).trigger('change');
        $('input[name="purchase_date"]', '#batchForm').val(btn.data('purchase-date'));
        $('input[name="purchase_price"]', '#batchForm').val(btn.data('purchase-price'));
        $('[name="physical_rate"]', '#batchForm').val(Math.round(btn.data('physical-rate'))).trigger('change');
        $('input[name="std_lifetime_yrs"]', '#batchForm').val(btn.data('lifetime'));
        $('select[name="location_id"]', '#batchForm').val(btn.data('location-id'));
        $('select[name="status"]', '#batchForm').val(btn.data('status'));
        $('input[name="_method"]').val('PUT');
        $('#batchModalTitle').text('Edit Asset');
        updatePreview();
        showMdl('modal-batch-form');
    });

    $('#saveBatchBtn').on('click', function() {
        const id  = $('#batchId').val();
        const url = id ? `${apiBase}/${id}` : apiBase;
        $.ajax({
            url, method: 'POST', data: $('#batchForm').serialize(),
            success: (res) => { toast('success', 'Success', res.message); hideMdl('modal-batch-form'); slowTable.ajax.reload(); },
            error:   (xhr) => { toast('error',   'Error',   xhr.responseJSON?.message || 'Failed'); }
        });
    });
});
</script>
@endpush
