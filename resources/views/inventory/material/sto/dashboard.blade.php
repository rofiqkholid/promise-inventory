@extends('layouts.app')

@section('title', 'STO Analytics Dashboard')
@section('page_title', 'STO Analytics')

@section('content')
<div class="dashboard-container w-full h-auto overflow-y-auto lg:h-[calc(100vh-85px)] lg:overflow-hidden flex flex-col gap-2 pb-0 custom-scrollbar select-none">
    {{-- Header, KPIs & Filters --}}
    <div class="flex flex-col xl:flex-row items-stretch xl:items-center justify-between gap-3 shrink-0">
        <!-- Section 1: Title & Subtitle Section -->
        <div class="flex-none">
            <h2 class="text-xl xl:text-2xl font-bold text-gray-800 dark:text-white leading-tight mb-0.5 flex items-center gap-2">
                STO Analytics
            </h2>
            <p class="text-[11px] text-slate-500 dark:text-gray-400 leading-tight">Stock Opname Analysis Deviations</p>
        </div>

        <!-- Section 2: KPI Cards & Filter Toggle -->
        <div class="flex-1 flex flex-col gap-2 items-stretch xl:items-center xl:flex-row lg:justify-end w-full">
            <!-- KPI Grid (6 slots total = 3 full 2-col rows on mobile) -->
            <div class="grid grid-cols-2 sm:grid-cols-2 xl:grid-cols-6 gap-2 flex-1">
                <!-- 1. Latest Event Card (Spans 2 columns) -->
                <div class="col-span-2 sm:col-span-1 xl:col-span-2 bg-white dark:bg-gray-800 rounded-xs border border-gray-200 dark:border-gray-700 px-2.5 py-1.5 flex items-center gap-2.5 min-h-[52px] h-auto transition-all duration-200">
                    <div class="w-8 h-8 rounded-xs bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600 dark:text-amber-400 flex-shrink-0">
                        <i class="fa-solid fa-tag text-xs"></i>
                    </div>
                    <div class="min-w-0 flex-1 flex flex-col justify-center">
                        <p class="text-[9px] font-bold text-slate-550 dark:text-slate-400 uppercase tracking-wider leading-none mb-0.5">Latest Event</p>
                        <div class="flex flex-wrap items-baseline gap-x-1.5 min-w-0">
                            <span class="text-xs font-extrabold text-slate-800 dark:text-white leading-tight truncate">{{ $stats['last_event'] }}</span>
                            @if($stats['last_period'] && $stats['last_period'] !== '-')
                                <span class="text-[8.5px] font-semibold text-slate-400 dark:text-slate-500 truncate">({{ $stats['last_period'] }})</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 2, 3, 4. KPI Cards -->
                @foreach([
                    ['icon' => 'fa-calendar-check', 'color' => 'slate',   'label' => 'Total Events',   'val' => $stats['total_events'],   'unit' => 'STO cycles'],
                    ['icon' => 'fa-lock',           'color' => 'indigo',  'label' => 'Closed Events',  'val' => $stats['closed_events'],  'unit' => 'completed'],
                    ['icon' => 'fa-spinner',        'color' => 'emerald', 'label' => 'Open Events',    'val' => $stats['open_events'],    'unit' => 'in progress'],
                ] as $kpi)
                <div class="bg-white dark:bg-gray-800 rounded-xs border border-gray-200 dark:border-gray-700 px-2.5 py-1.5 flex items-center gap-2.5 min-h-[52px] h-auto transition-all duration-200">
                    <div class="w-8 h-8 rounded-xs bg-{{ $kpi['color'] }}-50 dark:bg-{{ $kpi['color'] }}-900/20 flex items-center justify-center text-{{ $kpi['color'] }}-600 dark:text-{{ $kpi['color'] }}-400 flex-shrink-0">
                        <i class="fa-solid {{ $kpi['icon'] }} text-xs"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[9px] font-bold text-slate-550 dark:text-slate-400 uppercase tracking-wider leading-none mb-0.5 truncate">{{ $kpi['label'] }}</p>
                        <p class="text-xs font-extrabold text-slate-800 dark:text-white leading-none truncate">{{ $kpi['val'] }}
                            @if($kpi['unit'])<span class="text-[8px] font-semibold text-slate-400 dark:text-slate-500 ml-0.5 normal-case">{{ $kpi['unit'] }}</span>@endif
                        </p>
                    </div>
                </div>
                @endforeach

                <!-- 5. Filter Toggle Button (Fills 6th slot next to Open Events on Mobile) -->
                <button id="btnToggleDashFilter" type="button" title="Toggle Filters" class="col-span-1 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 px-2.5 py-1.5 flex items-center justify-center gap-2 min-h-[52px] h-auto transition-all duration-200 hover:bg-slate-50 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-200 font-bold text-xs shadow-2xs group">
                    <i class="fa-solid fa-filter text-slate-400 group-hover:text-slate-600 dark:group-hover:text-white text-xs"></i>
                    <span class="uppercase tracking-wider text-[10px]">Filter</span>
                </button>
            </div>

            <!-- Correction Log Button -->
            <div class="shrink-0 flex items-stretch">
                <button onclick="openGlobalAuditLogsModal()" title="View Global Correction Log" class="w-full xl:w-auto px-4 flex items-center justify-center gap-2 text-xs font-bold text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-600 dark:hover:bg-primary-500 rounded-xs transition-all h-[42px] xl:h-[52px] shadow-2xs">
                    <i class="fa-solid fa-clock-rotate-left text-xs text-white/80"></i> <span class="uppercase tracking-widest text-[9px] font-black">Correction Log</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Collapsible Filter Card --}}
    <div id="dashboardFilterCard" class="hidden bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 p-3 shrink-0 mb-1">
        <div class="flex flex-wrap items-center gap-4 sm:gap-6">
            <!-- Filter Mode Select -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-1.5 sm:gap-2 w-full sm:w-auto">
                <label class="text-[11px] font-bold text-slate-550 dark:text-slate-400 uppercase tracking-widest leading-none whitespace-nowrap">Filter Scope:</label>
                <div class="w-full sm:w-[180px]">
                    <select id="filterScope" class="w-full">
                        <option value="event" selected>Single STO Event</option>
                        <option value="range">Aggregate Range</option>
                    </select>
                </div>
            </div>

            <!-- Single STO Event Container -->
            <div id="divEventSelector" class="flex flex-col sm:flex-row sm:items-center gap-1.5 sm:gap-2 w-full sm:w-auto">
                <label class="text-[11px] font-bold text-slate-550 dark:text-slate-400 uppercase tracking-widest leading-none whitespace-nowrap">STO Cycle:</label>
                <div class="w-full sm:w-[240px]">
                    <select id="eventSelector" class="w-full">
                        @foreach($recentEvents as $e)
                            <option value="{{ $e['hash_id'] }}" data-code="{{ $e['code'] }}" data-period="{{ $e['period'] }}">{{ $e['code'] }} ({{ $e['period'] }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Time Range Container (Hidden by default) -->
            <div id="divRangeSelector" class="flex flex-col sm:flex-row sm:items-center gap-1.5 sm:gap-2 w-full sm:w-auto hidden">
                <label class="text-[11px] font-bold text-slate-550 dark:text-slate-400 uppercase tracking-widest leading-none whitespace-nowrap">Aggregate Range:</label>
                <div class="w-full sm:w-[200px]">
                    <select id="rangeSelector" class="w-full">
                        <option value="3m" data-code="Last 3 Months (Overall)" data-period="3 Months">Last 3 Months</option>
                        <option value="6m" data-code="Last 6 Months (Overall)" data-period="6 Months">Last 6 Months</option>
                        <option value="12m" data-code="Last 1 Year (Overall)" data-period="1 Year">Last 1 Year</option>
                        <option value="all" data-code="All STO Events (Overall)" data-period="All Time">All STO Events</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ROW 1: Top Cards --}}
    <div class="lg:flex-[50] flex flex-col lg:flex-row gap-2 min-h-0">
        <!-- Top-Left Card: Summary Result -->
        <div class="chart-card w-full lg:w-1/2 bg-white dark:bg-gray-800 p-2.5 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative min-h-[280px] lg:min-h-0 overflow-hidden transition-all duration-200">
            <div class="flex-none flex flex-wrap sm:flex-nowrap justify-between items-center gap-2 mb-1.5">
                <h3 class="text-xs sm:text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center gap-1.5 min-w-0 pr-1">
                    <i class="fa-solid fa-square-poll-vertical mr-1 text-primary-500 shrink-0"></i>
                    <span class="truncate">Summary Result</span>
                    <span class="hidden sm:inline-block ml-1 px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-slate-700 text-[10px] font-medium text-slate-500 dark:text-slate-400 border border-slate-200/50 dark:border-slate-600/50 shrink-0 whitespace-nowrap">(Target: Net ±1%, Abs 4%)</span>
                </h3>
                <!-- Inner Card Tabs -->
                <div class="flex bg-gray-100 dark:bg-gray-700/80 p-0.5 rounded-xs shrink-0 gap-0.5">
                    <button onclick="switchSummaryTab('amount')" id="summaryTabBtn-amount" class="px-2 py-1 rounded-xs text-[9px] font-bold uppercase tracking-wider transition-all whitespace-nowrap bg-white dark:bg-gray-600 text-slate-800 dark:text-white shadow-sm">
                        Mio IDR
                    </button>
                    <button onclick="switchSummaryTab('net')" id="summaryTabBtn-net" class="px-2 py-1 rounded-xs text-[9px] font-bold uppercase tracking-wider transition-all whitespace-nowrap bg-transparent text-slate-400 hover:text-slate-650 hover:bg-white/50 dark:hover:text-slate-200 dark:hover:bg-white/5">
                        % Net
                    </button>
                    <button onclick="switchSummaryTab('abs')" id="summaryTabBtn-abs" class="px-2 py-1 rounded-xs text-[9px] font-bold uppercase tracking-wider transition-all whitespace-nowrap bg-transparent text-slate-400 hover:text-slate-650 hover:bg-white/50 dark:hover:text-slate-200 dark:hover:bg-white/5">
                        % Abs
                    </button>
                </div>
            </div>
            
            <div class="flex-1 flex min-h-[220px] lg:min-h-0 items-center w-full h-full">
                <!-- Mini Chart Panel (full width) -->
                <div class="w-full h-full relative min-w-0 min-h-[220px] lg:min-h-0">
                     <div id="summaryAmountContainer" class="absolute inset-0 w-full h-full">
                          <canvas id="summaryAmountChart" class="w-full h-full"></canvas>
                     </div>
                     <div id="summaryNetContainer" class="absolute inset-0 w-full h-full hidden">
                          <canvas id="summaryNetChart" class="w-full h-full"></canvas>
                     </div>
                     <div id="summaryAbsContainer" class="absolute inset-0 w-full h-full hidden">
                          <canvas id="summaryAbsChart" class="w-full h-full"></canvas>
                     </div>
                </div>
            </div>
        </div>

        <!-- Top-Right Card: Accuracy Based on Cust -->
        <div class="chart-card w-full lg:w-1/2 bg-white dark:bg-gray-800 p-2.5 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative min-h-[280px] lg:min-h-0 overflow-hidden transition-all duration-200">
            <div class="flex-none flex justify-between items-center mb-1.5">
                <h3 class="text-xs sm:text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center min-w-0 pr-1">
                    <i class="fa-solid fa-scale-unbalanced mr-1.5 text-primary-500 shrink-0"></i>
                    <span class="truncate">Accuracy based on Cust</span>
                </h3>
                <!-- Inner Card Tabs -->
                <div class="flex bg-gray-100 dark:bg-gray-700/80 p-0.5 rounded-xs shrink-0 gap-0.5">
                    <button onclick="switchAccuracyTab('net')" id="accuracyTabBtn-net" class="px-2 py-1 rounded-xs text-[9px] font-bold uppercase tracking-wider transition-all whitespace-nowrap bg-white dark:bg-gray-600 text-slate-800 dark:text-white shadow-sm">
                        NET
                    </button>
                    <button onclick="switchAccuracyTab('abs')" id="accuracyTabBtn-abs" class="px-2 py-1 rounded-xs text-[9px] font-bold uppercase tracking-wider transition-all whitespace-nowrap bg-transparent text-slate-400 hover:text-slate-650 hover:bg-white/50 dark:hover:text-slate-200 dark:hover:bg-white/5">
                        ABS
                    </button>
                </div>
            </div>

            <!-- Chart Panel -->
            <div class="flex-1 min-h-[220px] lg:min-h-0 relative mt-1 w-full h-full">
                 <div id="accuracyNetContainer" class="absolute inset-0 w-full h-full">
                     <canvas id="accuracyNetChart" class="w-full h-full"></canvas>
                 </div>
                 <div id="accuracyAbsContainer" class="absolute inset-0 w-full h-full hidden">
                     <canvas id="accuracyAbsChart" class="w-full h-full"></canvas>
                 </div>
            </div>
        </div>
    </div>
    {{-- ROW 2: Bottom Cards --}}
    <div class="lg:flex-[50] flex flex-col lg:flex-row gap-2 min-h-0">
        <!-- Bottom-Left Card: Pareto Deviation by Part (1/2 width) -->
        <div class="chart-card w-full lg:w-1/2 bg-white dark:bg-gray-800 p-2.5 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative min-h-[280px] lg:min-h-0 overflow-hidden transition-all duration-200">
            <div class="flex-none flex justify-between items-center mb-1.5">
                <h3 class="text-xs sm:text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center min-w-0 pr-1">
                    <i class="fa-solid fa-chart-column mr-1.5 text-primary-500 shrink-0"></i>
                    <span class="truncate">Pareto Deviation by Part</span>
                    <span id="tab1-event-badge" class="hidden sm:inline-block ml-1 px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-slate-700 text-[10px] font-medium text-slate-500 dark:text-slate-400 border border-slate-200/50 dark:border-slate-600/50 shrink-0 whitespace-nowrap">...</span>
                </h3>
                <div class="flex items-center gap-1 shrink-0 border-l border-gray-200 dark:border-gray-700 pl-2">
                    <button id="btnParetoPrev" onclick="prevParetoPage()" class="w-6 h-6 flex items-center justify-center rounded-xs bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-500 disabled:opacity-30 disabled:cursor-not-allowed transition-colors" title="Previous page">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    </button>
                    <span id="paretoPageIndicator" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 min-w-[24px] text-center">1/1</span>
                    <button id="btnParetoNext" onclick="nextParetoPage()" class="w-6 h-6 flex items-center justify-center rounded-xs bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-500 disabled:opacity-30 disabled:cursor-not-allowed transition-colors" title="Next page">
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </button>
                </div>
            </div>
            <div class="relative w-full flex-1 min-h-[220px] lg:min-h-0">
                <canvas id="paretoModelChart" class="absolute inset-0 w-full h-full"></canvas>
            </div>
        </div>

        <!-- Bottom-Right Card: Problem Reason (1/2 width) -->
        <div class="chart-card w-full lg:w-1/2 bg-white dark:bg-gray-800 p-2.5 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative min-h-[280px] lg:min-h-0 overflow-hidden transition-all duration-200">
            <div class="flex-none flex justify-between items-center mb-1.5">
                <h3 class="text-xs sm:text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center min-w-0 pr-1">
                    <i class="fa-solid fa-triangle-exclamation mr-1.5 text-primary-500 shrink-0"></i>
                    <span class="truncate">Problem Reason</span>
                </h3>
            </div>
            <div class="relative w-full flex-1 min-h-[220px] lg:min-h-0">
                <canvas id="problemBreakdownChart" class="absolute inset-0 w-full h-full"></canvas>
            </div>
        </div>
    </div></div>{{-- Global Correction Log Modal (Original Audit Table Interface) --}}
