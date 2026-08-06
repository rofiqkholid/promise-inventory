@extends('layouts.app')
@section('title', 'Regular VA/VE Analysis')
@section('page_title', 'Regular Material Efficiency Analysis')
@section('header-title', 'Regular VA/VE Analysis')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="mb-4">
        <h2 class="text-xl xl:text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tighter leading-none">Regular VA/VE Analysis</h2>
        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 font-normal">Compare SQ (Sales Quotation) data with production revisions to analyze material efficiency for Regular models.</p>
    </div>

    {{-- UNIFIED CARD WITH HEADER TOOLBAR & COLLAPSIBLE FILTER --}}
    <div id="vaveFilterCard" class="mb-0 bg-white dark:bg-gray-800 rounded-t-xs rounded-b-none border border-b-0 border-slate-200 dark:border-gray-700 overflow-hidden shadow-xs">
        {{-- Card Header Toolbar --}}
        <div class="px-4 sm:px-5 py-3 bg-slate-50/70 dark:bg-slate-900/40 border-b border-slate-100 dark:border-gray-700 flex flex-col md:flex-row md:items-center md:justify-between gap-2.5">
            {{-- Left: Filter Toggle Button --}}
            <div class="flex flex-col xs:flex-row sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto">
                <button type="button" id="btnToggleFilter" class="inline-flex items-center justify-center gap-2 px-3.5 h-9 bg-white dark:bg-gray-800 text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700 border border-slate-200 dark:border-gray-700 rounded-xs text-xs font-medium active:scale-[0.98] transition-all shadow-xs w-full xs:w-auto">
                    <i class="fa-solid fa-filter text-primary-600"></i>
                    <span>Filters</span>
                    <i id="vaveFilterChevron" class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-200 text-xs ml-1"></i>
                </button>
            </div>

            {{-- Right: Import & Export Buttons --}}
            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                @if(Auth::user()->hasMenuPermission('inventory.vave.index', 'create'))
                <button type="button" id="btnImportSq" data-modal-target="importSqModal" class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-3.5 h-9 bg-indigo-600 hover:bg-indigo-700 border border-transparent text-white rounded-xs transition-all active:scale-[0.98] shadow-xs text-xs font-medium">
                    <i class="fa-solid fa-file-import"></i>
                    <span class="truncate">Import SQ Data</span>
                </button>
                @endif
                <button type="button" id="btnExportSummary" class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-3.5 h-9 bg-emerald-600 hover:bg-emerald-700 border border-transparent text-white text-xs font-medium rounded-xs transition-all active:scale-[0.98] shadow-xs" title="Export Summary to Excel">
                    <i class="fa-solid fa-file-excel"></i>
                    <span class="truncate">Export Summary</span>
                </button>
            </div>
        </div>

        {{-- Collapsible Filter Body --}}
        <div id="vaveFilterBody" class="hidden p-4 sm:p-5 border-b border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                <div>
                    <label class="block mb-1.5 text-xs font-medium text-slate-700 dark:text-gray-300">Customer</label>
                    <select id="filterCustomer" class="select2-simple w-full bg-slate-50 border border-slate-200 text-slate-900 text-xs rounded-xs block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">All Customers</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1.5 text-xs font-medium text-slate-700 dark:text-gray-300">Model</label>
                    <select id="filterModel" disabled class="select2-simple w-full bg-slate-50 border border-slate-200 text-slate-900 text-xs rounded-xs block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">All Models</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1.5 text-xs font-medium text-slate-700 dark:text-gray-300">SQ Bases (Export Only)</label>
                    <select id="filterSqBase" class="select2-simple w-full bg-slate-50 border border-slate-200 text-slate-900 text-xs rounded-xs block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">All Bases</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1.5 text-xs font-medium text-slate-700 dark:text-gray-300">Analysis Status</label>
                    <select id="filterStatus" class="select2-simple w-full bg-slate-50 border border-slate-200 text-slate-900 text-xs rounded-xs block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">All Statuses</option>
                        <option value="MERIT">MERIT</option>
                        <option value="LOSS">LOSS</option>
                        <option value="NO CHANGE">NO CHANGE</option>
                        <option value="NO DATA">NO DATA</option>
                    </select>
                </div>
                <div>
                    <button type="button" id="btnResetFilter" class="w-full h-9 inline-flex items-center justify-center gap-1.5 px-3 bg-slate-100 dark:bg-gray-700/60 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-600 dark:text-gray-300 font-medium text-xs rounded-xs border border-slate-200 dark:border-gray-600 active:scale-[0.98] transition-all shadow-xs" title="Reset all filters">
                        <i class="fa-solid fa-rotate-left text-slate-400"></i>
                        <span class="truncate">Reset Filter</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Seamless Table Wrapper --}}
    <div class="[&>div]:rounded-t-none [&>div]:border-t-0">
        <x-table id="vaveTable">
            <thead>
                <tr>
                    <th class="text-center w-16">No</th>
                    <th class="text-left w-48 min-w-[180px]">Part No</th>
                    <th class="text-left">Part Name</th>
                    <th class="text-center">Customer</th>
                    <th class="text-center">Model</th>
                    <th class="text-center">SQ (Kg)</th>
                    <th class="text-center">Latest (Kg)</th>
                    <th class="text-center">Analysis Status</th>
                    <th class="text-center w-[180px]">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </x-table>
    </div>
