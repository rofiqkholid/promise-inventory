@extends('layouts.app')
@section('title', 'Project VAVE Dashboard')
@section('page_title', 'VA/VE Analysis Dashboard (Project)')
@section('header-title', 'Project VAVE Dashboard')

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
    /* Reset paginate float when moved to custom header container */
    #tablePaginateWrapper .dataTables_paginate {
        float: none !important;
        padding-top: 0 !important;
        display: flex;
        align-items: center;
        gap: 2px;
    }
    #tablePaginateWrapper .dataTables_paginate .paginate_button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 26px;
        height: 26px;
        padding: 0 6px;
        font-size: 11px;
        border-radius: 3px;
        cursor: pointer;
        color: #64748b;
        border: 1px solid transparent;
    }
    #tablePaginateWrapper .dataTables_paginate .paginate_button:hover {
        background: #f1f5f9;
        border-color: #e2e8f0;
        color: #334155;
    }
    #tablePaginateWrapper .dataTables_paginate .paginate_button.current {
        background: #e0e0e0ff;
        border-color: #e0e0e0ff;
        color: #475569 !important;
        font-weight: 700;
    }
    #tablePaginateWrapper .dataTables_paginate .paginate_button.disabled {
        opacity: 0.35;
        border: 1px solid;
        cursor: default;
        pointer-events: none;
    }
</style>

