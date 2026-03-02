@extends('layouts.app')

@section('title', 'Transaction History')
@section('page_title', 'History')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="mb-6">
        <h2 class="text-2xl font-black text-gray-900 dark:text-white sm:text-3xl tracking-tighter">Transaction Journal</h2>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium tracking-wider">Audit trail of all inventory movements and manual adjustments.</p>
    </div>

    {{-- FILTER BAR --}}
    <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-md border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            {{-- DATE RANGE --}}
            <div class="w-full">
                <label class="block mb-1.5 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] leading-tight">Timeline</label>
                <div class="relative group">
                    <i class="fa-regular fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 text-xs pointer-events-none transition-colors z-10"></i>
                    <input type="text" id="filter_date_range" readonly class="premium-input w-full pl-9 pr-3 cursor-pointer" placeholder="Filter by Date">
                </div>
            </div>

            {{-- PRODUCT --}}
            <div class="w-full lg:col-span-1">
                <label class="block mb-1.5 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] leading-tight">Material / Part</label>
                <select id="filter_product" class="select2-filter w-full">
                    <option value="">All Products</option>
                    @foreach($products as $product)
                    <option value="{{ $product->hash_id }}">
                        {{ $product->part_no }} {{ $product->revision ? '- '.$product->revision : '' }} - {{ $product->part_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- CATEGORY --}}
            <div class="w-full">
                <label class="block mb-1.5 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] leading-tight">Type</label>
                <select id="filter_category" class="select2-filter w-full">
                    <option value="">All Transactions</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->hash_id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- User/PIC --}}
            <div class="w-full">
                <label class="block mb-1.5 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] leading-tight">Validator / PIC</label>
                <select id="filter_user" class="select2-filter w-full">
                    <option value="">All Users</option>
                    @foreach($pics as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- ACTIONS --}}
            <div class="flex items-center gap-2 w-full">
                <button type="button" id="reset_filter" class="flex-1 h-10 px-4 text-[10px] font-bold text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-all uppercase tracking-widest">
                    <i class="fa-solid fa-rotate-left mr-1"></i> Reset
                </button>
                <button type="button" id="apply_filter" class="flex-1 h-10 inline-flex items-center justify-center px-6 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-[10px] font-bold rounded-md transition-all shadow-sm hover:bg-slate-800 active:scale-95 gap-2 uppercase tracking-widest">
                    <i class="fa-solid fa-magnifying-glass text-[9px]"></i> Search
                </button>
            </div>
        </div>
    </div>

    {{-- Journal Table --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-md shadow-sm overflow-hidden">
        <x-table id="TransactionHistoryTable">
            <thead>
                <tr>
                    <th class="w-12">No</th>
                    <th class="w-32 text-left">Timestamp</th>
                    <th class="text-left">Material Details</th>
                    <th class="w-28 text-center">Type</th>
                    <th class="text-left">Origin / Destination</th>
                    <th class="w-24 text-center">Qty</th>
                    <th class="w-40 text-left">Validator</th>
                    <th class="text-left">Remarks</th>
                </tr>
            </thead>
            <tbody></tbody>
        </x-table>
    </div>
</div>

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
                format: 'YYYY-MM-DD',
                delimiter: ' - ',
                dropdowns: { months: true, years: true }
            });
        }
        initDatePicker();

        // Datatable Init
        table = window.defaultDataTable('#TransactionHistoryTable', {
            processing: true,
            serverSide: true,
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
                { data: null, className: 'text-center font-bold text-gray-300', render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1 },
                { data: 'transaction_date', className: 'text-[10px] font-mono font-bold text-gray-500 uppercase' },
                { 
                    data: null, 
                    className: 'py-3',
                    render: row => `
                        <div class="font-bold text-gray-900 dark:text-white leading-tight uppercase tracking-tighter">${row.part_no}</div>
                        <div class="text-[10px] text-gray-400 font-medium uppercase tracking-tight truncate max-w-[200px]">${row.product_name}</div>
                    `
                },
                { 
                    data: 'category', 
                    className: 'text-center',
                    render: d => {
                        const style = d === 'IN' 
                            ? 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800' 
                            : 'bg-red-50 text-red-700 border-red-100 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800';
                        return `<span class="px-2 py-0.5 text-[9px] font-bold rounded border ${style} uppercase tracking-widest">${d}</span>`;
                    }
                },
                { data: 'origin_destination', className: 'text-[10px] text-gray-500 font-medium italic' },
                { data: 'qty', className: 'text-center font-bold text-gray-900 dark:text-white' },
                { data: 'pic_name', className: 'text-[11px] font-medium text-blue-600 dark:text-blue-400' },
                { data: 'remark', defaultContent: '-', className: 'text-[10px] text-gray-400 font-normal' }
            ],
            order: [[1, 'desc']],
        });

        // Events
        $('#apply_filter').on('click', () => table.ajax.reload());
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