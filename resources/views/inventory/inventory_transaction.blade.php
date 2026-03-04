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
                <div class="px-6 py-4 border-b border-gray-50 dark:border-gray-700 bg-slate-50/50 dark:bg-slate-900/30">
                    <div class="flex flex-wrap justify-between items-center gap-4 mb-2" id="activityHeader">
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-widest flex items-center">
                            <i class="fa-solid fa-clock-rotate-left mr-2 text-blue-600 text-sm"></i> Transaction Activity Log
                        </h3>
                        <div class="flex items-center gap-3">
                            <button id="toggleFilters" class="flex items-center gap-2 px-4 h-9 text-[10px] font-bold text-gray-500 hover:text-blue-600 bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 rounded-md shadow-sm transition-all uppercase tracking-widest group">
                                <i class="fa-solid fa-filter text-[10px] text-gray-400 group-hover:text-blue-500 transition-colors"></i>
                                <span>Filter</span>
                                <i class="fa-solid fa-chevron-down text-[8px] transition-transform duration-300 ml-1" id="filterChevron"></i>
                            </button>
                            <button id="refreshTable" class="w-9 h-9 flex items-center justify-center text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 rounded-md shadow-sm">
                                <i class="fa-solid fa-arrows-rotate text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <div id="filterContainer" class="hidden mt-6 pt-6 border-t border-slate-100 dark:border-gray-700/50">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            {{-- Material Filter --}}
                            <div class="relative group">
                                <label class="block mb-2 text-[9px] font-medium text-gray-400 dark:text-gray-500 tracking-[0.05em]">Material</label>
                                <select id="filter_product_detail_id" class="select2-filter-log w-full">
                                    <option value="">All Materials</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->hash_id }}">{{ $product->part_no }} - {{ $product->part_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Category Filter --}}
                            <div class="relative group">
                                <label class="block mb-2 text-[9px] font-medium text-gray-400 dark:text-gray-500 tracking-[0.05em]">Category</label>
                                <select id="filter_category_id" class="select2-filter-log w-full">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->hash_id }}">{{ $category->name }} ({{ $category->effect == 1 ? 'IN' : 'OUT' }})</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- PIC Filter --}}
                            <div class="relative group">
                                <label class="block mb-2 text-[9px] font-medium text-gray-400 dark:text-gray-500 tracking-[0.05em]">PIC</label>
                                <select id="filter_pic_id" class="select2-filter-log w-full">
                                    <option value="">All PIC</option>
                                    @foreach($pics as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Date Filter --}}
                            <div class="relative group">
                                <label class="block mb-2 text-[9px] font-medium text-gray-400 dark:text-gray-500 tracking-[0.05em]">Date Range</label>
                                <div class="relative">
                                    <i class="fa-regular fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[10px] pointer-events-none transition-colors z-10"></i>
                                    <input type="text" id="filter_date_range" readonly 
                                        value="{{ date('Y-m-01') . ' - ' . date('Y-m-t') }}"
                                        class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 rounded-md pl-9 pr-2 h-9 text-[11px] font-normal text-gray-600 dark:text-gray-400 focus:ring-0 focus:border-blue-500 cursor-pointer shadow-sm w-full transition-all" 
                                        placeholder="Filter by Date">
                                </div>
                            </div>
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
                                @if(Auth::user()->hasAppRole('supervisor') || Auth::user()->hasAppRole('admin'))
                                <th class="w-20 text-center font-bold uppercase tracking-wider text-[10px]">Action</th>
                                @endif
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

