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
                        <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400 tracking-tight leading-none mb-1 whitespace-nowrap">{{ $stat['label'] }}</p>
                        <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 leading-none tracking-tight whitespace-nowrap" id="{{ $stat['id'] }}">
                            {{ $stat['val'] }} <span class="text-[9px] text-slate-400 font-normal ml-0.5">{{ $stat['unit'] }}</span>
                        </h3>
                        @if($stat['label'] === 'Total Value')
                            <p class="text-[9px] text-slate-400 dark:text-slate-500 font-bold leading-none mt-1 whitespace-nowrap">
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
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                    <td class="py-2 px-3 text-slate-800 dark:text-gray-200">
                                        <div class="font-bold truncate max-w-[130px]" title="{{ $warning['tool_name'] }}">{{ $warning['tool_name'] }}</div>
                                        <div class="text-[9px] text-slate-400 font-medium truncate max-w-[130px]">{{ $warning['spec_code'] }} | {{ $warning['location'] }}</div>
                                    </td>
                                    <td class="py-2 px-2 text-right text-slate-500 dark:text-slate-400 font-semibold">{{ $warning['qty_min'] }}</td>
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
                                        <div class="text-xs font-semibold">Stock nominal and safe!</div>
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
                                <th class="py-2 px-2 text-[10px] font-medium text-slate-500 dark:text-slate-400 tracking-wider text-right">Qty</th>
                                <th class="py-2 px-3 text-[10px] font-medium text-slate-500 dark:text-slate-400 tracking-wider text-right">Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-700 text-[11px] font-medium">
                            @forelse($latestSlowBatches as $batch)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                    <td class="py-2 px-3 text-slate-800 dark:text-gray-200">
                                        <div class="font-bold">{{ $batch->id_number }}</div>
                                        <div class="text-[9px] text-slate-400 font-medium truncate max-w-[80px]" title="{{ $batch->location?->name }}">{{ $batch->location?->name ?? '-' }}</div>
                                    </td>
                                    <td class="py-2 px-2 text-slate-500 dark:text-slate-400">
                                        <div class="font-bold text-slate-700 dark:text-slate-300 truncate max-w-[100px]" title="{{ $batch->tool?->name }}">{{ $batch->tool?->name }}</div>
                                        <div class="text-[9px] font-medium truncate max-w-[100px]" title="{{ $batch->tool?->spec_code }}">{{ $batch->tool?->spec_code }}</div>
                                    </td>
                                    <td class="py-2 px-2 text-center whitespace-nowrap">
                                        <span class="font-bold text-[10px] px-1.5 py-0.5 rounded-xs {{ $batch->age_years >= $batch->std_lifetime_yrs ? 'text-rose-600 bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/30' : 'text-slate-600 bg-slate-100 dark:bg-gray-800' }}">
                                            {{ number_format($batch->age_years, 1) }} / {{ $batch->std_lifetime_yrs }} Yr
                                        </span>
                                    </td>
                                    <td class="py-2 px-2 text-right text-slate-700 dark:text-slate-300 font-semibold">{{ $batch->qty_current }}</td>
                                    <td class="py-2 px-3 text-right font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">IDR {{ number_format($batch->current_value, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">
                                        <p class="text-xs font-semibold">No active slow batches.</p>
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
                    <h3 class="text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center">
                        <i class="fa-solid fa-clock-rotate-left mr-2 text-primary-500"></i> Recent Activity
                    </h3>
                </div>
                <div class="overflow-y-auto flex-1 custom-scrollbar border border-gray-100 dark:border-gray-700/50 rounded-xs">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/80 dark:bg-gray-700/50 sticky top-0 z-10 backdrop-blur-md">
                            <tr>
                                <th class="py-2.5 px-3 text-[11px] font-semibold text-slate-500 dark:text-slate-400 tracking-wider">Tool / Spec</th>
                                <th class="py-2.5 px-2 text-[11px] font-semibold text-slate-500 dark:text-slate-400 tracking-wider text-center">Type</th>
                                <th class="py-2.5 px-2 text-[11px] font-semibold text-slate-500 dark:text-slate-400 tracking-wider text-right">Qty</th>
                                <th class="py-2.5 px-3 text-[11px] font-semibold text-slate-500 dark:text-slate-400 tracking-wider text-center">Date</th>
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
                                        <div class="font-bold truncate max-w-[130px]" title="{{ $act['tool_name'] }}">{{ $act['tool_name'] }}</div>
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
                                    <td class="py-2.5 px-3 text-center text-slate-400 text-[11px] whitespace-nowrap" title="{{ $act['timestamp'] }}">{{ $act['display_time'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-400">
                                        <p class="text-xs font-semibold">No recent activity detected.</p>
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
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
    Chart.register(ChartDataLabels);

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
        const stockLabels = @json(array_keys($groupedStockStatus));
        const stockData = @json(array_values($groupedStockStatus));

        const stockCtx = document.getElementById('stockStatusChart').getContext('2d');
        new Chart(stockCtx, {
            type: 'bar',
            data: {
                labels: stockLabels,
                datasets: [
                    {
                        label: 'Critical',
                        data: stockData.map(d => d.critical),
                        backgroundColor: '#ef4444',
                        borderRadius: 2
                    },
                    {
                        label: 'Warning',
                        data: stockData.map(d => d.warning),
                        backgroundColor: '#f59e0b',
                        borderRadius: 2
                    },
                    {
                        label: 'Over',
                        data: stockData.map(d => d.over),
                        backgroundColor: '#3b82f6',
                        borderRadius: 2
                    },
                    {
                        label: 'Safe',
                        data: stockData.map(d => d.safe),
                        backgroundColor: '#10b981',
                        borderRadius: 2
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
                            pointStyle: 'rect',
                            padding: 15
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        titleFont: { size: 11, weight: 'bold' },
                        bodyFont: { size: 10 }
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
                        position: 'top',
                        labels: {
                            color: textMuted,
                            boxWidth: 10,
                            font: { size: 10, weight: '600' }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        titleFont: { size: 11, weight: 'bold' },
                        bodyFont: { size: 10 }
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
        const paretoCtx = document.getElementById('paretoChart').getContext('2d');
        new Chart(paretoCtx, {
            type: 'bar',
            data: {
                labels: @json($paretoData['labels']),
                datasets: [
                    {
                        type: 'bar',
                        label: 'Volume',
                        data: @json($paretoData['quantities']),
                        backgroundColor: '#6366f1',
                        borderRadius: 2,
                        yAxisID: 'y',
                        order: 2
                    },
                    {
                        type: 'line',
                        label: 'Cum %',
                        data: @json($paretoData['cumulative']),
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
                        position: 'top',
                        labels: {
                            color: textMuted,
                            boxWidth: 10,
                            font: { size: 10, weight: '600' }
                        }
                    },
                    tooltip: {
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
                        ticks: { color: textMuted, font: { size: 10 } },
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
    });
</script>
@endpush
