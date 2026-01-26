@extends('layouts.app')

@section('title', 'Inventory Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="dashboard-container flex-1 flex flex-col">
    <div class="flex gap-2 mb-2 items-stretch">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2 flex-1">
            @foreach([
            ['val' => number_format($stats['total_stock']), 'label' => 'Total Stock Value', 'icon' => 'fa-cubes', 'color' => 'text-blue-500', 'bg' => 'bg-blue-50', 'id' => 'stat_total_stock'],
            ['val' => number_format($stats['material_in']), 'label' => 'Material In', 'icon' => 'fa-arrow-right-to-bracket', 'color' => 'text-emerald-500', 'bg' => 'bg-emerald-50', 'id' => 'stat_material_in'],
            ['val' => number_format($stats['material_out']), 'label' => 'Total Out', 'icon' => 'fa-arrow-right-from-bracket', 'color' => 'text-amber-500', 'bg' => 'bg-amber-50', 'id' => 'stat_material_out'],
            ['val' => number_format($stats['out_pp']), 'label' => 'Out PP', 'icon' => 'fa-industry', 'color' => 'text-indigo-500', 'bg' => 'bg-indigo-50', 'id' => 'stat_out_pp'],
            ['val' => number_format($stats['out_event']), 'label' => 'Out Event', 'icon' => 'fa-calendar-check', 'color' => 'text-purple-500', 'bg' => 'bg-purple-50', 'id' => 'stat_out_event'],
            ['val' => number_format($stats['out_trial']), 'label' => 'Out Trial', 'icon' => 'fa-vial', 'color' => 'text-rose-500', 'bg' => 'bg-rose-50', 'id' => 'stat_out_trial'],
            ] as $stat)
            <div class="stat-card flex flex-col justify-between h-full px-3 py-2 bg-white dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-700 duration-200">
                <div class="flex gap-2 items-center">
                    <div class="{{ $stat['bg'] }} {{ $stat['color'] }} w-8 h-8 rounded-md flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid {{ $stat['icon'] }} text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <div class="text-[10px] font-base text-slate-400 dark:text-gray-400 tracking-wide uppercase">{{ $stat['label'] }}</div>
                        <div class="value font-bold text-lg text-slate-800 dark:text-gray-100 tracking-tight leading-tight" id="{{ $stat['id'] }}">{{ $stat['val'] }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <button id="btnToggleDashFilter" class="flex flex-col items-center justify-center gap-0.5 w-16 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md text-[10px] font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all">
            <i class="fa-solid fa-filter text-sm"></i>
            <span>Filter</span>
            <i class="fa-solid fa-chevron-down text-[8px] transition-transform duration-200 mt-0.5" id="filterChevron"></i>
        </button>
    </div>

    {{-- Collapsible Filter Card --}}
    <div id="dashboardFilterCard" class="hidden bg-white dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-700 p-3 mb-2">
        <form id="filterForm">
            <div class="flex flex-col xl:flex-row gap-2 xl:items-end">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-2 flex-1">
                    <div class="space-y-0.5">
                        <label class="block text-[10px] uppercase font-bold text-slate-500 dark:text-gray-400">Period</label>
                        <input type="month" id="month_picker" name="month_year" value="{{ $filters['month_year'] }}" class="w-full text-xs border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded h-[38px] px-2 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div class="space-y-0.5">
                        <label class="block text-[10px] uppercase font-bold text-slate-500 dark:text-gray-400">Customer</label>
                        <select id="filterCustomer" name="customer[]" class="w-full text-xs"></select>
                    </div>
                    <div class="space-y-0.5">
                        <label class="block text-[10px] uppercase font-bold text-slate-500 dark:text-gray-400">Model</label>
                        <select id="filterModel" name="model[]" class="w-full text-xs"></select>
                    </div>
                    <div class="space-y-0.5">
                        <label class="block text-[10px] uppercase font-bold text-slate-500 dark:text-gray-400">Balance</label>
                        <select id="filterBalance" name="status_balance[]" class="w-full text-xs"></select>
                    </div>
                    <div class="space-y-0.5">
                        <label class="block text-[10px] uppercase font-bold text-slate-500 dark:text-gray-400">Usage</label>
                        <select id="filterUsage" name="status_usage[]" class="w-full text-xs"></select>
                    </div>
                </div>

                <div class="flex gap-2 pt-2 xl:pt-0">
                    <button type="button" id="btnReset" class="h-[30px] px-3 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded text-xs text-gray-700 dark:text-gray-300 transition-colors">
                        Reset
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-12 gap-2 mt-0 items-stretch" style="min-height: calc(100vh - 200px);">
        <div class="col-span-12 xl:col-span-8 flex flex-col gap-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 flex-1 min-h-0">
                <div class="chart-card bg-white dark:bg-gray-800 p-2.5 rounded-md border border-gray-200 dark:border-gray-700 flex flex-col relative group hover:border-blue-300 transition-colors">
                    <div class="flex items-center justify-between mb-0.5">
                        <span class="font-bold text-[10px] text-slate-600 dark:text-gray-300 uppercase tracking-wide">Stock Status</span>
                        <button class="text-gray-300 hover:text-blue-500"><i class="fa-solid fa-expand text-[10px]"></i></button>
                    </div>
                    <div class="flex-1 min-h-0 relative"><canvas id="stockStatusChart"></canvas></div>
                </div>
                <div class="chart-card bg-white dark:bg-gray-800 p-2.5 rounded-md border border-gray-200 dark:border-gray-700 flex flex-col relative group hover:border-blue-300 transition-colors">
                    <div class="flex items-center justify-between mb-0.5">
                        <span class="font-bold text-[10px] text-slate-600 dark:text-gray-300 uppercase tracking-wide">Usage by Models</span>
                        <button class="text-gray-300 hover:text-blue-500"><i class="fa-solid fa-expand text-[10px]"></i></button>
                    </div>
                    <div class="flex-1 min-h-0 relative"><canvas id="usageModelChart"></canvas></div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 flex-1 min-h-0">
                <div class="chart-card bg-white dark:bg-gray-800 p-2.5 rounded-md border border-gray-200 dark:border-gray-700 flex flex-col relative group hover:border-blue-300 transition-colors">
                    <div class="flex items-center justify-between mb-0.5">
                        <span class="font-bold text-[10px] text-slate-600 dark:text-gray-300 uppercase tracking-wide">Transaction Trend</span>
                        <button class="text-gray-300 hover:text-blue-500"><i class="fa-solid fa-expand text-[10px]"></i></button>
                    </div>
                    <div class="flex-1 min-h-0 relative"><canvas id="trendlineChart"></canvas></div>
                </div>
                <div class="chart-card bg-white dark:bg-gray-800 p-2.5 rounded-md border border-gray-200 dark:border-gray-700 flex flex-col relative group hover:border-blue-300 transition-colors">
                    <div class="flex items-center justify-between mb-0.5">
                        <span class="font-bold text-[10px] text-slate-600 dark:text-gray-300 uppercase tracking-wide">Supply by Makers (IN)</span>
                        <button class="text-gray-300 hover:text-blue-500"><i class="fa-solid fa-expand text-[10px]"></i></button>
                    </div>
                    <div class="flex-1 min-h-0 relative"><canvas id="makerChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="col-span-12 xl:col-span-4 flex flex-col gap-2 h-full">
            {{-- Balance Table --}}
            <div class="table-container bg-white dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col flex-1 min-h-0">
                <div class="py-1.5 px-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-rose-500 text-[10px]"></i>
                        <span class="font-bold text-[10px] text-slate-700 dark:text-gray-200 uppercase tracking-wide">Balance Warnings</span>
                    </div>
                    @if(count($tables['balance']) > 0)
                    <span class="text-[9px] font-bold bg-rose-100 text-rose-600 px-1.5 rounded">{{ count($tables['balance']) }}</span>
                    @endif
                </div>
                <div class="overflow-y-auto flex-1 custom-scrollbar">
                    <table class="w-full text-[10px]">
                        <thead class="bg-gray-50/50 dark:bg-gray-700/50 sticky top-0 z-10 backdrop-blur-sm">
                            <tr>
                                <th class="py-1 px-2 text-left font-semibold text-slate-500 dark:text-gray-400">Item</th>
                                <th class="py-1 px-2 text-left font-semibold text-slate-500 dark:text-gray-400">Cust</th>
                                <th class="py-1 px-2 text-right font-semibold text-slate-500 dark:text-gray-400">Qty</th>
                                <th class="py-1 px-2 text-right font-semibold text-slate-500 dark:text-gray-400">Status</th>
                            </tr>
                        </thead>
                        <tbody id="balanceTableBody" class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($tables['balance'] as $row)
                            <tr class="hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="p-2 font-medium text-slate-700 dark:text-gray-300">
                                    <div class="truncate max-w-[100px]">{{ $row->part_no }}</div>
                                    <div class="text-[9px] text-slate-400">{{ $row->model_name }}</div>
                                </td>
                                <td class="p-2 text-slate-600 dark:text-gray-400">{{ $row->customer_code }}</td>
                                <td class="p-2 text-right whitespace-nowrap">
                                    <span class="font-mono text-[10px] font-bold text-slate-700 dark:text-gray-200">{{ number_format($row->current_stock_qty) }}</span>
                                    <span class="text-slate-400 mx-1">/</span>
                                    <span class="font-mono text-[9px] text-slate-500">{{ number_format($row->min_stock) }}</span>
                                </td>
                                <td class="p-2 text-right">
                                    <span class="text-[9px] font-bold {{ $row->status == 'Critical' ? 'text-rose-600' : ($row->status == 'Over' ? 'text-amber-600' : 'text-emerald-600') }}">
                                        {{ $row->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="p-2 text-center text-slate-400 italic">No warnings</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Usage Table --}}
            <div class="table-container bg-white dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col flex-1 min-h-0">
                <div class="py-1.5 px-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-chart-pie text-amber-500 text-[10px]"></i>
                        <span class="font-bold text-[10px] text-slate-700 dark:text-gray-200 uppercase tracking-wide">Usage Status</span>
                    </div>
                </div>
                <div class="overflow-y-auto flex-1 custom-scrollbar">
                    <table class="w-full text-[10px]">
                        <thead class="bg-gray-50/50 dark:bg-gray-700/50 sticky top-0 z-10 backdrop-blur-sm">
                            <tr>
                                <th class="py-1 px-2 text-left font-semibold text-slate-500 dark:text-gray-400">Item</th>
                                <th class="py-1 px-2 text-left font-semibold text-slate-500 dark:text-gray-400">Cust</th>
                                <th class="py-1 px-2 text-right font-semibold text-slate-500 dark:text-gray-400">Qty</th>
                                <th class="py-1 px-2 text-right font-semibold text-slate-500 dark:text-gray-400">Status</th>
                            </tr>
                        </thead>
                        <tbody id="usageTableBody" class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($tables['usage'] as $row)
                            <tr class="hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="p-2 font-medium text-slate-700 dark:text-gray-300">
                                    <div class="truncate max-w-[100px]">{{ $row->part_no }}</div>
                                </td>
                                <td class="p-2 text-slate-600 dark:text-gray-400">{{ $row->customer_code }}</td>
                                <td class="p-2 text-right whitespace-nowrap">
                                    <span class="font-mono text-[10px] font-bold text-slate-700 dark:text-gray-200">{{ number_format($row->current_stock_qty) }}</span>
                                    <span class="text-slate-400 mx-1">/</span>
                                    <span class="font-mono text-[9px] text-slate-500">{{ number_format($row->min_stock) }}</span>
                                </td>
                                <td class="p-2 text-right">
                                    <span class="text-[9px] font-bold {{ $row->status == 'Critical' ? 'text-rose-600' : ($row->status == 'Over' ? 'text-amber-600' : 'text-emerald-600') }}">
                                        {{ $row->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="p-2 text-center text-slate-400 italic">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recent Transactions --}}
            <div class="table-container bg-white dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col flex-1 min-h-0">
                <div class="py-1.5 px-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-blue-500 text-[10px]"></i>
                        <span class="font-bold text-[10px] text-slate-700 dark:text-gray-200 uppercase tracking-wide">Recent Transactions</span>
                    </div>
                </div>
                <div class="overflow-y-auto flex-1 custom-scrollbar">
                    <table class="w-full text-[10px]">
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($tables['history'] as $row)
                            <tr class="hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="p-2 font-medium text-slate-700 dark:text-gray-300">
                                    <div class="truncate max-w-[90px]">{{ $row->part_no }}</div>
                                </td>
                                <td class="p-2 text-center">
                                    <span class="px-1.5 py-0.5 rounded text-[9px] bg-gray-100 text-gray-600 border border-gray-200">{{ $row->category }}</span>
                                </td>
                                <td class="p-2 text-right font-mono text-slate-700 dark:text-gray-300">
                                    {{ number_format($row->qty * $row->pcs_per_unit) }}
                                </td>
                                <td class="p-2 text-right text-[9px] text-slate-500 dark:text-gray-400 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($row->transaction_date)->format('d/m') }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="p-2 text-center text-slate-400 italic">No history</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
    /* Hide chevron when clear button is present */
    .select2-container--default .select2-selection--single:has(.select2-selection__clear) .select2-selection__arrow {
        display: none !important;
    }
    
    /* Center clear button vertically */
    .select2-container--default .select2-selection--single .select2-selection__clear {
        top: 50% !important;
        transform: translateY(25%) !important;
    }
</style>
<script>
    $(document).ready(function() {
        // Toggle Filter Logic
        $('#btnToggleDashFilter').on('click', function(e) {
            e.stopPropagation();
            $('#dashboardFilterCard').slideToggle(200);
            $('#filterChevron').toggleClass('rotate-180');
        });

        const isDark = document.documentElement.classList.contains('dark');
        
        // Dynamic Chart Defaults
        Chart.defaults.color = isDark ? '#94a3b8' : '#64748b';
        Chart.defaults.borderColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)';
        Chart.defaults.font.family = "'Inter', sans-serif";
        
        const chartsData = {
            stockLabels: @json(array_keys($charts['stock_grouped'])).map(l => l.split('|')),
            stockData: @json(array_values($charts['stock_grouped'])),
            usageModelLabels: @json($charts['usage_model']->pluck('label')).map(l => l.split('|')),
            usageModelData: @json($charts['usage_model']->pluck('total')),
            trendData: @json($charts['trendline']),
            makerLabels: @json($charts['maker']->pluck('code')),
            makerData: @json($charts['maker']->pluck('total'))
        };
        $('#filterModel').select2({
            dropdownParent: $('#filterModel').parent(),
            width: '100%',
            placeholder: 'All Models',
            allowClear: true,
            ajax: {
                url: '{{ route("api.data.models") }}',
                method: 'POST',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term,
                        page: params.page,
                        customer_id: $('#filterCustomer').val(),
                        _token: '{{ csrf_token() }}'
                    };
                },
                processResults: function(data, params) {
                    const results = data.results || [];
                    // Add 'All' option only on first page and when no search term
                    if ((!params.term || params.term === '') && (!params.page || params.page === 1)) {
                        results.unshift({ id: '', text: 'All Models' });
                    }
                    return {
                        results: results,
                        pagination: data.pagination
                    };
                },
                cache: true
            }
        });

        $('#filterCustomer').select2({
            dropdownParent: $('#filterCustomer').parent(),
            width: '100%',
            placeholder: 'All Customers',
            allowClear: true,
            ajax: {
                url: '{{ route("api.data.customers") }}',
                method: 'POST',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term,
                        page: params.page,
                        _token: '{{ csrf_token() }}'
                    };
                },
                processResults: function(data, params) {
                    const results = data.results || [];
                    // Add 'All' option only on first page and when no search term
                    if ((!params.term || params.term === '') && (!params.page || params.page === 1)) {
                        results.unshift({ id: '', text: 'All Customers' });
                    }
                    return {
                        results: results,
                        pagination: data.pagination
                    };
                },
                cache: true
            }
        });

        $('#filterBalance').select2({
            dropdownParent: $('#filterBalance').parent(),
            width: '100%',
            placeholder: 'All Balance Status',
            allowClear: true,
            ajax: {
                url: '{{ route("api.data.statuses", ["type" => "balance"]) }}',
                method: 'GET',
                dataType: 'json',
                processResults: function(data) {
                    const results = [{ id: '', text: 'All' }].concat(data.results || []);
                    return { results: results };
                }
            }
        });
        $('#filterUsage').select2({
            dropdownParent: $('#filterUsage').parent(),
            width: '100%',
            placeholder: 'All Usage Status',
            allowClear: true,
            ajax: {
                url: '{{ route("api.data.statuses", ["type" => "usage"]) }}',
                method: 'GET',
                dataType: 'json',
                processResults: function(data) {
                    const results = [{ id: '', text: 'All' }].concat(data.results || []);
                    return { results: results };
                }
            }
        });

            function fetchDashboardData(formData, btn = null) {
                let originalText = '';
                if (btn && btn.length) {
                    originalText = btn.html();
                    btn.html('<i class="fa-solid fa-spinner fa-spin"></i>').prop('disabled', true);
                }

                $.ajax({
                    url: window.location.pathname,
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        // Update Stats
                        $('#stat_total_stock').text(new Intl.NumberFormat().format(response.stats.total_stock));
                        $('#stat_material_in').text(new Intl.NumberFormat().format(response.stats.material_in));
                        $('#stat_material_out').text(new Intl.NumberFormat().format(response.stats.material_out));
                        $('#stat_out_pp').text(new Intl.NumberFormat().format(response.stats.out_pp));
                        $('#stat_out_event').text(new Intl.NumberFormat().format(response.stats.out_event));
                        $('#stat_out_trial').text(new Intl.NumberFormat().format(response.stats.out_trial));

                        // Update Charts
                        updateChartData(stockStatusChart, 
                             Object.keys(response.charts.stock_grouped).map(l => l.split('|')), 
                             Object.values(response.charts.stock_grouped).map(d => d.critical),
                             Object.values(response.charts.stock_grouped).map(d => d.over),
                             Object.values(response.charts.stock_grouped).map(d => d.safe)
                        );
                        
                        updateChartDataSingle(usageModelChart, 
                            response.charts.usage_model.map(i => i.label.split('|')), 
                            response.charts.usage_model.map(i => i.total)
                        );
                        
                        updateChartDataSingle(makerChart, 
                            response.charts.maker.map(i => i.code), 
                            response.charts.maker.map(i => i.total)
                        );

                        const trends = response.charts.trendline;
                        const dates = [...new Set(trends.map(d => d.transaction_date))]; // X Axis
                        const cats = [...new Set(trends.map(d => d.category))]; // Legend
                        const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
                        
                        const newDatasets = cats.map((cat, idx) => ({
                            label: cat,
                            data: dates.map(d => (trends.find(td => td.transaction_date === d && td.category === cat) || {total: 0}).total),
                            borderColor: colors[idx] || '#cbd5e1',
                            backgroundColor: (colors[idx] || '#cbd5e1') + '40',
                            fill: true,
                            tension: 0.4
                        }));
                        
                        trendlineChart.data.labels = dates;
                        trendlineChart.data.datasets = newDatasets;
                        trendlineChart.update();

                        // Update Tables
                        renderTable('#balanceTableBody', response.tables.balance, generateBalanceRow);
                        renderTable('#usageTableBody', response.tables.usage, generateUsageRow);
                    },
                    error: function(err) {
                        console.error('Filter Error', err);
                        alert('Failed to filter data.');
                    },
                    complete: function() {
                        if (btn && btn.length) {
                            btn.html(originalText).prop('disabled', false);
                        }
                    }
                });
            }

            // Auto-apply logic
            $('#month_picker, #filterCustomer, #filterModel, #filterBalance, #filterUsage').on('change', function() {
                fetchDashboardData($('#filterForm').serialize());
            });

            $('#btnReset').on('click', function() {
                const btn = $(this);
                // Reset Form
                $('#month_picker').val('{{ date("Y-m") }}'); // Default to current month
                $('#filterCustomer').val(null).trigger('change');
                $('#filterModel').val(null).trigger('change');
                $('#filterBalance').val(null).trigger('change');
                $('#filterUsage').val(null).trigger('change');
                
                // Fetch Data with reset form
                fetchDashboardData($('#filterForm').serialize(), btn);
            });



            // Chart Globals
            let stockStatusChart, usageModelChart, trendlineChart, makerChart;

            // Helper functions
            function updateChartData(chart, labels, data1, data2, data3) {
                if(!chart) return;
                chart.data.labels = labels;
                if(data1) chart.data.datasets[0].data = data1;
                if(data2) chart.data.datasets[1].data = data2;
                if(data3) chart.data.datasets[2].data = data3;
                chart.update();
            }
            function updateChartDataSingle(chart, labels, data) {
                 if(!chart) return;
                 chart.data.labels = labels;
                 chart.data.datasets[0].data = data;
                 chart.update();
            }
            function renderTable(selector, data, rowGenerator) {
                const tbody = $(selector);
                tbody.empty();
                if (data.length === 0) {
                    tbody.append('<tr><td colspan="4" class="p-4 text-center text-slate-400 dark:text-gray-500 italic">No data found</td></tr>');
                    return;
                }
                data.forEach(item => {
                    tbody.append(rowGenerator(item));
                });
            }
            // Row Generators
            function generateBalanceRow(row) {
                 const statusColors = {
                     'Critical': 'bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400',
                     'Over': 'bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400',
                     'Safe': 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400'
                 };
                 const colorClass = statusColors[row.status] || statusColors['Safe'];
                 let partName = row.part_no + (row.revision ? ' - ' + row.revision : '');
                 
                 return `
                    <tr class="hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors">
                        <td class="p-3 font-medium text-slate-700 dark:text-gray-300">
                            <div class="truncate max-w-[120px]" title="${partName}">${partName}</div>
                            <div class="text-[10px] text-slate-400 dark:text-gray-500 mt-0.5">${row.model_name || '-'}</div>
                        </td>
                        <td class="p-3 text-slate-600 dark:text-gray-400">${row.customer_code || '-'}</td>
                        <td class="p-3 text-right whitespace-nowrap">
                            <span class="font-mono text-[10px] font-bold text-slate-700 dark:text-gray-200">${new Intl.NumberFormat().format(row.current_stock_qty)}</span>
                            <span class="text-slate-400 mx-1">/</span>
                            <span class="font-mono text-[9px] text-slate-500">${new Intl.NumberFormat().format(row.min_stock)}</span>
                        </td>
                        <td class="p-3 text-right">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold ${colorClass}">
                                ${row.status}
                            </span>
                        </td>
                    </tr>
                 `;
            }
            function generateUsageRow(row) {
                 const statusColors = {
                     'Critical': 'bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400',
                     'Over': 'bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400',
                     'Safe': 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400' 
                 };
                 const colorClass = statusColors[row.status] || statusColors['Safe'];
                 let partName = row.part_no + (row.revision ? ' - ' + row.revision : '');

                 return `
                    <tr class="hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors">
                        <td class="p-3 font-medium text-slate-700 dark:text-gray-300">
                            <div class="truncate max-w-[120px]" title="${partName}">${partName}</div>
                            <div class="text-[10px] text-slate-400 dark:text-gray-500 mt-0.5">${row.model_name || '-'}</div>
                        </td>
                        <td class="p-3 text-slate-600 dark:text-gray-400">${row.customer_code || '-'}</td>
                        <td class="p-3 text-right whitespace-nowrap">
                            <span class="font-mono text-[10px] font-bold text-slate-700 dark:text-gray-200">${new Intl.NumberFormat().format(row.current_stock_qty)}</span>
                            <span class="text-slate-400 mx-1">/</span>
                            <span class="font-mono text-[9px] text-slate-500">${new Intl.NumberFormat().format(row.min_stock)}</span>
                        </td>
                        <td class="p-3 text-right">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold ${colorClass}">
                                ${row.status}
                            </span>
                        </td>
                    </tr>
                 `;
            }


        if (document.getElementById('stockStatusChart')) {
            stockStatusChart = new Chart(document.getElementById('stockStatusChart'), {
                type: 'bar',
                data: {
                    labels: chartsData.stockLabels,
                    datasets: [{
                            label: 'Critical',
                            data: chartsData.stockData.map(d => d.critical),
                            backgroundColor: '#ef4444'
                        },
                        {
                            label: 'Over',
                            data: chartsData.stockData.map(d => d.over),
                            backgroundColor: '#3b82f6'
                        },
                        {
                            label: 'Safe',
                            data: chartsData.stockData.map(d => d.safe),
                            backgroundColor: '#10b981'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: false,
                            ticks: {
                                color: isDark ? '#94a3b8' : '#64748b',
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { color: isDark ? '#94a3b8' : '#64748b' }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: { color: isDark ? '#94a3b8' : '#64748b' }
                        }
                    }
                }
            });
        }

        if (document.getElementById('usageModelChart')) {
            usageModelChart = new Chart(document.getElementById('usageModelChart'), {
                type: 'bar',
                data: {
                    labels: chartsData.usageModelLabels,
                    datasets: [{
                        label: 'Usage',
                        data: chartsData.usageModelData,
                        backgroundColor: '#f59e0b'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            ticks: {
                                color: isDark ? '#94a3b8' : '#64748b',
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            ticks: { color: isDark ? '#94a3b8' : '#64748b' }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: { color: isDark ? '#94a3b8' : '#64748b' }
                        }
                    }
                }
            });
        }

        if (document.getElementById('trendlineChart')) {
            const trendData = chartsData.trendData;
            const dates = trendData ? [...new Set(trendData.map(d => d.transaction_date))] : [];
            const cats = trendData ? [...new Set(trendData.map(d => d.category))] : [];
            const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];

            trendlineChart = new Chart(document.getElementById('trendlineChart'), {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: cats.map((cat, idx) => ({
                        label: cat,
                        data: dates.map(d => (trendData.find(td => td.transaction_date === d && td.category === cat) || {
                            total: 0
                        }).total),
                        borderColor: colors[idx] || '#cbd5e1',
                        backgroundColor: (colors[idx] || '#cbd5e1') + '40',
                        fill: true,
                        tension: 0.4
                    }))
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            ticks: { color: isDark ? '#94a3b8' : '#64748b' }
                        },
                        y: {
                            stacked: true,
                            ticks: { color: isDark ? '#94a3b8' : '#64748b' }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: { color: isDark ? '#94a3b8' : '#64748b' }
                        }
                    }
                }
            });
        }

        if (document.getElementById('makerChart')) {
            makerChart = new Chart(document.getElementById('makerChart'), {
                type: 'bar',
                data: {
                    labels: chartsData.makerLabels,
                    datasets: [{
                        label: 'Usage',
                        data: chartsData.makerData,
                        backgroundColor: '#6366f1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            ticks: { color: isDark ? '#94a3b8' : '#64748b' }
                        },
                        y: {
                            ticks: { color: isDark ? '#94a3b8' : '#64748b' }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: { color: isDark ? '#94a3b8' : '#64748b' }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush