@extends('layouts.app')

@section('title', 'Transaction History')
@section('page_title', 'History')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="mb-4">
        <h2 class="text-xl xl:text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tighter leading-none">Transaction History</h2>
        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 font-normal">Audit trail of all inventory movements and manual adjustments.</p>
    </div>

    {{-- UNIFIED CARD WITH HEADER TOOLBAR & COLLAPSIBLE FILTER --}}
    <div id="historyFilterCard" class="mb-0 bg-white dark:bg-gray-800 rounded-t-xs rounded-b-none border border-b-0 border-slate-200 dark:border-gray-700 overflow-hidden shadow-xs">
        {{-- Card Header: Filter Toggle & Active Date Range Badge (Left) & Export Excel (Right) --}}
        <div class="px-4 sm:px-5 py-3 bg-slate-50/70 dark:bg-slate-900/40 border-b border-slate-100 dark:border-gray-700 flex flex-col md:flex-row md:items-center md:justify-between gap-2.5">
            {{-- Left: Filter Toggle Button & Active Date Badge --}}
            <div class="flex flex-col xs:flex-row sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto">
                <button type="button" id="btnToggleFilter" class="inline-flex items-center justify-center gap-2 px-3.5 h-9 bg-white dark:bg-gray-800 text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700 border border-slate-200 dark:border-gray-700 rounded-xs text-xs font-medium active:scale-[0.98] transition-all shadow-xs w-full xs:w-auto">
                    <i class="fa-solid fa-filter text-primary-600"></i>
                    <span>Filters</span>
                    <i id="historyFilterChevron" class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-200 text-xs ml-1"></i>
                </button>

                {{-- Active Date Range Badge --}}
                <div id="activeDateBadge" class="inline-flex items-center justify-center gap-2 px-3.5 py-1.5 bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded-full border border-primary-100 dark:border-primary-800/50 text-xs font-medium transition-all whitespace-nowrap w-full xs:w-auto">
                    <i class="fa-regular fa-calendar-days text-primary-500 shrink-0"></i>
                    <span id="activeDateText" class="truncate">{{ date('01-m-Y') . ' - ' . date('t-m-Y') }}</span>
                </div>
            </div>

            {{-- Right: Export Excel Button --}}
            <button type="button" id="btnExportExcel" class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-3.5 h-9 bg-emerald-600 hover:bg-emerald-700 border border-transparent rounded-xs text-xs font-medium text-white active:scale-[0.98] transition-all shadow-xs" title="Export Filtered Data to Excel">
                <i class="fa-solid fa-file-excel"></i>
                <span class="truncate">Export Excel</span>
            </button>
        </div>

        {{-- Collapsible Filter Body --}}
        <div id="historyFilterBody" class="hidden p-5 border-b border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                {{-- DATE RANGE --}}
                <div class="w-full">
                    <label class="block mb-2 text-xs font-medium text-slate-900 dark:text-gray-500">Timeline</label>
                    <div class="relative group">
                        <i class="fa-regular fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary-500 text-xs pointer-events-none transition-colors z-10"></i>
                        <input type="text" id="filter_date_range" readonly 
                            value="{{ date('01-m-Y') . ' - ' . date('t-m-Y') }}"
                            class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 rounded-xs h-9 text-xs text-gray-600 dark:text-slate-200 focus:ring-0 focus:border-primary-500 cursor-pointer w-full pl-9 transition-all font-medium" 
                            placeholder="Filter by Date">
                    </div>
                </div>

                {{-- PART --}}
                <div class="w-full">
                    <label class="block mb-2 text-xs font-medium text-slate-900 dark:text-gray-500">Part</label>
                    <select id="filter_product" class="select2-filter w-full">
                        <option value="">All Parts</option>
                        @foreach($products as $product)
                        <option value="{{ $product->hash_id }}">
                            [{{ $product->model_name ?? 'No Model' }}] {{ $product->part_no }}{{ $product->revision ? ' - '.$product->revision : '' }} - {{ $product->part_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- CATEGORY --}}
                <div class="w-full">
                    <label class="block mb-2 text-xs font-medium text-slate-900 dark:text-gray-500">Category</label>
                    <select id="filter_category" class="select2-filter w-full">
                        <option value="">All Transactions</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->hash_id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- User/PIC --}}
                <div class="w-full">
                    <label class="block mb-2 text-xs font-medium text-slate-900 dark:text-gray-500">PIC</label>
                    <select id="filter_user" class="select2-filter w-full">
                        <option value="">All PICs</option>
                        @foreach($pics as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- RESET BUTTON --}}
                <div class="w-full">
                    <button type="button" id="reset_filter" class="w-full h-9 inline-flex items-center justify-center gap-1.5 px-3 bg-slate-100 dark:bg-gray-700/60 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-600 dark:text-gray-300 font-medium text-xs rounded-xs border border-slate-200 dark:border-gray-600 active:scale-[0.98] transition-all shadow-xs" title="Reset all filters">
                        <i class="fa-solid fa-rotate-left text-slate-400"></i>
                        <span class="truncate">Reset Filter</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        #historyFilterCard + div {
            border-top-left-radius: 0 !important;
            border-top-right-radius: 0 !important;
        }
    </style>

    <x-table id="TransactionHistoryTable">
        <thead>
            <tr>
                <th class="w-12 text-center text-xs font-medium tracking-wider">No</th>
                <th class="w-28 text-left text-xs font-medium tracking-wider">Trans. Date</th>
                <th class="w-40 text-left text-xs font-medium tracking-wider">Timestamp</th>
                <th class="w-32 text-left text-xs font-medium tracking-wider">Model</th>
                <th class="text-left text-xs font-medium tracking-wider">Part Details</th>
                <th class="w-28 text-center text-xs font-medium tracking-wider">Category</th>
                <th class="w-32 text-left text-xs font-medium tracking-wider">Origin / Destination</th>
                <th class="w-20 text-center text-xs font-medium tracking-wider">Qty</th>
                <th class="w-32 text-left text-xs font-medium tracking-wider">PIC</th>
                <th class="text-left text-xs font-medium tracking-wider">Remarks</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

