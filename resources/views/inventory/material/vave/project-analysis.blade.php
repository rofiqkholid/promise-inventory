@extends('layouts.app')
@section('title', 'Project VA/VE Analysis')
@section('page_title', 'Project Material Efficiency Analysis')
@section('header-title', 'Project VA/VE Analysis')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h2 class="text-xl xl:text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tighter leading-none">Project VA/VE Analysis</h2>
            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 font-normal">Compare EBD (Engineering Breakdown) data with production revisions to analyze material efficiency for Project models.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <button type="button" id="btnImportEbd" data-modal-target="importEbdModal" class="h-9 px-4 inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 border border-transparent text-white rounded-xs transition-all gap-2 active:scale-95 shadow-sm text-xs font-medium">
                <i class="fa-solid fa-file-import"></i> Import EBD Data
            </button>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="mb-6 p-5 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700">
        <div class="flex flex-col md:flex-row items-end gap-5">
            <div class="w-full md:w-64">
                <label class="block mb-2 text-[11px] font-bold text-slate-500 dark:text-gray-400">Customer</label>
                <select id="filterCustomer" class="select2-simple w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xs focus:ring-primary-500 focus:border-primary-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">All Customers</option>
                </select>
            </div>
            <div class="w-full md:w-64">
                <label class="block mb-2 text-[11px] font-bold text-slate-500 dark:text-gray-400">Model</label>
                <select id="filterModel" disabled class="select2-simple w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xs focus:ring-primary-500 focus:border-primary-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">All Models</option>
                </select>
            </div>
            <div class="w-full md:w-64">
                <label class="block mb-2 text-[11px] font-bold text-slate-500 dark:text-gray-400">EBD Bases (Export Only)</label>
                <select id="filterEbdBase" class="select2-simple w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xs focus:ring-primary-500 focus:border-primary-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">All Bases</option>
                </select>
            </div>
            <div class="flex items-center gap-3 ml-auto w-full md:w-auto mt-4 md:mt-0">
                <button type="button" id="btnResetFilter" class="h-9 px-4 inline-flex items-center justify-center rounded-xs bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-500 hover:text-primary-600 hover:bg-slate-50 transition-all text-xs font-medium active:scale-95">
                    <i class="fa-solid fa-rotate-left mr-2"></i> Reset
                </button>
                <div class="hidden md:block h-8 w-px bg-slate-100 dark:bg-gray-700 mx-1"></div>
                <button type="button" id="btnExportSummary" class="h-9 px-6 inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-xs transition-all gap-2 active:scale-95 shadow-sm">
                    <i class="fa-solid fa-file-excel text-sm"></i> Export Summary
                </button>
            </div>
        </div>
    </div>

    <x-table id="vaveTable">
        <thead>
            <tr>
                <th class="text-center w-16">No</th>
                <th class="text-left w-48 min-w-[180px]">Part No</th>
                <th class="text-left">Part Name</th>
                <th class="text-center">Customer</th>
                <th class="text-center">Model</th>
                <th class="text-center">EBD (Kg)</th>
                <th class="text-center">Latest (Kg)</th>
                <th class="text-center">Analysis Status</th>
                <th class="text-center w-[180px]">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

    @include('inventory.material.vave.partials.ebd_modal')
    @include('inventory.material.vave.partials.import_modal', ['isRegular' => false])
    @include('inventory.material.vave.partials.comparison_modal')
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Global VAVE Configuration
window.VAVE_CONFIG = {
    route_data: '{{ route("inventory.projectVaveAnalysis.data") }}',
    url_base: '{{ url("inventory/project-vave-analysis/base") }}',
    route_storeBase: '{{ route("inventory.projectVaveAnalysis.storeBase") }}',
    url_comparison: '{{ url("inventory/project-vave-analysis/comparison") }}',
    route_getBases: '{{ route("inventory.projectVaveAnalysis.getBases") }}',
    route_exportSummary: '{{ route("inventory.projectVaveAnalysis.exportSummary") }}',
    route_importExcel: '{{ route("inventory.projectVaveAnalysis.importExcel") }}',
    route_downloadTemplate: '{{ route("inventory.projectVaveAnalysis.downloadTemplate") }}'
};

$(function() {
    window.vaveTable = window.defaultDataTable('#vaveTable', {
        processing: true,
        serverSide: true,
        ajax: {
            url: VAVE_CONFIG.route_data,
            data: function(d) {
                d.customer_id = $('#filterCustomer').val();
                d.model_id = $('#filterModel').val();
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
                render: d => d ? parseFloat(d).toFixed(3) : '<span class="text-gray-400">-</span>'
            },
            { 
                data: 'latest_weight', 
                className: 'text-center font-mono',
                render: d => d ? parseFloat(d).toFixed(3) : '<span class="text-gray-400">-</span>'
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
                        <button class="rfq-button h-8 px-4 inline-flex items-center justify-center gap-2 text-primary-600 bg-primary-50 dark:bg-primary-900/20 dark:text-primary-400 border border-primary-100 dark:border-primary-800 rounded-xs hover:bg-primary-100 dark:hover:bg-primary-900/30 transition-all font-bold text-[10px] active:scale-95 min-w-[85px]" data-id="${row.hash_id}" title="Manage EBD (Engineering Breakdown)">
                            <i class="fa-solid fa-pen-to-square btn-icon"></i> <span class="btn-text">EBD</span>
                        </button>
                        <button class="compare-button h-8 px-4 inline-flex items-center justify-center gap-2 text-purple-600 bg-purple-50 dark:bg-purple-900/20 dark:text-purple-400 border border-purple-100 dark:border-purple-800 rounded-xs hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-all font-bold text-[10px] active:scale-95 min-w-[100px] ${!row.has_base ? 'opacity-30 grayscale cursor-not-allowed' : ''}" data-id="${row.hash_id}" ${!row.has_base ? 'disabled' : ''} title="VAVE Analysis Comparison">
                            <i class="fa-solid fa-chart-line btn-icon"></i> <span class="btn-text">Analysis</span>
                        </button>
                    </div>`
            }
        ]
    });

    // Initialize EBD Bases on load (independent of customer)
    function refreshEbdBases(customerId = null) {
        $.get(VAVE_CONFIG.route_getBases, { customer_id: customerId }, function(data) {
            const baseSelect = $('#filterEbdBase').empty().append('<option value="">All Bases</option>');
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

        refreshEbdBases();

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

        $('#btnResetFilter').on('click', function() {
            $('#filterCustomer').val('').trigger('change');
            $('#filterModel').val('').trigger('change').prop('disabled', true);
            $('#filterEbdBase').val('').trigger('change');
            refreshEbdBases(); // Reset to global bases
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
            dropdownParent: $('#importEbdModal'),
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
        const baseName = $('#filterEbdBase').val();

        let url = VAVE_CONFIG.route_exportSummary;
        let params = [];
        if (customerId) params.push(`customer_id=${customerId}`);
        if (modelId) params.push(`model_id=${modelId}`);
        if (baseName) {
            params.push(`base_names[]=${encodeURIComponent(baseName)}`);
        }
        if (params.length > 0) url += '?' + params.join('&');
        
        handleAjaxDownload($(this), url, 'VAVE_Project_Summary_' + new Date().getTime() + '.xlsx');
    });
});
</script>
@endpush
