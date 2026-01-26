@extends('layouts.app')
@section('title', 'Stock Monitoring')
@section('page_title', 'Stock Monitoring')
@section('header-title', 'Stock Monitoring')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Stock Monitoring Report</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Monitoring stock balance and breakdown of usage.</p>
        </div>

        {{-- Action Toolbar --}}
        <div class="flex items-center gap-3 mt-4 sm:mt-0 relative">
            <button id="btnToggleFilter" class="w-10 h-10 rounded-full bg-white dark:bg-gray-800 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 shadow-sm border border-gray-200 dark:border-gray-700 flex items-center justify-center transition-all duration-200 hover:shadow-md p-0 leading-none" title="Toggle Filters">
                <i class="fa-solid fa-filter text-base"></i>
            </button>
            
            <div class="relative">
                <button id="toggleLegend" class="w-10 h-10 rounded-full bg-white dark:bg-gray-800 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 shadow-sm border border-gray-200 dark:border-gray-700 flex items-center justify-center transition-all duration-200 hover:shadow-md p-0 leading-none" title="Legend & Help">
                    <i class="fa-solid fa-question text-base"></i>
                </button>

                {{-- Legend Popover Content --}}
                <div id="legendPopover" class="hidden absolute right-0 top-full mt-2 w-72 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-4 z-50">
                    <h4 class="font-bold text-gray-900 dark:text-gray-100 text-xs uppercase tracking-wider mb-3">Stock Status</h4>
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 mr-2 flex-shrink-0"></span>
                            <div class="text-gray-600 dark:text-gray-300">Over <span class="text-gray-400">(&gt; Max)</span></div>
                        </div>
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-2 flex-shrink-0"></span>
                            <div class="text-gray-600 dark:text-gray-300">Safe <span class="text-gray-400">(Min - Max)</span></div>
                        </div>
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 mr-2 flex-shrink-0"></span>
                            <div class="text-gray-600 dark:text-gray-300">Warning <span class="text-gray-400">(Min-30 - Min-1)</span></div>
                        </div>
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500 mr-2 flex-shrink-0 animate-pulse"></span>
                            <div class="text-gray-600 dark:text-gray-300">Critical <span class="text-gray-400">(&lt; Min-30)</span></div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 dark:border-gray-700 my-3"></div>

                    <h4 class="font-bold text-gray-900 dark:text-gray-100 text-xs uppercase tracking-wider mb-3">Trial Validation</h4>
                    <div class="space-y-2">
                        <div class="flex items-center text-xs">
                            <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 font-bold mr-2 text-[10px]">WARN</span>
                            <div class="text-gray-600 dark:text-gray-300">Near Limit <span class="text-gray-400">(&gt; Limit-50)</span></div>
                        </div>
                        <div class="flex items-center text-xs">
                            <span class="px-1.5 py-0.5 rounded bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300 font-bold mr-2 text-[10px]">CRIT</span>
                            <div class="text-gray-600 dark:text-gray-300">Over Limit <span class="text-gray-400">(&gt; Limit)</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Collapsible Filter Card --}}
    <div id="filterCard" class="hidden bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5 mb-6 relative">
        <div class="absolute -top-2 right-14 w-0 h-0 border-l-[10px] border-l-transparent border-r-[10px] border-r-transparent border-b-[10px] border-b-gray-200 dark:border-b-gray-700"></div>
        <div class="absolute -top-[7px] right-14 w-0 h-0 border-l-[10px] border-l-transparent border-r-[10px] border-r-transparent border-b-[10px] border-b-white dark:border-b-gray-800"></div>
        
        <div class="flex flex-col xl:flex-row gap-4 xl:items-end">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 flex-1">
                <!-- Stock Status -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stock Status</label>
                    <div class="w-full">
                        <select id="filter_status" class="select2 w-full">
                            <option value="">All Status</option>
                            <option value="safe">Safe Stock</option>
                            <option value="warning">Warning</option>
                            <option value="danger">Critical</option>
                            <option value="over">Over Stock</option>
                        </select>
                    </div>
                </div>

                        <!-- Customer -->
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</label>
                            <div class="w-full">
                                <select id="filter_customer" class="select2 w-full">
                                    <option value="">All Customers</option>
                                    @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                <!-- Model -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Model</label>
                    <div class="w-full">
                        <select id="filter_model" class="select2 w-full">
                            <option value="">All Models</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex gap-2 pt-2 xl:pt-0">
                <button type="button" id="reset_filters" class="flex items-center justify-center gap-2 h-[38px] px-6 w-full md:w-auto bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 rounded-md transition-colors text-sm font-semibold">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </button>
            </div>
        </div>
    </div>
    
    {{-- Individual KPI Cards - Forced Single Row --}}
    <div class="flex flex-nowrap gap-3 mb-6 overflow-x-auto pb-2">
        <!-- Total -->
        <div class="flex-none w-[200px] flex-grow bg-white dark:bg-gray-800 p-3 rounded-md border border-gray-200 dark:border-gray-700 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center text-purple-500 text-lg">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div>
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Total Products</div>
                <div class="text-xl font-black text-gray-900 dark:text-white leading-tight">{{ number_format($stats['total'] ?? 0) }}</div>
            </div>
        </div>

        <!-- Safe -->
        <div class="flex-none w-[200px] flex-grow bg-white dark:bg-gray-800 p-3 rounded-md border border-gray-200 dark:border-gray-700 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-500 text-lg">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Safe Stock</div>
                <div class="text-xl font-black text-gray-900 dark:text-white leading-tight">{{ number_format($stats['safe'] ?? 0) }}</div>
            </div>
        </div>

        <!-- Warning -->
        <div class="flex-none w-[200px] flex-grow bg-white dark:bg-gray-800 p-3 rounded-md border border-gray-200 dark:border-gray-700 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-500 text-lg">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Warning</div>
                <div class="text-xl font-black text-gray-900 dark:text-white leading-tight">{{ number_format($stats['warning'] ?? 0) }}</div>
            </div>
        </div>

        <!-- Critical -->
        <div class="flex-none w-[200px] flex-grow bg-white dark:bg-gray-800 p-3 rounded-md border border-gray-200 dark:border-gray-700 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-red-500 text-lg">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div>
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Critical</div>
                <div class="text-xl font-black text-gray-900 dark:text-white leading-tight">{{ number_format($stats['danger'] ?? 0) }}</div>
            </div>
        </div>

        <!-- Over Stock -->
        <div class="flex-none w-[200px] flex-grow bg-white dark:bg-gray-800 p-3 rounded-md border border-gray-200 dark:border-gray-700 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-500 text-lg">
                <i class="fa-solid fa-arrow-trend-up"></i>
            </div>
            <div>
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Over Stock</div>
                <div class="text-xl font-black text-gray-900 dark:text-white leading-tight">{{ number_format($stats['over'] ?? 0) }}</div>
            </div>
        </div>
    </div>



    {{-- DataTable --}}
    <x-table id="stockMonitoringTable">
        <thead>
            <tr>
                <th rowspan="2" class="px-6 py-3 w-16 border-b dark:border-gray-600 align-middle">No</th>
                <th rowspan="2" class="px-6 py-3 border-b dark:border-gray-600 align-middle">Part No</th>
                <th rowspan="2" class="px-6 py-3 border-b dark:border-gray-600 align-middle">Spec & Size</th>
                <th rowspan="2" class="px-6 py-3 border-b dark:border-gray-600 align-middle">Remark</th>
                <th colspan="1" class="px-6 py-3 border-b dark:border-gray-600 text-center bg-gray-100 dark:bg-gray-700 dark:text-gray-200">Stock Level</th>
                <th colspan="{{ max(1, $categories->count()) + 1 }}" class="px-6 py-3 border-b dark:border-gray-600 text-center bg-red-50 dark:bg-red-900/40 dark:text-red-200">History & Adjustment (Pcs / Unit)</th>
                <th rowspan="2" class="px-6 py-3 border-b dark:border-gray-600 align-middle text-center">Action</th>
            </tr>
            <tr>
                <th class="px-6 py-3 border-b dark:border-gray-600 text-center">Balance</th>

                @foreach($categories as $cat)
                <th class="px-6 py-3 border-b dark:border-gray-600 text-center text-xs whitespace-nowrap">{{ $cat->code }}</th>
                @endforeach
                
                <th class="px-6 py-3 border-b dark:border-gray-600 text-center bg-gray-50/50 dark:bg-gray-800/50">STO</th>

                @if($categories->count() === 0)
                <th class="px-6 py-3 border-b dark:border-gray-600 text-center text-xs">-</th>
                @endif
            </tr>
        </thead>
        </tbody>
    </x-table>

    {{-- Popover Components --}}
    <x-inventory.sto-log-popover />
    <x-inventory.product-detail-popover />