@push('scripts')
<script>
    let table;
    let dateRangePicker;

    $(document).ready(function() {
        // Init Select2 with consistent styling
        $('.select2-filter').select2({
            width: '100%',
        });

        // Toggle Filter Logic
        $('#btnToggleFilter').on('click', function(e) {
            e.stopPropagation();
            $('#historyFilterBody').slideToggle(200);
            $('#historyFilterChevron').toggleClass('rotate-180');
        });

        // Date Picker Init
        function updateDateBadgeText() {
            const val = $('#filter_date_range').val();
            if (val && val.includes(' - ')) {
                $('#activeDateText').text(val);
            } else {
                $('#activeDateText').text('All Dates');
            }
        }

        function initDatePicker() {
            dateRangePicker = new Litepicker({
                element: document.getElementById('filter_date_range'),
                singleMode: false,
                autoApply: true,
                format: 'DD-MM-YYYY',
                delimiter: ' - ',
                setup: (picker) => {
                    picker.on('selected', (date1, date2) => {
                        updateDateBadgeText();
                        setTimeout(() => table.ajax.reload(), 10);
                    });
                }
            });
        }
        initDatePicker();

        // Datatable Init
        table = window.defaultDataTable('#TransactionHistoryTable', {
            processing: true,
            serverSide: true,
            language: {
                infoFiltered: "",
                processing: '<div class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 backdrop-blur-[1px] flex items-center justify-center z-20"><div class="loader-spinner !w-8 !h-8 !border-2"></div></div>'
            },
            ajax: {
                url: "{{ route('transactionHistory.getData') }}",
                type: 'GET',
                data: d => {
                    d.product_detail_id = $('#filter_product').val() || null;
                    d.transaction_category_id = $('#filter_category').val() || null;
                    d.user_id = $('#filter_user').val() || null;
                    const dateRange = $('#filter_date_range').val();
                    if (dateRange && dateRange.includes(' - ')) d.date_range = dateRange;
                }
            },
            columns: [
                { data: null, orderable: false, className: 'text-center text-gray-600', render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1 },
                { data: 'transaction_date', className: 'text-xs text-gray-600 font-medium' },
                { 
                    data: 'updated_at', 
                    className: 'text-[10px] text-gray-500 font-mono',
                    render: d => d || '-'
                },
                {
                    data: 'model_name',
                    className: 'text-left font-medium text-slate-700 dark:text-gray-300 text-[10px] tracking-tight',
                    render: d => d || '-'
                },
                { 
                    data: null, 
                    className: 'py-3 min-w-[200px]',
                    render: row => `
                        <div class="flex flex-col">
                            <div class="font-medium text-slate-800 dark:text-white leading-tight tracking-tight">${row.part_no}</div>
                            <div class="text-[10px] text-slate-500 truncate max-w-[250px] mt-1">${row.product_name}</div>
                        </div>
                    `
                },
                { 
                    data: 'category', 
                    className: 'text-center',
                    render: d => {
                        const colorMap = {
                            'IN': 'emerald',
                            'OUT-EVENT': 'amber',
                            'OUT-PP': 'indigo',
                            'OUT-TRIAL': 'rose'
                        };
                        const color = colorMap[d] || (d.includes('OUT') ? 'rose' : 'emerald');
                        const style = `bg-${color}-50 text-${color}-700 border-${color}-100 dark:bg-${color}-900/30 dark:text-${color}-400 dark:border-${color}-800`;
                        return `<span class="inline-block px-2 py-0.5 rounded-xs border text-[10px] font-medium tracking-widest whitespace-nowrap ${style}">${d}</span>`;
                    }
                },
                { data: 'origin_destination', orderable: false, className: 'text-xs text-gray-600 dark:text-gray-400' },
                { data: 'qty', className: 'text-center font-medium text-slate-900 dark:text-white' },
                { data: 'pic_name', className: 'text-xs text-gray-600 dark:text-gray-900' },
                { data: 'remark', defaultContent: '-', className: 'text-xs text-gray-500 font-normal leading-relaxed' }
            ],
            order: [[2, 'desc']], // Default sort by Timestamp Descending
        });

        // Filter Events
        $('#filter_product, #filter_category, #filter_user').on('change', function() {
            table.ajax.reload();
        });
        
        $('#reset_filter').on('click', function() {
            if (dateRangePicker) { dateRangePicker.destroy(); initDatePicker(); }
            $('#filter_date_range').val('').attr('placeholder', 'Filter by Date');
            updateDateBadgeText();
            $('#filter_product, #filter_category, #filter_user').val(null).trigger('change.select2');
            table.ajax.reload(null, true);
        });

        // Export Excel with Filters
        $('#btnExportExcel').on('click', function() {
            const params = new URLSearchParams({
                product_detail_id: $('#filter_product').val() || '',
                transaction_category_id: $('#filter_category').val() || '',
                user_id: $('#filter_user').val() || '',
                date_range: $('#filter_date_range').val() || '',
                search: table ? table.search() : ''
            });
            window.location.href = "{{ route('transactionHistory.exportExcel') }}?" + params.toString();
        });
    });
</script>
@endpush
@endsection