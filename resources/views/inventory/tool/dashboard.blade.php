@extends('layouts.app')

@section('title', 'Tool Inventory Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="dashboard-container w-full h-auto overflow-y-auto lg:h-[calc(100vh-85px)] lg:overflow-hidden flex flex-col gap-2 pb-0 custom-scrollbar lg:pb-0">
    
    {{-- Header, KPIs & Filters --}}
    <div class="flex flex-wrap items-center justify-between gap-y-2 gap-x-4">
        <!-- Section 1: Title Section -->
        <div class="flex-none">
            <h2 class="text-xl xl:text-2xl font-bold text-gray-800 dark:text-white leading-tight mb-0.5">Tool Monitoring Stock</h2>
            <p class="text-[11px] text-slate-500 dark:text-gray-400 leading-tight">Stock & consumption analytics</p>
        </div>

        <!-- Section 2: KPI Cards & Filter Toggle -->
        <div class="flex-1 flex flex-col md:flex-row gap-2 items-stretch lg:justify-end min-w-[100%] xl:min-w-[750px]">
            <!-- KPI Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-2 flex-1">
                @foreach([
                    ['val' => 'IDR ' . number_format($totalValue, 0, ',', '.'), 'label' => 'Total Value', 'unit' => '', 'icon' => 'fa-coins', 'color' => 'emerald', 'id' => 'stat_total_value'],
                    ['val' => number_format($totalStock), 'label' => 'Total Stock', 'unit' => 'PCS', 'icon' => 'fa-cubes', 'color' => 'slate', 'id' => 'stat_total_stock'],
                    ['val' => number_format($totalIn), 'label' => 'Total In', 'unit' => 'PCS', 'icon' => 'fa-arrow-right-to-bracket', 'color' => 'emerald', 'id' => 'stat_total_in'],
                    ['val' => number_format($totalOut), 'label' => 'Total Out', 'unit' => 'PCS', 'icon' => 'fa-arrow-right-from-bracket', 'color' => 'rose', 'id' => 'stat_total_out'],
                    ['val' => number_format($totalFastMoving), 'label' => 'Fast Moving', 'unit' => 'PCS', 'icon' => 'fa-bolt-lightning', 'color' => 'amber', 'id' => 'stat_total_fast_moving'],
                    ['val' => number_format($totalSlowMoving), 'label' => 'Slow Moving', 'unit' => 'PCS', 'icon' => 'fa-box-archive', 'color' => 'indigo', 'id' => 'stat_total_slow_moving'],
                ] as $stat)
                <div class="bg-white dark:bg-gray-800 px-2.5 py-2 rounded-xs border border-gray-200 dark:border-gray-700 flex items-center gap-2.5 min-h-[52px] h-auto">
                    <div class="w-9 h-9 rounded-xs bg-{{ $stat['color'] }}-50 dark:bg-{{ $stat['color'] }}-900/20 flex items-center justify-center text-{{ $stat['color'] }}-600 dark:text-{{ $stat['color'] }}-400 text-base shrink-0">
                        <i class="fa-solid {{ $stat['icon'] }}"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-bold text-slate-600 dark:text-slate-400 tracking-tight leading-none mb-1 whitespace-nowrap">{{ $stat['label'] }}</p>
                        <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 leading-none tracking-tight whitespace-nowrap" id="{{ $stat['id'] }}">
                            {{ $stat['val'] }} <span class="text-[9px] text-slate-400 font-normal ml-0.5">{{ $stat['unit'] }}</span>
                        </h3>
                        @if($stat['label'] === 'Total Value')
                            <p class="text-[9px] text-slate-400 dark:text-slate-500 font-medium leading-none mt-1 whitespace-nowrap">
                                Fast: {{ $fastValFormatted }} | Slow: {{ $slowValFormatted }}
                            </p>
                        @endif
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
    <div id="dashboardFilterCard" class="hidden bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-600 p-4 mb-2">
        <form id="filterForm" method="GET" action="{{ route('inventory.tool.dashboard') }}">
            <div class="flex flex-col lg:flex-row gap-4 lg:items-end">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 flex-1">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">Period</label>
                        <select name="period" id="periodSelect" onchange="handlePeriodChange(this.value)" class="w-full text-xs font-medium border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-900 dark:text-white rounded-xs h-[40px] px-3 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all focus:border-primary-500">
                            <option value="7d" {{ $period === '7d' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="30d" {{ $period === '30d' ? 'selected' : '' }}>Last 30 Days</option>
                            <option value="90d" {{ $period === '90d' ? 'selected' : '' }}>Last 90 Days</option>
                            <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="this_year" {{ $period === 'this_year' ? 'selected' : '' }}>This Year</option>
                            <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Range...</option>
                        </select>
                    </div>
                    
                    <div id="customDateRangeGroup" class="{{ $period === 'custom' ? 'block' : 'hidden' }} space-y-1.5 col-span-2">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">Date Range</label>
                        <div class="relative">
                            <input type="text" id="filter_date_range" class="w-full text-xs font-medium border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-900 dark:text-white rounded-xs h-[40px] px-3 pl-9 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all focus:border-primary-500" placeholder="Select date range..." readonly>
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-calendar-days text-[11px]"></i>
                            </div>
                            <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
                            <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
                        </div>
                    </div>
                </div>

                <div class="flex gap-2 pt-2 xl:pt-0">
                    <a href="{{ route('inventory.tool.dashboard') }}" class="h-[40px] px-6 bg-slate-100 hover:bg-slate-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-xs text-xs font-medium text-slate-600 dark:text-gray-300 transition-all border border-slate-200 dark:border-gray-600 flex items-center justify-center">
                        Reset Filters
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Main Visual Layout Row --}}
    <div class="flex flex-col lg:flex-row gap-2 flex-1 min-h-0">
        
        {{-- Column 1: Stock Status & Warnings --}}
        <div class="w-full lg:w-1/3 flex flex-col gap-2 h-full min-h-0">
            {{-- Chart Card 1: Stock status bar chart --}}
            <div class="chart-card bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative h-[250px] lg:h-auto lg:flex-[55] min-h-0">
                <div class="flex-none flex justify-between items-center mb-1">
                    <h3 class="text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center min-w-0 pr-2">
                        <i class="fa-solid fa-chart-column mr-2 text-primary-500 flex-shrink-0"></i> 
                        <span class="truncate">Stock Status</span> 
                        <span class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-slate-700 text-[8px] font-medium text-slate-500 dark:text-slate-400 border border-slate-200/50 dark:border-slate-600/50 flex-shrink-0 whitespace-nowrap">Fast Moving Category</span>
                    </h3>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button id="btnStockPrev" onclick="changeStockPage(-1)" class="w-6 h-6 flex items-center justify-center rounded-xs bg-slate-50 hover:bg-slate-100 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-500 dark:text-slate-300 transition-colors disabled:opacity-30 disabled:cursor-not-allowed border border-slate-200 dark:border-gray-600" title="Previous page">
                            <i class="fa-solid fa-chevron-left text-[9px]"></i>
                        </button>
                        <span id="stockPageIndicator" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 min-w-[24px] text-center">1/1</span>
                        <button id="btnStockNext" onclick="changeStockPage(1)" class="w-6 h-6 flex items-center justify-center rounded-xs bg-slate-50 hover:bg-slate-100 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-500 dark:text-slate-300 transition-colors disabled:opacity-30 disabled:cursor-not-allowed border border-slate-200 dark:border-gray-600" title="Next page">
                            <i class="fa-solid fa-chevron-right text-[9px]"></i>
                        </button>
                    </div>
                </div>
                <div class="relative w-full flex-1 min-h-0">
                    <canvas id="stockStatusChart"></canvas>
                </div>
            </div>

            {{-- Balance Warnings Table --}}
            <div class="table-container bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative h-[320px] lg:h-auto lg:flex-[45] min-h-0">
                <div class="flex-none flex justify-between items-center mb-1">
                    <h3 class="text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center">
                        <i class="fa-solid fa-triangle-exclamation mr-2 text-rose-500"></i> Balance Warnings
                        <span class="ml-2 px-1.5 py-0.5 rounded-xs bg-rose-50 dark:bg-rose-950/20 text-[9px] font-bold text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/30 flex-shrink-0 whitespace-nowrap">{{ count($balanceWarnings) }} Alerts</span>
                    </h3>
                </div>
                <div class="overflow-y-auto flex-1 custom-scrollbar border border-gray-100 dark:border-gray-700/50 rounded-xs">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/80 dark:bg-gray-700/50 sticky top-0 z-10 backdrop-blur-md">
                            <tr>
                                <th class="py-2 px-3 text-[10px] font-medium text-slate-500 dark:text-slate-400 tracking-wider">Tool / Spec</th>
                                <th class="py-2 px-2 text-[10px] font-medium text-slate-500 dark:text-slate-400 tracking-wider text-right">Min</th>
                                <th class="py-2 px-2 text-[10px] font-medium text-slate-500 dark:text-slate-400 tracking-wider text-right">Actual</th>
                                <th class="py-2 px-3 text-[10px] font-medium text-slate-500 dark:text-slate-400 tracking-wider text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-700 text-[11px] font-medium">
                            @forelse($balanceWarnings as $warning)
                                @php
                                    $badgeClass = $warning['status'] === 'Critical' 
                                        ? 'bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-900/40' 
                                        : 'bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 border-amber-100 dark:border-amber-900/40';
                                @endphp
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-800/30 transition-colors cursor-pointer" onclick="openDrilldown('stock', '{{ addslashes($warning['category']) }}', '{{ $warning['status'] }}', '{{ addslashes($warning['tool_name']) }}')">
                                    <td class="py-2 px-3 text-slate-800 dark:text-gray-200">
                                        <div class="flex items-center gap-1.5">
                                            <div class="font-medium truncate max-w-[130px]" title="{{ $warning['tool_name'] }}">{{ $warning['tool_name'] }}</div>
                                            @php
                                                $actionIcon = '';
                                                if ($warning['status'] === 'Critical' || $warning['status'] === 'Warning') {
                                                    if ($warning['action_status'] === 'Process') {
                                                        $actionIcon = '<i class="fa-solid fa-clock text-amber-500 text-[10px]" title="In Process"></i>';
                                                    } elseif ($warning['action_status'] === 'Ordered') {
                                                        $actionIcon = '<i class="fa-solid fa-circle-check text-emerald-500 text-[10px]" title="Ordered"></i>';
                                                    } else {
                                                        $actionIcon = '<i class="fa-solid fa-circle-exclamation text-rose-500 text-[10px]" title="Need Action"></i>';
                                                    }
                                                }
                                            @endphp
                                            {!! $actionIcon !!}
                                        </div>
                                        <div class="text-[9px] text-slate-400 font-medium truncate max-w-[130px]">{{ $warning['spec_code'] }} | {{ $warning['location'] }}</div>
                                        @if($warning['action_remark'])
                                            <p class="text-[8px] text-primary-500 italic mt-0.5 truncate max-w-[130px]"><i class="fa-solid fa-message mr-1 opacity-70"></i>{{ $warning['action_remark'] }}</p>
                                        @endif
                                    </td>
                                    <td class="py-2 px-2 text-right text-slate-500 dark:text-slate-400 font-medium">{{ $warning['qty_min'] }}</td>
                                    <td class="py-2 px-2 text-right font-bold text-rose-500 dark:text-rose-400">{{ $warning['current_qty'] }}</td>
                                    <td class="py-2 px-3 text-right">
                                        <span class="px-1.5 py-0.5 rounded-xs border text-[9px] font-black uppercase tracking-wider {{ $badgeClass }}">
                                            {{ $warning['status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-400">
                                        <i class="fa-solid fa-circle-check text-emerald-500 text-lg mb-1 mx-auto block"></i>
                                        <div class="text-xs font-medium">Stock nominal and safe!</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Column 2: Pareto Outflow & Slow Moving Batches Overview --}}
        <div class="w-full lg:w-1/3 flex flex-col gap-2 h-full min-h-0">
            {{-- Chart Card 2: Pareto Diagram --}}
            <div class="chart-card bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative h-[250px] lg:h-auto lg:flex-[55] min-h-0">
                <div class="flex-none flex justify-between items-center mb-1">
                    <h3 class="text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center min-w-0 pr-2">
                        <i class="fa-solid fa-magnifying-glass-chart mr-2 text-violet-500 flex-shrink-0"></i> 
                        <span class="truncate">Pareto Outflow (80/20)</span> 
                        <span class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-slate-700 text-[8px] font-medium text-slate-500 dark:text-slate-400 border border-slate-200/50 dark:border-slate-600/50 flex-shrink-0 whitespace-nowrap">Consumption</span>
                    </h3>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button id="btnParetoPrev" onclick="changeParetoPage(-1)" class="w-6 h-6 flex items-center justify-center rounded-xs bg-slate-50 hover:bg-slate-100 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-500 dark:text-slate-300 transition-colors disabled:opacity-30 disabled:cursor-not-allowed border border-slate-200 dark:border-gray-600" title="Previous page">
                            <i class="fa-solid fa-chevron-left text-[9px]"></i>
                        </button>
                        <span id="paretoPageIndicator" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 min-w-[24px] text-center">1/1</span>
                        <button id="btnParetoNext" onclick="changeParetoPage(1)" class="w-6 h-6 flex items-center justify-center rounded-xs bg-slate-50 hover:bg-slate-100 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-500 dark:text-slate-300 transition-colors disabled:opacity-30 disabled:cursor-not-allowed border border-slate-200 dark:border-gray-600" title="Next page">
                            <i class="fa-solid fa-chevron-right text-[9px]"></i>
                        </button>
                    </div>
                </div>
                <div class="relative w-full flex-1 min-h-0"><canvas id="paretoChart"></canvas></div>
            </div>

            {{-- Slow Moving Batches Table --}}
            <div class="table-container bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative h-[320px] lg:h-auto lg:flex-[45] min-h-0">
                <div class="flex-none flex justify-between items-center mb-1">
                    <h3 class="text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center">
                        <i class="fa-solid fa-box-archive mr-2 text-indigo-500"></i> Slow Moving Active Batches
                    </h3>
                </div>
                <div class="overflow-y-auto flex-1 custom-scrollbar border border-gray-100 dark:border-gray-700/50 rounded-xs">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/80 dark:bg-gray-700/50 sticky top-0 z-10 backdrop-blur-md">
                            <tr>
                                <th class="py-2 px-3 text-[10px] font-medium text-slate-500 dark:text-slate-400 tracking-wider">Batch ID</th>
                                <th class="py-2 px-2 text-[10px] font-medium text-slate-500 dark:text-slate-400 tracking-wider">Tool / Spec</th>
                                <th class="py-2 px-2 text-[10px] font-medium text-slate-500 dark:text-slate-400 tracking-wider text-center">Lifetime</th>
                                <th class="py-2 px-3 text-[10px] font-medium text-slate-500 dark:text-slate-400 tracking-wider text-right">Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-700 text-[11px] font-medium">
                            @forelse($latestSlowBatches as $batch)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                    <td class="py-2 px-3 text-slate-800 dark:text-gray-200">
                                        <div class="font-medium">{{ $batch->id_number }}</div>
                                        <div class="text-[9px] text-slate-400 font-medium truncate max-w-[80px]" title="{{ $batch->location?->name }}">{{ $batch->location?->name ?? '-' }}</div>
                                    </td>
                                    <td class="py-2 px-2 text-slate-500 dark:text-slate-400">
                                        <div class="font-medium text-slate-700 dark:text-slate-300 truncate max-w-[100px]" title="{{ $batch->tool?->name }}">{{ $batch->tool?->name }}</div>
                                        <div class="text-[9px] font-medium truncate max-w-[100px]" title="{{ $batch->tool?->spec_code }}">{{ $batch->tool?->spec_code }}</div>
                                    </td>
                                    <td class="py-2 px-2 text-center whitespace-nowrap">
                                        <span class="font-medium text-[10px] px-1.5 py-0.5 rounded-xs {{ $batch->age_years >= $batch->std_lifetime_yrs ? 'text-rose-600 bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/30' : 'text-slate-600 bg-slate-100 dark:bg-gray-800' }}">
                                            {{ number_format($batch->age_years, 1) }} / {{ $batch->std_lifetime_yrs }} Yr
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 text-right font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">IDR {{ number_format($batch->current_value, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-400">
                                        <p class="text-xs font-medium">No active slow batches.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Column 3: Transaction Trend & Recent Operational Activity --}}
        <div class="w-full lg:w-1/3 flex flex-col gap-2 h-full min-h-0">
            {{-- Chart Card 3: Transaction Trend --}}
            <div class="chart-card bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative h-[250px] lg:h-auto lg:flex-[55] min-h-0">
                <div class="flex-none flex justify-between items-center mb-1">
                    <h3 class="text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center min-w-0 pr-2">
                        <i class="fa-solid fa-arrow-trend-up mr-2 text-emerald-500 flex-shrink-0"></i> 
                        <span class="truncate">Transaction Trend</span> 
                        <span class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-slate-700 text-[8px] font-medium text-slate-500 dark:text-slate-400 border border-slate-200/50 dark:border-slate-600/50 flex-shrink-0 whitespace-nowrap">In vs Out</span>
                    </h3>
                </div>
                <div class="relative w-full flex-1 min-h-0"><canvas id="trendChart"></canvas></div>
            </div>

            {{-- Recent Operational Activity Table --}}
            <div class="table-container bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative h-[320px] lg:h-auto lg:flex-[45] min-h-0">
                <div class="flex-none flex justify-between items-center mb-1">
                    <h3 class="text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center min-w-0 pr-2">
                        <i class="fa-solid fa-clock-rotate-left mr-2 text-primary-500 flex-shrink-0"></i> 
                        <span class="truncate">Recent Activity</span>
                        <span class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-slate-700 text-[8px] font-medium text-slate-500 dark:text-slate-400 border border-slate-200/50 dark:border-slate-600/50 flex-shrink-0 whitespace-nowrap">Fast Moving</span>
                    </h3>
                </div>
                <div class="overflow-y-auto flex-1 custom-scrollbar border border-gray-100 dark:border-gray-700/50 rounded-xs">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/80 dark:bg-gray-700/50 sticky top-0 z-10 backdrop-blur-md">
                            <tr>
                                <th class="py-2.5 px-3 text-[11px] font-medium text-slate-500 dark:text-slate-400 tracking-wider">Tool / Spec</th>
                                <th class="py-2.5 px-2 text-[11px] font-medium text-slate-500 dark:text-slate-400 tracking-wider text-center">Type</th>
                                <th class="py-2.5 px-2 text-[11px] font-medium text-slate-500 dark:text-slate-400 tracking-wider text-right">Qty</th>
                                <th class="py-2.5 px-3 text-[11px] font-medium text-slate-500 dark:text-slate-400 tracking-wider text-center">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-700 text-xs font-medium">
                            @forelse($activities as $act)
                                @php
                                    $badgeColor = $act['type'] === 'IN' 
                                        ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/30'
                                        : 'bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-900/30';
                                @endphp
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                    <td class="py-2.5 px-3 text-slate-800 dark:text-gray-200">
                                        <div class="font-medium truncate max-w-[130px]" title="{{ $act['tool_name'] }}">{{ $act['tool_name'] }}</div>
                                        <div class="text-[10px] text-slate-400 font-medium truncate max-w-[130px]" title="{{ $act['spec_code'] }}">{{ $act['spec_code'] }}</div>
                                    </td>
                                    <td class="py-2.5 px-2 text-center whitespace-nowrap">
                                        <span class="px-1.5 py-0.5 rounded-xs border text-[10px] font-black uppercase tracking-wider {{ $badgeColor }}">
                                            {{ $act['type'] }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-2 text-right font-bold font-mono text-slate-700 dark:text-slate-300">
                                        {{ number_format(abs($act['qty'])) }} <span class="text-[10px] font-normal text-slate-400 uppercase ml-0.5">{{ $act['uom'] }}</span>
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-medium text-slate-400 text-[11px] whitespace-nowrap" title="{{ $act['timestamp'] }}">{{ $act['display_time'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-400">
                                        <p class="text-xs font-medium">No recent activity detected.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>


@endsection

@section('css')
<style>
    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
        height: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.3);
        border-radius: 9999px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(148, 163, 184, 0.5);
    }

    /* Force Spin Animation for Loaders */
    @keyframes spin-custom {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }
    .animate-spin, .fa-spin {
        animation: spin-custom 1s linear infinite !important;
        display: inline-block !important;
    }
