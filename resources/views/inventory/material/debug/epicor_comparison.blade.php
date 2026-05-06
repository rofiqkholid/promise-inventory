@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <div class="mb-8 flex justify-between items-end">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">
                    <li>Inventory</li>
                    <li><i class="fa-solid fa-chevron-right text-[8px] mx-1 text-gray-300"></i></li>
                    <li>Debug Tools</li>
                    <li><i class="fa-solid fa-chevron-right text-[8px] mx-1 text-gray-300"></i></li>
                    <li class="text-indigo-600">Epicor Sync</li>
                </ol>
            </nav>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Epicor vs Promise Sync</h1>
            <p class="text-gray-500 text-sm mt-1">Primary Source: Promise <b>products</b> table.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-white text-gray-700 border border-gray-200 rounded-xl hover:bg-gray-50 transition-all text-sm font-bold shadow-sm">
                <i class="fa-solid fa-house mr-2 text-gray-400"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Filters Area -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 tracking-widest">Customer</label>
                <select id="filter_customer" class="w-full border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 select2">
                    <option value="">All Customers</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->code }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 tracking-widest">Model</label>
                <select id="filter_model" class="w-full border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 select2">
                    <option value="">All Models</option>
                    @foreach($models as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 tracking-widest">Sync Status</label>
                <select id="filter_status" class="w-full border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    <option value="">All Status</option>
                    <option value="FOUND">FOUND</option>
                    <option value="NOT_FOUND">NOT FOUND</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button id="btn_filter" class="flex-1 h-[42px] bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-all text-sm font-bold shadow-lg shadow-indigo-100">
                    <i class="fa-solid fa-filter mr-2"></i> Filter
                </button>
                <button id="btn_export" class="flex-1 h-[42px] bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition-all text-sm font-bold shadow-lg shadow-emerald-100">
                    <i class="fa-solid fa-file-excel mr-2"></i> Export
                </button>
                <button id="btn_reset" class="w-[42px] h-[42px] bg-gray-100 text-gray-500 rounded-xl hover:bg-gray-200 transition-all flex items-center justify-center">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Table Area -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-50 bg-gray-50/30 flex justify-between items-center">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Data Comparison</span>
            <div id="table_search_container"></div>
        </div>
        <div class="p-4">
            <table id="epicorTable" class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-gray-400 border-b border-gray-50">
                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-widest">Part No</th>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-widest text-center">Status</th>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-widest">Vendor</th>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-widest text-right">Epicor Price</th>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-widest">Model</th>
                        <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-widest text-center">Cust</th>
                    </tr>
                </thead>
                <tbody class="text-sm"></tbody>
            </table>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    #epicorTable thead th { border-bottom: 2px solid #f9fafb !important; }
    #epicorTable tbody td { padding: 16px 12px !important; vertical-align: middle; border-bottom: 1px solid #f9fafb; }
    #epicorTable tbody tr:hover { background-color: #fcfdfe !important; }
    
    .dataTables_processing { 
        background: rgba(255,255,255,0.9) !important; 
        border: none !important; 
        box-shadow: none !important; 
        top: 50% !important;
    }
    .page-link { border: none !important; border-radius: 8px !important; margin: 0 2px; color: #64748b !important; font-size: 11px; font-weight: 700; }
    .page-item.active .page-link { background-color: #4f46e5 !important; color: white !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({ width: '100%' });

    const table = $('#epicorTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        pageLength: 15,
        ajax: {
            url: "{{ route('inventory.debug.epicor.data') }}",
            data: function(d) {
                d.customer_id = $('#filter_customer').val();
                d.model_id = $('#filter_model').val();
                d.status = $('#filter_status').val();
                d.search_part = $('.custom-search-input').val();
            }
        },
        columns: [
            { 
                data: 'promise_part_no',
                width: '25%',
                render: function(data, type, row) {
                    return `
                        <div>
                            <div class="font-bold text-gray-900 text-sm tracking-tight">${data}</div>
                            <div class="text-indigo-500 text-[10px] font-mono mt-1 opacity-80">Target: ${row.target_epicor}</div>
                        </div>`;
                }
            },
            { 
                data: 'status',
                width: '15%',
                className: 'text-center',
                render: function(data) {
                    if (data === 'FOUND') {
                        return `<span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100"><i class="fa-solid fa-check mr-1.5"></i> FOUND</span>`;
                    }
                    return `<span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100"><i class="fa-solid fa-xmark mr-1.5"></i> MISSING</span>`;
                }
            },
            { 
                data: 'vendor_id', 
                width: '10%',
                render: data => `<span class="text-gray-600 font-medium">${data}</span>` 
            },
            { 
                data: 'epicor_price',
                width: '20%',
                className: 'text-right px-4',
                render: function(data, type, row) {
                    if (data === null || data == 0) return `<span class="text-gray-300">—</span>`;
                    
                    let formattedPrice = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 4
                    }).format(data);

                    return `
                        <div class="flex flex-col items-end pr-2">
                            <span class="font-bold text-gray-900 text-sm">${formattedPrice}</span>
                            <div class="flex items-center gap-1.5 mt-1">
                                <span class="bg-indigo-50 text-indigo-500 text-[8px] font-black px-1.5 py-0.5 rounded uppercase tracking-tighter border border-indigo-100">/ ${row.epicor_pum || 'UNIT'}</span>
                                <div class="flex flex-col items-end leading-none">
                                    <span class="text-[7px] text-emerald-600 font-bold uppercase">Eff: ${row.epicor_effective}</span>
                                    <span class="text-[7px] text-rose-400 font-bold uppercase mt-0.5">Exp: ${row.epicor_expired}</span>
                                </div>
                            </div>
                        </div>`;
                }
            },
            { 
                data: 'model', 
                width: '15%',
                render: function(data, type, row) {
                    return `
                        <div>
                            <div class="font-bold text-gray-800">${data || '-'}</div>
                            <div class="text-[9px] font-bold text-gray-400 uppercase mt-1 tracking-widest">${row.project_status}</div>
                        </div>`;
                }
            },
            { 
                data: 'customer', 
                width: '10%',

                className: 'text-center',
                render: data => `<span class="bg-gray-100 text-gray-500 px-2 py-0.5 rounded text-[10px] font-bold">${data || '-'}</span>` 
            }
        ],
        order: [[0, 'asc']],
        language: {
            processing: '<div class="spinner-border text-indigo-600" role="status"></div>'
        },
        drawCallback: function() {
            $('.dataTables_paginate > .pagination').addClass('pagination-sm justify-content-end');
        }
    });

    // Custom Search Implementation
    $('#table_search_container').html('<div class="relative"><i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-300 text-xs"></i><input type="text" class="custom-search-input bg-white border-gray-200 rounded-xl pl-9 pr-4 py-1.5 text-xs w-64 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" placeholder="Search Part No..."></div>');

    $('.custom-search-input').on('keyup', function() {
        table.ajax.reload();
    });

    $('#btn_filter').on('click', function() {
        table.ajax.reload();
    });

    $('#btn_export').on('click', function() {
        const customer_id = $('#filter_customer').val();
        const model_id = $('#filter_model').val();
        const status = $('#filter_status').val();
        const search_part = $('.custom-search-input').val();
        
        let url = "{{ route('inventory.debug.epicor.export') }}";
        url += `?customer_id=${customer_id}&model_id=${model_id}&status=${status}&search_part=${search_part}`;
        
        window.location.href = url;
    });

    $('#btn_reset').on('click', function() {
        $('#filter_customer').val('').trigger('change');
        $('#filter_model').val('').trigger('change');
        $('#filter_status').val('');
        $('.custom-search-input').val('');
        table.ajax.reload();
    });
});
</script>
@endpush
@endsection
