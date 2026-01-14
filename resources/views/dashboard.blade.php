@extends('layouts.app')

@section('title', 'Inventory Dashboard')

@section('content')
<div class="dashboard-container min-h-screen">
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
                <div class="{{ $stat['bg'] }} {{ $stat['color'] }} w-12 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid {{ $stat['icon'] }} text-xl"></i>
                </div>
                <div class="flex flex-col">
                    <div class="text-sm font-base text-slate-400 tracking-wide">{{ $stat['label'] }}</div>
                    <div class="value font-bold text-2xl text-slate-800 tracking-tight">{{ $stat['val'] }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="filter-card bg-white rounded-xl border border-gray-200 p-4">
        <div class="section-title flex items-center gap-2 mb-6 text-slate-800">
            <span class="font-bold text-md tracking-wider">Filter Data</span>
        </div>
        <form id="filterForm">
            <div class="flex flex-col xl:flex-row gap-4 xl:items-end">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 flex-1">
                    <div class="space-y-1">
                        <label class="filter-label block text-sm text-slate-500 tracking-wide">Period</label>
                        <input type="month" id="month_picker" name="month_year" value="{{ $filters['month_year'] }}" class="modern-input w-full border-gray-300 rounded-lg h-[42px]">
                    </div>
                    <div class="space-y-1">
                        <label class="filter-label block text-sm text-slate-500 tracking-wide">Model</label>
                        <select id="filterModel" class="w-full" name="model[]" multiple>
                            @foreach($filters['initial_models'] as $m)
                            <option value="{{ $m->id }}" selected>{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="filter-label block text-sm text-slate-500 tracking-wide">Customer</label>
                        <select id="filterCustomer" class="w-full" name="customer[]" multiple>
                            @foreach($filters['initial_customers'] as $c)
                            <option value="{{ $c->id }}" selected>{{ $c->code }} - {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="filter-label block text-sm text-slate-500 tracking-wide">Balance Status</label>
                        <select id="filterBalance" class="w-full" name="status_balance[]" multiple>
                            <option value="Critical" {{ in_array('Critical', $filters['selected_status_balance']) ? 'selected' : '' }}>Critical</option>
                            <option value="Over" {{ in_array('Over', $filters['selected_status_balance']) ? 'selected' : '' }}>Over</option>
                            <option value="Safe" {{ in_array('Safe', $filters['selected_status_balance']) ? 'selected' : '' }}>Safe</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="filter-label block text-sm text-slate-500 tracking-wide">Usage Status</label>
                        <select id="filterUsage" class="w-full" name="status_usage[]" multiple>
                            <option value="Over" {{ in_array('Over', $filters['selected_status_usage']) ? 'selected' : '' }}>Over</option>
                            <option value="Safe" {{ in_array('Safe', $filters['selected_status_usage']) ? 'selected' : '' }}>Safe</option>
                        </select>
                    </div>
                </div>

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

    <div class="grid grid-cols-12 gap-3 mt-3">
        <div class="col-span-12 xl:col-span-8 flex flex-col gap-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="chart-card bg-white p-5 rounded-xl border border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-sm text-slate-700 tracking-wider">Material Stock Status</span>
                        </div>
                        <button class="text-slate-400 hover:text-blue-500"><i class="fa-solid fa-ellipsis"></i></button>
                    </div>
                    <div class="h-64"><canvas id="stockStatusChart"></canvas></div>
                </div>
                <div class="chart-card bg-white p-5 rounded-xl border border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-sm text-slate-700 tracking-wider">Usage by Models</span>
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
                            <span class="font-bold text-sm text-slate-700 tracking-wider">Transaction Trend</span>
                        </div>
                        <button class="text-slate-400 hover:text-emerald-500"><i class="fa-solid fa-ellipsis"></i></button>
                    </div>
                    <div class="h-64"><canvas id="trendlineChart"></canvas></div>
                </div>
                <div class="chart-card bg-white p-5 rounded-xl border border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-sm text-slate-700 tracking-wider">Usage by Makers</span>
                        </div>
                        <button class="text-slate-400 hover:text-indigo-500"><i class="fa-solid fa-ellipsis"></i></button>
                    </div>
                    <div class="h-64"><canvas id="makerChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="col-span-12 xl:col-span-4 flex flex-col gap-3">
            <div class="table-container bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col h-full max-h-[400px]">
                <div class="p-4 border-b border-gray-50 bg-white sticky top-0 z-10 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-500 flex items-center justify-center">
                            <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                        </div>
                        <span class="font-bold text-xs text-slate-700 tracking-wider">Balance Warnings</span>
                    </div>
                    @if(count($tables['balance']) > 0)
                    <span class="text-[10px] font-semibold bg-rose-100 text-rose-600 py-1 px-2 rounded-md">{{ count($tables['balance']) }} Items</span>
                    @endif
                </div>
                <div class="overflow-y-auto flex-1 custom-scrollbar">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 sticky top-0 z-10">
                            <tr>
                                <th class="p-3 text-left font-semibold text-slate-500 tracking-wider text-[10px]">Item No</th>
                                <th class="p-3 text-left font-semibold text-slate-500 tracking-wider text-[10px]">Cust</th>
                                <th class="p-3 text-right font-semibold text-slate-500 tracking-wider text-[10px]">Status</th>
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

            <div class="table-container bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col h-full max-h-[400px]">
                <div class="p-4 border-b border-gray-50 bg-white sticky top-0 z-10 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-500 flex items-center justify-center">
                            <i class="fa-solid fa-chart-pie text-xs"></i>
                        </div>
                        <span class="font-bold text-xs text-slate-700 tracking-wider">Usage Status</span>
                    </div>
                </div>
                <div class="overflow-y-auto flex-1 custom-scrollbar">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 sticky top-0 z-10">
                            <tr>
                                <th class="p-3 text-left font-semibold text-slate-500 tracking-wider text-[10px]">Item No</th>
                                <th class="p-3 text-left font-semibold text-slate-500 tracking-wider text-[10px]">Cust</th>
                                <th class="p-3 text-right font-semibold text-slate-500 tracking-wider text-[10px]">Status</th>
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

            <div class="table-container bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col h-full max-h-[400px]">
                <div class="p-4 border-b border-gray-50 bg-white sticky top-0 z-10 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center">
                            <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                        </div>
                        <span class="font-bold text-xs text-slate-700 tracking-wider">Recent Transactions</span>
                    </div>
                </div>
                <div class="overflow-y-auto flex-1 custom-scrollbar">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 sticky top-0 z-10">
                            <tr>
                                <th class="p-3 text-left font-semibold text-slate-500 tracking-wider text-[10px]">Item</th>
                                <th class="p-3 text-center font-semibold text-slate-500 tracking-wider text-[10px]">Type</th>
                                <th class="p-3 text-right font-semibold text-slate-500 tracking-wider text-[10px]">Qty</th>
                                <th class="p-3 text-right font-semibold text-slate-500 tracking-wider text-[10px]">Date</th>
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
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold tracking-wide border {{ $activeClass }}">
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
<script>
    $(document).ready(function() {
        const chartsData = {
            stockLabels: ['Model X - CUST01', 'Model Y - CUST02', 'Model Z - CUST01', 'Model A - CUST03'],
            stockData: [{
                    critical: 5,
                    over: 10,
                    safe: 85
                },
                {
                    critical: 2,
                    over: 5,
                    safe: 93
                },
                {
                    critical: 0,
                    over: 20,
                    safe: 80
                },
                {
                    critical: 8,
                    over: 2,
                    safe: 90
                }
            ],
            usageModelLabels: ['Model X', 'Model Y', 'Model Z', 'Model A', 'Model B'],
            usageModelData: [1200, 1900, 800, 1500, 2100],
            trendData: [{
                    transaction_date: '2024-01-01',
                    category: 'IN',
                    total: 50
                },
                {
                    transaction_date: '2024-01-01',
                    category: 'OUT-PP',
                    total: 30
                },
                {
                    transaction_date: '2024-01-02',
                    category: 'IN',
                    total: 60
                },
                {
                    transaction_date: '2024-01-02',
                    category: 'OUT-PP',
                    total: 40
                },
                {
                    transaction_date: '2024-01-03',
                    category: 'IN',
                    total: 45
                },
                {
                    transaction_date: '2024-01-03',
                    category: 'OUT-PP',
                    total: 55
                },
                {
                    transaction_date: '2024-01-04',
                    category: 'IN',
                    total: 80
                },
                {
                    transaction_date: '2024-01-04',
                    category: 'OUT-PP',
                    total: 20
                }
            ],
            makerLabels: ['MKER01', 'MKER02', 'MKER03', 'MKER04'],
            makerData: [5000, 3000, 4500, 2000]
        };
        $('#filterModel').select2({
            dropdownParent: $('#filterModel').parent(),
            width: '100%',
            placeholder: 'Select Model...',
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
                        _token: '{{ csrf_token() }}'
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.results,
                        pagination: data.pagination
                    };
                },
                cache: true
            }
        });

        $('#filterCustomer').select2({
            dropdownParent: $('#filterCustomer').parent(),
            width: '100%',
            placeholder: 'Select Customer...',
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
                processResults: function(data) {
                    return {
                        results: data.results,
                        pagination: data.pagination
                    };
                },
                cache: true
            }
        });

        $('#filterBalance').select2({
            placeholder: 'Select Balance Status',
            allowClear: true,
            width: '100%'
        });

        $('#filterUsage').select2({
            placeholder: 'Select Usage Status',
            allowClear: true,
            width: '100%'
        });

        $('#btnApply').on('click', () => window.location.href = window.location.pathname + "?" + $('#filterForm').serialize());
        $('#btnReset').on('click', () => window.location.href = window.location.pathname);

        if (document.getElementById('stockStatusChart')) {
            new Chart(document.getElementById('stockStatusChart'), {
                type: 'bar',
                data: {
                    labels: chartsData.stockLabels,
                    datasets: [{
                            label: 'Critical',
                            data: chartsData.stockData.map(d => d.critical),
                            backgroundColor: '#ef4444'
                        },
                        {
                            label: 'Over',
                            data: chartsData.stockData.map(d => d.over),
                            backgroundColor: '#3b82f6'
                        },
                        {
                            label: 'Safe',
                            data: chartsData.stockData.map(d => d.safe),
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
                            ticks: {
                                font: {
                                    size: 8
                                }
                            }
                        },
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        if (document.getElementById('usageModelChart')) {
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
                        x: {
                            ticks: {
                                font: {
                                    size: 8
                                }
                            }
                        }
                    }
                }
            });
        }

        if (document.getElementById('trendlineChart')) {
            const trendData = chartsData.trendData;
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
                    scales: {
                        y: {
                            stacked: true
                        }
                    }
                }
            });
        }

        if (document.getElementById('makerChart')) {
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