@extends('layouts.app')

@section('title', 'Inventory Dashboard')

{{-- Tambahkan CSS Select2 jika belum ada di layout --}}
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Styling tambahan agar Select2 sesuai dengan tema Tailwind */
    .select2-container .select2-selection--single, 
    .select2-container .select2-selection--multiple {
        border-color: #e5e7eb !important; /* gray-200 */
        border-radius: 0.5rem !important; /* rounded-lg */
        min-height: 42px !important;
        padding-top: 2px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #eff6ff !important; /* blue-50 */
        border: 1px solid #dbeafe !important; /* blue-100 */
        color: #1d4ed8 !important; /* blue-700 */
    }
</style>
@endpush

@section('content')
<div class="dashboard-container min-h-screen">
    {{-- STATS SECTION --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-3">
        @foreach([
        ['val' => number_format($stats['total_stock']), 'label' => 'Total Stock Value', 'icon' => 'fa-cubes', 'color' => 'text-blue-500', 'bg' => 'bg-blue-50'],
        ['val' => number_format($stats['material_in']), 'label' => 'Material In', 'icon' => 'fa-arrow-right-to-bracket', 'color' => 'text-emerald-500', 'bg' => 'bg-emerald-50'],
        ['val' => number_format($stats['material_out']), 'label' => 'Total Out', 'icon' => 'fa-arrow-right-from-bracket', 'color' => 'text-amber-500', 'bg' => 'bg-amber-50'],
        ['val' => number_format($stats['out_pp']), 'label' => 'Out PP', 'icon' => 'fa-industry', 'color' => 'text-indigo-500', 'bg' => 'bg-indigo-50'],
        ['val' => number_format($stats['out_event']), 'label' => 'Out Event', 'icon' => 'fa-calendar-check', 'color' => 'text-purple-500', 'bg' => 'bg-purple-50'],
        ['val' => number_format($stats['out_trial']), 'label' => 'Out Trial', 'icon' => 'fa-vial', 'color' => 'text-rose-500', 'bg' => 'bg-rose-50'],
        ] as $stat)
        <div class="stat-card flex flex-col justify-between h-full p-3 bg-white rounded-xl border border-gray-200 duration-200">
            <div class="flex gap-2">
                <div class="{{ $stat['bg'] }} {{ $stat['color'] }} w-14 h-14 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid {{ $stat['icon'] }} text-2xl"></i>
                </div>
                <div class="flex flex-col">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wide">{{ $stat['label'] }}</div>
                    <div class="value font-bold text-2xl text-slate-800 tracking-tight">{{ $stat['val'] }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- FILTER SECTION --}}
    <div class="filter-card bg-white rounded-xl border border-gray-200 mb-3 p-5">
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
                        <input type="month" id="month_picker" name="month_year" value="{{ $filters['month_year'] }}" class="modern-input w-full border-gray-300 rounded-lg h-[42px]">
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
                    <button type="button" id="btnReset" class="btn-modern-reset flex items-center justify-center gap-2 h-[38px] px-4 w-full md:w-auto border border-gray-300 rounded-lg hover:bg-gray-50">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span class="hidden md:inline">Reset</span>
                    </button>
                    <button type="button" id="btnApply" class="btn-modern-apply flex items-center justify-center gap-2 h-[38px] px-6 w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white border-0 rounded-lg">
                        <i class="fa-solid fa-filter"></i> Apply
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- MAIN GRID --}}
    <div class="grid grid-cols-12 gap-3">
        {{-- CHARTS COLUMN --}}
        <div class="col-span-12 xl:col-span-8 flex flex-col gap-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="chart-card bg-white p-5 rounded-xl border border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-4 bg-blue-500 rounded-full"></div>
                            <span class="font-bold text-xs uppercase text-slate-700 tracking-wider">Material Stock Status</span>
                        </div>
                        <button class="text-slate-400 hover:text-blue-500"><i class="fa-solid fa-ellipsis"></i></button>
                    </div>
                    <div class="h-64"><canvas id="stockStatusChart"></canvas></div>
                </div>
                <div class="chart-card bg-white p-5 rounded-xl border border-gray-200">
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="chart-card bg-white p-5 rounded-xl border border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-4 bg-emerald-500 rounded-full"></div>
                            <span class="font-bold text-xs uppercase text-slate-700 tracking-wider">Transaction Trend</span>
                        </div>
                        <button class="text-slate-400 hover:text-emerald-500"><i class="fa-solid fa-ellipsis"></i></button>
                    </div>
                    <div class="h-64"><canvas id="trendlineChart"></canvas></div>
                </div>
                <div class="chart-card bg-white p-5 rounded-xl border border-gray-200">
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
        <div class="col-span-12 xl:col-span-4 flex flex-col gap-3">
            {{-- Material Balance Table --}}
            <div class="table-container bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col h-full max-h-[400px]">
                <div class="p-4 border-b border-gray-50 bg-white sticky top-0 z-10 flex items-center justify-between">
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
                            <tr>
                                <td colspan="3" class="p-4 text-center text-slate-400 italic">No warnings found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Material Usage Table --}}
            <div class="table-container bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col h-full max-h-[400px]">
                <div class="p-4 border-b border-gray-50 bg-white sticky top-0 z-10 flex items-center justify-between">
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
                            <tr>
                                <td colspan="3" class="p-4 text-center text-slate-400 italic">No usage data found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recent History Table --}}
            <div class="table-container bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col h-full max-h-[400px]">
                <div class="p-4 border-b border-gray-50 bg-white sticky top-0 z-10 flex items-center justify-between">
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
                                    $activeClass = 'bg-slate-50 text-slate-600 border-gray-100';
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
                            <tr>
                                <td colspan="4" class="p-4 text-center text-slate-400 italic">No history found</td>
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

