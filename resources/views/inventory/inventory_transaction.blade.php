@extends('layouts.app')
@section('title', 'Inventory Transaction')
@section('page_title', 'Transactions')
@section('header-title', 'Inventory Transaction')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Transaction Form Panel --}}
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-md overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-gray-50 dark:border-gray-700 bg-slate-50/50 dark:bg-slate-900/30">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-3 uppercase tracking-widest">
                        <i class="fa-solid fa-right-left text-blue-600"></i> Transaction Form
                    </h3>
                </div>
                <div class="p-6">
                    <form id="transactionForm">
                        @csrf
                        {{-- Product Selection --}}
                        <div class="mb-5">
                            <div class="flex justify-between items-end mb-3">
                                <label for="product_detail_id" class="block text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em]">Product <span class="text-red-500">*</span></label>
                                <button type="button" id="btn-scan" class="inline-flex items-center px-4 py-1.5 text-[10px] font-bold text-blue-700 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800 transition-all shadow-sm uppercase tracking-wider">
                                    <i class="fa-solid fa-barcode mr-2"></i> Scan Camera
                                </button>
                            </div>
                            <select name="product_detail_id" id="product_detail_id" class="premium-input select2" data-placeholder="Select Product via Search or Scanner..." required>
                                <option value="">Select Product via Search or Scanner...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->hash_id }}" data-partno="{{ $product->part_no }}" data-pcs="{{ $product->pcs_per_unit ?? 1 }}">{{ $product->part_no }} {{ $product->revision ? '- ' . $product->revision : '' }} - {{ $product->part_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Transaction Category --}}
                        <div class="mb-5">
                            <label for="transaction_category_id" class="block mb-3 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em]">Category <span class="text-red-500">*</span></label>
                            <select name="transaction_category_id" id="transaction_category_id" class="premium-input" required>
                                <option value="">Select Category...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->hash_id }}" data-effect="{{ $category->effect }}">
                                        {{ $category->name }} ({{ $category->effect == 1 ? 'IN +' : 'OUT -' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Coil Center (For IN) --}}
                        <div class="mb-5 hidden" id="coilCenterContainer">
                            <label for="coil_center_id" class="block mb-3 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em]">Coil Center <span class="text-red-500">*</span></label>
                            <select name="coil_center_id" id="coil_center_id" class="premium-input select2" style="width: 100%">
                                <option value="">Select Coil Center...</option>
                                @foreach($coilCenters as $cc)
                                    <option value="{{ $cc->hash_id }}">{{ $cc->code }} - {{ $cc->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Supplier & Destination Row (For OUT) --}}
                        <div class="grid grid-cols-2 gap-4 mb-5 hidden" id="outFieldsContainer">
                            <div>
                                <label for="supplier_id" class="block mb-3 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em]">Supplier <span class="text-red-500">*</span></label>
                                <select name="supplier_id" id="supplier_id" class="premium-input select2" style="width: 100%">
                                    <option value="">Select Supplier...</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->hash_id }}">{{ $supplier->code }} - {{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="destination_id" class="block mb-3 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em]">Destination <span class="text-red-500">*</span></label>
                                <select name="destination_id" id="destination_id" class="premium-input select2" style="width: 100%">
                                    <option value="">Select Destination...</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->hash_id }}">{{ $supplier->code }} - {{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Qty & Date Row --}}
                        <div class="grid grid-cols-2 gap-4 mb-5">
                            <div>
                                <label for="qty" class="block mb-3 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em]">Qty (Unit) <span class="text-red-500">*</span></label>
                                <input type="number" name="qty" id="qty" step="1" min="1" class="premium-input w-full" required placeholder="0">
                                
                                {{-- Calculator Preview --}}
                                <div id="qtyPreview" class="mt-2 opacity-0 transition-opacity flex items-center gap-2 text-[10px]">
                                    <i class="fa-solid fa-calculator text-blue-500"></i>
                                    <div class="flex items-center gap-1 font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">
                                        <span id="calcResult">0</span> <span>PCS</span>
                                    </div>
                                    <span class="text-gray-400 text-[9px] font-normal uppercase tracking-tight">(@<span id="pcsInfo">0</span>/Unit)</span>
                                </div>
                            </div>
                            <div>
                                <label for="transaction_date" class="block mb-3 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em]">Date <span class="text-red-500">*</span></label>
                                <input type="date" name="transaction_date" id="transaction_date" value="{{ date('Y-m-d') }}" 
                                    onclick="this.showPicker()"
                                    class="premium-input w-full" required>
                            </div>
                        </div>

                        {{-- PIC --}}
                        <div class="mb-5">
                            <label class="block mb-3 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em]">PIC Name</label>
                            <div class="bg-gray-50 dark:bg-gray-900/50 border border-slate-200 dark:border-gray-700 text-slate-500 text-xs font-semibold rounded-md block w-full px-4 h-10 flex items-center gap-2">
                                <i class="fa-solid fa-user-circle text-gray-400 text-sm"></i>
                                <span>{{ Auth::user()->name }}</span>
                            </div>
                        </div>

                        {{-- Remark --}}
                        <div class="mb-6">
                            <label for="remark" class="block mb-3 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em]">Remark</label>
                            <textarea name="remark" id="remark" rows="2" class="block p-3 w-full text-xs font-semibold text-gray-900 bg-white dark:bg-gray-900 rounded-md border border-slate-200 dark:border-gray-700 focus:ring-0 focus:border-blue-500 transition-all dark:text-white placeholder-gray-300" placeholder="Optional notes..."></textarea>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" class="w-full h-12 text-white bg-slate-900 dark:bg-white dark:text-slate-900 hover:bg-slate-800 focus:ring-4 focus:ring-slate-300 font-bold rounded-md text-xs px-5 py-3 text-center transition-all shadow-md uppercase tracking-widest active:scale-[0.98]">
                            <i class="fa-solid fa-save mr-2 text-[10px]"></i> Save Transaction
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Recent Transactions Table --}}
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-md shadow-sm overflow-hidden h-full flex flex-col">
                <div class="px-6 py-4 border-b border-gray-50 dark:border-gray-700 flex flex-wrap justify-between items-center gap-4 bg-slate-50/50 dark:bg-slate-900/30">
                    <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-widest flex items-center">
                        <i class="fa-solid fa-clock-rotate-left mr-2 text-blue-600 text-sm"></i> Transaction Activity Log
                    </h3>
                    
                    <div class="flex items-center gap-2">
                        <div class="flex items-center">
                            <input type="month" id="filterMonthYear" value="{{ date('Y-m') }}" 
                                onclick="this.showPicker()"
                                class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 rounded-md px-2 h-8 text-[10px] font-bold text-gray-600 dark:text-gray-400 focus:ring-0 focus:border-blue-500 cursor-pointer shadow-sm uppercase">
                        </div>
                        <button id="refreshTable" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 rounded-md shadow-sm">
                            <i class="fa-solid fa-arrows-rotate text-xs"></i>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto flex-1 bg-white dark:bg-gray-800">
                    <x-table id="recentTransactionTable">
                        <thead>
                            <tr>
                                <th class="w-32 text-left font-bold uppercase tracking-wider text-[10px]">Timestamp</th>
                                <th class="text-left font-bold uppercase tracking-wider text-[10px]">Product Information</th>
                                <th class="w-32 text-center font-bold uppercase tracking-wider text-[10px]">Category</th>
                                <th class="w-24 text-center font-bold uppercase tracking-wider text-[10px]">Qty</th>
                                <th class="w-40 text-left font-bold uppercase tracking-wider text-[10px]">PIC</th>
                                <th class="text-left font-bold uppercase tracking-wider text-[10px]">Remark</th>
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
                $('#outFieldsContainer').addClass('hidden');
                $('#coil_center_id').prop('required', false);
                $('#supplier_id').prop('required', false);
                $('#destination_id').prop('required', false);

                if (effect == 1) { // IN
                    $('#coilCenterContainer').removeClass('hidden');
                    $('#coil_center_id').prop('required', true);
                } else if (effect == -1) { // OUT
                    $('#outFieldsContainer').removeClass('hidden');
                     // Supplier & Destination Required for OUT
                    $('#supplier_id').prop('required', true);
                    $('#destination_id').prop('required', true);
                }
            });

            // Initialize extra Select2
            $('#coil_center_id').select2({ width: '100%', placeholder: 'Select Coil Center...' });
            $('#supplier_id').select2({ width: '100%', placeholder: 'Select Supplier...' });
            $('#destination_id').select2({ width: '100%', placeholder: 'Select Destination...' });
        }

        // DataTable
        var table = window.defaultDataTable('recentTransactionTable', {
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('inventory.transaction.data') }}",
                data: function(d) {
                    let val = $('#filterMonthYear').val();
                    if (val) {
                        let parts = val.split('-');
                        d.year = parts[0];
                        d.month = parts[1];
                    }
                }
            },
            columns: [
                { 
                    data: 'transaction_date',
                    render: (d) => `<span class="text-[10px] font-semibold text-gray-500 uppercase tracking-tight">${d}</span>`
                },
                { 
                    data: 'part_no', 
                    render: (d, t, r) => `
                        <div class="flex flex-col">
                            <span class="font-bold text-gray-900 dark:text-white leading-tight uppercase tracking-tighter">${r.part_no}</span>
                            <span class="text-[9px] text-gray-400 font-semibold uppercase tracking-tight">${r.product_name || ''}</span>
                        </div>
                    `
                },
                { 
                    data: 'category',
                    className: 'text-center',
                    render: (d, t, r) => {
                        let isOut = d.includes('OUT');
                        let badgeClass = isOut 
                            ? 'bg-red-50 text-red-700 border-red-100 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800' 
                            : 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800';
                        return `<span class="inline-block px-2 py-0.5 rounded border ${badgeClass} text-[9px] font-bold uppercase tracking-widest whitespace-nowrap">${d}</span>`;
                    }
                },
                { 
                    data: 'qty', 
                    className: 'text-center',
                    render: (d) => `<span class="font-semibold text-slate-700 dark:text-slate-300 text-xs">${d}</span>`
                },
                { 
                    data: 'pic_name',
                    render: (d) => `<span class="text-[10px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-tight">${d}</span>`
                },
                { 
                    data: 'remark', 
                    render: (d) => d ? `<span class="text-[10px] text-gray-400 font-normal italic">${d}</span>` : `<span class="text-gray-200 font-mono text-[10px]">-</span>` 
                }
            ],
            order: [[0, 'desc']],
            pageLength: 10,
            lengthChange: false,
            bLengthChange: false,
            dom: "<'flex flex-col'rt><'flex justify-between items-center mt-4 gap-4 px-2'i p>",
            searching: false
        });

        $('#refreshTable').click(function() {
            table.ajax.reload();
        });

        $('#filterMonthYear').on('change', function() {
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
                    $('#destination_id').val('').trigger('change');
                    $('#coilCenterContainer').addClass('hidden');
                    $('#outFieldsContainer').addClass('hidden');
                    $('#qtyPreview').addClass('opacity-0');
                    
                    // Reload Table
                    table.ajax.reload();
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON.message || 'Something went wrong';
                    Swal.fire('Error', msg, 'error');
                }
            });
        });

        // Live Qty Preview
        function updateQtyPreview() {
            const qty = parseFloat($('#qty').val()) || 0;
            const selectedProduct = $('#product_detail_id').find(':selected');
            const pcsPerUnit = parseFloat(selectedProduct.data('pcs')) || 0;
            
            if (qty > 0 && pcsPerUnit > 0) {
                const totalPcs = qty * pcsPerUnit;
                $('#calcResult').text(new Intl.NumberFormat().format(totalPcs));
                $('#pcsInfo').text(pcsPerUnit);
                $('#qtyPreview').removeClass('opacity-0');
            } else {
                $('#qtyPreview').addClass('opacity-0');
            }
        }

        $('#qty').on('input change', updateQtyPreview);
        $('#product_detail_id').on('change', updateQtyPreview);

    });
</script>
@endpush