{{-- Edit Transaction Modal --}}
<div id="editTransactionModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 flex items-center justify-center w-full h-full bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="relative w-full max-w-lg">
        <div class="relative bg-white rounded-md shadow-2xl dark:bg-gray-800 border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-widest flex items-center gap-3">
                    <i class="fa-solid fa-pen-nib text-blue-600"></i> Adjust Transaction
                </h3>
                <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="editTransactionForm" class="p-6">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id" name="id">

                <div class="space-y-4">
                    {{-- Product Selection --}}
                    <div>
                        <label class="block mb-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Material <span class="text-red-500">*</span></label>
                        <select name="product_detail_id" id="edit_product_detail_id" class="select2-modal" required>
                            <option value="">Select Material...</option>
                            @foreach($products as $product)
                            <option value="{{ $product->hash_id }}">{{ $product->part_no }} {{ $product->revision ? '- ' . $product->revision : '' }} - {{ $product->part_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Transaction Category --}}
                    <div>
                        <label class="block mb-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Category <span class="text-red-500">*</span></label>
                        <select name="transaction_category_id" id="edit_transaction_category_id" class="select2-modal" required>
                            <option value="">Select Category...</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->hash_id }}" data-effect="{{ $category->effect }}">
                                {{ $category->name }} ({{ $category->effect == 1 ? 'IN +' : 'OUT -' }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Conditional Fields --}}
                    <div id="editCoilCenterContainer" class="hidden">
                        <label class="block mb-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Coil Center <span class="text-red-500">*</span></label>
                        <select name="coil_center_id" id="edit_coil_center_id" class="select2-modal">
                            <option value="">Select Coil Center...</option>
                            @foreach($coilCenters as $cc)
                                <option value="{{ $cc->hash_id }}">{{ $cc->code }} - {{ $cc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="editOutFieldsContainer" class="grid grid-cols-2 gap-4 hidden">
                        <div>
                            <label class="block mb-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Supplier <span class="text-red-500">*</span></label>
                            <select name="supplier_id" id="edit_supplier_id" class="select2-modal">
                                <option value="">Select Supplier...</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->hash_id }}">{{ $supplier->code }} - {{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Destination <span class="text-red-500">*</span></label>
                            <select name="destination_id" id="edit_destination_id" class="select2-modal">
                                <option value="">Select Destination...</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->hash_id }}">{{ $supplier->code }} - {{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Qty & Date Row --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Quantity <span class="text-red-500">*</span></label>
                            <input type="number" name="qty" id="edit_qty" step="1" min="1" class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-md text-sm font-bold focus:border-blue-500 focus:ring-0 outline-none transition-all" required placeholder="0">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Date <span class="text-red-500">*</span></label>
                            <input type="date" name="transaction_date" id="edit_transaction_date" class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-md text-xs font-bold focus:border-blue-500 focus:ring-0 outline-none transition-all uppercase" required>
                        </div>
                    </div>

                    {{-- PIC Info --}}
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/10 rounded-md border border-blue-100 dark:border-blue-800/50">
                        <label class="block mb-1.5 text-[8px] font-bold text-blue-500 uppercase tracking-widest">Registered By (PIC)</label>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-user-shield text-blue-400"></i>
                            <span id="edit_pic_name" class="text-xs font-medium text-gray-700 dark:text-gray-300">-</span>
                        </div>
                    </div>

                    {{-- Remark --}}
                    <div>
                        <label class="block mb-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Remark</label>
                        <textarea name="remark" id="edit_remark" rows="2" class="w-full p-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-md text-sm font-normal focus:border-blue-500 focus:ring-0 outline-none transition-all" placeholder="Optional audit explanation..."></textarea>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="closeEditModal()" class="flex-1 py-2.5 text-[10px] font-bold text-gray-500 uppercase tracking-widest hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-all">
                        Discard
                    </button>
                    <button type="submit" class="flex-1 py-2.5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-[10px] font-bold rounded-md shadow-md transition-all active:scale-95 uppercase tracking-widest">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
    /* DataTable Search Box custom padding */
    .dataTables_filter {
        padding: 0 24px 20px 24px !important;
    }
</style>
@endpush

