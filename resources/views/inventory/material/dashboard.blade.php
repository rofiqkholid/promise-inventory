@extends('layouts.app')

@section('title', 'Inventory Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="dashboard-container w-full h-auto overflow-y-auto lg:h-[calc(100vh-85px)] lg:overflow-hidden flex flex-col gap-2 pb-0 custom-scrollbar lg:pb-0">
    {{-- Header, KPIs & Filters --}}
    <div class="flex flex-wrap items-center justify-between gap-y-2 gap-x-4">
        <!-- Section 1: Title Section -->
        <div class="flex-none">
            <h2 class="text-xl xl:text-2xl font-bold text-gray-800 dark:text-white leading-tight mb-0.5">Inventory Overview</h2>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-tight">Stock monitoring and transaction analytics</p>
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
                        <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400 tracking-tight leading-none mb-1 whitespace-nowrap">{{ $stat['label'] }}</p>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 leading-none tracking-tight whitespace-nowrap">
                            <span id="{{ $stat['id'] }}">{{ $stat['val'] }}</span> <span class="text-[9px] text-slate-400 font-normal ml-0.5">{{ $stat['unit'] }}</span>
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
            <input type="hidden" name="stock_mode" id="stock_mode" value="current">
            <div class="flex flex-col lg:flex-row gap-4 lg:items-end">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-7 gap-4 flex-1">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">Period</label>
                        <input type="month" id="month_picker" name="month_year" value="{{ $filters['month_year'] }}" class="w-full text-xs font-medium border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-900 dark:text-white rounded-xs h-[40px] px-3 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all focus:border-primary-500">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">Data Mode</label>
                        <select id="filterAccumulate" name="accumulate" class="w-full text-xs">
                            <option value="single" {{ ($filters['accumulate'] ?? '') === 'single' ? 'selected' : '' }}>Single Month</option>
                            <option value="ytd" {{ ($filters['accumulate'] ?? 'ytd') === 'ytd' ? 'selected' : '' }}>Accumulated (YTD)</option>
                            <option value="all" {{ ($filters['accumulate'] ?? '') === 'all' ? 'selected' : '' }}>Accumulated (All-Time)</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">Customer</label>
                        <select id="filterCustomer" name="customer[]" class="w-full text-xs"></select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">Model</label>
                        <select id="filterModel" name="model[]" class="w-full text-xs"></select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">Project Status</label>
                        <select id="filterProjectStatus" name="project_status" class="w-full text-xs">
                            <option value="">All Status</option>
                            <option value="Regular">Regular</option>
                            <option value="Project">Project</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">Balance Status</label>
                        <select id="filterBalance" name="status_balance[]" class="w-full text-xs"></select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">Usage Status</label>
                        <select id="filterUsage" name="status_usage[]" class="w-full text-xs"></select>
                    </div>
                </div>

                <div class="flex gap-2 pt-2 xl:pt-0">
                    <button type="button" id="btnReset" class="h-9 px-6 bg-slate-100 hover:bg-slate-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-xs text-xs font-medium text-slate-600 dark:text-gray-300 transition-all border border-slate-200 dark:border-gray-600">
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
            <div class="chart-card bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative h-[250px] lg:h-auto lg:flex-[55] min-h-0">
                <div class="flex-none flex justify-between items-center mb-1">
                    <h3 class="text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center min-w-0 pr-2">
                        <i class="fa-solid fa-chart-column mr-2 text-primary-500 flex-shrink-0"></i> 
                        <span class="truncate">Stock status</span> 
                        <span class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-slate-700 text-[9px] font-medium text-slate-500 dark:text-slate-400 border border-slate-200/50 dark:border-slate-600/50 flex-shrink-0 whitespace-nowrap">Item part</span>
                    </h3>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <div class="flex bg-gray-100 dark:bg-gray-700 p-0.5 rounded-xs">
                            <button type="button" onclick="switchStockMode('current')" id="btnStockCurrent" class="px-2 py-1 rounded-xs text-[9px] font-medium transition-all bg-white dark:bg-gray-600 text-primary-600 shadow-sm">Usage</button>
                            <button type="button" onclick="switchStockMode('old')" id="btnStockOld" class="px-2 py-1 rounded-xs text-[9px] font-medium transition-all text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Old</button>
                        </div>
                        <div class="flex items-center gap-1 border-l border-gray-200 dark:border-gray-700 pl-2">
                            <button id="stockStatusChartPrev" onclick="paginateChart('stockStatusChart', -1)" disabled class="w-6 h-6 flex items-center justify-center rounded-xs bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-500 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"><i class="fa-solid fa-chevron-left text-xs"></i></button>
                            <button id="stockStatusChartNext" onclick="paginateChart('stockStatusChart', 1)" disabled class="w-6 h-6 flex items-center justify-center rounded-xs bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-500 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"><i class="fa-solid fa-chevron-right text-xs"></i></button>
                        </div>
                    </div>
                </div>
                <div class="relative w-full flex-1 min-h-0"><canvas id="stockStatusChart"></canvas></div>
            </div>

            {{-- Balance Warnings Table --}}
            <div class="table-container bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative h-[320px] lg:h-auto lg:flex-[45] min-h-0">
                <div class="flex-none flex justify-between items-center mb-1">
                    <h3 class="text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center">
                        <i class="fa-solid fa-triangle-exclamation mr-2 text-rose-500"></i> Balance Warnings
                    </h3>
                </div>
                <div class="overflow-y-auto flex-1 custom-scrollbar border border-gray-100 dark:border-gray-700/50 rounded-xs">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/80 dark:bg-gray-700/50 sticky top-0 z-10 backdrop-blur-md">
                            <tr>
                                <th class="py-2 px-3 text-[10px] font-medium text-slate-500 tracking-wider">Part No</th>
                                <th class="py-2 px-2 text-[10px] font-medium text-slate-500 tracking-wider text-right">Min</th>
                                <th class="py-2 px-2 text-[10px] font-medium text-slate-500 tracking-wider text-right">Actual</th>
                                <th class="py-2 px-3 text-[10px] font-medium text-slate-500 tracking-wider text-right">Status</th>
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
            <div class="chart-card bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative h-[250px] lg:h-auto lg:flex-[55] min-h-0">
                <div class="flex-none flex justify-between items-center mb-1">
                    <h3 id="usageChartTitle" class="text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center min-w-0 pr-2">
                        <i class="fa-solid fa-chart-pie mr-2 text-amber-500 flex-shrink-0"></i> 
                        <span class="truncate">Supply by Makers</span> 
                        <span class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-slate-700 text-[8px] font-medium text-slate-500 dark:text-slate-400 tracking-wider border border-slate-200/50 dark:border-slate-600/50 flex-shrink-0 whitespace-nowrap">Item Part</span>
                    </h3>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <div class="flex bg-gray-100 dark:bg-gray-700 p-0.5 rounded-xs">
                            <button type="button" onclick="switchUsageChart('model')" id="btnUsageModel" class="px-2 py-1 rounded-xs text-[9px] font-medium transition-all text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Model</button>
                            <button type="button" onclick="switchUsageChart('maker')" id="btnUsageMaker" class="px-2 py-1 rounded-xs text-[9px] font-medium transition-all bg-white dark:bg-gray-600 text-primary-600 dark:hover:text-gray-300 shadow-sm">Maker</button>
                        </div>
                        <div class="flex items-center gap-1 border-l border-gray-200 dark:border-gray-700 pl-2">
                            <button id="usageChartPrev" onclick="paginateActiveUsageChart(-1)" disabled class="w-6 h-6 flex items-center justify-center rounded-xs bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-500 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"><i class="fa-solid fa-chevron-left text-[10px]"></i></button>
                            <button id="usageChartNext" onclick="paginateActiveUsageChart(1)" disabled class="w-6 h-6 flex items-center justify-center rounded-xs bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-500 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"><i class="fa-solid fa-chevron-right text-[10px]"></i></button>
                        </div>
                    </div>
                </div>
                <div class="relative w-full flex-1 min-h-0">
                    <div id="containerUsageModel" class="h-full hidden"><canvas id="usageModelChart"></canvas></div>
                    <div id="containerUsageMaker" class="h-full"><canvas id="makerChart"></canvas></div>
                </div>
            </div>

            {{-- Material Usage Table --}}
            <div class="table-container bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative h-[320px] lg:h-auto lg:flex-[45] min-h-0">
                <div class="flex-none flex justify-between items-center mb-1">
                    <h3 class="text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center">
                        <i class="fa-solid fa-vial mr-2 text-indigo-500"></i> Material Usage Detail
                    </h3>
                </div>
                <div class="overflow-y-auto flex-1 custom-scrollbar border border-gray-100 dark:border-gray-700/50 rounded-xs">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/80 dark:bg-gray-700/50 sticky top-0 z-10 backdrop-blur-md">
                            <tr>
                                <th class="py-2 px-3 text-[10px] font-medium text-slate-500 tracking-wider">Part No</th>
                                <th class="py-2 px-2 text-[10px] font-medium text-slate-500 tracking-wider">Supplier</th>
                                <th class="py-2 px-2 text-[10px] font-medium text-slate-500 tracking-wider text-right">Actual</th>
                                <th class="py-2 px-2 text-[10px] font-medium text-slate-500 tracking-wider text-right">Gap</th>
                                <th class="py-2 px-3 text-[10px] font-medium text-slate-500 tracking-wider text-right">Status</th>
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
            <div class="chart-card bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative h-[250px] lg:h-auto lg:flex-[55] min-h-0">
                <div class="flex-none flex justify-between items-center mb-1">
                    <h3 class="text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center min-w-0">
                        <i class="fa-solid fa-arrow-trend-up mr-2 text-emerald-500 flex-shrink-0"></i> 
                        <span class="truncate">Transaction Trend</span> 
                        <span class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-slate-700 text-[8px] font-medium text-slate-500 dark:text-slate-400 tracking-wider border border-slate-200/50 dark:border-slate-600/50 flex-shrink-0 whitespace-nowrap">Item Part</span>
                    </h3>
                </div>
                <div class="relative w-full flex-1 min-h-0"><canvas id="trendlineChart"></canvas></div>
            </div>

            {{-- Recent Transactions --}}
            <div class="table-container bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative h-[320px] lg:h-auto lg:flex-[45] min-h-0">
                <div class="flex-none flex justify-between items-center mb-1">
                    <h3 class="text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center">
                        <i class="fa-solid fa-clock-rotate-left mr-2 text-primary-500"></i> Recent Activity
                    </h3>
                </div>
                <div class="overflow-y-auto flex-1 custom-scrollbar border border-gray-100 dark:border-gray-700/50 rounded-xs">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/80 dark:bg-gray-700/50 sticky top-0 z-10 backdrop-blur-md">
                            <tr>
                                <th class="py-2 px-3 text-[10px] font-medium text-slate-500 tracking-wider">Part No</th>
                                <th class="py-2 px-2 text-[10px] font-medium text-slate-500 tracking-wider text-center">Type</th>
                                <th class="py-2 px-2 text-[10px] font-medium text-slate-500 tracking-wider text-center">Date</th>
                                <th class="py-2 px-3 text-[10px] font-medium text-slate-500 tracking-wider text-right">Qty</th>
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

{{-- Drilldown Modal --}}
<div id="drilldownModal" class="fixed inset-0 z-50 hidden" aria-modal="true">
    <div class="absolute inset-0 bg-black/40" onclick="closeDrilldownModal()"></div>
    <div class="absolute right-0 top-0 bottom-0 w-full max-w-4xl bg-white dark:bg-gray-900 shadow-2xl flex flex-col transform transition-transform duration-300 translate-x-full" id="drilldownPanel">
        {{-- Header --}}
        <div class="flex-none flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <p class="text-xs font-medium text-primary-500 tracking-wider">Detail Explorer</p>
                    <span id="drilldownCountBadge" class="px-1.5 py-0.5 rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 text-[9px] font-bold">0</span>
                </div>
                <h2 id="drilldownTitle" class="text-base font-bold text-gray-800 dark:text-gray-100 truncate max-w-[600px]">Loading...</h2>
            </div>
            <button onclick="closeDrilldownModal()" class="w-8 h-8 flex items-center justify-center rounded-xs bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 transition-colors">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
        {{-- Loader --}}
        <div id="drilldownLoader" class="flex-1 flex items-center justify-center">
            <div class="text-center">
                <i class="fa-solid fa-spinner fa-spin text-2xl text-primary-400 mb-3"></i>
                <p class="text-[11px] text-slate-400">Fetching data...</p>
            </div>
        </div>
        {{-- Content --}}
        <div id="drilldownContent" class="flex-1 flex-col hidden min-h-0">
            {{-- Quick Filters (Segmented Control Style) --}}
            <div id="drilldownLegendContainer" class="px-5 py-3 border-b border-gray-100 dark:border-gray-800 bg-gray-50/30 dark:bg-gray-800/20">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-[10px] font-medium text-slate-400 dark:text-slate-500 tracking-wider">Filter by status</p>
                </div>
                <div id="drilldownLegendButtons" class="inline-flex p-1 bg-gray-100 dark:bg-gray-800/80 rounded-lg gap-1">
                    {{-- Buttons injected by JS --}}
                </div>
            </div>

            <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 sticky top-0 z-20 flex flex-col md:flex-row gap-3 items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-medium text-slate-400 whitespace-nowrap tracking-wider">Show</span>
                    <select id="drilldownPageSize" onchange="resetDrilldownAndFetch()" class="h-8 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-[11px] px-2 focus:ring-1 focus:ring-primary-500 outline-none cursor-pointer">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-[10px] font-medium text-slate-400 whitespace-nowrap tracking-wider">entries</span>
                </div>
                <div class="relative w-full md:w-60">
                    <input type="text" id="drilldownSearch" placeholder="Search Part No..." class="w-full h-8 pl-9 pr-4 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-[11px] focus:outline-none focus:ring-1 focus:ring-primary-500 transition-all placeholder:text-gray-400">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-[9px]"></i>
                    </div>
                </div>
            </div>
            <div class="flex-1 relative min-h-0">
                {{-- Partial Table Loader --}}
                <div id="drilldownTableLoader" class="hidden absolute inset-0 bg-white/60 dark:bg-gray-900/60 z-30 flex items-center justify-center backdrop-blur-[1px] transition-all">
                    <div class="flex flex-col items-center">
                        <i class="fa-solid fa-circle-notch fa-spin text-xl text-primary-500 mb-2"></i>
                        <span class="text-[10px] font-medium text-slate-500 tracking-wider">Updating...</span>
                    </div>
                </div>

                <div class="h-full overflow-y-auto custom-scrollbar">
                    <table class="w-full text-left text-[11px]">
                        <thead id="drilldownHead" class="bg-gray-50 dark:bg-gray-800 sticky top-0 z-10">
                        </thead>
                        <tbody id="drilldownBody" class="divide-y divide-slate-100 dark:divide-gray-700">
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- Pagination Footer --}}
            <div class="flex-none px-5 py-3 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between">
                <div class="text-[10px] text-slate-500 dark:text-slate-400">
                    Showing <span id="ddPageStart">0</span>-<span id="ddPageEnd">0</span> of <span id="ddTotal">0</span>
                </div>
                <div class="flex items-center gap-1">
                    <button onclick="changeDrilldownPage(-1)" id="ddPrev" class="w-7 h-7 flex items-center justify-center rounded-xs bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-500 hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed transition-all">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    </button>
                    <div class="px-2 text-[10px] font-bold text-slate-600 dark:text-slate-300">
                        Page <span id="ddCurrentPage">1</span>
                    </div>
                    <button onclick="changeDrilldownPage(1)" id="ddNext" class="w-7 h-7 flex items-center justify-center rounded-xs bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-500 hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed transition-all">
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
    Chart.register(ChartDataLabels);

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
        
        // Global Tooltip & Interaction
        const commonInteraction = { mode: 'index', intersect: false };
        const commonTooltip = {
            enabled: true,
            usePointStyle: true,
            backgroundColor: isDark ? '#1e293b' : '#ffffff',
            titleColor: isDark ? '#f8fafc' : '#1e293b',
            bodyColor: isDark ? '#94a3b8' : '#64748b',
            borderColor: isDark ? '#334155' : '#e2e8f0',
            borderWidth: 1,
            padding: 12,
            displayColors: true,
            boxPadding: 6,
            callbacks: {
                labelPointStyle: function(context) {
                    return { pointStyle: 'rect', rotation: 0 };
                },
                label: function(context) {
                    let label = context.dataset.label || '';
                    if (label) label += ': ';
                    if (context.parsed.y !== null) {
                        label += new Intl.NumberFormat().format(context.parsed.y) + ' Item';
                    }
                    return label;
                }
            }
        };

        const commonDataLabels = {
            backgroundColor: isDark ? 'rgba(30, 41, 59, 0.85)' : 'rgba(255, 255, 255, 0.85)',
            borderRadius: 1,
            color: isDark ? '#f8fafc' : '#1e293b',
            font: { weight: 'bold', size: 10 },
            formatter: (value) => value > 0 ? new Intl.NumberFormat().format(value) : '',
            padding: { top: 2, bottom: 0, left: 4, right: 4 },
            anchor: 'center',
            align: 'center',
            display: (context) => context.dataset.data[context.dataIndex] > 0 ? 'auto' : false,
            clip: false
        };
        
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
                '<i class="fa-solid fa-chart-pie mr-2 text-amber-500 flex-shrink-0"></i> ' + 
                '<span class="truncate">' + (isModel ? 'Usage by Models' : 'Supply by Makers') + '</span>' + 
                ' <span class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-slate-700 text-[8px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest border border-slate-200/50 dark:border-slate-600/50 flex-shrink-0 whitespace-nowrap">Item Part</span>'
            );
            
            // Toggle container
            $('#containerUsageModel').toggleClass('hidden', !isModel);
            $('#containerUsageMaker').toggleClass('hidden', isModel);
            
            // Toggle button style
            $('#btnUsageModel').toggleClass('bg-white dark:bg-gray-600 text-primary-600 shadow-sm', isModel)
                .toggleClass('text-slate-500 hover:text-slate-700', !isModel);
            $('#btnUsageMaker').toggleClass('bg-white dark:bg-gray-600 text-primary-600 shadow-sm', !isModel)
                .toggleClass('text-slate-500 hover:text-slate-700', isModel);
                
            // Update shared pagination buttons
            const activeId = isModel ? 'usageModelChart' : 'makerChart';
            if(window.chartStore && window.chartStore[activeId]) {
                const store = window.chartStore[activeId];
                const end = store.page * store.pageSize + store.pageSize;
                $('#usageChartPrev').prop('disabled', store.page <= 0);
                $('#usageChartNext').prop('disabled', end >= store.labels.length);
            }
        };
        
        window.switchStockMode = function(mode) {
            const isCurrent = mode === 'current';
            $('#btnStockCurrent').toggleClass('bg-white dark:bg-gray-600 text-primary-600 shadow-sm', isCurrent)
                .toggleClass('text-slate-500 hover:text-slate-700', !isCurrent);
            $('#btnStockOld').toggleClass('bg-white dark:bg-gray-600 text-primary-600 shadow-sm', !isCurrent)
                .toggleClass('text-slate-500 hover:text-slate-700', isCurrent);
                
            $('#stock_mode').val(mode);
            fetchDashboardData($('#filterForm').serialize());
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

        $('#filterProjectStatus').select2({
            placeholder: "Select Status",
            allowClear: true,
            multiple: false,
            width: '100%'
        });

        $('#filterAccumulate').select2({
            minimumResultsForSearch: -1,
            width: '100%'
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
                        updateStockChartData(response);
                        
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
            $('#month_picker, #filterAccumulate, #filterCustomer, #filterModel, #filterBalance, #filterUsage, #filterProjectStatus').on('change', function() {
                if (isResetting) return;
                fetchDashboardData($('#filterForm').serialize());
            });

            $('#btnReset').on('click', function() {
                const btn = $(this);
                isResetting = true;
                
                // Reset Form
                $('#month_picker').val('{{ date("Y-m") }}'); // Default to current month
                $('#filterAccumulate').val('ytd').trigger('change');
                $('#filterCustomer').val(null).trigger('change');
                $('#filterModel').val(null).trigger('change');
                $('#filterBalance').val(null).trigger('change');
                $('#filterUsage').val(null).trigger('change');
                $('#filterProjectStatus').val(null).trigger('change');
                
                // Fetch Data with reset form ONCE
                fetchDashboardData($('#filterForm').serialize(), btn);
                
                setTimeout(() => { isResetting = false; }, 100);
            });

            // Chart Globals
            let stockStatusChart, usageModelChart, trendlineChart, makerChart;

            // Helper to generate trendline datasets consistently
            function buildTrendlineDatasets(trendsData, datesList) {
                const cats = [...new Set(trendsData.map(d => d.category))];
                const colorMap = {
                    'IN': chartColors.emerald,
                    'OUT-EVENT': chartColors.amber,
                    'OUT-PP': chartColors.indigo,
                    'OUT-TRIAL': chartColors.rose
                };
                
                return cats.map((cat) => {
                    const color = colorMap[cat] || chartColors.primary;
                    return {
                        label: cat.replace('OUT-', '').split(' ').map(w => w === 'PP' ? w : w.charAt(0).toUpperCase() + w.slice(1).toLowerCase()).join(' '),
                        data: datesList.map(d => (trendsData.find(td => td.transaction_date === d && td.category === cat) || {total: 0}).total),
                        borderColor: color.solid,
                        backgroundColor: color.light,
                        fill: false,
                        tension: 0.5,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        borderWidth: 2,
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
                        ctx.lineWidth = 1.5;
                        ctx.strokeStyle = document.documentElement.classList.contains('dark') ? '#64748b' : '#94a3b8';
                        ctx.stroke();
                        ctx.restore();
                    }
                }
            };
            window.chartStore = {};
            window.paginateChart = function(id, dir) {
                if(chartStore[id]) {
                    chartStore[id].page += dir;
                    renderChartPage(id);
                }
            };
            window.paginateActiveUsageChart = function(dir) {
                const activeId = document.getElementById('containerUsageModel').classList.contains('hidden') ? 'makerChart' : 'usageModelChart';
                paginateChart(activeId, dir);
            };
            
            function renderChartPage(id) {
                const store = chartStore[id];
                const chart = id === 'stockStatusChart' ? stockStatusChart : (id === 'usageModelChart' ? usageModelChart : (id === 'makerChart' ? makerChart : trendlineChart));
                if(!chart || !store) return;
                
                const start = store.page * store.pageSize;
                const end = start + store.pageSize;
                
                chart.data.labels = store.labels.slice(start, end);
                store.datasets.forEach((data, i) => {
                    if(chart.data.datasets[i]) {
                        chart.data.datasets[i].data = data.slice(start, end);
                    }
                });
                chart.update();
                
                const btnPrev = id === 'usageModelChart' || id === 'makerChart' ? 'usageChartPrev' : id + 'Prev';
                const btnNext = id === 'usageModelChart' || id === 'makerChart' ? 'usageChartNext' : id + 'Next';
                
                $(`#${btnPrev}`).prop('disabled', store.page <= 0);
                $(`#${btnNext}`).prop('disabled', end >= store.labels.length);
            }

            function updateChartData(chart, labels, data1, data2, data3, data4) {
                if(!chart) return;
                const id = chart.canvas.id;
                
                if (id === 'trendlineChart') {
                    chart.data.labels = labels;
                    if(data1) chart.data.datasets[0].data = data1;
                    if(data2) chart.data.datasets[1].data = data2;
                    if(data3) chart.data.datasets[2].data = data3;
                    if(data4) chart.data.datasets[3].data = data4;
                    chart.update();
                    return;
                }

                chartStore[id] = chartStore[id] || { page: 0, pageSize: 6 };
                chartStore[id].labels = labels;
                chartStore[id].datasets = [data1, data2, data3, data4].filter(d => d !== undefined);
                chartStore[id].page = 0;
                renderChartPage(id);
            }

            function updateStockChartData(response) {
                if (!stockStatusChart) return;
                const labels = Object.keys(response.charts.stock_grouped).map(l => l.split('|'));
                const dataValues = Object.values(response.charts.stock_grouped);
                const isOld = $('#stock_mode').val() === 'old';

                if (isOld) {
                    stockStatusChart.data.datasets = [
                        {
                            label: 'Oldstock OK',
                            data: dataValues.map(d => d.oldstock_ok || 0),
                            backgroundColor: chartColors.emerald.solid,
                            borderRadius: 2
                        },
                        {
                            label: 'Oldstock NG',
                            data: dataValues.map(d => d.oldstock_ng || 0),
                            backgroundColor: chartColors.rose.solid,
                            borderRadius: 2
                        },
                        {
                            label: 'Other',
                            data: dataValues.map(d => d.other || 0),
                            backgroundColor: '#64748b',
                            borderRadius: 2
                        }
                    ];
                } else {
                    stockStatusChart.data.datasets = [
                        {
                            label: 'Critical',
                            data: dataValues.map(d => d.critical || 0),
                            backgroundColor: chartColors.rose.solid,
                            borderRadius: 2
                        },
                        {
                            label: 'Warning',
                            data: dataValues.map(d => d.warning || 0),
                            backgroundColor: chartColors.amber.solid,
                            borderRadius: 2
                        },
                        {
                            label: 'Over',
                            data: dataValues.map(d => d.over || 0),
                            backgroundColor: chartColors.primary.solid,
                            borderRadius: 2
                        },
                        {
                            label: 'Safe',
                            data: dataValues.map(d => d.safe || 0),
                            backgroundColor: chartColors.emerald.solid,
                            borderRadius: 2
                        }
                    ];
                }

                chartStore['stockStatusChart'] = chartStore['stockStatusChart'] || { page: 0, pageSize: 6 };
                chartStore['stockStatusChart'].labels = labels;
                chartStore['stockStatusChart'].datasets = stockStatusChart.data.datasets.map(ds => ds.data);
                chartStore['stockStatusChart'].page = 0;
                renderChartPage('stockStatusChart');
            }
            function updateChartDataSingle(chart, labels, data) {
                 updateChartData(chart, labels, data);
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
                     'Safe': 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800',
                     'Oldstock OK': 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800',
                     'Oldstock ok': 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800',
                     'Oldstock NG': 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400 border-red-100 dark:border-red-800',
                     'Oldstock ng': 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400 border-red-100 dark:border-red-800',
                     'Unknown': 'bg-slate-50 text-slate-600 dark:bg-slate-900/30 dark:text-slate-400 border-slate-100 dark:border-slate-800'
                 };
                 const colorClass = statusColors[row.status] || statusColors['Unknown'];
                 
                 let actionIcon = '';
                 const isOldMode = $('#stock_mode').val() === 'old';
                 if (!isOldMode && (row.status === 'Critical' || row.status === 'Warning')) {
                     if (row.action_status === 'Process') {
                         actionIcon = '<i class="fa-solid fa-clock text-amber-500 ml-1.5" title="In Process"></i>';
                     } else if (row.action_status === 'Ordered') {
                         actionIcon = '<i class="fa-solid fa-circle-check text-emerald-500 ml-1.5" title="Ordered"></i>';
                     } else {
                         actionIcon = '<i class="fa-solid fa-circle-exclamation text-rose-500 ml-1.5" title="Need Action"></i>';
                     }
                 }

                  return `
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-700/20 transition-colors">
                        <td class="py-1.5 px-3">
                            <div class="flex items-center">
                                <p class="text-[11px] font-medium text-slate-700 dark:text-gray-200 tracking-tight leading-tight">${row.part_no} ${row.revision ? '- ' + row.revision : ''}</p>
                                ${actionIcon}
                            </div>
                            <p class="text-[9px] text-slate-400 tracking-tighter">${row.model_name || '-'} | ${row.customer_code || '-'}</p>
                            ${row.action_remark ? `<p class="text-[8px] text-primary-500 italic mt-0.5"><i class="fa-solid fa-message mr-1 opacity-70"></i>${row.action_remark}</p>` : ''}
                        </td>
                        <td class="py-1.5 px-2 text-right">
                            <div class="text-[11px] font-medium text-slate-500 font-mono">${new Intl.NumberFormat().format(row.min_stock)}</div>
                        </td>
                        <td class="py-1.5 px-2 text-right">
                            <div class="text-[11px] font-medium text-slate-800 dark:text-white font-mono">${new Intl.NumberFormat().format(row.current_stock_pcs)}</div>
                        </td>
                        <td class="py-1.5 px-3 text-right">
                            <span class="inline-flex px-1.5 py-0.5 rounded-xs text-[9px] font-medium ${colorClass} border leading-none">${row.status}</span>
                        </td>
                    </tr>
                 `;
            }
            function generateUsageRow(row) {
                const color = row.status == 'Loss' ? 'red' : (row.status == 'Near Loss' ? 'amber' : 'emerald');
                return `
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-700/20 transition-colors">
                        <td class="py-1.5 px-3">
                            <p class="text-[11px] font-medium text-slate-700 dark:text-gray-200 tracking-tight leading-tight">${row.part_no} ${row.revision ? '- ' + row.revision : ''}</p>
                            <p class="text-[9px] text-slate-400 tracking-tighter">${row.model_name || '-'} | ${row.customer_code || '-'}</p>
                        </td>
                        <td class="py-1.5 px-2 text-[10px] text-slate-500 dark:text-slate-400 truncate max-w-[80px]">${row.supplier_name || '-'}</td>
                        <td class="py-1.5 px-2 text-[11px] font-medium text-slate-800 dark:text-white text-right font-mono">${new Intl.NumberFormat().format(row.out_trial)}</td>
                        <td class="py-1.5 px-2 text-[11px] font-medium ${row.gap < 0 ? 'text-red-500' : 'text-slate-500'} text-right font-mono">${new Intl.NumberFormat().format(row.gap)}</td>
                        <td class="py-1.5 px-3 text-right">
                            <span class="inline-flex px-1.5 py-0.5 rounded-xs text-[9px] font-medium bg-${color}-50 text-${color}-600 dark:bg-${color}-900/30 dark:text-${color}-400 border border-${color}-100 dark:border-${color}-800 leading-none">${row.status}</span>
                        </td>
                    </tr>
                `;
            }
            function generateHistoryRow(row) {
                const date = new Date(row.transaction_date);
                const createdAt = new Date(row.created_at);
                const dateStr = date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: '2-digit' });
                const timeStr = createdAt.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
                
                const colorMap = {
                    'IN': 'emerald',
                    'OUT-EVENT': 'amber',
                    'OUT-PP': 'indigo',
                    'OUT-TRIAL': 'rose'
                };
                const color = colorMap[row.category] || 'slate';
                const badgeClass = `bg-${color}-50 text-${color}-600 dark:bg-${color}-900/30 dark:text-${color}-400 border-${color}-100 dark:border-${color}-800`;

                return `
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-700/20 transition-colors">
                        <td class="py-1.5 px-3">
                            <p class="text-[11px] font-medium text-slate-700 dark:text-gray-200 tracking-tight leading-tight">${row.part_no} ${row.revision ? '- ' + row.revision : ''}</p>
                            <p class="text-[9px] text-slate-400 tracking-tighter">${row.model_name || '-'} | ${row.customer_code || '-'}</p>
                        </td>
                        <td class="py-1.5 px-2 text-center">
                            <span class="px-1.5 py-0.5 rounded-xs text-[9px] font-bold border whitespace-nowrap ${badgeClass}">${row.category}</span>
                        </td>
                        <td class="py-1.5 px-2 text-center whitespace-nowrap">
                            <div class="text-[10px] text-slate-500 dark:text-slate-400">${dateStr} <span class="text-[9px] text-slate-400 font-mono ml-1">${timeStr}</span></div>
                        </td>
                        <td class="py-1.5 px-3 text-right">
                            <div class="text-[11px] font-medium text-slate-800 dark:text-white font-mono">
                                ${new Intl.NumberFormat().format(row.qty)} 
                                <span class="text-[9px] text-slate-400 font-normal uppercase">
                                    ${(row.unit_name || '').toUpperCase() === 'COIL' ? 'KG' : ((row.unit_name || '').toUpperCase() === 'TRAPEZOID' ? 'SHEET' : (row.unit_name || 'UNIT'))}
                                </span>
                            </div>
                            <div class="text-[9px] text-slate-500 font-mono">
                                ${new Intl.NumberFormat().format(row.qty_pcs)} 
                                <span class="text-[8px] opacity-70 uppercase">
                                    ${(row.unit_name || '').toUpperCase() === 'COIL' ? 'KG' : ((row.unit_name || '').toUpperCase() === 'TRAPEZOID' ? 'SHEET' : 'PCS')}
                                </span>
                            </div>
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
                    onClick: (e, elements) => {
                        if (elements.length > 0) {
                            const i = elements[0].index;
                            const datasetIndex = elements[0].datasetIndex;
                            const label = stockStatusChart.data.labels[i];
                            const labelStr = Array.isArray(label) ? label.join('|') : label;
                            const status = stockStatusChart.data.datasets[datasetIndex].label;
                            openDrilldown('stock', labelStr, '');
                        }
                    },
                    onHover: (e, el) => { e.native.target.style.cursor = el[0] ? 'pointer' : 'default'; },
                    interaction: commonInteraction,
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
                            grace: '20%',
                            ticks: { 
                                color: isDark ? '#94a3b8' : '#64748b', 
                                font: { size: 11 },
                                precision: 0,
                                callback: (v) => Math.floor(v) === v ? v : ''
                            }
                        }
                    },
                    plugins: {
                        tooltip: commonTooltip,
                        datalabels: commonDataLabels,
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
                    onClick: (e, elements) => {
                        if (elements.length > 0) {
                            const i = elements[0].index;
                            const datasetIndex = elements[0].datasetIndex;
                            const label = usageModelChart.data.labels[i];
                            const labelStr = Array.isArray(label) ? label.join('|') : label;
                            const category = usageModelChart.data.datasets[datasetIndex].label;
                            // Map 'Event' -> 'OUT-EVENT', etc.
                            const statusMap = { 'Event': 'OUT-EVENT', 'PP': 'OUT-PP', 'Trial': 'OUT-TRIAL' };
                            openDrilldown('usage_model', labelStr, '');
                        }
                    },
                    onHover: (e, el) => { e.native.target.style.cursor = el[0] ? 'pointer' : 'default'; },
                    interaction: commonInteraction,
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
                            grace: '20%',
                            ticks: { 
                                color: isDark ? '#94a3b8' : '#64748b', 
                                font: { size: 11 },
                                precision: 0,
                                callback: (v) => Math.floor(v) === v ? v : ''
                            }
                        }
                    },
                    plugins: {
                        tooltip: commonTooltip,
                        datalabels: commonDataLabels,
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
                    onClick: (e, elements) => {
                        if (elements.length > 0) {
                            const i = elements[0].index;
                            const datasetIndex = elements[0].datasetIndex;
                            const label = trendlineChart.data.labels[i];
                            const category = trendlineChart.data.datasets[datasetIndex].label;
                            
                            // Map dataset label back to DB category
                            const statusMap = { 'In': 'IN', 'Event': 'OUT-EVENT', 'PP': 'OUT-PP', 'Trial': 'OUT-TRIAL' };
                            openDrilldown('trendline', label, '');
                        }
                    },
                    onHover: (e, el) => { e.native.target.style.cursor = el[0] ? 'pointer' : 'default'; },
                    interaction: commonInteraction,
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            ticks: { color: isDark ? '#94a3b8' : '#64748b', font: { size: 11 } },
                            grid: { 
                                display: true,
                                color: isDark ? 'rgba(71, 85, 105, 0.2)' : 'rgba(226, 232, 240, 0.6)',
                                drawBorder: false
                            }
                        },
                        y: {
                            stacked: false,
                            beginAtZero: true,
                            grace: '20%',
                            ticks: { 
                                color: isDark ? '#94a3b8' : '#64748b', 
                                font: { size: 11 },
                                precision: 0,
                                callback: (v) => Math.floor(v) === v ? v : ''
                            },
                            grid: { 
                                display: true,
                                color: isDark ? 'rgba(71, 85, 105, 0.2)' : 'rgba(226, 232, 240, 0.6)',
                                drawBorder: false
                            }
                        }
                    },
                    plugins: {
                        tooltip: commonTooltip,
                        datalabels: {
                            ...commonDataLabels,
                            anchor: 'end',
                            align: 'top',
                            offset: 2,
                            clip: false
                        },
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
                    onClick: (e, elements) => {
                        if (elements.length > 0) {
                            const i = elements[0].index;
                            const datasetIndex = elements[0].datasetIndex;
                            const label = makerChart.data.labels[i];
                            const labelStr = Array.isArray(label) ? label.join('|') : label;
                            const status = makerChart.data.datasets[datasetIndex].label;
                            openDrilldown('maker', label, '');
                        }
                    },
                    onHover: (e, el) => { e.native.target.style.cursor = el[0] ? 'pointer' : 'default'; },
                    interaction: commonInteraction,
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
                            grace: '20%',
                            ticks: { 
                                color: isDark ? '#94a3b8' : '#64748b', 
                                font: { size: 11 },
                                precision: 0,
                                callback: (v) => Math.floor(v) === v ? v : ''
                            }
                        }
                    },
                    plugins: {
                        tooltip: commonTooltip,
                        datalabels: commonDataLabels,
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

        // Initial Scroll Width Calculations
        if (typeof chartsData !== 'undefined') {
            if (stockStatusChart) {
                updateStockChartData({
                    charts: {
                        stock_grouped: @json($charts['stock_grouped'] ?? [])
                    }
                });
            }
            if (usageModelChart) updateChartData(usageModelChart, chartsData.usageModelLabels, chartsData.usageModelEvent, chartsData.usageModelPP, chartsData.usageModelTrial);
            if (makerChart) updateChartData(makerChart, chartsData.makerLabels, chartsData.makerOnBudget, chartsData.makerNearLoss, chartsData.makerLoss);
            
            // Set Maker as the default active tab on page load
            switchUsageChart('maker');
        }

        // --- DYNAMIC THEME OBSERVER ---
        function updateChartsTheme() {
            const dark = document.documentElement.classList.contains('dark');
            const textColor = dark ? '#94a3b8' : '#64748b';
            const gridColor = dark ? 'rgba(71, 85, 105, 0.25)' : 'rgba(226, 232, 240, 0.75)';
            const tooltipBg = dark ? '#1e293b' : '#ffffff';
            const tooltipTitle = dark ? '#f8fafc' : '#1e293b';
            const tooltipBody = dark ? '#94a3b8' : '#64748b';
            const tooltipBorder = dark ? '#334155' : '#e2e8f0';

            Chart.defaults.color = textColor;
            Chart.defaults.borderColor = dark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)';

            const chartsToUpdate = [stockStatusChart, usageModelChart, makerChart, trendlineChart];
            chartsToUpdate.forEach(chart => {
                if (chart && chart.options) {
                    // Update scales
                    if (chart.options.scales) {
                        if (chart.options.scales.x) {
                            if (chart.options.scales.x.ticks) chart.options.scales.x.ticks.color = textColor;
                            if (chart.options.scales.x.grid) chart.options.scales.x.grid.color = gridColor;
                        }
                        if (chart.options.scales.y) {
                            if (chart.options.scales.y.ticks) chart.options.scales.y.ticks.color = textColor;
                            if (chart.options.scales.y.grid) chart.options.scales.y.grid.color = gridColor;
                        }
                    }
                    // Update legend
                    if (chart.options.plugins && chart.options.plugins.legend && chart.options.plugins.legend.labels) {
                        chart.options.plugins.legend.labels.color = textColor;
                    }
                    // Update tooltip
                    if (chart.options.plugins && chart.options.plugins.tooltip) {
                        chart.options.plugins.tooltip.backgroundColor = tooltipBg;
                        chart.options.plugins.tooltip.titleColor = tooltipTitle;
                        chart.options.plugins.tooltip.bodyColor = tooltipBody;
                        chart.options.plugins.tooltip.borderColor = tooltipBorder;
                    }
                    chart.update();
                }
            });
        }

        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === "class") {
                    updateChartsTheme();
                }
            });
        });
        observer.observe(document.documentElement, { attributes: true });
        
    });
</script>

<script>
    // ── DRILLDOWN MODAL ──────────────────────────────────────────────────────
    const drilldownUrl = '{{ route("api.dashboard.drilldown") }}';
    let currentMonthYear = '{{ $filters["month_year"] ?? date("Y-m") }}';

    const DRILLDOWN_COLS = {
        stock: [
            { key: 'part_no',   label: 'Part No',      cls: 'text-left py-2 px-3' },
            { key: 'stock',     label: 'Stock',         cls: 'text-right py-2 px-2' },
            { key: 'min_stock', label: 'Min',           cls: 'text-right py-2 px-2' },
            { key: 'unit',      label: 'Unit',          cls: 'text-center py-2 px-2' },
            { key: 'status',    label: 'Status',        cls: 'text-center py-2 px-3' },
            { key: 'action_status', label: 'Action',    cls: 'text-center py-2 px-3' },
            { key: 'action_remark', label: 'Note',      cls: 'text-left py-2 px-3 max-w-[200px]' },
        ],
        usage_model: [
            { key: 'part_no',   label: 'Part No',       cls: 'text-left py-2 px-3' },
            { key: 'category',  label: 'Category',      cls: 'text-center py-2 px-2' },
            { key: 'quantity',  label: 'Quantity',      cls: 'text-right py-2 px-3 font-mono whitespace-nowrap' },
            { key: 'date',      label: 'Date',          cls: 'text-right py-2 px-3' }
        ],
        maker: [
            { key: 'part_no',   label: 'Part No',       cls: 'text-left py-2 px-3' },
            { key: 'model',     label: 'Model/Cust',    cls: 'text-left py-2 px-2 text-[10px]' },
            { key: 'quantity',  label: 'Quantity',      cls: 'text-right py-2 px-3 font-mono whitespace-nowrap' },
            { key: 'gap',       label: 'Gap',           cls: 'text-right py-2 px-3 font-mono' },
            { key: 'status',    label: 'Status',        cls: 'py-2 px-3 text-right' }
        ],
        trendline: [
            { key: 'part_no',           label: 'Part Number',           cls: 'py-2 px-3' },
            { key: 'category',          label: 'Category',              cls: 'py-2 px-2 text-center' },
            { key: 'origin_destination', label: 'Origin / Destination', cls: 'py-2 px-3 text-left' },
            { key: 'quantity',          label: 'Quantity',              cls: 'py-2 px-3 text-right font-mono whitespace-nowrap' },
            { key: 'date',              label: 'Date',                  cls: 'py-2 px-3 text-right' }
        ]
    };

    const STATUS_BADGE = {
        'Critical':   'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',
        'Warning':    'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
        'Over':       'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
        'Safe':       'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
        'Loss':       'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',
        'Near Loss':  'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
        'On Budget':  'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
        'IN':         'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
        'OUT-EVENT':  'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
        'OUT-PP':     'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
        'OUT-TRIAL':  'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',
        'Oldstock OK': 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
        'Oldstock NG': 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',
        'Other':       'bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-300',
        'Unknown':     'bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-300',
    };

    let drilldownPage = 1;
    let drilldownCurrentType = '';
    let drilldownCurrentLabel = '';
    let drilldownCurrentStatus = '';
    let searchDebounceTimer;

    window.openDrilldown = function(chartType, label, status = null) {
        drilldownCurrentType = chartType;
        drilldownCurrentLabel = label;
        drilldownCurrentStatus = status || '';
        drilldownPage = 1;

        const modal  = document.getElementById('drilldownModal');
        const panel  = document.getElementById('drilldownPanel');
        const searchInput = document.getElementById('drilldownSearch');

        if(searchInput) searchInput.value = '';

        modal.classList.remove('hidden');
        requestAnimationFrame(() => panel.classList.remove('translate-x-full'));
        
        document.getElementById('drilldownTitle').textContent = 'Loading...';
        document.getElementById('drilldownCountBadge').textContent = '0';

        renderDrilldownLegend(chartType, drilldownCurrentStatus);
        fetchDrilldownData(true);
    };

    function fetchDrilldownData(isInitial = false) {
        const my = document.getElementById('month_picker')?.value || currentMonthYear;
        const loader = document.getElementById('drilldownLoader');
        const tableLoader = document.getElementById('drilldownTableLoader');
        const content = document.getElementById('drilldownContent');
        const search = document.getElementById('drilldownSearch').value;
        const pageSize = document.getElementById('drilldownPageSize').value;
        
        if (isInitial) {
            loader.classList.remove('hidden');
            content.classList.add('hidden');
            content.classList.remove('flex');
        } else {
            tableLoader.classList.remove('hidden');
        }

        $.get(drilldownUrl, { 
            chart: drilldownCurrentType, 
            label: drilldownCurrentLabel, 
            status: drilldownCurrentStatus, 
            month_year: my,
            accumulate: $('#filterAccumulate').val(),
            search: search,
            project_status: $('#filterProjectStatus').val(),
            page: drilldownPage,
            pageSize: pageSize,
            stock_mode: $('#stock_mode').val()
        })
        .done(function(res) {
            document.getElementById('drilldownTitle').textContent = res.title;
            const cols = DRILLDOWN_COLS[res.chart] || [];
            const tbody = document.getElementById('drilldownBody');
            
            // Header
            document.getElementById('drilldownHead').innerHTML = '<tr>' + cols.map(c =>
                `<th class="${c.cls} text-[9px] font-bold text-slate-500 uppercase tracking-widest">${c.label}</th>`
            ).join('') + '</tr>';

            // Body
            if (!res.data || res.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="${cols.length}" class="py-10 text-center text-slate-400 italic text-[11px]">No data found.</td></tr>`;
            } else {
                tbody.innerHTML = res.data.map(row => {
                    return '<tr class="hover:bg-slate-50 dark:hover:bg-gray-800/60 transition-colors border-b border-gray-50 dark:border-gray-800">' + cols.map(c => {
                        if (c.key === 'action_status') {
                            const isCritical = row.status === 'Critical' || row.status === 'Warning' || row.status === 'Over';
                            if (!isCritical) return `<td class="${c.cls}"><span class="text-slate-300 italic text-[9px]">N/A</span></td>`;

                            const current = row[c.key] || '';
                            const statusMap = {
                                '': { label: 'NEED ACTION', cls: 'bg-rose-50 text-rose-600 border-rose-100 dark:bg-rose-900/20 dark:text-rose-400' },
                                'Process': { label: 'IN PROCESS', cls: 'bg-amber-50 text-amber-600 border-amber-100 dark:bg-amber-900/20 dark:text-amber-400' },
                                'Ordered': { label: 'ORDERED', cls: 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400' }
                            };
                            const st = statusMap[current] || statusMap[''];

                            let actionIcon = '';
                            const isOldMode = $('#stock_mode').val() === 'old';
                            if (!isOldMode && (row.status === 'Critical' || row.status === 'Warning')) {
                                if (row.action_status === 'Process') {
                                    actionIcon = '<i class="fa-solid fa-clock text-amber-500 ml-1.5" title="In Process"></i>';
                                } else if (row.action_status === 'Ordered') {
                                    actionIcon = '<i class="fa-solid fa-circle-check text-emerald-500 ml-1.5" title="Ordered"></i>';
                                } else {
                                    actionIcon = '<i class="fa-solid fa-circle-exclamation text-rose-500 ml-1.5" title="Need Action"></i>';
                                }
                            }
                            
                            return `<td class="${c.cls}">
                                <div class="relative inline-block text-left dropdown-action-container">
                                    <div class="flex items-center">
                                        <button onclick="toggleStatusDropdown(event, '${row.id}')" 
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[8px] font-bold border transition-all hover:bg-slate-50 dark:hover:bg-gray-700 ${st.cls}">
                                            <span class="w-1 h-1 rounded-full bg-current mr-1"></span>
                                            ${st.label}
                                            <i class="fa-solid fa-chevron-down ml-1 opacity-50 text-[6px]"></i>
                                        </button>
                                        ${actionIcon}
                                    </div>
                                    <div id="dropdown-${row.id}" class="hidden absolute right-0 mt-1 w-32 bg-white dark:bg-gray-800 rounded-xs shadow-xl border border-slate-200 dark:border-gray-700 z-[100] overflow-hidden">
                                        <div class="py-1">
                                            <button onclick="updateActionStatus('${row.id}', '')" class="w-full text-left px-3 py-1.5 text-[8px] font-bold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 flex items-center">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-2"></span> NEED ACTION
                                            </button>
                                            <button onclick="updateActionStatus('${row.id}', 'Process')" class="w-full text-left px-3 py-1.5 text-[8px] font-bold text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 flex items-center">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-2"></span> IN PROCESS
                                            </button>
                                            <button onclick="updateActionStatus('${row.id}', 'Ordered')" class="w-full text-left px-3 py-1.5 text-[8px] font-bold text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 flex items-center">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span> ORDERED
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>`;
                        }

                        const val = row[c.key] ?? '';
                        const badgeCls = (c.key === 'status' || c.key === 'category') ? STATUS_BADGE[val] : null;

                        let displayVal = val;
                        if (c.key === 'unit') {
                            const u = (val || '').toUpperCase();
                            displayVal = (u === 'COIL') ? 'KG' : (u === 'TRAPEZOID' ? 'SHEET' : val);
                        }
                        if (c.key === 'quantity') {
                            const unit = (row.unit || '').toUpperCase();
                            const unitLabel = (unit === 'COIL') ? 'KG' : (unit === 'TRAPEZOID' ? 'SHEET' : unit);
                            
                            // Format unit quantity: SHEET is integer, others have 2 decimals
                            let unitQtyFormatted;
                            const rawQty = parseFloat(row.qty) || 0;
                            if (unitLabel === 'SHEET') {
                                unitQtyFormatted = Math.round(rawQty).toLocaleString();
                            } else {
                                unitQtyFormatted = rawQty.toLocaleString(undefined, { 
                                    minimumFractionDigits: 2, 
                                    maximumFractionDigits: 2 
                                });
                            }
                            
                            const pcsQty = Math.round(parseFloat(row.qty_pcs) || 0).toLocaleString();
                            displayVal = `<span class="text-slate-800 dark:text-white">${unitQtyFormatted}</span> <span class="text-[8px] opacity-60">${unitLabel}</span> <span class="mx-1 text-slate-300">/</span> <span class="text-slate-600 dark:text-slate-400">${pcsQty}</span> <span class="text-[8px] opacity-60">PCS</span>`;
                        }

                        if (c.key === 'gap') {
                            displayVal = Math.round(parseFloat(val) || 0).toLocaleString() + ' PCS';
                        }

                        if (c.key === 'action_remark') {
                            const isCritical = row.status === 'Critical' || row.status === 'Warning' || row.status === 'Over';
                            if (!isCritical) return `<td class="${c.cls}"><span class="text-slate-300 italic text-[9px]">N/A</span></td>`;
                            
                            const displayNote = val || 'Add note...';
                            const noteCls = val ? 'text-slate-600 dark:text-slate-300' : 'text-slate-300 italic';

                            return `<td class="${c.cls}">
                                <div class="relative w-full dropdown-note-container">
                                    <button onclick="toggleNoteDropdown(event, '${row.id}')" 
                                        class="group flex items-start gap-1.5 w-full p-1.5 rounded-xs hover:bg-slate-50 dark:hover:bg-gray-800 transition-all border border-transparent hover:border-gray-100 dark:hover:border-gray-700 text-left">
                                        <i class="fa-solid fa-pen-to-square text-[9px] mt-0.5 opacity-30 group-hover:opacity-100 transition-opacity flex-shrink-0"></i>
                                        <span class="text-[10px] ${noteCls} break-words line-clamp-2 leading-snug">${displayNote}</span>
                                    </button>
                                    
                                    <div id="note-dropdown-${row.id}" class="hidden absolute right-0 mt-2 w-72 bg-white dark:bg-gray-800 rounded-xs shadow-2xl border border-slate-200 dark:border-gray-700 z-[110] p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Action Remark</p>
                                            <i class="fa-solid fa-message text-primary-500/50 text-[10px]"></i>
                                        </div>
                                        <textarea id="note-input-${row.id}" 
                                            class="w-full h-24 p-2.5 bg-slate-50 dark:bg-gray-900/50 border border-slate-100 dark:border-gray-700 rounded-xs text-[11px] focus:outline-none focus:ring-1 focus:ring-primary-500 placeholder:text-slate-300 mb-3"
                                            placeholder="Type follow-up note here...">${val}</textarea>
                                        <div class="flex justify-end gap-2">
                                            <button onclick="closeAllNoteDropdowns()" class="px-3 py-1.5 text-[10px] font-medium text-slate-400 hover:text-slate-600 transition-colors">Cancel</button>
                                            <button onclick="saveActionNote('${row.id}')" class="px-4 py-1.5 bg-primary-500 hover:bg-primary-600 text-white text-[10px] font-bold rounded-xs shadow-sm transition-all active:scale-95">Save Changes</button>
                                        </div>
                                    </div>
                                </div>
                            </td>`;
                        }

                        const cell = badgeCls
                            ? `<div class="flex items-center justify-center">
                                <span class="inline-block px-1.5 py-0.5 rounded-xs text-[9px] font-bold ${badgeCls}">${val}</span>
                               </div>`
                            : `<span class="${c.key === 'part_no' ? 'font-medium text-slate-700 dark:text-gray-200' : 'text-slate-500 dark:text-slate-400'}">${displayVal}</span>`;
                        return `<td class="${c.cls}">${cell}</td>`;
                    }).join('') + '</tr>';
                }).join('');
            }

            // Pagination Stats
            const total = res.total;
            const start = (drilldownPage - 1) * pageSize + 1;
            const end = Math.min(drilldownPage * pageSize, total);
            
            document.getElementById('drilldownCountBadge').textContent = total;
            document.getElementById('ddTotal').textContent = total;
            document.getElementById('ddPageStart').textContent = total === 0 ? 0 : start;
            document.getElementById('ddPageEnd').textContent = end;
            document.getElementById('ddCurrentPage').textContent = drilldownPage;
            
            document.getElementById('ddPrev').disabled = drilldownPage <= 1;
            document.getElementById('ddNext').disabled = end >= total;

            if (isInitial) {
                loader.classList.add('hidden');
                content.classList.remove('hidden');
                content.classList.add('flex');
                content.style.flexDirection = 'column';
            } else {
                tableLoader.classList.add('hidden');
            }
        });
    }

    window.resetDrilldownAndFetch = function() {
        drilldownPage = 1;
        fetchDrilldownData();
    };

    window.changeDrilldownPage = function(dir) {
        drilldownPage += dir;
        fetchDrilldownData();
        document.querySelector('#drilldownContent .overflow-y-auto').scrollTop = 0;
    };

    function renderDrilldownLegend(type, activeStatus) {
        const container = document.getElementById('drilldownLegendButtons');
        container.innerHTML = '';
        
        const isOld = $('#stock_mode').val() === 'old';
        const legends = {
            'stock': isOld ? ['Oldstock OK', 'Oldstock NG', 'Other'] : ['Critical', 'Warning', 'Over', 'Safe'],
            'usage_model': ['OUT-EVENT', 'OUT-PP', 'OUT-TRIAL'],
            'maker': ['On Budget', 'Near Loss', 'Loss'],
            'trendline': ['IN', 'OUT-EVENT', 'OUT-PP', 'OUT-TRIAL']
        };

        const currentLegends = legends[type] || [];
        
        const allBtn = createLegendBtn('All', activeStatus === '');
        allBtn.onclick = () => { drilldownCurrentStatus = ''; drilldownPage = 1; updateLegendActive(allBtn); fetchDrilldownData(); };
        container.appendChild(allBtn);

        currentLegends.forEach(leg => {
            const isActive = leg === activeStatus;
            const btn = createLegendBtn(leg.replace('OUT-', ''), isActive);
            btn.onclick = () => { drilldownCurrentStatus = leg; drilldownPage = 1; updateLegendActive(btn); fetchDrilldownData(); };
            container.appendChild(btn);
        });
    }

    function createLegendBtn(label, isActive) {
        const btn = document.createElement('button');
        btn.className = `legend-btn px-4 py-1.5 rounded-md text-[10px] font-bold uppercase transition-all duration-200 ${
            isActive 
            ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-sm' 
            : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'
        }`;
        btn.textContent = label;
        return btn;
    }

    function updateLegendActive(activeBtn) {
        $(activeBtn).siblings().removeClass('bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-sm')
            .addClass('text-slate-500 dark:text-slate-400');
        $(activeBtn).removeClass('text-slate-500 dark:text-slate-400')
            .addClass('bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-sm');
    }

    $('#drilldownSearch').on('input', function() {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            resetDrilldownAndFetch();
        }, 400);
    });

    window.closeDrilldownModal = function() {
        const panel = document.getElementById('drilldownPanel');
        panel.classList.add('translate-x-full');
        setTimeout(() => document.getElementById('drilldownModal').classList.add('hidden'), 300);
    };

    window.toggleStatusDropdown = function(event, id) {
        event.stopPropagation();
        const dropdown = document.getElementById(`dropdown-${id}`);
        const isHidden = dropdown.classList.contains('hidden');
        
        // Close all other dropdowns
        document.querySelectorAll('[id^="dropdown-"]').forEach(d => d.classList.add('hidden'));
        
        if (isHidden) {
            dropdown.classList.remove('hidden');
        }
    };

    // Global click listener to close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.dropdown-action-container')) {
            document.querySelectorAll('[id^="dropdown-"]').forEach(d => d.classList.add('hidden'));
        }
    });

    window.updateActionStatus = function(id, status) {
        // Close dropdown immediately
        const dropdown = document.getElementById(`dropdown-${id}`);
        if (dropdown) dropdown.classList.add('hidden');

        const url = '{{ route("inventory.master.product.updateActionStatus", ["id" => ":id"]) }}'.replace(':id', id);
        
        $.post(url, {
            _token: '{{ csrf_token() }}',
            action_status: status
        })
        .done(function(res) {
            if (res.success) {
                // Refresh drilldown data to show new status badge
                fetchDrilldownData();
            }
        })
        .fail(function() {
            Swal.fire({
                icon: 'error',
                title: 'Failed',
                text: 'Could not update status. Please try again.',
                customClass: { popup: 'rounded-xs' }
            });
        });
    };

    window.updateActionRemark = function(id, remark) {
        const url = '{{ route("inventory.master.product.updateActionStatus", ["id" => ":id"]) }}'.replace(':id', id);
        
        $.post(url, {
            _token: '{{ csrf_token() }}',
            action_remark: remark
        })
        .done(function(res) {
            if (res.success) {
                // No need to refresh everything for just a remark update
                // Unless we want to show a toast
                console.log('Remark updated');
            }
        });
    };

    window.toggleNoteDropdown = function(e, id) {
        e.stopPropagation();
        const dropdown = document.getElementById(`note-dropdown-${id}`);
        const isHidden = dropdown.classList.contains('hidden');
        
        closeAllNoteDropdowns();
        
        if (isHidden) {
            dropdown.classList.remove('hidden');
            const input = document.getElementById(`note-input-${id}`);
            input.focus();
            input.setSelectionRange(input.value.length, input.value.length);
        }
    };

    window.closeAllNoteDropdowns = function() {
        document.querySelectorAll('[id^="note-dropdown-"]').forEach(el => el.classList.add('hidden'));
    };

    window.saveActionNote = function(id) {
        const val = document.getElementById(`note-input-${id}`).value;
        const url = '{{ route("inventory.master.product.updateActionStatus", ["id" => ":id"]) }}'.replace(':id', id);
        
        $.post(url, {
            _token: '{{ csrf_token() }}',
            action_remark: val
        })
        .done(function(res) {
            if (res.success) {
                closeAllNoteDropdowns();
                fetchDrilldownData(); // Refresh to show new text in column
            }
        });
    };

    // Close on click outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown-note-container')) {
            closeAllNoteDropdowns();
        }
        if (!e.target.closest('.dropdown-action-container')) {
            document.querySelectorAll('[id^="dropdown-"]').forEach(d => d.classList.add('hidden'));
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDrilldownModal(); });
</script>
<style>
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Drilldown Modal Overrides (Anti-Pulling from app.css) */
    #drilldownModal select {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        padding-right: 1.75rem !important;
        background-position: right 0.4rem center !important;
        background-size: 1rem 1rem !important;
        height: 32px !important;
        line-height: 32px !important;
    }
    #drilldownModal input#drilldownSearch {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        height: 32px !important;
        line-height: 32px !important;
        padding-left: 2.25rem !important;
    }
    #drilldownModal .legend-btn {
        padding: 0.4rem 1rem !important;
        border: none !important;
        height: auto !important;
        line-height: 1 !important;
        width: auto !important;
        box-shadow: none !important;
    }
    #drilldownModal .legend-btn.bg-white {
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06) !important;
    }
</style>
@endpush