</div>

    @include('inventory.material.vave.partials.sq_modal')
    @include('inventory.material.vave.partials.import_modal', ['isRegular' => true])
    @include('inventory.material.vave.partials.comparison_modal', ['isRegular' => true])
@endsection

@push('push_styles')
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Global VAVE Configuration
window.VAVE_CONFIG = {
    route_data: '{{ route("inventory.regularVaveAnalysis.data") }}',
    url_base: '{{ url("inventory/regular-vave-analysis/base") }}',
    route_storeBase: '{{ route("inventory.regularVaveAnalysis.storeBase") }}',
    url_comparison: '{{ url("inventory/regular-vave-analysis/comparison") }}',
    route_getBases: '{{ route("inventory.regularVaveAnalysis.getBases") }}',
    route_exportSummary: '{{ route("inventory.regularVaveAnalysis.exportSummary") }}',
    route_importExcel: '{{ route("inventory.regularVaveAnalysis.importExcel") }}',
    route_downloadTemplate: '{{ route("inventory.regularVaveAnalysis.downloadTemplate") }}'
};

$(function() {
    // Collapsible Filter Drawer Toggle
    $('#btnToggleFilter').on('click', function(e) {
        e.stopPropagation();
        $('#vaveFilterBody').slideToggle(200);
        $('#vaveFilterChevron').toggleClass('rotate-180');
        $(this).toggleClass('bg-primary-50 dark:bg-primary-950/60 border-primary-300 dark:border-primary-700 text-primary-700 dark:text-primary-300');
    });

    window.vaveTable = window.defaultDataTable('#vaveTable', {
        processing: true,
        serverSide: true,
        ajax: {
            url: VAVE_CONFIG.route_data,
            data: function(d) {
                d.customer_id = $('#filterCustomer').val();
                d.model_id = $('#filterModel').val();
                d.status = $('#filterStatus').val();
            }
        },
        columns: [
            { data: 'id', orderable: false, className: 'text-center', render: (d, t, r, m) => m.row + 1 },
            { data: 'part_no', className: 'font-medium' },
            { data: 'part_name' },
            { data: 'customer_code', className: 'text-center' },
            { data: 'model_name', className: 'text-center' },
            { 
                data: 'baseline_weight', 
                className: 'text-center font-mono',
                render: (d, t, r) => {
                    if (!d) return '<span class="text-gray-400">-</span>';
                    let html = `<div>${parseFloat(d).toFixed(3)}</div>`;
                    if (r.base_unit_name) {
                        html += `<div class="text-[10px] text-slate-500 dark:text-slate-400 font-sans mt-0.5">${r.base_unit_name}</div>`;
                    }
                    return html;
                }
            },
            { 
                data: 'latest_weight', 
                className: 'text-center font-mono',
                render: (d, t, r) => {
                    if (!d) return '<span class="text-gray-400">-</span>';
                    let html = `<div>${parseFloat(d).toFixed(3)}</div>`;
                    if (r.latest_unit_name) {
                        html += `<div class="text-[10px] text-slate-500 dark:text-slate-400 font-sans mt-0.5">${r.latest_unit_name}</div>`;
                    }
                    return html;
                }
            },
            { 
                data: 'status', 
                className: 'text-center',
                render: (d, t, r) => {
                    if (d === 'MERIT') {
                        return `<div class="flex flex-col items-center gap-1">
                            <span class="px-3 py-1 text-[9px] font-black rounded-xs bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 uppercase tracking-[0.1em]">MERIT</span>
                            <span class="text-[9px] text-emerald-600 dark:text-emerald-500 font-bold tracking-tight italic">${r.diff_pct.toFixed(1)}% Saving</span>
                        </div>`;
                    } else if (d === 'LOSS') {
                        return `<div class="flex flex-col items-center gap-1">
                            <span class="px-3 py-1 text-[9px] font-black rounded-xs bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800 uppercase tracking-[0.1em]">LOSS</span>
                            <span class="text-[9px] text-red-600 dark:text-red-500 font-bold tracking-tight italic">${r.diff_pct.toFixed(1)}% Loss</span>
                        </div>`;
                    } else if (d === 'NO CHANGE') {
                        return `<div class="flex flex-col items-center gap-1">
                            <span class="px-3 py-1 text-[9px] font-black rounded-xs bg-slate-50 text-slate-500 dark:bg-gray-800 dark:text-gray-400 border border-slate-200 dark:border-gray-700 uppercase tracking-[0.1em]">NO CHANGE</span>
                            <span class="text-[9px] text-slate-400 dark:text-slate-500 font-bold tracking-tight italic">Same Weight</span>
                        </div>`;
                    } else {
                        return `<span class="px-3 py-1 text-[9px] font-black rounded-xs bg-slate-50 text-slate-500 dark:bg-gray-800 dark:text-gray-400 border border-slate-200 dark:border-gray-700 uppercase tracking-[0.1em]">NO DATA</span>`;
                    }
                }
            },
            {
                data: null,
                orderable: false,
                render: row => `
                    <div class="flex items-center justify-center gap-1.5">
                        <button class="sq-button h-8 px-4 inline-flex items-center justify-center gap-2 text-primary-600 bg-primary-50 dark:bg-primary-900/20 dark:text-primary-400 border border-primary-100 dark:border-primary-800 rounded-xs hover:bg-primary-100 dark:hover:bg-primary-900/30 transition-all font-bold text-[10px] active:scale-95 min-w-[85px]" data-id="${row.hash_id}" title="Manage SQ (Sales Quotation)">
                            <i class="fa-solid fa-pen-to-square btn-icon"></i> <span class="btn-text">SQ</span>
                        </button>
                        <button class="compare-button h-8 px-4 inline-flex items-center justify-center gap-2 text-purple-600 bg-purple-50 dark:bg-purple-900/20 dark:text-purple-400 border border-purple-100 dark:border-purple-800 rounded-xs hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-all font-bold text-[10px] active:scale-95 min-w-[100px] ${!row.has_base ? 'opacity-30 grayscale cursor-not-allowed' : ''}" data-id="${row.hash_id}" ${!row.has_base ? 'disabled' : ''} title="VAVE Analysis Comparison">
                            <i class="fa-solid fa-chart-line btn-icon"></i> <span class="btn-text">Analysis</span>
                        </button>
                    </div>`
            }
        ]
    });

    // Initialize SQ Bases on load (independent of customer)
    function refreshSqBases(customerId = null) {
        $.get(VAVE_CONFIG.route_getBases, { customer_id: customerId }, function(data) {
            const baseSelect = $('#filterSqBase').empty().append('<option value="">All Bases</option>');
            data.forEach(name => {
                baseSelect.append(`<option value="${name}">${name}</option>`);
            });
        });
    }

    // Populate Master Filters
    function loadMainFilters() {
        $.get('{{ route("inventory.master.product.getCustomers") }}', function(data) {
            data.forEach(c => {
                $('#filterCustomer').append(`<option value="${c.id}">${c.code}</option>`);
            });
        });

        refreshSqBases();

        $('#filterCustomer').on('change', function() {
            const customerId = $(this).val();
            $('#filterModel').empty().append('<option value="">All Models</option>');
            
            if (customerId) {
                $('#filterModel').prop('disabled', false);
                $.get('{{ route("inventory.master.product.getModels") }}', { customer_id: customerId }, function(data) {
                    data.forEach(m => {
                        $('#filterModel').append(`<option value="${m.id}">${m.name}</option>`);
                    });
                    window.vaveTable.ajax.reload();
                });
            } else {
                $('#filterModel').prop('disabled', true);
                window.vaveTable.ajax.reload();
            }
        });

        $('#filterModel').on('change', function() {
            window.vaveTable.ajax.reload();
        });

        $('#filterStatus').on('change', function() {
            window.vaveTable.ajax.reload();
        });

        $('#btnResetFilter').on('click', function() {
            $('#filterCustomer').val('').trigger('change');
            $('#filterModel').val('').trigger('change').prop('disabled', true);
            $('#filterSqBase').val('').trigger('change');
            $('#filterStatus').val('').trigger('change');
            refreshSqBases(); // Reset to global bases
            window.vaveTable.ajax.reload();
        });
        
        $('.select2-simple').select2({
            width: '100%',
            placeholder: 'Select...',
            allowClear: true
        });

        $('.select2-multiple').select2({
            width: '100%',
            placeholder: 'All Versions',
            allowClear: true,
            closeOnSelect: false
        });

        $('.select2-import').select2({
            dropdownParent: $('#importSqModal'),
            width: '100%',
            placeholder: 'Select...',
        });
    }

    // Populate Master Filters
    loadMainFilters();

    // Helper to handle AJAX download with blob (precise spinner control)
    function handleAjaxDownload($btn, url, fileNameDefault) {
        const originalHtml = $btn.html();
        
        $btn.prop('disabled', true).addClass('opacity-70 cursor-wait');
        if($btn.find('.btn-icon').length) {
            $btn.find('.btn-icon').attr('class', 'fa-solid fa-circle-notch fa-spin');
            if($btn.find('.btn-text').length) $btn.find('.btn-text').text('Processing...');
        } else {
            $btn.html('<i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Processing...');
        }

        $.ajax({
            url: url,
            method: 'GET',
            xhrFields: { responseType: 'blob' },
            success: function(data, status, xhr) {
                const contentType = xhr.getResponseHeader('content-type');
                const blob = new Blob([data], { type: contentType });
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                
                let fileName = fileNameDefault;
                const disposition = xhr.getResponseHeader('Content-Disposition');
                if (disposition && disposition.indexOf('attachment') !== -1) {
                    const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                    const matches = filenameRegex.exec(disposition);
                    if (matches != null && matches[1]) {
                        fileName = matches[1].replace(/['"]/g, '');
                    }
                }
                
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(link.href);
            },
            error: function() {
                window.showToast('Error downloading file', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).removeClass('opacity-70 cursor-wait').html(originalHtml);
            }
        });
    }

    // Handle Export Summary dengan AJAX Blob
    $('#btnExportSummary').on('click', function() {
        const customerId = $('#filterCustomer').val();
        const modelId = $('#filterModel').val();
        const baseName = $('#filterSqBase').val();

        let url = VAVE_CONFIG.route_exportSummary;
        let params = [];
        if (customerId) params.push(`customer_id=${customerId}`);
        if (modelId) params.push(`model_id=${modelId}`);
        if (baseName) {
            params.push(`base_names[]=${encodeURIComponent(baseName)}`);
        }
        if (params.length > 0) url += '?' + params.join('&');
        
        handleAjaxDownload($(this), url, 'VAVE_Regular_Summary_' + new Date().getTime() + '.xlsx');
    });
});
</script>
@endpush
