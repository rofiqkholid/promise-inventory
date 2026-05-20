@extends('layouts.app')

@section('title', 'STO Analytics Dashboard')
@section('page_title', 'STO Analytics')

@section('content')
<div class="dashboard-container w-full h-auto overflow-y-auto lg:h-[calc(100vh-85px)] lg:overflow-hidden flex flex-col gap-2 pb-0 custom-scrollbar select-none">
    {{-- Header, KPIs & Filters --}}
    <div class="flex flex-wrap items-center justify-between gap-y-2 gap-x-4 shrink-0">
        <!-- Title & Subtitle Section -->
        <div class="flex-none">
            <h2 class="text-xl xl:text-2xl font-bold text-gray-800 dark:text-white leading-tight mb-0.5 flex items-center gap-2">
                Stock Opname (STO) Analytics
            </h2>
            <p class="text-[11px] text-slate-500 dark:text-gray-400 leading-tight">Deviation analysis, model concentration, and adjustment logs</p>
        </div>

        <!-- Right Side Filter dropdown & Logs -->
        <div class="flex items-center gap-3 shrink-0">
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
            
            <button onclick="openGlobalAuditLogsModal()" class="h-[38px] px-4 flex items-center gap-1.5 text-xs font-bold text-slate-650 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 shadow-sm transition-all hover:bg-slate-50 dark:hover:bg-gray-700">
                <i class="fa-solid fa-clock-rotate-left"></i> Audit Logs
            </button>
        </div>
    </div>

    {{-- KPI Metrics Row --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-2 shrink-0">
        @foreach([
            ['icon' => 'fa-calendar-check', 'color' => 'slate',   'label' => 'Total Events',   'val' => $stats['total_events'],   'unit' => 'STO cycles'],
            ['icon' => 'fa-lock',           'color' => 'indigo',  'label' => 'Closed Events',  'val' => $stats['closed_events'],  'unit' => 'completed'],
            ['icon' => 'fa-spinner',        'color' => 'emerald', 'label' => 'Open Events',    'val' => $stats['open_events'],    'unit' => 'in progress'],
            ['icon' => 'fa-tag',            'color' => 'amber',   'label' => 'Latest Event',   'val' => $stats['last_event'],     'unit' => ''],
            ['icon' => 'fa-calendar-days',  'color' => 'primary', 'label' => 'Latest Period',  'val' => $stats['last_period'],    'unit' => ''],
        ] as $kpi)
        <div class="bg-white dark:bg-gray-800 rounded-xs border border-gray-200 dark:border-gray-700 shadow-sm px-3 py-2.5 flex items-center gap-3 kpi-card-hover">
            <div class="w-9 h-9 rounded-xs bg-{{ $kpi['color'] }}-50 dark:bg-{{ $kpi['color'] }}-900/20 flex items-center justify-center text-{{ $kpi['color'] }}-600 dark:text-{{ $kpi['color'] }}-400 flex-shrink-0">
                <i class="fa-solid {{ $kpi['icon'] }} text-sm"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider leading-none mb-1 truncate">{{ $kpi['label'] }}</p>
                <p class="text-sm font-extrabold text-slate-800 dark:text-white leading-none truncate">{{ $kpi['val'] }}
                    @if($kpi['unit'])<span class="text-[9px] font-semibold text-slate-400 dark:text-slate-500 ml-1 normal-case">{{ $kpi['unit'] }}</span>@endif
                </p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ROW 1: Top Cards --}}
    <div class="lg:flex-[50] flex flex-col lg:flex-row gap-2 min-h-0">
        <!-- Top-Left Card: Summary Result -->
        <div class="chart-card w-full lg:w-1/2 bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative min-h-0 shadow-sm overflow-hidden chart-card-hover">
            <div class="flex-none flex justify-between items-center mb-1">
                <h3 class="text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center gap-1.5 min-w-0 pr-2">
                    <i class="fa-solid fa-square-poll-vertical mr-1 text-primary-500"></i>
                    <span class="truncate">Summary Result</span>
                    <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">(Target: Net ±1%, Abs 4%)</span>
                </h3>
                <!-- Inner Card Tabs -->
                <div class="flex bg-gray-100 dark:bg-gray-700/80 p-0.5 rounded-xs shrink-0 gap-0.5">
                    <button onclick="switchSummaryTab('amount')" id="summaryTabBtn-amount" class="segmented-tab active-tab">
                        Mio IDR
                    </button>
                    <button onclick="switchSummaryTab('net')" id="summaryTabBtn-net" class="segmented-tab inactive-tab">
                        % Net
                    </button>
                    <button onclick="switchSummaryTab('abs')" id="summaryTabBtn-abs" class="segmented-tab inactive-tab">
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
        <div class="chart-card w-full lg:w-1/2 bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative min-h-0 shadow-sm overflow-hidden chart-card-hover">
            <div class="flex-none flex justify-between items-center mb-1">
                <h3 class="text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center min-w-0 pr-2">
                    <i class="fa-solid fa-scale-unbalanced mr-2 text-primary-500"></i>
                    <span class="truncate">Accuracy based on Cust</span>
                </h3>
                <!-- Inner Card Tabs -->
                <div class="flex bg-gray-100 dark:bg-gray-700/80 p-0.5 rounded-xs shrink-0 gap-0.5">
                    <button onclick="switchAccuracyTab('net')" id="accuracyTabBtn-net" class="segmented-tab active-tab">
                        NET
                    </button>
                    <button onclick="switchAccuracyTab('abs')" id="accuracyTabBtn-abs" class="segmented-tab inactive-tab">
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
        <div class="chart-card w-full lg:w-2/3 bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative min-h-0 shadow-sm overflow-hidden chart-card-hover">
            <div class="flex-none flex justify-between items-center mb-1">
                <h3 class="text-sm lg:text-base font-bold text-gray-800 dark:text-gray-100 flex items-center min-w-0 pr-2">
                    <i class="fa-solid fa-chart-column mr-2 text-primary-500 flex-shrink-0"></i>
                    <span class="truncate">Pareto Deviation by Part</span>
                    <span id="tab1-event-badge" class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-slate-700 text-[8px] font-medium text-slate-500 dark:text-slate-400 border border-slate-200/50 dark:border-slate-600/50 flex-shrink-0 whitespace-nowrap">...</span>
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
        <div class="chart-card w-full lg:w-1/3 bg-white dark:bg-gray-800 p-2 lg:p-2.5 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative min-h-0 shadow-sm overflow-hidden chart-card-hover">
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
</div>
{{-- Global Correction Log Modal (Original Audit Table Interface) --}}
<div id="correctionDetailModal" class="fixed inset-0 z-50 hidden overflow-y-auto animate-fade-in" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 bg-slate-950/60 backdrop-blur-sm">
        <div class="relative bg-white dark:bg-gray-900 rounded-xs max-w-5xl w-full shadow-2xl border border-slate-200 dark:border-gray-800 flex flex-col max-h-[85vh] overflow-hidden transform transition-all animate-fade-in-up">
            <div class="px-5 py-4 border-b border-slate-150 dark:border-gray-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/30">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xs bg-primary-50 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 text-lg shadow-xs">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-tight">
                            Global STO Correction & Audit Log
                        </h3>
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Aggregate models adjustment log summary across cycles</p>
                    </div>
                </div>
                <button onclick="closeCorrectionModal()" class="w-8 h-8 flex items-center justify-center text-slate-450 dark:text-gray-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-gray-800 rounded-xs transition-all outline-none">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>
            
            <div class="overflow-x-auto flex-1 custom-scrollbar">
                <table class="custom-table w-full text-left border-collapse" id="correctionLogTable">
                    <thead class="sticky top-0 bg-slate-50 dark:bg-gray-800 border-b border-slate-200 dark:border-gray-700 z-10">
                        <tr>
                            <th class="py-2.5 px-3 text-left text-[9px] font-black tracking-wider text-slate-500 dark:text-slate-400 uppercase">Model Identification</th>
                            <th class="py-2.5 px-2 text-center text-[9px] font-black tracking-wider text-slate-500 dark:text-slate-400 uppercase w-16">Events</th>
                            <th class="py-2.5 px-2 text-center text-[9px] font-black tracking-wider text-slate-500 dark:text-slate-400 uppercase w-24">Affected Parts</th>
                            <th class="py-2.5 px-3 text-right text-[9px] font-black tracking-wider text-slate-500 dark:text-slate-400 uppercase w-36">ABS Adj. Value</th>
                            <th class="py-2.5 px-2 text-center text-[9px] font-black tracking-wider text-slate-500 dark:text-slate-400 uppercase w-32">Qty Balance (+/-)</th>
                            <th class="py-2.5 px-3 text-right text-[9px] font-black tracking-wider text-slate-500 dark:text-slate-400 uppercase w-32">Net Impact</th>
                            <th class="py-2.5 px-3 text-center text-[9px] font-black tracking-wider text-slate-500 dark:text-slate-400 uppercase w-16">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-gray-800">
                        @foreach($correctionByModel as $model)
                            <tr class="hover:bg-slate-50 dark:hover:bg-gray-800/60 transition-colors group">
                                <td class="py-2.5 px-3">
                                    <div class="text-[11px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-tight">{{ $model['model_name'] }}</div>
                                    <div class="text-[9px] text-slate-400 dark:text-slate-500 font-semibold uppercase mt-0.5">Inventory Category</div>
                                </td>
                                <td class="py-2.5 px-2 text-center">
                                    <span class="inline-flex items-center justify-center font-bold text-[11px] text-indigo-600 dark:text-indigo-400">{{ $model['event_count'] }}</span>
                                </td>
                                <td class="py-2.5 px-2 text-center font-bold text-[11px] text-slate-700 dark:text-slate-300">{{ $model['affected_parts'] }}</td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-[11px] text-slate-800 dark:text-slate-200">
                                    Rp {{ number_format($model['total_correction']) }}
                                </td>
                                <td class="py-2.5 px-2">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <span class="text-[9px] font-black text-emerald-700 bg-emerald-50 dark:bg-emerald-950/30 px-1.5 py-0.5 rounded-xs border border-emerald-200 dark:border-emerald-900/50">+{{ number_format($model['increment_pcs']) }}</span>
                                        <span class="text-[9px] font-black text-rose-700 bg-rose-50 dark:bg-rose-950/30 px-1.5 py-0.5 rounded-xs border border-rose-200 dark:border-rose-900/50">-{{ number_format($model['decrement_pcs']) }}</span>
                                    </div>
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-extrabold text-[11px] {{ $model['net_correction'] < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    {{ $model['net_correction'] < 0 ? '−' : '+' }} Rp {{ number_format(abs($model['net_correction'])) }}
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <button onclick="showCorrectionDetail('{{ $model['model_name'] }}')" title="View detailed log" class="h-7 w-7 inline-flex items-center justify-center rounded-xs bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:border-primary-300 dark:hover:border-primary-700 transition-all shadow-sm group-hover:shadow-md">
                                        <i class="fa-solid fa-magnifying-glass-chart text-[10px]"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Audit Sub Modal --}}
            <div id="subModalContainer" class="hidden absolute inset-0 bg-slate-950/40 backdrop-blur-sm flex items-center justify-center p-6 z-30 animate-fade-in">
                <div class="bg-white dark:bg-gray-900 w-full h-full rounded-xs shadow-2xl flex flex-col overflow-hidden border border-slate-200 dark:border-gray-800 transform transition-all animate-fade-in-up">
                    <div class="px-5 py-4 border-b border-slate-150 dark:border-gray-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/30">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xs bg-primary-50 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400">
                                <i class="fa-solid fa-receipt"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-tight">
                                    Audit Trail Log — <span id="modalModelName" class="text-primary-600 dark:text-primary-400">Model Name</span>
                                </h3>
                                <p class="text-[8px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Detailed correction items for this inventory category</p>
                            </div>
                        </div>
                        <button onclick="closeSubModal()" class="w-8 h-8 flex items-center justify-center text-slate-450 dark:text-gray-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-gray-800 rounded-xs transition-all outline-none">
                            <i class="fa-solid fa-xmark text-base"></i>
                        </button>
                    </div>
                    <div class="overflow-y-auto flex-1 custom-scrollbar text-[10px]">
                        <table class="custom-table w-full text-left border-collapse" id="correctionSubTable">
                            <thead class="sticky top-0 bg-slate-50 dark:bg-gray-850 border-b border-slate-200 dark:border-gray-750 z-10">
                                <tr>
                                    <th class="text-left text-[10px] font-semibold tracking-wider text-slate-550 dark:text-slate-400 uppercase">STO Event</th>
                                    <th class="text-left text-[10px] font-semibold tracking-wider text-slate-550 dark:text-slate-400 uppercase">Part No</th>
                                    <th class="text-left text-[10px] font-semibold tracking-wider text-slate-550 dark:text-slate-400 uppercase">Reason Category</th>
                                    <th class="text-right text-[10px] font-semibold tracking-wider text-slate-550 dark:text-slate-400 uppercase w-24">Quantity Adj.</th>
                                    <th class="text-right text-[10px] font-semibold tracking-wider text-slate-550 dark:text-slate-400 uppercase w-32">Value Impact</th>
                                    <th class="text-left text-[10px] font-semibold tracking-wider text-slate-550 dark:text-slate-400 uppercase">Audit Remark</th>
                                </tr>
                            </thead>
                            <tbody id="correctionDetailBody" class="divide-y divide-slate-100 dark:divide-gray-850 font-medium">
                            </tbody>
                        </table>
                    </div>
                    <div class="px-5 py-3 bg-slate-50 dark:bg-gray-900/50 border-t border-slate-150 dark:border-gray-850 flex justify-end">
                        <button onclick="closeSubModal()" class="px-5 py-1.5 bg-white dark:bg-gray-800 hover:bg-slate-50 dark:hover:bg-gray-700 border border-slate-200 dark:border-gray-600 text-[10px] font-bold text-slate-600 dark:text-gray-300 rounded-xs transition-all shadow-xs outline-none">
                            BACK TO OVERVIEW
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="px-5 py-3 bg-slate-50 dark:bg-gray-900/50 border-t border-slate-150 dark:border-gray-850 flex justify-end">
                <button onclick="closeCorrectionModal()" class="px-5 py-1.5 bg-white dark:bg-gray-800 hover:bg-slate-50 dark:hover:bg-gray-700 border border-slate-200 dark:border-gray-600 text-[10px] font-bold text-slate-600 dark:text-gray-300 rounded-xs transition-all shadow-xs outline-none">
                    CLOSE AUDIT LOG
                </button>
            </div>
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

    // --- Initialization ---
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2 on #eventSelector with premium config
        const $selector = $('#eventSelector');
        if ($selector.length) {
            $selector.select2({
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
    });

    // --- Tab Switchers ---
    function switchSummaryTab(tabId) {
        summaryActiveTab = tabId;
        
        // Tab buttons styling
        ['amount', 'net', 'abs'].forEach(mode => {
            const btn = document.getElementById(`summaryTabBtn-${mode}`);
            if (btn) {
                if (mode === tabId) {
                    btn.className = 'segmented-tab active-tab';
                } else {
                    btn.className = 'segmented-tab inactive-tab';
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
                    btn.className = 'segmented-tab active-tab';
                } else {
                    btn.className = 'segmented-tab inactive-tab';
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
        document.getElementById('correctionDetailModal').classList.remove('hidden');
    }

    function closeCorrectionModal() {
        document.getElementById('correctionDetailModal').classList.add('hidden');
        closeSubModal();
    }

    function showCorrectionDetail(modelName) {
        document.getElementById('modalModelName').innerText = modelName;
        const tbody = document.getElementById('correctionDetailBody');
        tbody.innerHTML = '<tr><td colspan="6" class="p-20 text-center"><i class="fa-solid fa-spinner fa-spin text-2xl text-primary-500"></i></td></tr>';
        document.getElementById('subModalContainer').classList.remove('hidden');

        fetch(`/inventory/sto/dashboard/correction-log/${encodeURIComponent(modelName)}`)
            .then(res => res.json())
            .then(data => {
                tbody.innerHTML = data.detail.length ? '' : '<tr><td colspan="6" class="p-10 text-center italic text-slate-400">No logs found.</td></tr>';
                data.detail.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-50 dark:hover:bg-gray-850 transition-colors';
                    const date = new Date(row.period_end).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'});
                    tr.innerHTML = `
                        <td>
                            <div class="text-[10px] font-bold text-primary-600 dark:text-primary-400 tracking-tight uppercase">${row.event_code}</div>
                            <div class="text-[9px] text-slate-400 dark:text-slate-500 font-semibold uppercase mt-0.5">${date}</div>
                        </td>
                        <td class="font-mono font-bold text-xs text-slate-800 dark:text-slate-200">${row.part_no}</td>
                        <td>
                            <span class="px-1.5 py-0.5 bg-slate-100 dark:bg-gray-800 rounded-xs text-[9px] font-semibold text-slate-650 dark:text-slate-350 border border-slate-200/60 dark:border-gray-700/60 tracking-wider uppercase">
                                ${row.reason_name}
                            </span>
                        </td>
                        <td class="text-right">
                            <span class="font-bold text-xs ${row.diff_qty < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'}">
                                ${row.diff_qty > 0 ? '+' : ''}${new Intl.NumberFormat('id-ID').format(row.diff_qty)}
                            </span>
                        </td>
                        <td class="text-right font-mono font-bold text-xs text-slate-800 dark:text-slate-200">
                            Rp ${new Intl.NumberFormat('id-ID').format(Math.abs(row.diff_amount))}
                        </td>
                        <td class="italic text-slate-500 dark:text-slate-450 font-normal break-words max-w-[200px]">${row.remark || '-'}</td>
                    `;
                    tbody.appendChild(tr);
                });
            });
    }

    function closeSubModal() {
        document.getElementById('subModalContainer').classList.add('hidden');
    }
</script>

<style>
    /* ── Animations ───────────────────────────────────────── */
    .animate-fade-in     { animation: fadeIn    0.25s ease-out forwards; }
    .animate-fade-in-up  { animation: fadeInUp  0.25s ease-out forwards; }
    @keyframes fadeIn    { from { opacity: 0; transform: scale(0.995); } to { opacity: 1; transform: scale(1); } }
    @keyframes fadeInUp  { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

    @keyframes spin-custom { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    .animate-spin, .fa-spin { animation: spin-custom 1s linear infinite !important; display: inline-block !important; }

    /* ── Custom Scrollbar ─────────────────────────────────── */
    .custom-scrollbar::-webkit-scrollbar       { width: 5px; height: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(156,163,175,0.4); border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(156,163,175,0.65); }

    /* ── KPI Card hover lift ──────────────────────────────── */
    .kpi-card-hover {
        transition: box-shadow 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
    }
    .kpi-card-hover:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px -2px rgba(0,0,0,0.08);
        border-color: #bfdbfe;
    }
    .dark .kpi-card-hover:hover { border-color: #3b82f640; }

    /* ── Chart Card hover lift ────────────────────────────── */
    .chart-card-hover {
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .chart-card-hover:hover {
        box-shadow: 0 4px 16px -4px rgba(0,0,0,0.1);
        border-color: #e0e7ff;
    }
    .dark .chart-card-hover:hover { border-color: #4f46e520; }

    /* ── Segmented Tab Controls ───────────────────────────── */
    .segmented-tab {
        padding: 3px 9px;
        border-radius: 0.125rem;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        transition: all 0.15s ease;
        white-space: nowrap;
    }
    .segmented-tab.active-tab {
        background: #ffffff;
        color: #2563eb;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    }
    .dark .segmented-tab.active-tab {
        background: #4b5563;
        color: #93c5fd;
    }
    .segmented-tab.inactive-tab {
        background: transparent;
        color: #94a3b8;
    }
    .segmented-tab.inactive-tab:hover {
        color: #475569;
        background: rgba(255,255,255,0.5);
    }
    .dark .segmented-tab.inactive-tab { color: #94a3b8; }
    .dark .segmented-tab.inactive-tab:hover { color: #e2e8f0; background: rgba(255,255,255,0.06); }
</style>
@endpush
