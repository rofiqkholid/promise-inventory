@extends('layouts.app')
@section('title', 'Inventory Transaction')
@section('page_title', 'Transactions')
@section('header-title', 'Inventory Transaction')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Transaction Form Panel --}}
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 sm:rounded-lg overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-right-left"></i> Transaction Form
                    </h3>
                </div>
                <div class="p-6">
                    <form id="transactionForm">
                        @csrf
                        {{-- Product Selection --}}
                        <div class="mb-4">
                            <div class="flex justify-between items-end mb-2">
                                <label for="product_detail_id" class="block text-sm font-medium text-gray-900 dark:text-white">Product <span class="text-red-500">*</span></label>
                                <button type="button" id="btn-scan" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-blue-700 bg-blue-50 border border-blue-300 rounded-lg hover:bg-blue-100 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-700 dark:hover:bg-blue-900/60 transition-all shadow-sm">
                                    <i class="fa-solid fa-barcode mr-2"></i> Scan Camera
                                </button>
                            </div>
                            <select name="product_detail_id" id="product_detail_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white select2" data-placeholder="Select Product..." required>
                                <option value="">Select Product...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->hash_id }}" data-partno="{{ $product->part_no }}">{{ $product->part_no }} {{ $product->revision ? '- ' . $product->revision : '' }} - {{ $product->part_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Transaction Category --}}
                        <div class="mb-4">
                            <label for="transaction_category_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Category <span class="text-red-500">*</span></label>
                            <select name="transaction_category_id" id="transaction_category_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                                <option value="">Select Category...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->hash_id }}" data-effect="{{ $category->effect }}" class="{{ $category->effect == 1 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $category->name }} ({{ $category->effect == 1 ? 'IN +' : 'OUT -' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Coil Center (For IN) --}}
                        <div class="mb-4 hidden" id="coilCenterContainer">
                            <label for="coil_center_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Coil Center <span class="text-red-500">*</span></label>
                            <select name="coil_center_id" id="coil_center_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white select2" style="width: 100%">
                                <option value="">Select Coil Center...</option>
                                @foreach($coilCenters as $cc)
                                    <option value="{{ $cc->hash_id }}">{{ $cc->code }} - {{ $cc->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Supplier (For OUT) --}}
                        <div class="mb-4 hidden" id="supplierContainer">
                            <label for="supplier_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Supplier (Destination) <span class="text-red-500">*</span></label>
                            <select name="supplier_id" id="supplier_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white select2" style="width: 100%">
                                <option value="">Select Supplier...</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->hash_id }}">{{ $supplier->code }} - {{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Qty & Date Row --}}
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="qty" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Qty (Unit) <span class="text-red-500">*</span></label>
                                <input type="number" name="qty" id="qty" step="1" min="1" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required placeholder="0">
                            </div>
                            <div>
                                <label for="transaction_date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date <span class="text-red-500">*</span></label>
                                <input type="date" name="transaction_date" id="transaction_date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required value="{{ date('Y-m-d') }}">
                            </div>
                        </div>

                        {{-- PIC --}}
                        <div class="mb-4">
                            <label for="pic_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">PIC Name <span class="text-red-500">*</span></label>
                            <select name="pic_id" id="pic_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white select2" required>
                                <option value="">Select PIC...</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->hash_id }}">{{ $pic->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Remark --}}
                        <div class="mb-6">
                            <label for="remark" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Remark</label>
                            <textarea name="remark" id="remark" rows="2" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Optional notes..."></textarea>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-3 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            <i class="fa-solid fa-save mr-2"></i> Save Transaction
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Recent Transactions Table --}}
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 sm:rounded-lg overflow-hidden h-full flex flex-col">
                <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800">
                    <h3 class="text-lg font-medium text-slate-900 dark:text-white">
                        <i class="fa-solid fa-clock-rotate-left mr-2"></i> Recent Transactions
                    </h3>
                    <button id="refreshTable" class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </button>
                </div>
                <div class="overflow-x-auto flex-1 bg-white dark:bg-gray-800">
                    <x-table id="recentTransactionTable">
                        <thead>
                            <tr>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Product</th>
                                <th class="px-6 py-3">Category</th>
                                <th class="px-6 py-3 text-right">Qty</th>
                                <th class="px-6 py-3">PIC</th>
                                <th class="px-6 py-3">Remark</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </x-table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Scanner Modal --}}
