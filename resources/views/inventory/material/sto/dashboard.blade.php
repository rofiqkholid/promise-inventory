@extends('layouts.app')

@section('title', 'STO Analytics Dashboard')
@section('page_title', 'STO Analytics')

@section('content')
<div class="dashboard-container w-full h-auto flex flex-col gap-4 pb-10">
    {{-- Header & High-Level KPIs --}}
    <div class="flex flex-wrap items-center justify-between gap-y-3 gap-x-4">
        <div class="flex-none">
            <h2 class="text-xl xl:text-2xl font-bold text-gray-800 dark:text-white leading-tight mb-0.5 flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-primary-500"></i>
                Stock Opname Analytics
            </h2>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-tight italic">Deviation analysis, model concentration (Pareto), and correction logs.</p>
        </div>

        <div class="flex-1 flex flex-col md:flex-row gap-2 items-stretch lg:justify-end min-w-[100%] xl:min-w-[700px]">
            <!-- KPI Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 flex-1">
                @foreach([
                    ['val' => $stats['total_events'], 'label' => 'Total Events', 'unit' => 'Counts', 'icon' => 'fa-calendar-check', 'color' => 'slate', 'id' => 'stat_total_events'],
                    ['val' => $stats['last_event'], 'label' => 'Last Event', 'unit' => 'Code', 'icon' => 'fa-boxes-stacked', 'color' => 'primary', 'id' => 'stat_last_event'],
                    ['val' => '...', 'label' => 'Top Deviation', 'unit' => 'Model', 'icon' => 'fa-triangle-exclamation', 'color' => 'rose', 'id' => 'topDeviationModel'],
                    ['val' => '...', 'label' => 'Adjustments', 'unit' => 'Pcs', 'icon' => 'fa-history', 'color' => 'emerald', 'id' => 'totalCorrectionPcs'],
                ] as $stat)
                <div class="bg-white dark:bg-gray-800 px-3 py-2 rounded-xs border border-gray-200 dark:border-gray-700 flex items-center gap-3 h-[56px] shadow-sm">
                    <div class="w-10 h-10 rounded-xs bg-{{ $stat['color'] }}-50 dark:bg-{{ $stat['color'] }}-900/20 flex items-center justify-center text-{{ $stat['color'] }}-600 dark:text-{{ $stat['color'] }}-400 text-lg shrink-0 transition-transform group-hover:scale-110">
                        <i class="fa-solid {{ $stat['icon'] }}"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 tracking-wider leading-none mb-1.5 uppercase">{{ $stat['label'] }}</p>
                        <h3 class="text-sm font-black text-slate-800 dark:text-slate-100 leading-none tracking-tight truncate" id="{{ $stat['id'] }}">
                            {{ $stat['val'] }}
                        </h3>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Actions -->
            <div class="shrink-0 flex items-stretch gap-2">
                <button id="btnToggleDashFilter" title="Toggle Filters" class="group flex items-center justify-center w-full md:w-[52px] h-[52px] md:h-auto bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xs transition-all hover:bg-slate-50 dark:hover:bg-gray-700 shadow-sm">
                    <i class="fa-solid fa-filter text-slate-400 group-hover:text-primary-500 transition-colors text-sm"></i>
                </button>
                <a href="{{ route('inventory.sto.index') }}" title="Go to Events" class="group flex items-center justify-center w-full md:w-[52px] h-[52px] md:h-auto bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xs transition-all hover:bg-slate-50 dark:hover:bg-gray-700 shadow-sm">
                    <i class="fa-solid fa-list-check text-slate-400 group-hover:text-emerald-500 transition-colors text-sm"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Collapsible Filter Card --}}
    <div id="dashboardFilterCard" class="hidden bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 p-4 shadow-sm animate-fade-in-down">
        <div class="flex flex-col lg:flex-row gap-4 lg:items-end">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 flex-1">
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Analysis Period</label>
                    <select id="analysisPeriod" class="w-full text-xs font-bold border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-900 dark:text-white rounded-xs h-[40px] px-3 focus:ring-1 focus:ring-primary-500 outline-none">
                        <option value="last_6">Last 6 Months</option>
                        <option value="last_12" selected>Last 12 Months</option>
                        <option value="ytd">Year to Date</option>
                        <option value="all">All Records</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2">
                <button type="button" onclick="window.location.reload()" class="h-10 px-6 bg-slate-50 hover:bg-slate-100 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-xs text-xs font-bold text-slate-600 dark:text-gray-300 transition-all border border-slate-200 dark:border-gray-600">
                    Apply Filter
                </button>
            </div>
        </div>
    </div>

    {{-- Main Analytics Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 flex-1">
        <!-- Event Accuracy Trend -->
        <div class="lg:col-span-8 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 flex flex-col shadow-sm">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-chart-line text-emerald-500"></i>
                        STO Performance Trend
                    </h3>
                    <p class="text-[10px] text-gray-400">Net vs Absolute deviation percentage per event period.</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-primary-500"></span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">ABS %</span>
                    </div>
                    <div class="flex items-center gap-2 border-l border-slate-100 dark:border-gray-700 pl-4">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Net %</span>
                    </div>
                </div>
            </div>
            <div class="p-6 flex-1 min-h-[320px]">
                <canvas id="eventTrendChart"></canvas>
            </div>
        </div>

        <!-- Recent Event Selection -->
        <div class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 flex flex-col shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-gray-700 bg-slate-50/50 dark:bg-slate-900/30 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Recent Results</h3>
                <span class="text-[10px] font-bold text-slate-400">{{ count($recentEvents) }} Events</span>
            </div>
            <div class="flex-1 overflow-y-auto max-h-[350px] custom-scrollbar p-2 space-y-1">
                @foreach($recentEvents as $event)
                    <button onclick="loadPareto('{{ $event['hash_id'] }}', '{{ $event['code'] }}')" class="event-item w-full text-left p-3 rounded-xs border border-transparent hover:border-primary-200 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-all group">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[11px] font-black text-slate-700 dark:text-gray-200 group-hover:text-primary-600 transition-colors uppercase">{{ $event['code'] }}</span>
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-xs bg-white dark:bg-gray-700 border border-slate-100 dark:border-gray-600 text-slate-400 uppercase tracking-tighter">{{ $event['period'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex gap-3">
                                <div class="flex flex-col">
                                    <span class="text-[8px] text-slate-400 font-bold uppercase">ABS Dev.</span>
                                    <span class="text-[11px] font-black text-slate-800 dark:text-white">Rp {{ number_format($event['abs_amount']) }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[8px] text-slate-400 font-bold uppercase">Accuracy</span>
                                    <span class="text-[11px] font-black {{ $event['abs_pct'] > 5 ? 'text-rose-500' : 'text-emerald-500' }}">{{ 100 - min(100, $event['abs_pct']) }}%</span>
                                </div>
                            </div>
                            <i class="fa-solid fa-chevron-right text-[10px] text-slate-300 group-hover:text-primary-400 group-hover:translate-x-1 transition-all"></i>
                        </div>
                    </button>
                @endforeach
            </div>
            <div class="p-3 bg-slate-50/50 dark:bg-slate-900/30 border-t border-slate-100 dark:border-gray-700 text-center">
                <p class="text-[10px] text-slate-400 italic">Select an event to drill-down analysis</p>
            </div>
        </div>
    </div>

    {{-- Accuracy by Model Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <!-- Accuracy NET -->
        <div class="bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 shadow-sm flex flex-col">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-scale-unbalanced text-blue-500"></i>
                    Accuracy Based on Customer - NET
                </h3>
            </div>
            <div class="p-6 h-[300px]">
                <canvas id="accuracyNetChart"></canvas>
            </div>
        </div>

        <!-- Accuracy ABS -->
        <div class="bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 shadow-sm flex flex-col">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-scale-unbalanced text-rose-500"></i>
                    Accuracy Based on Customer - ABS
                </h3>
            </div>
            <div class="p-6 h-[300px]">
                <canvas id="accuracyAbsChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Pareto & Reason Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Pareto Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 shadow-sm flex flex-col">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-chart-column text-primary-500"></i>
                        Pareto Deviation by Model
                        <span id="activeEventBadge" class="ml-2 text-[9px] bg-primary-500 text-white px-2 py-0.5 rounded-xs font-black uppercase tracking-tighter shadow-sm">...</span>
                    </h3>
                    <p class="text-[10px] text-gray-400">Models sorted by cumulative absolute deviation value.</p>
                </div>
            </div>
            <div class="p-6 h-[350px]">
                <canvas id="paretoModelChart"></canvas>
            </div>
        </div>

        <!-- Reason Distribution Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 shadow-sm flex flex-col overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-gray-700 bg-slate-50/50 dark:bg-slate-900/30 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Reason Breakdown Analysis</h3>
                <div class="flex items-center gap-1.5">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Grouping:</span>
                    <button class="px-2.5 py-1 rounded-xs bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 text-[9px] font-black text-primary-600 shadow-sm transition-all hover:scale-105 active:scale-95">By Model</button>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto custom-scrollbar" style="max-height: 350px;">
                <table class="w-full text-left border-collapse" id="reasonTable">
                    <thead class="sticky top-0 bg-gray-50/80 dark:bg-gray-900/80 backdrop-blur-sm z-10">
                        <tr>
                            <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-200 dark:border-gray-700 w-10 text-center">No</th>
                            <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-200 dark:border-gray-700">Cust-Model</th>
                            <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-200 dark:border-gray-700">Problem</th>
                            <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-200 dark:border-gray-700">Action Plan</th>
                            <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-200 dark:border-gray-700 text-center w-20">PIC</th>
                            <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-200 dark:border-gray-700 text-center w-24">Due Date</th>
                        </tr>
                    </thead>
                    <tbody class="text-[11px] font-medium text-slate-700 dark:text-gray-300 divide-y divide-slate-50 dark:divide-gray-800">
                        <!-- Loaded via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Correction Log Section --}}
    <div class="bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-gray-700 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/30 dark:bg-slate-900/20">
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-history text-indigo-500"></i>
                    Global Correction Log Summary
                </h3>
                <p class="text-[10px] text-gray-500">Aggregate audit adjustments across all finalized STO cycles.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 rounded-xs border border-emerald-100 dark:border-emerald-800 shadow-inner">
                    <span class="text-[9px] font-bold text-emerald-600 uppercase">Plus:</span>
                    <span id="totalIncPcs" class="text-xs font-black text-emerald-700 dark:text-emerald-400">+0 Pcs</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 bg-rose-50 dark:bg-rose-900/20 rounded-xs border border-rose-100 dark:border-rose-800 shadow-inner">
                    <span class="text-[9px] font-bold text-rose-600 uppercase">Minus:</span>
                    <span id="totalDecPcs" class="text-xs font-black text-rose-700 dark:text-rose-400">-0 Pcs</span>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse" id="correctionLogTable">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-slate-900/50">
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-200 dark:border-gray-700">Model Identification</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-200 dark:border-gray-700">Events</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-200 dark:border-gray-700">Affected Parts</th>
                        <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-200 dark:border-gray-700">ABS Adj. Value</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-200 dark:border-gray-700">Quantity Balance (+/-)</th>
                        <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-200 dark:border-gray-700">Net Impact</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-200 dark:border-gray-700 w-24">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-gray-800 text-[11px]">
                    @foreach($correctionByModel as $model)
                        <tr class="hover:bg-primary-50/30 dark:hover:bg-primary-900/10 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-tighter group-hover:text-primary-600 transition-colors">{{ $model['model_name'] }}</div>
                                <div class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Inventory Category</div>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-slate-600 dark:text-slate-400">{{ $model['event_count'] }}</td>
                            <td class="px-6 py-4 text-center font-bold text-slate-600 dark:text-slate-400">{{ $model['affected_parts'] }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="text-xs font-black text-slate-900 dark:text-white tracking-tight">Rp {{ number_format($model['total_correction']) }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <span class="text-[10px] font-black text-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 px-2 py-0.5 rounded-xs border border-emerald-100 dark:border-emerald-800">+{{ number_format($model['increment_pcs']) }}</span>
                                    <span class="text-[10px] font-black text-rose-500 bg-rose-50 dark:bg-rose-900/20 px-2 py-0.5 rounded-xs border border-rose-100 dark:border-rose-800">-{{ number_format($model['decrement_pcs']) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="text-xs font-black {{ $model['net_correction'] < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ $model['net_correction'] < 0 ? '-' : '+' }} Rp {{ number_format(abs($model['net_correction'])) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button onclick="showCorrectionDetail('{{ $model['model_name'] }}')" class="h-8 px-3 inline-flex items-center gap-2 rounded-xs bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 text-[10px] font-black text-slate-500 hover:text-primary-600 hover:border-primary-200 transition-all shadow-sm">
                                    <i class="fa-solid fa-magnifying-glass-chart"></i>
                                    DETAIL
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal: Correction Detail (Refined) --}}
<div id="correctionDetailModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity" onclick="closeCorrectionModal()"></div>
        
        <div class="relative bg-white dark:bg-gray-800 rounded-xs max-w-5xl w-full shadow-2xl border border-slate-200 dark:border-gray-700 flex flex-col max-h-[85vh] overflow-hidden transform transition-all animate-fade-in-up">
            <div class="px-6 py-5 border-b border-slate-100 dark:border-gray-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-900/50">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xs bg-primary-50 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 text-xl">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tighter">
                            Correction Audit Log — <span id="modalModelName" class="text-primary-600">Model Name</span>
                        </h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Detailed transaction history for selected model</p>
                    </div>
                </div>
                <button onclick="closeCorrectionModal()" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-full transition-all">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <div class="overflow-y-auto custom-scrollbar flex-1">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 bg-gray-50 dark:bg-gray-900 z-10 shadow-sm">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-gray-700">STO Event</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-gray-700">Part No</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-gray-700">Reason Category</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-gray-700">Quantity Adj.</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-gray-700">Value Impact</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-gray-700">Audit Remark</th>
                        </tr>
                    </thead>
                    <tbody id="correctionDetailBody" class="text-[11px] font-bold text-slate-600 dark:text-gray-300 divide-y divide-slate-50 dark:divide-gray-800">
                        <!-- Loaded via JS -->
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-gray-700 flex justify-end">
                <button onclick="closeCorrectionModal()" class="px-6 py-2 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-600 text-[10px] font-black text-slate-600 dark:text-gray-300 rounded-xs hover:bg-slate-50 transition-all shadow-sm">
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
    // --- Dashboard State ---
    let trendChart = null;
    let paretoChart = null;
    let accuracyNetChart = null;
    let accuracyAbsChart = null;
    const statsData = @json($stats);
    const recentEventsData = @json($recentEvents);
    const correctionByModel = @json($correctionByModel);

    // --- Initialization ---
    document.addEventListener('DOMContentLoaded', function() {
        initTrendChart();
        setupFilterToggle();
        
        // Auto-load first event
        const firstEvent = document.querySelector('.event-item');
        if (firstEvent) {
            firstEvent.click();
        }
        
        updateSummaryKPIs();
    });

    function setupFilterToggle() {
        const btn = document.getElementById('btnToggleDashFilter');
        const card = document.getElementById('dashboardFilterCard');
        if (btn && card) {
            btn.addEventListener('click', () => card.classList.toggle('hidden'));
        }
    }

    function initTrendChart() {
        const ctx = document.getElementById('eventTrendChart').getContext('2d');
        
        trendChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: recentEventsData.map(e => e.period),
                datasets: [
                    {
                        label: 'ABS % Deviation',
                        data: recentEventsData.map(e => e.abs_pct),
                        backgroundColor: 'rgba(59, 130, 246, 0.85)',
                        borderRadius: 3,
                        barThickness: 40,
                        order: 2
                    },
                    {
                        label: 'Net % Deviation',
                        data: recentEventsData.map(e => e.net_pct),
                        type: 'line',
                        borderColor: '#10b981',
                        borderWidth: 3,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        fill: false,
                        tension: 0.4,
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
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        padding: 12,
                        titleFont: { size: 11, weight: 'bold' },
                        bodyFont: { size: 10 },
                        callbacks: {
                            label: (context) => `${context.dataset.label}: ${context.raw}%`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], color: 'rgba(156, 163, 175, 0.2)' },
                        ticks: { font: { size: 9, weight: 'bold' }, callback: v => v + '%' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10, weight: 'black' } }
                    }
                }
            }
        });
    }

    function loadPareto(hashId, eventCode) {
        document.querySelectorAll('.event-item').forEach(el => el.classList.remove('active-event', 'bg-primary-50/50', 'border-primary-200', 'dark:bg-primary-900/10'));
        
        // Find current target if called from click, or the element if called programmatically
        const target = event && event.currentTarget ? event.currentTarget : document.querySelector(`[onclick*="${hashId}"]`);
        
        if (target) {
            target.classList.add('active-event', 'bg-primary-50/50', 'border-primary-200', 'dark:bg-primary-900/10');
        }
        
        document.getElementById('activeEventBadge').innerText = eventCode;

        fetch(`/inventory/sto/${hashId}/pareto-by-model`)
            .then(res => res.json())
            .then(data => {
                renderAccuracyCharts(data.pareto);
                renderParetoChart(data.pareto);
                renderReasonTable(data.reason_breakdown);
                
                if (data.pareto.length > 0) {
                    const top = data.pareto[0];
                    document.getElementById('topDeviationModel').innerText = top.model_name;
                }
            });
    }

    function renderAccuracyCharts(paretoData) {
        const netCtx = document.getElementById('accuracyNetChart').getContext('2d');
        const absCtx = document.getElementById('accuracyAbsChart').getContext('2d');
        if (accuracyNetChart) accuracyNetChart.destroy();
        if (accuracyAbsChart) accuracyAbsChart.destroy();

        const labels = paretoData.map(d => [d.customer_code && d.customer_code !== 'Unknown' ? d.customer_code : 'Unknown', d.model_name]);
        const sysData = paretoData.map(d => d.system_amount / 1000000);
        const realData = paretoData.map(d => d.real_amount / 1000000);
        const netData = paretoData.map(d => d.net_amount / 1000000);
        const absData = paretoData.map(d => d.abs_amount / 1000000);

        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: { label: (ctx) => `${ctx.dataset.label}: ${ctx.raw.toFixed(2)} Mio` }
                }
            },
            scales: {
                y: { type: 'linear', position: 'left', title: { display: true, text: 'Millions', font: { size: 10 } }, ticks: { font: { size: 9 } } },
                y1: { type: 'linear', position: 'right', title: { display: true, text: 'Millions', font: { size: 10 } }, ticks: { font: { size: 9 } }, grid: { drawOnChartArea: false } },
                x: { ticks: { font: { size: 10, weight: 'bold' } } }
            }
        };

        // Render NET Chart
        accuracyNetChart = new Chart(netCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Amount CO', data: sysData, backgroundColor: '#0f766e', barPercentage: 0.8, categoryPercentage: 0.8, order: 2, yAxisID: 'y' },
                    { label: 'Amount STO', data: realData, backgroundColor: '#f97316', barPercentage: 0.8, categoryPercentage: 0.8, order: 2, yAxisID: 'y' },
                    { label: 'Sum of NET', data: netData, type: 'line', borderColor: '#ef4444', borderWidth: 2, pointBackgroundColor: '#ef4444', fill: false, order: 1, yAxisID: 'y1' }
                ]
            },
            options: commonOptions
        });

        // Render ABS Chart
        accuracyAbsChart = new Chart(absCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Amount STO', data: realData, backgroundColor: '#0f766e', barPercentage: 0.8, categoryPercentage: 0.8, order: 2, yAxisID: 'y' },
                    { label: 'Amount CO', data: sysData, backgroundColor: '#f97316', barPercentage: 0.8, categoryPercentage: 0.8, order: 2, yAxisID: 'y' },
                    { label: 'Sum of ABS', data: absData, type: 'line', borderColor: '#ef4444', borderWidth: 2, pointBackgroundColor: '#ef4444', fill: false, order: 1, yAxisID: 'y1' }
                ]
            },
            options: commonOptions
        });
    }

    function renderParetoChart(paretoData) {
        const ctx = document.getElementById('paretoModelChart').getContext('2d');
        if (paretoChart) paretoChart.destroy();
        
        paretoChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: paretoData.map(d => d.model_name),
                datasets: [
                    {
                        label: 'ABS Deviation Value',
                        data: paretoData.map(d => d.abs_amount),
                        backgroundColor: paretoData.map(d => d.abs_pct > 25 ? 'rgba(244, 63, 94, 0.8)' : 'rgba(59, 130, 246, 0.7)'),
                        borderRadius: 2,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Cumulative %',
                        data: paretoData.map(d => d.cumulative_pct),
                        type: 'line',
                        borderColor: '#f59e0b',
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: '#f59e0b',
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], color: 'rgba(156, 163, 175, 0.1)' },
                        ticks: { font: { size: 9 }, callback: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) }
                    },
                    y1: {
                        position: 'right',
                        max: 100, min: 0,
                        grid: { display: false },
                        ticks: { font: { size: 9, weight: 'bold' }, callback: v => v + '%' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 9, weight: 'black' }, autoSkip: false, maxRotation: 45 }
                    }
                }
            }
        });
    }

    function renderReasonTable(reasons) {
        const tbody = document.querySelector('#reasonTable tbody');
        tbody.innerHTML = reasons.length ? '' : '<tr><td colspan="6" class="px-6 py-10 text-center italic text-slate-400">No records found.</td></tr>';

        reasons.forEach((row, idx) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50 dark:hover:bg-gray-800/50 transition-colors';
            tr.innerHTML = `
                <td class="px-4 py-3 text-center font-bold text-slate-500">${idx + 1}</td>
                <td class="px-4 py-3 font-semibold text-slate-800 dark:text-white uppercase tracking-tight">${row.model_name} ${row.customer_code && row.customer_code !== 'Unknown' ? '- ' + row.customer_code : ''}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-widest ${row.reason_category === 'SHORTAGE' ? 'bg-rose-100 text-rose-700 border border-rose-200' : 'bg-emerald-100 text-emerald-700 border border-emerald-200'}">
                        ${row.reason_name}
                    </span>
                </td>
                <td class="px-4 py-3 text-slate-500 italic text-[10px]">T.B.D</td>
                <td class="px-4 py-3 text-center text-slate-500 text-[10px] font-bold">ME</td>
                <td class="px-4 py-3 text-center text-slate-500 text-[10px]">T.B.D</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function updateSummaryKPIs() {
        let totalInc = 0, totalDec = 0;
        correctionByModel.forEach(m => {
            totalInc += m.increment_pcs;
            totalDec += m.decrement_pcs;
        });
        
        document.getElementById('totalIncPcs').innerText = '+' + new Intl.NumberFormat('id-ID').format(totalInc) + ' Pcs';
        document.getElementById('totalDecPcs').innerText = '-' + new Intl.NumberFormat('id-ID').format(totalDec) + ' Pcs';
        document.getElementById('totalCorrectionPcs').innerText = new Intl.NumberFormat('id-ID').format(totalInc + totalDec);
    }

    function showCorrectionDetail(modelName) {
        document.getElementById('modalModelName').innerText = modelName;
        const tbody = document.getElementById('correctionDetailBody');
        tbody.innerHTML = '<tr><td colspan="6" class="p-20 text-center"><i class="fa-solid fa-spinner fa-spin text-3xl text-primary-500"></i></td></tr>';
        document.getElementById('correctionDetailModal').classList.remove('hidden');

        fetch(`/inventory/sto/dashboard/correction-log/${encodeURIComponent(modelName)}`)
            .then(res => res.json())
            .then(data => {
                tbody.innerHTML = data.detail.length ? '' : '<tr><td colspan="6" class="p-10 text-center italic text-slate-400">No logs found.</td></tr>';
                data.detail.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-50 dark:hover:bg-gray-800/50 transition-colors';
                    const date = new Date(row.period_end).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'});
                    tr.innerHTML = `
                        <td class="px-6 py-4">
                            <div class="text-primary-600 font-black tracking-tighter uppercase">${row.event_code}</div>
                            <div class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">${date}</div>
                        </td>
                        <td class="px-6 py-4 font-black text-slate-800 dark:text-white">${row.part_no}</td>
                        <td class="px-6 py-4"><span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700 rounded-full text-[9px] font-black text-slate-500 uppercase tracking-widest border border-slate-200 dark:border-gray-600">${row.reason_name}</span></td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-black ${row.diff_qty < 0 ? 'text-rose-500' : 'text-emerald-500'}">
                                ${row.diff_qty > 0 ? '+' : ''}${new Intl.NumberFormat('id-ID').format(row.diff_qty)}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-black text-slate-800 dark:text-white">Rp ${new Intl.NumberFormat('id-ID').format(Math.abs(row.diff_amount))}</td>
                        <td class="px-6 py-4 italic text-slate-400 font-medium">${row.remark || '-'}</td>
                    `;
                    tbody.appendChild(tr);
                });
            });
    }

    function closeCorrectionModal() {
        document.getElementById('correctionDetailModal').classList.add('hidden');
    }
</script>

<style>
    .active-event { position: relative; }
    .active-event::after { content: ''; position: absolute; left: 0; top: 20%; height: 60%; width: 3px; background: #3b82f6; border-radius: 0 4px 4px 0; }
    .animate-fade-in-down { animation: fadeInDown 0.3s ease-out; }
    .animate-fade-in-up { animation: fadeInUp 0.3s ease-out; }
    @keyframes fadeInDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-bounce-slow { animation: bounce 2s infinite; }
    @keyframes bounce { 0%, 100% { transform: translateY(-5%); animation-timing-function: cubic-bezier(0.8,0,1,1); } 50% { transform: none; animation-timing-function: cubic-bezier(0,0,0.2,1); } }
</style>
@endpush
