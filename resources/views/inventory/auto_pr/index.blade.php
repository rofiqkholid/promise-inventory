@extends('layouts.app')
@section('title', 'Auto PR - Material Procurement')
@section('page_title', 'Auto Purchase Requisition')
@section('header-title', 'Auto PR')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">Auto Purchase Requisition</h2>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium italic">Automatically list items below minimum stock requiring material procurement.</p>
    </div>

    {{-- Stats Widgets --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 p-5 rounded-lg border border-slate-200 dark:border-gray-700 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-red-600 dark:text-red-400">
                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Urgent (Critical)</p>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white">{{ $stats['critical'] }}</h3>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-lg border border-slate-200 dark:border-gray-700 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600 dark:text-amber-400">
                <i class="fa-solid fa-circle-exclamation text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Replenish (Warning)</p>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white">{{ $stats['warning'] }}</h3>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="mb-6 p-5 bg-white dark:bg-gray-800 rounded-lg border border-slate-200 dark:border-gray-700">
        <div class="flex flex-col md:flex-row items-end gap-5">
            <div class="w-full md:w-64">
                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Customer</label>
                <select id="filterCustomer" class="select2-simple w-full">
                    <option value="">All Customers</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-3 ml-auto w-full md:w-auto mt-4 md:mt-0">
                <button type="button" id="btnResetFilter" class="flex-1 md:flex-none px-4 py-2.5 text-xs font-bold text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors flex items-center justify-center gap-2 uppercase tracking-wider">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </button>
                <div class="hidden md:block h-8 w-px bg-gray-100 dark:bg-gray-700 mx-1"></div>
                <button type="button" id="btnExport" class="flex-1 md:flex-none inline-flex items-center justify-center px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold uppercase tracking-widest rounded-md transition-all gap-2">
                    <i class="fa-solid fa-file-excel"></i> Export PR Draft
                </button>
            </div>
        </div>
    </div>

    {{-- DataTable Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-slate-200 dark:border-gray-700 overflow-hidden">
        <div class="p-1 px-4 py-3 bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-gray-700 flex items-center justify-between">
            <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Material Shortage List</h4>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tight">Critical</span>
                <span class="w-2 h-2 rounded-full bg-amber-500 ml-3"></span>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tight">Warning</span>
            </div>
        </div>
        <x-table id="autoPrTable">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Part Number & Rev</th>
                    <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Material Info</th>
                    <th scope="col" class="px-6 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Cust/Model</th>
                    <th scope="col" class="px-6 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Current (PCS)</th>
                    <th scope="col" class="px-6 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Min Stock</th>
                    <th scope="col" class="px-6 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Status</th>
                    <th scope="col" class="px-6 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700"></tbody>
        </x-table>
    </div>
</div>

    {{-- Detail Modal --}}
    <div id="detailModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 justify-center items-center w-full h-full bg-slate-900/50 backdrop-blur-sm flex">
        <div class="relative p-4 w-full max-w-2xl max-h-[90vh]">
            <div class="relative bg-white rounded-xl shadow-2xl dark:bg-gray-800 overflow-hidden border border-slate-200 dark:border-gray-700">
                <button type="button" class="close-modal-button text-gray-400 absolute top-4 right-4 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
                
                <div class="px-6 py-5 bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-gray-700">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-widest">Requirement Detail</h3>
                    <p class="text-[10px] text-gray-500 dark:text-gray-400 font-medium mt-0.5">Stock status and procurement recommendation</p>
                </div>

                <div class="p-8 space-y-8 overflow-y-auto max-h-[70vh] custom-scrollbar">
                    {{-- Header Info --}}
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 p-5 bg-blue-50/50 dark:bg-blue-900/10 rounded-xl border border-blue-100/50 dark:border-blue-800/30">
                        <div>
                            <h4 id="det_part_no" class="text-xl font-black text-slate-900 dark:text-white tracking-tight">-</h4>
                            <p id="det_material" class="text-xs text-slate-500 dark:text-gray-400 font-medium mt-1 uppercase tracking-wide">-</p>
                        </div>
                        <div id="det_status_badge" class="shrink-0">-</div>
                    </div>

                    {{-- Data Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        {{-- Left Side: Stock Info --}}
                        <div class="space-y-4">
                            <h5 class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest border-b border-gray-100 dark:border-gray-700 pb-2">Inventory Data</h5>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-3 bg-slate-50 dark:bg-gray-700/30 rounded-lg border border-slate-100 dark:border-gray-700">
                                    <p class="text-[9px] text-gray-400 font-bold uppercase mb-1">Current Stock</p>
                                    <p id="det_current" class="text-sm font-black text-slate-700 dark:text-gray-200">-</p>
                                </div>
                                <div class="p-3 bg-slate-50 dark:bg-gray-700/30 rounded-lg border border-slate-100 dark:border-gray-700">
                                    <p class="text-[9px] text-gray-400 font-bold uppercase mb-1">Target Min</p>
                                    <p id="det_target" class="text-sm font-black text-slate-700 dark:text-gray-200">-</p>
                                </div>
                                <div class="col-span-2 p-4 bg-red-50/50 dark:bg-red-900/10 rounded-lg border border-red-100/50 dark:border-red-800/30">
                                    <p class="text-[9px] text-red-400 font-bold uppercase mb-1">Missing Components (Shortage)</p>
                                    <p id="det_shortage" class="text-2xl font-black text-red-600 dark:text-red-400">-</p>
                                    <p class="text-[9px] text-red-400 font-medium mt-1">*Amount required to reach target level</p>
                                </div>
                            </div>
                        </div>

                        {{-- Right Side: Product Details --}}
                        <div class="space-y-4">
                            <h5 class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest border-b border-gray-100 dark:border-gray-700 pb-2">Product Trace</h5>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-400 font-medium">Customer</span>
                                    <span id="det_customer" class="font-bold text-slate-700 dark:text-gray-300">-</span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-400 font-medium">Model Code</span>
                                    <span id="det_model" class="font-bold text-slate-700 dark:text-gray-300">-</span>
                                </div>
                                <div class="flex items-center justify-between text-xs pt-2 border-t border-dashed border-gray-100 dark:border-gray-700">
                                    <span class="text-gray-400 font-medium">Measurement Unit</span>
                                    <span id="det_unit" class="font-bold text-slate-700 dark:text-gray-300">-</span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-400 font-medium">Pcs per Unit</span>
                                    <span id="det_pcs_unit" class="font-bold text-slate-700 dark:text-gray-300">-</span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-400 font-medium">Unit per Car</span>
                                    <span id="det_unit_car" class="font-bold text-slate-700 dark:text-gray-300">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 px-8 py-5 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-gray-700">
                    <button type="button" class="close-modal-button flex-1 px-6 py-3 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 border border-slate-200 dark:border-gray-600 rounded-lg text-[10px] font-black uppercase tracking-widest text-slate-600 dark:text-gray-400 transition-all">
                        Close Detail
                    </button>
                    <button type="button" class="flex-1 px-6 py-3 bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 rounded-lg text-[10px] font-black uppercase tracking-widest text-white transition-all">
                        Request PR
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
$(function() {
    const table = window.defaultDataTable('#autoPrTable', {
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("inventory.autoPr.data") }}',
            data: function(d) {
                d.customer_id = $('#filterCustomer').val();
            }
        },
        columns: [
            { 
                data: 'part_no', 
                render: function(data, type, row) {
                    return `<div class="font-black text-slate-900 dark:text-white text-xs">${data}</div>`;
                }
            },
            { 
                data: 'material',
                render: function(data, type, row) {
                    return `<div class="text-[11px] font-medium text-slate-600 dark:text-gray-400">${data}</div>`;
                }
            },
            { 
                data: null,
                className: 'text-center',
                render: function(data, type, row) {
                    return `<div class="text-[10px] font-bold text-slate-500 uppercase">${row.customer}</div><div class="text-[10px] text-slate-400">${row.model}</div>`;
                }
            },
            { 
                data: 'current_stock', 
                className: 'text-center font-mono font-bold text-xs' 
            },
            { 
                data: 'shortage', 
                className: 'text-center font-mono font-bold text-xs text-red-600 dark:text-red-400' 
            },
            { 
                data: 'status', 
                className: 'text-center',
                render: function(data, type, row) {
                    if (data === 'critical') {
                        return `<span class="px-2 py-0.5 text-[9px] font-black rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800 uppercase tracking-widest italic">CRITICAL</span>`;
                    } else if (data === 'warning') {
                        return `<span class="px-2 py-0.5 text-[9px] font-black rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800 uppercase tracking-widest italic">WARNING</span>`;
                    }
                    return `<span class="px-2 py-0.5 text-[9px] font-black rounded-full bg-slate-100 text-slate-700 uppercase italic">SAFE</span>`;
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    return `
                        <button class="view-detail-btn h-8 w-8 inline-flex items-center justify-center text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-md transition-all" title="View Details">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    `;
                }
            }
        ],
        drawCallback: function() {
            // Apply tooltips or other UI polish
        }
    });

    $('#filterCustomer').select2({
        width: '100%',
        placeholder: 'Select Customer...',
        allowClear: true
    }).on('change', function() {
        table.ajax.reload();
    });

    $('#btnResetFilter').on('click', function() {
        $('#filterCustomer').val('').trigger('change');
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
        $('#det_material').text(data.material);
        $('#det_customer').text(data.customer);
        $('#det_model').text(data.model);
        $('#det_current').text(data.current_stock + ' PCS');
        $('#det_target').text(data.min_stock_val || '-'); // Need actual val
        $('#det_shortage').text(data.shortage + ' PCS');
        
        // Use row internal data if available
        // Note: Controller needs to return more field for better detail
        
        let statusHtml = '';
        if (data.status === 'critical') {
            statusHtml = `<span class="px-4 py-1.5 text-[10px] font-black rounded-full bg-red-100 text-red-700 border-2 border-red-200 uppercase tracking-widest italic">CRITICAL</span>`;
        } else {
            statusHtml = `<span class="px-4 py-1.5 text-[10px] font-black rounded-full bg-amber-100 text-amber-700 border-2 border-amber-200 uppercase tracking-widest italic">WARNING</span>`;
        }
        $('#det_status_badge').html(statusHtml);

        $('#detailModal').removeClass('hidden').addClass('flex');
    });

    $('.close-modal-button').on('click', function() {
        $('#detailModal').addClass('hidden').removeClass('flex');
    });
    // Detail Modal Logic
    $(document).on('click', '.view-detail-btn', function() {
        const data = table.row($(this).closest('tr')).data();
        
        $('#det_part_no').text(data.part_no);
        $('#det_material').text(data.material);
        $('#det_customer').text(data.customer);
        $('#det_model').text(data.model);
        $('#det_current').text(data.current_stock + ' PCS');
        $('#det_target').text(data.min_stock_val);
        $('#det_shortage').text(data.shortage + ' PCS');
        $('#det_unit').text(data.unit_name);
        $('#det_pcs_unit').text(data.pcs_per_unit + ' PCS');
        $('#det_unit_car').text(data.unit_per_car + ' UNIT');
        
        let statusHtml = '';
        if (data.status === 'critical') {
            statusHtml = `<span class="px-4 py-1.5 text-[10px] font-black rounded-full bg-red-100 text-red-700 border-2 border-red-200 uppercase tracking-widest italic">CRITICAL</span>`;
        } else {
            statusHtml = `<span class="px-4 py-1.5 text-[10px] font-black rounded-full bg-amber-100 text-amber-700 border-2 border-amber-200 uppercase tracking-widest italic">WARNING</span>`;
        }
        $('#det_status_badge').html(statusHtml);

        $('#detailModal').removeClass('hidden').addClass('flex');
    });

    $('.close-modal-button').on('click', function() {
        $('#detailModal').addClass('hidden').removeClass('flex');
    });
});
</script>
@endpush
@endsection