@include('components.scanner-modal')
@endsection

@push('style')
@endpush

@push('scripts')
{{-- html5-qrcode library --}}
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            function formatCategory(state) {
                if (!state.id) return state.text;
                let effect = $(state.element).data('effect');
                let icon = effect == 1 
                    ? '<i class="fa-solid fa-arrow-down text-emerald-500 mr-2"></i>' // IN (Arrow Down/In)
                    : '<i class="fa-solid fa-arrow-up text-red-500 mr-2"></i>';     // OUT (Arrow Up/Out)
                
                let textClass = effect == 1 ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400';
                
                return $(`<span class="${textClass} flex items-center">${icon}${state.text}</span>`);
            }

            $('#transaction_category_id').select2({
                minimumResultsForSearch: Infinity,
                templateResult: formatCategory,
                templateSelection: formatCategory,
                width: '100%'
            }).on('change', function() {
                let selected = $(this).select2('data')[0];
                let element = $(selected.element);
                let effect = element.data('effect');
                
                // Reset
                $('#coilCenterContainer').addClass('hidden');
                $('#supplierContainer').addClass('hidden');
                $('#coil_center_id').prop('required', false);
                $('#supplier_id').prop('required', false);

                if (effect == 1) { // IN
                    $('#coilCenterContainer').removeClass('hidden');
                    $('#coil_center_id').prop('required', true);
                } else if (effect == -1) { // OUT
                    $('#supplierContainer').removeClass('hidden');
                     // Supplier Required for OUT
                    $('#supplier_id').prop('required', true);
                }
            });

            // Initialize extra Select2
            $('#coil_center_id').select2({ width: '100%', placeholder: 'Select Coil Center...' });
            $('#supplier_id').select2({ width: '100%', placeholder: 'Select Supplier...' });
        }

        // DataTable
        var table = window.defaultDataTable('recentTransactionTable', {
            processing: true,
            serverSide: true,
            ajax: "{{ route('inventory.transaction.data') }}",
            columns: [
                { data: 'transaction_date', width: '15%' },
                { 
                    data: 'part_no', 
                    render: (d, t, r) => `<div class="font-medium text-gray-900 dark:text-white">${r.part_no}</div><div class="text-xs">${r.product_name}</div>`
                },
                { 
                    data: 'category',
                    render: (d, t, r) => {
                        let color = d.includes('IN') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                        return `<span class="${color} text-xs font-medium px-2 py-0.5 rounded">${d}</span>`;
                    }
                },
                { data: 'qty', className: 'text-right font-medium' },
                { data: 'pic_name' },
                { data: 'remark', render: (d) => d || '-' }
            ],
            order: [[0, 'desc']],
            pageLength: 10,
            dom: "<'flex flex-col md:flex-row justify-between items-center mb-4'rt><'flex flex-col md:flex-row justify-between items-center mt-4 gap-4 px-2'i p>",
            searching: false
        });

        $('#refreshTable').click(function() {
            table.ajax.reload();
        });

        // Initialize Global Scanner Service (Unified)
        if (typeof InventoryScanner !== 'undefined') {
            new InventoryScanner({
                selectId: '#product_detail_id',
                scanButtonId: '#btn-scan',
                qtyInputId: '#qty',
                modalId: '#scannerModal'
            });
        }

        // Form Submit
        $('#transactionForm').submit(function(e) {
            e.preventDefault();
            
            if (!$('#product_detail_id').val()) {
                Swal.fire('Error', 'Please select a product', 'error');
                return;
            }

            let formData = $(this).serialize();
            
            $.ajax({
                url: "{{ route('inventory.transaction.store') }}",
                type: "POST",
                data: formData,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    
                    // Reset Form
                    $('#transactionForm')[0].reset();
                    $('#product_detail_id').val('').trigger('change'); // Reset Select2
                    $('#transaction_category_id').val('').trigger('change'); // Reset Category
                    $('#transaction_date').val(new Date().toISOString().split('T')[0]); // Reset Date to today
                    $('#coil_center_id').val('').trigger('change');
                    $('#supplier_id').val('').trigger('change');
                    $('#coilCenterContainer').addClass('hidden');
                    $('#supplierContainer').addClass('hidden');
                    
                    // Reload Table
                    table.ajax.reload();
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON.message || 'Something went wrong';
                    Swal.fire('Error', msg, 'error');
                }
            });
        });

    });
</script>
@endpush
