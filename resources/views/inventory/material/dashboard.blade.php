@extends('layouts.app')

@section('title', 'Inventory Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="dashboard-container w-full h-auto overflow-y-auto lg:h-[calc(100vh-85px)] lg:overflow-hidden flex flex-col gap-2 pb-0 custom-scrollbar lg:pb-0">
    {{-- Header, KPIs & Filters --}}
    <div class="flex flex-wrap items-center justify-between gap-y-2 gap-x-4">
        <!-- Section 1: Title Section -->
        <div class="flex-none">
            <h2 class="text-lg xl:text-xl font-bold text-gray-800 dark:text-white leading-none mb-1">Inventory Overview</h2>
            <p class="text-[11px] xl:text-xs text-gray-500 dark:text-gray-400 leading-tight">Stock monitoring and transaction analytics</p>
        </div>

        <!-- Section 2: KPI Cards & Filter Toggle -->
        <div class="flex-1 flex flex-col md:flex-row gap-2 items-stretch lg:justify-end min-w-[100%] xl:min-w-[750px]">
            <!-- KPI Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-2 flex-1">
                @foreach([
                    ['val' => number_format($stats['total_stock_value']), 'label' => 'Total Value', 'unit' => 'IDR', 'icon' => 'fa-coins', 'color' => 'primary', 'id' => 'stat_total_stock_value'],
                    ['val' => number_format($stats['total_stock']), 'label' => 'Total Stock', 'unit' => 'Item Part', 'icon' => 'fa-cubes', 'color' => 'slate', 'id' => 'stat_total_stock'],
                    ['val' => number_format($stats['material_in']), 'label' => 'In', 'unit' => 'Item Part', 'icon' => 'fa-arrow-right-to-bracket', 'color' => 'emerald', 'id' => 'stat_material_in'],
                    ['val' => number_format($stats['out_event']), 'label' => 'Out Event', 'unit' => 'Item Part', 'icon' => 'fa-arrow-right-from-bracket', 'color' => 'amber', 'id' => 'stat_out_event'],
                    ['val' => number_format($stats['out_pp']), 'label' => 'Out PP', 'unit' => 'Item Part', 'icon' => 'fa-industry', 'color' => 'indigo', 'id' => 'stat_out_pp'],
                    ['val' => number_format($stats['out_trial']), 'label' => 'Out Trial', 'unit' => 'Item Part', 'icon' => 'fa-vial', 'color' => 'rose', 'id' => 'stat_out_trial'],
                ] as $stat)
                <div class="bg-white dark:bg-gray-800 px-2.5 py-2 rounded-xs border border-gray-200 dark:border-gray-700 flex items-center gap-2.5 h-[52px]">
                    <div class="w-9 h-9 rounded-xs bg-{{ $stat['color'] }}-50 dark:bg-{{ $stat['color'] }}-900/20 flex items-center justify-center text-{{ $stat['color'] }}-600 dark:text-{{ $stat['color'] }}-400 text-base shrink-0">
                        <i class="fa-solid {{ $stat['icon'] }}"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest leading-none mb-1 whitespace-nowrap">{{ $stat['label'] }}</p>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 leading-none tracking-tight whitespace-nowrap" id="{{ $stat['id'] }}">
                            {{ $stat['val'] }} <span class="text-[9px] text-slate-400 font-medium ml-0.5">{{ $stat['unit'] }}</span>
                        </h3>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Filter Toggle Section -->
            <div class="shrink-0 flex items-stretch">
                <button id="btnToggleDashFilter" title="Toggle Filters" class="group flex items-center justify-center w-full md:w-[52px] h-[52px] md:h-auto bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-600 rounded-xs transition-all hover:bg-slate-50 dark:hover:bg-gray-700">
                    <i class="fa-solid fa-filter text-slate-400 group-hover:text-primary-500 transition-colors text-sm"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Collapsible Filter Card --}}
    <div id="dashboardFilterCard" class="hidden bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-600 p-4">
        <form id="filterForm">
            <div class="flex flex-col lg:flex-row gap-4 lg:items-end">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 flex-1">
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-medium text-slate-700 dark:text-slate-200 uppercase tracking-widest">Period</label>
                        <input type="month" id="month_picker" name="month_year" value="{{ $filters['month_year'] }}" class="w-full text-xs font-medium border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-900 dark:text-white rounded-xs h-[40px] px-3 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all focus:border-primary-500">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-medium text-slate-700 dark:text-slate-200 uppercase tracking-widest">Customer</label>
                        <select id="filterCustomer" name="customer[]" class="w-full text-xs"></select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-medium text-slate-700 dark:text-slate-200 uppercase tracking-widest">Model</label>
                        <select id="filterModel" name="model[]" class="w-full text-xs"></select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-medium text-slate-700 dark:text-slate-200 uppercase tracking-widest">Balance Status</label>
                        <select id="filterBalance" name="status_balance[]" class="w-full text-xs"></select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-medium text-slate-700 dark:text-slate-200 uppercase tracking-widest">Usage Status</label>
                        <select id="filterUsage" name="status_usage[]" class="w-full text-xs"></select>
                    </div>
                </div>

                <div class="flex gap-2 pt-2 xl:pt-0">
                    <button type="button" id="btnReset" class="h-[40px] px-6 bg-slate-100 hover:bg-slate-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-xs text-[10px] font-bold text-slate-600 dark:text-gray-300 uppercase tracking-widest transition-all">
                        Reset Filters
                    </button>
                </div>
            </div>
        </form>
    </div>


    <div class="flex flex-col lg:flex-row gap-2 flex-1 min-h-0">
        {{-- Column 1 --}}
        <div class="w-full lg:w-1/3 flex flex-col gap-2 h-full min-h-0">
            {{-- Chart Card 1 --}}
            <div class="chart-card bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative h-[250px] lg:h-auto lg:flex-1 min-h-0">
                <div class="flex-none flex justify-between items-center mb-1">
                    <h3 class="text-sm lg:text-base font-semibold text-gray-800 dark:text-gray-100 flex items-center">
                        <i class="fa-solid fa-chart-column mr-2 text-primary-500"></i> Stock Status <span class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-slate-700 text-[8px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest border border-slate-200/50 dark:border-slate-600/50">Item Part</span>
                    </h3>
                    <button class="text-gray-400 text-xs p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"><i class="fa-solid fa-expand"></i></button>
                </div>
                <div class="relative w-full flex-1 min-h-0"><canvas id="stockStatusChart"></canvas></div>
            </div>

            {{-- Balance Warnings Table --}}
            <div class="table-container bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative h-[320px] lg:h-auto lg:flex-1 min-h-0">
                <div class="flex-none flex justify-between items-center mb-1">
                    <h3 class="text-sm lg:text-base font-semibold text-gray-800 dark:text-gray-100 flex items-center">
                        <i class="fa-solid fa-triangle-exclamation mr-2 text-rose-500"></i> Balance Warnings
                    </h3>
                </div>
                <div class="overflow-y-auto flex-1 custom-scrollbar border border-gray-100 dark:border-gray-700/50 rounded-xs">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/80 dark:bg-gray-700/50 sticky top-0 z-10 backdrop-blur-md">
                            <tr>
                                <th class="py-2 px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest">Part No</th>
                                <th class="py-2 px-2 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-right">Min</th>
                                <th class="py-2 px-2 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-right">Actual</th>
                                <th class="py-2 px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody id="balanceTableBody" class="divide-y divide-slate-100 dark:divide-gray-700">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Column 2 --}}
        <div class="w-full lg:w-1/3 flex flex-col gap-2 h-full min-h-0">
            {{-- Chart Card 2 --}}
            <div class="chart-card bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative h-[250px] lg:h-auto lg:flex-1 min-h-0">
                <div class="flex-none flex justify-between items-center mb-1">
                    <h3 id="usageChartTitle" class="text-sm lg:text-base font-semibold text-gray-800 dark:text-gray-100 flex items-center">
                        <i class="fa-solid fa-chart-pie mr-2 text-amber-500"></i> Usage by Models <span class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-slate-700 text-[8px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest border border-slate-200/50 dark:border-slate-600/50">Item Part</span>
                    </h3>
                    <div class="flex items-center gap-2">
                            <div class="flex bg-gray-100 dark:bg-gray-700 p-0.5 rounded-xs">
                            <button type="button" onclick="switchUsageChart('model')" id="btnUsageModel" class="px-2 py-1 rounded-xs text-[9px] font-bold uppercase transition-all bg-white dark:bg-gray-600 text-primary-600 shadow-sm">Model</button>
                            <button type="button" onclick="switchUsageChart('maker')" id="btnUsageMaker" class="px-2 py-1 rounded-xs text-[9px] font-bold uppercase transition-all text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Maker</button>
                        </div>
                        <button class="text-gray-400 text-xs p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"><i class="fa-solid fa-expand"></i></button>
                    </div>
                </div>
                <div class="relative w-full flex-1 min-h-0">
                    <div id="containerUsageModel" class="h-full"><canvas id="usageModelChart"></canvas></div>
                    <div id="containerUsageMaker" class="h-full hidden"><canvas id="makerChart"></canvas></div>
                </div>
            </div>

            {{-- Material Usage Table --}}
            <div class="table-container bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative h-[320px] lg:h-auto lg:flex-1 min-h-0">
                <div class="flex-none flex justify-between items-center mb-1">
                    <h3 class="text-sm lg:text-base font-semibold text-gray-800 dark:text-gray-100 flex items-center">
                        <i class="fa-solid fa-vial mr-2 text-indigo-500"></i> Material Usage Detail
                    </h3>
                </div>
                <div class="overflow-y-auto flex-1 custom-scrollbar border border-gray-100 dark:border-gray-700/50 rounded-xs">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/80 dark:bg-gray-700/50 sticky top-0 z-10 backdrop-blur-md">
                            <tr>
                                <th class="py-2 px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest">Part No</th>
                                <th class="py-2 px-2 text-[9px] font-bold text-slate-500 uppercase tracking-widest">Supplier</th>
                                <th class="py-2 px-2 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-right">Actual</th>
                                <th class="py-2 px-2 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-right">Gap</th>
                                <th class="py-2 px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody id="usageTableBody" class="divide-y divide-slate-100 dark:divide-gray-700">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Column 3 --}}
        <div class="w-full lg:w-1/3 flex flex-col gap-2 h-full min-h-0">
            {{-- Transaction Trend Chart --}}
            <div class="chart-card bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative h-[250px] lg:h-auto lg:flex-1 min-h-0">
                <div class="flex-none flex justify-between items-center mb-1">
                    <h3 class="text-sm lg:text-base font-semibold text-gray-800 dark:text-gray-100 flex items-center">
                        <i class="fa-solid fa-arrow-trend-up mr-2 text-emerald-500"></i> Transaction Trend <span class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-slate-700 text-[8px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest border border-slate-200/50 dark:border-slate-600/50">Item Part</span>
                    </h3>
                    <button class="text-gray-400 text-xs p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"><i class="fa-solid fa-expand"></i></button>
                </div>
                <div class="relative w-full flex-1 min-h-0"><canvas id="trendlineChart"></canvas></div>
            </div>

            {{-- Recent Transactions --}}
            <div class="table-container bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative h-[320px] lg:h-auto lg:flex-1 min-h-0">
                <div class="flex-none flex justify-between items-center mb-1">
                    <h3 class="text-sm lg:text-base font-semibold text-gray-800 dark:text-gray-100 flex items-center">
                        <i class="fa-solid fa-clock-rotate-left mr-2 text-primary-500"></i> Recent Activity
                    </h3>
                </div>
                <div class="overflow-y-auto flex-1 custom-scrollbar border border-gray-100 dark:border-gray-700/50 rounded-xs">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/80 dark:bg-gray-700/50 sticky top-0 z-10 backdrop-blur-md">
                            <tr>
                                <th class="py-2 px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest">Part No</th>
                                <th class="py-2 px-2 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-center">Type</th>
                                <th class="py-2 px-2 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-center">Date</th>
                                <th class="py-2 px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-right">Qty</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody" class="divide-y divide-slate-100 dark:divide-gray-700">
                        </tbody>
                    </table>
            </div>
        </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')

<script>
    $(document).ready(function() {
        // Toggle Filter Logic
        $('#btnToggleDashFilter').on('click', function(e) {
            e.stopPropagation();
            $('#dashboardFilterCard').slideToggle(200);
            
            // Toggle active styling
            $(this).toggleClass('bg-white dark:bg-gray-800 border-slate-200 dark:border-gray-600 hover:bg-slate-50 dark:hover:bg-gray-700');
            $(this).toggleClass('bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-700 hover:bg-primary-100 dark:hover:bg-primary-900/40');
            $(this).find('i').toggleClass('text-slate-400 group-hover:text-primary-500');
            $(this).find('i').toggleClass('text-primary-600 dark:text-primary-400');
        });

        const isDark = document.documentElement.classList.contains('dark');
        
        // Dynamic Chart Defaults
        Chart.defaults.color = isDark ? '#94a3b8' : '#64748b';
        Chart.defaults.borderColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)';
        Chart.defaults.font.family = "'Inter', sans-serif";
        
        const chartsData = {
            stockLabels: @json(array_keys($charts['stock_grouped'])).map(l => l.split('|')),
            stockData: @json(array_values($charts['stock_grouped'])),
            stockWarning: @json(collect(array_values($charts['stock_grouped']))->pluck('warning')),
            usageModelLabels: @json(collect($charts['usage_model'])->pluck('label')).map(l => l.split('|')),
            usageModelEvent: @json(collect($charts['usage_model'])->pluck('event')),
            usageModelPP: @json(collect($charts['usage_model'])->pluck('pp')),
            usageModelTrial: @json(collect($charts['usage_model'])->pluck('trial')),
            trendData: @json($charts['trendline']),
            makerLabels: @json(collect($charts['maker'])->pluck('maker')),
            makerOnBudget: @json(collect($charts['maker'])->pluck('on_budget')),
            makerNearLoss: @json(collect($charts['maker'])->pluck('near_loss')),
            makerLoss: @json(collect($charts['maker'])->pluck('loss'))
        };

        window.switchUsageChart = function(type) {
            const isModel = type === 'model';
            $('#usageChartTitle').html(
                '<i class="fa-solid fa-chart-pie mr-2 text-amber-500"></i> ' + 
                (isModel ? 'Usage by Models' : 'Supply by Makers') + 
                ' <span class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-slate-700 text-[8px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest border border-slate-200/50 dark:border-slate-600/50">Item Part</span>'
            );
            
            // Toggle container
            $('#containerUsageModel').toggleClass('hidden', !isModel);
            $('#containerUsageMaker').toggleClass('hidden', isModel);
            
            // Toggle button style
            $('#btnUsageModel').toggleClass('bg-white dark:bg-gray-600 text-primary-600 shadow-sm', isModel)
                .toggleClass('text-slate-500 hover:text-slate-700', !isModel);
            $('#btnUsageMaker').toggleClass('bg-white dark:bg-gray-600 text-primary-600 shadow-sm', !isModel)
                .toggleClass('text-slate-500 hover:text-slate-700', isModel);
        };

        const chartColors = {
            primary: {
                solid: '#0ea5e9',
                light: 'rgba(14, 165, 233, 0.1)',
                grad: ['rgba(14, 165, 233, 0.5)', 'rgba(14, 165, 233, 0.05)']
            },
            emerald: {
                solid: '#10b981',
                light: 'rgba(16, 185, 129, 0.1)',
                grad: ['rgba(16, 185, 129, 0.5)', 'rgba(16, 185, 129, 0.05)']
            },
            amber: {
                solid: '#f59e0b',
                light: 'rgba(245, 158, 11, 0.1)',
                grad: ['rgba(245, 158, 11, 0.5)', 'rgba(245, 158, 11, 0.05)']
            },
            rose: {
                solid: '#ef4444',
                light: 'rgba(239, 68, 68, 0.1)',
                grad: ['rgba(239, 68, 68, 0.5)', 'rgba(239, 68, 68, 0.05)']
            },
            indigo: {
                solid: '#6366f1',
                light: 'rgba(99, 102, 241, 0.1)',
                grad: ['rgba(99, 102, 241, 0.5)', 'rgba(99, 102, 241, 0.05)']
            }
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
                        $('#stat_total_stock_value').text(new Intl.NumberFormat().format(response.stats.total_stock_value));
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
                             Object.values(response.charts.stock_grouped).map(d => d.warning),
                             Object.values(response.charts.stock_grouped).map(d => d.over),
                             Object.values(response.charts.stock_grouped).map(d => d.safe)
                        );
                        
                        updateChartData(usageModelChart, 
                            response.charts.usage_model.map(i => i.label.split('|')), 
                            response.charts.usage_model.map(i => i.event),
                            response.charts.usage_model.map(i => i.pp),
                            response.charts.usage_model.map(i => i.trial)
                        );
                        
                        updateChartData(makerChart, 
                            response.charts.maker.map(i => i.maker), 
                            response.charts.maker.map(i => i.on_budget),
                            response.charts.maker.map(i => i.near_loss),
                            response.charts.maker.map(i => i.loss)
                        );

                        // Update Tables
                        renderTable('#balanceTableBody', response.tables.balance, generateBalanceRow);
                        renderTable('#usageTableBody', response.tables.usage, generateUsageRow);

                        const trends = response.charts.trendline;
                        const dates = [...new Set(trends.map(d => d.transaction_date))]; 
                        
                        trendlineChart.data.labels = dates;
                        trendlineChart.data.datasets = buildTrendlineDatasets(trends, dates);
                        trendlineChart.update();

                        // Update Recent Activity
                        renderTable('#historyTableBody', response.tables.history, generateHistoryRow);
                    },
                    error: function(err) {
                        console.error('Filter Error', err);
                    },
                    complete: function() {
                        if (btn && btn.length) {
                            btn.html(originalText).prop('disabled', false);
                        }
                    }
                });
            }

            let isResetting = false;

            // Auto-apply logic
            $('#month_picker, #filterCustomer, #filterModel, #filterBalance, #filterUsage').on('change', function() {
                if (isResetting) return;
                fetchDashboardData($('#filterForm').serialize());
            });

            $('#btnReset').on('click', function() {
                const btn = $(this);
                isResetting = true;
                
                // Reset Form
                $('#month_picker').val('{{ date("Y-m") }}'); // Default to current month
                $('#filterCustomer').val(null).trigger('change');
                $('#filterModel').val(null).trigger('change');
                $('#filterBalance').val(null).trigger('change');
                $('#filterUsage').val(null).trigger('change');
                
                // Fetch Data with reset form ONCE
                fetchDashboardData($('#filterForm').serialize(), btn);
                
                setTimeout(() => { isResetting = false; }, 100);
            });

            // Chart Globals
            let stockStatusChart, usageModelChart, trendlineChart, makerChart;

            // Helper to generate trendline datasets consistently
            function buildTrendlineDatasets(trendsData, datesList) {
                const cats = [...new Set(trendsData.map(d => d.category))];
                return cats.map((cat, idx) => {
                    const colorKeys = Object.keys(chartColors);
                    const color = chartColors[colorKeys[idx % colorKeys.length]];
                    return {
                        label: cat.replace('OUT-', '').split(' ').map(w => w === 'PP' ? w : w.charAt(0).toUpperCase() + w.slice(1).toLowerCase()).join(' '),
                        data: datesList.map(d => (trendsData.find(td => td.transaction_date === d && td.category === cat) || {total: 0}).total),
                        borderColor: color.solid,
                        backgroundColor: color.light,
                        fill: false,
                        tension: 0.5,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        borderWidth: 1.5,
                        pointStyle: 'circle'
                    };
                });
            }


            // Custom Vertical Line Plugin
            const verticalLinePlugin = {
                id: 'verticalLine',
                afterDraw: (chart) => {
                    if (chart.tooltip?._active?.length) {
                        const x = chart.tooltip._active[0].element.x;
                        const yAxis = chart.scales.y;
                        const ctx = chart.ctx;
                        ctx.save();
                        ctx.beginPath();
                        ctx.setLineDash([5, 5]);
                        ctx.moveTo(x, yAxis.top);
                        ctx.lineTo(x, yAxis.bottom);
                        ctx.lineWidth = 1;
                        ctx.strokeStyle = isDark ? '#475569' : '#cbd5e1';
                        ctx.stroke();
                        ctx.restore();
                    }
                }
            };
            function updateChartData(chart, labels, data1, data2, data3, data4) {
                if(!chart) return;
                chart.data.labels = labels;
                if(data1) chart.data.datasets[0].data = data1;
                if(data2) chart.data.datasets[1].data = data2;
                if(data3) chart.data.datasets[2].data = data3;
                if(data4) chart.data.datasets[3].data = data4;
                chart.update();
            }
            function updateChartDataSingle(chart, labels, data) {
                 if(!chart) return;
                 chart.data.labels = labels;
                 chart.data.datasets[0].data = data;
                 chart.update();
            }
            function renderTable(selector, data, rowGenerator, emptyText = 'No data found') {
                const tbody = $(selector);
                tbody.empty();
                if (!data || data.length === 0) {
                    const colCount = $(selector).closest('table').find('thead th').length || 5;
                    tbody.append(`<tr><td colspan="${colCount}" class="p-8 text-center text-slate-400 dark:text-gray-500 italic text-[11px]">${emptyText}</td></tr>`);
                    return;
                }
                data.slice(0, 15).forEach(item => {
                    tbody.append(rowGenerator(item));
                });
            }
             // Row Generators
            function generateBalanceRow(row) {
                 const statusColors = {
                     'Critical': 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400 border-red-100 dark:border-red-800',
                     'Warning': 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 border-amber-100 dark:border-amber-800',
                     'Over': 'bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400 border-primary-100 dark:border-primary-800',
                     'Safe': 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800'
                 };
                 const colorClass = statusColors[row.status] || statusColors['Safe'];
                 let partName = row.part_no + (row.revision ? ' - ' + row.revision : '');
                  return `
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-700/20 transition-colors">
                        <td class="py-1.5 px-3">
                            <p class="text-[11px] font-medium text-slate-700 dark:text-gray-200 tracking-tight leading-tight uppercase">${row.part_no} ${row.revision ? '- ' + row.revision : ''}</p>
                            <p class="text-[9px] text-slate-400 uppercase tracking-tighter">${row.model_name} | ${row.customer_code || '-'}</p>
                        </td>
                        <td class="py-1.5 px-2 text-right">
                            <div class="text-[11px] font-medium text-slate-500 font-mono">${new Intl.NumberFormat().format(row.min_stock)}</div>
                        </td>
                        <td class="py-1.5 px-2 text-right">
                            <div class="text-[11px] font-medium text-slate-800 dark:text-white font-mono">${new Intl.NumberFormat().format(row.current_stock_qty)}</div>
                        </td>
                        <td class="py-1.5 px-3 text-right">
                            <span class="inline-flex px-1.5 py-0.5 rounded-xs text-[9px] font-medium ${colorClass} border uppercase leading-none">${row.status}</span>
                        </td>
                    </tr>
                 `;
            }
            function generateUsageRow(row) {
                const color = row.status == 'Loss' ? 'red' : (row.status == 'Near Loss' ? 'amber' : 'emerald');
                return `
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-700/20 transition-colors">
                        <td class="py-1.5 px-3 text-[11px] font-medium text-slate-700 dark:text-gray-200 uppercase tracking-tight">${row.part_no}</td>
                        <td class="py-1.5 px-2 text-[10px] text-slate-500 dark:text-slate-400 uppercase truncate max-w-[80px]">${row.supplier_name || '-'}</td>
                        <td class="py-1.5 px-2 text-[11px] font-medium text-slate-800 dark:text-white text-right font-mono">${new Intl.NumberFormat().format(row.out_trial)}</td>
                        <td class="py-1.5 px-2 text-[11px] font-medium ${row.gap < 0 ? 'text-red-500' : 'text-emerald-500'} text-right font-mono">${new Intl.NumberFormat().format(row.gap)}</td>
                        <td class="py-1.5 px-3 text-right">
                            <span class="inline-flex px-1.5 py-0.5 rounded-xs text-[9px] font-medium bg-${color}-50 text-${color}-600 dark:bg-${color}-900/30 dark:text-${color}-400 border border-${color}-100 dark:border-${color}-800 uppercase leading-none">${row.status}</span>
                        </td>
                    </tr>
                `;
            }
            function generateHistoryRow(row) {
                const date = new Date(row.transaction_date);
                const createdAt = new Date(row.created_at);
                const dateStr = date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: '2-digit' });
                const timeStr = createdAt.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
                
                return `
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-700/20 transition-colors">
                        <td class="py-1.5 px-3">
                            <p class="text-[11px] font-medium text-slate-700 dark:text-gray-200 tracking-tight leading-tight uppercase">${row.part_no} ${row.revision ? '- ' + row.revision : ''}</p>
                            <p class="text-[9px] text-slate-400 uppercase tracking-tighter">${row.model_name} | ${row.customer_code}</p>
                        </td>
                        <td class="py-1.5 px-2 text-center">
                            <span class="px-1.5 py-0.5 rounded-xs text-[9px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 uppercase">${row.category}</span>
                        </td>
                        <td class="py-1.5 px-2 text-center whitespace-nowrap">
                            <div class="text-[10px] text-slate-500 dark:text-slate-400">${dateStr} <span class="text-[9px] text-slate-400 font-mono ml-1">${timeStr}</span></div>
                        </td>
                        <td class="py-1.5 px-3 text-right">
                            <div class="text-[11px] font-medium text-slate-800 dark:text-white font-mono">${new Intl.NumberFormat().format(row.qty_pcs)}</div>
                        </td>
                    </tr>
                `;
            }

            // --- INITIALIZATION ---
            renderTable('#balanceTableBody', @json($tables['balance'] ?? []), generateBalanceRow, 'All items are currently within safe limits.');
            renderTable('#usageTableBody', @json($tables['usage'] ?? []), generateUsageRow, 'No trial data available.');
            renderTable('#historyTableBody', @json($tables['history'] ?? []), generateHistoryRow, 'No recent activity.');


                if (document.getElementById('stockStatusChart')) {
            stockStatusChart = new Chart(document.getElementById('stockStatusChart'), {
                type: 'bar',
                data: {
                    labels: chartsData.stockLabels,
                    datasets: [{
                            label: 'Critical',
                            data: chartsData.stockData.map(d => d.critical),
                            backgroundColor: chartColors.rose.solid,
                            borderRadius: 2
                        },
                        {
                            label: 'Warning',
                            data: chartsData.stockWarning,
                            backgroundColor: chartColors.amber.solid,
                            borderRadius: 2
                        },
                        {
                            label: 'Over',
                            data: chartsData.stockData.map(d => d.over),
                            backgroundColor: chartColors.primary.solid,
                            borderRadius: 2
                        },
                        {
                            label: 'Safe',
                            data: chartsData.stockData.map(d => d.safe),
                            backgroundColor: chartColors.emerald.solid,
                            borderRadius: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { 
                            stacked: true, 
                            ticks: { color: isDark ? '#94a3b8' : '#64748b', font: { size: 11 } }
                        },
                        y: { 
                            stacked: true, 
                            beginAtZero: true, 
                            ticks: { color: isDark ? '#94a3b8' : '#64748b' }
                        }
                    },
                    plugins: {
                        legend: { 
                            position: 'bottom', 
                            labels: { 
                                color: isDark ? '#94a3b8' : '#64748b',
                                font: { size: 10 },
                                usePointStyle: true,
                                pointStyle: 'rect',
                                padding: 15
                            } 
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
                    datasets: [
                        { label: 'Event', data: chartsData.usageModelEvent, backgroundColor: chartColors.amber.solid, borderRadius: 2 },
                        { label: 'PP', data: chartsData.usageModelPP, backgroundColor: chartColors.indigo.solid, borderRadius: 2 },
                        { label: 'Trial', data: chartsData.usageModelTrial, backgroundColor: chartColors.rose.solid, borderRadius: 2 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { 
                            stacked: true, 
                            ticks: { color: isDark ? '#94a3b8' : '#64748b' }
                        },
                        y: { 
                            stacked: true, 
                            ticks: { color: isDark ? '#94a3b8' : '#64748b' }
                        }
                    },
                    plugins: {
                        legend: { 
                            position: 'bottom', 
                            labels: { 
                                color: isDark ? '#94a3b8' : '#64748b',
                                font: { size: 10 },
                                usePointStyle: true,
                                pointStyle: 'rect',
                                padding: 15
                            } 
                        }
                    }
                }
            });
        }

        if (document.getElementById('trendlineChart')) {
            const trendData = chartsData.trendData;
            const dates = trendData ? [...new Set(trendData.map(d => d.transaction_date))] : [];

            trendlineChart = new Chart(document.getElementById('trendlineChart'), {
                type: 'line',
                plugins: [verticalLinePlugin],
                data: {
                    labels: dates,
                    datasets: buildTrendlineDatasets(trendData || [], dates)
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        x: {
                            ticks: { color: isDark ? '#94a3b8' : '#64748b', font: { size: 10 } },
                            grid: { 
                                display: true,
                                color: isDark ? 'rgba(71, 85, 105, 0.2)' : 'rgba(226, 232, 240, 0.6)',
                                drawBorder: false
                            }
                        },
                        y: {
                            stacked: false,
                            beginAtZero: true,
                            ticks: { color: isDark ? '#94a3b8' : '#64748b', font: { size: 10 } },
                            grid: { 
                                display: true,
                                color: isDark ? 'rgba(71, 85, 105, 0.2)' : 'rgba(226, 232, 240, 0.6)',
                                drawBorder: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { 
                                color: isDark ? '#94a3b8' : '#64748b',
                                font: { size: 10 },
                                usePointStyle: true,
                                pointStyle: 'circle',
                                padding: 15
                            }
                        },
                        tooltip: {
                            enabled: true,
                            usePointStyle: true,
                            backgroundColor: isDark ? '#1e293b' : '#ffffff',
                            titleColor: isDark ? '#f8fafc' : '#1e293b',
                            bodyColor: isDark ? '#94a3b8' : '#64748b',
                            borderColor: isDark ? '#334155' : '#e2e8f0',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) label += ': ';
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat().format(context.parsed.y) + ' Item';
                                    }
                                    return label;
                                },
                                labelPointStyle: function(context) {
                                    return {
                                        pointStyle: 'circle',
                                        rotation: 0
                                    };
                                }
                            }
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
                    datasets: [
                        {
                            label: 'On Budget',
                            data: chartsData.makerOnBudget,
                            backgroundColor: chartColors.emerald.solid,
                            borderRadius: 2
                        },
                        {
                            label: 'Near Loss',
                            data: chartsData.makerNearLoss,
                            backgroundColor: chartColors.amber.solid,
                            borderRadius: 2
                        },
                        {
                            label: 'Loss',
                            data: chartsData.makerLoss,
                            backgroundColor: chartColors.rose.solid,
                            borderRadius: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { 
                            stacked: true, 
                            ticks: { color: isDark ? '#94a3b8' : '#64748b' }
                        },
                        y: { 
                            stacked: true, 
                            ticks: { color: isDark ? '#94a3b8' : '#64748b' }
                        }
                    },
                    plugins: {
                        legend: { 
                            position: 'bottom', 
                            labels: { 
                                color: isDark ? '#94a3b8' : '#64748b',
                                font: { size: 10 },
                                usePointStyle: true,
                                pointStyle: 'rect',
                                padding: 15
                            } 
                        }
                    }
                }
            });
        }
    });
</script>
<style>
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endpush