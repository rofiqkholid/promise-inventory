@extends('layouts.app')
@section('title', 'VAVE Dashboard')
@section('page_title', 'VA/VE Analysis Dashboard')
@section('header-title', 'VAVE Dashboard')

@section('content')
<style>
    /* Custom Styling for Input Month */
    input[type="month"] {
        position: relative;
        padding-right: 30px;
    }
    input[type="month"]::-webkit-calendar-picker-indicator {
        background: transparent;
        bottom: 0;
        color: transparent;
        cursor: pointer;
        height: auto;
        left: 0;
        position: absolute;
        right: 0;
        top: 0;
        width: auto;
    }
    
    /* Fix DataTables Pagination to Right */
    .dataTables_wrapper .dataTables_paginate {
        float: right !important;
        padding-top: 0.5rem;
    }
    .dataTables_wrapper .dataTables_info {
        float: left !important;
        padding-top: 0.75rem;
        font-size: 11px;
        color: #475569;
        font-weight: 600;
    }
    .dataTables_wrapper .dataTables_length {
        float: left !important;
        margin-bottom: 0.5rem;
    }
    .dataTables_wrapper .dataTables_filter {
        float: right !important;
        margin-bottom: 0.5rem;
    }
    .dataTables_wrapper::after {
        content: "";
        clear: both;
        display: table;
    }
</style>

