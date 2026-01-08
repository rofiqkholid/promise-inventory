@extends('layouts.app')
@section('title', 'Inventory Transaction')
@section('header-title', 'Inventory Transaction')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Transaction Form Panel --}}
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-right-left"></i> Transaction Form
                    </h3>
                </div>
                <div class="p-6">
                    <form id="transactionForm">
                        @csrf
                        {{-- Scan ID (for future use or manual entry) --}}
                        <div class="mb-4">
                            <label for="scan_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Scan ID / Part No</label>
                            <div class="flex gap-2">
                                <input type="text" id="scan_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Scan or Type Part No...">
                                <button type="button" id="btn-scan" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
                                    <i class="fa-solid fa-barcode"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Product Selection --}}
                        <div class="mb-4">
                            <label for="product_detail_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Product <span class="text-red-500">*</span></label>
                            <select name="product_detail_id" id="product_detail_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white select2" required>
                                <option value="">Select Product...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-partno="{{ $product->part_no }}">{{ $product->part_no }} {{ $product->revision ? '- ' . $product->revision : '' }} - {{ $product->part_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Transaction Category --}}
                        <div class="mb-4">
                            <label for="category" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Category <span class="text-red-500">*</span></label>
                            <select name="category" id="category" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                                <option value="">Select Category...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->code }}" data-effect="{{ $category->effect }}" class="{{ $category->effect == 1 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $category->name }} ({{ $category->effect == 1 ? 'IN +' : 'OUT -' }})
                                    </option>
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
                                    <option value="{{ $pic->id }}">{{ $pic->name }}</option>
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
            <div class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-xl overflow-hidden h-full flex flex-col border border-slate-200 dark:border-slate-700">
                <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800">
                    <h3 class="text-lg font-medium text-slate-900 dark:text-white">
                        <i class="fa-solid fa-clock-rotate-left mr-2"></i> Recent Transactions
                    </h3>
                    <button id="refreshTable" class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </button>
                </div>
                <div class="p-4 md:p-6 overflow-x-auto flex-1 bg-white dark:bg-gray-800">
                    <table id="recentTransactionTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-400">
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
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Select2 CSS & JS (Ensure these are loaded in layout or here) --}}
{{-- Using simple Select2 initialization if available, otherwise standard select --}}
<script>
    $(document).ready(function() {
        // Initialize Select2 for Product (Searchable)
        if ($.fn.select2) {
            $('.select2').select2({
                placeholder: "Select Product...",
                allowClear: false,
                width: '100%'
            });

            // Custom Select2 for Category
            function formatCategory(state) {
                if (!state.id) return state.text;
                let effect = $(state.element).data('effect');
                let icon = effect == 1 
                    ? '<i class="fa-solid fa-arrow-down text-emerald-500 mr-2"></i>' // IN (Arrow Down/In)
                    : '<i class="fa-solid fa-arrow-up text-red-500 mr-2"></i>';     // OUT (Arrow Up/Out)
                
                let textClass = effect == 1 ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400';
                
                return $(`<span class="${textClass} flex items-center">${icon}${state.text}</span>`);
            }

            $('#category').select2({
                placeholder: "Select Category...",
                minimumResultsForSearch: Infinity, // Disable search box for categories
                width: '100%',
                templateResult: formatCategory,
                templateSelection: formatCategory
            });
        }

        // DataTable
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
                        // Color label based on category (simple check or passed logic)
                        // Ideally we pass effect in data, but for now simple check
                        let color = d.includes('IN') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                        return `<span class="${color} text-xs font-medium px-2 py-0.5 rounded">${d}</span>`;
                    }
                },
                { data: 'qty', className: 'text-right font-medium' },
                { data: 'pic_name' },
                { data: 'remark', render: (d) => d || '-' }
            ],
            order: [[0, 'desc']], // Order by date desc
            pageLength: 10,
            
            // Custom simplified layout for this widget-like table
            dom: "<'flex flex-col md:flex-row justify-between items-center mb-4'rt><'flex flex-col md:flex-row justify-between items-center mt-4 gap-4 px-2'i p>",
            searching: false
        });

        $('#refreshTable').click(function() {
            table.ajax.reload();
        });

        // Auto-select product on scan (Simple implementation)
        $('#scan_id').on('input', function() {
            let val = $(this).val();
            // Find option with data-partno matching val
            let option = $(`#product_detail_id option`).filter(function() {
                return $(this).data('partno') == val;
            });
            
            if (option.length > 0) {
                $('#product_detail_id').val(option.val()).trigger('change');
                // Optional: Focus Qty
                $('#qty').focus();
            }
        });

        // Form Submit
        $('#transactionForm').submit(function(e) {
            e.preventDefault();
            
            // Basic Frontend Validation
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
                    $('#transaction_date').val(new Date().toISOString().split('T')[0]); // Reset Date to today
                    
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
