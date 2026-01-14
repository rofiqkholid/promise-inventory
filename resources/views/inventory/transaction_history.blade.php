@extends('layouts.app')
@section('title', 'Inventory Product Management')
@section('header-title', 'Inventory Product')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Transaction History</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400"></p>
        </div>
    </div>
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-xl overflow-hidden h-full flex flex-col border border-slate-200 dark:border-slate-700">
            <div class="p-4 md:p-6 overflow-x-auto flex-1 bg-white dark:bg-gray-800">
                <table id="TransactionHistoryTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-400">
                        <tr class="text-center">
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
        function editTransaction(id) {
    $('#editTransactionForm')[0].reset();
    
    $.ajax({
        url: "{{ url('inventory/transaction-history') }}/" + id + "/edit",
        type: 'GET',
        success: function(response) {
            // 1. Isi input teks biasa
            $('#edit_id').val(response.id);
            $('#edit_qty').val(response.qty);
            $('#edit_transaction_date').val(response.transaction_date);
            $('#edit_remark').val(response.remark);

            // 2. Tampilkan Modal (Wajib sebelum inisialisasi Select2)
            $('#editTransactionModal').removeClass('hidden').addClass('flex');

            // 3. Inisialisasi Select2 khusus untuk modal
            $('.select2-modal').select2({
                dropdownParent: $('#editTransactionModal'),
                width: '100%'
            });

            // 4. Set nilai dropdown dengan jeda sedikit (untuk memastikan Select2 siap)
            setTimeout(function() {
                $('#edit_product_detail_id').val(response.product_detail_id).trigger('change');
                $('#edit_transaction_category_id').val(response.transaction_category_id).trigger('change');
                $('#edit_pic_id').val(response.pic_id).trigger('change');
            }, 100);
        }
    });
}

        function closeModal() {
            $('#editTransactionModal').addClass('hidden').removeClass('flex');
        }
        $(document).ready(function() {
            $('#TransactionHistoryTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('inventory.transactionHistory.getData') }}",
                    type: 'GET',
                    data: function(d) {
                        d.product_detail_id = $('#filter_product').val();
                    }
                },
                columnDefs: [{
                    targets: '_all',
                    className: 'text-center'
                }],
                columns: [{
                        data: 'transaction_date',
                        name: 'transaction_date'
                    },
                    {
                        data: null,
                        name: 'product_name',
                        render: function(data, type, row) {
                            return `<div class="font-medium text-slate-900 dark:text-white">${row.part_no}</div>
                                <div class="text-xs text-gray-500">${row.product_name}</div>`;
                        }
                    },
                    {
                        data: 'category',
                        name: 'category'
                    },
                    {
                        data: 'qty',
                        name: 'qty'
                    },
                    {
                        data: 'pic_name',
                        name: 'pic_name'
                    },
                    {
                        data: 'remark',
                        name: 'remark',
                        render: function(data) {
                            return data ? `<span class="italic text-gray-400 text-xs">${data}</span>` : '-';
                        }
                    },
                    {
                        data: null,
                        name: 'action',
                        searchable: false,
                        orderable: false,
                        render: function(data, type, row) {
                            // Kita simpan hashed ID di atribut data-id
                            return `
                                        <div class="flex justify-center">
                                            <button type="button" data-id="${row.id}" class="edit-transaction-btn text-blue-600 hover:text-blue-900 p-2">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    `;
                        }
                    }
                ],

                language: {
                    processing: '<div class="p-4 text-blue-500">Loading data...</div>',
                },
                order: [
                    [0, 'desc']
                ]
            });
            $('#filter_product').on('change', function() {
                $('#TransactionHistoryTable').DataTable().ajax.reload();
            });
            // 2. Di dalam $(document).ready(function() { ... }), tambahkan ini:
            $('#TransactionHistoryTable').on('click', '.edit-transaction-btn', function() {
                let hashedId = $(this).data('id'); // Mengambil string hash
                editTransaction(hashedId);
            });
            // HANDLE SUBMIT FORM UPDATE
            $('#editTransactionForm').on('submit', function(e) {
                e.preventDefault();

                const id = $('#edit_id').val(); // Ini adalah Hashed ID
                const formData = $(this).serialize();

                $.ajax({
                    url: `{{ url('inventory/transaction-history') }}/${id}`,
                    type: 'POST', // Gunakan PUT untuk update
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            closeModal();
                            $('#TransactionHistoryTable').DataTable().ajax.reload(); // Refresh tabel
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = xhr.responseJSON?.message || 'Update failed';
                        alert('Error: ' + errorMsg);
                    }
                });
            });
        });
        // Fungsi untuk membuka modal dan mengambil data
    </script>
    @endpush