<div class="dashboard-container w-full h-auto overflow-y-auto lg:h-[calc(100vh-85px)] lg:overflow-hidden flex flex-col gap-2 pb-0 custom-scrollbar lg:pb-0">
    {{-- Header & KPI Stats --}}
    <div class="flex flex-wrap items-center justify-between gap-y-2 gap-x-4">
        <!-- Title Section -->
        <div class="flex-none">
            <h2 class="text-xl xl:text-2xl font-bold text-gray-900 dark:text-white leading-tight mb-0.5 tracking-tight">Project Model Vave Analysis</h2>
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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-7 gap-4 flex-1">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">Analysis Mode</label>
                        <select id="filterMode" class="w-full text-xs font-medium border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-900 dark:text-white rounded-xs h-[40px] px-3 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all focus:border-primary-500">
                            <option value="monthly">Monthly View</option>
                            <option value="yearly" selected>Yearly Trend</option>
                            <option value="comparison">Yearly Comparison</option>
                        </select>
                    </div>
                    <div class="space-y-1.5 hidden" id="divFilterPeriod">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">Period</label>
                        <input type="month" id="filterPeriod" value="{{ date('Y-m') }}" class="w-full text-xs font-medium border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-900 dark:text-white rounded-xs h-[40px] px-3 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all focus:border-primary-500">
                    </div>
                    <div class="space-y-1.5" id="divFilterYear">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">Year</label>
                        <input type="number" id="filterYear" value="{{ date('Y') }}" min="2000" max="{{ date('Y') + 5 }}" class="w-full text-xs font-medium border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-900 dark:text-white rounded-xs h-[40px] px-3 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all focus:border-primary-500">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">Customer</label>
                        <select id="filterCustomer" class="select2-simple w-full"></select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider">Model</label>
                        <select id="filterModel" class="select2-simple w-full" disabled></select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider">EBD Version</label>
                        <select id="filterEbdVersion" class="select2-simple w-full">
                            <option value="">All Versions</option>
                            @foreach($versions as $version)
                                <option value="{{ $version }}">{{ $version }}</option>
                            @endforeach
                        </select>
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
    <div class="flex flex-col gap-2 flex-1 min-h-0">
        {{-- Charts Row --}}
        <div class="flex flex-col lg:flex-row gap-2 flex-none">
            {{-- Combined Chart --}}
            <div class="chart-card bg-white dark:bg-gray-800 p-2.5 lg:p-3 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative lg:w-1/2 min-w-0 h-[300px] lg:h-[320px]">
                <div class="flex-none flex flex-wrap justify-between items-center gap-2 mb-1">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 flex items-center truncate tracking-tight" id="combinedChartTitle">
                        <i class="fa-solid fa-money-bill-trend-up mr-2 text-emerald-500" id="combinedChartIcon"></i> <span id="combinedChartText">Benefit by Model</span>
                        <span id="combinedChartUnit" class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-slate-700 text-[8px] font-medium text-slate-500 dark:text-slate-400 tracking-wider border border-slate-200/50 dark:border-slate-600/50 flex-shrink-0 whitespace-nowrap">IDR</span>
                    </h3>
                    <div class="flex items-center bg-slate-100 dark:bg-slate-700 rounded-xs p-0.5 shrink-0">
                        <button type="button" class="combined-chart-switch active px-2.5 py-1 text-[10px] font-bold rounded-xs transition-all bg-white dark:bg-gray-600 shadow-sm text-emerald-600 dark:text-emerald-400" data-type="benefit" data-color="emerald">Benefit</button>
                        <button type="button" class="combined-chart-switch px-2.5 py-1 text-[10px] font-bold rounded-xs transition-all text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200" data-type="weight" data-color="blue">Weight</button>
                        <button type="button" class="combined-chart-switch px-2.5 py-1 text-[10px] font-bold rounded-xs transition-all text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200" data-type="efficiency" data-color="amber">Efficiency</button>
                    </div>
                </div>
                <div class="relative w-full flex-1 min-h-0">
                    <canvas id="combinedModelChart"></canvas>
                </div>
            </div>

            {{-- Pareto Chart --}}
            <div class="chart-card bg-white dark:bg-gray-800 p-2.5 lg:p-3 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative lg:w-1/2 min-w-0 h-[300px] lg:h-[320px]">
                <div class="flex-none flex flex-wrap justify-between items-center gap-2 mb-1">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 flex items-center truncate tracking-tight">
                        <i class="fa-solid fa-chart-simple mr-2 text-primary-500"></i> Pareto Analysis
                        <span class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 dark:bg-slate-700 text-[8px] font-medium text-slate-500 dark:text-slate-400 tracking-wider border border-slate-200/50 dark:border-slate-600/50 flex-shrink-0 whitespace-nowrap">Contribution</span>
                    </h3>
                    <div class="flex items-center bg-slate-100 dark:bg-slate-700 rounded-xs p-0.5 shrink-0">
                        <button type="button" class="pareto-chart-switch px-2.5 py-1 text-[10px] font-medium rounded-xs transition-all text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200" data-by="model" data-color="primary">Model</button>
                        <button type="button" class="pareto-chart-switch active px-2.5 py-1 text-[10px] font-medium rounded-xs transition-all bg-white dark:bg-gray-600 shadow-sm text-primary-600 dark:text-primary-400" data-by="part" data-color="primary">Part No</button>
                    </div>
                </div>
                <div class="relative w-full flex-1 min-h-0">
                    <canvas id="paretoChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Detailed Data Table --}}
        <div class="table-container bg-white dark:bg-gray-800 p-3 lg:p-4 rounded-xs border border-gray-200 dark:border-gray-700 flex flex-col relative flex-1 min-h-0">
            <div class="flex-none flex flex-wrap justify-between items-center gap-2 mb-2">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 flex items-center">
                    <i class="fa-solid fa-table-list mr-2 text-primary-500"></i> Detailed VAVE Analysis (Project Model)
                </h3>
                <div class="flex items-center gap-2">
                    {{-- Search with icon inside --}}
                    <div class="relative">
                        <input type="text" id="vaveTableSearch" placeholder="Search Part... " class="bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-[11px] rounded-xs h-[30px] pl-8 pr-3 w-44 focus:ring-1 focus:ring-primary-500 outline-none transition-all text-slate-700 dark:text-slate-200">
                    </div>
                    {{-- Pagination inline --}}
                    <div id="tablePaginateWrapper" class="flex items-center"></div>
                    {{-- Export --}}
                    <button id="btnExportExcel" class="h-[30px] px-3 text-[11px] font-medium text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 tracking-wide flex items-center gap-1.5 bg-emerald-50 dark:bg-emerald-900/20 rounded-xs border border-emerald-100 dark:border-emerald-800/50 transition-all whitespace-nowrap">
                        <i class="fa-solid fa-file-excel text-[10px]"></i> Export Excel
                    </button>
                </div>
            </div>
            
            <div class="flex-1 overflow-auto custom-scrollbar" style="min-height:0">
                <table id="vaveDetailTable" class="w-full text-left" style="min-width:700px">
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
            {{-- Info Row (Showing X to Y of Z) --}}
            <div class="flex-none pt-1.5 mt-1">
                <div id="vaveTableInfo" class="text-[11px] text-slate-500 dark:text-slate-400 font-semibold"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>
