@extends('layouts.app')
@section('title', 'Material Monitoring')
@section('page_title', 'Material Monitoring')
@section('header-title', 'Material Monitoring')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-4">
        <div>
            <h2 class="text-xl xl:text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tighter leading-none">Material Monitoring</h2>
            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 font-normal">Real-time status of material balance and usage.</p>
        </div>

        {{-- Action Toolbar --}}
        <div class="flex items-center gap-2 mt-4 sm:mt-0 relative">
            {{-- Mode Switch --}}
            <div class="bg-slate-100 dark:bg-gray-900/50 p-1 rounded-xs flex items-center mr-2 border border-slate-200 dark:border-gray-700">
                <button id="mode-balance" class="px-4 py-1.5 text-xs font-medium tracking-wider rounded-xs transition-all duration-200 bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm">Balance Stock</button>
                <button id="mode-usage" class="px-4 py-1.5 text-xs font-medium tracking-wider rounded-xs transition-all duration-200 text-slate-500 hover:text-slate-700 dark:text-gray-400 dark:hover:text-gray-200">Material Usage</button>
            </div>

            <button id="btnToggleFilter" class="h-9 px-4 rounded-xs bg-white dark:bg-gray-800 text-slate-600 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 border border-slate-200 dark:border-gray-700 flex items-center justify-center transition-all duration-200" title="Toggle Filters">
                <i class="fa-solid fa-filter text-sm mr-2.5"></i>
                <span class="text-xs font-medium">Filters</span>
            </button>

            <button id="btnExportExcel" class="h-9 px-4 rounded-xs bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm flex items-center justify-center transition-all duration-200 active:scale-95" title="Export Excel">
                <i class="fa-solid fa-file-excel text-sm mr-2.5"></i>
                <span class="text-xs font-medium">Export Excel</span>
            </button>
            
            <div class="relative">
                <button id="toggleLegend" class="h-9 px-4 rounded-xs bg-white dark:bg-gray-800 text-slate-600 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 border border-slate-200 dark:border-gray-700 flex items-center justify-center transition-all duration-200" title="Legend & Help">
                    <i class="fa-solid fa-circle-question text-sm mr-2.5"></i>
                    <span class="text-xs font-medium">Legend</span>
                </button>

                {{-- Legend Popover Content --}}
                <div id="legendPopover" class="hidden absolute right-0 top-full mt-2 w-72 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 p-6 z-50">
                    <h4 class="font-bold text-gray-900 dark:text-gray-100 text-xs tracking-wider mb-2">Stock Status</h4>
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-primary-500 mr-2 flex-shrink-0"></span>
                            <div class="text-gray-600 dark:text-gray-300 font-medium">Over <span class="text-gray-400 text-[10px] tracking-tighter">(Stock &gt; [Min Stock x 3])</span></div>
                        </div>
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-2 flex-shrink-0"></span>
                            <div class="text-gray-600 dark:text-gray-300 font-medium">Safe <span class="text-gray-400 text-[10px] tracking-tighter">(Min Stock to [Min Stock x 3])</span></div>
                        </div>
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 mr-2 flex-shrink-0"></span>
                            <div class="text-gray-600 dark:text-gray-300 font-medium">Warning <span class="text-gray-400 text-[10px] tracking-tighter">(Min Stock-30 to Min Stock)</span></div>
                        </div>
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500 mr-2 flex-shrink-0 animate-pulse"></span>
                            <div class="text-gray-600 dark:text-gray-300 font-medium">Critical <span class="text-gray-400 text-[10px] tracking-tighter">(Stock &lt; Min Stock-30)</span></div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 dark:border-gray-700 my-3"></div>

                    <h4 class="font-bold text-gray-900 dark:text-gray-100 text-xs tracking-wider mb-3">Trial Material Usage</h4>
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-2 flex-shrink-0"></span>
                            <div class="text-gray-600 dark:text-gray-300 font-medium">On Budget <span class="text-gray-400 text-[10px] tracking-tighter">(Usage 0 to Rank-50)</span></div>
                        </div>
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 mr-2 flex-shrink-0"></span>
                            <div class="text-gray-600 dark:text-gray-300 font-medium">Near Loss <span class="text-gray-400 text-[10px] tracking-tighter">(Usage Rank-49 to Rank)</span></div>
                        </div>
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500 mr-2 flex-shrink-0"></span>
                            <div class="text-gray-600 dark:text-gray-300 font-medium">Loss <span class="text-gray-400 text-[10px] tracking-tighter">(Usage &gt; Rank)</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Collapsible Filter Card --}}
    <div id="filterCard" class="hidden bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 p-4 mb-4 relative">
        <div class="absolute -top-2 right-14 w-0 h-0 border-l-[10px] border-l-transparent border-r-[10px] border-r-transparent border-b-[10px] border-b-slate-200 dark:border-b-gray-700"></div>
        <div class="absolute -top-[7px] right-14 w-0 h-0 border-l-[10px] border-l-transparent border-r-[10px] border-r-transparent border-b-[10px] border-b-white dark:border-b-gray-800"></div>
        
        <div class="flex flex-col xl:flex-row gap-3 xl:items-end">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 flex-1">

                <!-- Customer -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-900 dark:text-gray-500 tracking-wider leading-tight">Customer</label>
                    <div class="w-full">
                        <select id="filter_customer" class="select2 w-full">
                            <option value="">All Customers</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->code }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Model -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-900 dark:text-gray-500 tracking-wider leading-tight">Model</label>
                    <div class="w-full">
                        <select id="filter_model" class="select2 w-full">
                            <option value="">All Models</option>
                        </select>
                    </div>
                </div>

                <!-- Status -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-900 dark:text-gray-500 tracking-wider leading-tight">Project Status</label>
                    <div class="w-full">
                        <select id="filter_project_status" class="select2 w-full">
                            <option value="">All Status</option>
                            @foreach($project_statuses as $ps)
                                <option value="{{ $ps }}">{{ $ps }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Stock Status -->
                <div class="space-y-1.5" id="filter_status_container">
                    <label class="block text-xs font-medium text-slate-900 dark:text-gray-500 tracking-wider leading-tight">Stock Status</label>
                    <div class="w-full">
                        <select id="filter_status" class="select2 w-full">
                            <option value="">All Status</option>
                            <option value="safe">Safe</option>
                            <option value="warning">Warning</option>
                            <option value="critical">Critical</option>
                            <option value="over">Over</option>
                        </select>
                    </div>
                </div>

                <!-- Usage Status (Hidden by default) -->
                <div class="space-y-1.5 hidden" id="filter_usage_status_container">
                    <label class="block text-xs font-medium text-slate-900 dark:text-gray-500 tracking-wider leading-tight">Usage Status</label>
                    <div class="w-full">
                        <select id="filter_usage_status" class="select2 w-full">
                            <option value="">All Status</option>
                            <option value="on_budget">On Budget</option>
                            <option value="near_loss">Near Loss</option>
                            <option value="loss">Loss</option>
                        </select>
                    </div>
                </div>

            </div>

            <div class="flex gap-2 pt-2 xl:pt-0">
                <button type="button" id="reset_filters" class="h-9 px-4 flex items-center justify-center gap-2 bg-white dark:bg-gray-800 text-slate-500 hover:text-primary-600 border border-slate-200 dark:border-gray-700 rounded-xs transition-all text-xs font-medium active:scale-95 shadow-xs">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </button>
            </div>
        </div>
    </div>
    
    {{-- Individual KPI Cards - Forced Single Row --}}
    <div id="kpi-balance" class="flex flex-nowrap gap-3 mb-4 overflow-x-auto scrollbar-hide">
        <!-- Total -->
        <div class="flex-none w-[180px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all hover:bg-slate-50/50 dark:hover:bg-gray-700/50">
            <div class="w-10 h-10 rounded-xs bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 flex items-center justify-center text-slate-500 text-lg">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div>
                <div class="text-sm font-bold text-slate-900 dark:text-gray-500 tracking-tight mb-1">Total parts</div>
                <div class="text-sm font-bold text-slate-800 dark:text-white leading-none tracking-tighter">{{ number_format($stats['balance']['total'] ?? 0) }}</div>
            </div>
        </div>

        <!-- Safe -->
        <div class="flex-none w-[180px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all">
            <div class="w-10 h-10 rounded-xs bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-lg">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="text-sm font-bold text-emerald-600 dark:text-emerald-500 tracking-tight mb-1">Safe stock</div>
                <div class="text-sm font-bold text-slate-800 dark:text-white leading-none tracking-tighter">{{ number_format($stats['balance']['safe'] ?? 0) }}</div>
            </div>
        </div>

        <!-- Warning -->
        <div class="flex-none w-[180px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all">
            <div class="w-10 h-10 rounded-xs bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800/50 flex items-center justify-center text-amber-600 dark:text-amber-400 text-lg">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <div class="text-sm font-bold text-amber-600 dark:text-amber-500 tracking-tight mb-1">Warning</div>
                <div class="text-sm font-bold text-slate-800 dark:text-white leading-none tracking-tighter">{{ number_format($stats['balance']['warning'] ?? 0) }}</div>
            </div>
        </div>

        <!-- Critical -->
        <div class="flex-none w-[180px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all">
            <div class="w-10 h-10 rounded-xs bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800/50 flex items-center justify-center text-red-600 dark:text-red-400 text-lg">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div>
                <div class="text-sm font-bold text-red-600 dark:text-red-500 tracking-tight mb-1">Critical</div>
                <div class="text-sm font-bold text-slate-800 dark:text-white leading-none tracking-tighter">{{ number_format($stats['balance']['critical'] ?? 0) }}</div>
            </div>
        </div>

        <!-- Over Stock -->
        <div class="flex-none w-[180px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all">
            <div class="w-10 h-10 rounded-xs bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800/50 flex items-center justify-center text-primary-600 dark:text-primary-400 text-lg">
                <i class="fa-solid fa-arrow-trend-up"></i>
            </div>
            <div>
                <div class="text-sm font-bold text-primary-600 dark:text-primary-500 tracking-tight mb-1">Over stock</div>
                <div class="text-sm font-bold text-slate-800 dark:text-white leading-none tracking-tighter">{{ number_format($stats['balance']['over'] ?? 0) }}</div>
            </div>
        </div>
    </div>
    
    {{-- Individual KPI Cards - Usage / Out Trial --}}
    <div id="kpi-usage" class="hidden flex-nowrap gap-3 mb-4 overflow-x-auto scrollbar-hide">
        <!-- Total Trial Parts -->
        <div class="flex-none w-[180px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all hover:bg-slate-50/50 dark:hover:bg-gray-700/50">
            <div class="w-10 h-10 rounded-xs bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 flex items-center justify-center text-slate-500 text-lg">
                <i class="fa-solid fa-flask"></i>
            </div>
            <div>
                <div class="text-sm font-bold text-gray-400 dark:text-gray-500 tracking-tight mb-1">Total trial parts</div>
                <div class="text-sm font-bold text-slate-800 dark:text-white leading-none tracking-tighter">{{ number_format($stats['usage']['total'] ?? 0) }}</div>
            </div>
        </div>

        <!-- On Budget -->
        <div class="flex-none w-[180px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all">
            <div class="w-10 h-10 rounded-xs bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-lg">
                <i class="fa-solid fa-piggy-bank"></i>
            </div>
            <div>
                <div class="text-sm font-bold text-emerald-600 dark:text-emerald-500 tracking-tight mb-1">On budget</div>
                <div class="text-sm font-bold text-slate-800 dark:text-white leading-none tracking-tighter">{{ number_format($stats['usage']['on_budget'] ?? 0) }}</div>
            </div>
        </div>

        <!-- Near Loss -->
        <div class="flex-none w-[180px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all">
            <div class="w-10 h-10 rounded-xs bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800/50 flex items-center justify-center text-amber-600 dark:text-amber-400 text-lg">
                <i class="fa-solid fa-coins"></i>
            </div>
            <div>
                <div class="text-sm font-bold text-amber-600 dark:text-amber-500 tracking-tight mb-1">Near loss</div>
                <div class="text-sm font-bold text-slate-800 dark:text-white leading-none tracking-tighter">{{ number_format($stats['usage']['near_loss'] ?? 0) }}</div>
            </div>
        </div>

        <!-- Loss -->
        <div class="flex-none w-[180px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all">
            <div class="w-10 h-10 rounded-xs bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800/50 flex items-center justify-center text-red-600 dark:text-red-400 text-lg">
                <i class="fa-solid fa-arrow-trend-down"></i>
            </div>
            <div>
                <div class="text-sm font-bold text-red-600 dark:text-red-500 tracking-tight mb-1">Loss</div>
                <div class="text-sm font-bold text-slate-800 dark:text-white leading-none tracking-tighter">{{ number_format($stats['usage']['loss'] ?? 0) }}</div>
            </div>
        </div>
    </div>



    {{-- DataTables Wrapper --}}
    <div id="balanceView">
        <div>
            <x-table id="stockMonitoringTable">
                <thead>
                    <tr>
                        <th rowspan="2" class="w-12 border-b border-slate-200 dark:border-gray-700 text-center font-medium tracking-wider text-xs">No</th>
                        <th rowspan="2" class="border-b border-slate-200 dark:border-gray-700 text-left font-medium tracking-wider text-xs">Model</th>
                        <th rowspan="2" class="border-b border-slate-200 dark:border-gray-700 text-left font-medium tracking-wider text-xs">Part information</th>
                        <th rowspan="2" class="border-b border-slate-200 dark:border-gray-700 text-left font-medium tracking-wider text-xs">Status</th>
                        <th colspan="{{ 2 + max(1, $categories->count()) + 2 }}" class="border-b border-slate-200 dark:border-gray-700 text-center font-medium tracking-wider text-xs bg-slate-50/50 dark:bg-slate-900/50">Current balance & movement</th>
                        <th rowspan="2" class="w-20 border-b border-slate-200 dark:border-gray-700 text-center font-medium tracking-wider text-xs">Action</th>
                    </tr>
                    <tr>
                        <th class="border-b border-slate-200 dark:border-gray-700 text-center font-medium tracking-wider text-[10px] bg-slate-50/50 dark:bg-slate-900/30">Min stock</th>
                        <th class="border-b border-slate-200 dark:border-gray-700 text-center font-medium tracking-wider text-[10px] bg-slate-50/50 dark:bg-slate-900/30">Balance</th>

                        @foreach($categories as $cat)
                        <th class="border-b border-slate-200 dark:border-gray-700 text-center font-medium tracking-wider text-[10px] whitespace-nowrap">{{ $cat->code }}</th>
                        @endforeach
                        
                        <th class="border-b border-slate-200 dark:border-gray-700 text-center font-medium tracking-wider text-[10px] bg-amber-50/30 dark:bg-amber-900/10">STO gap</th>
                        <th class="border-b border-slate-200 dark:border-gray-700 text-center font-medium tracking-wider text-[10px] bg-emerald-50/30 dark:bg-emerald-900/10">Amount (Rp)</th>

                        @if($categories->count() === 0)
                        <th class="border-b border-slate-200 dark:border-gray-700 text-center font-bold uppercase tracking-widest text-[9px]">-</th>
                        @endif
                    </tr>
                </thead>
                <tbody></tbody>
            </x-table>
        </div>
    </div>

    <div id="usageView" class="hidden">
        <div >
            <x-table id="usageTable">
                <thead>
                    <tr>
                        <th class="w-12 border-b border-slate-200 dark:border-gray-700 text-center font-medium tracking-wider text-xs">No</th>
                        <th class="border-b border-slate-200 dark:border-gray-700 text-left font-medium tracking-wider text-xs">Model</th>
                        <th class="border-b border-slate-200 dark:border-gray-700 text-left font-medium tracking-wider text-xs">Part information</th>
                        <th class="border-b border-slate-200 dark:border-gray-700 text-left font-medium tracking-wider text-xs">Project status</th>
                        <th class="border-b border-slate-200 dark:border-gray-700 text-left font-medium tracking-wider text-xs">Supplier</th>
                        <th class="border-b border-slate-200 dark:border-gray-700 text-center font-medium tracking-wider text-xs">Rank</th>
                        <th class="border-b border-slate-200 dark:border-gray-700 text-center font-medium tracking-wider text-xs">Out-trial</th>
                        <th class="border-b border-slate-200 dark:border-gray-700 text-center font-medium tracking-wider text-xs bg-amber-50/30 dark:bg-amber-900/10">Gap</th>
                        <th class="border-b border-slate-200 dark:border-gray-700 text-center font-medium tracking-wider text-xs">Status</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </x-table>
        </div>
    </div>

    {{-- Popover Components --}}
    <x-inventory.sto-log-popover />
    <x-inventory.product-detail-popover />
</div>
@endsection

@push('style')
<style>
    /* Tooltip content style */
    #global-tooltip-portal {
        pointer-events: none;
        display: none;
    }
    
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        let currentMode = 'balance'; // 'balance' or 'usage'
        
        // Base Columns Configuration
        let baseColumns = [{
                data: null,
                orderable: false, // Not sortable
                searchable: false,
                render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1
            },
            { 
                data: 'model_name',
                className: 'text-left font-medium text-slate-700 dark:text-gray-300 text-[10px] tracking-tight',
                render: d => d || '-'
            },
            {
                data: 'part_no',
                render: function(data, type, row) {
                    const partNoDisplay = data + (row.revision ? ' - ' + row.revision : '');
                    if (type === 'display') {
                        if (row.details) {
                            const details = JSON.stringify(row.details).replace(/"/g, '&quot;');
                            return `
                                <div class="hover-trigger cursor-pointer group/part relative pl-3 transition-all" data-details="${details}">
                                    <div class="absolute left-0 top-0 bottom-0 w-0.5 bg-primary-500 scale-y-0 group-hover/part:scale-y-100 transition-transform origin-center"></div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-700 dark:text-slate-200 leading-none tracking-tight group-hover/part:text-primary-600 transition-colors">
                                            ${partNoDisplay}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-medium truncate max-w-[180px] mt-1 group-hover/part:text-slate-500 transition-colors">
                                            ${row.part_name || ''}
                                        </span>
                                    </div>
                                </div>
                            `;
                        }
                        return `
                            <div class="flex flex-col pl-3">
                                <span class="font-medium text-slate-900 dark:text-slate-200 leading-none tracking-tight">${partNoDisplay}</span>
                                <span class="text-[10px] text-slate-500 font-medium truncate max-w-[180px] mt-1">${row.part_name || ''}</span>
                            </div>
                        `;
                    }
                    return data;
                }
            },
            {
                data: 'project_status',
                className: 'text-center',
                render: function(data) {
                    let colorClass = 'bg-gray-50 text-gray-600 border-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700';
                    if (data === 'Project') colorClass = 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800/50';
                    else if (data === 'Regular') colorClass = 'bg-primary-50 text-primary-700 border-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:border-primary-800/50';
                    else if (data === 'Oldstock OK') colorClass = 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/50';
                    else if (data === 'Oldstock NG') colorClass = 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800/50';
                    else if (data === 'Damage') colorClass = 'bg-red-50 text-red-700 border-red-100 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800/50';
                    else if (data === 'Drawing Change') colorClass = 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800/50';
                    else if (data === 'Under') colorClass = 'bg-fuchsia-50 text-fuchsia-700 border-fuchsia-100 dark:bg-fuchsia-900/20 dark:text-fuchsia-400 dark:border-fuchsia-800/50';
                    else if (data === 'Other') colorClass = 'bg-slate-50 text-slate-600 border-slate-100 dark:bg-slate-900/20 dark:text-slate-400 dark:border-slate-800/50';
                    
                    return `<span class="px-3 py-1.5 rounded-xs border ${colorClass} text-[10px] font-medium tracking-wider inline-block">${data}</span>`;
                }
            },
            {
                data: 'min_stock',
                className: 'text-center font-medium text-slate-900 text-[11px]',
                render: (data) => data || '0'
            },
            {
                data: 'balance_pcs',
                className: 'text-center',
                render: function(data, type, row) {
                    if (type === 'display') {
                        let indicatorClass = 'bg-slate-600';
                        let textColorClass = 'text-gray-900 dark:text-white';
                        const status = row.stock_status || 'default';

                        if (status === 'safe') {
                            indicatorClass = 'bg-emerald-500';
                            textColorClass = 'text-emerald-700 dark:text-emerald-400';
                        } else if (status === 'warning') {
                            indicatorClass = 'bg-amber-500';
                            textColorClass = 'text-amber-700 dark:text-amber-400';
                        } else if (status === 'critical') {
                            indicatorClass = 'bg-red-500 animate-pulse';
                            textColorClass = 'text-red-700 dark:text-red-400';
                        } else if (status === 'over') {
                            indicatorClass = 'bg-primary-500';
                            textColorClass = 'text-primary-700 dark:text-primary-400';
                        }

                        const pcsVal = parseFloat(row.balance_pcs.replace(/,/g, '')) || 0;
                        const unitVal = parseFloat(row.current_qty) || 0;
                        let unitLabel = (row.balance_unit || '').toUpperCase();
                        if (unitLabel.includes('COIL')) {
                            unitLabel = 'KG';
                        }
                        
                        const pcsDisplay = pcsVal.toLocaleString(undefined, { maximumFractionDigits: 0 }) + ' PCS';
                        const unitDisplay = unitVal.toLocaleString(undefined, { maximumFractionDigits: 2 }) + ' ' + unitLabel;

                        const breakdown = `In: ${row.total_in} | Out: ${row.total_out} | STO: ${row.sto_gap_plain}`;

                        let qtyHtml = '';
                        if (parseFloat(row.pcs_per_unit) == 1 && !(row.balance_unit || '').toLowerCase().includes('coil')) {
                            qtyHtml = `<span class="font-bold ${textColorClass} text-xs tracking-tight">${pcsDisplay}</span>`;
                        } else {
                            qtyHtml = `
                                <div class="flex flex-col items-center justify-center">
                                    <span class="font-bold ${textColorClass} text-xs tracking-tight">${unitDisplay}</span>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium tracking-tighter">${pcsDisplay}</span>
                                </div>
                            `;
                        }

                        return `
                            <div class="flex flex-col items-center justify-center p-2" title="${breakdown}">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="w-2 h-2 rounded-full ${indicatorClass}"></span>
                                    ${qtyHtml}
                                </div>
                            </div>
                        `;
                    }
                    return data;
                }
            }
        ];

        const amountColumnDef = {
            data: 'total_amount',
            className: 'text-right font-mono text-[11px] font-medium text-slate-700 dark:text-slate-300',
            render: function(data) {
                return data ? 'Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(data) : 'Rp 0';
            }
        };

        const stoColumnDef = {
            data: 'sto_gap', // Use raw data for sorting
            className: 'text-center',
            render: function(data, type, row) {
                if (type === 'display') {
                    const display = row.sto_gap_display;
                    if (display === '-') return `<span class="text-gray-300 font-mono text-[10px]">-</span>`;
                    let colorClass = 'text-slate-600 dark:text-gray-400';
                    if (parseFloat(row.sto_gap) > 0) colorClass = 'text-emerald-600 dark:text-emerald-400';
                    else if (parseFloat(row.sto_gap) < 0) colorClass = 'text-rose-600 dark:text-rose-400';
                    
                    return `
                        <div class="sto-log-trigger cursor-pointer inline-flex items-center gap-2 px-3 py-1.5 rounded-xs hover:bg-slate-100 dark:hover:bg-slate-700/40 transition-all group/gap" data-id="${row.id}">
                            <span class="${colorClass} font-mono font-bold text-[13px] tracking-tight transition-transform group-hover/gap:scale-110">${display}</span>
                            <i class="fa-solid fa-clock-rotate-left text-[9px] opacity-20 group-hover/gap:opacity-80 transition-opacity"></i>
                        </div>
                    `;
                }
                return data;
            }
        };

        // Clone base columns for balance table
        let balanceColumns = [...baseColumns];
        
        const categories = <?php echo json_encode(isset($categories) ? $categories->values() : []); ?>;

        if (Array.isArray(categories) && categories.length > 0) {
            categories.forEach(cat => {
                let safeCode = cat.code.replace(/[^a-zA-Z0-9]/g, '_');
                let alias = 'usage_' + safeCode;

                if (cat.is_trial) {
                    balanceColumns.push({
                        data: alias,
                        className: 'text-center',
                        defaultContent: '-',
                        render: function(data, type, row) {
                            if (type === 'display' && row.trial_status) {
                                let badgeClass = '';
                                if (row.trial_status === 'critical') badgeClass = 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800/50';
                                else if (row.trial_status === 'warning') badgeClass = 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800/50';

                                if (badgeClass) return `<span class="px-3 py-1.5 rounded-xs border ${badgeClass} text-[10px] font-medium tracking-wider inline-block">${data}</span>`;
                            }
                            return `<span class="text-[10px] font-medium text-gray-500">${data || '-'}</span>`;
                        }
                    });
                } else {
                    balanceColumns.push({
                        data: alias,
                        className: 'text-center text-[10px] font-medium text-gray-500',
                        defaultContent: '-'
                    });
                }
            });
        }
        
        balanceColumns.push(stoColumnDef);
        balanceColumns.push(amountColumnDef);

        if (!(Array.isArray(categories) && categories.length > 0)) {
            balanceColumns.splice(balanceColumns.length - 1, 0, {
                data: null,
                defaultContent: '-',
                className: 'text-center text-gray-300 font-mono text-[10px]'
            });
        }

        // Add Action Column for Balance
        balanceColumns.push({
            data: null,
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: function(data, type, row) {
                return `
                    <button class="print-balance-button w-8 h-8 inline-flex items-center justify-center text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 hover:bg-primary-100 dark:hover:bg-primary-900/30 rounded-xs transition-all active:scale-95 border border-primary-100/50 dark:border-primary-800/30" data-id="${row.hash_id}" title="Print Balance Label">
                        <i class="fa-solid fa-print text-sm"></i>
                    </button>
                `;
            }
        });

        const table = window.defaultDataTable('#stockMonitoringTable', {
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('inventory.stockMonitoring.data') }}",
                data: function(d) {
                    d.stock_status = $('#filter_status').val();
                    d.customer_id = $('#filter_customer').val();
                    d.model_id = $('#filter_model').val();
                    d.project_status = $('#filter_project_status').val();
                }
            },
            order: [[1, 'asc']], // Default sort by Part Information
            columns: balanceColumns,
            createdRow: function(row, data, dataIndex) {
                if (data.stock_status === 'critical') {
                    $(row).addClass('bg-red-50 dark:bg-red-900/10');
                } else if (data.stock_status === 'warning') {
                    $(row).addClass('bg-amber-50 dark:bg-amber-900/10');
                } else if (data.stock_status === 'over') {
                    $(row).addClass('bg-primary-50 dark:bg-primary-900/10');
                }
            }
        });

        // Setup Usage Table
        let usageColumns = [...baseColumns.slice(0, 4)]; // Take only No, Model, Part No, Status
        
        // Add Usage specific columns
        usageColumns.push(
            {
                data: 'supplier_name',
                className: 'text-left text-[11px] font-medium text-slate-700 dark:text-gray-300',
                render: d => d || '-'
            },
            {
                data: 'rank_value',
                className: 'text-center font-mono font-medium text-slate-600 dark:text-gray-300 text-[11px]',
                render: d => d || '0'
            },
            {
                data: 'out_trial_value',
                className: 'text-center font-mono font-medium text-primary-600 dark:text-primary-400 text-[11px]',
                render: d => d || '0'
            },
            {
                data: 'gap',
                className: 'text-center font-mono font-medium text-[11px]',
                render: function(data, type, row) {
                    let val = parseFloat(data) || 0;
                    let colorClass = val >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400';
                    return `<span class="${colorClass}">${data}</span>`;
                }
            },
            {
                data: 'material_usage_status',
                className: 'text-center',
                render: function(data) {
                    let colorClass = 'bg-slate-50 text-slate-600 border-slate-100';
                    if (data === 'Loss') colorClass = 'bg-red-50 text-red-700 border-red-100 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800/50';
                    else if (data === 'Near Loss') colorClass = 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800/50';
                    else if (data === 'On Budget') colorClass = 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/50';
                    
                    return `<span class="px-2.5 py-1 rounded-xs border ${colorClass} text-[10px] font-medium tracking-wider inline-block">${data || '-'}</span>`;
                }
            }
        );

        const usageTable = window.defaultDataTable('#usageTable', {
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('inventory.stockMonitoring.data') }}",
                data: function(d) {
                    d.customer_id = $('#filter_customer').val();
                    d.model_id = $('#filter_model').val();
                    d.project_status = $('#filter_project_status').val();
                    d.usage_status = $('#filter_usage_status').val();
                }
            },
            order: [[1, 'asc']],
            columns: usageColumns,
            createdRow: function(row, data, dataIndex) {
                if (data.material_usage_status === 'Loss') {
                    $(row).addClass('bg-red-50 dark:bg-red-900/10');
                } else if (data.material_usage_status === 'Near Loss') {
                    $(row).addClass('bg-amber-50 dark:bg-amber-900/10');
                }
            }
        });

        // Mode Toggling Logic
        $('#mode-balance').click(function() {
            currentMode = 'balance';
            $(this).addClass('bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm').removeClass('text-slate-500 hover:text-slate-700 dark:text-gray-400 dark:hover:text-gray-200');
            $('#mode-usage').removeClass('bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm').addClass('text-slate-500 hover:text-slate-700 dark:text-gray-400 dark:hover:text-gray-200');
            
            $('#kpi-balance').removeClass('hidden').addClass('flex');
            $('#kpi-usage').addClass('hidden').removeClass('flex');
            
            $('#balanceView').removeClass('hidden');
            $('#usageView').addClass('hidden');
            
            $('#filter_status_container').show();
            $('#filter_usage_status_container').addClass('hidden');
            $('#filter_usage_status').val('').trigger('change.select2');
            
            // Re-adjust columns for balance table since it might have been hidden
            setTimeout(function() {
                if ($.fn.dataTable) table.columns.adjust();
            }, 50);
        });

        $('#mode-usage').click(function() {
            currentMode = 'usage';
            $(this).addClass('bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm').removeClass('text-slate-500 hover:text-slate-700 dark:text-gray-400 dark:hover:text-gray-200');
            $('#mode-balance').removeClass('bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm').addClass('text-slate-500 hover:text-slate-700 dark:text-gray-400 dark:hover:text-gray-200');
            
            $('#kpi-usage').removeClass('hidden').addClass('flex');
            $('#kpi-balance').addClass('hidden').removeClass('flex');
            
            $('#usageView').removeClass('hidden');
            $('#balanceView').addClass('hidden');
            
            $('#filter_status_container').hide();
            $('#filter_status').val('').trigger('change.select2'); // Reset stock status filter when moving to usage

            $('#filter_usage_status_container').removeClass('hidden');
            
            // Re-adjust columns for usage table
            setTimeout(function() {
                if ($.fn.dataTable) usageTable.columns.adjust();
            }, 50);
        });

        // Search from URL parameter initialization
        const urlParams = new URLSearchParams(window.location.search);
        const searchVal = urlParams.get('search');
        if (searchVal) {
            table.search(searchVal).draw();
            usageTable.search(searchVal).draw();
        }

        // Filter Toggle Logic
        $('#btnToggleFilter').on('click', function(e) {
            e.stopPropagation();
            const btn = $(this);
            const card = $('#filterCard');
            
            card.slideToggle(200);
            btn.toggleClass('bg-primary-50 text-primary-600 ring-2 ring-primary-500/50');
            
            // Close Legend if open
            if (!$('#legendPopover').hasClass('hidden')) {
                $('#legendPopover').addClass('hidden');
            }
        });
        
        // Close Filter Card when clicking outside
        $(document).click(function(e) {
            if (!$(e.target).closest('#filterCard, #btnToggleFilter, .select2-container').length) {
                $('#filterCard').slideUp(200);
                $('#btnToggleFilter').removeClass('bg-primary-50 text-primary-600 ring-2 ring-primary-500/50');
            }
        });

        $('#toggleLegend').click(function(e) {
            e.stopPropagation();
            $('#legendPopover').toggleClass('hidden');
        });

        $(document).click(function() {
            $('#legendPopover').addClass('hidden');
        });

        $('#legendPopover').click(function(e) {
            e.stopPropagation();
        });

        $(document).on('click', 'button[\\@click*="toggleSidebar"]', function() {
            setTimeout(function() {
                if ($.fn.dataTable) {
                    if (currentMode === 'balance') table.columns.adjust();
                    else usageTable.columns.adjust();
                }
            }, 300);
        });

        $(window).on('resize', function() {
            if ($.fn.dataTable) {
                if (currentMode === 'balance') table.columns.adjust();
                else usageTable.columns.adjust();
            }
        });

        // Export Excel Handler
        $('#btnExportExcel').on('click', function() {
            const params = {
                stock_status: $('#filter_status').val(),
                usage_status: $('#filter_usage_status').val(),
                customer_id: $('#filter_customer').val(),
                model_id: $('#filter_model').val(),
                project_status: $('#filter_project_status').val(),
                search: currentMode === 'balance' ? table.search() : usageTable.search()
            };
            
            const queryString = $.param(params);
            window.open("{{ route('inventory.stockMonitoring.exportExcel') }}?" + queryString, '_blank');
        });

        // Print Balance Button Handler
        $(document).on('click', '.print-balance-button', function() {
            const id = $(this).data('id');
            window.open(`{{ url('inventory/stock-monitoring') }}/${id}/print-balance`, '_blank');
        });

        // Filter Handlers
        // Initialize Select2
        $('.select2').select2({
            minimumResultsForSearch: 0, 
            width: '100%'
        });

        // Filter Handlers
        $('#filter_customer, #filter_model, #filter_project_status, #filter_status, #filter_usage_status').on('change', function() {
            if (currentMode === 'balance') table.draw();
            else usageTable.draw();
        });

        // Reset Filters
        $('#reset_filters').click(function() {
            $('#filter_customer, #filter_model, #filter_project_status, #filter_status, #filter_usage_status').val('').trigger('change.select2');
            if (currentMode === 'balance') table.draw();
            else usageTable.draw();
        });

        // Load Models Function
        function loadModels(customerId) {
            const modelSelect = $('#filter_model');
            const currentModel = modelSelect.val();

            modelSelect.empty().append('<option value="">Loading...</option>');

            $.ajax({
                url: "{{ route('inventory.master.product.getModels') }}",
                data: { 
                    customer_id: customerId,
                    for_filter: 1 
                },
                success: function(data) {
                    modelSelect.empty().append('<option value="">All Models</option>');
                    data.forEach(function(model) {
                         const isSelected = currentModel == model.id;
                         const option = new Option(model.name, model.id, isSelected, isSelected);
                         modelSelect.append(option);
                    });
                    modelSelect.trigger('change.select2');
                },
                error: function() {
                    modelSelect.empty().append('<option value="">Error Loading Models</option>');
                }
            });
        }

        // Initial Load
        loadModels(null);

        $('#reset_filters').on('click', function() {
            $('#filter_customer').val('').trigger('change.select2');
            $('#filter_model').val('').trigger('change.select2');
            $('#filter_project_status').val('').trigger('change.select2');
            $('#filter_status').val('').trigger('change.select2');
            
            table.search('').columns().search('').draw();
            usageTable.search('').columns().search('').draw();
        });

        // Dynamic Model Loading
        $('#filter_customer').on('change', function() {
            const customerId = $(this).val();
            loadModels(customerId);
        });
    });
</script>
@endpush