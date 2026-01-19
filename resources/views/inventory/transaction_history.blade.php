@extends('layouts.app')

@section('title', 'Transaction History')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Transaction History</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400"></p>
        </div>
    </div>
    {{-- FILTER BAR (REFINED) --}}
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="px-5 py-4 flex items-center gap-4">

            {{-- ICON --}}
            <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/30">
                <i class="fa-solid fa-filter text-sm"></i>
            </div>

            {{-- DATE RANGE --}}
            <div class="relative min-w-[160px]">
                <i class="fa-regular fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text"
                    id="filter_date_range"
                    readonly
                    class="w-full h-[42px] pl-9 pr-3 text-sm
           bg-gray-50 border border-gray-300 rounded-lg
           focus:outline-none focus:ring-2 focus:ring-blue-500
           dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    placeholder="Select Date Range">
                <input type="hidden" id="filter_date_from">
                <input type="hidden" id="filter_date_to">
            </div>

            {{-- PRODUCT --}}
            <select id="filter_product" class="select2-filter min-w-[160px]">
                <option value="">All Product</option>
                @foreach($products as $product)
                <option value="{{ $product->hash_id }}">
                    {{ $product->part_no }} - {{ $product->part_name }}
                </option>
                @endforeach
            </select>


            {{-- CATEGORY --}}
            <select id="filter_category" class="select2-filter min-w-[140px]">
                <option value="">All Category</option>
                @foreach($categories as $category)
                <option value="{{ $category->hash_id }}">
                    {{ $category->name }}
                </option>
                @endforeach
            </select>


            {{-- PIC --}}
            <select id="filter_pic" class="select2-filter min-w-[140px]">
                <option value="">All PIC</option>
                @foreach($pics as $pic)
                <option value="{{ $pic->hash_id }}">
                    {{ $pic->name }}
                </option>
                @endforeach
            </select>


            {{-- SPACER --}}
            <div class="flex-1"></div>

            {{-- RESET --}}
            <button id="reset_filter"
                class="inline-flex items-center gap-2 text-sm font-medium
                   text-gray-600 hover:text-red-600
                   dark:text-gray-300 dark:hover:text-red-400">
                <i class="fa-solid fa-rotate-left text-xs"></i>
                Reset
            </button>
            <button id="apply_filter"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
           text-white bg-blue-600 hover:bg-blue-700 rounded-lg">
                <i class="fa-solid fa-check"></i>
                Apply
            </button>

        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-xl overflow-hidden h-full flex flex-col border border-slate-200 dark:border-slate-700">
            <div class="p-4 md:p-6 overflow-x-auto flex-1 bg-white dark:bg-gray-800">
                <table id="TransactionHistoryTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-400">
                        <tr class="text-center">
                            <th scope="col" class="px-6 py-3 w-16">No</th>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Product</th>
                            <th class="px-6 py-3">Category</th>
                            <th class="px-6 py-3">Qty</th>
                            <th class="px-6 py-3">PIC</th>
                            <th class="px-6 py-3">Remark</th>
                            <th class="px-6 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    <div id="editTransactionModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 justify-center items-center w-full h-full bg-slate-900/50 backdrop-blur-sm flex p-4">
        <div class="relative w-full max-w-lg h-auto mx-auto">
            <div class="relative bg-white rounded-xl shadow-xl dark:bg-gray-800 border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700 rounded-t-xl">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-pen-to-square"></i> Edit Transaction
                    </h3>
                    <button type="button" onclick="closeModal()" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                        <i class="fa-solid fa-xmark w-5 h-5"></i>
                    </button>
                </div>

                <div class="p-6">
                    <form id="editTransactionForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_id" name="id">

                        {{-- Product Selection --}}
                        <div class="mb-4">
                            <label for="edit_product_detail_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Product <span class="text-red-500">*</span></label>
                            <select name="product_detail_id" id="edit_product_detail_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white select2-modal" required>
                                <option value="">Select Product...</option>
                                @foreach($products as $product)
                                <option value="{{ $product->hash_id }}">{{ $product->part_no }} {{ $product->revision ? '- ' . $product->revision : '' }} - {{ $product->part_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Transaction Category --}}
                        <div class="mb-4">
                            <label for="edit_transaction_category_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Category <span class="text-red-500">*</span></label>
                            <select name="transaction_category_id" id="edit_transaction_category_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white select2-modal" required>
                                <option value="">Select Category...</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->hash_id }}" data-effect="{{ $category->effect }}">
                                    {{ $category->name }} ({{ $category->effect == 1 ? 'IN +' : 'OUT -' }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Qty & Date Row --}}
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="edit_qty" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Qty (Unit) <span class="text-red-500">*</span></label>
                                <input type="number" name="qty" id="edit_qty" step="1" min="1" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required placeholder="0">
                            </div>
                            <div>
                                <label for="edit_transaction_date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date <span class="text-red-500">*</span></label>
                                <input type="date" name="transaction_date" id="edit_transaction_date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                            </div>
                        </div>

                        {{-- PIC --}}
                        <div class="mb-4">
                            <label for="edit_pic_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">PIC Name <span class="text-red-500">*</span></label>
                            <select name="pic_id" id="edit_pic_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white select2-modal" required>
                                <option value="">Select PIC...</option>
                                @foreach($pics as $pic)
                                <option value="{{ $pic->hash_id }}">{{ $pic->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Remark --}}
                        <div class="mb-6">
                            <label for="edit_remark" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Remark</label>
                            <textarea name="remark" id="edit_remark" rows="2" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Optional notes..."></textarea>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex gap-3">
                            <button type="submit" class="flex-1 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-3 text-center dark:bg-blue-600 dark:hover:bg-blue-700">
                                <i class="fa-solid fa-save mr-2"></i> Update Transaction
                            </button>
                            <button type="button" onclick="closeModal()" class="flex-1 text-gray-700 bg-gray-200 hover:bg-gray-300 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-3 text-center dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endsection
    @push('scripts')
    <script>
        let table;
        let dateRangePicker;
        let isResetting = false;

        $(document).ready(function() {

            /* =========================
             * INIT SELECT2 FILTERS
             * ========================= */
            $('#filter_product').select2({
                width: '100%',
                placeholder: 'Select Product',
                allowClear: true
            });

            $('#filter_category').select2({
                width: '100%',
                placeholder: 'Select Category',
                allowClear: true
            });

            $('#filter_pic').select2({
                width: '100%',
                placeholder: 'Select PIC',
                allowClear: true
            });


            /* =========================
             * DATE RANGE DEFAULT (THIS MONTH)
             * ========================= */
            const startOfMonth = new Date();
            startOfMonth.setDate(1);

            const endOfMonth = new Date(
                startOfMonth.getFullYear(),
                startOfMonth.getMonth() + 1,
                0
            );


            /* =========================
             * INIT DATATABLE (SINGLE INIT)
             * ========================= */
            table = window.defaultDataTable('TransactionHistoryTable', {
                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ route('transactionHistory.getData') }}",
                    type: 'GET',
                    data: d => {
                        d.product_detail_id = $('#filter_product').val() || null;
                        d.transaction_category_id = $('#filter_category').val() || null;
                        d.pic_id = $('#filter_pic').val() || null;

                        const dateRange = $('#filter_date_range').val();
                        if (dateRange && dateRange.includes(' - ')) {
                            d.date_range = dateRange;
                        } else {
                            delete d.date_range; // ⬅️ PENTING
                        }
                    }
                },

                columns: [{
                        data: null,
                        className: 'text-center',
                        render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1
                    },
                    {
                        data: 'transaction_date'
                    },

                    {
                        data: null,
                        render: row => `
                <div class="font-semibold text-slate-900">
                    ${row.part_no}
                </div>
                <div class="text-xs text-gray-500">
                    ${row.product_name}
                </div>
            `
                    },

                    {
                        data: 'category',
                        render: d => {
                            if (d === 'IN') {
                                return `<span class="badge badge-success">IN</span>`;
                            }
                            return `<span class="badge badge-danger">${d}</span>`;
                        }
                    },

                    {
                        data: 'qty',
                        className: 'font-semibold'
                    },

                    {
                        data: 'pic_name'
                    },

                    {
                        data: 'remark',
                        defaultContent: '-',
                        className: 'text-xs text-gray-500'
                    },

                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: row => `
                <button
                    class="edit-transaction-btn h-8 w-8 inline-flex items-center justify-center
                           text-blue-600 rounded-lg bg-blue-50 hover:bg-blue-100
                           transition"
                    data-id="${row.id}">
                    <i class="fa-solid fa-pen-to-square text-sm"></i>
                </button>
            `
                    }
                ],

                order: [
                    [0, 'desc']
                ],

                /* ===============================
                 * EXPORT CONFIG
                 * =============================== */
                buttons: [{
                        extend: 'excel',
                        title: 'Transaction History',
                        filename: 'transaction-history',
                        exportOptions: {
                            columns: ':not(:last-child)' // ❌ exclude Action
                        }
                    },
                    {
                        extend: 'csv',
                        title: 'Transaction History',
                        filename: 'transaction-history',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'pdf',
                        title: 'Transaction History',
                        filename: 'transaction-history',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    }
                ]
            });


            /* =========================
             * INIT LITEPICKER (AFTER TABLE)
             * ========================= */
            function initDatePicker() {
                dateRangePicker = new Litepicker({
                    element: document.getElementById('filter_date_range'),
                    singleMode: false,
                    autoApply: true,
                    format: 'YYYY-MM-DD',
                    delimiter: ' - ',
                    dropdowns: {
                        months: true,
                        years: true
                    }
                });
            }
            initDatePicker();

            /* =========================
             * APPLY FILTER
             * ========================= */
            $('#apply_filter').on('click', function() {
                table.ajax.reload();
            });

            /* =========================
             * RESET FILTER
             * ========================= */
            $('#reset_filter').on('click', function() {

                // 1. destroy litepicker instance (WAJIB)
                if (dateRangePicker) {
                    dateRangePicker.destroy();
                    dateRangePicker = null;
                }

                // 2. clear input manual
                $('#filter_date_range')
                    .val('')
                    .attr('placeholder', 'Select Date Range');

                // 3. init ulang litepicker
                initDatePicker();

                // 4. reset select2
                $('#filter_product').val(null).trigger('change.select2');
                $('#filter_category').val(null).trigger('change.select2');
                $('#filter_pic').val(null).trigger('change.select2');

                // 5. reload ke default (tanpa cache)
                table.ajax.reload(null, true);
            });




            /* =========================
             * EDIT BUTTON
             * ========================= */
            $('#TransactionHistoryTable').on('click', '.edit-transaction-btn', function() {
                editTransaction($(this).data('id'));
            });

            /* =========================
             * SUBMIT EDIT FORM
             * ========================= */
            $('#editTransactionForm').on('submit', function(e) {
                e.preventDefault();

                const id = $('#edit_id').val();

                $.ajax({
                    url: `{{ url('transaction-history') }}/${id}`,
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            closeModal();
                            table.ajax.reload();
                        }
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Update failed');
                    }
                });
            });



            function editTransaction(id) {
                $('#editTransactionForm')[0].reset();

                $.ajax({
                    url: "{{ url('transaction-history') }}/" + id + "/edit",
                    type: 'GET',
                    success: function(response) {

                        $('#edit_id').val(response.id);
                        $('#edit_qty').val(response.qty);
                        $('#edit_transaction_date').val(response.transaction_date);
                        $('#edit_remark').val(response.remark);

                        $('#editTransactionModal')
                            .removeClass('hidden')
                            .addClass('flex');

                        $('.select2-modal').select2({
                            dropdownParent: $('#editTransactionModal'),
                            width: '100%'
                        });

                        setTimeout(() => {
                            $('#edit_product_detail_id').val(response.product_detail_id).trigger('change');
                            $('#edit_transaction_category_id').val(response.transaction_category_id).trigger('change');
                            $('#edit_pic_id').val(response.pic_id).trigger('change');
                        }, 100);
                    }
                });
            }

            function closeModal() {
                $('#editTransactionModal')
                    .addClass('hidden')
                    .removeClass('flex');
            }
        });
    </script>

    @endpush