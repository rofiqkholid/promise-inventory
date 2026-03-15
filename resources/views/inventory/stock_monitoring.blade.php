@extends('layouts.app')
@section('title', 'Stock Monitoring')
@section('page_title', 'Stock Monitoring')
@section('header-title', 'Stock Monitoring')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter leading-none">Stock Monitoring</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Real-time status of parts balance and consumption.</p>
        </div>

        {{-- Action Toolbar --}}
        <div class="flex items-center gap-2 mt-4 sm:mt-0 relative">
            <button id="btnToggleFilter" class="h-10 px-4 rounded-xs bg-white dark:bg-gray-800 text-slate-600 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 border border-slate-200 dark:border-gray-700 flex items-center justify-center transition-all duration-200" title="Toggle Filters">
                <i class="fa-solid fa-filter text-sm mr-2.5"></i>
                <span class="text-[11px] font-bold uppercase tracking-wider">Filters</span>
            </button>

            <button id="btnExportExcel" class="h-10 px-4 rounded-xs bg-white dark:bg-gray-800 text-emerald-600 hover:text-emerald-700 dark:text-emerald-500 dark:hover:text-emerald-400 border border-slate-200 dark:border-gray-700 flex items-center justify-center transition-all duration-200" title="Export Excel">
                <i class="fa-solid fa-file-excel text-sm mr-2.5"></i>
                <span class="text-[11px] font-bold uppercase tracking-wider">Export Excel</span>
            </button>
            
            <div class="relative">
                <button id="toggleLegend" class="h-10 px-4 rounded-xs bg-white dark:bg-gray-800 text-slate-600 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 border border-slate-200 dark:border-gray-700 flex items-center justify-center transition-all duration-200" title="Legend & Help">
                    <i class="fa-solid fa-circle-question text-sm mr-2.5"></i>
                    <span class="text-[11px] font-bold uppercase tracking-wider">Legend</span>
                </button>

                {{-- Legend Popover Content --}}
                <div id="legendPopover" class="hidden absolute right-0 top-full mt-2 w-72 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 p-6 z-50">
                    <h4 class="font-bold text-gray-900 dark:text-gray-100 text-xs uppercase tracking-wider mb-2">Stock Status</h4>
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-primary-500 mr-2 flex-shrink-0"></span>
                            <div class="text-gray-600 dark:text-gray-300 font-medium">Over <span class="text-gray-400 text-[10px] tracking-tighter">(&gt; Max)</span></div>
                        </div>
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-2 flex-shrink-0"></span>
                            <div class="text-gray-600 dark:text-gray-300 font-medium">Safe <span class="text-gray-400 text-[10px] tracking-tighter">(Min - Max)</span></div>
                        </div>
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 mr-2 flex-shrink-0"></span>
                            <div class="text-gray-600 dark:text-gray-300 font-medium">Warning <span class="text-gray-400 text-[10px] tracking-tighter">(Min-30 - Min-1)</span></div>
                        </div>
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500 mr-2 flex-shrink-0 animate-pulse"></span>
                            <div class="text-gray-600 dark:text-gray-300 font-medium">Critical <span class="text-gray-400 text-[10px] tracking-tighter">(&lt; Min-30)</span></div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 dark:border-gray-700 my-3"></div>

                    <h4 class="font-bold text-gray-900 dark:text-gray-100 text-xs uppercase tracking-wider mb-3">Trial Validation</h4>
                    <div class="space-y-2">
                        <div class="flex items-center text-xs">
                            <span class="px-1.5 py-0.5 rounded-xs bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 font-bold mr-2 text-[10px]">WARN</span>
                            <div class="text-gray-600 dark:text-gray-300 font-medium">Near Limit <span class="text-gray-400 text-[10px] tracking-tighter">(&gt; Limit-50)</span></div>
                        </div>
                        <div class="flex items-center text-xs">
                            <span class="px-1.5 py-0.5 rounded-xs bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300 font-bold mr-2 text-[10px]">CRIT</span>
                            <div class="text-gray-600 dark:text-gray-300 font-medium">Over Limit <span class="text-gray-400 text-[10px] tracking-tighter">(&gt; Limit)</span></div>
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
                    <label class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] leading-tight">Customer</label>
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
                    <label class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] leading-tight">Model</label>
                    <div class="w-full">
                        <select id="filter_model" class="select2 w-full">
                            <option value="">All Models</option>
                        </select>
                    </div>
                </div>

                <!-- Status -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] leading-tight">Project Status</label>
                    <div class="w-full">
                        <select id="filter_project_status" class="select2 w-full">
                            <option value="">All Statuses</option>
                            @foreach($project_statuses as $ps)
                                <option value="{{ $ps }}">{{ $ps }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Stock Status -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] leading-tight">Stock Status</label>
                    <div class="w-full">
                        <select id="filter_status" class="select2 w-full">
                            <option value="">All Status</option>
                            <option value="safe">Safe Stock</option>
                            <option value="warning">Warning</option>
                            <option value="danger">Critical</option>
                            <option value="over">Over Stock</option>
                        </select>
                    </div>
                </div>

            </div>

            <div class="flex gap-2 pt-2 xl:pt-0">
                <button type="button" id="reset_filters" class="flex items-center justify-center gap-2 h-10 px-6 w-full md:w-auto bg-slate-50 dark:bg-gray-700 text-slate-600 dark:text-gray-300 border border-slate-100 dark:border-gray-600 rounded-xs transition-all text-[10px] font-bold uppercase tracking-widest hover:bg-slate-100 active:scale-95">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </button>
            </div>
        </div>
    </div>
    
    {{-- Individual KPI Cards - Forced Single Row --}}
    <div class="flex flex-nowrap gap-3 mb-4 overflow-x-auto pb-2 scrollbar-hide">
        <!-- Total -->
        <div class="flex-none w-[180px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all hover:bg-slate-50/50 dark:hover:bg-gray-700/50">
            <div class="w-10 h-10 rounded-xs bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 flex items-center justify-center text-slate-500 text-lg">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div>
                <div class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Total Parts</div>
                <div class="text-xl font-black text-slate-800 dark:text-white leading-none tracking-tighter">{{ number_format($stats['total'] ?? 0) }}</div>
            </div>
        </div>

        <!-- Safe -->
        <div class="flex-none w-[180px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all">
            <div class="w-10 h-10 rounded-xs bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-lg">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="text-[9px] font-bold text-emerald-600 dark:text-emerald-500 uppercase tracking-widest mb-1">Safe Stock</div>
                <div class="text-xl font-black text-slate-800 dark:text-white leading-none tracking-tighter">{{ number_format($stats['safe'] ?? 0) }}</div>
            </div>
        </div>

        <!-- Warning -->
        <div class="flex-none w-[180px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all">
            <div class="w-10 h-10 rounded-xs bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800/50 flex items-center justify-center text-amber-600 dark:text-amber-400 text-lg">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <div class="text-[9px] font-bold text-amber-600 dark:text-amber-500 uppercase tracking-widest mb-1">Warning</div>
                <div class="text-xl font-black text-slate-800 dark:text-white leading-none tracking-tighter">{{ number_format($stats['warning'] ?? 0) }}</div>
            </div>
        </div>

        <!-- Critical -->
        <div class="flex-none w-[180px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all">
            <div class="w-10 h-10 rounded-xs bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800/50 flex items-center justify-center text-red-600 dark:text-red-400 text-lg">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div>
                <div class="text-[9px] font-bold text-red-600 dark:text-red-500 uppercase tracking-widest mb-1">Critical</div>
                <div class="text-xl font-black text-slate-800 dark:text-white leading-none tracking-tighter">{{ number_format($stats['danger'] ?? 0) }}</div>
            </div>
        </div>

        <!-- Over Stock -->
        <div class="flex-none w-[180px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all">
            <div class="w-10 h-10 rounded-xs bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800/50 flex items-center justify-center text-primary-600 dark:text-primary-400 text-lg">
                <i class="fa-solid fa-arrow-trend-up"></i>
            </div>
            <div>
                <div class="text-[9px] font-bold text-primary-600 dark:text-primary-500 uppercase tracking-widest mb-1">Over Stock</div>
                <div class="text-xl font-black text-slate-800 dark:text-white leading-none tracking-tighter">{{ number_format($stats['over'] ?? 0) }}</div>
            </div>
        </div>
    </div>



    {{-- DataTable --}}
    <div class="bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 overflow-hidden">
        <x-table id="stockMonitoringTable">
            <thead>
                <tr>
                    <th rowspan="2" class="w-12 border-b border-slate-200 dark:border-gray-700 text-center font-bold uppercase tracking-wider text-[10px]">No</th>
                    <th rowspan="2" class="border-b border-slate-200 dark:border-gray-700 text-left font-bold uppercase tracking-wider text-[10px]">Part Information</th>
                    <th rowspan="2" class="border-b border-slate-200 dark:border-gray-700 text-left font-bold uppercase tracking-wider text-[10px]">Status</th>
                    <th colspan="{{ 2 + max(1, $categories->count()) + 2 }}" class="border-b border-slate-200 dark:border-gray-700 text-center font-bold uppercase tracking-wider text-[10px] bg-slate-50/50 dark:bg-slate-900/50">Current Balance & Movement</th>
                    <th rowspan="2" class="w-20 border-b border-slate-200 dark:border-gray-700 text-center font-bold uppercase tracking-wider text-[10px]">Action</th>
                </tr>
                <tr>
                    <th class="border-b border-slate-200 dark:border-gray-700 text-center font-bold uppercase tracking-widest text-[9px] bg-slate-50/50 dark:bg-slate-900/30">Min Stock</th>
                    <th class="border-b border-slate-200 dark:border-gray-700 text-center font-bold uppercase tracking-widest text-[9px] bg-slate-50/50 dark:bg-slate-900/30">Balance</th>

                    @foreach($categories as $cat)
                    <th class="border-b border-slate-200 dark:border-gray-700 text-center font-bold uppercase tracking-widest text-[9px] whitespace-nowrap">{{ $cat->code }}</th>
                    @endforeach
                    
                    <th class="border-b border-slate-200 dark:border-gray-700 text-center font-bold uppercase tracking-widest text-[9px] bg-amber-50/30 dark:bg-amber-900/10">STO GAP</th>
                    <th class="border-b border-slate-200 dark:border-gray-700 text-center font-bold uppercase tracking-widest text-[9px] bg-emerald-50/30 dark:bg-emerald-900/10">Amount</th>

                    @if($categories->count() === 0)
                    <th class="border-b border-slate-200 dark:border-gray-700 text-center font-bold uppercase tracking-widest text-[9px]">-</th>
                    @endif
                </tr>
            </thead>
            <tbody></tbody>
        </x-table>
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
        // DataTable Configuration
        let columns = [{
                data: null,
                orderable: false, // Not sortable
                searchable: false,
                render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1
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
                                        <span class="font-bold text-slate-700 dark:text-slate-200 leading-none uppercase tracking-tight group-hover/part:text-primary-600 transition-colors">
                                            ${partNoDisplay}
                                        </span>
                                        <span class="text-[9px] text-slate-400 font-medium uppercase truncate max-w-[180px] mt-1 group-hover/part:text-slate-500 transition-colors">
                                            ${row.part_name || ''}
                                        </span>
                                    </div>
                                </div>
                            `;
                        }
                        return `
                            <div class="flex flex-col pl-3">
                                <span class="font-bold text-slate-700 dark:text-slate-200 leading-none uppercase tracking-tight">${partNoDisplay}</span>
                                <span class="text-[9px] text-slate-400 font-medium uppercase truncate max-w-[180px] mt-1">${row.part_name || ''}</span>
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
                    else if (data === 'Allsize OK') colorClass = 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/50';
                    else if (data === 'Allsize NG') colorClass = 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800/50';
                    else if (data === 'Damage') colorClass = 'bg-red-50 text-red-700 border-red-100 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800/50';
                    else if (data === 'Other') colorClass = 'bg-slate-50 text-slate-600 border-slate-100 dark:bg-slate-900/20 dark:text-slate-400 dark:border-slate-800/50';
                    
                    return `<span class="px-3 py-1.5 rounded-xs border ${colorClass} text-[10px] font-bold uppercase tracking-widest inline-block">${data}</span>`;
                }
            },
            {
                data: 'min_stock',
                className: 'text-center font-bold text-slate-500 text-[11px]',
                render: (data) => data || '0'
            },
            {
                data: 'balance_pcs',
                className: 'text-center',
                render: function(data, type, row) {
                    if (type === 'display') {
                        let indicatorClass = 'bg-gray-400';
                        let textColorClass = 'text-gray-900 dark:text-white';
                        const status = row.stock_status || 'default';

                        if (status === 'safe') {
                            indicatorClass = 'bg-emerald-500';
                            textColorClass = 'text-emerald-700 dark:text-emerald-400';
                        } else if (status === 'warning') {
                            indicatorClass = 'bg-amber-500';
                            textColorClass = 'text-amber-700 dark:text-amber-400';
                        } else if (status === 'danger') {
                            indicatorClass = 'bg-red-500 animate-pulse';
                            textColorClass = 'text-red-700 dark:text-red-400';
                        } else if (status === 'over') {
                            indicatorClass = 'bg-primary-500';
                            textColorClass = 'text-primary-700 dark:text-primary-400';
                        }

                        const unitSub = row.current_qty + ' ' + row.balance_unit;
                        const breakdown = `In: ${row.total_in} | Out: ${row.total_out} | STO: ${row.sto_gap_plain}`;

                        return `
                            <div class="flex flex-col items-center justify-center p-2" title="${breakdown}">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="w-2 h-2 rounded-full ${indicatorClass}"></span>
                                    <span class="font-black ${textColorClass} text-xs tracking-tight">${data}</span>
                                </div>
                                <span class="text-[9px] text-gray-400 font-bold uppercase tracking-widest opacity-80">${row.balance_unit} (${row.current_qty})</span>
                            </div>
                        `;
                    }
                    return data;
                }
            }
        ];

        const amountColumnDef = {
            data: 'total_amount',
            className: 'text-right font-mono text-[11px] font-bold text-slate-700 dark:text-slate-300',
            render: function(data) {
                return data ? new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(data) : '0';
            }
        };

        const stoColumnDef = {
            data: 'sto_gap', // Use raw data for sorting
            className: 'text-center',
            render: function(data, type, row) {
                if (type === 'display') {
                    const display = row.sto_gap_display;
                    if (display === '-') return `<span class="text-gray-300 font-mono text-[10px]">-</span>`;
                    let colorClass = 'text-slate-500 dark:text-gray-400';
                    if (parseFloat(row.sto_gap) > 0) colorClass = 'text-emerald-600 dark:text-emerald-400';
                    else if (parseFloat(row.sto_gap) < 0) colorClass = 'text-rose-600 dark:text-rose-400';
                    
                    return `
                        <div class="sto-log-trigger cursor-pointer inline-flex items-center gap-2 px-3 py-1.5 rounded-xs hover:bg-slate-100 dark:hover:bg-slate-700/40 transition-all group/gap" data-id="${row.id}">
                            <span class="${colorClass} font-mono font-black text-[13px] tracking-tight transition-transform group-hover/gap:scale-110">${display}</span>
                            <i class="fa-solid fa-clock-rotate-left text-[9px] opacity-20 group-hover/gap:opacity-80 transition-opacity"></i>
                        </div>
                    `;
                }
                return data;
            }
        };

        const categories = <?php echo json_encode(isset($categories) ? $categories->values() : []); ?>;

        if (Array.isArray(categories) && categories.length > 0) {
            categories.forEach(cat => {
                let safeCode = cat.code.replace(/[^a-zA-Z0-9]/g, '_');
                let alias = 'usage_' + safeCode;

                if (cat.is_trial) {
                    columns.push({
                        data: alias,
                        className: 'text-center',
                        defaultContent: '-',
                        render: function(data, type, row) {
                            if (type === 'display' && row.trial_status) {
                                let badgeClass = '';
                                if (row.trial_status === 'danger') badgeClass = 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800/50';
                                else if (row.trial_status === 'warning') badgeClass = 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800/50';

                                if (badgeClass) return `<span class="px-3 py-1.5 rounded-xs border ${badgeClass} text-[10px] font-bold uppercase tracking-widest inline-block">${data}</span>`;
                            }
                            return `<span class="text-[10px] font-medium text-gray-500">${data || '-'}</span>`;
                        }
                    });
                } else {
                    columns.push({
                        data: alias,
                        className: 'text-center text-[10px] font-medium text-gray-500',
                        defaultContent: '-'
                    });
                }
            });
        }
        
        columns.push(stoColumnDef);
        columns.push(amountColumnDef);

        if (!(Array.isArray(categories) && categories.length > 0)) {
            columns.splice(columns.length - 1, 0, {
                data: null,
                defaultContent: '-',
                className: 'text-center text-gray-300 font-mono text-[10px]'
            });
        }

        // Add Action Column
        columns.push({
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
            columns: columns,
            createdRow: function(row, data, dataIndex) {
                if (data.stock_status === 'danger') {
                    $(row).addClass('bg-red-50 dark:bg-red-900/10');
                } else if (data.stock_status === 'warning') {
                    $(row).addClass('bg-amber-50 dark:bg-amber-900/10');
                } else if (data.stock_status === 'over') {
                    $(row).addClass('bg-primary-50 dark:bg-primary-900/10');
                }
            }
        });

        // Search from URL parameter initialization
        const urlParams = new URLSearchParams(window.location.search);
        const searchVal = urlParams.get('search');
        if (searchVal) {
            table.search(searchVal).draw();
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
                    $.fn.dataTable.tables({
                        visible: true,
                        api: true
                    }).columns.adjust();
                }
            }, 300);
        });

        $(window).on('resize', function() {
            if ($.fn.dataTable) {
                $.fn.dataTable.tables({
                    visible: true,
                    api: true
                }).columns.adjust();
            }
        });

        // Export Excel Handler
        $('#btnExportExcel').on('click', function() {
            const params = {
                stock_status: $('#filter_status').val(),
                customer_id: $('#filter_customer').val(),
                model_id: $('#filter_model').val(),
                project_status: $('#filter_project_status').val(),
                search: table.search()
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
        $('#filter_status, #filter_customer, #filter_model, #filter_project_status').on('change', function() {
            table.draw();
        });

        // Load Models Function
        function loadModels(customerId) {
            const modelSelect = $('#filter_model');
            const currentModel = modelSelect.val();

            modelSelect.empty().append('<option value="">Loading...</option>');

            $.ajax({
                url: "{{ route('inventory.master.product.getModels') }}",
                data: { customer_id: customerId },
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
            $('#filter_customer').val('').trigger('change');
            
            $('#filter_model').val(null).trigger('change.select2');
            $('#filter_project_status').val('').trigger('change');
            
            table.search('').draw();
        });

        // Dynamic Model Loading
        $('#filter_customer').on('change', function() {
            const customerId = $(this).val();
            loadModels(customerId);
        });
    });
</script>
@endpush