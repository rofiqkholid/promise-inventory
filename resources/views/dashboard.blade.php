@extends('layouts.app')

@section('title', 'Inventory Dashboard')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
<style>
    .dashboard-container { background-color: #f8fafc; }
    
    /* Advanced Select2 Styling */
    /* Hide raw select to prevent FOUC */
    select.select2-filter {
        opacity: 0;
        height: 0;
        overflow: hidden;
        position: absolute;
        width: 0;
        z-index: -1;
    }

    .select2-container--bootstrap-5 .select2-selection {
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        background-color: #f8fafc !important;
        height: 40px !important;
        max-height: 40px !important;
        overflow: hidden !important;
        min-height: 40px !important;
        transition: all 0.2s ease;
        padding: 4px 8px !important;
        box-shadow: none !important;
        display: flex !important;
        align-items: center !important;
    }
    
    /* Remove the annoying blue outline and shadow */
    .select2-container--bootstrap-5.select2-container--focus .select2-selection,
    .select2-container--bootstrap-5.select2-container--open .select2-selection {
        outline: none !important;
        border-color: #3b82f6 !important;
        box-shadow: none !important;
    }

    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__rendered {
        padding: 0 !important;
        display: flex !important;
        align-items: center !important;
        gap: 4px !important;
        flex-wrap: wrap !important;
    }

    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        background-color: #eff6ff !important;
        border: 1px solid #dbeafe !important;
        border-radius: 6px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        color: #1e40af !important;
        margin: 0 !important;
        padding: 2px 8px !important;
        display: flex !important;
        align-items: center !important;
    }

    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
        color: #3b82f6 !important;
        margin-right: 6px !important;
        border-right: 1px solid #dbeafe !important;
        padding-right: 4px !important;
    }

    /* Search input inside Select2 */
    /* Search input inside Select2 */
    .select2-container--bootstrap-5 .select2-search__field {
        margin-top: 0 !important;
        font-size: 12px !important;
        color: #334155 !important;
        background: transparent !important;
        outline: none !important;
        box-shadow: none !important;
        resize: none !important; /* Hide resize handle */
        min-height: 0 !important;
        line-height: 1.5 !important;
        padding: 0 !important;
        vertical-align: middle !important;
    }

    .select2-container .select2-selection--multiple .select2-search__field {
        width: 100% !important;
        margin-left: 2px !important;
    }

    /* Dropdown Styling */
    .select2-container--bootstrap-5 .select2-dropdown {
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        overflow: hidden !important;
        z-index: 1060 !important;
        padding: 4px !important;
        border-top: none !important;
    }

    .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background-color: #3b82f6 !important;
        border-radius: 6px !important;
    }

    .select2-container--bootstrap-5 .select2-results__option {
        font-size: 12px !important;
        padding: 8px 12px !important;
        border-radius: 6px !important;
        margin-bottom: 2px !important;
    }

    /* Input & Labels */
    .modern-input {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background-color: #f8fafc;
        height: 40px !important;
        padding: 0 12px;
        font-size: 12px !important;
        line-height: 38px !important;
        color: #334155;
        width: 100%;
        transition: all 0.2s ease;
    }

    .modern-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        background-color: white;
    }

    /* Buttons */
    .btn-modern-reset {
        height: 38px;
        padding: 0 16px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        background-color: #ffffff;
        color: #64748b;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    .btn-modern-reset:hover {
        background-color: #f8fafc;
        border-color: #cbd5e1;
        color: #475569;
    }

    .btn-modern-apply {
        height: 38px;
        padding: 0 20px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        background-color: #2563eb;
        color: white;
        border: none;
        box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
        transition: all 0.2s ease;
    }

    .btn-modern-apply:hover {
        background-color: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 6px 8px -1px rgba(37, 99, 235, 0.3);
    }
    
    /* Custom Scrollbar for tables */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
@endsection