$(function() {
    let paretoChart, combinedModelChart;
    let mainTable = null;
    let currentChartType = 'benefit';
    let currentChartMode = 'yearly';
    let currentParetoBy = 'part';
    let currentChartData = {
        meritModels: null,
        trendIdr: null,
        trendKg: null,
        trendLabels: null,
        compIdr: null,
        compKg: null,
        compLabels: null
    };

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
    
    // Dynamic Chart Defaults
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

    // Global Tooltip & Interaction
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

    const commonDataLabels = {
        backgroundColor: isDark ? 'rgba(30, 41, 59, 0.85)' : 'rgba(255, 255, 255, 0.85)',
        borderRadius: 1,
        color: isDark ? '#f8fafc' : '#1e293b',
        font: { weight: 'bold', size: 10 },
        padding: { top: 2, bottom: 0, left: 4, right: 4 },
        anchor: 'center',
        align: 'center',
        clip: false,
        display: (context) => context.dataset.data[context.dataIndex] !== 0 ? 'auto' : false
    };

    const commonLegend = { 
        position: 'bottom', 
        labels: { 
            color: isDark ? '#94a3b8' : '#64748b',
            font: { size: 11 },
            usePointStyle: true,
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
            if (mode === 'yearly' || mode === 'comparison') {
                $('#divFilterPeriod').addClass('hidden');
                $('#divFilterYear').removeClass('hidden');
            } else {
                $('#divFilterPeriod').removeClass('hidden');
                $('#divFilterYear').addClass('hidden');
            }
            refreshData();
        });

        $('#filterPeriod, #filterModel, #filterYear, #filterEbdVersion').on('change', function() {
            refreshData();
        });

        $('#btnReset').on('click', function() {
            $('#filterMode').val('yearly').trigger('change');
            $('#filterCustomer').val('').trigger('change');
            $('#filterModel').val('').trigger('change');
            $('#filterEbdVersion').val('').trigger('change');
            refreshData();
        });
    }

    // Refresh Data & Charts
    function refreshData() {
        const mode = $('#filterMode').val();
        let year, month;

        if (mode === 'yearly' || mode === 'comparison') {
            year = $('#filterYear').val();
            month = null;
        } else {
            const periodValue = $('#filterPeriod').val();
            if (!periodValue) return;
            [year, month] = periodValue.split('-');
            month = parseInt(month);
        }

        const params = {
            mode: mode,
            year: $('#filterYear').val(),
            month: month,
            customer_id: $('#filterCustomer').val(),
            model_id: $('#filterModel').val(),
            ebd_version: $('#filterEbdVersion').val()
        };

        const btn = $('#btnReset');
        btn.html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> ...').prop('disabled', true);

        const restoreBtn = () => {
            btn.html('<i class="fa-solid fa-rotate-left mr-2"></i> Reset Filters').prop('disabled', false);
        };

        currentChartMode = mode;

        // Update UI Titles
        const titlesMap = {
            'monthly': 'by Model',
            'yearly': 'Trend (12 Mo)',
            'comparison': 'Trend (5 Yr)'
        };
        const targetSuffix = titlesMap[mode] || 'by Model';

        $('#labelPeriod').text(mode === 'monthly' ? 'Period' : (mode === 'yearly' ? 'Target Year' : 'End Year (Last 5Y)'));
        
        $('.chart-card h3').not('#combinedChartTitle').each(function() {
            const h3 = $(this);
            let html = h3.html();
            const currentSuffix = ['by Model', 'Trend (12 Mo)', 'Trend (5 Yr)'].find(s => html.includes(s));
            
            if (currentSuffix) {
                h3.html(html.replace(currentSuffix, targetSuffix));
            }
        });

        // UPDATED: Point to projectVaveDashboard routes
        $.get('{{ route("inventory.projectVaveDashboard.chartData") }}', params, function(res) {
            updateKPIs(res.kpi);
            
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

            currentChartData.meritModels = meritModels;

            if (mode === 'comparison' && res.comparison) {
                currentChartData.compLabels = res.comparison.map(c => c.year);
                currentChartData.compIdr = res.comparison.map(c => c.gap_benefit_idr);
                currentChartData.compKg = res.comparison.map(c => c.gap_kg_total);
            } else if (mode === 'yearly' && res.trend) {
                currentChartData.trendLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                const trendIdr = new Array(12).fill(0);
                const trendKg = new Array(12).fill(0);
                
                res.trend.forEach(t => {
                    if (parseFloat(t.gap_benefit_idr) > 0) {
                        trendIdr[t.month_num - 1] = parseFloat(t.gap_benefit_idr);
                        trendKg[t.month_num - 1] = parseFloat(t.gap_kg_total);
                    }
                });

                currentChartData.trendIdr = trendIdr;
                currentChartData.trendKg = trendKg;
            }

            renderCombinedChart();
            updateTable(res.items.filter(item => item.gap_benefit_idr > 0));
            restoreBtn();
        }).fail(function() {
            restoreBtn();
        });

        refreshParetoData();
    }

    function refreshParetoData() {
        const mode = $('#filterMode').val();
        let year, month;

        if (mode === 'yearly' || mode === 'comparison') {
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
            model_id: $('#filterModel').val(),
            ebd_version: $('#filterEbdVersion').val(),
            by: currentParetoBy
        };

        $.get('{{ route("inventory.projectVaveDashboard.paretoData") }}', params, function(res) {
            const meritPareto = res.pareto.filter(p => p.gap_benefit_idr > 0);
            renderParetoChart(meritPareto);
        });
    }

    $('.pareto-chart-switch').on('click', function() {
        const by = $(this).data('by');
        const color = $(this).data('color');
        
        currentParetoBy = by;

        $('.pareto-chart-switch').removeClass('active bg-white dark:bg-gray-600 shadow-sm text-primary-600 dark:text-primary-400')
            .addClass('text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200');
        
        $(this).removeClass('text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200')
            .addClass(`active bg-white dark:bg-gray-600 shadow-sm text-${color}-600 dark:text-${color}-400`);

        refreshParetoData();
    });

    $('.combined-chart-switch').on('click', function() {
        const type = $(this).data('type');
        const color = $(this).data('color');
        
        currentChartType = type;

        $('.combined-chart-switch').removeClass('active bg-white dark:bg-gray-600 shadow-sm text-emerald-600 dark:text-emerald-400 text-blue-600 dark:text-blue-400 text-amber-600 dark:text-amber-400')
            .addClass('text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200');
        
        $(this).removeClass('text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200')
            .addClass(`active bg-white dark:bg-gray-600 shadow-sm text-${color}-600 dark:text-${color}-400`);

        renderCombinedChart();
    });

    function updateKPIs(kpi) {
        const benefitEl = $('#kpi-benefit');
        benefitEl.html(`${formatIDR(kpi.gap_benefit_idr)} <span class="text-[9px] text-slate-400 font-medium ml-0.5">IDR</span>`);
        
        const benefitColor = kpi.gap_benefit_idr >= 0 ? 'text-emerald-600' : 'text-rose-600';
        benefitEl.removeClass('text-emerald-600 text-rose-600 text-slate-900 dark:text-slate-100').addClass(benefitColor);

        $('#kpi-kg').html(`${kpi.gap_kg_total.toFixed(3)} <span class="text-[9px] text-slate-400 font-medium ml-0.5">KG</span>`);
        $('#kpi-saving-rate').html(`${kpi.saving_rate.toFixed(1)} <span class="text-[9px] text-slate-400 font-medium ml-0.5">%</span>`);
        $('#kpi-merit').html(`${kpi.merit_count} <span class="text-[9px] text-slate-400 font-medium ml-0.5">PART</span>`);
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
                        backgroundColor: '#fff',
                        pointBackgroundColor: '#fff',
                        pointBorderColor: chartColors.amber,
                        pointBorderWidth: 2,
                        yAxisID: 'y1',
                        borderWidth: 2,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointStyle: 'circle',
                        datalabels: { 
                            ...commonDataLabels,
                            anchor: 'end',
                            align: 'top',
                            offset: 8,
                            formatter: (v) => v.toFixed(0) + '%' 
                        }
                    },
                    {
                        label: 'Benefit (IDR)',
                        data: data.map(d => d.gap_benefit_idr),
                        backgroundColor: chartColors.emerald,
                        yAxisID: 'y',
                        borderRadius: 2,
                        pointStyle: 'rect',
                        datalabels: { 
                            ...commonDataLabels,
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
                            callback: (v) => Math.abs(v) >= 1000000 ? (v/1000000).toFixed(1) + 'M' : (Math.abs(v) >= 1000 ? (v/1000).toFixed(0) + 'k' : v) 
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
                    tooltip: commonTooltip
                }
            }
        });
    }

    function renderCombinedChart() {
        if (!currentChartData.meritModels) return;

        let labels, data, color, isCurrency, chartType, labelName;

        if (currentChartType === 'benefit') {
            color = chartColors.emerald;
            isCurrency = true;
            chartType = currentChartMode === 'monthly' ? 'bar' : 'line';
            labelName = 'Benefit (IDR)';

            if (currentChartMode === 'monthly') {
                labels = currentChartData.meritModels.labels;
                data = currentChartData.meritModels.idr;
            } else if (currentChartMode === 'yearly') {
                labels = currentChartData.trendLabels;
                data = currentChartData.trendIdr;
            } else {
                labels = currentChartData.compLabels;
                data = currentChartData.compIdr;
            }
        } else if (currentChartType === 'weight') {
            color = chartColors.blue;
            isCurrency = false;
            chartType = currentChartMode === 'monthly' ? 'bar' : 'line';
            labelName = 'Saving Weight (Kg)';

            if (currentChartMode === 'monthly') {
                labels = currentChartData.meritModels.labels;
                data = currentChartData.meritModels.kg;
            } else if (currentChartMode === 'yearly') {
                labels = currentChartData.trendLabels;
                data = currentChartData.trendKg;
            } else {
                labels = currentChartData.compLabels;
                data = currentChartData.compKg;
            }
        } else if (currentChartType === 'efficiency') {
            color = chartColors.amber;
            isCurrency = false;
            chartType = 'bar'; // Efficiency always bar
            labelName = 'Efficiency %';
            
            labels = currentChartData.meritModels.labels;
            data = currentChartData.meritModels.idr.map((idr, i) => {
                const planCost = currentChartData.meritModels.plan_cost[i] || 1;
                return (idr / planCost) * 100;
            });
        }

        // Update UI Titles and Icons
        const iconMap = {
            'benefit': 'fa-money-bill-trend-up',
            'weight': 'fa-weight-hanging',
            'efficiency': 'fa-percent'
        };
        const colorClassMap = {
            'benefit': 'text-emerald-500',
            'weight': 'text-blue-500',
            'efficiency': 'text-amber-500'
        };
        const textMap = {
            'benefit': 'Benefit',
            'weight': 'Saving Weight',
            'efficiency': 'Saving Efficiency'
        };
        const unitMap = {
            'benefit': 'IDR',
            'weight': 'KG',
            'efficiency': '%'
        };

        const titlesMap = {
            'monthly': 'by Model',
            'yearly': 'Trend (12 Mo)',
            'comparison': 'Trend (5 Yr)'
        };
        let targetSuffix = titlesMap[currentChartMode] || 'by Model';
        if (currentChartType === 'efficiency') targetSuffix = 'by Model';

        $('#combinedChartIcon').attr('class', `fa-solid ${iconMap[currentChartType]} mr-2 ${colorClassMap[currentChartType]}`);
        $('#combinedChartText').text(`${textMap[currentChartType]} ${targetSuffix}`);
        if (currentChartType === 'efficiency') {
            $('#combinedChartUnit').hide();
        } else {
            $('#combinedChartUnit').text(unitMap[currentChartType]).show();
        }

        if (combinedModelChart) combinedModelChart.destroy();
        const ctx = document.getElementById('combinedModelChart').getContext('2d');

        if (chartType === 'line') {
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, color + '40');
            gradient.addColorStop(1, color + '00');

            combinedModelChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: labelName,
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
                            ...commonDataLabels,
                            anchor: 'end',
                            align: 'top',
                            offset: 4,
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
                            ticks: {
                                color: Chart.defaults.color,
                                font: { size: 11 },
                                maxTicksLimit: 5,
                                callback: (val) => {
                                    if (isCurrency) {
                                        if (Math.abs(val) >= 1000000) return (val/1000000).toFixed(1) + 'M';
                                        if (Math.abs(val) >= 1000) return (val/1000).toFixed(0) + 'k';
                                        return val;
                                    }
                                    return val;
                                }
                            }
                        },
                        x: { grid: { display: false }, ticks: { color: Chart.defaults.color, font: { size: 11 } } }
                    },
                    plugins: { legend: { display: false }, tooltip: commonTooltip }
                }
            });
        } else {
            let bgColors = color;
            if (currentChartType === 'benefit') {
                bgColors = (context) => {
                    const val = context.dataset.data[context.dataIndex];
                    return val >= 0 ? chartColors.emerald : chartColors.rose;
                };
            }
            
            let formatFn;
            if (currentChartType === 'benefit') {
                formatFn = (v) => v === 0 ? '' : (Math.abs(v) >= 1000000 ? (v/1000000).toFixed(1) + 'M' : (v/1000).toFixed(0) + 'k');
            } else if (currentChartType === 'weight') {
                formatFn = (v) => v === 0 ? '' : v.toFixed(1);
            } else {
                formatFn = (v) => v.toFixed(1) + '%';
            }

            const options = {
                responsive: true,
                maintainAspectRatio: false,
                interaction: commonInteraction,
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grace: '20%',
                        grid: { borderDash: [5, 5], drawBorder: false },
                        ticks: { color: Chart.defaults.color, font: { size: 12 }, maxTicksLimit: 6, callback: (val) => isCurrency ? val.toLocaleString() : val } 
                    },
                    x: { grid: { display: false }, ticks: { color: Chart.defaults.color, font: { size: 12 }, maxTicksLimit: 10 } }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: currentChartType === 'efficiency' ? {
                        ...commonTooltip,
                        callbacks: {
                            label: (ctx) => `Efficiency: ${ctx.raw.toFixed(2)}%`
                        }
                    } : commonTooltip
                }
            };

            if (currentChartType === 'efficiency') {
                options.indexAxis = 'y';
                options.scales.x = { 
                    beginAtZero: true, 
                    grace: '15%',
                    grid: { borderDash: [5, 5], drawBorder: false }, 
                    ticks: { 
                        color: Chart.defaults.color, 
                        font: { size: 11 },
                        callback: (v) => v + '%'
                    } 
                };
                options.scales.y = { 
                    grid: { display: false },
                    ticks: { color: Chart.defaults.color, font: { size: 11 } } 
                };
            }

            combinedModelChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: labelName,
                        data: data,
                        backgroundColor: bgColors,
                        borderRadius: 2,
                        datalabels: { 
                            ...commonDataLabels,
                            formatter: formatFn
                        }
                    }]
                },
                options: options
            });
        }
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
                    <td class="py-2 px-2 text-center font-mono text-[12px] text-slate-500 dark:text-gray-400" data-order="${item.plan_kg}">${item.plan_kg.toLocaleString('id-ID', { minimumFractionDigits: 3, maximumFractionDigits: 3 })} <span class="text-[9px] opacity-50">kg</span></td>
                    <td class="py-2 px-2 text-center font-mono text-[12px] text-slate-500 dark:text-gray-400" data-order="${item.actual_kg}">${item.actual_kg.toLocaleString('id-ID', { minimumFractionDigits: 3, maximumFractionDigits: 3 })} <span class="text-[9px] opacity-50">kg</span></td>
                    <td class="py-2 px-2 text-center font-bold text-[12px] ${gapPerUnit >= 0 ? 'text-emerald-600' : 'text-rose-600'}" data-order="${gapPerUnit}">${gapPerUnit.toLocaleString('id-ID', { minimumFractionDigits: 3, maximumFractionDigits: 3 })} <span class="text-[10px] font-medium opacity-70">kg</span></td>
                    <td class="py-2 px-2 text-center text-[12px] text-slate-400" data-order="${item.idr_per_kg}">${formatIDR(item.idr_per_kg)}</td>
                    <td class="py-2 px-2 text-center font-medium text-[12px] text-slate-700 dark:text-gray-300" data-order="${item.qty_usage}">${item.qty_usage.toLocaleString('id-ID')}</td>
                    <td class="py-2 px-3 text-right font-bold text-[12px] ${item.gap_benefit_idr >= 0 ? 'text-emerald-600' : 'text-rose-600'}" data-order="${item.gap_benefit_idr}">${formatIDR(item.gap_benefit_idr)}</td>
                    <td class="py-2 px-3 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded-xs border text-[10px] font-bold tracking-widest ${statusClass} leading-none uppercase">${statusText}</span>
                    </td>
                </tr>
            `);
        });

        mainTable = $('#vaveDetailTable').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25],
            ordering: true,
            autoWidth: false,
            searching: true,
            dom: 'rtp',
            columnDefs: [
                { targets: [3, 4, 5, 6, 7, 8], type: 'num' }
            ],
            language: {
                paginate: {
                    previous: "<i class='fa-solid fa-chevron-left'></i>",
                    next: "<i class='fa-solid fa-chevron-right'></i>"
                }
            }
        });

        // Wire custom search input
        $('#vaveTableSearch').off('keyup').on('keyup', function() {
            mainTable.search(this.value).draw();
        });

        // Move pagination & info to custom containers
        setTimeout(() => {
            mainTable.columns.adjust();
            const paginateEl = $('#vaveDetailTable_wrapper .dataTables_paginate').detach();
            const infoEl = $('#vaveDetailTable_wrapper .dataTables_info').detach();
            $('#tablePaginateWrapper').empty().append(paginateEl);
            $('#vaveTableInfo').empty().append(infoEl);
        }, 150);
    }


    $('#btnExportExcel').on('click', function() {
        const wb = XLSX.utils.book_new();
        const data = [];
        
        // 1. Header Information
        data.push(['VAVE ANALYSIS REPORT - PROJECT MODEL']);
        data.push(['Generated At:', new Date().toLocaleString()]);
        data.push(['Customer:', $('#filterCustomer option:selected').text() || 'All']);
        data.push(['Model:', $('#filterModel option:selected').text() || 'All']);
        data.push(['Period:', $('#filterPeriod').val() || 'All']);
        data.push([]); // Spacer

        // 2. Table Headers
        data.push([
            'Part No', 'Model', 'EBD Version', 'Plan (Kg)', 'Actual (Kg)', 
            'Gap (Kg)', 'IDR/Kg', 'Qty In', 'Benefit (IDR)', 'Status'
        ]);

        // 3. Table Content
        const rows = mainTable ? mainTable.rows({ search: 'applied' }).nodes() : $('#vaveDetailTable tbody tr');
        $(rows).each(function() {
            const row = [];
            $(this).find('td').each(function(i) {
                let text = $(this).text().trim();
                if (i === 0) text = $(this).find('div').text().trim(); // Part No
                
                // Clean numeric values using data-order attribute if present (locale-independent)
                let orderVal = $(this).attr('data-order');
                if (orderVal !== undefined) {
                    const num = parseFloat(orderVal) || 0;
                    if (i >= 3 && i <= 5) {
                        row.push({ v: num, t: 'n', z: '#,##0.000' });
                    } else if (i === 6 || i === 8) {
                        row.push({ v: num, t: 'n', z: '"Rp "#,##0' });
                    } else {
                        row.push({ v: num, t: 'n', z: '#,##0' });
                    }
                } else if (i >= 3 && i <= 8) {
                    // Fallback in case data-order is missing
                    if (i >= 3 && i <= 5) {
                        text = text.replace(/[^0-9.-]/g, '');
                        row.push({ v: parseFloat(text) || 0, t: 'n', z: '#,##0.000' });
                    } else if (i === 6 || i === 8) {
                        text = text.replace(/[^0-9-]/g, '');
                        row.push({ v: parseFloat(text) || 0, t: 'n', z: '"Rp "#,##0' });
                    } else {
                        text = text.replace(/[^0-9-]/g, '');
                        row.push({ v: parseFloat(text) || 0, t: 'n', z: '#,##0' });
                    }
                } else {
                    row.push(text);
                }
            });
            data.push(row);
        });

        const ws = XLSX.utils.aoa_to_sheet(data);

        // 4. Styling (Column Widths)
        ws['!cols'] = [
            { wch: 25 }, { wch: 15 }, { wch: 15 }, { wch: 12 }, { wch: 12 },
            { wch: 12 }, { wch: 15 }, { wch: 10 }, { wch: 20 }, { wch: 12 }
        ];

        XLSX.utils.book_append_sheet(wb, ws, "Analysis");
        XLSX.writeFile(wb, "VAVE_Project_Analysis_" + new Date().getTime() + ".xlsx");
    });

    loadFilters();
    refreshData();
});
</script>
@endpush
