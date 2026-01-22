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

        {{-- Legend Popover --}}
        <div class="relative mt-4 sm:mt-0">
            <button id="toggleLegend" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-full text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700 dark:hover:bg-gray-700">
                <i class="fa-solid fa-circle-question mr-1.5 text-blue-500"></i> Legend
            </button>

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
        <tbody></tbody>
    </x-table>
</div>
@endsection

@push('style')
<style>
    /* Tooltip content style */
    #global-tooltip-portal {
        pointer-events: none;
        /* Let mouse events pass through */
        display: none;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Create Portal Container if not exists
        if ($('#global-tooltip-portal').length === 0) {
            $('body').append('<div id="global-tooltip-portal" class="fixed z-[9999] bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-4 w-64 text-left hidden"></div>');
        }

        const tooltip = $('#global-tooltip-portal');

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
                    return `<span class="${colorClass}">${data}</span>`;
                }
                return data;
            }
        };

        const categories = <?php echo json_encode(isset($categories) ? $categories->values() : []); ?>;

        // Safety check: Pastikan categories benar-benar array sebelum di-loop
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
            ajax: "{{ route('inventory.stockMonitoring.data') }}",
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

        $(document).on('mouseenter', '.hover-trigger', function(e) {
            const el = $(this);
            const data = el.data('details');
            if (!data) return;

            let content = `
                <h4 class="font-bold text-gray-900 dark:text-white mb-2 border-b pb-1">Product Details</h4>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="text-gray-500 dark:text-gray-400">Customer:</div>
                    <div class="font-medium text-gray-900 dark:text-white truncate">${data.customer || '-'}</div>
                    <div class="text-gray-500 dark:text-gray-400">Model:</div>
                    <div class="font-medium text-gray-900 dark:text-white truncate">${data.model || '-'}</div>
                    <div class="text-gray-500 dark:text-gray-400">Rank/Limit:</div>
                    <div class="font-medium text-gray-900 dark:text-white">${data.rank || '-'} <span class="text-gray-400">(${data.limit_value || '-'})</span></div>
                    <div class="text-gray-500 dark:text-gray-400">Coating:</div>
                    <div class="font-medium text-gray-900 dark:text-white">${data.coating_type || '-'}</div>
                    <div class="text-gray-500 dark:text-gray-400">Min. Stock:</div>
                    <div class="font-medium text-gray-900 dark:text-white">${data.min_stock || '-'}</div>
                    <div class="text-gray-500 dark:text-gray-400">Unit/Car:</div>
                    <div class="font-medium text-gray-900 dark:text-white">${data.unit_per_car || '-'}</div>
                    <div class="text-gray-500 dark:text-gray-400">Last Upd:</div>
                    <div class="font-medium text-gray-900 dark:text-white col-span-2">${data.last_update || '-'}</div>
                </div>
            `;

            tooltip.html(content).removeClass('hidden').show();
            const rect = this.getBoundingClientRect();
            let top = rect.bottom + 5;
            let left = rect.left;

            if (top + tooltip.outerHeight() > window.innerHeight) top = rect.top - tooltip.outerHeight() - 5;
            if (left + tooltip.outerWidth() > window.innerWidth) left = window.innerWidth - tooltip.outerWidth() - 10;

            tooltip.css({
                top: top + 'px',
                left: left + 'px',
                position: 'fixed'
            });
        });

        $(document).on('mouseleave', '.hover-trigger', function() {
            tooltip.hide();
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
    });
</script>
@endpush