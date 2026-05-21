@extends('layouts.app')

@section('title', 'STO Analytics Dashboard')
@section('page_title', 'STO Analytics')

@section('content')
<div class="dashboard-container w-full h-auto overflow-y-auto lg:h-[calc(100vh-85px)] lg:overflow-hidden flex flex-col gap-2 pb-0 custom-scrollbar select-none">
    {{-- Header, KPIs & Filters --}}
    <div class="flex flex-wrap items-center justify-between gap-y-2 gap-x-4 shrink-0">
        <!-- Section 1: Title & Subtitle Section -->
        <div class="flex-none">
            <h2 class="text-xl xl:text-2xl font-bold text-gray-800 dark:text-white leading-tight mb-0.5 flex items-center gap-2">
                Stock Opname Analytics
            </h2>
            <p class="text-[11px] text-slate-500 dark:text-gray-400 leading-tight">Deviation analysis, model concentration, and adjustment logs</p>
        </div>

        <!-- Section 2: KPI Cards & Filter Toggle -->
        <div class="flex-1 flex flex-col md:flex-row gap-2 items-stretch lg:justify-end min-w-[100%] xl:min-w-[750px]">
            <!-- KPI Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-2 flex-1">
                @foreach([
                    ['icon' => 'fa-calendar-check', 'color' => 'slate',   'label' => 'Total Events',   'val' => $stats['total_events'],   'unit' => 'STO cycles'],
                    ['icon' => 'fa-lock',           'color' => 'indigo',  'label' => 'Closed Events',  'val' => $stats['closed_events'],  'unit' => 'completed'],
                    ['icon' => 'fa-spinner',        'color' => 'emerald', 'label' => 'Open Events',    'val' => $stats['open_events'],    'unit' => 'in progress'],
                    ['icon' => 'fa-tag',            'color' => 'amber',   'label' => 'Latest Event',   'val' => $stats['last_event'],     'unit' => ''],
                    ['icon' => 'fa-calendar-days',  'color' => 'primary', 'label' => 'Latest Period',  'val' => $stats['last_period'],    'unit' => ''],
                ] as $kpi)
                <div class="bg-white dark:bg-gray-800 rounded-xs border border-gray-200 dark:border-gray-700 px-2.5 py-1.5 flex items-center gap-2.5 h-[52px] transition-all duration-200">
                    <div class="w-8 h-8 rounded-xs bg-{{ $kpi['color'] }}-50 dark:bg-{{ $kpi['color'] }}-900/20 flex items-center justify-center text-{{ $kpi['color'] }}-600 dark:text-{{ $kpi['color'] }}-400 flex-shrink-0">
                        <i class="fa-solid {{ $kpi['icon'] }} text-xs"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider leading-none mb-0.5 truncate">{{ $kpi['label'] }}</p>
                        <p class="text-xs font-extrabold text-slate-800 dark:text-white leading-none truncate">{{ $kpi['val'] }}
                            @if($kpi['unit'])<span class="text-[8px] font-semibold text-slate-400 dark:text-slate-500 ml-0.5 normal-case">{{ $kpi['unit'] }}</span>@endif
                        </p>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Filter Toggle Section & Correction Logs -->
            <div class="shrink-0 flex items-stretch gap-2">
                <button onclick="openGlobalAuditLogsModal()" title="View Global Correction Log" class="px-3 flex items-center justify-center gap-2 text-xs font-bold text-slate-700 hover:text-slate-900 dark:text-gray-300 dark:hover:text-white bg-white hover:bg-slate-50 dark:bg-gray-800 dark:hover:bg-gray-700 border border-slate-200 dark:border-gray-700 rounded-xs transition-all h-[52px] md:h-auto shadow-3xs hover:shadow-2xs">
                    <i class="fa-solid fa-clock-rotate-left text-xs text-slate-400"></i> <span class="hidden xl:inline uppercase tracking-widest text-[9px] font-black">Correction Log</span>
                </button>
                <button id="btnToggleDashFilter" title="Toggle Filters" class="group flex items-center justify-center w-full md:w-[52px] h-[52px] md:h-auto bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xs transition-all">
                    <i class="fa-solid fa-filter text-slate-400 text-sm"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Collapsible Filter Card --}}
    <div id="dashboardFilterCard" class="hidden bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 p-3 shrink-0 mb-1">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <label class="text-[11px] font-bold text-slate-550 dark:text-slate-400 uppercase tracking-widest leading-none whitespace-nowrap">STO Period:</label>
                <div class="w-[240px]">
                    <select id="eventSelector" class="w-full">
                        @foreach($recentEvents as $e)
                            <option value="{{ $e['hash_id'] }}" data-code="{{ $e['code'] }}" data-period="{{ $e['period'] }}">{{ $e['code'] }} ({{ $e['period'] }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ROW 1: Top Cards --}}
    <div class="lg:flex-[50] flex flex-col lg:flex-row gap-2 min-h-0">
        <!-- Top-Left Card: Summary Result -->
        <div class="chart-card w-full lg:w-1/2 bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative min-h-0 overflow-hidden transition-all duration-200">
            <div class="flex-none flex justify-between items-center mb-1">
                <h3 class="text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center gap-1.5 min-w-0 pr-2">
                    <i class="fa-solid fa-square-poll-vertical mr-1 text-primary-500"></i>
                    <span class="truncate">Summary Result</span>
                    <span class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-slate-700 text-[10px] font-medium text-slate-500 dark:text-slate-400 border border-slate-200/50 dark:border-slate-600/50 flex-shrink-0 whitespace-nowrap">(Target: Net ±1%, Abs 4%)</span>
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
            
            <div class="flex-1 flex min-h-0 items-center w-full h-full">
                <!-- Mini Chart Panel (full width) -->
                <div class="w-full h-full relative min-w-0">
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
        <div class="chart-card w-full lg:w-1/2 bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative min-h-0 overflow-hidden transition-all duration-200">
            <div class="flex-none flex justify-between items-center mb-1">
                <h3 class="text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center min-w-0 pr-2">
                    <i class="fa-solid fa-scale-unbalanced mr-2 text-primary-500"></i>
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
            <div class="flex-1 min-h-0 relative mt-1">
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
        <!-- Bottom-Left Card: Pareto Deviation by Part (2/3 width) -->
        <div class="chart-card w-full lg:w-2/3 bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative min-h-0 overflow-hidden transition-all duration-200">
            <div class="flex-none flex justify-between items-center mb-1">
                <h3 class="text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center min-w-0 pr-2">
                    <i class="fa-solid fa-chart-column mr-2 text-primary-500 flex-shrink-0"></i>
                    <span class="truncate">Pareto Deviation by Part</span>
                    <span id="tab1-event-badge" class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-slate-700 text-[10px] font-medium text-slate-500 dark:text-slate-400 border border-slate-200/50 dark:border-slate-600/50 flex-shrink-0 whitespace-nowrap">...</span>
                </h3>
                <div class="flex items-center gap-1 flex-shrink-0 border-l border-gray-200 dark:border-gray-700 pl-2">
                    <button id="btnParetoPrev" onclick="prevParetoPage()" class="w-6 h-6 flex items-center justify-center rounded-xs bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-500 disabled:opacity-30 disabled:cursor-not-allowed transition-colors" title="Previous page">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    </button>
                    <span id="paretoPageIndicator" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 min-w-[24px] text-center">1/1</span>
                    <button id="btnParetoNext" onclick="nextParetoPage()" class="w-6 h-6 flex items-center justify-center rounded-xs bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-500 disabled:opacity-30 disabled:cursor-not-allowed transition-colors" title="Next page">
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </button>
                </div>
            </div>
            <div class="relative w-full flex-1 min-h-0">
                <canvas id="paretoModelChart" class="absolute inset-0 w-full h-full"></canvas>
            </div>
        </div>

        <!-- Bottom-Right Card: Problem Breakdown Frequency (1/3 width) -->
        <div class="chart-card w-full lg:w-1/3 bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative min-h-0 overflow-hidden transition-all duration-200">
            <div class="flex-none flex justify-between items-center mb-1">
                <h3 class="text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center min-w-0 pr-2">
                    <i class="fa-solid fa-triangle-exclamation mr-2 text-primary-500"></i>
                    <span class="truncate">Problem Breakdown Frequency</span>
                </h3>
            </div>
            <div class="relative w-full flex-1 min-h-0">
                <canvas id="problemBreakdownChart" class="absolute inset-0 w-full h-full"></canvas>
            </div>
        </div>
    </div>
