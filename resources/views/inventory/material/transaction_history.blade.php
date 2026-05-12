@extends('layouts.app')

@section('title', 'Transaction History')
@section('page_title', 'History')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-4">
        <div>
            <h2 class="text-xl xl:text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight leading-none">Transaction History</h2>
            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 font-normal">Audit trail of all inventory movements and manual adjustments.</p>
        </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="mb-4 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-gray-700 bg-slate-50/50 dark:bg-slate-900/30">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fa-solid fa-filter text-primary-600"></i> History Filter
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 items-end">
                {{-- DATE RANGE --}}
                <div class="w-full">
                    <label class="block mb-2 text-xs font-medium text-slate-900 dark:text-gray-500">Timeline</label>
                    <div class="relative group">
                        <i class="fa-regular fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-slate-900 group-focus-within:text-primary-500 text-[10px] pointer-events-none transition-colors z-10"></i>
                        <input type="text" id="filter_date_range" readonly 
                            value="{{ date('01-m-Y') . ' - ' . date('t-m-Y') }}"
                            class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 rounded-xs h-10 text-xs text-gray-600 dark:text-slate-900 focus:ring-0 focus:border-primary-500 cursor-pointer w-full pl-10 transition-all font-medium" 
                            placeholder="Filter by Date">
                    </div>
                </div>

                {{-- PART --}}
                <div class="w-full lg:col-span-1">
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

                {{-- ACTIONS --}}
                <div class="flex items-center w-full">
                    <button type="button" id="reset_filter" class="h-9 px-4 text-xs font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xs border border-slate-200 dark:border-gray-700 transition-all active:scale-95 shadow-sm">
                        <i class="fa-solid fa-rotate-left mr-1.5"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- History Table Panel --}}
    <div class="bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xs overflow-hidden flex flex-col">
        <div class="px-6 py-5 border-b border-slate-100 dark:border-gray-700 bg-slate-50/50 dark:bg-slate-900/30">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white flex items-center">
                <i class="fa-solid fa-table-list mr-3 text-primary-600"></i> History Data
            </h3>
        </div>
        <div class="overflow-x-auto">
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
    </div>
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

        // Date Picker Init
        function initDatePicker() {
            dateRangePicker = new Litepicker({
                element: document.getElementById('filter_date_range'),
                singleMode: false,
                autoApply: true,
                format: 'DD-MM-YYYY',
                delimiter: ' - ',
                setup: (picker) => {
                    picker.on('selected', (date1, date2) => {
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
            dom: "<'flex flex-col sm:flex-row justify-between items-center mb-4 gap-4'<'flex items-center gap-4'l B> f><'overflow-x-auto w-full border border-slate-100 dark:border-gray-700/50 rounded-xs mb-2't><'flex flex-col md:flex-row justify-between items-center mt-4 gap-4 px-2'i p>",
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

        // Events
        $('#filter_product, #filter_category, #filter_user').on('change', function() {
            table.ajax.reload();
        });
        
        $('#reset_filter').on('click', function() {
            if (dateRangePicker) { dateRangePicker.destroy(); initDatePicker(); }
            $('#filter_date_range').val('').attr('placeholder', 'Filter by Date');
            $('#filter_product, #filter_category, #filter_user').val(null).trigger('change.select2');
            table.ajax.reload(null, true);
        });
    });
</script>

@endpush
@endsection