<div class="text-gray-900 dark:text-gray-100 -mt-2">
    {{-- Header & Filter --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white sm:text-2xl tracking-tight uppercase">VAVE Analysis Dashboard</h2>
            <p class="text-[11px] text-slate-500 font-bold tracking-wide italic">Gap Benefit = (Plan - Actual Kg) × IDR/Kg × Qty In</p>
        </div>
        <div class="mt-2 sm:mt-0 flex flex-wrap gap-2 items-center">
            {{-- Period Picker (Month Type) --}}
            <div class="relative w-44">
                <input type="month" id="filterPeriod" value="{{ date('Y-m') }}" 
                    class="w-full h-9 !important pl-3 pr-8 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-[11px] text-slate-600 dark:text-gray-300 rounded-xs focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-all outline-none appearance-none cursor-pointer tracking-tight py-0">
                <i class="fa-solid fa-calendar-day absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-[12px]"></i>
            </div>

            <div class="w-40">
                <select id="filterCustomer" class="select2-simple w-full bg-white dark:bg-gray-800 border-slate-200 dark:border-gray-700 text-xs rounded-xs">
                    <option value="">All Customers</option>
                </select>
            </div>
            <div class="w-40">
                <select id="filterModel" class="select2-simple w-full bg-white dark:bg-gray-800 border-slate-200 dark:border-gray-700 text-xs rounded-xs" disabled>
                    <option value="">All Models</option>
                </select>
            </div>
            <button type="button" id="btnRefresh" class="h-8 px-4 inline-flex items-center justify-center bg-primary-600 hover:bg-primary-700 text-white text-[9px] font-bold uppercase tracking-widest rounded-xs transition-all active:scale-95 shadow-sm">
                <i class="fa-solid fa-arrows-rotate mr-2"></i> Refresh
            </button>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        {{-- Total Gap Benefit --}}
        <div class="bg-white dark:bg-gray-800 px-4 py-3 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center justify-between transition-all hover:border-primary-300 dark:hover:border-primary-500 group">
            <div class="flex-1 min-w-0">
                <p class="text-[10px] font-bold text-slate-600 dark:text-gray-400 uppercase tracking-widest mb-1 truncate">Total Gap Benefit</p>
                <div class="flex items-baseline gap-1">
                    <h3 class="text-xl font-black text-slate-900 dark:text-white leading-none tracking-tight" id="kpi-benefit">Rp 0</h3>
                    <span class="text-[9px] font-black text-slate-500 dark:text-gray-500 uppercase tracking-tighter">IDR</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xs bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center text-primary-600 dark:text-primary-400 text-xl transition-all group-hover:scale-110 group-hover:bg-primary-100 dark:group-hover:bg-primary-900/40">
                <i class="fa-solid fa-money-bill-trend-up"></i>
            </div>
        </div>

        {{-- Total Gap Kg --}}
        <div class="bg-white dark:bg-gray-800 px-4 py-3 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center justify-between transition-all hover:border-blue-300 dark:hover:border-blue-500 group">
            <div class="flex-1 min-w-0">
                <p class="text-[10px] font-bold text-slate-600 dark:text-gray-400 uppercase tracking-widest mb-1 truncate">Total Saving Weight</p>
                <div class="flex items-baseline gap-1">
                    <h3 class="text-xl font-black text-slate-900 dark:text-white leading-none tracking-tight" id="kpi-kg">0.000</h3>
                    <span class="text-[9px] font-black text-slate-500 dark:text-gray-500 uppercase tracking-tighter">KG</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xs bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 dark:text-blue-400 text-xl transition-all group-hover:scale-110 group-hover:bg-blue-100 dark:group-hover:bg-blue-900/40">
                <i class="fa-solid fa-weight-hanging"></i>
            </div>
        </div>

        {{-- Merit Items --}}
        <div class="bg-white dark:bg-gray-800 px-4 py-3 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center justify-between transition-all hover:border-emerald-300 dark:hover:border-emerald-500 group">
            <div class="flex-1 min-w-0">
                <p class="text-[10px] font-bold text-slate-600 dark:text-gray-400 uppercase tracking-widest mb-1 truncate">Merit Items</p>
                <div class="flex items-baseline gap-1">
                    <h3 class="text-xl font-black text-emerald-600 dark:text-emerald-400 leading-none tracking-tight" id="kpi-merit">0</h3>
                    <span class="text-[9px] font-black text-slate-500 dark:text-gray-500 uppercase tracking-tighter">PART</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xs bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-xl transition-all group-hover:scale-110 group-hover:bg-emerald-100 dark:group-hover:bg-emerald-900/40">
                <i class="fa-solid fa-circle-arrow-up"></i>
            </div>
        </div>

        {{-- Loss Items --}}
        <div class="bg-white dark:bg-gray-800 px-4 py-3 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center justify-between transition-all hover:border-rose-300 dark:hover:border-rose-500 group">
            <div class="flex-1 min-w-0">
                <p class="text-[10px] font-bold text-slate-600 dark:text-gray-400 uppercase tracking-widest mb-1 truncate">Loss Items</p>
                <div class="flex items-baseline gap-1">
                    <h3 class="text-xl font-black text-rose-600 dark:text-rose-400 leading-none tracking-tight" id="kpi-loss">0</h3>
                    <span class="text-[9px] font-black text-slate-500 dark:text-gray-500 uppercase tracking-tighter">PART</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xs bg-rose-50 dark:bg-rose-900/20 flex items-center justify-center text-rose-600 dark:text-rose-400 text-xl transition-all group-hover:scale-110 group-hover:bg-rose-100 dark:group-hover:bg-rose-900/40">
                <i class="fa-solid fa-circle-arrow-down"></i>
            </div>
        </div>
    </div>

    {{-- Row 1: Model Analysis (50:50) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-3">
        {{-- Benefit by Model --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-xs border border-slate-200 dark:border-gray-700">
            <h3 class="text-[11px] font-black text-slate-800 dark:text-white uppercase tracking-widest mb-4 flex items-center gap-2">
                <i class="fa-solid fa-money-bill-trend-up text-emerald-500"></i> Benefit by Model (IDR)
            </h3>
            <div class="h-[240px] relative">
                <canvas id="benefitModelChart"></canvas>
            </div>
        </div>

        {{-- Saving Weight by Model --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-xs border border-slate-200 dark:border-gray-700">
            <h3 class="text-[11px] font-black text-slate-800 dark:text-white uppercase tracking-widest mb-4 flex items-center gap-2">
                <i class="fa-solid fa-weight-hanging text-blue-500"></i> Saving Weight by Model (Kg)
            </h3>
            <div class="h-[240px] relative">
                <canvas id="weightModelChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Row 2: Pareto (Wide 8/12) & Item Performance (4/12) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 mb-3">
        {{-- Pareto Analysis --}}
        <div class="lg:col-span-8 bg-white dark:bg-gray-800 p-4 rounded-xs border border-slate-200 dark:border-gray-700">
            <h3 class="text-[11px] font-black text-slate-800 dark:text-white uppercase tracking-widest mb-4 flex items-center gap-2">
                <i class="fa-solid fa-chart-simple text-primary-500"></i> Pareto Analysis (Benefit per Part)
            </h3>
            <div class="h-[300px] relative">
                <canvas id="paretoChart"></canvas>
            </div>
        </div>

        {{-- Item Performance Count --}}
        <div class="lg:col-span-4 bg-white dark:bg-gray-800 p-4 rounded-xs border border-slate-200 dark:border-gray-700">
            <h3 class="text-[11px] font-black text-slate-800 dark:text-white uppercase tracking-widest mb-4 flex items-center gap-2">
                <i class="fa-solid fa-list-check text-emerald-500"></i> Item Performance by Model (Count)
            </h3>
            <div class="h-[300px] relative">
                <canvas id="statusModelChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 dark:border-gray-700 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-between">
            <h3 class="text-[11px] font-black text-slate-800 dark:text-white uppercase tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-table-list text-primary-500"></i> Detailed VAVE Item Analysis
            </h3>
            <button id="btnExportExcel" class="text-[9px] font-bold text-emerald-600 hover:text-emerald-700 uppercase tracking-widest flex items-center gap-1.5 transition-all">
                <i class="fa-solid fa-file-excel"></i> Export CSV
            </button>
        </div>
        <div class="p-3">
            <table id="vaveDetailTable" class="w-full text-[12px] text-left">
                <thead class="text-[10px] font-black text-slate-500 uppercase tracking-widest bg-slate-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-3 py-3">Part No</th>
                        <th class="px-3 py-3">Model</th>
                        <th class="px-3 py-3 text-center">Plan (Kg)</th>
                        <th class="px-3 py-3 text-center">Act (Kg)</th>
                        <th class="px-3 py-3 text-center text-primary-600 bg-primary-50/30">Gap (Kg)</th>
                        <th class="px-3 py-3 text-center">IDR/Kg</th>
                        <th class="px-3 py-3 text-center">Qty In</th>
                        <th class="px-4 py-3 text-right">Benefit (IDR)</th>
                        <th class="px-3 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                    {{-- Dynamic --}}
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>
$(function() {
    let paretoChart, benefitChart, weightChart, statusChart;
    let mainTable = null;

    // Register Plugin
    Chart.register(ChartDataLabels);

    const isDark = document.documentElement.classList.contains('dark');
    const chartColors = {
        primary: '#0ea5e9',
        emerald: '#10b981',
        blue: '#3b82f6',
        rose: '#f43f5e',
        amber: '#f59e0b',
        indigo: '#6366f1',
        slate: isDark ? '#94a3b8' : '#475569'
    };

    // Default Chart Config
    Chart.defaults.color = chartColors.slate;
    Chart.defaults.font.family = "'Outfit', sans-serif";
    Chart.defaults.font.size = 11;

    // Format IDR helper
    const formatIDR = (val) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(val);
    };

    // Initialize Select2
    $('.select2-simple').select2({
        width: '100%',
        placeholder: 'Select...',
        allowClear: true
    });

    // Load Filters
    function loadFilters() {
        $.get('{{ route("inventory.master.product.getCustomers") }}', function(data) {
            data.forEach(c => {
                $('#filterCustomer').append(`<option value="${c.id}">${c.code}</option>`);
            });
        });

        $('#filterCustomer').on('change', function() {
            const customerId = $(this).val();
            $('#filterModel').empty().append('<option value="">All Models</option>');
            
            if (customerId) {
                $('#filterModel').prop('disabled', false);
                $.get('{{ route("inventory.master.product.getModels") }}', { customer_id: customerId }, function(data) {
                    data.forEach(m => {
                        $('#filterModel').append(`<option value="${m.id}">${m.name}</option>`);
                    });
                    refreshData();
                });
            } else {
                $('#filterModel').prop('disabled', true);
                refreshData();
            }
        });

        // New Change Handler for Input Month
        $('#filterPeriod').on('change', function() {
            refreshData();
        });

        $('#filterModel').on('change', function() {
            refreshData();
        });

        $('#btnRefresh').on('click', function() {
            refreshData();
        });
    }

    // Refresh Data & Charts
    function refreshData() {
        const periodValue = $('#filterPeriod').val(); // Format: YYYY-MM
        const [year, month] = periodValue.split('-');

        const params = {
            year: year,
            month: parseInt(month),
            customer_id: $('#filterCustomer').val(),
            model_id: $('#filterModel').val()
        };

        $('#btnRefresh i').addClass('fa-spin');

        $.get('{{ route("inventory.vaveDashboard.chartData") }}', params, function(res) {
            updateKPIs(res.kpi);
            renderBenefitChart(res.models);
            renderWeightChart(res.models);
            renderStatusChart(res.models);
            updateTable(res.items);
            $('#btnRefresh i').removeClass('fa-spin');
        });

        $.get('{{ route("inventory.vaveDashboard.paretoData") }}', params, function(res) {
            renderParetoChart(res.pareto);
        });
    }

    function updateKPIs(kpi) {
        $('#kpi-benefit').text(formatIDR(kpi.gap_benefit_idr));
        $('#kpi-kg').text(kpi.gap_kg_total.toFixed(3));
        $('#kpi-merit').text(kpi.merit_count);
        $('#kpi-loss').text(kpi.loss_count);
        
        const benefitColor = kpi.gap_benefit_idr >= 0 ? 'text-emerald-600' : 'text-rose-600';
        $('#kpi-benefit').removeClass('text-emerald-600 text-rose-600 text-slate-900').addClass(benefitColor);
    }

    function renderParetoChart(data) {
        if (paretoChart) paretoChart.destroy();
        const ctx = document.getElementById('paretoChart').getContext('2d');
        paretoChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => d.label),
                datasets: [
                    {
                        label: 'Cumulative %',
                        type: 'line',
                        data: data.map(d => d.cumulative_pct),
                        borderColor: chartColors.amber,
                        backgroundColor: 'transparent',
                        yAxisID: 'y1',
                        borderWidth: 2,
                        tension: 0.3,
                        pointRadius: 3,
                        datalabels: { align: 'top', anchor: 'end', offset: 2, font: { size: 10, weight: 'bold' }, formatter: (v) => v.toFixed(0) + '%' }
                    },
                    {
                        label: 'Benefit (IDR)',
                        data: data.map(d => d.gap_benefit_idr),
                        backgroundColor: chartColors.primary,
                        yAxisID: 'y',
                        borderRadius: 2,
                        datalabels: { display: false }
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 20, bottom: 0 } },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        suggestedMax: function(context) { 
                            let max = Math.max(...context.chart.data.datasets[1].data);
                            return max > 0 ? max * 1.2 : 1000000;
                        },
                        ticks: { callback: (v) => v.toLocaleString(), font: { size: 10 } } 
                    },
                    y1: { position: 'right', max: 110, min: 0, grid: { drawOnChartArea: false }, ticks: { callback: (v) => v + '%', font: { size: 10 } } },
                    x: { ticks: { font: { size: 9 } } }
                },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 }, padding: 8 } },
                    datalabels: { color: chartColors.slate }
                }
            }
        });
    }

    function renderBenefitChart(models) {
        if (benefitChart) benefitChart.destroy();
        const ctx = document.getElementById('benefitModelChart').getContext('2d');
        benefitChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: models.labels,
                datasets: [{
                    label: 'Benefit (IDR)',
                    data: models.idr,
                    backgroundColor: chartColors.emerald,
                    borderRadius: 2,
                    datalabels: { align: 'top', anchor: 'end', offset: 2, font: { size: 10, weight: 'bold' }, formatter: (v) => v === 0 ? '' : (v/1000).toFixed(0) + 'k' }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 25, bottom: 0 } },
                scales: {
                    y: { beginAtZero: true, suggestedMax: function(context) { 
                        let max = Math.max(...context.chart.data.datasets[0].data);
                        return max > 0 ? max * 1.3 : 1000000;
                    }, ticks: { callback: (val) => val.toLocaleString(), font: { size: 10 } } },
                    x: { ticks: { font: { size: 10 } } }
                },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 }, padding: 8 } },
                    tooltip: { enabled: true, callbacks: { label: (c) => 'Benefit: ' + formatIDR(c.parsed.y) } }
                }
            }
        });
    }

    function renderWeightChart(models) {
        if (weightChart) weightChart.destroy();
        const ctx = document.getElementById('weightModelChart').getContext('2d');
        weightChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: models.labels,
                datasets: [{
                    label: 'Saving Weight (Kg)',
                    data: models.kg,
                    backgroundColor: chartColors.blue,
                    borderRadius: 2,
                    datalabels: { align: 'end', anchor: 'end', offset: 2, font: { size: 10, weight: 'bold' }, formatter: (v) => v === 0 ? '' : v.toFixed(1) }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 25, bottom: 0 } },
                scales: {
                    y: { beginAtZero: true, suggestedMax: function(context) { 
                        let max = Math.max(...context.chart.data.datasets[0].data);
                        return max > 0 ? max * 1.3 : 10;
                    }, ticks: { font: { size: 10 } } },
                    x: { ticks: { font: { size: 10 } } }
                },
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 }, padding: 8 } } }
            }
        });
    }

    function renderStatusChart(models) {
        if (statusChart) statusChart.destroy();
        const ctx = document.getElementById('statusModelChart').getContext('2d');
        statusChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: models.labels,
                datasets: [
                    { label: 'Merit', data: models.merit, backgroundColor: chartColors.emerald, datalabels: { color: '#fff', font: { weight: 'bold', size: 10 } } },
                    { label: 'Loss', data: models.loss, backgroundColor: chartColors.rose, datalabels: { color: '#fff', font: { weight: 'bold', size: 10 } } }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 20, bottom: 0 } },
                scales: { x: { stacked: true, ticks: { font: { size: 10 } } }, y: { stacked: true, beginAtZero: true, ticks: { font: { size: 10 } } } },
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 }, padding: 8 } }, datalabels: { display: (c) => c.dataset.data[c.dataIndex] > 0 } }
            }
        });
    }

    function updateTable(items) {
        if (mainTable !== null) {
            mainTable.destroy();
        }

        const tbody = $('#vaveDetailTable tbody').empty();
        items.forEach(item => {
            const gapPerUnit = item.plan_kg - item.actual_kg;
            const statusClass = item.gap_benefit_idr > 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 
                              (item.gap_benefit_idr < 0 ? 'bg-rose-50 text-rose-700 border-rose-100' : 'bg-slate-50 text-slate-500 border-slate-100');
            const statusText = item.gap_benefit_idr > 0 ? 'MERIT' : (item.gap_benefit_idr < 0 ? 'LOSS' : 'STABLE');
            tbody.append(`
                <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-700/20 transition-colors">
                    <td class="px-3 py-3"><div class="font-bold text-slate-900 dark:text-white">${item.part_no}</div></td>
                    <td class="px-3 py-3"><span class="px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-gray-700 text-[10px] font-bold uppercase">${item.model_name}</span></td>
                    <td class="px-3 py-3 text-center font-mono text-slate-600 dark:text-gray-400">${item.plan_kg.toFixed(3)}</td>
                    <td class="px-3 py-3 text-center font-mono text-slate-600 dark:text-gray-400">${item.actual_kg.toFixed(3)}</td>
                    <td class="px-3 py-3 text-center font-black bg-primary-50/20 ${gapPerUnit >= 0 ? 'text-emerald-600' : 'text-rose-600'}">${gapPerUnit.toFixed(3)}</td>
                    <td class="px-3 py-3 text-center text-slate-500">Rp ${item.idr_per_kg.toLocaleString()}</td>
                    <td class="px-3 py-3 text-center font-bold text-slate-700 dark:text-gray-300">${item.qty_usage.toLocaleString()}</td>
                    <td class="px-4 py-3 text-right font-black ${item.gap_benefit_idr >= 0 ? 'text-emerald-600' : 'text-rose-600'}">${formatIDR(item.gap_benefit_idr)}</td>
                    <td class="px-3 py-3 text-center"><span class="px-2 py-0.5 rounded-full border text-[9px] font-black tracking-widest ${statusClass}">${statusText}</span></td>
                </tr>
            `);
        });

        mainTable = $('#vaveDetailTable').DataTable({
            pageLength: 10, 
            lengthMenu: [10, 25, 50], 
            ordering: true,
            dom: '<"flex items-center justify-between gap-4 mb-2"lf>rtip',
            language: { 
                search: "", 
                searchPlaceholder: "Search Part...",
                paginate: {
                    previous: "<i class='fa-solid fa-chevron-left'></i>",
                    next: "<i class='fa-solid fa-chevron-right'></i>"
                }
            }
        });

        $('.dataTables_filter input').addClass('bg-white dark:bg-gray-800 border-slate-200 dark:border-gray-700 text-[11px] rounded-xs px-3 py-1.5 w-48');
    }

    $('#btnExportExcel').on('click', function() {
        let csv = 'Part No,Model,Plan (Kg),Act (Kg),Gap (Kg),IDR/Kg,Qty In (Pcs),Benefit (IDR),Status\n';
        $('#vaveDetailTable tbody tr').each(function() {
            let row = [];
            $(this).find('td').each(function(index) {
                let text = $(this).text().trim().replace(/Rp/g, '').replace(/\./g, '').replace(/,/g, '.');
                if (index === 0) text = $(this).find('.font-bold').text().trim();
                row.push('"' + text + '"');
            });
            csv += row.join(',') + '\n';
        });
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement("a");
        link.setAttribute("href", URL.createObjectURL(blob));
        link.setAttribute("download", "VAVE_Export_" + new Date().getTime() + ".csv");
        link.click();
    });

    loadFilters();
    refreshData();
});
</script>
@endpush
