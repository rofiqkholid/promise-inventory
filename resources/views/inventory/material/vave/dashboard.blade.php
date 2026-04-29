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

<div class="dashboard-container w-full flex flex-col gap-3 pb-6">
    {{-- Header & KPI Stats --}}
    <div class="flex flex-wrap items-center justify-between gap-y-2 gap-x-4">
        <!-- Title Section -->
        <div class="flex-none">
            <h2 class="text-xl xl:text-2xl font-bold text-gray-900 dark:text-white leading-tight mb-0.5 tracking-tight">Vave Analysis</h2>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 font-normal leading-tight">Gap Benefit: (Plan - Act Kg) × Price × Qty In</p>
        </div>

            <!-- KPI Grid & Filter Toggle -->
        <div class="flex-1 flex flex-col md:flex-row gap-2 items-stretch lg:justify-end min-w-[100%] xl:min-w-[850px]">
            <!-- KPI Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 flex-1">
                @foreach([
                    ['id' => 'kpi-benefit', 'label' => 'Total Benefit', 'val' => 'Rp 0', 'unit' => 'IDR', 'icon' => 'fa-money-bill-trend-up', 'color' => 'primary'],
                    ['id' => 'kpi-kg', 'label' => 'Saving Weight', 'val' => '0.000', 'unit' => 'KG', 'icon' => 'fa-weight-hanging', 'color' => 'blue'],
                    ['id' => 'kpi-saving-rate', 'label' => 'Saving Rate', 'val' => '0.0', 'unit' => '%', 'icon' => 'fa-percent', 'color' => 'amber'],
                    ['id' => 'kpi-merit', 'label' => 'Merit Items', 'val' => '0', 'unit' => 'PART', 'icon' => 'fa-circle-arrow-up', 'color' => 'emerald'],
                ] as $stat)
                <div class="bg-white dark:bg-gray-800 px-2.5 py-2 rounded-xs border border-gray-200 dark:border-gray-700 flex items-center gap-2.5 h-[52px]">
                    <div class="w-9 h-9 rounded-xs bg-{{ $stat['color'] }}-50 dark:bg-{{ $stat['color'] }}-900/20 flex items-center justify-center text-{{ $stat['color'] }}-600 dark:text-{{ $stat['color'] }}-400 text-base shrink-0">
                        <i class="fa-solid {{ $stat['icon'] }}"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 tracking-tight leading-none mb-1 truncate">{{ $stat['label'] }}</p>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 leading-none tracking-tight whitespace-nowrap" id="{{ $stat['id'] }}">
                            {{ $stat['val'] }} <span class="text-[9px] text-slate-400 font-normal ml-0.5">{{ $stat['unit'] }}</span>
                        </h3>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Filter Toggle -->
            <div class="shrink-0 flex items-stretch">
                <button id="btnToggleDashFilter" title="Toggle Filters" class="group flex items-center justify-center w-full md:w-[52px] h-[52px] md:h-auto bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-600 rounded-xs transition-all hover:bg-slate-50 dark:hover:bg-gray-700">
                    <i class="fa-solid fa-filter text-slate-400 group-hover:text-primary-500 transition-colors text-sm"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Collapsible Filter Card --}}
    <div id="dashboardFilterCard" class="hidden bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-600 p-4">
        <form id="filterForm">
            <div class="flex flex-col lg:flex-row gap-4 lg:items-end">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 flex-1">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">Analysis Mode</label>
                        <select id="filterMode" class="w-full text-xs font-medium border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-900 dark:text-white rounded-xs h-[40px] px-3 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all focus:border-primary-500">
                            <option value="monthly">Monthly View</option>
                            <option value="yearly">Yearly Trend</option>
                        </select>
                    </div>
                    <div class="space-y-1.5" id="divFilterPeriod">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">Period</label>
                        <input type="month" id="filterPeriod" value="{{ date('Y-m') }}" class="w-full text-xs font-medium border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-900 dark:text-white rounded-xs h-[40px] px-3 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all focus:border-primary-500">
                    </div>
                    <div class="space-y-1.5 hidden" id="divFilterYear">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">Year</label>
                        <input type="number" id="filterYear" value="{{ date('Y') }}" min="2000" max="{{ date('Y') + 5 }}" class="w-full text-xs font-medium border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-900 dark:text-white rounded-xs h-[40px] px-3 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all focus:border-primary-500">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">Customer</label>
                        <select id="filterCustomer" class="select2-simple w-full"></select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">Model</label>
                        <select id="filterModel" class="select2-simple w-full" disabled></select>
                    </div>
                    <div class="flex gap-2 items-end">
                        <button type="button" id="btnReset" class="h-10 px-6 bg-slate-600 hover:bg-slate-700 rounded-xs text-xs font-medium text-white transition-all shadow-sm active:scale-95">
                            <i class="fa-solid fa-rotate-left mr-2"></i> Reset Filters
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Dashboard Content Area --}}
    <div class="flex flex-col gap-3 flex-1">
        {{-- Charts Row 1 --}}
        <div class="flex flex-col lg:flex-row gap-3">
            {{-- Benefit Chart --}}
            <div class="chart-card bg-white dark:bg-gray-800 p-2.5 lg:p-3 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative flex-1 min-w-0 h-[320px]">
                <div class="flex-none flex justify-between items-center mb-1">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 flex items-center truncate tracking-tight">
                        <i class="fa-solid fa-money-bill-trend-up mr-2 text-emerald-500"></i> Benefit by Model
                        <span class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-slate-700 text-[8px] font-medium text-slate-500 dark:text-slate-400 tracking-wider border border-slate-200/50 dark:border-slate-600/50 flex-shrink-0 whitespace-nowrap">IDR</span>
                    </h3>
                </div>
                <div class="relative w-full flex-1 min-h-0">
                    <canvas id="benefitModelChart"></canvas>
                </div>
            </div>

            {{-- Weight Chart --}}
            <div class="chart-card bg-white dark:bg-gray-800 p-2.5 lg:p-3 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative flex-1 min-w-0 h-[320px]">
                <div class="flex-none flex justify-between items-center mb-1">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 flex items-center truncate tracking-tight">
                        <i class="fa-solid fa-weight-hanging mr-2 text-blue-500"></i> Saving Weight by Model
                        <span class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-slate-700 text-[8px] font-medium text-slate-500 dark:text-slate-400 tracking-wider border border-slate-200/50 dark:border-slate-600/50 flex-shrink-0 whitespace-nowrap">KG</span>
                    </h3>
                </div>
                <div class="relative w-full flex-1 min-h-0">
                    <canvas id="weightModelChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Row 2: Pareto (8/12) & Status (4/12) --}}
        <div class="flex flex-col lg:flex-row gap-3">
            {{-- Pareto Chart --}}
            <div class="chart-card bg-white dark:bg-gray-800 p-2.5 lg:p-3 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative lg:flex-[8] min-w-0 h-[360px]">
                <div class="flex-none flex justify-between items-center mb-1">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 flex items-center truncate tracking-tight">
                        <i class="fa-solid fa-chart-simple mr-2 text-primary-500"></i> Pareto Analysis
                        <span class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-slate-700 text-[8px] font-medium text-slate-500 dark:text-slate-400 tracking-wider border border-slate-200/50 dark:border-slate-600/50 flex-shrink-0 whitespace-nowrap">Contribution</span>
                    </h3>
                </div>
                <div class="relative w-full flex-1 min-h-0">
                    <canvas id="paretoChart"></canvas>
                </div>
            </div>

            {{-- Status Chart --}}
            <div class="chart-card bg-white dark:bg-gray-800 p-2.5 lg:p-3 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative lg:flex-[4] min-w-0 h-[360px]">
                <div class="flex-none flex justify-between items-center mb-1">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 flex items-center truncate tracking-tight">
                        <i class="fa-solid fa-percent mr-2 text-amber-500"></i> Saving Efficiency by Model
                    </h3>
                </div>
                <div class="relative w-full flex-1 min-h-0">
                    <canvas id="statusModelChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Detailed Data Table --}}
        <div class="table-container bg-white dark:bg-gray-800 p-3 lg:p-4 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative">
            <div class="flex-none flex justify-between items-center mb-2">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 flex items-center">
                    <i class="fa-solid fa-table-list mr-2 text-primary-500"></i> Detailed VAVE Analysis
                </h3>
                <button id="btnExportExcel" class="text-[9px] font-medium text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 tracking-wider flex items-center gap-1.5 px-2 py-1 bg-emerald-50 dark:bg-emerald-900/20 rounded-xs border border-emerald-100 dark:border-emerald-800/50 transition-all">
                    <i class="fa-solid fa-file-excel"></i> Export CSV
                </button>
            </div>
            
            <div class="flex-1 overflow-hidden custom-scrollbar">
                <table id="vaveDetailTable" class="w-full text-left">
                    <thead class="bg-gray-50/80 dark:bg-gray-700/50 sticky top-0 z-10 backdrop-blur-md">
                        <tr>
                            <th class="py-2 px-3 text-[11px] font-medium text-slate-500 tracking-wider">Part No</th>
                            <th class="py-2 px-3 text-[11px] font-medium text-slate-500 tracking-wider">Model</th>
                            <th class="py-2 px-3 text-[11px] font-medium text-slate-500 tracking-wider">EBD Version</th>
                            <th class="py-2 px-2 text-[11px] font-medium text-slate-500 tracking-wider text-center">Plan (Kg)</th>
                            <th class="py-2 px-2 text-[11px] font-medium text-slate-500 tracking-wider text-center">Actual (Kg)</th>
                            <th class="py-2 px-2 text-[11px] font-medium text-slate-500 tracking-wider text-center">Gap (Kg)</th>
                            <th class="py-2 px-2 text-[11px] font-medium text-slate-500 tracking-wider text-center">IDR/Kg</th>
                            <th class="py-2 px-2 text-[11px] font-medium text-slate-500 tracking-wider text-center">Qty In</th>
                            <th class="py-2 px-3 text-[11px] font-medium text-slate-500 tracking-wider text-right">Benefit</th>
                            <th class="py-2 px-3 text-[11px] font-medium text-slate-500 tracking-wider text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                    </tbody>
                </table>
            </div>
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

    Chart.register(ChartDataLabels);

    // Toggle Filter Logic
    $('#btnToggleDashFilter').on('click', function(e) {
        e.stopPropagation();
        $('#dashboardFilterCard').slideToggle(200);
        
        $(this).toggleClass('bg-white dark:bg-gray-800 border-slate-200 dark:border-gray-600 hover:bg-slate-50 dark:hover:bg-gray-700');
        $(this).toggleClass('bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-700 hover:bg-primary-100 dark:hover:bg-primary-900/40');
        $(this).find('i').toggleClass('text-slate-400 group-hover:text-primary-500');
        $(this).find('i').toggleClass('text-primary-600 dark:text-primary-400');
    });

    // Register Plugin
    Chart.register(ChartDataLabels);

    const isDark = document.documentElement.classList.contains('dark');
    
    // Dynamic Chart Defaults (STRICT MATCH with Main Dashboard)
    Chart.defaults.color = isDark ? '#94a3b8' : '#64748b';
    Chart.defaults.borderColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)';
    Chart.defaults.font.family = "'Inter', sans-serif";
    
    const chartColors = {
        primary: '#0ea5e9',
        emerald: '#10b981',
        blue: '#3b82f6',
        rose: '#ef4444',
        amber: '#f59e0b',
        indigo: '#6366f1',
        slate: isDark ? '#94a3b8' : '#64748b'
    };

    // Global Tooltip & Interaction (STRICT MATCH with Main Dashboard)
    const commonInteraction = { mode: 'index', intersect: false };
    const commonTooltip = {
        enabled: true,
        usePointStyle: true,
        backgroundColor: isDark ? '#1e293b' : '#ffffff',
        titleColor: isDark ? '#f8fafc' : '#1e293b',
        bodyColor: isDark ? '#94a3b8' : '#64748b',
        borderColor: isDark ? '#334155' : '#e2e8f0',
        borderWidth: 1,
        padding: 12,
        displayColors: true,
        boxPadding: 6,
        callbacks: {
            label: function(context) {
                let label = context.dataset.label || '';
                if (label) label += ': ';
                if (context.parsed.y !== null) {
                    const unit = context.dataset.label.includes('Benefit') ? 'Rp ' : '';
                    const val = context.parsed.y;
                    label += unit + (val >= 1000 ? val.toLocaleString() : val.toFixed(2));
                }
                return label;
            }
        }
    };

    const commonLegend = { 
        position: 'bottom', 
        labels: { 
            color: isDark ? '#94a3b8' : '#64748b',
            font: { size: 11 },
            usePointStyle: true,
            pointStyle: 'rect',
            padding: 15
        } 
    };

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
            $('#filterCustomer').empty().append('<option value="">All Customers</option>');
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

        $('#filterMode').on('change', function() {
            const mode = $(this).val();
            if (mode === 'yearly') {
                $('#divFilterPeriod').addClass('hidden');
                $('#divFilterYear').removeClass('hidden');
            } else {
                $('#divFilterPeriod').removeClass('hidden');
                $('#divFilterYear').addClass('hidden');
            }
            refreshData();
        });

        $('#filterPeriod, #filterModel, #filterYear').on('change', function() {
            refreshData();
        });

        $('#btnReset').on('click', function() {
            $('#filterMode').val('monthly').trigger('change');
            $('#filterCustomer').val('').trigger('change');
            $('#filterPeriod').val('{{ date("Y-m") }}');
        });
    }

    // Refresh Data & Charts
    function refreshData() {
        const mode = $('#filterMode').val();
        let year, month;

        if (mode === 'yearly') {
            year = $('#filterYear').val();
            month = null;
        } else {
            const periodValue = $('#filterPeriod').val();
            if (!periodValue) return;
            [year, month] = periodValue.split('-');
            month = parseInt(month);
        }

        const params = {
            year: year,
            month: month,
            customer_id: $('#filterCustomer').val(),
            model_id: $('#filterModel').val()
        };

        const btn = $('#btnReset');
        btn.html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> ...').prop('disabled', true);

        const restoreBtn = () => {
            btn.html('<i class="fa-solid fa-rotate-left mr-2"></i> Reset Filters').prop('disabled', false);
        };

        // Update UI Titles
        if (mode === 'yearly') {
            $('#labelPeriod').text('Target Year');
            $('.chart-card h3').each(function() {
                const h3 = $(this);
                if (h3.text().includes('by Model')) {
                    h3.html(h3.html().replace('by Model', 'Trend (12 Mo)'));
                }
            });
        } else {
            $('#labelPeriod').text('Period');
            $('.chart-card h3').each(function() {
                const h3 = $(this);
                if (h3.text().includes('Trend (12 Mo)')) {
                    h3.html(h3.html().replace('Trend (12 Mo)', 'by Model'));
                }
            });
        }

        $.get('{{ route("inventory.vaveDashboard.chartData") }}', params, function(res) {
            updateKPIs(res.kpi);
            
            // Filter only positive benefit models (Merit)
            const meritModels = {
                labels: [],
                idr: [],
                kg: [],
                merit: [],
                loss: [],
                plan_cost: []
            };

            res.models.labels.forEach((label, i) => {
                if (res.models.idr[i] > 0) {
                    meritModels.labels.push(label);
                    meritModels.idr.push(res.models.idr[i]);
                    meritModels.kg.push(res.models.kg[i]);
                    meritModels.merit.push(res.models.merit[i]);
                    meritModels.loss.push(res.models.loss[i]);
                    meritModels.plan_cost.push(res.models.plan_cost[i]);
                }
            });

            if (mode === 'yearly' && res.trend) {
                const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                const trendIdr = new Array(12).fill(0);
                const trendKg = new Array(12).fill(0);
                
                res.trend.forEach(t => {
                    if (parseFloat(t.gap_benefit_idr) > 0) {
                        trendIdr[t.month_num - 1] = parseFloat(t.gap_benefit_idr);
                        trendKg[t.month_num - 1] = parseFloat(t.gap_kg_total);
                    }
                });

                renderTrendChart('benefitModelChart', 'Benefit (IDR)', months, trendIdr, '#10b981', true);
                renderTrendChart('weightModelChart', 'Saving Weight (Kg)', months, trendKg, '#3b82f6', false);
            } else {
                renderBenefitChart(meritModels);
                renderWeightChart(meritModels);
            }

            renderEfficiencyChart(meritModels);
            updateTable(res.items.filter(item => item.gap_benefit_idr > 0));
            btn.html('<i class="fa-solid fa-rotate-left mr-2"></i> Reset Filters').prop('disabled', false);
        });

        $.get('{{ route("inventory.vaveDashboard.paretoData") }}', params, function(res) {
            const meritPareto = res.pareto.filter(p => p.gap_benefit_idr > 0);
            renderParetoChart(meritPareto);
        });
    }

    function renderTrendChart(canvasId, label, labels, data, color, isCurrency) {
        const chartVar = canvasId === 'benefitModelChart' ? 'benefitChart' : 'weightChart';
        if (chartVar === 'benefitChart' && benefitChart) benefitChart.destroy();
        if (chartVar === 'weightChart' && weightChart) weightChart.destroy();
        
        const ctx = document.getElementById(canvasId).getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, color + '40');
        gradient.addColorStop(1, color + '00');

        const newChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: data,
                    borderColor: color,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: color,
                    pointBorderWidth: 2,
                    datalabels: { 
                        display: true,
                        align: 'top', 
                        anchor: 'end', 
                        offset: 4, 
                        font: { size: 13, weight: '500' }, 
                        formatter: (v) => v === 0 ? '' : (isCurrency ? (Math.abs(v) >= 1000000 ? (v/1000000).toFixed(1) + 'M' : (v/1000).toFixed(0) + 'k') : v.toFixed(1))
                    }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: commonInteraction,
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grace: '20%',
                        grid: { borderDash: [5, 5], drawBorder: false },
                        ticks: { color: Chart.defaults.color, font: { size: 12 }, callback: (val) => isCurrency ? val.toLocaleString() : val } 
                    },
                    x: { grid: { display: false }, ticks: { color: Chart.defaults.color, font: { size: 12 } } }
                },
                plugins: { legend: { display: false }, tooltip: commonTooltip }
            }
        });

        if (chartVar === 'benefitChart') benefitChart = newChart;
        else weightChart = newChart;
    }

    function updateKPIs(kpi) {
        // Update Benefit
        const benefitEl = $('#kpi-benefit');
        benefitEl.html(`${formatIDR(kpi.gap_benefit_idr)} <span class="text-[9px] text-slate-400 font-medium ml-0.5">IDR</span>`);
        
        const benefitColor = kpi.gap_benefit_idr >= 0 ? 'text-emerald-600' : 'text-rose-600';
        benefitEl.removeClass('text-emerald-600 text-rose-600 text-slate-900 dark:text-slate-100').addClass(benefitColor);

        // Update KG
        $('#kpi-kg').html(`${kpi.gap_kg_total.toFixed(3)} <span class="text-[9px] text-slate-400 font-medium ml-0.5">KG</span>`);
        
        // Update Saving Rate
        $('#kpi-saving-rate').html(`${kpi.saving_rate.toFixed(1)} <span class="text-[9px] text-slate-400 font-medium ml-0.5">%</span>`);

        // Update Part Counts
        $('#kpi-merit').html(`${kpi.merit_count} <span class="text-[9px] text-slate-400 font-medium ml-0.5">PART</span>`);
    }

    function renderParetoChart(data) {
        if (paretoChart) paretoChart.destroy();
        const ctx = document.getElementById('paretoChart').getContext('2d');
        
        const maxBenefit = Math.max(...data.map(d => Math.abs(d.gap_benefit_idr))) || 1000;

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
                        backgroundColor: '#fff',
                        pointBackgroundColor: '#fff',
                        pointBorderColor: chartColors.amber,
                        pointBorderWidth: 2,
                        yAxisID: 'y1',
                        borderWidth: 2,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        datalabels: { 
                            align: 'top', 
                            anchor: 'end', 
                            offset: 8, 
                            font: { size: 13, weight: '500' }, 
                            formatter: (v) => v.toFixed(0) + '%' 
                        }
                    },
                    {
                        label: 'Benefit (IDR)',
                        data: data.map(d => d.gap_benefit_idr),
                        backgroundColor: chartColors.primary,
                        yAxisID: 'y',
                        borderRadius: 2,
                        datalabels: { 
                            display: true,
                            align: 'top',
                            anchor: 'end',
                            offset: 2,
                            font: { size: 13, weight: '500' },
                            formatter: (v) => v === 0 ? '' : (Math.abs(v) >= 1000000 ? (v/1000000).toFixed(1) + 'M' : (v/1000).toFixed(0) + 'k')
                        }
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: commonInteraction,
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grace: '25%', 
                        grid: { borderDash: [5, 5], drawBorder: false },
                        ticks: { 
                            color: Chart.defaults.color, 
                            font: { size: 12 }, 
                            maxTicksLimit: 6,
                            callback: (v) => v >= 1000000 ? (v/1000000).toFixed(1) + 'M' : v.toLocaleString() 
                        }
                    },
                    y1: { 
                        position: 'right', 
                        max: 120, 
                        min: 0, 
                        grid: { display: false },
                        ticks: { 
                            color: Chart.defaults.color, 
                            font: { size: 12 },
                            maxTicksLimit: 5,
                            callback: (v) => v + '%' 
                        }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { color: Chart.defaults.color, font: { size: 12 }, maxTicksLimit: 8 }
                    }
                },
                plugins: {
                    legend: commonLegend,
                    tooltip: commonTooltip,
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
                    backgroundColor: (context) => {
                        const val = context.dataset.data[context.dataIndex];
                        return val >= 0 ? '#10b981' : '#ef4444';
                    },
                    borderRadius: 2,
                    datalabels: { 
                        display: true,
                        align: 'top', 
                        anchor: 'end', 
                        offset: 2, 
                        font: { size: 13, weight: '500' }, 
                        formatter: (v) => v === 0 ? '' : (Math.abs(v) >= 1000000 ? (v/1000000).toFixed(1) + 'M' : (v/1000).toFixed(0) + 'k') 
                    }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: commonInteraction,
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grace: '20%', // Provide space at top
                        grid: { borderDash: [5, 5], drawBorder: false },
                        ticks: { color: Chart.defaults.color, font: { size: 12 }, maxTicksLimit: 6, callback: (val) => val.toLocaleString() } 
                    },
                    x: { grid: { display: false }, ticks: { color: Chart.defaults.color, font: { size: 12 }, maxTicksLimit: 10 } }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: commonTooltip
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
                    backgroundColor: '#3b82f6',
                    borderRadius: 2,
                    datalabels: { align: 'end', anchor: 'end', offset: 2, font: { size: 13, weight: '500' }, formatter: (v) => v === 0 ? '' : v.toFixed(1) }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: commonInteraction,
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grace: '20%',
                        grid: { borderDash: [5, 5], drawBorder: false }, 
                        ticks: { color: Chart.defaults.color, font: { size: 12 }, maxTicksLimit: 6 } 
                    },
                    x: { grid: { display: false }, ticks: { color: Chart.defaults.color, font: { size: 12 }, maxTicksLimit: 10 } }
                },
                plugins: { legend: { display: false }, tooltip: commonTooltip }
            }
        });
    }

    function renderEfficiencyChart(models) {
        if (statusChart) statusChart.destroy();
        const ctx = document.getElementById('statusModelChart').getContext('2d');
        
        const efficiencyData = models.idr.map((idr, i) => {
            const planCost = models.plan_cost[i] || 1;
            return (idr / planCost) * 100;
        });

        statusChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: models.labels,
                datasets: [
                    { 
                        label: 'Efficiency %', 
                        data: efficiencyData, 
                        backgroundColor: chartColors.amber, 
                        borderRadius: 2, 
                        datalabels: { 
                            align: 'end',
                            anchor: 'end',
                            color: Chart.defaults.color, 
                            font: { weight: '600', size: 12 },
                            formatter: (v) => v.toFixed(1) + '%'
                        } 
                    }
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                interaction: commonInteraction,
                scales: { 
                    x: { 
                        beginAtZero: true, 
                        grace: '15%',
                        grid: { borderDash: [5, 5], drawBorder: false }, 
                        ticks: { 
                            color: Chart.defaults.color, 
                            font: { size: 11 },
                            callback: (v) => v + '%'
                        } 
                    }, 
                    y: { 
                        grid: { display: false },
                        ticks: { color: Chart.defaults.color, font: { size: 11 } } 
                    } 
                },
                plugins: { 
                    legend: { display: false }, 
                    tooltip: {
                        ...commonTooltip,
                        callbacks: {
                            label: (ctx) => `Efficiency: ${ctx.raw.toFixed(2)}%`
                        }
                    }
                }
            }
        });
    }

    function updateTable(items) {
        if ($.fn.DataTable.isDataTable('#vaveDetailTable')) {
            $('#vaveDetailTable').DataTable().destroy();
        }

        const tbody = $('#vaveDetailTable tbody').empty();
        items.forEach(item => {
            const gapPerUnit = item.plan_kg - item.actual_kg;
            const statusClass = item.gap_benefit_idr > 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800' : 
                               (item.gap_benefit_idr < 0 ? 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800' : 'bg-slate-50 text-slate-500 border-slate-100 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700');
            const statusText = item.gap_benefit_idr > 0 ? 'MERIT' : (item.gap_benefit_idr < 0 ? 'LOSS' : 'STABLE');
            
            tbody.append(`
                <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-700/20 transition-colors">
                    <td class="py-2 px-3">
                        <div class="text-[12px] font-medium text-slate-700 dark:text-gray-200 uppercase tracking-tight">${item.part_no}</div>
                    </td>
                    <td class="py-2 px-3">
                        <span class="px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-gray-700 text-[11px] font-medium text-slate-600 dark:text-gray-400 uppercase">${item.model_name}</span>
                    </td>
                    <td class="py-2 px-3">
                        <span class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-tight">${item.ebd_version || '-'}</span>
                    </td>
                    <td class="py-2 px-2 text-center font-mono text-[12px] text-slate-500 dark:text-gray-400">${item.plan_kg.toFixed(3)} <span class="text-[9px] opacity-50">kg</span></td>
                    <td class="py-2 px-2 text-center font-mono text-[12px] text-slate-500 dark:text-gray-400">${item.actual_kg.toFixed(3)} <span class="text-[9px] opacity-50">kg</span></td>
                    <td class="py-2 px-2 text-center font-bold text-[12px] ${gapPerUnit >= 0 ? 'text-emerald-600' : 'text-rose-600'}">${gapPerUnit.toFixed(3)} <span class="text-[10px] font-medium opacity-70">kg</span></td>
                    <td class="py-2 px-2 text-center text-[12px] text-slate-400">Rp ${item.idr_per_kg.toLocaleString()}</td>
                    <td class="py-2 px-2 text-center font-medium text-[12px] text-slate-700 dark:text-gray-300">${item.qty_usage.toLocaleString()}</td>
                    <td class="py-2 px-3 text-right font-bold text-[12px] ${item.gap_benefit_idr >= 0 ? 'text-emerald-600' : 'text-rose-600'}">${formatIDR(item.gap_benefit_idr)}</td>
                    <td class="py-2 px-3 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded-xs border text-[10px] font-bold tracking-widest ${statusClass} leading-none uppercase">${statusText}</span>
                    </td>
                </tr>
            `);
        });

        mainTable = $('#vaveDetailTable').DataTable({
            pageLength: 10, 
            lengthMenu: [10, 25, 50], 
            ordering: true,
            autoWidth: false,
            responsive: true,
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

        // Force column adjustment to prevent shifting
        setTimeout(() => {
            mainTable.columns.adjust().draw();
        }, 100);

        $('.dataTables_filter input').addClass('bg-white dark:bg-gray-800 border-slate-200 dark:border-gray-700 text-[11px] rounded-xs px-3 py-1.5 w-48 focus:ring-1 focus:ring-primary-500 outline-none transition-all');
    }


    $('#btnExportExcel').on('click', function() {
        let csv = 'Part No,Model,Plan (Kg),Act (Kg),Gap (Kg),IDR/Kg,Qty In (Part),Benefit (IDR),Status\n';
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