</div>{{-- Global Correction Log Modal (Original Audit Table Interface) --}}<div id="correctionDetailModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-slate-900/70 transition-all" role="dialog" aria-modal="true">
    <div class="bg-white dark:bg-gray-800 rounded-xs shadow-xl w-full max-w-[92vw] 2xl:max-w-7xl overflow-hidden border border-slate-200 dark:border-gray-700 flex flex-col h-[80vh] max-h-[85vh] animate-fade-in-up relative">
        <div class="px-6 py-4 bg-slate-50 dark:bg-gray-900 border-b border-slate-200 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xs bg-slate-100 dark:bg-slate-900/45 flex items-center justify-center text-slate-700 dark:text-slate-350 text-lg shadow-xs flex-shrink-0 border border-slate-200 dark:border-slate-800/30">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-3 text-sm uppercase tracking-widest">
                        Global STO Correction Log
                    </h3>
                    <p class="text-[9px] text-slate-450 dark:text-slate-550 font-bold uppercase tracking-wider mt-0.5">Aggregate models adjustment log summary across cycles</p>
                </div>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button onclick="closeCorrectionModal()" class="text-slate-400 hover:text-rose-500 transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
        </div>
        
        <div class="overflow-y-auto flex-1 custom-scrollbar bg-white dark:bg-gray-800">
            <table class="w-full text-left border-collapse" id="correctionLogTable">
                <thead class="sticky top-0 bg-slate-50 dark:bg-gray-900 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-gray-700 z-10">
                    <tr>
                        <th class="py-3 px-6 text-left">Model Identification</th>
                        <th class="py-3 px-3 text-center w-24">Events</th>
                        <th class="py-3 px-3 text-center w-28">Affected Parts</th>
                        <th class="py-3 px-4 text-right w-40">ABS Adj. Value</th>
                        <th class="py-3 px-3 text-center w-40">Qty Balance (+/-)</th>
                        <th class="py-3 px-4 text-right w-40">Net Impact</th>
                        <th class="py-3 px-6 text-center w-32">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-gray-700/60 text-[11px] font-medium text-slate-700 dark:text-slate-350">
                    @foreach($correctionByModel as $model)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-gray-700/30 transition-colors group">
                            <td class="py-3 px-6">
                                <div class="flex items-center gap-3">   
                                    <div>
                                        <div class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-tight">{{ $model['model_name'] }}</div>
                                    </div>
                                </div>
                            </td>                            <td class="py-3 px-3 text-center">
                                <span class="inline-flex items-center justify-center font-medium text-[10px] px-2.5 py-0.5 rounded-full bg-slate-50 dark:bg-slate-900 text-slate-650 dark:text-slate-305 border border-slate-200/60 dark:border-slate-700/60 shadow-3xs">
                                    {{ $model['event_count'] }} <span class="text-[8px] font-medium ml-0.5 text-slate-450 dark:text-slate-500">events</span>
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="inline-flex items-center justify-center font-medium text-[10px] px-2.5 py-0.5 rounded-full bg-slate-50 dark:bg-gray-900 text-slate-600 dark:text-slate-300 border border-slate-200/60 dark:border-slate-700/60 shadow-3xs">
                                    <i class="fa-solid fa-cube text-[8px] mr-1 text-slate-400 dark:text-slate-550"></i>{{ $model['affected_parts'] }} <span class="text-[8px] font-medium ml-0.5 text-slate-450 dark:text-slate-550">parts</span>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="font-mono font-medium text-[11px] text-slate-800 dark:text-slate-200">
                                    <span class="text-[9px] font-semibold text-slate-400 dark:text-slate-500 mr-0.5">Rp</span>{{ number_format($model['total_correction']) }}
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="inline-flex items-center gap-1 text-[9px] font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/20 px-2 py-0.5 rounded border border-emerald-100/60 dark:border-emerald-900/30 shadow-3xs">
                                        <i class="fa-solid fa-arrow-up text-[7px]"></i>{{ number_format($model['increment_pcs']) }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-[9px] font-medium text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/20 px-2 py-0.5 rounded border border-rose-100/60 dark:border-rose-900/30 shadow-3xs">
                                        <i class="fa-solid fa-arrow-down text-[7px]"></i>{{ number_format($model['decrement_pcs']) }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($model['net_correction'] < 0)
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 text-[9px]"><i class="fa-solid fa-caret-down"></i></span>
                                        <span class="font-mono font-medium text-xs text-rose-600 dark:text-rose-400">
                                            -Rp {{ number_format(abs($model['net_correction'])) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 text-[9px]"><i class="fa-solid fa-caret-up"></i></span>
                                        <span class="font-mono font-medium text-xs text-emerald-600 dark:text-emerald-400">
                                            +Rp {{ number_format(abs($model['net_correction'])) }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-6 text-center">
                                <button onclick="showCorrectionDetail('{{ $model['model_name'] }}')" title="Explore detailed correction logs" class="h-8 px-3 inline-flex items-center justify-center gap-1.5 rounded bg-white hover:bg-primary-50 dark:bg-gray-800 dark:hover:bg-primary-950/20 border border-slate-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-800 text-slate-600 hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400 transition-all shadow-xs hover:shadow text-[10px] font-bold uppercase tracking-wider outline-none whitespace-nowrap">
                                    <i class="fa-solid fa-magnifying-glass-chart text-[10px]"></i> View Logs
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    {{-- noModelMatchRow removed to prevent '_DT_CellIndex' jQuery DataTables initialization error --}}
                </tbody>
            </table>
        </div>

        {{-- Audit Sub Modal --}}
        <div id="subModalContainer" class="hidden absolute inset-0 z-30 animate-fade-in bg-white dark:bg-gray-800 flex flex-col">
            <div class="w-full h-full flex flex-col overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 dark:bg-gray-900 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xs bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-slate-650 dark:text-slate-305 text-sm shadow-xs border border-slate-200 dark:border-slate-800">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-3 text-sm uppercase tracking-widest">
                                Correction Trail Log — <span id="modalModelName" class="text-slate-800 dark:text-slate-200 font-extrabold">Model Name</span>
                            </h3>
                            <p class="text-[9px] text-slate-450 dark:text-slate-550 font-bold uppercase tracking-wider mt-0.5">Detailed correction items for this inventory category</p>
                        </div>
                    </div>
                    <button onclick="closeSubModal()" class="text-slate-400 hover:text-rose-500 transition-colors">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <div class="overflow-y-auto flex-1 custom-scrollbar">
                    <table class="w-full text-left border-collapse" id="correctionSubTable">
                        <thead class="sticky top-0 bg-slate-50 dark:bg-gray-900 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-gray-700 z-10">
                            <tr>
                                <th class="py-3 px-6 text-left">STO Event</th>
                                <th class="py-3 px-3 text-left">Part No</th>
                                <th class="py-3 px-3 text-left">Reason Category</th>
                                <th class="py-3 px-4 text-right w-32">Quantity Adj.</th>
                                <th class="py-3 px-4 text-right w-36">Value Impact</th>
                                <th class="py-3 px-6 text-left">Correction Remark</th>
                            </tr>
                        </thead>
                        <tbody id="correctionDetailBody" class="divide-y divide-slate-100 dark:divide-gray-700/60 text-[11px] font-medium text-slate-700 dark:text-slate-350">
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 bg-slate-50 dark:bg-gray-900 border-t border-slate-200 dark:border-gray-700 shrink-0 flex justify-end">
                    <button onclick="closeSubModal()" class="px-5 py-2 bg-white hover:bg-slate-50 border border-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 dark:border-gray-600 text-[10px] font-bold text-slate-650 dark:text-gray-300 rounded-xs transition-all uppercase tracking-widest shadow-xs">
                        BACK TO OVERVIEW
                    </button>
                </div>
            </div>
        </div>
        
        <div class="px-6 py-3 bg-slate-50 dark:bg-gray-900 border-t border-slate-200 dark:border-gray-700 shrink-0 flex justify-end">
            <button onclick="closeCorrectionModal()" class="px-5 py-2 bg-white hover:bg-slate-50 border border-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 dark:border-gray-600 text-[10px] font-bold text-slate-650 dark:text-gray-300 rounded-xs transition-all uppercase tracking-widest shadow-xs">
                CLOSE CORRECTION LOG
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // --- Global Chart.js Defaults (match Outfit layout theme) ---
    (function() {
        const isDark = document.documentElement.classList.contains('dark');
        Chart.defaults.font.family = "'Outfit', sans-serif";
        Chart.defaults.font.size   = 10.5;
        Chart.defaults.color       = isDark ? '#94a3b8' : '#64748b';
        Chart.defaults.borderColor = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.05)';
        Chart.defaults.plugins.legend.labels.padding = 14;
        Chart.defaults.plugins.legend.labels.usePointStyle = false;
    })();

    // --- Custom Chart.js Plugins ---
    
    // Custom plugin to draw yellow numeric value tags on lines/bars
    // Custom plugin to draw yellow numeric value tags on lines/bars
    const yellowDataLabelsPlugin = {
        id: 'yellowDataLabels',
        afterDatasetsDraw(chart, args, options) {
            const { ctx } = chart;
            const opt = options || {};
            
            if (!chart.data || !Array.isArray(chart.data.datasets)) return;
            
            chart.data.datasets.forEach((dataset, datasetIndex) => {
                if (!dataset || !dataset.yellowLabels) return;
                
                const meta = chart.getDatasetMeta(datasetIndex);
                if (!meta || !Array.isArray(meta.data)) return;
                
                meta.data.forEach((element, index) => {
                    if (!element) return;
                    const value = dataset.data[index];
                    if (value === undefined || value === null) return;
                    
                    let text = value;
                    if (typeof value === 'number') {
                        if (dataset.yellowLabelFormat && typeof dataset.yellowLabelFormat === 'function') {
                            text = dataset.yellowLabelFormat(value, index);
                        } else {
                            text = value.toFixed(opt.precision !== undefined ? opt.precision : 2);
                            if (text.endsWith('.00')) text = text.slice(0, -3);
                            if (value < 0) {
                                text = '(' + Math.abs(value).toFixed(opt.precision !== undefined ? opt.precision : 2) + ')';
                                if (text.endsWith('.00)')) text = text.replace('.00)', ')');
                            } else if (value === 0 && opt.zeroText) {
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
                    ctx.font = "bold 10px 'Outfit', sans-serif";
                    const textWidth = ctx.measureText(text).width;
                    const rectWidth = textWidth + 8;
                    const rectHeight = 14;
                    
                    ctx.fillStyle = '#fef08a'; // yellow-200
                    ctx.strokeStyle = '#eab308'; // yellow-500
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
                    
                    ctx.fillStyle = '#854d0e'; // yellow-800
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
                    ctx.fillStyle = '#fef08a';
                    ctx.strokeStyle = '#eab308';
                    ctx.lineWidth = 1;
                    ctx.font = "bold 10px 'Outfit', sans-serif";
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
                    
                    ctx.fillStyle = '#854d0e';
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
    
    // Sub-Tabs State
    let summaryActiveTab = 'amount';
    let accuracyActiveTab = 'net';

    // Pareto Client-Side Pagination
    let paretoPage = 1;
    const itemsPerPage = 15;

    // Chart instances
    let paretoChart = null;
    let problemBreakdownChart = null;
    let accuracyNetChart = null;
    let accuracyAbsChart = null;
    let summaryAmountChart = null;
    let summaryNetChart = null;
    let summaryAbsChart = null;
    let subTable = null;

    // --- Initialization ---
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle Filter Logic
        $('#btnToggleDashFilter').on('click', function(e) {
            e.stopPropagation();
            $('#dashboardFilterCard').slideToggle(200);
            
            // Toggle active styling
            $(this).toggleClass('bg-white dark:bg-gray-800 border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700');
            $(this).toggleClass('bg-slate-100 dark:bg-gray-750 border-slate-350 dark:border-gray-650');
            $(this).find('i').toggleClass('text-slate-400');
            $(this).find('i').toggleClass('text-slate-700 dark:text-slate-200');
        });

        // Initialize Select2 on #eventSelector with premium config
        const $selector = $('#eventSelector');
        if ($selector.length) {
            $selector.select2({
                dropdownParent: $selector.parent(),
                minimumResultsForSearch: -1, // Clean view, no search needed for STO periods
                dropdownAutoWidth: true,
                width: '100%'
            }).on('change', function() {
                changeStoEvent($(this).val());
            });
        }

        // Auto-select and load the first STO event
        const selector = document.getElementById('eventSelector');
        if (selector && selector.options.length > 0) {
            selector.selectedIndex = 0;
            changeStoEvent(selector.value);
        }

        // Initialize all Summary Trend charts
        initSummaryTrendCharts();

        // Switch to default tabs
        switchSummaryTab('amount');
        switchAccuracyTab('net');

        // Initialize DataTable for main Correction Log Table using the premium helper
        let correctionTable;
        if (window.defaultDataTable) {
            correctionTable = window.defaultDataTable('#correctionLogTable', {
                order: [[0, 'asc']], // Alphabetical order by Model Name
                pageLength: 10,
                lengthMenu: [10, 25, 50],
                dom: "<'flex flex-col sm:flex-row justify-between items-center mb-3 gap-2 px-6 pt-3'<'flex items-center gap-3'l B><'w-full sm:w-auto'f>>r<'overflow-x-auto w-full relative't><'flex flex-col md:flex-row justify-between items-center gap-4 text-gray-500 py-3 px-6 border-t border-slate-100 dark:border-gray-700/60'i p>",
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
                dom: "<'flex flex-col sm:flex-row justify-between items-center mb-3 gap-2 px-6 pt-3'<'flex items-center text-slate-500'l><'text-slate-500'f>>t<'flex flex-col md:flex-row justify-between items-center gap-4 text-gray-500 py-3 px-6 border-t border-slate-100 dark:border-gray-700/60'i p>",
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
        const $selector = $('#eventSelector');
        if ($selector.length && $selector.val() !== hashId) {
            $selector.val(hashId).trigger('change.select2');
        }

        // Find selected option attributes securely
        const $option = $selector.find(`option[value="${hashId}"]`);
        if (!$option.length) return;

        const eventCode = $option.attr('data-code');
        const periodStr = $option.attr('data-period');
        
        // Update Pareto badge
        const badge = document.getElementById('tab1-event-badge');
        if (badge) badge.innerText = eventCode;

        fetch(`/inventory/sto/${hashId}/pareto-by-model`)
            .then(res => res.json())
            .then(data => {
                reasonBreakdownData = data.reason_breakdown || [];
                paretoPage = 1;

                renderParetoDashboard(data);
                renderCustomerAccuracyCharts(data);
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
                        barPercentage: 0.45,
                        categoryPercentage: 0.6,
                        yAxisID: 'y',
                        yellowLabels: true,
                        yellowLabelFormat: v => {
                            if (v >= 1000000) return (v / 1000000).toFixed(1) + 'M';
                            if (v >= 1000) return (v / 1000).toFixed(0) + 'K';
                            return v;
                        },
                        order: 2
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
                        order: 1
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
                            boxWidth: 12,
                            font: { size: 10.5, weight: 'bold' }
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
                            font: { size: 10, weight: 'bold' },
                            maxTicksLimit: 5,
                            callback: v => {
                                if (v >= 1000000) return (v / 1000000).toFixed(0) + 'M';
                                if (v >= 1000) return (v / 1000).toFixed(0) + 'K';
                                return v;
                            }
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        max: 110,
                        grid: { drawOnChartArea: false },
                        ticks: {
                            font: { size: 10, weight: 'bold' },
                            maxTicksLimit: 5,
                            callback: v => v.toFixed(0) + '%'
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 9.5, weight: 'bold' },
                            maxRotation: 45,
                            minRotation: 45,
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

        // Dynamic names from database
        const labelsPB = stoReasons.map(item => item.name);
        const dataPB = stoReasons.map(item => {
            const found = distribution.find(d => d.reason_name === item.name || d.reason_name.toUpperCase() === item.name.toUpperCase());
            return found ? found.count : 0;
        });

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
                        barPercentage: 0.45,
                        categoryPercentage: 0.6,
                        order: 2
                    },
                    {
                        label: 'Trend',
                        data: dataPB,
                        type: 'line',
                        borderColor: '#f59e0b',
                        borderWidth: 1.5,
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 1.5,
                        pointRadius: 4,
                        fill: false,
                        tension: 0.15,
                        yellowLabels: true, // triggers custom yellow label tags
                        order: 1
                    }
                ]
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
                    yellowDataLabels: { precision: 0 }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: suggestedMaxPB,
                        grid: { color: 'rgba(156, 163, 175, 0.1)', borderDash: [2, 2] },
                        ticks: { font: { size: 10, weight: 'bold' }, maxTicksLimit: 5 }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 9, weight: 'black' }, maxRotation: 30 }
                    }
                }
            }
        });
    }

    function prevParetoPage() {
        if (paretoPage > 1) {
            paretoPage--;
            const selector = document.getElementById('eventSelector');
            changeStoEvent(selector.value);
        }
    }

    function nextParetoPage() {
        const sortedBreakdown = [...reasonBreakdownData].sort((a, b) => b.abs_amount - a.abs_amount);
        const totalPages = Math.ceil(sortedBreakdown.length / itemsPerPage) || 1;
        if (paretoPage < totalPages) {
            paretoPage++;
            const selector = document.getElementById('eventSelector');
            changeStoEvent(selector.value);
        }
    }

    // --- TAB 2 (CUSTOMER ACCURACY) RENDERING ---

    function renderCustomerAccuracyCharts(data) {
        const paretoData = data.pareto || [];
        const labels = paretoData.map(d => [d.model_name, d.customer_code]);
        
        const amountCO = paretoData.map(d => d.system_amount / 1000000);
        const amountSTO = paretoData.map(d => d.real_amount / 1000000);
        const sumNET = paretoData.map(d => d.net_amount / 1000000);
        const sumABS = paretoData.map(d => d.abs_amount / 1000000);

        const commonScales = {
            y: {
                beginAtZero: true,
                position: 'left',
                grace: '35%',
                grid: { color: 'rgba(156, 163, 175, 0.1)', borderDash: [2, 2] },
                ticks: { font: { size: 10, weight: 'bold' }, maxTicksLimit: 5 },
                title: { display: true, text: 'Millions', font: { size: 9, weight: 'bold' } }
            },
            y1: {
                beginAtZero: true,
                position: 'right',
                grace: '35%',
                grid: { drawOnChartArea: false },
                ticks: { font: { size: 10, weight: 'bold' }, maxTicksLimit: 5 },
                title: { display: true, text: 'Millions', font: { size: 9, weight: 'bold' } }
            },
            x: {
                grid: { display: false },
                ticks: { font: { size: 9.5, weight: 'black' } }
            }
        };

        const commonPluginOptions = {
            legend: {
                position: 'bottom',
                labels: { boxWidth: 12, font: { size: 10.5, weight: 'bold' } }
            },
            tooltip: {
                titleFont: { size: 11, weight: 'bold' },
                bodyFont: { size: 10 }
            },
            yellowDataLabels: { precision: 2, zeroText: '-' }
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
                labels: labels,
                datasets: [
                    {
                        label: 'System Amount (CO)',
                        data: amountCO,
                        backgroundColor: '#6366f1',
                        barPercentage: 0.5,
                        categoryPercentage: 0.7,
                        yAxisID: 'y',
                        order: 2
                    },
                    {
                        label: 'Physical Amount (STO)',
                        data: amountSTO,
                        backgroundColor: '#10b981',
                        barPercentage: 0.5,
                        categoryPercentage: 0.7,
                        yAxisID: 'y',
                        order: 2
                    },
                    {
                        label: 'Net Deviation',
                        data: sumNET,
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
                        yAxisID: 'y1',
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: commonPluginOptions,
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
                labels: labels,
                datasets: [
                    {
                        label: 'Physical Amount (STO)',
                        data: amountSTO,
                        backgroundColor: '#10b981',
                        barPercentage: 0.5,
                        categoryPercentage: 0.7,
                        yAxisID: 'y',
                        order: 2
                    },
                    {
                        label: 'System Amount (CO)',
                        data: amountCO,
                        backgroundColor: '#6366f1',
                        barPercentage: 0.5,
                        categoryPercentage: 0.7,
                        yAxisID: 'y',
                        order: 2
                    },
                    {
                        label: 'Absolute Deviation',
                        data: sumABS,
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
                        yAxisID: 'y1',
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
                    data: amounts.map(a => a / 1000000), // convert to Millions
                    backgroundColor: '#10b981',
                    barPercentage: 0.35,
                    categoryPercentage: 0.6,
                    yellowLabels: true
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
                    yellowDataLabels: { precision: 0 }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grace: '35%',
                        title: { display: true, text: 'Millions', font: { size: 9, weight: 'bold' } },
                        ticks: { font: { size: 10, weight: 'bold' }, maxTicksLimit: 5 },
                        grid: { color: 'rgba(156, 163, 175, 0.1)', borderDash: [2, 2] }
                    },
                    x: { grid: { display: false }, ticks: { font: { size: 9.5, weight: 'black' } } }
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
                    yellowLabels: true
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
                        ticks: { font: { size: 10, weight: 'bold' }, maxTicksLimit: 5, callback: v => v.toFixed(0) + '%' },
                        grid: { color: 'rgba(156, 163, 175, 0.1)', borderDash: [2, 2] }
                    },
                    x: { grid: { display: false }, ticks: { font: { size: 9.5, weight: 'black' } } }
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
                    yellowLabels: true
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
                        ticks: { font: { size: 10, weight: 'bold' }, maxTicksLimit: 5, callback: v => v.toFixed(0) + '%' },
                        grid: { color: 'rgba(156, 163, 175, 0.1)', borderDash: [2, 2] }
                    },
                    x: { grid: { display: false }, ticks: { font: { size: 9.5, weight: 'black' } } }
                }
            }
        });

        // Force correct container visibility on initialization
        switchSummaryTab(summaryActiveTab);
    }

    // --- CORRECTION AUDIT LOG MODAL HANDLERS ---
    
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
        tbody.innerHTML = '<tr><td colspan="6" class="p-20 text-center"><i class="fa-solid fa-spinner fa-spin text-2xl text-primary-500"></i></td></tr>';
        document.getElementById('subModalContainer').classList.remove('hidden');

        fetch(`/inventory/sto/dashboard/correction-log/${encodeURIComponent(modelName)}`)
            .then(res => res.json())
            .then(data => {
                tbody.innerHTML = '';

                data.detail.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-50/80 dark:hover:bg-gray-700/30 transition-colors duration-150 group';
                    const date = new Date(row.period_end).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'});
                    const diffQtyFormatted = new Intl.NumberFormat('id-ID').format(row.diff_qty);
                    const isNegative = row.diff_qty < 0;
                    const amountColorClass = isNegative ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400';
                    const sign = isNegative ? '-' : '+';
                    
                    tr.innerHTML = `
                        <td class="py-3 px-6">
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex items-center justify-center w-2.5 h-2.5 rounded-full ${isNegative ? 'bg-rose-500 animate-pulse' : 'bg-emerald-500'}"></span>
                                <div>
                                    <div class="text-[10px] font-bold text-slate-800 dark:text-white uppercase tracking-tight">${row.event_code}</div>
                                    <div class="text-[9px] text-slate-450 dark:text-slate-550 font-medium uppercase tracking-wider mt-0.5">${date}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-3 font-mono font-medium text-[11px] text-slate-800 dark:text-slate-200">${row.part_no}</td>
                        <td class="py-3 px-3">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-slate-50 dark:bg-gray-900 rounded-sm text-[9px] font-medium text-slate-600 dark:text-slate-350 border border-slate-200/60 dark:border-gray-700/60 uppercase">
                                <i class="fa-solid fa-tag text-[8px] text-slate-400 dark:text-slate-550"></i> ${row.reason_name}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <span class="inline-flex items-center gap-1 text-[9px] font-medium ${isNegative ? 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/20 px-2 py-0.5 rounded border border-rose-100/60 dark:border-rose-900/30 shadow-3xs' : 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/20 px-2 py-0.5 rounded border border-emerald-100/60 dark:border-emerald-900/30 shadow-3xs'}">
                                <i class="fa-solid ${isNegative ? 'fa-arrow-down' : 'fa-arrow-up'} text-[8px]"></i>
                                ${row.diff_qty > 0 ? '+' : ''}${diffQtyFormatted} <span class="text-[8px] font-medium opacity-85">pcs</span>
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-medium text-[11px] ${amountColorClass}">
                            ${sign}<span class="text-[9px] font-semibold text-slate-450 dark:text-slate-550 mr-0.5">Rp</span>${new Intl.NumberFormat('id-ID').format(Math.abs(row.diff_amount))}
                        </td>
                        <td class="py-3 px-6">
                            ${row.remark ? `
                            <div class="relative bg-slate-50 dark:bg-gray-900 border-l-2 border-slate-400 px-2.5 py-1.5 rounded-r-md max-w-[240px]">
                                <p class="text-[10px] text-slate-600 dark:text-slate-350 italic font-medium leading-relaxed break-words pr-2">
                                    "${row.remark}"
                                </p>
                                <i class="fa-solid fa-quote-right absolute right-1.5 bottom-1 text-[8px] text-slate-400/20"></i>
                            </div>
                            ` : `
                            <span class="text-[10px] text-slate-400 italic font-normal">-</span>
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
                        dom: "<'flex flex-col sm:flex-row justify-between items-center mb-3 gap-2 px-6 pt-3'<'flex items-center gap-3'l B><'w-full sm:w-auto'f>>r<'overflow-x-auto w-full relative't><'flex flex-col md:flex-row justify-between items-center gap-4 text-gray-500 py-3 px-6 border-t border-slate-100 dark:border-gray-700/60'i p>",
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
                        dom: "<'flex flex-col sm:flex-row justify-between items-center mb-3 gap-2 px-6 pt-3'<'flex items-center text-slate-500'l><'text-slate-500'f>>t<'flex flex-col md:flex-row justify-between items-center gap-4 text-gray-500 py-3 px-6 border-t border-slate-100 dark:border-gray-700/60'i p>",
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