@push('scripts')
{{-- html5-qrcode library --}}
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    $(document).ready(function() {
        // Pre-select product from URL if exists
        const urlParams = new URLSearchParams(window.location.search);
        const preSelectedProduct = urlParams.get('product');
        if (preSelectedProduct) {
            $('#product_detail_id').val(preSelectedProduct).trigger('change');
            // Small delay to ensure Select2 is ready
            setTimeout(() => {
                $('#product_detail_id').val(preSelectedProduct).trigger('change.select2');
            }, 100);
        }

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
        var table = window.defaultDataTable('#recentTransactionTable', {
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('inventory.transaction.data') }}",
                data: function(d) {
                    const dateRange = $('#filter_date_range').val();
                    if (dateRange && dateRange.includes(' - ')) {
                        d.date_range = dateRange;
                    }
                    d.product_detail_id = $('#filter_product_detail_id').val();
                    d.category_id = $('#filter_category_id').val();
                    d.pic_id = $('#filter_pic_id').val();
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
                @if(Auth::user()->hasAppRole('supervisor') || Auth::user()->hasAppRole('admin'))
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: (d, t, r) => {
                        return `
                            <div class="flex items-center justify-center gap-1">
                                <button class="edit-transaction-btn p-1.5 text-slate-400 hover:text-blue-500 transition-colors" data-id="${r.id}" title="Edit">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </button>
                                <button class="delete-transaction-btn p-1.5 text-slate-400 hover:text-red-500 transition-colors" data-id="${r.id}" title="Delete">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        `;
                    }
                }
                @endif
            ],
            order: [[0, 'desc']],
            pageLength: 10,
            lengthChange: false,
            bLengthChange: false,
            dom: "<'flex justify-between items-center mb-2'f><'flex flex-col'rt><'flex justify-between items-center mt-4 gap-4 px-2'i p>",
            searching: true
        });

        $('#refreshTable').click(function() {
            table.ajax.reload();
        });

        // Date Picker Init
        const dateRangePicker = new Litepicker({
            element: document.getElementById('filter_date_range'),
            singleMode: false,
            autoApply: true,
            format: 'YYYY-MM-DD',
            delimiter: ' - ',
            dropdowns: { months: true, years: true },
            startDate: "{{ date('Y-m-01') }}",
            endDate: "{{ date('Y-m-t') }}",
            setup: (picker) => {
                picker.on('selected', (date1, date2) => {
                    setTimeout(() => table.ajax.reload(), 10);
                });
            }
        });

        // Filter Toggle Handler
        $('#toggleFilters').click(function() {
            const container = $('#filterContainer');
            const chevron = $('#filterChevron');
            const header = $('#activityHeader');
            
            if (container.hasClass('hidden')) {
                container.removeClass('hidden').hide().slideDown(300);
                chevron.addClass('rotate-180');
                header.addClass('mb-6');
            } else {
                container.slideUp(300, function() {
                    $(this).addClass('hidden');
                    header.removeClass('mb-4');
                });
                chevron.removeClass('rotate-180');
            }
        });

        // Search Handlers
        $('#filter_product_detail_id, #filter_category_id, #filter_pic_id').on('change', function() {
            table.ajax.reload();
        });

        $('.select2-filter-log').select2({
            width: '100%',
            containerCssClass: 'select2-filter-log',
            dropdownCssClass: 'select2-filter-log'
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

        // --- EDIT & DELETE HANDLERS ---
        $('#recentTransactionTable').on('click', '.edit-transaction-btn', function() {
            const id = $(this).data('id');
            $('#editTransactionForm')[0].reset();
            
            $.ajax({
                url: `{{ url('inventory/transaction') }}/${id}/edit`,
                type: 'GET',
                success: function(response) {
                    $('#edit_id').val(response.id);
                    $('#edit_qty').val(response.qty);
                    $('#edit_transaction_date').val(response.transaction_date);
                    $('#edit_remark').val(response.remark);
                    $('#edit_pic_name').text(response.pic_name || 'System');
                    
                    // Open Modal
                    $('#editTransactionModal').removeClass('hidden').addClass('flex');
                    
                    // Init Select2 for Modal
                    $('.select2-modal').select2({ dropdownParent: $('#editTransactionModal'), width: '100%' });
                    
                    // Trigger changes for conditional fields
                    setTimeout(() => {
                        $('#edit_product_detail_id').val(response.product_detail_id).trigger('change');
                        $('#edit_transaction_category_id').val(response.transaction_category_id).trigger('change');
                        
                        // Handle IN/OUT conditional fields after category change
                        let effect = $('#edit_transaction_category_id.select2-modal').find(':selected').data('effect');
                        if (response.coil_center_id) $('#edit_coil_center_id').val(response.coil_center_id).trigger('change');
                        if (response.supplier_id) $('#edit_supplier_id').val(response.supplier_id).trigger('change');
                        if (response.destination_id) $('#edit_destination_id').val(response.destination_id).trigger('change');
                    }, 200);
                }
            });
        });

        $('#edit_transaction_category_id').on('change', function() {
            let effect = $(this).find(':selected').data('effect');
            $('#editCoilCenterContainer').addClass('hidden');
            $('#editOutFieldsContainer').addClass('hidden');
            
            if (effect == 1) $('#editCoilCenterContainer').removeClass('hidden');
            else if (effect == -1) $('#editOutFieldsContainer').removeClass('hidden');
        });

        $('#editTransactionForm').on('submit', function(e) {
            e.preventDefault();
            const id = $('#edit_id').val();
            $.ajax({
                url: `{{ url('inventory/transaction') }}/${id}`,
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        Swal.fire({ title: 'Success', text: response.message, icon: 'success', timer: 1500, showConfirmButton: false });
                        window.closeEditModal();
                        table.ajax.reload(null, false);
                    }
                },
                error: xhr => Swal.fire('Error', xhr.responseJSON?.message || 'Update failed', 'error')
            });
        });

        $('#recentTransactionTable').on('click', '.delete-transaction-btn', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "Stock levels will be reverted automatically.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('inventory/transaction') }}/${id}`,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({ title: 'Deleted!', text: response.message, icon: 'success', timer: 1500, showConfirmButton: false });
                                table.ajax.reload(null, false);
                            }
                        },
                        error: xhr => Swal.fire('Error', xhr.responseJSON?.message || 'Delete failed', 'error')
                    });
                }
            });
        });

        window.closeEditModal = () => $('#editTransactionModal').addClass('hidden').removeClass('flex');

    });
</script>
@endpush