</style>

{{-- Drilldown Modal HTML --}}
<div id="drilldownModal" class="fixed inset-0 z-[60] hidden" aria-modal="true">
    <div class="absolute inset-0 bg-slate-900/50" onclick="closeDrilldownModal()"></div>
    <div class="absolute right-0 top-0 bottom-0 w-full max-w-4xl bg-white dark:bg-gray-900 shadow-2xl flex flex-col transform transition-transform duration-300 translate-x-full" id="drilldownPanel">
        {{-- Header --}}
        <div class="flex-none flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <p class="text-xs font-bold text-primary-500 tracking-wider">Detail Explorer</p>
                    <span id="drilldownCountBadge" class="px-2 py-0.5 rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 text-[10px] font-bold">0</span>
                </div>
                <h2 id="drilldownTitle" class="text-sm font-extrabold text-gray-800 dark:text-gray-100 truncate max-w-[600px]">Loading...</h2>
            </div>
            <button onclick="closeDrilldownModal()" class="w-7 h-7 flex items-center justify-center rounded-xs bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 transition-colors">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>
        {{-- Loader --}}
        <div id="drilldownLoader" class="flex-1 flex items-center justify-center">
            <div class="text-center">
                <i class="fa-solid fa-spinner fa-spin animate-spin text-3xl text-primary-500 mb-3.5"></i>
                <p class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Fetching data...</p>
            </div>
        </div>
        {{-- Content --}}
        <div id="drilldownContent" class="flex-1 flex-col hidden min-h-0">
            {{-- Quick Filters --}}
            <div id="drilldownLegendContainer" class="px-5 py-3 border-b border-gray-100 dark:border-gray-800 bg-gray-50/30 dark:bg-gray-800/20">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Filter by status</p>
                </div>
                <div id="drilldownLegendButtons" class="inline-flex p-1 bg-gray-100 dark:bg-gray-800/80 rounded-lg gap-1">
                    {{-- Buttons injected by JS --}}
                </div>
            </div>

            <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 sticky top-0 z-20 flex flex-col md:flex-row gap-3 items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">Show</span>
                    <select id="drilldownPageSize" onchange="resetDrilldownAndFetch()" class="h-7 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-xs font-medium focus:ring-1 focus:ring-primary-500 outline-none cursor-pointer py-0" style="min-width: 60px; padding-top: 0px; padding-bottom: 0px; padding-left: 6px; padding-right: 16px; height: 28px; line-height: normal;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 whitespace-nowrap">entries</span>
                </div>
                <div class="relative w-full md:w-56">
                    <input type="text" id="drilldownSearch" placeholder="Search Name or Spec..." class="w-full h-7 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-xs font-medium focus:outline-none focus:ring-1 focus:ring-primary-500 transition-all placeholder:text-gray-400/80" style="padding-left: 26px; padding-right: 12px; height: 28px;">
                    <div class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </div>
                </div>
            </div>
            <div class="flex-1 relative min-h-0">
                <div id="drilldownTableLoader" class="hidden absolute inset-0 bg-white/60 dark:bg-gray-900/60 z-30 flex items-center justify-center backdrop-blur-[1px] transition-all">
                    <div class="flex flex-col items-center">
                        <i class="fa-solid fa-circle-notch fa-spin text-xl text-primary-500 mb-2"></i>
                        <span class="text-sm font-semibold text-slate-500 tracking-wider">Updating...</span>
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
            <div class="flex-none px-5 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/80 flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Showing <span id="ddPageStart" class="font-bold text-gray-800 dark:text-gray-100">0</span> to <span id="ddPageEnd" class="font-bold text-gray-800 dark:text-gray-100">0</span> of <span id="ddTotal" class="font-bold text-gray-800 dark:text-gray-100">0</span> entries</span>
                <div class="flex items-center gap-1">
                    <button id="ddPrev" onclick="changeDrilldownPage(-1)" disabled class="w-7 h-7 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xs text-xs font-bold text-slate-700 dark:text-slate-200 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-600 transition-all shadow-xs flex items-center justify-center flex-shrink-0" title="Previous Page">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    </button>
                    <span id="ddCurrentPage" class="w-7 h-7 bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-700/50 rounded-xs text-xs font-extrabold text-primary-600 dark:text-primary-400 flex items-center justify-center flex-shrink-0">1</span>
                    <button id="ddNext" onclick="changeDrilldownPage(1)" disabled class="w-7 h-7 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xs text-xs font-bold text-slate-700 dark:text-slate-200 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-600 transition-all shadow-xs flex items-center justify-center flex-shrink-0" title="Next Page">
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Location Tooltip Portal --}}
<div id="location-tooltip-portal" class="fixed z-[9999] bg-white dark:bg-gray-800 rounded-xs shadow-2xl border border-slate-200 dark:border-gray-700 p-3.5 w-60 text-left hidden font-sans scale-in"></div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
    Chart.register(ChartDataLabels);

    // Location Click Popover Listener
    $(document).on('click', '.location-click-trigger', function(e) {
        e.stopPropagation();
        e.preventDefault();
        
        const el = $(this);
        let details = el.attr('data-locations');
        if (!details) return;

        try {
            if (typeof details === 'string') {
                details = JSON.parse(details);
            }
        } catch (err) {
            console.error("Failed to parse locations data:", err);
            return;
        }

        if (!Array.isArray(details) || details.length === 0) return;

        const portal = $('#location-tooltip-portal');
        
        // If clicking the same button that is currently open, close it
        if (!portal.hasClass('hidden') && portal.data('trigger-el') === this) {
            portal.addClass('hidden').hide();
            return;
        }

        // Dynamic portal settings
        const title = el.attr('data-popup-title') || 'Location Details';
        const icon = el.attr('data-popup-icon') || 'fa-map-location-dot';

        let content = `
            <h4 class="font-bold text-slate-900 dark:text-white mb-3 border-b border-slate-100 dark:border-gray-700 pb-2 text-[10px] uppercase tracking-widest flex items-center gap-1.5">
                <i class="fa-solid ${icon} text-primary-500"></i> ${title}
            </h4>
            <div class="space-y-1 max-h-[250px] overflow-y-auto custom-scrollbar">`;

        details.forEach(item => {
            let badgeColor = 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800/30';
            if (item.category === 'machine') {
                badgeColor = 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-400 dark:border-purple-800/30';
            } else if (item.category === 'subcont') {
                badgeColor = 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-900/20 dark:text-orange-400 dark:border-orange-800/30';
            } else if (item.category === 'scrap' || item.category === 'lost') {
                badgeColor = 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800/30';
            }
            
            content += `
                <div class="flex items-center justify-between py-1 border-b border-slate-50 dark:border-gray-800/40 last:border-0 gap-4">
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-xs text-[9px] font-bold border ${badgeColor}" title="${item.category.toUpperCase()}">${item.code}</span>
                    <span class="text-slate-500 dark:text-slate-400 font-medium text-[11px] truncate max-w-[120px]" title="${item.name}">${item.name}</span>
                    <span class="font-mono font-bold text-slate-800 dark:text-white text-[11px]">${item.qty} PCS</span>
                </div>`;
        });

        content += `</div>`;

        portal.html(content).removeClass('hidden').data('trigger-el', this).show();
        
        const rect = this.getBoundingClientRect();
        const tipWidth = portal.outerWidth();
        const tipHeight = portal.outerHeight();
        
        let top = rect.bottom + 5;
        let left = rect.left;

        if (top + tipHeight > window.innerHeight) top = rect.top - tipHeight - 5;
        if (left + tipWidth > window.innerWidth) left = window.innerWidth - tipWidth - 10;
        if (left < 10) left = 10;

        portal.css({
            top: top + 'px',
            left: left + 'px',
            position: 'fixed'
        });
    });

    // Close when clicking anywhere outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.location-click-trigger, #location-tooltip-portal').length) {
            $('#location-tooltip-portal').addClass('hidden').hide();
        }
    });

    function handlePeriodChange(val) {
        const customGroup = document.getElementById('customDateRangeGroup');
        if (val === 'custom') {
            customGroup.classList.remove('hidden');
            if (window.filterDatePicker) {
                window.filterDatePicker.show();
            }
        } else {
            customGroup.classList.add('hidden');
            document.getElementById('start_date').value = '';
            document.getElementById('end_date').value = '';
            document.getElementById('filterForm').submit();
        }
    }

    $(document).ready(function() {
        // Init Litepicker for Custom Date Range
        let pickerStartDate = "{{ request('start_date') }}";
        let pickerEndDate = "{{ request('end_date') }}";

        window.filterDatePicker = new Litepicker({
            element: document.getElementById('filter_date_range'),
            singleMode: false,
            autoApply: true,
            format: 'DD-MM-YYYY',
            delimiter: ' - ',
            startDate: pickerStartDate ? new Date(pickerStartDate) : null,
            endDate: pickerEndDate ? new Date(pickerEndDate) : null,
            setup: (picker) => {
                picker.on('selected', (date1, date2) => {
                    document.getElementById('start_date').value = date1.format('YYYY-MM-DD');
                    document.getElementById('end_date').value = date2.format('YYYY-MM-DD');
                    // Automatically trigger form submit!
                    document.getElementById('filterForm').submit();
                });
            }
        });

        // Set the input field display text manually on load if values exist
        if (pickerStartDate && pickerEndDate) {
            const formatDisplay = (dStr) => {
                const parts = dStr.split('-');
                return `${parts[2]}-${parts[1]}-${parts[0]}`; // YYYY-MM-DD to DD-MM-YYYY
            };
            document.getElementById('filter_date_range').value = `${formatDisplay(pickerStartDate)} - ${formatDisplay(pickerEndDate)}`;
        }

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
        const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)';
        const textMuted = isDark ? '#94a3b8' : '#64748b';

        // Set Chart defaults to match Material Dashboard
        Chart.defaults.color = textMuted;
        Chart.defaults.borderColor = gridColor;
        Chart.defaults.font.family = "'Inter', sans-serif";

        const commonDataLabels = {
            backgroundColor: isDark ? 'rgba(30, 41, 59, 0.85)' : 'rgba(255, 255, 255, 0.85)',
            borderRadius: 1,
            color: isDark ? '#f8fafc' : '#1e293b',
            font: { weight: 'bold', size: 9 },
            formatter: (value) => value > 0 ? new Intl.NumberFormat().format(value) : '',
            padding: { top: 1, bottom: 0, left: 3, right: 3 },
            anchor: 'center',
            align: 'center',
            display: (context) => context.dataset.data[context.dataIndex] > 0 ? 'auto' : false,
            clip: false
        };

        // 1. Stock Status Stacked Bar Chart
        const fullStockLabels = @json(array_keys($groupedStockStatus));
        const fullStockData = @json(array_values($groupedStockStatus));
        let stockPage = 1;
        const stockPageSize = 6;

        const stockCtx = document.getElementById('stockStatusChart').getContext('2d');
        const stockStatusChart = new Chart(stockCtx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Critical',
                        data: [],
                        backgroundColor: '#ef4444',
                        borderRadius: 2
                    },
                    {
                        label: 'Warning',
                        data: [],
                        backgroundColor: '#f59e0b',
                        borderRadius: 2
                    },
                    {
                        label: 'Over',
                        data: [],
                        backgroundColor: '#3b82f6',
                        borderRadius: 2
                    },
                    {
                        label: 'Safe',
                        data: [],
                        backgroundColor: '#10b981',
                        borderRadius: 2
                    }
                ]
            },
            options: {
                onClick: (e, elements) => {
                    if (elements.length > 0) {
                        const i = elements[0].index;
                        const label = stockStatusChart.data.labels[i];
                        openDrilldown('stock', label, ''); // Show 'All' by default
                    }
                },
                onHover: (e, el) => { e.native.target.style.cursor = el[0] ? 'pointer' : 'default'; },
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textMuted,
                            boxWidth: 10,
                            font: { size: 10, weight: '600' },
                            usePointStyle: false,
                            padding: 15
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 },
                        padding: 12
                    },
                    datalabels: commonDataLabels
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: { display: false },
                        ticks: { color: textMuted, font: { size: 10 } }
                    },
                    y: {
                        stacked: true,
                        grid: { color: gridColor },
                        ticks: { color: textMuted, font: { size: 10 }, precision: 0 },
                        grace: '20%'
                    }
                }
            }
        });

        window.changeStockPage = function(dir) {
            const maxPage = Math.ceil(fullStockLabels.length / stockPageSize) || 1;
            stockPage += dir;
            if (stockPage < 1) stockPage = 1;
            if (stockPage > maxPage) stockPage = maxPage;
            updateStockChart();
        };

        function updateStockChart() {
            const start = (stockPage - 1) * stockPageSize;
            const end = start + stockPageSize;
            const slicedLabels = fullStockLabels.slice(start, end);
            const slicedData = fullStockData.slice(start, end);

            stockStatusChart.data.labels = slicedLabels;
            stockStatusChart.data.datasets[0].data = slicedData.map(d => d.critical);
            stockStatusChart.data.datasets[1].data = slicedData.map(d => d.warning);
            stockStatusChart.data.datasets[2].data = slicedData.map(d => d.over);
            stockStatusChart.data.datasets[3].data = slicedData.map(d => d.safe);
            stockStatusChart.update();

            const maxPage = Math.ceil(fullStockLabels.length / stockPageSize) || 1;
            document.getElementById('stockPageIndicator').textContent = `${stockPage}/${maxPage}`;
            document.getElementById('btnStockPrev').disabled = stockPage <= 1;
            document.getElementById('btnStockNext').disabled = stockPage >= maxPage;
        }

        // Initialize Stock Chart
        updateStockChart();

        // 2. Transaction Trend Chart (IN vs OUT)
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        const inGrad = trendCtx.createLinearGradient(0, 0, 0, 220);
        inGrad.addColorStop(0, 'rgba(16, 185, 129, 0.3)');
        inGrad.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

        const outGrad = trendCtx.createLinearGradient(0, 0, 0, 220);
        outGrad.addColorStop(0, 'rgba(244, 63, 94, 0.3)');
        outGrad.addColorStop(1, 'rgba(244, 63, 94, 0.0)');

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: @json($trendData['labels']),
                datasets: [
                    {
                        label: 'Inflow',
                        data: @json($trendData['ins']),
                        borderColor: '#10b981',
                        borderWidth: 1.5,
                        backgroundColor: inGrad,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 2,
                        pointHoverRadius: 4
                    },
                    {
                        label: 'Outflow',
                        data: @json($trendData['outs']),
                        borderColor: '#f43f5e',
                        borderWidth: 1.5,
                        backgroundColor: outGrad,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 2,
                        pointHoverRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textMuted,
                            boxWidth: 10,
                            font: { size: 10, weight: '600' },
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 },
                        padding: 12
                    },
                    datalabels: {
                        backgroundColor: isDark ? 'rgba(30, 41, 59, 0.85)' : 'rgba(255, 255, 255, 0.85)',
                        borderRadius: 1,
                        color: isDark ? '#f8fafc' : '#1e293b',
                        font: { weight: 'bold', size: 10 },
                        formatter: (value) => value > 0 ? new Intl.NumberFormat().format(value) : '',
                        padding: { top: 1, bottom: 0, left: 3, right: 3 },
                        anchor: 'end',
                        align: 'top',
                        offset: 4,
                        display: (context) => context.dataset.data[context.dataIndex] > 0 ? 'auto' : false,
                        clip: false
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: textMuted, font: { size: 10 } }
                    },
                    y: {
                        grid: { color: gridColor },
                        ticks: { color: textMuted, font: { size: 10 } },
                        grace: '20%'
                    }
                }
            }
        });

        // 3. Pareto Chart
        const fullParetoLabels = @json($paretoData['labels']);
        const fullParetoQuantities = @json($paretoData['quantities']);
        const fullParetoCumulative = @json($paretoData['cumulative']);
        let paretoPage = 1;
        const paretoPageSize = 6;

        const paretoCtx = document.getElementById('paretoChart').getContext('2d');
        const paretoChart = new Chart(paretoCtx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [
                    {
                        type: 'bar',
                        label: 'Volume',
                        data: [],
                        backgroundColor: '#6366f1',
                        borderRadius: 2,
                        yAxisID: 'y',
                        order: 2
                    },
                    {
                        type: 'line',
                        label: 'Cum %',
                        data: [],
                        borderColor: '#f59e0b',
                        borderWidth: 2,
                        backgroundColor: 'transparent',
                        pointBackgroundColor: '#f59e0b',
                        pointRadius: 3,
                        tension: 0.2,
                        yAxisID: 'y2',
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textMuted,
                            boxWidth: 10,
                            font: { size: 10, weight: '600' },
                            usePointStyle: false,
                            padding: 15
                        }
                    },
                    tooltip: {
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 },
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.dataset.type === 'line') {
                                    label += parseFloat(context.raw).toFixed(1) + '%';
                                } else {
                                    label += new Intl.NumberFormat().format(context.raw) + ' PCS';
                                }
                                return label;
                            }
                        }
                    },
                    datalabels: {
                        backgroundColor: isDark ? 'rgba(30, 41, 59, 0.85)' : 'rgba(255, 255, 255, 0.85)',
                        borderRadius: 1,
                        color: isDark ? '#f8fafc' : '#1e293b',
                        font: { weight: 'bold', size: 10 },
                        formatter: (value, context) => {
                            if (context.dataset.type === 'line') {
                                return parseFloat(value).toFixed(0) + '%';
                            }
                            return value > 0 ? new Intl.NumberFormat().format(value) : '';
                        },
                        padding: { top: 1, bottom: 0, left: 3, right: 3 },
                        anchor: (context) => context.dataset.type === 'line' ? 'end' : 'center',
                        align: (context) => context.dataset.type === 'line' ? 'top' : 'center',
                        offset: (context) => context.dataset.type === 'line' ? 4 : 0,
                        display: (context) => context.dataset.data[context.dataIndex] > 0 ? 'auto' : false,
                        clip: false
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: textMuted, font: { size: 10 } }
                    },
                    y: {
                        position: 'left',
                        grid: { color: gridColor },
                        ticks: {
                            color: textMuted,
                            font: { size: 10 },
                            callback: function(value) {
                                if (Math.floor(value) === value) return value;
                            }
                        },
                        grace: '20%'
                    },
                    y2: {
                        position: 'right',
                        grid: { display: false },
                        ticks: {
                            color: textMuted,
                            font: { size: 10 },
                            callback: function(value) { return value + '%'; }
                        },
                        min: 0,
                        max: 110
                    }
                }
            }
        });

        window.changeParetoPage = function(dir) {
            const maxPage = Math.ceil(fullParetoLabels.length / paretoPageSize) || 1;
            paretoPage += dir;
            if (paretoPage < 1) paretoPage = 1;
            if (paretoPage > maxPage) paretoPage = maxPage;
            updateParetoChart();
        };

        function updateParetoChart() {
            const start = (paretoPage - 1) * paretoPageSize;
            const end = start + paretoPageSize;
            
            paretoChart.data.labels = fullParetoLabels.slice(start, end);
            paretoChart.data.datasets[0].data = fullParetoQuantities.slice(start, end);
            paretoChart.data.datasets[1].data = fullParetoCumulative.slice(start, end);
            paretoChart.update();

            const maxPage = Math.ceil(fullParetoLabels.length / paretoPageSize) || 1;
            document.getElementById('paretoPageIndicator').textContent = `${paretoPage}/${maxPage}`;
            document.getElementById('btnParetoPrev').disabled = paretoPage <= 1;
            document.getElementById('btnParetoNext').disabled = paretoPage >= maxPage;
        }

        // Initialize Pareto Chart
        updateParetoChart();

        // ── DRILLDOWN MODAL ──────────────────────────────────────────────────────
        const drilldownUrl = '{{ route("api.tool.dashboard.drilldown") }}';
        let currentMonthYear = '{{ date("Y-m") }}'; // Fallback if no filter

        const DRILLDOWN_COLS = {
            stock: [
                { key: 'part_no',          label: 'Tool Information', cls: 'text-left py-2 px-3', style: 'min-width: 200px;' },
                { key: 'location',         label: 'Stock / Location', cls: 'text-left py-2 px-2', style: 'min-width: 140px;' },
                { key: 'min_stock',        label: 'Min',              cls: 'text-right py-2 px-2' },
                { key: 'max_stock',        label: 'Max',              cls: 'text-right py-2 px-2' },
                { key: 'status',           label: 'Status',           cls: 'text-center py-2 px-3' },
                { key: 'action_status',    label: 'Action',           cls: 'text-center py-2 px-3' },
                { key: 'action_remark',    label: 'Note',             cls: 'text-left py-2 px-3 max-w-[200px]' },
            ]
        };

        const STATUS_BADGE = {
            'Critical':   'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',
            'Warning':    'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
            'Over':       'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
            'Safe':       'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'
        };

        let drilldownPage = 1;
        let drilldownCurrentType = '';
        let drilldownCurrentLabel = '';
        let drilldownCurrentStatus = '';
        let searchDebounceTimer;

        window.openDrilldown = function(chartType, label, status = null, searchVal = null) {
            drilldownCurrentType = chartType;
            drilldownCurrentLabel = label;
            drilldownCurrentStatus = status || '';
            drilldownPage = 1;

            const modal  = document.getElementById('drilldownModal');
            const panel  = document.getElementById('drilldownPanel');
            const searchInput = document.getElementById('drilldownSearch');

            if(searchInput) searchInput.value = searchVal || '';

            modal.classList.remove('hidden');
            requestAnimationFrame(() => panel.classList.remove('translate-x-full'));
            
            document.getElementById('drilldownTitle').textContent = 'Loading...';
            document.getElementById('drilldownCountBadge').textContent = '0';

            renderDrilldownLegend(chartType, drilldownCurrentStatus);
            fetchDrilldownData(true);
        };

        function fetchDrilldownData(isInitial = false) {
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
                search: search,
                page: drilldownPage,
                pageSize: pageSize
            })
            .done(function(res) {
                document.getElementById('drilldownTitle').textContent = res.title;
                const cols = DRILLDOWN_COLS[res.chart] || [];
                const tbody = document.getElementById('drilldownBody');
                
                // Header
                document.getElementById('drilldownHead').innerHTML = '<tr>' + cols.map(c =>
                    `<th class="${c.cls} text-[11px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider bg-slate-50/50 dark:bg-gray-800/40" ${c.style ? `style="${c.style}"` : ''}>${c.label}</th>`
                ).join('') + '</tr>';

                // Body
                if (!res.data || res.data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="${cols.length}" class="py-10 text-center text-slate-400 italic text-xs">No data found.</td></tr>`;
                } else {
                    tbody.innerHTML = res.data.map(row => {
                        return '<tr class="hover:bg-slate-50 dark:hover:bg-gray-800/60 transition-colors border-b border-gray-50 dark:border-gray-800">' + cols.map(c => {
                            if (c.key === 'part_no') {
                                const brandStr = row.brand && row.brand !== '-' ? row.brand : 'No Brand';
                                const specStr = row.spec_code && row.spec_code !== '-' ? row.spec_code : '';
                                const subText = specStr ? `${brandStr} — ${specStr}` : brandStr;
                                return `<td class="${c.cls}">
                                    <div class="flex flex-col gap-0.5 text-left">
                                        <span class="font-bold text-gray-900 dark:text-white text-[11px]">${row.part_no}</span>
                                        <span class="text-[9px] text-slate-500 dark:text-gray-400 font-medium">${subText}</span>
                                    </div>
                                </td>`;
                            }

                            if (c.key === 'location') {
                                const val = row[c.key] ?? '';
                                return `<td class="${c.cls}">${val}</td>`;
                            }

                            if (c.key === 'action_status') {
                                const isCritical = row.status === 'Critical' || row.status === 'Warning';
                                if (!isCritical) return `<td class="${c.cls}"><span class="text-slate-300 italic text-[10px] font-medium">N/A</span></td>`;

                                const current = row[c.key] || '';
                                const statusMap = {
                                    '': { label: 'NEED ACTION', cls: 'bg-rose-50 text-rose-600 border-rose-100 dark:bg-rose-900/20 dark:text-rose-400' },
                                    'Process': { label: 'IN PROCESS', cls: 'bg-amber-50 text-amber-600 border-amber-100 dark:bg-amber-900/20 dark:text-amber-400' },
                                    'Ordered': { label: 'ORDERED', cls: 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400' }
                                };
                                const st = statusMap[current] || statusMap[''];

                                let actionIcon = '';
                                if (row.status === 'Critical' || row.status === 'Warning') {
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
                                        <div class="flex items-center justify-center">
                                            <button onclick="toggleStatusDropdown(event, '${row.id}')" 
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold border transition-all hover:bg-slate-50 dark:hover:bg-gray-700 ${st.cls}">
                                                <span class="w-1.5 h-1.5 rounded-full bg-current mr-1.5"></span>
                                                ${st.label}
                                                <i class="fa-solid fa-chevron-down ml-1 opacity-50 text-[8px]"></i>
                                            </button>
                                            ${actionIcon}
                                        </div>
                                        <div id="dropdown-${row.id}" class="hidden absolute right-0 mt-1 w-32 bg-white dark:bg-gray-800 rounded-xs shadow-xl border border-slate-200 dark:border-gray-700 z-[100] overflow-hidden">
                                            <div class="py-1">
                                                <button onclick="updateDrilldownActionStatus('${row.id}', '')" class="w-full text-left px-3.5 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 flex items-center">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-2.5"></span> NEED ACTION
                                                </button>
                                                <button onclick="updateDrilldownActionStatus('${row.id}', 'Process')" class="w-full text-left px-3.5 py-2 text-xs font-bold text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 flex items-center">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-2.5"></span> IN PROCESS
                                                </button>
                                                <button onclick="updateDrilldownActionStatus('${row.id}', 'Ordered')" class="w-full text-left px-3.5 py-2 text-xs font-bold text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 flex items-center">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2.5"></span> ORDERED
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>`;
                            }

                            const val = row[c.key] ?? '';
                            const badgeCls = c.key === 'status' ? STATUS_BADGE[val] : null;

                            if (c.key === 'action_remark') {
                                const isCritical = row.status === 'Critical' || row.status === 'Warning';
                                if (!isCritical) return `<td class="${c.cls}"><span class="text-slate-300 italic text-[10px] font-medium">N/A</span></td>`;
                                
                                const displayNote = val || 'Add note...';
                                const noteCls = val ? 'text-slate-600 dark:text-slate-300' : 'text-slate-400/70 italic';

                                return `<td class="${c.cls}">
                                    <div class="relative w-full dropdown-note-container">
                                        <button onclick="toggleNoteDropdown(event, '${row.id}')" 
                                            class="group flex items-start gap-1.5 w-full p-1.5 rounded-xs hover:bg-slate-50 dark:hover:bg-gray-800 transition-all border border-transparent hover:border-gray-100 dark:hover:border-gray-700 text-left">
                                            <i class="fa-solid fa-pen-to-square text-[11px] mt-0.5 opacity-30 group-hover:opacity-100 transition-opacity flex-shrink-0"></i>
                                            <span class="text-[10px] font-medium ${noteCls} break-words line-clamp-2 leading-snug">${displayNote}</span>
                                        </button>
                                        
                                        <div id="note-dropdown-${row.id}" class="hidden absolute right-0 mt-2 w-72 bg-white dark:bg-gray-800 rounded-xs shadow-2xl border border-slate-200 dark:border-gray-700 z-[110] p-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Action Remark</p>
                                                <i class="fa-solid fa-message text-primary-500/50 text-xs"></i>
                                            </div>
                                            <textarea id="note-input-${row.id}" 
                                                class="w-full h-24 p-2.5 bg-slate-50 dark:bg-gray-900/50 border border-slate-100 dark:border-gray-700 rounded-xs text-xs font-medium focus:outline-none focus:ring-1 focus:ring-primary-500 placeholder:text-slate-300 mb-3"
                                                placeholder="Type follow-up note here...">${val}</textarea>
                                            <div class="flex justify-end gap-2">
                                                <button onclick="closeAllNoteDropdowns()" class="px-3.5 py-2 text-xs font-semibold text-slate-400 hover:text-slate-600 transition-colors">Cancel</button>
                                                <button onclick="saveActionNote('${row.id}')" class="px-4.5 py-2 bg-primary-500 hover:bg-primary-600 text-white text-xs font-bold rounded-xs shadow-sm transition-all active:scale-95">Save Changes</button>
                                            </div>
                                        </div>
                                    </div>
                                </td>`;
                            }

                            const cell = badgeCls
                                ? `<div class="flex items-center justify-center">
                                    <span class="inline-block px-2.5 py-1 rounded-xs text-[11px] font-extrabold ${badgeCls}">${val}</span>
                                   </div>`
                                : `<span class="${c.key === 'part_no' ? 'font-medium text-slate-700 dark:text-gray-200' : 'text-slate-500 dark:text-slate-400'}">${val}</span>`;
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
            
            const legends = ['Critical', 'Warning', 'Over', 'Safe'];
            
            const allBtn = createLegendBtn('All', activeStatus === '');
            allBtn.onclick = () => { drilldownCurrentStatus = ''; drilldownPage = 1; updateLegendActive(allBtn); fetchDrilldownData(); };
            container.appendChild(allBtn);

            legends.forEach(leg => {
                const isActive = leg.toLowerCase() === activeStatus.toLowerCase();
                const btn = createLegendBtn(leg, isActive);
                btn.onclick = () => { drilldownCurrentStatus = leg; drilldownPage = 1; updateLegendActive(btn); fetchDrilldownData(); };
                container.appendChild(btn);
            });
        }

        function createLegendBtn(label, isActive) {
            const btn = document.createElement('button');
            btn.className = `legend-btn px-4 py-1.5 rounded-md text-[10px] font-extrabold uppercase transition-all duration-200 ${
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
            window.location.reload(); // Reload to refresh balance warnings side-panel if anything changed
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
            if (!event.target.closest('.dropdown-note-container')) {
                closeAllNoteDropdowns();
            }
        });

        window.updateDrilldownActionStatus = function(id, status) {
            const dropdown = document.getElementById(`dropdown-${id}`);
            if (dropdown) dropdown.classList.add('hidden');

            const url = '{{ route("inventory.tool.dashboard.updateActionStatus", ["id" => ":id"]) }}'.replace(':id', id);
            
            $.post(url, {
                _token: '{{ csrf_token() }}',
                action_status: status
            })
            .done(function(res) {
                if (res.success) {
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
            const url = '{{ route("inventory.tool.dashboard.updateActionStatus", ["id" => ":id"]) }}'.replace(':id', id);
            
            $.post(url, {
                _token: '{{ csrf_token() }}',
                action_remark: val
            })
            .done(function(res) {
                if (res.success) {
                    closeAllNoteDropdowns();
                    fetchDrilldownData();
                }
            });
        };

        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDrilldownModal(); });

    });
</script>
@endpush