@section('content')
<div class="dashboard-container min-h-screen p-6 bg-slate-50">
    {{-- STATS SECTION --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 mb-8">
        @foreach([
            ['val' => number_format($stats['total_stock']), 'label' => 'Total Stock Value', 'icon' => 'fa-cubes', 'color' => 'text-blue-500', 'bg' => 'bg-blue-50'],
            ['val' => number_format($stats['material_in']), 'label' => 'Material In', 'icon' => 'fa-arrow-right-to-bracket', 'color' => 'text-emerald-500', 'bg' => 'bg-emerald-50'],
            ['val' => number_format($stats['material_out']), 'label' => 'Total Out', 'icon' => 'fa-arrow-right-from-bracket', 'color' => 'text-amber-500', 'bg' => 'bg-amber-50'],
            ['val' => number_format($stats['out_pp']), 'label' => 'Out PP', 'icon' => 'fa-industry', 'color' => 'text-indigo-500', 'bg' => 'bg-indigo-50'],
            ['val' => number_format($stats['out_event']), 'label' => 'Out Event', 'icon' => 'fa-calendar-check', 'color' => 'text-purple-500', 'bg' => 'bg-purple-50'],
            ['val' => number_format($stats['out_trial']), 'label' => 'Out Trial', 'icon' => 'fa-vial', 'color' => 'text-rose-500', 'bg' => 'bg-rose-50'],
        ] as $stat)
        <div class="stat-card flex flex-col justify-between h-full p-4 bg-white rounded-xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between mb-3">
                <div class="{{ $stat['bg'] }} {{ $stat['color'] }} w-10 h-10 rounded-lg flex items-center justify-center shadow-sm">
                    <i class="fa-solid {{ $stat['icon'] }} text-lg"></i>
                </div>
            </div>
            <div>
                <div class="value font-bold text-2xl text-slate-800 tracking-tight">{{ $stat['val'] }}</div>
                <div class="text-xs font-semibold text-slate-400 mt-1 uppercase tracking-wide">{{ $stat['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- FILTER SECTION --}}
    <div class="filter-card bg-white rounded-xl border border-slate-100 shadow-sm mb-8 p-6">
        <div class="section-title flex items-center gap-2 mb-6 text-slate-800">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                <i class="fa-solid fa-filter text-sm"></i>
            </div>
            <span class="font-bold text-sm uppercase tracking-wider">Filter Data</span>
        </div>
        <form id="filterForm">
            <div class="flex flex-col xl:flex-row gap-4 xl:items-end">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 flex-1">
                    <div class="space-y-1">
                        <label class="filter-label block text-[11px] font-bold text-slate-500 uppercase tracking-wide">Period</label>
                        <div class="relative">
                            <input type="text" id="month_picker" name="month_year" value="{{ $filters['month_year'] }}" class="modern-input w-full pl-3 pr-10 h-[40px] cursor-pointer" readonly placeholder="Select Month">
                            <i class="fa-regular fa-calendar absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="filter-label block text-[11px] font-bold text-slate-500 uppercase tracking-wide">Model</label>
                        <select class="select2-filter w-full" name="model[]" multiple>
                            @foreach($filters['models'] as $m)
                                <option value="{{ $m->id }}" {{ in_array($m->id, $filters['selected_models']) ? 'selected' : '' }}>{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="filter-label block text-[11px] font-bold text-slate-500 uppercase tracking-wide">Customer</label>
                        <select class="select2-filter w-full" name="customer[]" multiple>
                            @foreach($filters['customers'] as $c)
                                <option value="{{ $c->id }}" {{ in_array($c->id, $filters['selected_customers']) ? 'selected' : '' }}>{{ $c->code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="filter-label block text-[11px] font-bold text-slate-500 uppercase tracking-wide">Balance Status</label>
                        <select class="select2-filter w-full" name="status_balance[]" multiple>
                            <option value="Critical" {{ in_array('Critical', $filters['selected_status_balance']) ? 'selected' : '' }}>Critical</option>
                            <option value="Over" {{ in_array('Over', $filters['selected_status_balance']) ? 'selected' : '' }}>Over</option>
                            <option value="Safe" {{ in_array('Safe', $filters['selected_status_balance']) ? 'selected' : '' }}>Safe</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="filter-label block text-[11px] font-bold text-slate-500 uppercase tracking-wide">Usage Status</label>
                        <select class="select2-filter w-full" name="status_usage[]" multiple>
                            <option value="Over" {{ in_array('Over', $filters['selected_status_usage']) ? 'selected' : '' }}>Over</option>
                            <option value="Safe" {{ in_array('Safe', $filters['selected_status_usage']) ? 'selected' : '' }}>Safe</option>
                        </select>
                    </div>
                </div>
                
                {{-- Action Buttons --}}
                <div class="flex gap-2 pt-2 xl:pt-0">
                    <button type="button" id="btnReset" class="btn-modern-reset flex items-center justify-center gap-2 h-[38px] px-4 w-full md:w-auto">
                        <i class="fa-solid fa-rotate-left"></i> 
                        <span class="hidden md:inline">Reset</span>
                    </button>
                    <button type="button" id="btnApply" class="btn-modern-apply flex items-center justify-center gap-2 h-[38px] px-6 w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white border-0 shadow-md">
                        <i class="fa-solid fa-filter"></i> Apply
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- MAIN GRID --}}
    <div class="grid grid-cols-12 gap-8">
        {{-- CHARTS COLUMN --}}
        <div class="col-span-12 xl:col-span-8 flex flex-col gap-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="chart-card bg-white p-5 rounded-xl border border-slate-100 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-4 bg-blue-500 rounded-full"></div>
                            <span class="font-bold text-xs uppercase text-slate-700 tracking-wider">Material Stock Status</span>
                        </div>
                        <button class="text-slate-400 hover:text-blue-500"><i class="fa-solid fa-ellipsis"></i></button>
                    </div>
                    <div class="h-64"><canvas id="stockStatusChart"></canvas></div>
                </div>
                <div class="chart-card bg-white p-5 rounded-xl border border-slate-100 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-4 bg-amber-500 rounded-full"></div>
                            <span class="font-bold text-xs uppercase text-slate-700 tracking-wider">Usage by Models</span>
                        </div>
                        <button class="text-slate-400 hover:text-amber-500"><i class="fa-solid fa-ellipsis"></i></button>
                    </div>
                    <div class="h-64"><canvas id="usageModelChart"></canvas></div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="chart-card bg-white p-5 rounded-xl border border-slate-100 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-4 bg-emerald-500 rounded-full"></div>
                            <span class="font-bold text-xs uppercase text-slate-700 tracking-wider">Transaction Trend</span>
                        </div>
                         <button class="text-slate-400 hover:text-emerald-500"><i class="fa-solid fa-ellipsis"></i></button>
                    </div>
                    <div class="h-64"><canvas id="trendlineChart"></canvas></div>
                </div>
                <div class="chart-card bg-white p-5 rounded-xl border border-slate-100 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-4 bg-indigo-500 rounded-full"></div>
                            <span class="font-bold text-xs uppercase text-slate-700 tracking-wider">Usage by Makers</span>
                        </div>
                        <button class="text-slate-400 hover:text-indigo-500"><i class="fa-solid fa-ellipsis"></i></button>
                    </div>
                    <div class="h-64"><canvas id="makerChart"></canvas></div>
                </div>
            </div>
        </div>

        {{-- TABLES COLUMN --}}
        <div class="col-span-12 xl:col-span-4 flex flex-col gap-6">
            {{-- Material Balance Table --}}
            <div class="table-container bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden flex flex-col h-full max-h-[400px]">
                <div class="p-4 border-b border-slate-50 bg-white sticky top-0 z-10 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-500 flex items-center justify-center">
                            <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                        </div>
                        <span class="font-bold text-xs uppercase text-slate-700 tracking-wider">Balance Warnings</span>
                    </div>
                    @if(count($tables['balance']) > 0)
                    <span class="text-[10px] font-semibold bg-rose-100 text-rose-600 py-1 px-2 rounded-md">{{ count($tables['balance']) }} Items</span>
                    @endif
                </div>
                <div class="overflow-y-auto flex-1 custom-scrollbar">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 sticky top-0 z-10">
                            <tr>
                                <th class="p-3 text-left font-semibold text-slate-500 uppercase tracking-wider text-[10px]">Item No</th>
                                <th class="p-3 text-left font-semibold text-slate-500 uppercase tracking-wider text-[10px]">Cust</th>
                                <th class="p-3 text-right font-semibold text-slate-500 uppercase tracking-wider text-[10px]">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($tables['balance'] as $row)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="p-3 font-medium text-slate-700">
                                    <div class="truncate max-w-[120px]" title="{{ $row->part_no . ($row->revision ? ' - ' . $row->revision : '') }}">{{ $row->part_no }}{{ $row->revision ? ' - ' . $row->revision : '' }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $row->model_name }}</div>
                                </td>
                                <td class="p-3 text-slate-600">{{ $row->customer_code }}</td>
                                <td class="p-3 text-right">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold {{ $row->status == 'Critical' ? 'bg-rose-100 text-rose-600' : ($row->status == 'Over' ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600') }}">
                                        {{ $row->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="p-4 text-center text-slate-400 italic">No warnings found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Material Usage Table --}}
            <div class="table-container bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden flex flex-col h-full max-h-[400px]">
                <div class="p-4 border-b border-slate-50 bg-white sticky top-0 z-10 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-500 flex items-center justify-center">
                            <i class="fa-solid fa-chart-pie text-xs"></i>
                        </div>
                        <span class="font-bold text-xs uppercase text-slate-700 tracking-wider">Usage Status</span>
                    </div>
                </div>
                <div class="overflow-y-auto flex-1 custom-scrollbar">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 sticky top-0 z-10">
                            <tr>
                                <th class="p-3 text-left font-semibold text-slate-500 uppercase tracking-wider text-[10px]">Item No</th>
                                <th class="p-3 text-left font-semibold text-slate-500 uppercase tracking-wider text-[10px]">Cust</th>
                                <th class="p-3 text-right font-semibold text-slate-500 uppercase tracking-wider text-[10px]">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($tables['usage'] as $row)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="p-3 font-medium text-slate-700">
                                    <div class="truncate max-w-[120px]" title="{{ $row->part_no . ($row->revision ? ' - ' . $row->revision : '') }}">{{ $row->part_no }}{{ $row->revision ? ' - ' . $row->revision : '' }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $row->model_name }}</div>
                                </td>
                                <td class="p-3 text-slate-600">{{ $row->customer_code }}</td>
                                <td class="p-3 text-right">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold {{ $row->status == 'Over' ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600' }}">
                                        {{ $row->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="p-4 text-center text-slate-400 italic">No usage data found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recent History Table --}}
            <div class="table-container bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden flex flex-col h-full max-h-[400px]">
                <div class="p-4 border-b border-slate-50 bg-white sticky top-0 z-10 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center">
                            <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                        </div>
                        <span class="font-bold text-xs uppercase text-slate-700 tracking-wider">Recent Transactions</span>
                    </div>
                </div>
                <div class="overflow-y-auto flex-1 custom-scrollbar">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 sticky top-0 z-10">
                            <tr>
                                <th class="p-3 text-left font-semibold text-slate-500 uppercase tracking-wider text-[10px]">Item</th>
                                <th class="p-3 text-center font-semibold text-slate-500 uppercase tracking-wider text-[10px]">Type</th>
                                <th class="p-3 text-right font-semibold text-slate-500 uppercase tracking-wider text-[10px]">Qty</th>
                                <th class="p-3 text-right font-semibold text-slate-500 uppercase tracking-wider text-[10px]">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($tables['history'] as $row)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="p-3 font-medium text-slate-700">
                                    <div class="truncate max-w-[100px]" title="{{ $row->part_no . ($row->revision ? ' - ' . $row->revision : '') }}">{{ $row->part_no }}{{ $row->revision ? ' - ' . $row->revision : '' }}</div>
                                </td>
                                <td class="p-3 text-center">
                                    @php
                                        $cat = strtolower($row->category);
                                        $activeClass = 'bg-slate-50 text-slate-600 border-slate-100';
                                        if (Str::contains($cat, 'in')) $activeClass = 'bg-emerald-50 text-emerald-600 border-emerald-100';
                                        elseif (Str::contains($cat, 'trial')) $activeClass = 'bg-rose-50 text-rose-600 border-rose-100';
                                        elseif (Str::contains($cat, 'event')) $activeClass = 'bg-purple-50 text-purple-600 border-purple-100';
                                        elseif (Str::contains($cat, 'pp')) $activeClass = 'bg-indigo-50 text-indigo-600 border-indigo-100';
                                        elseif (Str::contains($cat, 'out')) $activeClass = 'bg-amber-50 text-amber-600 border-amber-100';
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide border {{ $activeClass }}">
                                        {{ $row->category }}
                                    </span>
                                </td>
                                <td class="p-3 text-right font-mono font-medium text-slate-700">
                                    {{ number_format($row->qty * $row->pcs_per_unit) }}
                                </td>
                                <td class="p-3 text-right text-[10px] text-slate-500 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($row->transaction_date)->format('d/m/y') }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="p-4 text-center text-slate-400 italic">No history found</td></tr>
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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        flatpickr("#month_picker", {
            plugins: [new monthSelectPlugin({ shorthand: true, dateFormat: "Y-m", altFormat: "F Y", theme: "light" })]
        });
        $('.select2-filter').select2({ placeholder: 'Select...', allowClear: true, width: '100%', theme: 'bootstrap-5' });
        
        $('#btnApply').on('click', () => window.location.href = window.location.pathname + "?" + $('#filterForm').serialize());
        $('#btnReset').on('click', () => window.location.href = window.location.pathname);

        // Chart 1: Stock Status (Grouped Bar)
        const stockLabels = {!! json_encode(array_keys($charts['stock_grouped'])) !!};
        const stockData = {!! json_encode(array_values($charts['stock_grouped'])) !!};
        new Chart(document.getElementById('stockStatusChart'), {
            type: 'bar',
            data: {
                labels: stockLabels,
                datasets: [
                    { label: 'Critical', data: stockData.map(d => d.critical), backgroundColor: '#ef4444' },
                    { label: 'Over', data: stockData.map(d => d.over), backgroundColor: '#3b82f6' },
                    { label: 'Safe', data: stockData.map(d => d.safe), backgroundColor: '#10b981' }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { x: { stacked: false, ticks: {font:{size:8}} }, y: { beginAtZero: true } } }
        });

        // Chart 2: Usage Models
        new Chart(document.getElementById('usageModelChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($charts['usage_model']->pluck('label')) !!},
                datasets: [{ label: 'Usage', data: {!! json_encode($charts['usage_model']->pluck('total')) !!}, backgroundColor: '#f59e0b' }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { x: { ticks: {font:{size:8}} } } }
        });

        // Chart 3: Trendline (Stacked Area)
        const trendData = {!! json_encode($charts['trendline']) !!};
        const dates = [...new Set(trendData.map(d => d.transaction_date))];
        const cats = [...new Set(trendData.map(d => d.category))];
        const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
        new Chart(document.getElementById('trendlineChart'), {
            type: 'line',
            data: {
                labels: dates,
                datasets: cats.map((cat, idx) => ({
                    label: cat,
                    data: dates.map(d => (trendData.find(td => td.transaction_date === d && td.category === cat) || {total:0}).total),
                    borderColor: colors[idx],
                    backgroundColor: colors[idx] + '40',
                    fill: true,
                    tension: 0.4
                }))
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { stacked: true } } }
        });

        // Chart 4: Maker
        new Chart(document.getElementById('makerChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($charts['maker']->pluck('code')) !!},
                datasets: [{ label: 'Usage', data: {!! json_encode($charts['maker']->pluck('total')) !!}, backgroundColor: '#6366f1' }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    });
</script>
@endpush