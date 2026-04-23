@extends('layouts.app')

@section('title', 'Inventory Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="dashboard-container flex-1 flex flex-col gap-4">
    {{-- Header & Filters --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight uppercase">Inventory Overview</h2>
            <p class="text-[11px] text-slate-500 font-bold tracking-wide">Real-time stock monitoring and transaction analytics</p>
        </div>
        <div class="flex items-center gap-2">
            <button id="btnToggleDashFilter" class="group flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-600 rounded-xs">
                <i class="fa-solid fa-filter text-slate-400 transition-colors"></i>
                <span class="text-xs font-bold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Filters</span>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-300 transition-transform duration-300" id="filterChevron"></i>
            </button>
            <div class="h-8 w-px bg-slate-200 dark:bg-gray-700 mx-1"></div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ date('d M Y') }}</p>
        </div>
    </div>

    {{-- Collapsible Filter Card --}}
    <div id="dashboardFilterCard" class="hidden bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-600 p-4 mb-2">
        <form id="filterForm">
            <div class="flex flex-col xl:flex-row gap-4 xl:items-end">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 flex-1">
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Period</label>
                        <input type="month" id="month_picker" name="month_year" value="{{ $filters['month_year'] }}" class="w-full text-xs font-bold border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-900 dark:text-white rounded-xs h-[40px] px-3 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all focus:border-primary-500">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Customer</label>
                        <select id="filterCustomer" name="customer[]" class="w-full text-xs"></select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Model</label>
                        <select id="filterModel" name="model[]" class="w-full text-xs"></select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Balance Status</label>
                        <select id="filterBalance" name="status_balance[]" class="w-full text-xs"></select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Usage Status</label>
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

    {{-- KPI Cards Section --}}
    <div class="overflow-x-auto scrollbar-hide">
        <div class="flex gap-3 min-w-max">
            @foreach([
                ['val' => number_format($stats['total_stock_value']), 'label' => 'Total Stock Value', 'sub' => 'IDR', 'icon' => 'fa-coins', 'color' => 'primary', 'id' => 'stat_total_stock_value'],
                ['val' => number_format($stats['total_stock']), 'label' => 'Total Stock', 'sub' => 'ITEM PART', 'icon' => 'fa-cubes', 'color' => 'slate', 'id' => 'stat_total_stock'],
                ['val' => number_format($stats['material_in']), 'label' => 'Total In', 'sub' => 'ITEM PART', 'icon' => 'fa-arrow-right-to-bracket', 'color' => 'emerald', 'id' => 'stat_material_in'],
                ['val' => number_format($stats['out_event']), 'label' => 'Out Event', 'sub' => 'ITEM PART', 'icon' => 'fa-arrow-right-from-bracket', 'color' => 'amber', 'id' => 'stat_out_event'],
                ['val' => number_format($stats['out_pp']), 'label' => 'Out PP', 'sub' => 'ITEM PART', 'icon' => 'fa-industry', 'color' => 'indigo', 'id' => 'stat_out_pp'],
                ['val' => number_format($stats['out_trial']), 'label' => 'Trial Usage', 'sub' => 'ITEM PART', 'icon' => 'fa-vial', 'color' => 'rose', 'id' => 'stat_out_trial'],
            ] as $stat)
            <div class="flex-1 min-w-[180px] bg-white dark:bg-gray-800 px-4 py-3 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center justify-between transition-all hover:border-{{ $stat['color'] }}-300 dark:hover:border-{{ $stat['color'] }}-500 group">
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-1 truncate">{{ $stat['label'] }}</p>
                    <div class="flex items-baseline gap-1">
                        <h3 class="text-xl font-black text-slate-900 dark:text-white leading-none tracking-tight" id="{{ $stat['id'] }}">{{ $stat['val'] }}</h3>
                        <span class="text-[7px] font-black text-slate-300 dark:text-gray-600 uppercase">{{ $stat['sub'] }}</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xs bg-{{ $stat['color'] }}-50 dark:bg-{{ $stat['color'] }}-900/20 flex items-center justify-center text-{{ $stat['color'] }}-600 dark:text-{{ $stat['color'] }}-400 text-xl transition-all group-hover:scale-110 group-hover:bg-{{ $stat['color'] }}-100 dark:group-hover:bg-{{ $stat['color'] }}-900/40">
                    <i class="fa-solid {{ $stat['icon'] }}"></i>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-12 gap-4 mt-1 items-stretch" style="min-height: calc(100vh - 250px);">
        <div class="col-span-12 xl:col-span-8 flex flex-col gap-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 flex-1 min-h-0">
                {{-- Chart Card 1 --}}
                <div class="chart-card bg-white dark:bg-gray-800 p-6 rounded-xs border border-slate-200 dark:border-gray-700 flex flex-col relative h-[340px]">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-1 h-5 bg-primary-500 rounded-full"></div>
                            <h4 class="font-bold text-[11px] text-slate-800 dark:text-white uppercase tracking-widest">Stock Status (PART)</h4>
                        </div>
                        <button class="w-8 h-8 rounded-xs flex items-center justify-center text-slate-400 hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors"><i class="fa-solid fa-expand text-[10px]"></i></button>
                    </div>
                    <div class="flex-1 min-h-0 relative"><canvas id="stockStatusChart"></canvas></div>
                </div>
                {{-- Chart Card 2 --}}
                <div class="chart-card bg-white dark:bg-gray-800 p-6 rounded-xs border border-slate-200 dark:border-gray-700 flex flex-col relative h-[340px]">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-1 h-5 bg-amber-500 rounded-full"></div>
                            <h4 id="usageChartTitle" class="font-bold text-[11px] text-slate-800 dark:text-white uppercase tracking-widest">Usage by Models (PART)</h4>
                        </div>
                        <div class="flex items-center gap-3">
                             <div class="flex bg-slate-100 dark:bg-gray-700 p-1 rounded-xs">
                                <button type="button" onclick="switchUsageChart('model')" id="btnUsageModel" class="px-3 py-1 rounded-xs text-[8px] font-black uppercase transition-all bg-white dark:bg-gray-600 text-primary-600 shadow-sm">Model</button>
                                <button type="button" onclick="switchUsageChart('maker')" id="btnUsageMaker" class="px-3 py-1 rounded-xs text-[8px] font-black uppercase transition-all text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">Maker</button>
                            </div>
                            <button class="w-8 h-8 rounded-xs flex items-center justify-center text-slate-400 hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors"><i class="fa-solid fa-expand text-[10px]"></i></button>
                        </div>
                    </div>
                    <div class="flex-1 min-h-0 relative">
                        <div id="containerUsageModel" class="h-full"><canvas id="usageModelChart"></canvas></div>
                        <div id="containerUsageMaker" class="h-full hidden"><canvas id="makerChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 flex-1 min-h-0">
                {{-- Chart Card 3 --}}
                {{-- Balance Warnings Table (Moved here) --}}
                {{-- Balance Warnings Table --}}
                <div class="table-container bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 overflow-hidden flex flex-col relative h-[340px]">
                    <div class="py-4 px-6 border-b border-slate-50 dark:border-gray-700 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xs bg-rose-50 dark:bg-rose-900/20 flex items-center justify-center text-rose-500">
                                <i class="fa-solid fa-triangle-exclamation text-[11px]"></i>
                            </div>
                            <span class="font-bold text-[11px] text-slate-800 dark:text-white uppercase tracking-widest">Balance Warnings</span>
                        </div>
                    </div>
                    <div class="overflow-y-auto flex-1 custom-scrollbar">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/80 dark:bg-gray-700/50 sticky top-0 z-10 backdrop-blur-md">
                                <tr>
                                    <th class="py-3 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Part No</th>
                                    <th class="py-3 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Min</th>
                                    <th class="py-3 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Actual</th>
                                    <th class="py-3 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody id="balanceTableBody" class="divide-y divide-slate-50 dark:divide-gray-700">
                                @forelse($tables['balance'] as $row)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-700/20 transition-colors">
                                    <td class="py-3 px-6">
                                        <p class="text-[11px] font-bold text-slate-800 dark:text-white tracking-tight leading-tight uppercase">{{ $row->part_no }} {{ $row->revision ? '- ' . $row->revision : '' }}</p>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">{{ $row->model_name }} | {{ $row->customer_code }}</p>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="text-[11px] font-bold text-slate-400 font-mono">{{ number_format($row->min_stock) }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="text-[11px] font-black text-slate-900 dark:text-white font-mono">{{ number_format($row->current_stock_qty) }}</div>
                                    </td>
                                    <td class="py-3 px-6 text-right">
                                        @php 
                                            $color = $row->status == 'Critical' ? 'red' : ($row->status == 'Warning' ? 'amber' : ($row->status == 'Over' ? 'primary' : 'emerald'));
                                        @endphp
                                        <span class="inline-flex px-2 py-1 rounded-xs text-[8px] font-black bg-{{ $color == 'primary' ? 'primary' : $color }}-50 text-{{ $color == 'primary' ? 'primary' : $color }}-600 dark:bg-{{ $color == 'primary' ? 'primary' : $color }}-900/30 dark:text-{{ $color == 'primary' ? 'primary' : $color }}-400 border border-{{ $color == 'primary' ? 'primary' : $color }}-100 dark:border-{{ $color == 'primary' ? 'primary' : $color }}-800 uppercase italic leading-none">{{ $row->status }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="p-10 text-center text-slate-400 italic text-[11px]">All items are currently within safe limits.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Material Usage Table --}}
                <div class="table-container bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 overflow-hidden flex flex-col relative h-[340px]">
                    <div class="py-4 px-6 border-b border-slate-50 dark:border-gray-700 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xs bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-500">
                                <i class="fa-solid fa-vial text-[11px]"></i>
                            </div>
                            <span class="font-bold text-[11px] text-slate-800 dark:text-white uppercase tracking-widest">Material Usage Detail</span>
                        </div>
                    </div>
                    <div class="overflow-y-auto flex-1 custom-scrollbar">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/80 dark:bg-gray-700/50 sticky top-0 z-10 backdrop-blur-md">
                                <tr>
                                    <th class="py-3 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Part No</th>
                                    <th class="py-3 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Supplier</th>
                                    <th class="py-3 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Rank</th>
                                    <th class="py-3 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Actual</th>
                                    <th class="py-3 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Gap</th>
                                    <th class="py-3 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody id="usageTableBody" class="divide-y divide-slate-50 dark:divide-gray-700">
                                @forelse($tables['usage'] as $row)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-700/20 transition-colors">
                                    <td class="py-3 px-6 text-[10px] font-black text-slate-700 dark:text-white uppercase tracking-tighter">{{ $row['part_no'] }} {{ $row['revision'] ? '- ' . $row['revision'] : '' }}</td>
                                    <td class="py-3 px-4 text-[11px] font-bold text-slate-800 dark:text-white uppercase tracking-tight">{{ $row['supplier_name'] }}</td>
                                    <td class="py-3 px-4 text-[10px] font-bold text-slate-500 dark:text-slate-400 font-mono">{{ $row['rank_display'] }}</td>
                                    <td class="py-3 px-4 text-[11px] font-black text-slate-900 dark:text-white text-right font-mono">{{ number_format($row['out_trial']) }}</td>
                                    <td class="py-3 px-4 text-[11px] font-black {{ $row['gap'] < 0 ? 'text-red-500' : 'text-emerald-500' }} text-right font-mono">{{ number_format($row['gap']) }}</td>
                                    <td class="py-3 px-6 text-right">
                                        @php 
                                            $color = $row['status'] == 'Loss' ? 'red' : ($row['status'] == 'Near Loss' ? 'amber' : 'emerald');
                                        @endphp
                                        <span class="inline-flex px-2 py-1 rounded-xs text-[8px] font-black bg-{{ $color }}-50 text-{{ $color }}-600 dark:bg-{{ $color }}-900/30 dark:text-{{ $color }}-400 border border-{{ $color }}-100 dark:border-{{ $color }}-800 uppercase italic leading-none">{{ $row['status'] }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="p-10 text-center text-slate-400 italic text-[11px]">No trial data available.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-span-12 xl:col-span-4 flex flex-col gap-4">
            {{-- Transaction Trend Chart --}}
            <div class="chart-card bg-white dark:bg-gray-800 p-6 rounded-xs border border-slate-200 dark:border-gray-700 flex flex-col relative h-[340px]">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-1 h-5 bg-emerald-500 rounded-full"></div>
                        <h4 class="font-bold text-[11px] text-slate-800 dark:text-white uppercase tracking-widest">Transaction Trend (ITEM PART)</h4>
                    </div>
                    <button class="w-8 h-8 rounded-xs flex items-center justify-center text-slate-400 hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors"><i class="fa-solid fa-expand text-[10px]"></i></button>
                </div>
                <div class="flex-1 min-h-0 relative"><canvas id="trendlineChart"></canvas></div>
            </div>

            {{-- Recent Transactions --}}
            <div class="table-container bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-600 overflow-hidden flex flex-col flex-1 min-h-0">
                <div class="py-3 px-5 border-b border-slate-50 dark:border-gray-700 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-xs bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center text-primary-500">
                            <i class="fa-solid fa-clock-rotate-left text-[10px]"></i>
                        </div>
                        <span class="font-bold text-[10px] text-slate-800 dark:text-white uppercase tracking-wider">Recent Activity</span>
                    </div>
                </div>
                <div class="overflow-y-auto flex-1 custom-scrollbar">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50/80 dark:bg-gray-700/50 sticky top-0 z-10 backdrop-blur-md">
                            <tr>
                                <th class="py-3 px-5 text-[8px] font-black text-slate-400 uppercase tracking-widest">Part No</th>
                                <th class="py-3 px-3 text-[8px] font-black text-slate-400 uppercase tracking-widest text-center">Type</th>
                                <th class="py-3 px-5 text-[8px] font-black text-slate-400 uppercase tracking-widest text-center">Datetime</th>
                                <th class="py-3 px-5 text-[8px] font-black text-slate-400 uppercase tracking-widest text-right">Qty</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody" class="divide-y divide-slate-50 dark:divide-gray-700">
                            @forelse($tables['history'] as $row)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-700/20 transition-colors">
                                <td class="py-3 px-5">
                                    <p class="text-[10px] font-black text-slate-800 dark:text-white leading-tight uppercase">{{ $row->part_no }} {{ $row->revision ? '- ' . $row->revision : '' }}</p>
                                    <p class="text-[8px] font-bold text-slate-400 uppercase">{{ $row->model_name }} | {{ $row->customer_code }}</p>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="px-1.5 py-0.5 rounded-xs text-[8px] font-black bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 uppercase">{{ $row->category }}</span>
                                </td>
                                <td class="py-3 px-5 text-center">
                                    <div class="text-[9px] font-bold text-slate-600 dark:text-slate-400">{{ \Carbon\Carbon::parse($row->transaction_date)->format('d M y') }}</div>
                                    <div class="text-[8px] font-bold text-slate-400 dark:text-slate-500 font-mono">{{ \Carbon\Carbon::parse($row->created_at)->format('H:i') }}</div>
                                </td>
                                <td class="py-3 px-5 text-right">
                                    <div class="text-[10px] font-black text-slate-900 dark:text-white font-mono">{{ number_format($row->qty_pcs) }}</div>
                                    <div class="text-[7px] font-bold text-slate-400 uppercase leading-none">PCS</div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="p-10 text-center text-slate-400 italic text-[10px]">No activity.</td></tr>
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

    /* Match Select2 height with other inputs (38px) */
    .select2-container .select2-selection--single {
        height: 38px !important;
        display: flex !important;
        align-items: center !important;
        border-color: #d1d5db !important; /* gray-300 */
        border-radius: 0.125rem !important; /* rounded-xs */
    }
    .dark .select2-container .select2-selection--single {
        background-color: #1f2937 !important; /* gray-800 */
        border-color: #4b5563 !important; /* gray-600 */
        color: white !important;
    }
    .select2-container .select2-selection--single .select2-selection__rendered {
        padding-left: 0.75rem !important; /* px-3 */
        font-size: 0.75rem !important; /* text-xs */
        font-weight: 600 !important;
        color: #1e293b !important; /* slate-800 */
    }
    .dark .select2-container .select2-selection--single .select2-selection__rendered {
        color: #e2e8f0 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px !important;
        top: 0 !important;
        right: 8px !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        margin-top: 0 !important;
        top: auto !important;
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
            $('#usageChartTitle').text(isModel ? 'Usage by Models (PART)' : 'Supply by Makers (PART)');
            
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
                        const cats = [...new Set(trends.map(d => d.category))];
                        
                        const newDatasets = cats.map((cat, idx) => {
                            const colorKeys = Object.keys(chartColors);
                            const color = chartColors[colorKeys[idx % colorKeys.length]];
                            return {
                                label: cat.replace('OUT-', ''),
                                data: dates.map(d => (trends.find(td => td.transaction_date === d && td.category === cat) || {total: 0}).total),
                                borderColor: color.solid,
                                backgroundColor: color.light,
                                fill: false,
                                tension: 0.4,
                                pointRadius: 3,
                                pointHoverRadius: 6,
                                borderWidth: 3
                            };
                        });
                        
                        trendlineChart.data.labels = dates;
                        trendlineChart.data.datasets = newDatasets;
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
            function renderTable(selector, data, rowGenerator) {
                const tbody = $(selector);
                tbody.empty();
                if (data.length === 0) {
                    const colCount = $(selector).closest('table').find('thead th').length || 5;
                    tbody.append(`<tr><td colspan="${colCount}" class="p-10 text-center text-slate-400 dark:text-gray-500 italic text-[11px]">No data found</td></tr>`);
                    return;
                }
                data.forEach(item => {
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
                        <td class="py-3 px-6">
                            <div class="flex items-center gap-1.5 mb-1.5">
                                <span class="px-1.2 py-0.5 rounded-[2px] bg-slate-100 dark:bg-slate-700 text-[7px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest border border-slate-200/50 dark:border-slate-600/50">
                                    ${row.customer_code || '-'}
                                </span>
                                <span class="px-1.2 py-0.5 rounded-[2px] bg-primary-50 dark:bg-primary-900/20 text-[7px] font-black text-primary-600 dark:text-primary-400 uppercase tracking-widest border border-primary-100/50 dark:border-primary-800/50">
                                    ${row.model_name || 'No Model'}
                                </span>
                            </div>
                            <p class="text-[11px] font-bold text-slate-800 dark:text-white truncate max-w-[150px] leading-tight uppercase tracking-tight">
                                ${row.part_no} ${row.revision ? '- ' + row.revision : ''}
                            </p>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">
                                ${row.model_name} | ${row.customer_code}
                            </p>
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-[11px] font-bold text-slate-400">
                            ${new Intl.NumberFormat().format(row.min_stock)}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-[11px] font-black text-slate-900 dark:text-white">
                            ${new Intl.NumberFormat().format(row.current_stock_qty)}
                        </td>
                        <td class="py-3 px-6 text-right">
                            <span class="inline-flex px-2 py-1 rounded-xs text-[8px] font-black ${colorClass} border uppercase italic leading-none">${row.status}</span>
                        </td>
                    </tr>
                 `;
            }
            function generateUsageRow(row) {
                const color = row.status == 'Loss' ? 'red' : (row.status == 'Near Loss' ? 'amber' : 'emerald');
                return `
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-700/20 transition-colors">
                        <td class="py-3 px-6 text-[10px] font-black text-slate-700 dark:text-white uppercase tracking-tighter">${row.part_no} ${row.revision ? '- ' + row.revision : ''}</td>
                        <td class="py-3 px-4 text-[11px] font-bold text-slate-800 dark:text-white uppercase tracking-tight">${row.supplier_name}</td>
                        <td class="py-3 px-4 text-[10px] font-bold text-slate-500 dark:text-slate-400 font-mono">${row.rank_display}</td>
                        <td class="py-3 px-4 text-[11px] font-black text-slate-900 dark:text-white text-right font-mono">${new Intl.NumberFormat().format(row.out_trial)}</td>
                        <td class="py-3 px-4 text-[11px] font-black ${row.gap < 0 ? 'text-red-500' : 'text-emerald-500'} text-right font-mono">${new Intl.NumberFormat().format(row.gap)}</td>
                        <td class="py-3 px-6 text-right">
                            <span class="inline-flex px-2 py-1 rounded-xs text-[8px] font-black bg-${color}-50 text-${color}-600 dark:bg-${color}-900/30 dark:text-${color}-400 border border-${color}-100 dark:border-${color}-800 uppercase italic leading-none">${row.status}</span>
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
                        <td class="py-3 px-5">
                            <p class="text-[10px] font-black text-slate-800 dark:text-white leading-tight uppercase">${row.part_no} ${row.revision ? '- ' + row.revision : ''}</p>
                            <p class="text-[8px] font-bold text-slate-400 uppercase">${row.model_name} | ${row.customer_code}</p>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-1.5 py-0.5 rounded-xs text-[8px] font-black bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 uppercase">${row.category}</span>
                        </td>
                        <td class="py-3 px-5 text-center">
                            <div class="text-[9px] font-bold text-slate-600 dark:text-slate-400">${dateStr}</div>
                            <div class="text-[8px] font-bold text-slate-400 dark:text-slate-500 font-mono">${timeStr}</div>
                        </td>
                        <td class="py-3 px-5 text-right">
                            <div class="text-[10px] font-black text-slate-900 dark:text-white font-mono">${new Intl.NumberFormat().format(row.qty_pcs)}</div>
                            <div class="text-[7px] font-bold text-slate-400 uppercase leading-none">PCS</div>
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
                        legend: { position: 'top', labels: { color: isDark ? '#94a3b8' : '#64748b' } }
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
                        legend: { position: 'top', labels: { color: isDark ? '#94a3b8' : '#64748b' } }
                    }
                }
            });
        }

        if (document.getElementById('trendlineChart')) {
            const trendData = chartsData.trendData;
            const dates = trendData ? [...new Set(trendData.map(d => d.transaction_date))] : [];
            const cats = trendData ? [...new Set(trendData.map(d => d.category))] : [];
            const colors = ['#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];

                trendlineChart = new Chart(document.getElementById('trendlineChart'), {
                type: 'line',
                plugins: [verticalLinePlugin],
                data: {
                    labels: dates,
                    datasets: cats.map((cat, idx) => {
                        const colorKeys = Object.keys(chartColors);
                        const color = chartColors[colorKeys[idx % colorKeys.length]];
                        return {
                            label: cat.replace('OUT-', ''),
                            data: dates.map(d => (trendData.find(td => td.transaction_date === d && td.category === cat) || {
                                total: 0
                            }).total),
                            borderColor: color.solid,
                            backgroundColor: color.light,
                            fill: false,
                            tension: 0.4,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            borderWidth: 3,
                            pointStyle: 'circle'
                        };
                    })
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
                            position: 'top',
                            labels: { 
                                color: isDark ? '#94a3b8' : '#64748b',
                                usePointStyle: true,
                                pointStyle: 'circle',
                                padding: 20
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
                                        label += new Intl.NumberFormat().format(context.parsed.y) + ' ITEM PART';
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
                        legend: { position: 'top', labels: { color: isDark ? '#94a3b8' : '#64748b' } }
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