@extends('layouts.app')
@section('title', 'PR - Part Procurement')
@section('page_title', 'Purchase Requisition')
@section('header-title', 'PR Requisition')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    <div class="sm:flex sm:items-center sm:justify-between mb-4 gap-4">
        <div class="flex-1">
            <h2 class="text-xl xl:text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tighter leading-none">Purchase Requisition</h2>
            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 font-normal">List of parts below minimum stock requiring procurement requisition.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            {{-- Urgent (Critical) --}}
            <div class="flex-none min-w-[160px] bg-white dark:bg-gray-800 p-3 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all hover:bg-slate-50/50 dark:hover:bg-gray-700/50">
                <div class="w-9 h-9 rounded-xs bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-red-600 dark:text-red-400 border border-red-100 dark:border-red-800/50">
                    <i class="fa-solid fa-triangle-exclamation text-base"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-gray-400 dark:text-gray-500 tracking-tight leading-none mb-1">Urgent (Critical)</p>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white leading-none tracking-tighter">{{ number_format($stats['critical']) }}</h3>
                </div>
            </div>

            {{-- Replenish (Warning) --}}
            <div class="flex-none min-w-[160px] bg-white dark:bg-gray-800 p-3 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all hover:bg-slate-50/50 dark:hover:bg-gray-700/50">
                <div class="w-9 h-9 rounded-xs bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-800/50">
                    <i class="fa-solid fa-circle-exclamation text-base"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-gray-400 dark:text-gray-500 tracking-tight leading-none mb-1">Replenish (Warning)</p>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white leading-none tracking-tighter">{{ number_format($stats['warning']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="mb-4 p-6 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-6 items-end">
            <div class="col-span-1">
                <label class="block mb-2 text-xs font-medium text-gray-900 dark:text-gray-500">Customer</label>
                <select id="filterCustomer" class="select2-filter w-full">
                    <option value="">All Customers</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-1">
                <label class="block mb-2 text-xs font-medium text-gray-900 dark:text-gray-500">Model</label>
                <select id="filterModel" class="select2-filter w-full">
                    <option value="">All Models</option>
                    @foreach($models as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-1">
                <label class="block mb-2 text-xs font-medium text-gray-900 dark:text-gray-500">Stock Status</label>
                <select id="filterStatus" class="select2-filter w-full">
                    <option value="">All Problematic</option>
                    <option value="critical">Critical Only</option>
                    <option value="warning">Warning Only</option>
                </select>
            </div>
            <div class="col-span-1 lg:col-span-2 flex items-center gap-3">
                <button type="button" id="btnResetFilter" class="flex-1 h-9 px-4 text-xs font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xs border border-slate-200 dark:border-gray-700 transition-all active:scale-95">
                    <i class="fa-solid fa-rotate-left mr-1"></i> Reset
                </button>
                <button type="button" id="btnExport" class="flex-1 h-9 inline-flex items-center justify-center px-4 bg-emerald-600 border border-transparent text-white text-xs font-medium rounded-xs transition-all hover:bg-emerald-700 gap-2">
                    <i class="fa-solid fa-file-excel"></i> Export PR Draft
                </button>
            </div>
        </div>
    </div>

    {{-- Table Section --}}
    <div class="bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-gray-700 bg-slate-50/50 dark:bg-slate-900/30 flex items-center justify-between">
            <h3 class="text-xs font-bold text-gray-900 dark:text-white flex items-center">
                <i class="fa-solid fa-list-check mr-3 text-primary-600"></i> Part Shortage List
            </h3>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-500 shadow-sm shadow-red-200"></span>
                    <span class="text-[10px] font-medium text-slate-600 dark:text-gray-400 uppercase tracking-tight">Critical</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 shadow-sm shadow-amber-200"></span>
                    <span class="text-[10px] font-medium text-slate-600 dark:text-gray-400 uppercase tracking-tight">Warning</span>
                </div>
            </div>
        </div>
        <div class="p-0">
            <x-table id="prTable">
                <thead>
                    <tr>
                        <th class="px-6 py-4 w-12 text-center text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">No</th>
                        <th class="px-6 py-4 w-40 text-center text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Model / Customer</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Part Details</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Specifications</th>
                        <th class="px-6 py-4 w-24 text-center text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Stock</th>
                        <th class="px-6 py-4 w-24 text-center text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Min Stock</th>
                        <th class="px-6 py-4 w-24 text-center text-xs font-bold text-red-600 border-b border-gray-200 dark:border-gray-700">Shortage</th>
                        <th class="px-6 py-4 w-32 text-center text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Status</th>
                        <th class="px-6 py-4 w-20 text-center text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </x-table>
        </div>
    </div>
</div>

{{-- Detail Modal --}}
<div id="detailModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-[60] justify-center items-center w-full h-full bg-slate-900/50 p-4 overflow-y-auto">
    <div class="relative w-full max-w-2xl">
        <div class="relative bg-white dark:bg-gray-800 rounded-xs shadow-2xl border border-slate-200 dark:border-gray-700 overflow-hidden scale-in">
            <div class="px-6 py-5 bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-slate-900 dark:text-white flex items-center gap-2">
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
                        <h4 id="det_part_no" class="text-2xl font-medium text-slate-900 dark:text-white tracking-tighter leading-none mb-1">-</h4>
                        <p id="det_material" class="text-xs text-slate-500 dark:text-slate-600 font-medium tracking-wide">-</p>
                    </div>
                    <div id="det_status_badge">-</div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Left: Stock Metrics --}}
                    <div class="space-y-4">
                        <label class="block text-md font-medium text-slate-900 dark:text-slate-500 border-b border-slate-100 dark:border-gray-700 pb-2">Inventory Balance</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-white dark:bg-gray-900/50 rounded-xs border border-slate-100 dark:border-gray-700">
                                <p class="text-sm text-slate-900 font-medium mb-1">Stock</p>
                                <p id="det_current" class="text-md font-medium text-slate-700 dark:text-gray-200 leading-none">-</p>
                            </div>
                            <div class="p-4 bg-white dark:bg-gray-900/50 rounded-xs border border-slate-100 dark:border-gray-700">
                                <p class="text-sm text-slate-900 font-medium mb-1">Target</p>
                                <p id="det_target" class="text-md font-medium text-slate-700 dark:text-gray-200 leading-none">-</p>
                            </div>
                            <div class="col-span-2 p-5 bg-red-50/50 dark:bg-red-900/10 rounded-xs border border-red-100/50 dark:border-red-900/30">
                                <p class="text-[9px] text-red-500 font-bold uppercase mb-1 flex items-center gap-1.5">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Material Shortage
                                </p>
                                <div class="flex items-baseline gap-1">
                                    <p id="det_shortage" class="text-3xl font-medium text-slate-700 dark:text-slate-500 leading-none">-</p>
                                    <span class="text-[10px] font-medium text-slate-700 dark:text-slate-500 uppercase tracking-widest">PCS</span>
                                </div>
                                <p class="text-[9px] text-red-400/80 font-medium mt-2 leading-relaxed">Required replenishment to maintain minimum safety level.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Right: Traceability --}}
                    <div class="space-y-4">
                        <label class="block text-md font-medium text-slate-900 dark:text-slate-500 border-b border-slate-100 dark:border-gray-700 pb-2">Part Traceability</label>
                        <div class="space-y-4">
                            <div class="flex justify-between items-start border-b border-slate-50 dark:border-gray-700/50 pb-3">
                                <span class="text-xs text-slate-900 dark:text-slate-400 font-medium">Customer</span>
                                <span id="det_customer" class="text-xs font-bold text-slate-700 dark:text-gray-300 uppercase tracking-tight text-right">-</span>
                            </div>
                            <div class="flex justify-between items-start border-b border-slate-50 dark:border-gray-700/50 pb-3">
                                <span class="text-xs text-slate-900 dark:text-slate-400 font-medium">Model</span>
                                <span id="det_model" class="text-xs font-bold text-slate-700 dark:text-gray-300 uppercase tracking-tight text-right">-</span>
                            </div>
                            <div class="flex justify-between items-start border-b border-slate-50 dark:border-gray-700/50 pb-3">
                                <span class="text-xs text-slate-900 dark:text-slate-400 font-medium">Unit</span>
                                <span id="det_unit" class="text-xs font-bold text-slate-700 dark:text-gray-300 uppercase tracking-tight text-right">-</span>
                            </div>
                            <div class="flex justify-between items-start border-b border-slate-50 dark:border-gray-700/50 pb-3">
                                <span class="text-xs text-slate-900 dark:text-slate-400 font-medium">Pcs/Unit</span>
                                <span id="det_pcs_unit" class="text-xs font-bold text-slate-700 dark:text-gray-300 uppercase tracking-tight text-right">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 px-8 py-6 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-gray-700">
                <button type="button" class="close-modal-button flex-1 h-10 px-6 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 border border-slate-200 dark:border-gray-600 rounded-xs text-xs font-medium text-slate-500 dark:text-gray-400 transition-all active:scale-95 shadow-sm">
                    Close
                </button>
                <button type="button" class="flex-1 h-10 px-6 bg-primary-600 hover:bg-primary-700 rounded-xs text-xs font-medium text-white transition-all shadow-sm active:scale-95">
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
        dom: "<'flex flex-col sm:flex-row justify-between items-center mb-4 gap-4'<'flex items-center gap-4'l B> f><'overflow-x-auto w-full border border-slate-100 dark:border-gray-700/50 rounded-xs mb-2't><'flex flex-col md:flex-row justify-between items-center mt-4 gap-4 px-2'i p>",
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
                orderable: false,
                className: 'text-center text-gray-400', 
                render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1 
            },
            { 
                data: null,
                className: 'text-center',
                render: function(data, type, row) {
                    return `
                        <div class="text-xs font-medium text-slate-900 dark:text-gray-300 uppercase leading-none mb-1.5">${row.model || '-'}</div>
                        <div class="text-[10px] font-medium text-slate-600 dark:text-gray-500 uppercase tracking-widest pl-1">${row.customer || '-'}</div>
                    `;
                }
            },
            { 
                data: 'part_no', 
                className: 'py-3 min-w-[180px]',
                render: function(data, type, row) {
                    return `
                        <div class="font-medium text-slate-900 dark:text-white leading-tight uppercase tracking-tight">${data}</div>
                        <div class="text-[10px] text-slate-600 uppercase truncate max-w-[200px]">${row.part_name || '-'}</div>
                    `;
                }
            },
            { 
                data: 'material',
                className: 'text-xs text-slate-600 dark:text-gray-600',
                render: d => d || '-'
            },
            { 
                data: 'current_stock', 
                className: 'text-center font-medium text-slate-900 dark:text-white text-xs' 
            },
            { 
                data: 'min_stock', 
                className: 'text-center font-medium text-slate-900 dark:text-slate-900 text-xs' 
            },
            { 
                data: 'shortage', 
                className: 'text-center font-medium text-red-600 dark:text-red-400 text-xs' 
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
                    const label = (data || '').toUpperCase();
                    
                    return `<span class="px-3 py-1.5 rounded-xs text-[10px] font-medium border ${colorClass}">${label}</span>`;
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
            statusHtml = `<span class="px-4 py-1.5 text-[10px] font-bold rounded border-2 bg-red-50 text-red-700 border-red-200 italic">${data.status.toUpperCase()}</span>`;
        } else {
            statusHtml = `<span class="px-4 py-1.5 text-[10px] font-bold rounded border-2 bg-amber-50 text-amber-700 border-amber-200 italic">${data.status.toUpperCase()}</span>`;
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
