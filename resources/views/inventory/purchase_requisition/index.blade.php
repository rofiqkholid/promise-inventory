@extends('layouts.app')
@section('title', 'PR - Part Procurement')
@section('page_title', 'Purchase Requisition')
@section('header-title', 'PR Requisition')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">Purchase Requisition</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">List of parts below minimum stock requiring procurement requisition.</p>
        </div>
    </div>

    {{-- Stats Widgets --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 p-5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-4 transition-all hover:shadow-sm">
            <div class="w-12 h-12 rounded-xs bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-red-600 dark:text-red-400 border border-red-100 dark:border-red-800/50">
                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Urgent (Critical)</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white leading-none">{{ number_format($stats['critical']) }}</h3>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-4 transition-all hover:shadow-sm">
            <div class="w-12 h-12 rounded-xs bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-800/50">
                <i class="fa-solid fa-circle-exclamation text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Replenish (Warning)</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white leading-none">{{ number_format($stats['warning']) }}</h3>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="mb-6 p-6 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-6 items-end">
            <div class="col-span-1">
                <label class="block mb-2 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Customer</label>
                <select id="filterCustomer" class="select2-filter w-full">
                    <option value="">All Customers</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-1">
                <label class="block mb-2 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Model</label>
                <select id="filterModel" class="select2-filter w-full">
                    <option value="">All Models</option>
                    @foreach($models as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-1">
                <label class="block mb-2 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Stock Status</label>
                <select id="filterStatus" class="select2-filter w-full">
                    <option value="">All Problematic</option>
                    <option value="critical">Critical Only</option>
                    <option value="warning">Warning Only</option>
                </select>
            </div>
            <div class="col-span-1 lg:col-span-2 flex items-center gap-3">
                <button type="button" id="btnResetFilter" class="flex-1 h-10 px-4 text-[10px] font-bold text-gray-500 hover:text-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xs border border-slate-100 dark:border-gray-700 transition-all uppercase tracking-widest active:scale-95">
                    <i class="fa-solid fa-rotate-left mr-1"></i> Reset
                </button>
                <button type="button" id="btnExport" class="flex-1 h-10 inline-flex items-center justify-center px-6 bg-emerald-600 border border-transparent text-white text-[10px] font-bold rounded-xs transition-all hover:bg-emerald-700 gap-2 uppercase tracking-widest">
                    <i class="fa-solid fa-file-excel"></i> Export PR Draft
                </button>
            </div>
        </div>
    </div>

    {{-- Table Section --}}
    <div class="bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-gray-700 bg-slate-50/50 dark:bg-slate-900/30 flex items-center justify-between">
            <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-widest flex items-center">
                <i class="fa-solid fa-list-check mr-3 text-primary-600"></i> Part Shortage List
            </h3>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-500 shadow-sm shadow-red-200"></span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">Critical</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 shadow-sm shadow-amber-200"></span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">Warning</span>
                </div>
            </div>
        </div>
        <div class="p-0">
            <x-table id="prTable">
                <thead>
                    <tr>
                        <th class="w-12 text-center text-xs font-bold uppercase tracking-wider">No</th>
                        <th class="text-left text-xs font-bold uppercase tracking-wider">Part Details</th>
                        <th class="text-left text-xs font-bold uppercase tracking-wider">Specifications</th>
                        <th class="w-40 text-center text-xs font-bold uppercase tracking-wider">Model / Customer</th>
                        <th class="w-24 text-center text-xs font-bold uppercase tracking-wider">Stock</th>
                        <th class="w-24 text-center text-xs font-bold uppercase tracking-wider">Min Stock</th>
                        <th class="w-24 text-center text-xs font-bold uppercase tracking-wider text-red-600">Shortage</th>
                        <th class="w-32 text-center text-xs font-bold uppercase tracking-wider">Status</th>
                        <th class="w-20 text-center text-xs font-bold uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </x-table>
        </div>
    </div>
</div>

{{-- Detail Modal --}}
<div id="detailModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-[60] justify-center items-center w-full h-full bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto">
    <div class="relative w-full max-w-2xl">
        <div class="relative bg-white dark:bg-gray-800 rounded-xs shadow-2xl border border-slate-200 dark:border-gray-700 overflow-hidden scale-in">
            <div class="px-6 py-5 bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-widest flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-primary-600"></i> Requirement Detail
                    </h3>
                </div>
                <button type="button" class="close-modal-button text-gray-400 hover:text-red-500 transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-8 space-y-8">
                {{-- Header Summary --}}
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 p-6 bg-slate-50 dark:bg-gray-700/30 rounded-xs border border-slate-100 dark:border-gray-700/50">
                    <div>
                        <h4 id="det_part_no" class="text-2xl font-black text-slate-800 dark:text-white tracking-tighter uppercase leading-none mb-1">-</h4>
                        <p id="det_material" class="text-xs text-slate-400 dark:text-gray-500 font-medium uppercase tracking-wide">-</p>
                    </div>
                    <div id="det_status_badge">-</div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Left: Stock Metrics --}}
                    <div class="space-y-4">
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest border-b border-slate-100 dark:border-gray-700 pb-2">Inventory Balance</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-white dark:bg-gray-900/50 rounded-xs border border-slate-100 dark:border-gray-700">
                                <p class="text-[9px] text-gray-400 font-bold uppercase mb-1">Stock</p>
                                <p id="det_current" class="text-lg font-black text-slate-700 dark:text-gray-200 leading-none">-</p>
                            </div>
                            <div class="p-4 bg-white dark:bg-gray-900/50 rounded-xs border border-slate-100 dark:border-gray-700">
                                <p class="text-[9px] text-gray-400 font-bold uppercase mb-1">Target</p>
                                <p id="det_target" class="text-lg font-black text-slate-700 dark:text-gray-200 leading-none">-</p>
                            </div>
                            <div class="col-span-2 p-5 bg-red-50/50 dark:bg-red-900/10 rounded-xs border border-red-100/50 dark:border-red-900/30">
                                <p class="text-[9px] text-red-500 font-bold uppercase mb-1 flex items-center gap-1.5">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Material Shortage
                                </p>
                                <div class="flex items-baseline gap-1">
                                    <p id="det_shortage" class="text-3xl font-black text-red-600 dark:text-red-400 tracking-tighter leading-none">-</p>
                                    <span class="text-[10px] font-bold text-red-400 uppercase tracking-widest">PCS</span>
                                </div>
                                <p class="text-[9px] text-red-400/80 font-medium mt-2 leading-relaxed">Required replenishment to maintain minimum safety level.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Right: Traceability --}}
                    <div class="space-y-4">
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest border-b border-slate-100 dark:border-gray-700 pb-2">Part Traceability</label>
                        <div class="space-y-4">
                            <div class="flex justify-between items-start border-b border-slate-50 dark:border-gray-700/50 pb-3">
                                <span class="text-xs text-gray-400 font-medium">Customer</span>
                                <span id="det_customer" class="text-xs font-bold text-slate-700 dark:text-gray-300 uppercase tracking-tight text-right">-</span>
                            </div>
                            <div class="flex justify-between items-start border-b border-slate-50 dark:border-gray-700/50 pb-3">
                                <span class="text-xs text-gray-400 font-medium">Model</span>
                                <span id="det_model" class="text-xs font-bold text-slate-700 dark:text-gray-300 uppercase tracking-tight text-right">-</span>
                            </div>
                            <div class="flex justify-between items-start border-b border-slate-50 dark:border-gray-700/50 pb-3">
                                <span class="text-xs text-gray-400 font-medium">Unit</span>
                                <span id="det_unit" class="text-xs font-bold text-slate-700 dark:text-gray-300 uppercase tracking-tight text-right">-</span>
                            </div>
                            <div class="flex justify-between items-start border-b border-slate-50 dark:border-gray-700/50 pb-3">
                                <span class="text-xs text-gray-400 font-medium">Pcs/Unit</span>
                                <span id="det_pcs_unit" class="text-xs font-bold text-slate-700 dark:text-gray-300 uppercase tracking-tight text-right">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 px-8 py-6 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-gray-700">
                <button type="button" class="close-modal-button flex-1 h-11 px-6 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 border border-slate-200 dark:border-gray-600 rounded-xs text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-gray-400 transition-all active:scale-95">
                    Close
                </button>
                <button type="button" class="flex-1 h-11 px-6 bg-primary-600 hover:bg-primary-700 rounded-xs text-[10px] font-black uppercase tracking-widest text-white transition-all">
                    <i class="fa-solid fa-file-signature mr-2"></i> Request PR
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function() {
    // Datatable Init
    const table = window.defaultDataTable('#prTable', {
        processing: true,
        serverSide: true,
        dom: "<'flex justify-between items-center mb-4'<'flex items-center gap-4'l B> f><'overflow-x-auto w-full border border-slate-100 dark:border-gray-700/50 rounded-xs mb-2't><'flex justify-between items-center mt-4 gap-4 px-2'i p>",
        ajax: {
            url: '{{ route("inventory.purchaseRequisition.data") }}',
            data: function(d) {
                d.customer_id = $('#filterCustomer').val();
                d.model_id = $('#filterModel').val();
                d.status = $('#filterStatus').val();
            }
        },
        columns: [
            { 
                data: null, 
                className: 'text-center text-gray-400', 
                render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1 
            },
            { 
                data: 'part_no', 
                className: 'py-3 min-w-[180px]',
                render: function(data, type, row) {
                    return `
                        <div class="font-bold text-slate-800 dark:text-white leading-tight uppercase tracking-tight">${data}</div>
                        <div class="text-[10px] text-slate-400 uppercase truncate max-w-[200px]">${row.part_name || '-'}</div>
                    `;
                }
            },
            { 
                data: 'material',
                className: 'text-xs text-gray-600 dark:text-gray-400',
                render: d => d || '-'
            },
            { 
                data: null,
                className: 'text-center',
                render: function(data, type, row) {
                    return `
                        <div class="text-xs font-bold text-slate-700 dark:text-gray-300 uppercase leading-none mb-1.5">${row.model || '-'}</div>
                        <div class="text-[10px] font-medium text-slate-400 dark:text-gray-500 uppercase tracking-widest pl-1">${row.customer || '-'}</div>
                    `;
                }
            },
            { 
                data: 'current_stock', 
                className: 'text-center font-bold text-slate-900 dark:text-white text-xs' 
            },
            { 
                data: 'min_stock', 
                className: 'text-center font-bold text-slate-500 dark:text-gray-400 text-xs' 
            },
            { 
                data: 'shortage', 
                className: 'text-center font-black text-red-600 dark:text-red-400 text-xs' 
            },
            { 
                data: 'status', 
                className: 'text-center',
                render: function(data, type, row) {
                    const config = {
                        'critical': 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800/50',
                        'warning': 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800/50',
                        'safe': 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/50'
                    };
                    const colorClass = config[data] || 'bg-gray-50 text-gray-600 border-gray-100';
                    const label = data.toUpperCase();
                    
                    return `<span class="px-3 py-1.5 rounded-xs text-[10px] font-bold uppercase tracking-widest border ${colorClass}">${label}</span>`;
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    return `
                        <button class="view-detail-btn inline-flex items-center justify-center p-2 text-primary-600 hover:text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 rounded-xs transition-all active:scale-90" title="View Details">
                            <i class="fa-solid fa-arrow-right text-sm"></i>
                        </button>
                    `;
                }
            }
        ],
        order: [[6, 'desc']] // Default order by shortage (now at index 6)
    });

    // Select2 Filters
    $('.select2-filter').select2({
        width: '100%',
        allowClear: true
    }).on('change', function() {
        table.ajax.reload();
    });

    $('#btnResetFilter').on('click', function() {
        $('#filterCustomer, #filterModel, #filterStatus').val('').trigger('change');
    });

    $('#btnExport').on('click', function() {
        Swal.fire({
            title: 'Export PR Draft',
            text: 'Development for PR creation is in progress.',
            icon: 'info',
            confirmButtonColor: '#0f172a'
        });
    });

    // Detail Modal Logic
    $(document).on('click', '.view-detail-btn', function() {
        const data = table.row($(this).closest('tr')).data();
        
        $('#det_part_no').text(data.part_no);
        $('#det_material').text(data.part_name || data.material); // Priority to part name
        $('#det_customer').text(data.customer || '-');
        $('#det_model').text(data.model || '-');
        $('#det_current').text(numberFormat(data.current_stock) + ' PCS');
        $('#det_target').text(numberFormat(data.min_stock_val) + ' PCS');
        $('#det_shortage').text(numberFormat(data.shortage));
        $('#det_unit').text(data.unit_name || '-');
        $('#det_pcs_unit').text(numberFormat(data.pcs_per_unit || 1) + ' PCS/Unit');
        
        let statusHtml = '';
        if (data.status === 'critical') {
            statusHtml = `<span class="px-4 py-1.5 text-[10px] font-black rounded border-2 bg-red-50 text-red-700 border-red-200 uppercase tracking-widest italic">CRITICAL</span>`;
        } else {
            statusHtml = `<span class="px-4 py-1.5 text-[10px] font-black rounded border-2 bg-amber-50 text-amber-700 border-amber-200 uppercase tracking-widest italic">WARNING</span>`;
        }
        $('#det_status_badge').html(statusHtml);

        $('#detailModal').removeClass('hidden').addClass('flex');
    });

    $('.close-modal-button').on('click', function() {
        $('#detailModal').addClass('hidden').removeClass('flex');
    });

    // Close on backdrop click
    $('#detailModal').on('click', function(e) {
        if (e.target === this) $(this).addClass('hidden').removeClass('flex');
    });

    function numberFormat(val) {
        return parseFloat(val || 0).toLocaleString('en-US');
    }
});
</script>
@endpush
@endsection
