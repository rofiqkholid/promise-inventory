@extends('layouts.app')
@section('title', 'Inventory Transaction')
@section('page_title', 'Transactions')
@section('header-title', 'Inventory Transaction')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="mb-4">
        <h2 class="text-xl xl:text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tighter leading-none">Inventory Transactions</h2>
        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 font-normal">Manage material incoming and outgoing records.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-stretch">
        {{-- Transaction Form Panel --}}
        <div class="lg:col-span-1">
            <div id="transactionFormCard" class="bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xs overflow-hidden shadow-xs h-full flex flex-col">
                <div class="px-5 py-3.5 border-b border-slate-100 dark:border-gray-700 bg-slate-50/70 dark:bg-slate-900/40 flex items-center justify-between shrink-0">
                    <h3 class="text-xs font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2.5 tracking-tight">
                        <i class="fa-solid fa-right-left text-primary-600"></i> Transaction Input
                    </h3>
                    <button type="button" id="btn-scan" class="inline-flex items-center justify-center gap-1.5 px-3 h-8 text-xs font-medium text-primary-700 bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/30 dark:text-primary-400 border border-primary-200 dark:border-primary-800/60 rounded-xs transition-all active:scale-95 shadow-2xs">
                        <i class="fa-solid fa-barcode"></i> Scan Camera
                    </button>
                </div>
                <div class="p-5 flex-1 flex flex-col justify-between">
                    <form id="transactionForm" class="space-y-4">
                        @csrf
                        {{-- Product Selection --}}
                        <div>
                            <label for="product_detail_id" class="block mb-1.5 text-xs font-medium text-slate-700 dark:text-gray-300">Part Number <span class="text-red-500">*</span></label>
                            <select name="product_detail_id" id="product_detail_id" class="premium-input select2" data-placeholder="Select Part via Search or Scanner..." required>
                                <option value="">Select Part via Search or Scanner...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->hash_id }}" 
                                        data-partno="{{ $product->part_no }}" 
                                        data-pcs="{{ $product->pcs_per_unit ?? 1 }}"
                                        data-weight="{{ $product->weight_kg ?? 0 }}"
                                        data-gross="{{ $product->gross_coil ?? 0 }}"
                                        data-top="{{ $product->top_coil ?? 0 }}"
                                        data-end="{{ $product->end_coil ?? 0 }}"
                                        data-pitch="{{ $product->pitch ?? 0 }}"
                                        data-ppp="{{ $product->pcs_per_pitch ?? 1 }}"
                                        data-unit="{{ $product->unit_name ?? '' }}"
                                        data-stock="{{ $product->current_stock_qty ?? 0 }}"
                                    >{{ '[' . ($product->model_name ?? 'No Model') . '] ' . $product->part_no }}{{ $product->revision ? ' - ' . $product->revision : '' }} - {{ $product->part_name }}</option>
                                @endforeach
                            </select>
                            
                            {{-- Balance Display --}}
                            <div id="balanceDisplay" class="mt-2 hidden">
                                <div class="px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-gray-700/80 rounded-xs flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-boxes-stacked text-primary-500 text-xs"></i>
                                        <span class="text-xs font-medium text-slate-500 dark:text-gray-400">Current Balance</span>
                                    </div>
                                    <span id="currentBalanceVal" class="text-xs font-bold text-slate-900 dark:text-white">0</span>
                                </div>
                            </div>
                        </div>

                        {{-- Transaction Category --}}
                        <div>
                            <label for="transaction_category_id" class="block mb-1.5 text-xs font-medium text-slate-700 dark:text-gray-300">Category <span class="text-red-500">*</span></label>
                            <select name="transaction_category_id" id="transaction_category_id" class="premium-input" required>
                                <option value="">Select Category...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->hash_id }}" data-effect="{{ $category->effect }}" data-code="{{ $category->code }}" data-name="{{ $category->name }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Coil Center (For IN) --}}
                        <div class="hidden" id="coilCenterContainer">
                            <label for="coil_center_id" class="block mb-1.5 text-xs font-medium text-slate-700 dark:text-gray-300">Coil Center <span class="text-red-500">*</span></label>
                            <select name="coil_center_id" id="coil_center_id" class="premium-input select2" style="width: 100%">
                                <option value="">Select Coil Center...</option>
                                @foreach($coilCenters as $cc)
                                    <option value="{{ $cc->hash_id }}">{{ $cc->code }} - {{ $cc->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Supplier & Destination Row (For OUT) --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 hidden" id="outFieldsContainer">
                            <div>
                                <label for="supplier_id" class="block mb-1.5 text-xs font-medium text-slate-700 dark:text-gray-300">Supplier <span class="text-red-500">*</span></label>
                                <select name="supplier_id" id="supplier_id" class="premium-input select2" style="width: 100%">
                                    <option value="">Select Supplier...</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->hash_id }}">{{ $supplier->code }} - {{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="destination_id" class="block mb-1.5 text-xs font-medium text-slate-700 dark:text-gray-300">Destination <span class="text-red-500">*</span></label>
                                <select name="destination_id" id="destination_id" class="premium-input select2" style="width: 100%">
                                    <option value="">Select Destination...</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->hash_id }}">{{ $supplier->code }} - {{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Qty & Date Row --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label id="qtyLabel" for="qty" class="block mb-1.5 text-xs font-medium text-slate-700 dark:text-gray-300">Qty (Unit) <span class="text-red-500">*</span></label>
                                <input type="number" name="qty" id="qty" step="any" min="0.01" class="premium-input w-full" required placeholder="0">
                                
                                {{-- Calculator Preview --}}
                                <div id="qtyPreview" class="mt-1.5 opacity-0 transition-opacity flex items-center gap-1.5 text-[10px]">
                                    <i class="fa-solid fa-calculator text-primary-500"></i>
                                    <div class="flex items-center gap-1 font-medium text-primary-600 dark:text-primary-400 tracking-wider">
                                        <span id="calcResult">0</span> <span>PCS</span>
                                    </div>
                                    <span class="text-gray-400 text-[9px] font-normal uppercase tracking-tight">(@<span id="pcsInfo">0</span>)</span>
                                </div>
                            </div>
                            <div>
                                <label for="transaction_date" class="block mb-1.5 text-xs font-medium text-slate-700 dark:text-gray-300">Date <span class="text-red-500">*</span></label>
                                <input type="date" name="transaction_date" id="transaction_date" value="{{ date('Y-m-d') }}" 
                                    onclick="this.showPicker()"
                                    class="premium-input w-full" required>
                            </div>
                        </div>

                        {{-- PIC --}}
                        <div>
                            <label class="block mb-1.5 text-xs font-medium text-slate-700 dark:text-gray-300">PIC Name</label>
                            <div class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-300 text-xs font-medium rounded-xs block w-full px-3.5 h-9 flex items-center gap-2">
                                <i class="fa-solid fa-user-circle text-slate-400 text-sm"></i>
                                <span>{{ Auth::user()->name }}</span>
                            </div>
                        </div>

                        {{-- Remark --}}
                        <div>
                            <label for="remark" class="block mb-1.5 text-xs font-medium text-slate-700 dark:text-gray-300">Remark</label>
                            <textarea name="remark" id="remark" rows="2" class="block p-3 w-full text-xs font-medium text-gray-900 bg-white dark:bg-gray-900 rounded-xs border border-slate-200 dark:border-gray-700 focus:ring-0 focus:border-primary-500 transition-all dark:text-white placeholder-gray-400" placeholder="Optional notes..."></textarea>
                        </div>

                        {{-- Submit Button --}}
                        <div class="pt-2">
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-5 h-9 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-xs font-medium text-white active:scale-[0.98] transition-all shadow-xs">
                                <i class="fa-solid fa-floppy-disk"></i> Save Transaction
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Recent Transactions Table --}}
        <div class="lg:col-span-2">
            <div id="recentTransactionCard" class="bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xs overflow-hidden shadow-xs h-full flex flex-col justify-between">
                {{-- Header Toolbar --}}
                <div class="px-5 py-3.5 border-b border-slate-100 dark:border-gray-700 bg-slate-50/70 dark:bg-slate-900/40 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shrink-0" id="activityHeader">
                    <h3 class="text-xs font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2.5 tracking-tight shrink-0">
                        <i class="fa-solid fa-clock-rotate-left text-primary-600"></i> Transaction Log
                    </h3>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        {{-- Search Input Integrated in Toolbar --}}
                        <div class="relative w-full sm:w-56">
                            <input type="text" id="logSearchInput" placeholder="Search records..." class="w-full h-8 pl-8 pr-3 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xs text-xs text-slate-700 dark:text-slate-200 focus:outline-none focus:border-primary-500 transition-all shadow-2xs">
                        </div>

                        <button id="toggleFilters" type="button" class="inline-flex items-center justify-center gap-1.5 px-3 h-8 bg-white dark:bg-gray-800 text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700 border border-slate-200 dark:border-gray-700 rounded-xs text-xs font-medium active:scale-95 transition-all shadow-2xs shrink-0">
                            <i class="fa-solid fa-filter text-primary-600 text-xs"></i>
                            <span>Filters</span>
                            <i class="fa-solid fa-chevron-down text-slate-400 text-[10px] transition-transform duration-200 ml-0.5" id="filterChevron"></i>
                        </button>

                        {{-- Active Date Range Badge --}}
                        <div id="activeDateBadge" class="hidden sm:inline-flex items-center justify-center gap-1.5 px-3 py-1 bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded-full border border-primary-100 dark:border-primary-800/50 text-[11px] font-medium transition-all whitespace-nowrap shrink-0">
                            <i class="fa-regular fa-calendar-days text-primary-500 shrink-0"></i>
                            <span id="activeDateText" class="truncate">{{ date('01-m-Y') . ' - ' . date('t-m-Y') }}</span>
                        </div>
                        <button id="refreshTable" type="button" class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xs transition-all active:scale-95 shadow-2xs shrink-0" title="Refresh Table">
                            <i class="fa-solid fa-arrows-rotate text-xs"></i>
                        </button>
                        <a href="{{ route('transactionHistory') }}" class="inline-flex items-center justify-center gap-1.5 px-2.5 h-8 bg-slate-100 dark:bg-gray-700 hover:bg-slate-200 dark:hover:bg-gray-600 text-slate-600 dark:text-gray-200 rounded-xs text-xs font-medium transition-all shadow-2xs shrink-0" title="View Full Transaction History">
                            <span>View All</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>

                {{-- Collapsible Filter Body --}}
                <div id="filterContainer" class="hidden p-5 border-b border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 shrink-0">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                        {{-- Part Filter --}}
                        <div class="w-full">
                            <label class="block mb-1.5 text-xs font-medium text-slate-700 dark:text-gray-400">Part</label>
                            <select id="filter_product_detail_id" class="select2-filter-log w-full">
                                <option value="">All Parts</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->hash_id }}">{{ '[' . ($product->model_name ?? 'No Model') . '] ' . $product->part_no }}{{ $product->revision ? ' - ' . $product->revision : '' }} - {{ $product->part_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Category Filter --}}
                        <div class="w-full">
                            <label class="block mb-1.5 text-xs font-medium text-slate-700 dark:text-gray-400">Category</label>
                            <select id="filter_category_id" class="select2-filter-log w-full">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->hash_id }}" data-effect="{{ $category->effect }}" data-code="{{ $category->code }}" data-name="{{ $category->name }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- PIC Filter --}}
                        <div class="w-full">
                            <label class="block mb-1.5 text-xs font-medium text-slate-700 dark:text-gray-400">PIC</label>
                            <select id="filter_pic_id" class="select2-filter-log w-full">
                                <option value="">All PIC</option>
                                @foreach($pics as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Date Filter --}}
                        <div class="w-full">
                            <label class="block mb-1.5 text-xs font-medium text-slate-700 dark:text-gray-400">Date Range</label>
                            <div class="relative group">
                                <i class="fa-regular fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none transition-colors z-10"></i>
                                <input type="text" id="filter_date_range" readonly 
                                    value="{{ date('Y-m-01') . ' - ' . date('Y-m-t') }}"
                                    class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 rounded-xs h-9 text-xs text-slate-700 dark:text-slate-200 pl-9 cursor-pointer w-full transition-all font-medium" 
                                    placeholder="Filter by Date">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-1 flex flex-col justify-between overflow-x-auto min-h-[360px] bg-white dark:bg-gray-800">
                    <x-table id="recentTransactionTable">
                        <thead>
                            <tr>
                                <th class="w-32 text-left font-medium tracking-wider text-xs">Timestamp</th>
                                <th class="text-left font-medium tracking-wider text-xs">Model</th>
                                <th class="text-left font-medium tracking-wider text-xs">Part Information</th>
                                <th class="w-32 text-center font-medium tracking-wider text-xs">Category</th>
                                <th class="w-24 text-center font-medium tracking-wider text-xs">Qty</th>
                                <th class="w-40 text-left font-medium tracking-wider text-xs">PIC</th>
                                @if(Auth::user()->hasMenuPermission('inventory.transactions.index', 'edit') || Auth::user()->hasMenuPermission('inventory.transactions.index', 'delete'))
                                <th class="w-[100px] text-center font-medium tracking-wider text-xs">Action</th>
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
<div id="editTransactionModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 flex items-center justify-center w-full h-full bg-slate-900/50 transition-all p-4">
    <div class="relative w-full max-w-lg">
        <div class="relative bg-white rounded-xs dark:bg-gray-800 border border-gray-100 dark:border-gray-700 overflow-hidden">
            <button type="button" onclick="closeEditModal()" class="text-gray-400 absolute top-3 right-3 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded-xs text-sm p-2 ml-auto inline-flex items-center dark:hover:bg-gray-700 dark:hover:text-white z-10 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>

            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-primary-50/80 dark:bg-slate-900/50">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white tracking-wider flex items-center gap-3">
                    <i class="fa-solid fa-pen-nib text-primary-600"></i> Adjust Transaction
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium tracking-tight">Modify transaction record and audit history</p>
            </div>

            <form id="editTransactionForm" class="px-6 py-5">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id" name="id">

                <div class="space-y-3.5">
                    {{-- Product Selection --}}
                    <div>
                        <label class="block mb-2 text-xs font-medium text-slate-900 dark:text-gray-300 tracking-wider">Material <span class="text-red-500">*</span></label>
                        <select id="edit_product_detail_id" class="select2-modal" disabled>
                            <option value="">Select Material...</option>
                            @foreach($products as $product)
                             <option value="{{ $product->hash_id }}"
                                 data-partno="[{{ $product->model_name ?? 'No Model' }}] {{ $product->part_no }}" 
                                 data-pcs="{{ $product->pcs_per_unit ?? 1 }}"
                                 data-weight="{{ $product->weight_kg ?? 0 }}"
                                 data-unit="{{ $product->unit_name ?? '' }}"
                            >{{ '[' . ($product->model_name ?? 'No Model') . '] ' . $product->part_no }}{{ $product->revision ? ' - ' . $product->revision : '' }} - {{ $product->part_name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="product_detail_id" id="hidden_edit_product_detail_id">
                    </div>

                    {{-- Transaction Category --}}
                    <div>
                        <label class="block mb-2 text-xs font-medium text-slate-900 dark:text-gray-300 tracking-wider">Category <span class="text-red-500">*</span></label>
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
                        <label class="block mb-2 text-xs font-medium text-slate-900 dark:text-gray-300 tracking-wider">Coil Center <span class="text-red-500">*</span></label>
                        <select name="coil_center_id" id="edit_coil_center_id" class="select2-modal">
                            <option value="">Select Coil Center...</option>
                            @foreach($coilCenters as $cc)
                                <option value="{{ $cc->hash_id }}">{{ $cc->code }} - {{ $cc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="editOutFieldsContainer" class="grid grid-cols-2 gap-5 hidden">
                        <div>
                            <label class="block mb-2 text-xs font-medium text-slate-900 dark:text-gray-300 tracking-wider">Supplier <span class="text-red-500">*</span></label>
                            <select name="supplier_id" id="edit_supplier_id" class="select2-modal">
                                <option value="">Select Supplier...</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->hash_id }}">{{ $supplier->code }} - {{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block mb-2 text-xs font-medium text-slate-900 dark:text-gray-300 tracking-wider">Destination <span class="text-red-500">*</span></label>
                            <select name="destination_id" id="edit_destination_id" class="select2-modal">
                                <option value="">Select Destination...</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->hash_id }}">{{ $supplier->code }} - {{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Qty & Date Row --}}
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block mb-2 text-xs font-medium text-slate-900 dark:text-gray-300 tracking-wider">Quantity <span class="text-red-500">*</span></label>
                            <input type="number" name="qty" id="edit_qty" step="1" min="1" class="w-full h-10 px-3 bg-gray-50 border border-slate-200 dark:bg-gray-900 dark:border-gray-700 rounded-xs text-[11px] font-medium focus:border-primary-500 focus:ring-0 outline-none transition-all dark:text-white" required placeholder="0">
                        </div>
                        <div>
                            <label class="block mb-2 text-xs font-medium text-slate-600 dark:text-gray-300 tracking-wider">Date <span class="text-red-500">*</span></label>
                            <input type="date" name="transaction_date" id="edit_transaction_date" class="w-full h-10 px-3 bg-gray-50 border border-slate-200 dark:bg-gray-900 dark:border-gray-700 rounded-xs text-[10px] font-medium focus:border-primary-500 focus:ring-0 outline-none transition-all dark:text-white" required>
                        </div>
                    </div>

                    {{-- PIC Info --}}
                    <div class="p-3 bg-primary-50/50 dark:bg-primary-900/10 rounded-xs border border-primary-100/50 dark:border-primary-800/30 flex items-center justify-between mt-1">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400">
                                <i class="fa-solid fa-user-shield text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <label class="block text-[10px] font-medium text-primary-400 dark:text-primary-500 tracking-widest leading-none mb-1">PIC (Audit Log)</label>
                                <span id="edit_pic_name" class="text-xs font-medium text-slate-700 dark:text-gray-300">-</span>
                            </div>
                        </div>
                    </div>

                    {{-- Remark --}}
                    <div>
                        <label class="block mb-2 text-xs font-medium text-slate-600 dark:text-gray-300 tracking-wider">Adjustment Remark</label>
                        <textarea name="remark" id="edit_remark" rows="2" class="w-full p-4 bg-gray-50 dark:bg-gray-900 border border-slate-200 dark:border-gray-700 rounded-xs text-xs font-medium focus:border-primary-500 focus:ring-0 outline-none transition-all dark:text-white placeholder-gray-400" placeholder="Optional audit explanation..."></textarea>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="mt-6 flex gap-4">
                    <button type="button" onclick="closeEditModal()" class="flex-1 px-6 py-2.5 text-xs font-medium text-gray-500 hover:text-gray-900 tracking-widest hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xs transition-all active:scale-95">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-xs font-medium text-white tracking-widest active:scale-[0.98] transition-all">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

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
            window.formatCategory = function(state) {
                if (!state.id) return state.text;
                let element = $(state.element);
                let code = element.data('code') || state.text.trim();
                let effect = element.data('effect');
                
                let badge = '';
                if (effect == 1) {
                    badge = '<span class="inline-flex items-center justify-center px-1.5 py-1 rounded-xs text-[12px] font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 mr-2 leading-none"><i class="fa-solid fa-arrow-down mr-1"></i>IN</span>';
                } else if (effect == -1) {
                    badge = '<span class="inline-flex items-center justify-center px-1.5 py-1 rounded-xs text-[12px] font-medium bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 mr-2 leading-none"><i class="fa-solid fa-arrow-up mr-1"></i>OUT</span>';
                }
                
                return $(`<div class="flex items-center"><div class="flex items-center h-5">${badge}</div><span class="text-xs font-medium text-slate-700 dark:text-gray-300 truncate">${code}</span></div>`);
            }

            $('#transaction_category_id').select2({
                minimumResultsForSearch: Infinity,
                templateResult: window.formatCategory,
                templateSelection: window.formatCategory,
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
                    render: (d) => `<span class="text-[10px] font-medium text-gray-500 uppercase tracking-tight">${d}</span>`
                },
                {
                    data: 'model_name',
                    className: 'text-left font-medium text-slate-700 dark:text-gray-300 text-[10px] tracking-tight',
                    render: d => d || '-'
                },
                { 
                    data: 'part_no', 
                    render: (d, t, r) => `
                        <div class="flex flex-col py-1">
                            <span class="font-medium text-gray-900 dark:text-white leading-tight tracking-tighter">${r.part_no}</span>
                            <span class="text-[9px] text-gray-400 tracking-tight mt-1">${r.product_name || ''}</span>
                        </div>
                    `
                },
                { 
                    data: 'category',
                    className: 'text-center',
                    render: (d, t, r) => {
                        const colorMap = {
                            'IN': 'emerald',
                            'OUT-EVENT': 'amber',
                            'OUT-PP': 'indigo',
                            'OUT-TRIAL': 'rose'
                        };
                        const color = colorMap[d] || (d.includes('OUT') ? 'rose' : 'emerald');
                        const badgeClass = `bg-${color}-50 text-${color}-700 border-${color}-100 dark:bg-${color}-900/30 dark:text-${color}-400 dark:border-${color}-800`;
                        return `<span class="inline-block px-2 py-0.5 rounded-xs border ${badgeClass} text-[10px] font-medium tracking-widest whitespace-nowrap">${d}</span>`;
                    }
                },
                { 
                    data: 'qty', 
                    className: 'text-center',
                    render: (d) => `<span class="font-medium text-slate-700 dark:text-slate-300 text-xs">${d}</span>`
                },
                { 
                    data: 'pic_name',
                    render: (d) => `<span class="text-[10px] font-medium text-gray-600 dark:text-gray-400 uppercase tracking-tight">${d}</span>`
                },
                @if(Auth::user()->hasMenuPermission('inventory.transactions.index', 'edit') || Auth::user()->hasMenuPermission('inventory.transactions.index', 'delete'))
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: (d, t, r) => {
                        let buttons = '';
                        @if(Auth::user()->hasMenuPermission('inventory.transactions.index', 'edit'))
                        buttons += `
                            <button class="edit-transaction-btn h-8 w-8 inline-flex items-center justify-center text-primary-600 rounded-xs bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:hover:bg-primary-900/30 transition-colors" data-id="${r.id}" title="Edit">
                                <i class="fa-solid fa-pen-to-square text-sm"></i>
                            </button>
                        `;
                        @endif
                        @if(Auth::user()->hasMenuPermission('inventory.transactions.index', 'delete'))
                        buttons += `
                            <button class="delete-transaction-btn h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-xs bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors" data-id="${r.id}" title="Delete">
                                <i class="fa-solid fa-trash-can text-sm"></i>
                            </button>
                        `;
                        @endif
                        return `<div class="flex items-center justify-center gap-1.5">${buttons}</div>`;
                    }
                }
                @endif
            ],
            order: [[0, 'desc']],
            pageLength: 5,
            lengthChange: false,
            bLengthChange: false,
            dom: "<'flex-1 flex flex-col justify-between overflow-x-auto w-full't><'flex flex-col sm:flex-row justify-between items-center px-4 py-3 gap-3 border-t border-slate-100 dark:border-gray-700/50 mt-auto'i p>",
            searching: true
        });

        // Search Input Event
        $('#logSearchInput').on('keyup search input', function() {
            table.search(this.value).draw();
        });

        $('#refreshTable').click(function() {
            table.ajax.reload();
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

        const dateRangePicker = new Litepicker({
            element: document.getElementById('filter_date_range'),
            singleMode: false,
            autoApply: true,
            format: 'DD-MM-YYYY',
            delimiter: ' - ',
            startDate: "{{ date('Y-m-01') }}",
            endDate: "{{ date('Y-m-t') }}",
            setup: (picker) => {
                picker.on('selected', (date1, date2) => {
                    updateDateBadgeText();
                    setTimeout(() => table.ajax.reload(), 10);
                });
            }
        });

        // Filter Toggle Handler
        $('#toggleFilters').click(function() {
            const container = $('#filterContainer');
            const chevron = $('#filterChevron');
            
            if (container.hasClass('hidden')) {
                container.removeClass('hidden').hide().slideDown(200);
                chevron.addClass('rotate-180');
            } else {
                container.slideUp(200, function() {
                    $(this).addClass('hidden');
                });
                chevron.removeClass('rotate-180');
            }
        });

        // Search Handlers
        $('#filter_product_detail_id, #filter_category_id, #filter_pic_id').on('change', function() {
            table.ajax.reload();
        });

        $('#filter_product_detail_id, #filter_pic_id').select2({
            width: '100%',
            containerCssClass: 'select2-filter-log',
            dropdownCssClass: 'select2-filter-log'
        });

        $('#filter_category_id').select2({
            width: '100%',
            containerCssClass: 'select2-filter-log',
            dropdownCssClass: 'select2-filter-log',
            templateResult: window.formatCategory,
            templateSelection: window.formatCategory
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

            if (!$('#qty').val() || $('#qty').val() <= 0) {
                Swal.fire('Error', 'Quantity must be greater than 0', 'error');
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
            const inputVal = parseFloat($('#qty').val()) || 0;
            const selectedProduct = $('#product_detail_id').find(':selected');
            const pcsPerUnit = parseFloat(selectedProduct.data('pcs')) || 0;
            const weightKg = parseFloat(selectedProduct.data('weight')) || 0;
            const unitType = (selectedProduct.data('unit') || '').toLowerCase();
            const currentStock = parseFloat(selectedProduct.data('stock')) || 0;
            const isCoil = unitType.includes('coil');

            // Show/Update Balance Display
            if (selectedProduct.val()) {
                $('#balanceDisplay').removeClass('hidden');
                $('#currentBalanceVal').text(new Intl.NumberFormat().format(currentStock) + ' ' + (isCoil ? 'KG' : (unitType || 'Unit')));
            } else {
                $('#balanceDisplay').addClass('hidden');
            }

            if (isCoil) {
                $('#qtyLabel').html('Qty (Kg) <span class="text-red-500">*</span>');
                $('#qty').attr('step', '0.01').attr('min', '0.01');
                
                const grossCoil = parseFloat(selectedProduct.data('gross')) || 0;
                const topMm = parseFloat(selectedProduct.data('top')) || 0;
                const endMm = parseFloat(selectedProduct.data('end')) || 0;
                const pitch = parseFloat(selectedProduct.data('pitch')) || 0;
                const ppp = parseFloat(selectedProduct.data('ppp')) || 1;
                
                if (grossCoil <= 0 || topMm <= 0 || endMm <= 0 || pitch <= 0) {
                    $('#calcResult').text('0');
                    $('#pcsInfo').html(`<span class="text-rose-500 font-bold"><i class="fa-solid fa-triangle-exclamation mr-1"></i> COIL DATA INCOMPLETE</span>`);
                    $('#btnSubmit').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
                    $('#qtyPreview').removeClass('opacity-0');
                    return;
                }

                if (inputVal > 0) {
                    const weightPerMm = weightKg / pitch;
                    const scrapKg = (topMm + endMm) * weightPerMm;
                    const weightPerPitch = weightKg; // data-weight is already weight per pitch
                    
                    const yieldRatio = Math.max(0, (grossCoil - scrapKg) / grossCoil);
                    const netVal = inputVal * yieldRatio;
                    // Match Master Data Rounding: floor(Strokes) * Pcs/Pitch
                    const strokes = Math.floor(netVal / weightPerPitch);
                    const totalPcs = strokes * ppp;
                    
                    $('#calcResult').text(new Intl.NumberFormat().format(totalPcs));
                    $('#pcsInfo').text(`${netVal.toFixed(2)} Kg Net (${(yieldRatio * 100).toFixed(1)}%) @ ${strokes} Strokes x ${ppp}`);
                    $('#qtyPreview').removeClass('opacity-0');
                    $('#btnSubmit').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                } else {
                    $('#qtyPreview').addClass('opacity-0');
                    $('#btnSubmit').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                }
            } else {
                // Non-Coil
                $('#btnSubmit').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                $('#qtyLabel').html('Qty (Unit) <span class="text-red-500">*</span>');
                $('#qty').attr('step', '1').attr('min', '1');
                
                if (inputVal > 0 && pcsPerUnit > 0) {
                    const totalPcs = inputVal * pcsPerUnit;
                    $('#calcResult').text(new Intl.NumberFormat().format(totalPcs));
                    $('#pcsInfo').text(pcsPerUnit);
                    $('#qtyPreview').removeClass('opacity-0');
                } else {
                    $('#qtyPreview').addClass('opacity-0');
                }
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
                        const productDetailId = response.product_detail_id;
                        $('#edit_product_detail_id').val(productDetailId).trigger('change');
                        $('#hidden_edit_product_detail_id').val(productDetailId);
                        
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
                        type: 'POST',
                        data: { 
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
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