<div id="correctionDetailModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 p-0 md:p-4" role="dialog" aria-modal="true">
    <div class="relative w-full h-full md:h-[95vh] md:w-[95vw] transform overflow-hidden md:rounded-xs bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 flex flex-col shadow-2xl transition-all">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 shrink-0">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white uppercase tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-primary-500"></i> Global STO Correction Log
            </h3>
            <button onclick="closeCorrectionModal()" class="text-gray-400 hover:text-gray-500 w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" title="Close Modal">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        
        <div class="flex-1 min-h-0 flex flex-col bg-white dark:bg-gray-900 overflow-hidden">
            <table id="correctionLogTable" class="custom-table w-full text-left border-collapse">
                <thead class="sticky top-0 bg-gray-50 dark:bg-gray-800/80 text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-gray-800 z-10">
                    <tr>
                        <th class="py-3 px-4 text-left text-[10px] font-medium tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Model Identification</th>
                        <th class="py-3 px-3 text-center w-28 text-[10px] font-medium tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Customer</th>
                        <th class="py-3 px-3 text-center w-28 text-[10px] font-medium tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Events</th>
                        <th class="py-3 px-3 text-center w-32 text-[10px] font-medium tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Affected Parts</th>
                        <th class="py-3 px-4 text-right w-44 text-[10px] font-medium tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">ABS Adj. Value</th>
                        <th class="py-3 px-3 text-center w-48 text-[10px] font-medium tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Qty Balance (+/-)</th>
                        <th class="py-3 px-4 text-right w-44 text-[10px] font-medium tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Net Impact</th>
                        <th class="py-3 px-6 text-center w-36 text-[10px] font-medium tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-[11px] font-medium text-gray-700 dark:text-gray-300">
                    @foreach($correctionByModel as $model)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors duration-150">
                            <td class="py-3 px-4">
                                <span class="font-normal text-gray-900 dark:text-white uppercase tracking-tight">{{ $model['model_name'] }}</span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="inline-flex items-center justify-center font-medium text-[9px] px-2 py-0.5 rounded-xs bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 uppercase">
                                    {{ $model['customer_code'] ?? 'Unknown' }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="inline-flex items-center justify-center font-medium text-[9px] px-2 py-0.5 rounded-xs bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 uppercase">
                                    {{ $model['event_count'] }} events
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="inline-flex items-center justify-center font-medium text-[9px] px-2 py-0.5 rounded-xs bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 uppercase">
                                    {{ $model['affected_parts'] }} parts
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="font-mono font-medium text-xs text-gray-900 dark:text-white">
                                    <span class="text-[9.5px] font-normal text-gray-400 dark:text-gray-550 mr-0.5">Rp</span>{{ number_format($model['total_correction']) }}
                                </div>
                            </td>
                            <td class="py-3 px-3 text-center">
                                @php
                                    $incVal = (float)($model['increment_pcs'] ?? 0);
                                    $decVal = (float)($model['decrement_pcs'] ?? 0);
                                @endphp
                                <div class="flex flex-col items-center gap-0.5 font-mono font-medium text-xs">
                                    <span class="{{ $incVal > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400 dark:text-slate-500' }}">{{ $incVal > 0 ? '+' : '' }}{{ number_format($incVal) }} pcs</span>
                                    <span class="{{ $decVal > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400 dark:text-slate-500' }}">{{ $decVal > 0 ? '-' : '' }}{{ number_format($decVal) }} pcs</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-medium text-xs">
                                @php
                                    $netCorrVal = (float)($model['net_correction'] ?? 0);
                                @endphp
                                @if($netCorrVal < 0)
                                    <span class="text-rose-600 dark:text-rose-400">-Rp {{ number_format(abs($netCorrVal)) }}</span>
                                @elseif($netCorrVal > 0)
                                    <span class="text-rose-600 dark:text-rose-400">+Rp {{ number_format(abs($netCorrVal)) }}</span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">Rp 0</span>
                                @endif
                            </td>
                            <td class="py-3 px-6 text-center">
                                <button onclick="showCorrectionDetail('{{ $model['model_name'] }}')" title="Explore detailed correction logs" class="h-7 px-3 inline-flex items-center justify-center bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-[9px] font-medium text-gray-600 dark:text-gray-400 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    View Logs
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Audit Sub Modal --}}
        <div id="subModalContainer" class="hidden absolute inset-0 z-35 bg-white dark:bg-gray-900 flex flex-col rounded-xs overflow-hidden">
            <div class="w-full h-full flex flex-col overflow-hidden">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 shrink-0">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white uppercase tracking-widest flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-primary-500"></i> Correction Trail Log — <span id="modalModelName" class="text-primary-600 dark:text-primary-400 font-medium">Model Name</span>
                    </h3>
                    <button onclick="closeSubModal()" class="text-gray-400 hover:text-gray-500 w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" title="Back to Overview">
                        <i class="fa-solid fa-arrow-left text-base"></i>
                    </button>
                </div>
                <div class="flex-1 min-h-0 flex flex-col bg-white dark:bg-gray-900 overflow-hidden">
                    <table id="correctionSubTable" class="custom-table w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-gray-50 dark:bg-gray-800/80 text-[10px] font-medium text-gray-555 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-gray-800 z-10">
                            <tr>
                                <th class="py-3 px-4 text-left text-[10px] font-medium tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">STO Event</th>
                                <th class="py-3 px-3 text-left text-[10px] font-medium tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Part No</th>
                                <th class="py-3 px-3 text-left text-[10px] font-medium tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Reason Category</th>
                                <th class="py-3 px-4 text-right w-36 text-[10px] font-medium tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Quantity Adj.</th>
                                <th class="py-3 px-4 text-right w-40 text-[10px] font-medium tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Value Impact</th>
                                <th class="py-3 px-6 text-left text-[10px] font-medium tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Correction Remark</th>
                            </tr>
                        </thead>
                        <tbody id="correctionDetailBody" class="divide-y divide-gray-100 dark:divide-gray-800 text-[11px] font-medium text-gray-700 dark:text-gray-300">
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800 shrink-0 flex justify-end">
                    <button onclick="closeSubModal()" class="px-4 py-2.5 bg-white dark:bg-gray-850 border border-gray-200 dark:border-gray-700 rounded-xs text-[10px] font-medium text-gray-600 dark:text-gray-400 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-left text-[10px]"></i> Back to Overview
                    </button>
                </div>
            </div>
        </div>
        
        <div class="px-6 py-3 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800 shrink-0 flex justify-end">
            <button onclick="closeCorrectionModal()" class="px-5 py-2.5 bg-white dark:bg-gray-850 border border-gray-200 dark:border-gray-700 rounded-xs text-[10px] font-medium text-gray-600 dark:text-gray-400 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center gap-1.5">
                <i class="fa-solid fa-xmark text-[10px]"></i> Close Correction Log
            </button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Custom DataTable styles for Correction Log and Sub-table */
    #correctionLogTable_wrapper,
    #correctionSubTable_wrapper {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        height: 100%;
    }
    
    #correctionLogTable th:first-child,
    #correctionLogTable td:first-child,
    #correctionSubTable th:first-child,
    #correctionSubTable td:first-child {
        padding-left: 1.5rem !important; /* px-6 equivalent */
    }
    
    #correctionLogTable th:last-child,
    #correctionLogTable td:last-child,
    #correctionSubTable th:last-child,
    #correctionSubTable td:last-child {
        padding-right: 1.5rem !important; /* px-6 equivalent */
    }
    
    #correctionLogTable tbody tr,
    #correctionSubTable tbody tr {
        transition: all 0.15s ease-in-out;
    }
    }
    
    #correctionLogTable tbody tr:nth-child(even),
    #correctionSubTable tbody tr:nth-child(even) {
        background-color: rgba(248, 250, 252, 0.4);
    }
    .dark #correctionLogTable tbody tr:nth-child(even),
    .dark #correctionSubTable tbody tr:nth-child(even) {
        background-color: rgba(17, 24, 39, 0.2);
    }

    /* Align and style search input and length menu */
    .dataTables_wrapper .dataTables_length select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
        background-position: right 0.5rem center !important;
        background-repeat: no-repeat !important;
        background-size: 1.25em 1.25em !important;
        padding-right: 2rem !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        appearance: none !important;
        border-radius: 0.125rem !important;
        border: 1px solid rgba(203, 213, 225, 0.6) !important;
        font-size: 10px !important;
        font-weight: 700 !important;
        color: #475569 !important;
        transition: all 0.2s !important;
        background-color: #f8fafc !important;
    }
    .dark .dataTables_wrapper .dataTables_length select {
        background-color: #111827 !important;
        border-color: rgba(75, 85, 99, 0.6) !important;
        color: #d1d5db !important;
    }
    .dataTables_wrapper .dataTables_length select:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15) !important;
        outline: none !important;
    }

    .dataTables_wrapper .dataTables_filter input {
        border-radius: 0.125rem !important;
        border: 1px solid rgba(203, 213, 225, 0.6) !important;
        font-size: 11px !important;
        font-weight: 500 !important;
        padding: 0.5rem 0.75rem !important;
        width: 220px !important;
        transition: all 0.2s !important;
        outline: none !important;
        background-color: #f8fafc !important;
    }
    .dark .dataTables_wrapper .dataTables_filter input {
        background-color: #111827 !important;
        border-color: rgba(75, 85, 99, 0.6) !important;
        color: #f3f4f6 !important;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15) !important;
    }

    /* Pagination container styling */
    .dataTables_wrapper .dataTables_paginate {
        display: flex !important;
        align-items: center !important;
        gap: 4px !important;
        margin-top: 0.5rem !important;
    }

    /* Pagination button styling */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 28px !important;
        height: 28px !important;
        padding: 0 8px !important;
        font-size: 10px !important;
        font-weight: 700 !important;
        border-radius: 0.125rem !important;
        cursor: pointer !important;
        color: #64748b !important;
        background: #f8fafc !important;
        border: 1px solid rgba(226, 232, 240, 0.8) !important;
        transition: all 0.2s !important;
        user-select: none !important;
        text-transform: uppercase !important;
        letter-spacing: 0.025em !important;
    }

    .dark .dataTables_wrapper .dataTables_paginate .paginate_button {
        color: #94a3b8 !important;
        background: #111827 !important;
        border-color: rgba(55, 65, 81, 0.8) !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        color: #4f46e5 !important;
        background: #e0e7ff !important;
        border-color: rgba(99, 102, 241, 0.3) !important;
    }

    .dark .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        color: #818cf8 !important;
        background: rgba(99, 102, 241, 0.1) !important;
        border-color: rgba(129, 140, 248, 0.3) !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        color: #ffffff !important;
        background: #4f46e5 !important;
        border-color: #4f46e5 !important;
    }

    .dark .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dark .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        color: #ffffff !important;
        background: #6366f1 !important;
        border-color: #6366f1 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        opacity: 0.4 !important;
        background: #f8fafc !important;
        border-color: rgba(226, 232, 240, 0.8) !important;
        color: #94a3b8 !important;
        cursor: not-allowed !important;
        pointer-events: none !important;
    }

    .dark .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dark .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        background: #1f2937 !important;
        border-color: rgba(55, 65, 81, 0.8) !important;
        color: #4b5563 !important;
    }

    /* Info text */
    .dataTables_wrapper .dataTables_info {
        font-size: 11px !important;
        font-weight: 600 !important;
        color: #64748b !important;
        display: inline-block !important;
    }
    .dark .dataTables_wrapper .dataTables_info {
        color: #94a3b8 !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // --- Global Chart.js Defaults (match Inter layout theme) ---
    (function() {
        const isDark = document.documentElement.classList.contains('dark');
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.font.size   = 10.5;
        Chart.defaults.color       = isDark ? '#94a3b8' : '#64748b';
        Chart.defaults.borderColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)';
        Chart.defaults.plugins.legend.labels.padding = 15;
        Chart.defaults.plugins.legend.labels.usePointStyle = true;
    })();

    // --- Dynamic Currency/Numeric Suffix Formatter ---
    const formatSuffix = (v) => {
        const absVal = Math.abs(v);
        const sign = v < 0 ? '-' : '';
        if (absVal >= 1000000000) {
            let res = (absVal / 1000000000).toFixed(1);
            if (res.endsWith('.0')) res = res.slice(0, -2);
            return sign + res + 'B';
        }
        if (absVal >= 1000000) {
            let res = (absVal / 1000000).toFixed(1);
            if (res.endsWith('.0')) res = res.slice(0, -2);
            return sign + res + 'M';
        }
        if (absVal >= 1000) {
            return sign + (absVal / 1000).toFixed(0) + 'k';
        }
        return sign + absVal;
    };

    // --- Custom Chart.js Plugins ---
    
    // Custom plugin to draw premium numeric value tags on lines/bars
    const yellowDataLabelsPlugin = {
        id: 'yellowDataLabels',
        afterDatasetsDraw(chart, args, options) {
            const { ctx } = chart;
            const opt = options || {};
            
            if (!chart.data || !Array.isArray(chart.data.datasets)) return;
            
            const isDark = document.documentElement.classList.contains('dark');
            
            chart.data.datasets.forEach((dataset, datasetIndex) => {
                if (!dataset || !dataset.yellowLabels) return;
                if (!chart.isDatasetVisible(datasetIndex)) return;
                
                const meta = chart.getDatasetMeta(datasetIndex);
                if (!meta || !Array.isArray(meta.data)) return;
                
                meta.data.forEach((element, index) => {
                    if (!element) return;
                    const value = dataset.data[index];
                    if (value === undefined || value === null) return;
                    
                    let text = value;
                    let valNum = typeof value === 'number' ? value : parseFloat(value);
                    if (!isNaN(valNum)) {
                        if (dataset.yellowLabelFormat && typeof dataset.yellowLabelFormat === 'function') {
                            text = dataset.yellowLabelFormat(valNum, index);
                        } else {
                            text = valNum.toFixed(opt.precision !== undefined ? opt.precision : 2);
                            if (text.endsWith('.00')) text = text.slice(0, -3);
                            if (valNum < 0) {
                                text = '-' + Math.abs(valNum).toFixed(opt.precision !== undefined ? opt.precision : 2);
                                if (text.endsWith('.00')) text = text.slice(0, -3);
                            } else if (valNum === 0 && opt.zeroText) {
                                text = opt.zeroText;
                            }
                        }
                    }
                    
                    let x, y;
                    if (element.tooltipPosition && typeof element.tooltipPosition === 'function') {
                        try {
                            const pos = element.tooltipPosition();
                            x = pos ? pos.x : element.x;
                            y = pos ? pos.y : element.y;
                        } catch (err) {
                            x = element.x;
                            y = element.y;
                        }
                    } else {
                        x = element.x;
                        y = element.y;
                    }
                    if (x === undefined || y === undefined) {
                        x = element.x;
                        y = element.y;
                    }
                    ctx.save();
                    ctx.font = "bold 9.5px 'Inter', sans-serif";
                    const textWidth = ctx.measureText(text).width;
                    const rectWidth = textWidth + 8;
                    const rectHeight = 14;
                    
                    ctx.fillStyle = isDark ? 'rgba(30, 41, 59, 0.85)' : 'rgba(255, 255, 255, 0.85)';
                    ctx.strokeStyle = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)';
                    ctx.lineWidth = 1;
                    
                    const rx = x - rectWidth / 2;
                    let ry;
                    if (element.base !== undefined) {
                        const yCenter = (element.y + element.base) / 2;
                        ry = yCenter - rectHeight / 2;
                    } else {
                        ry = y - 20;
                    }
                    
                    ctx.beginPath();
                    if (ctx.roundRect) {
                        ctx.roundRect(rx, ry, rectWidth, rectHeight, 2.5);
                    } else {
                        ctx.rect(rx, ry, rectWidth, rectHeight);
                    }
                    ctx.fill();
                    ctx.stroke();
                    
                    ctx.fillStyle = isDark ? '#f8fafc' : '#1e293b';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(text, x, ry + rectHeight / 2 + 0.5);
                    ctx.restore();
                });
            });
        }
    };

    // Custom plugin to draw target threshold lines
    const targetLinesPlugin = {
        id: 'targetLines',
        afterDraw(chart) {
            const { ctx, scales } = chart;
            if (!scales || !scales.y) return;
            const y = scales.y;
            
            const plugins = chart.options && chart.options.plugins;
            if (!plugins) return;
            const targets = plugins.targetLines;
            if (!targets || !Array.isArray(targets)) return;
            
            const isDark = document.documentElement.classList.contains('dark');
            
            targets.forEach(target => {
                if (!target || typeof target.value !== 'number') return;
                const yVal = y.getPixelForValue(target.value);
                ctx.save();
                ctx.beginPath();
                ctx.moveTo(chart.chartArea.left, yVal);
                ctx.lineTo(chart.chartArea.right, yVal);
                ctx.strokeStyle = target.color || '#ef4444';
                ctx.lineWidth = target.lineWidth || 1.25;
                ctx.setLineDash(target.dash || [3, 3]);
                ctx.stroke();
                
                if (target.label) {
                    ctx.fillStyle = isDark ? 'rgba(30, 41, 59, 0.85)' : 'rgba(255, 255, 255, 0.85)';
                    ctx.strokeStyle = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)';
                    ctx.lineWidth = 1;
                    ctx.font = "bold 9.5px 'Inter', sans-serif";
                    const textWidth = ctx.measureText(target.label).width;
                    const rx = chart.chartArea.right - textWidth - 8;
                    const ry = yVal - 7;
                    ctx.beginPath();
                    if (ctx.roundRect) {
                        ctx.roundRect(rx, ry, textWidth + 6, 14, 2.5);
                    } else {
                        ctx.rect(rx, ry, textWidth + 6, 14);
                    }
                    ctx.fill();
                    ctx.stroke();
                    
                    ctx.fillStyle = isDark ? '#f8fafc' : '#1e293b';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(target.label, rx + (textWidth + 6)/2, ry + 7.5);
                }
                ctx.restore();
            });
        }
    };

    Chart.register(yellowDataLabelsPlugin, targetLinesPlugin);

    // --- State Variables ---
    let loadedEventId = null;
    let reasonBreakdownData = [];
    let recentEvents = @json($recentEvents);
    let statsData = @json($stats);
    let stoReasons = @json($reasons ?? []);
    let lastStoDashboardData = null;
    
    // Sub-Tabs State
    let summaryActiveTab = 'amount';
    let accuracyActiveTab = 'net';

    // Pareto Client-Side Pagination (Displays 7 data items per page)
    let paretoPage = 1;
    const itemsPerPage = 7;

    // Chart instances
    let paretoChart = null;
    let problemBreakdownChart = null;
    let accuracyNetChart = null;
    let accuracyAbsChart = null;
    let summaryAmountChart = null;
    let summaryNetChart = null;
    let summaryAbsChart = null;
    let subTable = null;
    let correctionTable = null;

    // --- Initialization ---
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle Filter Logic with Soft Active Highlight
        $('#btnToggleDashFilter').on('click', function(e) {
            e.stopPropagation();
            const $filterCard = $('#dashboardFilterCard');
            $filterCard.slideToggle(200, function() {
                const isOpen = $filterCard.is(':visible');
                const $btn = $('#btnToggleDashFilter');
                if (isOpen) {
                    $btn.addClass('bg-primary-50 dark:bg-primary-950/60 border-primary-300 dark:border-primary-700 text-primary-700 dark:text-primary-300 shadow-2xs')
                        .removeClass('bg-white dark:bg-gray-800 border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-200');
                    $btn.find('i').addClass('text-primary-600 dark:text-primary-400').removeClass('text-slate-400 group-hover:text-slate-600 dark:group-hover:text-white');
                } else {
                    $btn.removeClass('bg-primary-50 dark:bg-primary-950/60 border-primary-300 dark:border-primary-700 text-primary-700 dark:text-primary-300 shadow-2xs')
                        .addClass('bg-white dark:bg-gray-800 border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-200');
                    $btn.find('i').removeClass('text-primary-600 dark:text-primary-400').addClass('text-slate-400 group-hover:text-slate-600 dark:group-hover:text-white');
                }
            });
        });

        // Initialize Select2 on #filterScope
        const $scope = $('#filterScope');
        if ($scope.length) {
            $scope.select2({
                dropdownParent: $scope.parent(),
                minimumResultsForSearch: -1,
                dropdownAutoWidth: true,
                width: '100%'
            }).on('change', function() {
                const val = $(this).val();
                if (val === 'range') {
                    $('#divEventSelector').addClass('hidden');
                    $('#divRangeSelector').removeClass('hidden');
                    changeStoEvent($('#rangeSelector').val());
                } else {
                    $('#divRangeSelector').addClass('hidden');
                    $('#divEventSelector').removeClass('hidden');
                    changeStoEvent($('#eventSelector').val());
                }
            });
        }

        // Initialize Select2 on #rangeSelector
        const $range = $('#rangeSelector');
        if ($range.length) {
            $range.select2({
                dropdownParent: $range.parent(),
                minimumResultsForSearch: -1,
                dropdownAutoWidth: true,
                width: '100%'
            }).on('change', function() {
                changeStoEvent($(this).val());
            });
        }

        // Initialize Select2 on #eventSelector with premium config
        const $selector = $('#eventSelector');
        if ($selector.length) {
            $selector.select2({
                dropdownParent: $selector.parent(),
                dropdownAutoWidth: true,
                width: '100%'
            }).on('change', function() {
                changeStoEvent($(this).val());
            });
        }

        // Auto-select and load the default STO selector
        const scopeVal = $('#filterScope').val();
        if (scopeVal === 'range') {
            const rangeSelector = document.getElementById('rangeSelector');
            if (rangeSelector && rangeSelector.options.length > 0) {
                rangeSelector.selectedIndex = 0;
                changeStoEvent(rangeSelector.value);
            }
        } else {
            const eventSelector = document.getElementById('eventSelector');
            if (eventSelector && eventSelector.options.length > 0) {
                eventSelector.selectedIndex = 0;
                changeStoEvent(eventSelector.value);
            }
        }

        // Initialize all Summary Trend charts
        initSummaryTrendCharts();

        // Switch to default tabs
        switchSummaryTab('amount');
        switchAccuracyTab('net');

        // Initialize DataTable for main Correction Log Table using the premium helper
        correctionTable = null;
        if (window.defaultDataTable) {
            correctionTable = window.defaultDataTable('#correctionLogTable', {
                order: [[0, 'asc']], // Alphabetical order by Model Name
                pageLength: 10,
                lengthMenu: [10, 25, 50],
                dom: "<'flex flex-col sm:flex-row justify-between items-center mb-3 gap-2 px-6 pt-3'<'flex items-center gap-3'l B><'w-full sm:w-auto'f>>r<'overflow-y-auto overflow-x-auto flex-1 min-h-0 w-full relative custom-scrollbar't><'flex flex-col md:flex-row justify-between items-center gap-4 text-gray-500 py-3 px-6 border-t border-slate-100 dark:border-gray-700/60'i p>",
                language: {
                    search: "",
                    searchPlaceholder: "Filter by model name...",
                    emptyTable: `
                        <div class="py-16 flex flex-col items-center justify-center text-center w-full">
                            <div>
                                <i class="fa-solid fa-folder-open text-3xl text-slate-350 dark:text-gray-650 m-4"></i>
                            </div>
                            <h4 class="text-xs font-bold text-slate-700 dark:text-white uppercase tracking-widest mb-2">No Correction Models Found</h4>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500 max-w-xs mx-auto font-medium leading-relaxed">It looks like there are no correction models logged yet.</p>
                        </div>
                    `,
                    zeroRecords: `
                        <div class="py-16 flex flex-col items-center justify-center text-center w-full">
                            <div>
                                <i class="fa-solid fa-magnifying-glass text-3xl text-slate-350 dark:text-gray-650 m-4"></i>
                            </div>
                            <h4 class="text-xs font-bold text-slate-700 dark:text-white uppercase tracking-widest mb-2">No Matching Models</h4>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500 max-w-xs mx-auto font-medium leading-relaxed">We couldn't find any models matching your search. Try using a different keyword.</p>
                        </div>
                    `
                }
            });
        } else {
            correctionTable = $('#correctionLogTable').DataTable({
                processing: true,
                serverSide: false,
                scrollCollapse: true,
                autoWidth: false,
                ordering: true,
                order: [[0, 'asc']], // Alphabetical order by Model Name
                pageLength: 10,
                lengthMenu: [10, 25, 50],
                dom: "<'flex flex-col sm:flex-row justify-between items-center mb-3 gap-2 px-6 pt-3'<'flex items-center text-slate-500'l><'text-slate-500'f>><'overflow-y-auto overflow-x-auto flex-1 min-h-0 w-full relative custom-scrollbar't><'flex flex-col md:flex-row justify-between items-center gap-4 text-gray-500 py-3 px-6 border-t border-slate-100 dark:border-gray-700/60'i p>",
                language: {
                    search: "",
                    searchPlaceholder: "Filter by model name...",
                    paginate: { 
                        previous: '<i class="fa-solid fa-chevron-left text-[9px]"></i>', 
                        next: '<i class="fa-solid fa-chevron-right text-[9px]"></i>' 
                    },
                    emptyTable: '<div class="py-12 px-6 text-center italic text-slate-400 dark:text-slate-550">No matching correction models found.</div>'
                }
            });
        }

        // Style DataTables length dropdown and search input to match premium look
        $('#correctionLogTable_wrapper .dataTables_filter input').addClass('bg-slate-50 dark:bg-gray-900 border border-slate-200 dark:border-gray-750 text-[10px] font-medium rounded-xs px-2.5 py-1 w-44 focus:ring-0 focus:border-slate-400 outline-none transition-all placeholder:text-slate-400 placeholder:uppercase placeholder:font-medium');
        $('#correctionLogTable_wrapper .dataTables_length select').addClass('bg-slate-50 dark:bg-gray-900 border border-slate-200 dark:border-gray-700 text-[10px] font-medium rounded-xs px-2 py-1 mx-1.5 focus:ring-0 focus:border-slate-400 outline-none transition-all');
    });

    // --- Tab Switchers ---
    function switchSummaryTab(tabId) {
        summaryActiveTab = tabId;
        
        // Tab buttons styling
        ['amount', 'net', 'abs'].forEach(mode => {
            const btn = document.getElementById(`summaryTabBtn-${mode}`);
            if (btn) {
                if (mode === tabId) {
                    btn.className = 'px-2 py-1 rounded-xs text-[9px] font-medium uppercase tracking-wider transition-all whitespace-nowrap bg-white dark:bg-gray-600 text-slate-800 dark:text-white shadow-sm';
                } else {
                    btn.className = 'px-2 py-1 rounded-xs text-[9px] font-medium uppercase tracking-wider transition-all whitespace-nowrap bg-transparent text-slate-400 hover:text-slate-650 hover:bg-white/50 dark:hover:text-slate-200 dark:hover:bg-white/5';
                }
            }
        });
        
        // Wrapper containers visibility
        ['amount', 'net', 'abs'].forEach(mode => {
            const containerId = 'summary' + mode.charAt(0).toUpperCase() + mode.slice(1) + 'Container';
            const container = document.getElementById(containerId);
            if (container) {
                if (mode === tabId) {
                    container.style.display = 'block';
                    container.classList.remove('hidden');
                } else {
                    container.style.display = 'none';
                    container.classList.add('hidden');
                }
            }
        });
        
        // Resize chart to ensure correct width
        setTimeout(() => {
            if (tabId === 'amount' && summaryAmountChart) summaryAmountChart.resize();
            if (tabId === 'net' && summaryNetChart) summaryNetChart.resize();
            if (tabId === 'abs' && summaryAbsChart) summaryAbsChart.resize();
        }, 50);
    }

    function switchAccuracyTab(tabId) {
        accuracyActiveTab = tabId;
        
        // Tab buttons styling
        ['net', 'abs'].forEach(mode => {
            const btn = document.getElementById(`accuracyTabBtn-${mode}`);
            if (btn) {
                if (mode === tabId) {
                    btn.className = 'px-2 py-1 rounded-xs text-[9px] font-medium uppercase tracking-wider transition-all whitespace-nowrap bg-white dark:bg-gray-600 text-slate-800 dark:text-white shadow-sm';
                } else {
                    btn.className = 'px-2 py-1 rounded-xs text-[9px] font-medium uppercase tracking-wider transition-all whitespace-nowrap bg-transparent text-slate-400 hover:text-slate-650 hover:bg-white/50 dark:hover:text-slate-200 dark:hover:bg-white/5';
                }
            }
        });
        
        // Wrapper containers visibility
        ['net', 'abs'].forEach(mode => {
            const containerId = 'accuracy' + mode.charAt(0).toUpperCase() + mode.slice(1) + 'Container';
            const container = document.getElementById(containerId);
            if (container) {
                if (mode === tabId) {
                    container.style.display = 'block';
                    container.classList.remove('hidden');
                } else {
                    container.style.display = 'none';
                    container.classList.add('hidden');
                }
            }
        });
        
        // Resize chart to ensure correct width
        setTimeout(() => {
            if (tabId === 'net' && accuracyNetChart) accuracyNetChart.resize();
            if (tabId === 'abs' && accuracyAbsChart) accuracyAbsChart.resize();
        }, 50);
    }

    // --- Dropdown Selector Listener ---
    function changeStoEvent(hashId) {
        if (!hashId) return;
        loadedEventId = hashId;

        // Sync Select2 value if programmatically triggered and differs
        const scopeVal = $('#filterScope').val();
        const targetSelectorId = scopeVal === 'range' ? '#rangeSelector' : '#eventSelector';
        const $targetSelector = $(targetSelectorId);
        if ($targetSelector.length && $targetSelector.val() !== hashId) {
            $targetSelector.val(hashId).trigger('change.select2');
        }

        // Find selected option attributes securely
        const $option = $targetSelector.find(`option[value="${hashId}"]`);
        if (!$option.length) return;

        const eventCode = $option.attr('data-code');
        const periodStr = $option.attr('data-period');
        
        // Update Pareto badge
        const badge = document.getElementById('tab1-event-badge');
        if (badge) badge.innerText = eventCode;

        fetch("{{ url('inventory/sto') }}/" + hashId + "/pareto-by-model")
            .then(res => res.json())
            .then(data => {
                const rawBreakdown = data.reason_breakdown || [];
                
                // Aggregate deviation value by part_no to eliminate duplicate bars in Pareto chart
                const aggregatedMap = {};
                rawBreakdown.forEach(item => {
                    const partNo = item.part_no || 'Unknown';
                    const revCode = item.revision_code || '';
                    const partKey = revCode ? `${partNo} - ${revCode}` : partNo;
                    if (!aggregatedMap[partKey]) {
                        aggregatedMap[partKey] = {
                            part_no: partKey,
                            abs_amount: 0
                        };
                    }
                    aggregatedMap[partKey].abs_amount += parseFloat(item.abs_amount) || 0;
                });
                
                reasonBreakdownData = Object.values(aggregatedMap);
                paretoPage = 1;
                lastStoDashboardData = data;

                renderParetoDashboard(data);
                renderCustomerAccuracyCharts(data);
                fetchAndRenderCorrectionLog(hashId);
            });
    }

    // --- TAB 1 (PARETO ANALYSIS) RENDERING ---

    function renderParetoDashboard(data) {
        if (paretoChart) {
            try { paretoChart.destroy(); } catch(e) {}
            paretoChart = null;
        }
        let pCanvas = document.getElementById('paretoModelChart');
        if (pCanvas) {
            let newPCanvas = document.createElement('canvas');
            newPCanvas.id = 'paretoModelChart';
            newPCanvas.className = 'absolute inset-0 w-full h-full';
            pCanvas.parentNode.replaceChild(newPCanvas, pCanvas);
        }
        const ctx = document.getElementById('paretoModelChart').getContext('2d');

        // Sort reasoning parts by absolute deviation descending
        const sortedBreakdown = [...reasonBreakdownData].sort((a, b) => b.abs_amount - a.abs_amount);
        const totalAbsSum = sortedBreakdown.reduce((sum, item) => sum + item.abs_amount, 0);

        // Precompute absolute cumulative percentages
        let runningSum = 0;
        const allCumPcts = sortedBreakdown.map(item => {
            runningSum += item.abs_amount;
            return totalAbsSum > 0 ? (runningSum / totalAbsSum) * 100 : 0;
        });

        // Sliced items for the current page
        const startIdx = (paretoPage - 1) * itemsPerPage;
        const slicedParts = sortedBreakdown.slice(startIdx, startIdx + itemsPerPage);
        const slicedCumPcts = allCumPcts.slice(startIdx, startIdx + itemsPerPage);

        const totalPages = Math.ceil(sortedBreakdown.length / itemsPerPage) || 1;
        document.getElementById('paretoPageIndicator').innerText = `${paretoPage}/${totalPages}`;
        document.getElementById('btnParetoPrev').disabled = (paretoPage === 1);
        document.getElementById('btnParetoNext').disabled = (paretoPage === totalPages);

        const labels = slicedParts.map(item => item.part_no);
        const deviationValues = slicedParts.map(item => item.abs_amount);

        paretoChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Absolute Deviation',
                        data: deviationValues,
                        backgroundColor: '#6366f1',
                        barPercentage: 0.8,
                        categoryPercentage: 0.9,
                        yAxisID: 'y',
                        yellowLabels: true,
                        yellowLabelFormat: v => formatSuffix(v),
                        order: 2,
                        pointStyle: 'rect'
                    },
                    {
                        label: 'Cumulative %',
                        data: slicedCumPcts,
                        type: 'line',
                        borderColor: '#f59e0b',
                        borderWidth: 1.5,
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 1,
                        pointRadius: 3,
                        fill: false,
                        tension: 0.15,
                        yAxisID: 'y1',
                        yellowLabels: true,
                        yellowLabelFormat: v => v.toFixed(0) + '%',
                        order: 1,
                        pointStyle: 'circle'
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
                            usePointStyle: true,
                            boxWidth: 12,
                            font: { size: 10.5, weight: 'normal' }
                        }
                    },
                    tooltip: {
                        titleFont: { size: 11, weight: 'bold' },
                        bodyFont: { size: 10 },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.datasetIndex === 0) {
                                    label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                } else {
                                    label += context.parsed.y.toFixed(1) + '%';
                                }
                                return label;
                            }
                        }
                    },
                    yellowDataLabels: { precision: 0 }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        position: 'left',
                        grace: '30%',
                        grid: { color: 'rgba(156, 163, 175, 0.12)', borderDash: [3, 3] },
                        ticks: {
                            font: { size: 10, weight: 'normal' },
                            maxTicksLimit: 5,
                            callback: v => formatSuffix(v)
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        max: 110,
                        grid: { drawOnChartArea: false },
                        ticks: {
                            font: { size: 10, weight: 'normal' },
                            maxTicksLimit: 5,
                            callback: v => v.toFixed(0) + '%'
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 9.5, weight: 'normal' },
                            maxRotation: 45,
                            minRotation: 0,
                            autoSkip: false
                        }
                    }
                }
            }
        });

        // 2. Render Problem Breakdown Frequency Chart (Bottom Right Panel)
        const distribution = data.reason_distribution || [];
        if (problemBreakdownChart) {
            try { problemBreakdownChart.destroy(); } catch(e) {}
            problemBreakdownChart = null;
        }
        let pbCanvas = document.getElementById('problemBreakdownChart');
        if (pbCanvas) {
            let newPbCanvas = document.createElement('canvas');
            newPbCanvas.id = 'problemBreakdownChart';
            newPbCanvas.className = 'absolute inset-0 w-full h-full';
            pbCanvas.parentNode.replaceChild(newPbCanvas, pbCanvas);
        }
        const pbCtx = document.getElementById('problemBreakdownChart').getContext('2d');

        // Create reason list with count (Filtered to only include items with volume > 0)
        const reasonList = stoReasons.map(item => {
            const found = distribution.find(d => d.reason_name === item.name || d.reason_name.toUpperCase() === item.name.toUpperCase());
            const count = found ? (parseInt(found.count, 10) || 0) : 0;
            return { name: item.name, count: count };
        }).filter(item => item.count > 0);

        // Sort descending by count
        reasonList.sort((a, b) => b.count - a.count);

        const totalCount = reasonList.reduce((sum, item) => sum + item.count, 0);
        let runningSumPB = 0;
        const cumulativePcts = reasonList.map(item => {
            runningSumPB += item.count;
            return totalCount > 0 ? (runningSumPB / totalCount) * 100 : 0;
        });

        const labelsPB = reasonList.map(item => item.name);
        const dataPB = reasonList.map(item => item.count);

        const maxValPB = Math.max(...dataPB) || 0;
        const suggestedMaxPB = maxValPB > 0 ? Math.ceil(maxValPB * 1.4) : 10;
        
        problemBreakdownChart = new Chart(pbCtx, {
            type: 'bar',
            data: {
                labels: labelsPB,
                datasets: [
                    {
                        label: 'Problem Volume',
                        data: dataPB,
                        backgroundColor: '#6366f1',
                        barPercentage: 0.8,
                        categoryPercentage: 0.9,
                        yAxisID: 'y',
                        yellowLabels: true,
                        yellowLabelFormat: v => new Intl.NumberFormat('id-ID').format(v),
                        order: 2,
                        pointStyle: 'rect'
                    },
                    {
                        label: 'Cumulative %',
                        data: cumulativePcts,
                        type: 'line',
                        borderColor: '#f59e0b',
                        borderWidth: 1.5,
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 1,
                        pointRadius: 4,
                        fill: false,
                        tension: 0.15,
                        yAxisID: 'y1',
                        yellowLabels: true, // triggers custom yellow label tags
                        yellowLabelFormat: v => v.toFixed(0) + '%',
                        order: 1,
                        pointStyle: 'circle'
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
                    legend: { display: false },
                    tooltip: {
                        titleFont: { size: 11, weight: 'bold' },
                        bodyFont: { size: 10 },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.datasetIndex === 0) {
                                    label += context.parsed.y;
                                } else {
                                    label += context.parsed.y.toFixed(1) + '%';
                                }
                                return label;
                            }
                        }
                    },
                    yellowDataLabels: { precision: 0 }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: suggestedMaxPB,
                        grid: { color: 'rgba(156, 163, 175, 0.1)', borderDash: [2, 2] },
                        ticks: { font: { size: 10, weight: 'normal' }, maxTicksLimit: 5 }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        max: 110,
                        grid: { drawOnChartArea: false },
                        ticks: {
                            font: { size: 10, weight: 'normal' },
                            maxTicksLimit: 5,
                            callback: v => v.toFixed(0) + '%'
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 9, weight: 'normal' },
                            maxRotation: 30,
                            minRotation: 0,
                            callback: function(value) {
                                const label = this.getLabelForValue(value);
                                if (typeof label === 'string' && label.length > 12) {
                                    return label.substring(0, 12) + '...';
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    function prevParetoPage() {
        if (paretoPage > 1) {
            paretoPage--;
            renderParetoDashboard(lastStoDashboardData);
        }
    }

    function nextParetoPage() {
        const sortedBreakdown = [...reasonBreakdownData].sort((a, b) => b.abs_amount - a.abs_amount);
        const totalPages = Math.ceil(sortedBreakdown.length / itemsPerPage) || 1;
        if (paretoPage < totalPages) {
            paretoPage++;
            renderParetoDashboard(lastStoDashboardData);
        }
    }

    // --- TAB 2 (CUSTOMER ACCURACY) RENDERING ---

    function renderCustomerAccuracyCharts(data) {
        const paretoData = data.pareto || [];
        
        // Sort for NET Chart: descending by system_amount
        const sortedNet = [...paretoData].sort((a, b) => b.system_amount - a.system_amount);
        const labelsNet = sortedNet.map(d => [d.model_name, d.customer_code]);
        const amountCO_Net = sortedNet.map(d => d.system_amount);
        const amountSTO_Net = sortedNet.map(d => d.real_amount);
        const sumNET_Net = sortedNet.map(d => d.net_amount);

        // Sort for ABS Chart: descending by abs_amount
        const sortedAbs = [...paretoData].sort((a, b) => b.abs_amount - a.abs_amount);
        const labelsAbs = sortedAbs.map(d => [d.model_name, d.customer_code]);
        const amountCO_Abs = sortedAbs.map(d => d.system_amount);
        const amountSTO_Abs = sortedAbs.map(d => d.real_amount);
        const sumABS_Abs = sortedAbs.map(d => d.abs_amount);

        const commonScales = {
            y: {
                beginAtZero: true,
                position: 'left',
                grace: '35%',
                grid: { color: 'rgba(156, 163, 175, 0.1)', borderDash: [2, 2] },
                ticks: {
                    font: { size: 10, weight: 'normal' },
                    maxTicksLimit: 5,
                    callback: v => formatSuffix(v)
                }
            },
            y1: {
                beginAtZero: true,
                position: 'right',
                grace: '35%',
                grid: { drawOnChartArea: false },
                ticks: {
                    font: { size: 10, weight: 'normal' },
                    maxTicksLimit: 5,
                    callback: v => formatSuffix(v)
                }
            },
            x: {
                grid: { display: false },
                ticks: { font: { size: 9.5, weight: 'normal' } }
            }
        };

        const commonPluginOptions = {
            legend: {
                position: 'bottom',
                labels: { boxWidth: 12, font: { size: 10.5, weight: 'normal' } }
            },
            tooltip: {
                titleFont: { size: 11, weight: 'bold' },
                bodyFont: { size: 10 },
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                        return label;
                    }
                }
            },
            yellowDataLabels: { precision: 0, zeroText: '-' }
        };

        // Render NET Chart (Left)
        if (accuracyNetChart) {
            try { accuracyNetChart.destroy(); } catch(e) {}
            accuracyNetChart = null;
        }
        let netCanvas = document.getElementById('accuracyNetChart');
        if (netCanvas) {
            let newNetCanvas = document.createElement('canvas');
            newNetCanvas.id = 'accuracyNetChart';
            newNetCanvas.className = 'w-full h-full';
            netCanvas.parentNode.replaceChild(newNetCanvas, netCanvas);
        }
        const netCtx = document.getElementById('accuracyNetChart').getContext('2d');
        accuracyNetChart = new Chart(netCtx, {
            type: 'bar',
            data: {
                labels: labelsNet,
                datasets: [
                    {
                        label: 'System Amount (CO)',
                        data: amountCO_Net,
                        backgroundColor: '#6366f1',
                        barPercentage: 0.8,
                        categoryPercentage: 0.9,
                        yAxisID: 'y',
                        yellowLabels: true,
                        yellowLabelFormat: v => formatSuffix(v),
                        order: 2,
                        pointStyle: 'rect'
                    },
                    {
                        label: 'Physical Amount (STO)',
                        data: amountSTO_Net,
                        backgroundColor: '#10b981',
                        barPercentage: 0.8,
                        categoryPercentage: 0.9,
                        yAxisID: 'y',
                        yellowLabels: true,
                        yellowLabelFormat: v => formatSuffix(v),
                        order: 2,
                        pointStyle: 'rect'
                    },
                    {
                        label: 'Net Deviation',
                        data: sumNET_Net,
                        type: 'line',
                        borderColor: '#ef4444',
                        borderWidth: 1.5,
                        pointBackgroundColor: '#ef4444',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 1,
                        pointRadius: 4,
                        fill: false,
                        tension: 0.15,
                        yellowLabels: true,
                        yellowLabelFormat: v => formatSuffix(v),
                        yAxisID: 'y1',
                        order: 1,
                        pointStyle: 'circle'
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
                    ...commonPluginOptions,
                    legend: {
                        ...commonPluginOptions.legend,
                        labels: {
                            ...commonPluginOptions.legend.labels,
                            usePointStyle: true
                        }
                    }
                },
                scales: commonScales
            }
        });

        // Render ABS Chart (Right)
        if (accuracyAbsChart) {
            try { accuracyAbsChart.destroy(); } catch(e) {}
            accuracyAbsChart = null;
        }
        let absCanvas = document.getElementById('accuracyAbsChart');
        if (absCanvas) {
            let newAbsCanvas = document.createElement('canvas');
            newAbsCanvas.id = 'accuracyAbsChart';
            newAbsCanvas.className = 'w-full h-full';
            absCanvas.parentNode.replaceChild(newAbsCanvas, absCanvas);
        }
        const absCtx = document.getElementById('accuracyAbsChart').getContext('2d');
        accuracyAbsChart = new Chart(absCtx, {
            type: 'bar',
            data: {
                labels: labelsAbs,
                datasets: [
                    {
                        label: 'System Amount (CO)',
                        data: amountCO_Abs,
                        backgroundColor: '#6366f1',
                        barPercentage: 0.8,
                        categoryPercentage: 0.9,
                        yAxisID: 'y',
                        yellowLabels: true,
                        yellowLabelFormat: v => formatSuffix(v),
                        order: 2,
                        pointStyle: 'rect'
                    },
                    {
                        label: 'Physical Amount (STO)',
                        data: amountSTO_Abs,
                        backgroundColor: '#10b981',
                        barPercentage: 0.8,
                        categoryPercentage: 0.9,
                        yAxisID: 'y',
                        yellowLabels: true,
                        yellowLabelFormat: v => formatSuffix(v),
                        order: 2,
                        pointStyle: 'rect'
                    },
                    {
                        label: 'Absolute Deviation',
                        data: sumABS_Abs,
                        type: 'line',
                        borderColor: '#ef4444',
                        borderWidth: 1.5,
                        pointBackgroundColor: '#ef4444',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 1,
                        pointRadius: 4,
                        fill: false,
                        tension: 0.15,
                        yellowLabels: true,
                        yellowLabelFormat: v => formatSuffix(v),
                        yAxisID: 'y1',
                        order: 1,
                        pointStyle: 'circle'
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
                plugins: commonPluginOptions,
                scales: commonScales
            }
        });

        // Force correct container visibility after recreating charts
        switchAccuracyTab(accuracyActiveTab);
    }

    // --- TAB 3 (SUMMARY STO TRENDS) RENDERING ---

    function initSummaryTrendCharts() {
        const summaryCycles = [...recentEvents].slice(-3); // July 25, Aug 25, Dec 25
        
        const periods = summaryCycles.map(c => c.period);
        const amounts = summaryCycles.map(c => c.total_amount);
        const netPcts = summaryCycles.map(c => c.net_pct);
        const absPcts = summaryCycles.map(c => c.abs_pct);

        // 1. Amount IDR Chart
        const amtCtx = document.getElementById('summaryAmountChart').getContext('2d');
        if (summaryAmountChart) summaryAmountChart.destroy();
        summaryAmountChart = new Chart(amtCtx, {
            type: 'bar',
            data: {
                labels: periods,
                datasets: [{
                    label: 'Amount IDR',
                    data: amounts,
                    backgroundColor: '#10b981',
                    barPercentage: 0.35,
                    categoryPercentage: 0.6,
                    yellowLabels: true,
                    yellowLabelFormat: v => formatSuffix(v),
                    pointStyle: 'rect'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        titleFont: { size: 11, weight: 'bold' },
                        bodyFont: { size: 10 },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                return label;
                            }
                        }
                    },
                    yellowDataLabels: { precision: 0 }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grace: '35%',
                        ticks: {
                            font: { size: 10, weight: 'normal' },
                            maxTicksLimit: 5,
                            callback: v => formatSuffix(v)
                        },
                        grid: { color: 'rgba(156, 163, 175, 0.1)', borderDash: [2, 2] }
                    },
                    x: { grid: { display: false }, ticks: { font: { size: 9.5, weight: 'normal' } } }
                }
            }
        });

        // 2. Net % Chart (Target Dashed Lines +/- 1%)
        const netCtx = document.getElementById('summaryNetChart').getContext('2d');
        if (summaryNetChart) summaryNetChart.destroy();
        summaryNetChart = new Chart(netCtx, {
            type: 'bar',
            data: {
                labels: periods,
                datasets: [{
                    label: 'Net %',
                    data: netPcts,
                    backgroundColor: '#f59e0b',
                    barPercentage: 0.35,
                    categoryPercentage: 0.6,
                    yellowLabels: true,
                    yellowLabelFormat: v => (v < 0 ? '-' : '') + Math.abs(v).toFixed(2) + '%',
                    pointStyle: 'rect'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        titleFont: { size: 11, weight: 'bold' },
                        bodyFont: { size: 10 }
                    },
                    yellowDataLabels: { precision: 2 },
                    targetLines: [
                        { value: 1.0, color: '#ef4444', dash: [3, 3], label: '1%' },
                        { value: -1.0, color: '#ef4444', dash: [3, 3], label: '-1%' }
                    ]
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grace: '35%',
                        ticks: { font: { size: 10, weight: 'normal' }, maxTicksLimit: 5, callback: v => v.toFixed(0) + '%' },
                        grid: { color: 'rgba(156, 163, 175, 0.1)', borderDash: [2, 2] }
                    },
                    x: { grid: { display: false }, ticks: { font: { size: 9.5, weight: 'normal' } } }
                }
            }
        });

        // 3. ABS % Chart (Target Dashed Line 4%)
        const absCtx = document.getElementById('summaryAbsChart').getContext('2d');
        if (summaryAbsChart) summaryAbsChart.destroy();
        summaryAbsChart = new Chart(absCtx, {
            type: 'bar',
            data: {
                labels: periods,
                datasets: [{
                    label: 'ABS %',
                    data: absPcts,
                    backgroundColor: '#6366f1',
                    barPercentage: 0.35,
                    categoryPercentage: 0.6,
                    yellowLabels: true,
                    yellowLabelFormat: v => (v < 0 ? '-' : '') + Math.abs(v).toFixed(2) + '%',
                    pointStyle: 'rect'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        titleFont: { size: 11, weight: 'bold' },
                        bodyFont: { size: 10 }
                    },
                    yellowDataLabels: { precision: 2 },
                    targetLines: [
                        { value: 4.0, color: '#ef4444', dash: [3, 3], label: '4%' }
                    ]
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grace: '35%',
                        ticks: { font: { size: 10, weight: 'normal' }, maxTicksLimit: 5, callback: v => v.toFixed(0) + '%' },
                        grid: { color: 'rgba(156, 163, 175, 0.1)', borderDash: [2, 2] }
                    },
                    x: { grid: { display: false }, ticks: { font: { size: 9.5, weight: 'normal' } } }
                }
            }
        });

        // Force correct container visibility on initialization
        switchSummaryTab(summaryActiveTab);
    }

    // --- CORRECTION AUDIT LOG MODAL HANDLERS ---

    const escapeSingleQuotes = (str) => {
        return str.replace(/'/g, "\\'");
    };

    function fetchAndRenderCorrectionLog(hashId) {
        if (!correctionTable) return;
        
        fetch("{{ url('inventory/sto/dashboard/correction-log') }}?event_id=" + hashId)
            .then(res => res.json())
            .then(res => {
                const data = res.data || [];
                
                // Clear the DataTable
                correctionTable.clear();
                
                data.forEach(model => {
                    // Format adjustment value
                    const absValFormatted = new Intl.NumberFormat('id-ID').format(model.total_correction);
                    
                    // Format increment and decrement pcs
                    // Format increment and decrement pcs
                    const incVal = parseFloat(model.increment_pcs) || 0;
                    const decVal = parseFloat(model.decrement_pcs) || 0;
                    const incPcs = new Intl.NumberFormat('id-ID').format(incVal);
                    const decPcs = new Intl.NumberFormat('id-ID').format(decVal);
                    
                    const incColor = incVal > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400 dark:text-slate-500';
                    const decColor = decVal > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400 dark:text-slate-500';
                    const incSign = incVal > 0 ? '+' : '';
                    const decSign = decVal > 0 ? '-' : '';

                    // Format net correction impact
                    let netImpactHtml = '';
                    const netCorrVal = parseFloat(model.net_correction) || 0;
                    const absNet = Math.abs(netCorrVal);
                    const formattedNet = new Intl.NumberFormat('id-ID').format(absNet);
                    if (netCorrVal < 0) {
                        netImpactHtml = `<span class="text-rose-600 dark:text-rose-400">-Rp ${formattedNet}</span>`;
                    } else if (netCorrVal > 0) {
                        netImpactHtml = `<span class="text-rose-600 dark:text-rose-400">+Rp ${formattedNet}</span>`;
                    } else {
                        netImpactHtml = `<span class="text-slate-400 dark:text-slate-500">Rp 0</span>`;
                    }
                    
                    const rowNode = correctionTable.row.add([
                        `<span class="font-normal text-gray-900 dark:text-white uppercase tracking-tight">${model.model_name}</span>`,
                        `<span class="inline-flex items-center justify-center font-medium text-[9px] px-2 py-0.5 rounded-xs bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 uppercase">${model.customer_code || 'Unknown'}</span>`,
                        `<span class="inline-flex items-center justify-center font-medium text-[9px] px-2 py-0.5 rounded-xs bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 uppercase">${model.event_count} events</span>`,
                        `<span class="inline-flex items-center justify-center font-medium text-[9px] px-2 py-0.5 rounded-xs bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 uppercase">${model.affected_parts} parts</span>`,
                        `<div class="font-mono font-medium text-xs text-gray-900 dark:text-white"><span class="text-[9.5px] font-normal text-gray-400 dark:text-gray-555 mr-0.5">Rp</span>${absValFormatted}</div>`,
                        `<div class="flex flex-col items-center gap-0.5 font-mono font-medium text-xs"><span class="${incColor}">${incSign}${incPcs} Pcs</span><span class="${decColor}">${decSign}${decPcs} Pcs</span></div>`,
                        netImpactHtml,
                        `<button onclick="showCorrectionDetail('${escapeSingleQuotes(model.model_name)}')" title="Explore detailed correction logs" class="h-7 px-3 inline-flex items-center justify-center bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-[9px] font-medium text-gray-600 dark:text-gray-400 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">View Logs</button>`
                    ]).node();
                    
                    $(rowNode).addClass('hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors duration-150');
                });
                
                correctionTable.draw();
            });
    }
    
    function openGlobalAuditLogsModal() {
        const modal = document.getElementById('correctionDetailModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        if ($.fn.DataTable.isDataTable('#correctionLogTable')) {
            $('#correctionLogTable').DataTable().columns.adjust().draw();
        }
    }

    function closeCorrectionModal() {
        const modal = document.getElementById('correctionDetailModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        closeSubModal();
    }

    function showCorrectionDetail(modelName) {
        document.getElementById('modalModelName').innerText = modelName;
        
        if (subTable) {
            subTable.destroy();
            subTable = null;
        }

        const tbody = document.getElementById('correctionDetailBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="py-24 text-center">
                    <div class="flex flex-col items-center justify-center gap-3">
                        <i class="fa-solid fa-spinner animate-spin text-3xl text-primary-500"></i>
                        <span class="text-[10px] font-medium tracking-widest text-slate-400 dark:text-gray-550 uppercase">Fetching Trails...</span>
                    </div>
                </td>
            </tr>
        `;
        document.getElementById('subModalContainer').classList.remove('hidden');

        fetch("{{ url('inventory/sto/dashboard/correction-log') }}/" + encodeURIComponent(modelName) + "?event_id=" + loadedEventId)
            .then(res => res.json())
            .then(data => {
                tbody.innerHTML = '';

                data.detail.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors duration-150';
                    const date = new Date(row.period_end).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'});
                    const diffVal = parseFloat(row.diff_qty) || 0;
                    const diffQtyFormatted = new Intl.NumberFormat('id-ID').format(diffVal);
                    const isNegative = diffVal < 0;
                    const isDeviation = diffVal !== 0;
                    const deviationColorClass = isDeviation ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400 dark:text-slate-500';
                    const sign = isNegative ? '-' : '+';
                    
                    const pcsPerUnit = parseFloat(row.pcs_per_unit) || 1;
                    const grossCoil = parseFloat(row.gross_coil) || 0;
                    const unitCode = row.unit_code || 'PCS';
                    const unitLower = unitCode.toLowerCase();
                    let diffPcs = 0;
                    if (unitLower.includes('coil') && grossCoil > 0) {
                        diffPcs = (diffVal / grossCoil) * pcsPerUnit;
                    } else {
                        diffPcs = diffVal * pcsPerUnit;
                    }
                    const roundedDiffPcs = Math.round(diffPcs);
                    const diffPcsFormatted = new Intl.NumberFormat('id-ID').format(roundedDiffPcs);

                    let qtyAdjHtml = '';
                    if (unitLower === 'pcs') {
                        qtyAdjHtml = `<div class="flex flex-col items-end">
                            <span class="font-normal font-mono ${deviationColorClass}">${diffVal > 0 ? '+' : ''}${diffQtyFormatted} pcs</span>
                        </div>`;
                    } else {
                        qtyAdjHtml = `<div class="flex flex-col items-end">
                            <span class="font-normal font-mono ${deviationColorClass}">${diffVal > 0 ? '+' : ''}${diffQtyFormatted} ${unitCode}</span>
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 font-normal font-mono">(${roundedDiffPcs > 0 ? '+' : ''}${diffPcsFormatted} pcs)</span>
                        </div>`;
                    }

                    tr.innerHTML = `
                        <td class="py-3 px-4">
                            <div class="flex flex-col">
                                <span class="font-medium text-gray-900 dark:text-white uppercase tracking-tight">${row.event_code}</span>
                                <span class="text-[10px] text-gray-500 font-medium">${date}</span>
                            </div>
                        </td>
                        <td class="py-3 px-3">
                            <span class="font-mono text-xs text-gray-900 dark:text-white font-normal">${row.part_no}${row.revision_code ? ` - ${row.revision_code}` : ''}</span>
                        </td>
                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 rounded-xs text-[9px] font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 uppercase tracking-wider">${row.reason_name}</span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            ${qtyAdjHtml}
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-normal text-xs ${deviationColorClass}">
                            ${isDeviation ? sign : ''}Rp ${new Intl.NumberFormat('id-ID').format(Math.abs(row.diff_amount))}
                        </td>
                        <td class="py-3 px-6">
                            ${row.remark ? `
                            <span class="text-xs text-gray-600 dark:text-gray-400 italic font-medium leading-relaxed break-words">
                                "${row.remark}"
                            </span>
                            ` : `
                            <span class="text-xs text-gray-400 dark:text-gray-550 italic">-</span>
                            `}
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                // Initialize DataTable for subTable using the premium helper
                if (window.defaultDataTable) {
                    subTable = window.defaultDataTable('#correctionSubTable', {
                        order: [[0, 'desc']],
                        pageLength: 5,
                        lengthMenu: [5, 10, 25],
                        dom: "<'flex flex-col sm:flex-row justify-between items-center mb-3 gap-2 px-6 pt-3'<'flex items-center gap-3'l B><'w-full sm:w-auto'f>>r<'overflow-y-auto overflow-x-auto flex-1 min-h-0 w-full relative custom-scrollbar't><'flex flex-col md:flex-row justify-between items-center gap-4 text-gray-500 py-3 px-6 border-t border-slate-100 dark:border-gray-700/60'i p>",
                        language: {
                            search: "",
                            searchPlaceholder: "Search within log...",
                            emptyTable: `
                                <div class="py-12 flex flex-col items-center justify-center text-center w-full">
                                    <div>
                                        <i class="fa-solid fa-folder-open text-2xl text-slate-350 dark:text-gray-650 m-3"></i>
                                    </div>
                                    <h4 class="text-[11px] font-bold text-slate-700 dark:text-white uppercase tracking-widest mb-1">No Correction Records</h4>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 max-w-xs mx-auto font-medium">No logs were found for this inventory category.</p>
                                </div>
                            `,
                            zeroRecords: `
                                <div class="py-12 flex flex-col items-center justify-center text-center w-full">
                                    <div>
                                        <i class="fa-solid fa-magnifying-glass text-2xl text-slate-350 dark:text-gray-650 m-3"></i>
                                    </div>
                                    <h4 class="text-[11px] font-bold text-slate-700 dark:text-white uppercase tracking-widest mb-1">No Matching Logs</h4>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 max-w-xs mx-auto font-medium">Try search with a different keyword.</p>
                                </div>
                            `
                        }
                    });
                } else {
                    subTable = $('#correctionSubTable').DataTable({
                        processing: true,
                        serverSide: false,
                        scrollCollapse: true,
                        autoWidth: false,
                        ordering: true,
                        order: [[0, 'desc']],
                        pageLength: 5,
                        lengthMenu: [5, 10, 25],
                        dom: "<'flex flex-col sm:flex-row justify-between items-center mb-3 gap-2 px-6 pt-3'<'flex items-center text-slate-500'l><'text-slate-500'f>><'overflow-y-auto overflow-x-auto flex-1 min-h-0 w-full relative custom-scrollbar't><'flex flex-col md:flex-row justify-between items-center gap-4 text-gray-500 py-3 px-6 border-t border-slate-100 dark:border-gray-700/60'i p>",
                        language: {
                            search: "",
                            searchPlaceholder: "Search within log...",
                            paginate: { 
                                previous: '<i class="fa-solid fa-chevron-left text-[9px]"></i>', 
                                next: '<i class="fa-solid fa-chevron-right text-[9px]"></i>' 
                            }
                        }
                    });
                }
            });
    }

    function closeSubModal() {
        document.getElementById('subModalContainer').classList.add('hidden');
        if (subTable) {
            subTable.destroy();
            subTable = null;
        }
    }
</script>

@endpush