@push('scripts')
{{-- 1. LOAD JQUERY (Wajib ada sebelum Select2 atau script lain) --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

{{-- 2. LOAD SELECT2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- 3. LOAD CHART.JS (Penyebab error utama Anda) --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

{{-- 4. DATA SCRIPT --}}
<script type="application/json" id="dashboardData">
{
    "stockLabels": {!! json_encode(array_keys($charts['stock_grouped'])) !!},
    "stockData": {!! json_encode(array_values($charts['stock_grouped'])) !!},
    "usageModelLabels": {!! json_encode($charts['usage_model']->pluck('label')) !!},
    "usageModelData": {!! json_encode($charts['usage_model']->pluck('total')) !!},
    "trendData": {!! json_encode($charts['trendline']) !!},
    "makerLabels": {!! json_encode($charts['maker']->pluck('code')) !!},
    "makerData": {!! json_encode($charts['maker']->pluck('total')) !!}
}
</script>

{{-- 5. LOGIC SCRIPT --}}
<script>
    $(document).ready(function() {
        const chartsData = JSON.parse(document.getElementById('dashboardData').textContent);

        // Initialize Select2
        $('.select2-filter').select2({
            placeholder: 'Select...',
            allowClear: true,
            width: '100%',
            // Fix agar dropdown tidak tertutup elemen lain di Tailwind
            dropdownCssClass: 'text-sm'
        });

        $('#btnApply').on('click', () => window.location.href = window.location.pathname + "?" + $('#filterForm').serialize());
        $('#btnReset').on('click', () => window.location.href = window.location.pathname);

        // Chart 1: Stock Status (Grouped Bar)
        const stockLabels = chartsData.stockLabels;
        const stockData = chartsData.stockData;
        
        // Cek jika elemen chart ada sebelum inisialisasi
        if(document.getElementById('stockStatusChart')) {
            new Chart(document.getElementById('stockStatusChart'), {
                type: 'bar',
                data: {
                    labels: stockLabels,
                    datasets: [{
                            label: 'Critical',
                            data: stockData.map(d => d.critical),
                            backgroundColor: '#ef4444'
                        },
                        {
                            label: 'Over',
                            data: stockData.map(d => d.over),
                            backgroundColor: '#3b82f6'
                        },
                        {
                            label: 'Safe',
                            data: stockData.map(d => d.safe),
                            backgroundColor: '#10b981'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: false,
                            ticks: { font: { size: 8 } }
                        },
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        // Chart 2: Usage Models
        if(document.getElementById('usageModelChart')) {
            new Chart(document.getElementById('usageModelChart'), {
                type: 'bar',
                data: {
                    labels: chartsData.usageModelLabels,
                    datasets: [{
                        label: 'Usage',
                        data: chartsData.usageModelData,
                        backgroundColor: '#f59e0b'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { ticks: { font: { size: 8 } } }
                    }
                }
            });
        }

        // Chart 3: Trendline (Stacked Area)
        if(document.getElementById('trendlineChart')) {
            const trendData = chartsData.trendData;
            // Handle jika trendData kosong
            const dates = trendData ? [...new Set(trendData.map(d => d.transaction_date))] : [];
            const cats = trendData ? [...new Set(trendData.map(d => d.category))] : [];
            const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
            
            new Chart(document.getElementById('trendlineChart'), {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: cats.map((cat, idx) => ({
                        label: cat,
                        data: dates.map(d => (trendData.find(td => td.transaction_date === d && td.category === cat) || {
                            total: 0
                        }).total),
                        borderColor: colors[idx] || '#cbd5e1',
                        backgroundColor: (colors[idx] || '#cbd5e1') + '40',
                        fill: true,
                        tension: 0.4
                    }))
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { stacked: true } }
                }
            });
        }

        // Chart 4: Maker
        if(document.getElementById('makerChart')) {
            new Chart(document.getElementById('makerChart'), {
                type: 'bar',
                data: {
                    labels: chartsData.makerLabels,
                    datasets: [{
                        label: 'Usage',
                        data: chartsData.makerData,
                        backgroundColor: '#6366f1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    });
</script>
@endpush