</div>
@endsection

@push('style')
<style>
    /* Tooltip content style */
    #global-tooltip-portal {
        pointer-events: none;
        display: none;
    }
    
    /* Dashboard-style Select2 Override */
    .select2-container .select2-selection--single {
        height: 38px !important; 
        font-size: 14px !important;
        display: flex !important;
        align-items: center !important;
        border-color: #d1d5db !important; /* gray-300 to match inputs */
        border-radius: 0.375rem !important; /* rounded-md/lg */
    }
    .dark .select2-container .select2-selection--single {
        background-color: #374151 !important; /* gray-700 */
        border-color: #4b5563 !important; /* gray-600 */
        color: #e5e7eb !important;
    }
    .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #e5e7eb !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
        padding-left: 12px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 4px !important;
    }
    .select2-dropdown {
        font-size: 13px !important;
        border-color: #d1d5db !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // DataTable Configuration
        let columns = [{
                data: null,
                render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1
            },
            {
                data: 'part_no',
                render: function(data, type, row) {
                    if (type === 'display') {
                        if (row.details) {
                            const details = JSON.stringify(row.details).replace(/"/g, '&quot;');
                            return `<div class="hover-trigger cursor-pointer font-medium text-blue-600 dark:text-blue-400" data-details="${details}">${data}</div>`;
                        }
                        return `<div class="font-medium text-gray-700 dark:text-gray-300">${data}</div>`;
                    }
                    return data;
                }
            },
            {
                data: 'spec_size'
            },
            {
                data: 'remark',
                name: 'inv_t_product_detail.remark',
                defaultContent: '-',
                className: 'text-xs text-gray-500'
            },
            {
                data: 'balance_pcs',
                className: 'text-center font-bold',
                render: function(data, type, row) {
                    if (type === 'display') {
                        let colorClass = 'text-gray-900 dark:text-gray-100';
                        let indicator = '';
                        const status = row.stock_status || 'default';

                        if (status === 'safe') {
                            colorClass = 'text-emerald-600 dark:text-emerald-400';
                            indicator = '<span class="inline-block w-2 h-2 rounded-full bg-emerald-500 mr-1"></span>';
                        } else if (status === 'warning') {
                            colorClass = 'text-amber-600 dark:text-amber-400';
                            indicator = '<span class="inline-block w-2 h-2 rounded-full bg-amber-500 mr-1"></span>';
                        } else if (status === 'danger') {
                            colorClass = 'text-red-600 dark:text-red-400';
                            indicator = '<span class="inline-block w-2 h-2 rounded-full bg-red-500 mr-1 animate-pulse"></span>';
                        } else if (status === 'over') {
                            colorClass = 'text-blue-600 dark:text-blue-400';
                            indicator = '<span class="inline-block w-2 h-2 rounded-full bg-blue-500 mr-1"></span>';
                        }

                        const mainVal = row.balance_pcs;
                        const unitSub = row.current_qty + ' ' + row.balance_unit;
                        const breakdown = `In: ${row.total_in} | Out: ${row.total_out} | STO: ${row.sto_gap_plain}`;

                        return `
                            <div class="flex flex-col items-center justify-center ${colorClass}" title="${breakdown}">
                                <div class="flex items-center">
                                    ${indicator}
                                    <span>${mainVal}</span>
                                </div>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400 font-normal">(${unitSub})</span>
                            </div>
                        `;
                    }
                    return data;
                }
            }
        ];

        const stoColumnDef = {
            data: 'sto_gap_display',
            className: 'text-center font-bold',
            render: function(data, type, row) {
                if (type === 'display') {
                    if (data === '-') return data;
                    let colorClass = 'text-gray-600 dark:text-gray-400';
                    if (parseFloat(row.sto_gap) > 0) colorClass = 'text-green-600 dark:text-green-400';
                    else if (parseFloat(row.sto_gap) < 0) colorClass = 'text-red-600 dark:text-red-400';
                    
                    return `<span class="${colorClass} sto-log-trigger cursor-pointer hover:underline decoration-dotted underline-offset-4" data-id="${row.id}">${data} <i class="fa-solid fa-caret-down text-[10px] ml-0.5 opacity-50"></i></span>`;
                }
                return data;
            }
        };

        const categories = <?php echo json_encode(isset($categories) ? $categories->values() : []); ?>;

        if (Array.isArray(categories) && categories.length > 0) {
            categories.forEach(cat => {
                let safeCode = cat.code.replace(/[^a-zA-Z0-9]/g, '_');
                let alias = 'usage_' + safeCode;

                if (cat.is_trial) {
                    columns.push({
                        data: alias,
                        className: 'text-center',
                        defaultContent: '-',
                        render: function(data, type, row) {
                            if (type === 'display' && row.trial_status) {
                                let bgClass = '';
                                if (row.trial_status === 'danger') bgClass = 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300 rounded px-1 font-bold';
                                else if (row.trial_status === 'warning') bgClass = 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 rounded px-1 font-bold';

                                if (bgClass) return `<span class="${bgClass}">${data}</span>`;
                            }
                            return data ? data : '-';
                        }
                    });
                } else {
                    columns.push({
                        data: alias,
                        className: 'text-center',
                        defaultContent: '-'
                    });
                }
            });
        }
        
        columns.push(stoColumnDef);

        if (!(Array.isArray(categories) && categories.length > 0)) {
            columns.splice(columns.length - 1, 0, {
                data: null,
                defaultContent: '-',
                className: 'text-center'
            });
        }

        // Add Action Column
        columns.push({
            data: null,
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: function(data, type, row) {
                return `
                    <button class="print-balance-button h-7 w-7 inline-flex items-center justify-center text-purple-600 rounded bg-purple-50 hover:bg-purple-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-purple-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500" data-id="${row.hash_id}" title="Print Balance Label">
                        <i class="fa-solid fa-print text-xs"></i>
                    </button>
                `;
            }
        });

        const table = window.defaultDataTable('stockMonitoringTable', {
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('inventory.stockMonitoring.data') }}",
                data: function(d) {
                    d.stock_status = $('#filter_status').val();
                    d.customer_id = $('#filter_customer').val();
                    d.model_id = $('#filter_model').val();
                }
            },
            columns: columns,
            pageLength: 25,
            createdRow: function(row, data, dataIndex) {
                if (data.stock_status === 'danger') {
                    $(row).addClass('bg-red-50 dark:bg-red-900/10');
                } else if (data.stock_status === 'warning') {
                    $(row).addClass('bg-amber-50 dark:bg-amber-900/10');
                } else if (data.stock_status === 'over') {
                    $(row).addClass('bg-blue-50 dark:bg-blue-900/10');
                }
            }
        });

        // Search from URL parameter initialization
        const urlParams = new URLSearchParams(window.location.search);
        const searchVal = urlParams.get('search');
        if (searchVal) {
            table.search(searchVal).draw();
        }

        // Filter Toggle Logic
        $('#btnToggleFilter').on('click', function(e) {
            e.stopPropagation();
            const btn = $(this);
            const card = $('#filterCard');
            
            card.slideToggle(200);
            btn.toggleClass('bg-blue-50 text-blue-600 ring-2 ring-blue-500/50');
            
            // Close Legend if open
            if (!$('#legendPopover').hasClass('hidden')) {
                $('#legendPopover').addClass('hidden');
            }
        });
        
        // Close Filter Card when clicking outside
        $(document).click(function(e) {
            if (!$(e.target).closest('#filterCard, #btnToggleFilter, .select2-container').length) {
                $('#filterCard').slideUp(200);
                $('#btnToggleFilter').removeClass('bg-blue-50 text-blue-600 ring-2 ring-blue-500/50');
            }
        });

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

        $(document).on('click', 'button[\\@click*="toggleSidebar"]', function() {
            setTimeout(function() {
                if ($.fn.dataTable) {
                    $.fn.dataTable.tables({
                        visible: true,
                        api: true
                    }).columns.adjust();
                }
            }, 300);
        });

        $(window).on('resize', function() {
            if ($.fn.dataTable) {
                $.fn.dataTable.tables({
                    visible: true,
                    api: true
                }).columns.adjust();
            }
        });

        // Print Balance Button Handler
        $(document).on('click', '.print-balance-button', function() {
            const id = $(this).data('id');
            window.open(`{{ url('inventory/stock-monitoring') }}/${id}/print-balance`, '_blank');
        });

        // Filter Handlers
        // Initialize Select2
        $('.select2').select2({
            minimumResultsForSearch: 0, 
            width: '100%'
        });

        // Filter Handlers
        $('#filter_status, #filter_customer, #filter_model').on('change', function() {
            table.draw();
        });

        // Load Models Function
        function loadModels(customerId) {
            const modelSelect = $('#filter_model');
            const currentModel = modelSelect.val();

            modelSelect.empty().append('<option value="">Loading...</option>');

            $.ajax({
                url: "{{ route('inventory.product.getModels') }}",
                data: { customer_id: customerId },
                success: function(data) {
                    modelSelect.empty().append('<option value="">All Models</option>');
                    data.forEach(function(model) {
                         const isSelected = currentModel == model.id;
                         const option = new Option(model.name, model.id, isSelected, isSelected);
                         modelSelect.append(option);
                    });
                    modelSelect.trigger('change.select2');
                },
                error: function() {
                    modelSelect.empty().append('<option value="">Error Loading Models</option>');
                }
            });
        }

        // Initial Load
        loadModels(null);

        $('#reset_filters').on('click', function() {
            $('#filter_status').val('').trigger('change');
            $('#filter_customer').val('').trigger('change');
            
            $('#filter_model').val(null).trigger('change.select2');
            
            table.search('').draw();
        });

        // Dynamic Model Loading
        $('#filter_customer').on('change', function() {
            const customerId = $(this).val();
            loadModels(customerId);
        });
    });
</script>
@endpush