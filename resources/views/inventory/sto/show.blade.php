@extends('layouts.app')

@section('title', 'STO Event Details')
@section('page_title', 'Stock Opname')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start gap-6">
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('inventory.sto.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-gray-400 hover:text-gray-900 dark:hover:text-white uppercase tracking-widest transition-all">
                    <i class="fa-solid fa-arrow-left"></i> Back to Monitor
                </a>
                <span class="text-gray-200 dark:text-gray-700">/</span>
                <span class="px-2.5 py-0.5 text-[10px] rounded-md font-bold uppercase tracking-widest border {{ $stoEvent->status === 'OPEN' ? 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800' : 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700' }}">
                    {{ $stoEvent->status }}
                </span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white sm:text-3xl tracking-tighter">{{ $stoEvent->name }}</h2>
            <div class="mt-1 flex items-center gap-3 text-xs font-bold text-gray-500">
                <span class="font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded border border-gray-200 dark:border-gray-700">#{{ $stoEvent->code }}</span>
                <span class="bg-gray-50 dark:bg-gray-900 px-2 py-0.5 rounded border border-gray-200 dark:border-gray-700"><i class="fa-solid fa-calendar-days mr-1 text-gray-400"></i> Started: {{ $stoEvent->period_start->format('d M Y') }}</span>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 w-full md:w-auto">
            <a href="{{ route('inventory.sto.exportExcel', $stoEvent->hash_id) }}" class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 px-6 py-2 bg-emerald-600 text-white text-sm font-bold rounded-md hover:bg-emerald-700 transition-all shadow-sm shadow-emerald-200 dark:shadow-none uppercase tracking-tighter">
                <i class="fa-solid fa-file-excel"></i> Export Result
            </a>

            @php
                 $user = auth()->user();
                 $isChecker = $user->hasAppRole('checker') || $user->hasAppRole('approver') || $user->hasAppRole('admin');
                 $isApprover = $user->hasAppRole('approver') || $user->hasAppRole('admin');
            @endphp

            @if($stoEvent->status === 'OPEN')
                <form action="{{ route('inventory.sto.submitForCheck', $stoEvent->hash_id) }}" method="POST" id="submitForCheckForm" class="flex-1 md:flex-none">
                    @csrf
                    <button type="button" onclick="confirmSubmitForCheck()" class="w-full inline-flex items-center justify-center gap-2 px-6 py-2 bg-blue-600 text-white text-sm font-bold rounded-md hover:bg-blue-700 transition-all shadow-sm shadow-blue-200 dark:shadow-none uppercase tracking-tighter">
                        <i class="fa-solid fa-paper-plane"></i> Submit for Check
                    </button>
                </form>
            @endif

            @if($stoEvent->status === 'WAITING CHECK' && $isChecker)
                <div class="flex gap-2 w-full md:w-auto">
                    <form action="{{ route('inventory.sto.verify', $stoEvent->hash_id) }}" method="POST" id="verifyForm" class="flex-1 md:flex-none">
                        @csrf
                        <button type="button" onclick="confirmVerify()" class="w-full inline-flex items-center justify-center gap-2 px-6 py-2 bg-amber-500 text-white text-sm font-bold rounded-md hover:bg-amber-600 transition-all shadow-sm shadow-amber-200 dark:shadow-none uppercase tracking-tighter">
                            <i class="fa-solid fa-check-double"></i> Verify Data
                        </button>
                    </form>
                    <button type="button" onclick="openRejectModal()" class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 px-6 py-2 bg-red-500 text-white text-sm font-bold rounded-md hover:bg-red-600 transition-all uppercase tracking-tighter">
                        <i class="fa-solid fa-xmark"></i> Reject
                    </button>
                </div>
            @endif

            @if($stoEvent->status === 'WAITING APPROVAL' && $isApprover)
                <div class="flex flex-col md:flex-row gap-2 w-full md:w-auto">
                    <form action="{{ route('inventory.sto.finalize', $stoEvent->hash_id) }}" method="POST" id="finalizeForm" class="flex-1 md:min-w-[200px]">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-3 px-8 py-2.5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-sm font-bold rounded-md hover:bg-slate-800 transition-all shadow-lg uppercase tracking-widest">
                            <i class="fa-solid fa-lock"></i> Finalize & Adjust
                        </button>
                    </form>
                    <button type="button" onclick="openRejectModal()" class="inline-flex items-center justify-center gap-2 px-6 py-2 bg-red-500 text-white text-sm font-bold rounded-md hover:bg-red-600 transition-all uppercase tracking-tighter font-bold">
                        <i class="fa-solid fa-xmark"></i> Reject
                    </button>
                </div>
            @endif

            @if(($stoEvent->status === 'CLOSED' || $stoEvent->status === 'WAITING CHECK' || $stoEvent->status === 'WAITING APPROVAL') && $isApprover)
                <form action="{{ route('inventory.sto.reopen', $stoEvent->hash_id) }}" method="POST" id="reopenForm" class="flex-1 md:flex-none">
                    @csrf
                    <button type="button" onclick="confirmReopen()" class="w-full inline-flex items-center justify-center gap-2 px-6 py-2 bg-gray-600 text-white text-sm font-bold rounded-md hover:bg-gray-700 transition-all uppercase tracking-tighter">
                        <i class="fa-solid fa-rotate-left"></i> Reopen
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Statistics Dashboard -->
    <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-md border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden relative">
        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50/50 dark:bg-blue-900/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
        <div class="relative flex flex-wrap items-center gap-6 md:gap-8">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-blue-50 dark:bg-blue-900/30 rounded-md border border-blue-100 dark:border-blue-800 shadow-sm">
                    <i class="fa-solid fa-boxes-stacked text-blue-600 dark:text-blue-400"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Scanned</span>
                    <div class="flex items-baseline gap-1.5">
                        <span id="stat-total-items" class="text-xl font-bold text-gray-900 dark:text-white leading-none">{{ $stats['total_items'] }}</span>
                        <span class="text-[10px] font-bold text-gray-400 bg-gray-50 dark:bg-gray-800 px-1 py-0.5 rounded border border-gray-100 dark:border-gray-700"><span id="stat-progress">{{ $progress }}</span>% Covered</span>
                    </div>
                </div>
            </div>

            <div class="h-10 w-px bg-gray-100 dark:bg-gray-700 hidden md:block"></div>

            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-emerald-50 dark:bg-emerald-900/30 rounded-md border border-emerald-100 dark:border-emerald-800 shadow-sm">
                    <i class="fa-solid fa-arrow-trend-up text-emerald-600 dark:text-emerald-400"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">Stock Increment</span>
                    <span id="stat-total-increase-pcs" class="text-base font-bold text-emerald-700 dark:text-emerald-400 leading-none">{{ number_format($stats['total_increase_pcs'], 0) }} Pcs</span>
                    <span id="stat-total-increase" class="text-[10px] font-bold text-emerald-500/80">({{ number_format($stats['total_increase'], 0) }} Unit / {{ $stats['count_increase'] }} items)</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-red-50 dark:bg-red-900/30 rounded-md border border-red-100 dark:border-red-800 shadow-sm">
                    <i class="fa-solid fa-arrow-trend-down text-red-600 dark:text-red-400"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-red-600 dark:text-red-400 uppercase tracking-widest">Stock Decrement</span>
                    <span id="stat-total-decrease-pcs" class="text-base font-bold text-red-700 dark:text-red-400 leading-none">{{ number_format($stats['total_decrease_pcs'], 0) }} Pcs</span>
                    <span id="stat-total-decrease" class="text-[10px] font-bold text-red-500/80">({{ number_format($stats['total_decrease'], 0) }} Unit / {{ $stats['count_decrease'] }} items)</span>
                </div>
            </div>

            <div class="h-10 w-px bg-gray-100 dark:bg-gray-700 hidden md:block"></div>

            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-purple-50 dark:bg-purple-900/30 rounded-md border border-purple-100 dark:border-purple-800 shadow-sm font-bold leading-none flex items-center justify-center text-purple-600 dark:text-purple-400 uppercase text-[10px]">
                    Net
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-purple-600 dark:text-purple-400 uppercase tracking-widest">Adjustment Impact</span>
                    <span id="stat-net-adjustment-pcs" class="text-base font-bold text-purple-700 dark:text-purple-400 leading-none">{{ ($stats['net_adjustment_pcs'] >= 0 ? '+' : '') . number_format($stats['net_adjustment_pcs'], 0) }} Pcs</span>
                    <span id="stat-net-adjustment" class="text-[10px] font-bold text-purple-500/80">({{ ($netAdjustment >= 0 ? '+' : '') . number_format($netAdjustment, 0) }} Unit)</span>
                </div>
            </div>
            
            <div class="ml-auto bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 px-4 py-2 rounded-md hidden lg:flex items-center gap-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Perfect Match</span>
                <span id="stat-total-matched" class="text-lg font-bold text-slate-900 dark:text-white leading-none">{{ $stats['total_matched'] }}</span>
            </div>
        </div>
    </div>

    @if($stoEvent->status === 'OPEN' && $stoEvent->rejection_note)
    <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 mb-6 rounded-md shadow-sm animate-pulse-once">
        <div class="flex items-start gap-4">
            <div class="p-2 bg-red-100 dark:bg-red-900/40 rounded-md text-red-600 dark:text-red-400 shadow-sm border border-red-200 dark:border-red-800">
                <i class="fa-solid fa-circle-exclamation text-xl"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-xs font-bold text-red-800 dark:text-red-300 uppercase tracking-[0.2em] mb-1.5 flex items-center gap-2">
                    REJECTION FEEDBACK RECEIVED
                </h3>
                <div class="bg-white/50 dark:bg-black/20 p-3 rounded border border-red-100 dark:border-red-800/50 italic font-bold text-sm text-red-700 dark:text-red-400 leading-relaxed shadow-inner">
                    "{{ $stoEvent->rejection_note }}"
                </div>
                <div class="mt-2 flex items-center gap-1.5 text-[10px] font-bold text-red-500 uppercase tracking-wider underline decoration-2">
                   Please rectify reported issues and resubmit for verification.
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($stoEvent->status === 'OPEN')
    <!-- SCANNER SECTION -->
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-md p-4 md:p-6 mb-6 shadow-sm border-t-4 border-t-blue-600">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-900 dark:text-white uppercase tracking-widest text-sm flex items-center gap-2">
                <i class="fa-solid fa-barcode text-blue-600"></i> Count Entry Center
            </h3>
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Enter Part No or Scan QR Code</span>
        </div>
        
        <div class="flex gap-3">
            <div class="flex-1">
                <select id="product_detail_id" class="select2 w-full" data-placeholder="Select Product via Search or Scanner...">
                    <option value="">Select Product via Search or Scanner...</option>
                    @foreach($products as $product)
                        <option value="{{ $product->hash_id }}" data-partno="{{ $product->part_no }}">
                            {{ $product->part_no }} {{ $product->revision ? '- ' . $product->revision : '' }} - {{ $product->part_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button id="btn-scan" class="flex-shrink-0 bg-gray-50 hover:bg-gray-100 text-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-100 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-600 transition-all shadow-sm" title="Open Scanner Camera">
                <i class="fa-solid fa-camera text-lg"></i>
            </button>
        </div>

        <div class="mt-4 hidden" id="scanResultArea">
             <div class="flex flex-col lg:flex-row items-stretch gap-4 p-5 rounded-md border-2 border-dashed border-blue-200 dark:border-blue-800 bg-blue-50/20 dark:bg-blue-900/5 relative overflow-hidden">
                 <div class="absolute top-0 left-0 w-1 h-full bg-blue-600"></div>
                 
                 <!-- Product Info Section -->
                 <div class="flex-1 flex flex-col justify-center">
                    <div class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-1" id="resPartNo">-</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white leading-tight mb-4" id="resPartName">-</div>
                    <div class="flex flex-wrap gap-2">
                        <div class="flex flex-col px-3 py-1.5 bg-white dark:bg-gray-800 rounded border border-gray-100 dark:border-gray-700 min-w-[80px]">
                            <span class="text-[8px] font-bold text-gray-400 uppercase leading-none mb-1">Stock Unit</span>
                            <span class="text-xs font-bold text-gray-900 dark:text-white" id="resUnit">-</span>
                        </div>
                        <div class="flex flex-col px-3 py-1.5 bg-white dark:bg-gray-800 rounded border border-gray-100 dark:border-gray-700 min-w-[80px]">
                            <span class="text-[8px] font-bold text-blue-400 uppercase leading-none mb-1">System Qty</span>
                            <span class="text-xs font-bold text-blue-600 dark:text-blue-400" id="resSystemQty">0</span>
                        </div>
                        <div class="flex flex-col px-3 py-1.5 bg-white dark:bg-gray-800 rounded border border-gray-100 dark:border-gray-700 min-w-[80px]">
                            <span class="text-[8px] font-bold text-purple-400 uppercase leading-none mb-1">Prev Count</span>
                            <span class="text-xs font-bold text-purple-600 dark:text-purple-400" id="resPrevReal">0</span>
                        </div>
                    </div>
                 </div>
                 
                 <div class="w-px bg-blue-200 dark:bg-blue-800 hidden lg:block mx-2"></div>

                 <!-- Entry Form Section -->
                 <div class="flex flex-col sm:flex-row items-end gap-3 lg:w-3/5">
                    <div class="flex-1 min-w-0 w-full flex flex-col">
                        <div id="unitHelperLabel" class="text-[9px] font-bold text-blue-500 uppercase tracking-tighter mb-1.5 hidden">Enter quantity below</div>
                        <div class="relative group">
                            <input type="number" id="realQtyInput" step="any" 
                                class="w-full bg-white dark:bg-gray-900 border-2 border-gray-200 dark:border-gray-700 group-focus-within:border-blue-500 rounded-md h-[48px] text-center font-bold text-lg focus:ring-0 transition-all outline-none" 
                                placeholder="0.00">
                            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-pen-nib text-gray-300 group-focus-within:text-blue-400"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0 w-full">
                        <label class="text-[9px] font-bold text-gray-400 uppercase mb-1.5 block">Remark / Location</label>
                        <input type="text" id="remarkInput" 
                               class="w-full bg-white dark:bg-gray-900 border-2 border-gray-200 dark:border-gray-700 focus:border-blue-500 rounded-md h-[48px] text-sm font-bold px-4 focus:ring-0 outline-none transition-all" 
                               placeholder="Floor, Bin, or specific note...">
                    </div>
                    <button id="btnSaveCount" class="w-full sm:w-auto h-[48px] bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-8 rounded-md font-bold text-xs uppercase tracking-widest transition-all shadow-md hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2">
                        Commit <i class="fa-solid fa-arrow-right"></i>
                    </button>
                 </div>
             </div>
             <input type="hidden" id="currentHashId">
        </div>
        <div id="scanError" class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 text-red-600 text-xs font-bold rounded-md border border-red-100 dark:border-red-800 hidden flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation"></i> <span id="errorMsg"></span>
        </div>
    </div>
    @endif
    
    @include('components.scanner-modal')

    <!-- RESULTS TABLE -->
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-md shadow-sm overflow-hidden">
        <div class="p-4 md:p-6 border-b border-gray-50 dark:border-gray-700 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-slate-100 dark:bg-slate-700 rounded-md">
                   <i class="fa-solid fa-list-check text-slate-600 dark:text-slate-300"></i>
                </div>
                <div>
                   <h3 class="font-bold text-gray-900 dark:text-white uppercase tracking-widest text-sm">Counting Journal</h3>
                   <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">Real-time log of recorded quantities.</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="px-4 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 rounded-md flex items-center gap-3">
                    <span class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">Matched</span>
                    <span id="table-total-matched" class="text-sm font-bold text-emerald-700 dark:text-emerald-400">{{ $stats['total_matched'] }}</span>
                </div>
                <div class="px-4 py-1.5 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800 rounded-md flex items-center gap-3">
                    <span class="text-[9px] font-bold text-red-600 dark:text-red-400 uppercase tracking-widest">Mismatch</span>
                    <span id="table-total-diff" class="text-sm font-bold text-red-700 dark:text-red-400">{{ $stats['total_diff'] }}</span>
                </div>
            </div>
        </div>
        
        <x-table id="stoDetailsTable">
            <thead>
                <tr>
                    <th class="w-12">No</th>
                    <th class="w-40 text-left">Timestamp</th>
                    <th class="text-left">Material Information</th>
                    <th class="w-24 text-center">System</th>
                    <th class="w-32 text-center">Real Count</th>
                    <th class="w-24 text-center">Variance</th>
                    <th class="text-left">Remark</th>
                    @if($stoEvent->status === 'OPEN')
                    <th class="w-20 text-center">Action</th>
                    @endif
                </tr>
            </thead>
            <tbody></tbody>
        </x-table>
    </div>
</div>

<!-- Finalize Modal & Reject Modal UI logic remains the same, but styled consistently -->

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-slate-900/80 backdrop-blur-md transition-all">
    <div class="bg-white dark:bg-gray-800 rounded-md shadow-2xl w-full max-w-md overflow-hidden border border-gray-100 dark:border-gray-700">
        <div class="px-6 py-4 bg-red-50 dark:bg-red-900/20 border-b border-red-100 dark:border-red-800 flex justify-between items-center">
            <h3 class="font-bold text-red-900 dark:text-red-400 flex items-center gap-3 text-sm uppercase tracking-widest">
                <i class="fa-solid fa-ban"></i> Reject Submission
            </h3>
            <button onclick="closeRejectModal()" class="text-red-400 hover:text-red-900 dark:hover:text-white transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form action="{{ route('inventory.sto.reject', $stoEvent->hash_id) }}" method="POST" class="p-6">
            @csrf
            <div class="mb-5">
                <label for="rejection_note" class="block text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-3">Feedback for the PIC</label>
                <textarea name="rejection_note" id="rejection_note" rows="4" required 
                    class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-gray-100 dark:border-gray-700 rounded-md p-4 text-sm font-bold focus:ring-0 focus:border-red-500 transition-all dark:text-gray-200 placeholder-gray-300"
                    placeholder="Provide clear reasons for rejection (e.g. invalid count for ITEM-X)..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeRejectModal()" class="flex-1 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-all">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-3 bg-red-600 hover:bg-red-700 text-white text-[10px] font-bold rounded-md shadow-lg shadow-red-200 dark:shadow-none transition-all active:scale-95 uppercase tracking-widest">
                    Confirm Reject
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const scanUrl = "{{ route('inventory.sto.scan', $stoEvent->hash_id) }}";
    const saveUrl = "{{ route('inventory.sto.saveCount', $stoEvent->hash_id) }}";
    const csrfToken = "{{ csrf_token() }}";

    let table;
    $(document).ready(function() {
        if (window.defaultDataTable) {
            table = window.defaultDataTable('stoDetailsTable', {
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('inventory.sto.detailsData', $stoEvent->hash_id) }}",
                    type: 'GET'
                },
                columns: [
                    { data: 'row_number', className: 'text-center font-bold text-gray-300', orderable: false, searchable: false },
                    { data: 'updated_at', className: 'text-[10px] font-mono font-bold text-gray-500' },
                    { data: 'product_info', className: 'font-bold' },
                    { data: 'system_qty', className: 'text-center font-mono text-sm text-blue-600 dark:text-blue-400 bg-blue-50/30 dark:bg-blue-900/10' },
                    { data: 'real_qty', className: 'text-center' },
                    { data: 'diff', className: 'text-center font-bold' },
                    { data: 'remark', className: 'text-xs text-gray-500 italic' },
                    @if($stoEvent->status === 'OPEN')
                    { data: 'action', className: 'text-center', orderable: false }
                    @endif
                ],
                order: [[1, 'desc']],
            });

            // Inline Editing logic (Qty)
            $('#stoDetailsTable').on('blur', '.qty-input', function() {
                const $input = $(this);
                const productId = $input.data('product-id');
                const newQty = $input.val();
                const originalQty = $input.data('original-value');

                if (newQty === originalQty || !newQty || newQty === '') return;

                const $row = $input.closest('tr');
                const $remarkInput = $row.find('.remark-input');
                const existingRemark = $remarkInput.length ? $remarkInput.val() : '';

                fetch(saveUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ product_id_hash: productId, real_qty: newQty, remark: existingRemark })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        $input.data('original-value', newQty);
                        table.ajax.reload(null, false);
                        if (data.stats && window.updateStatsCard) window.updateStatsCard(data.stats);
                    }
                });
            });

            // Inline Editing logic (Remark)
            $('#stoDetailsTable').on('blur', '.remark-input', function() {
                const $input = $(this);
                const newRemark = $input.val();
                const originalRemark = $input.data('original-value') || '';

                if (newRemark === originalRemark) return;

                const $row = $input.closest('tr');
                const $qtyInput = $row.find('.qty-input');
                const productId = $qtyInput.data('product-id');
                const currentQty = $qtyInput.val();

                fetch(saveUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ product_id_hash: productId, real_qty: currentQty, remark: newRemark })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        $input.data('original-value', newRemark);
                        if (data.stats && window.updateStatsCard) window.updateStatsCard(data.stats);
                    }
                });
            });
        }
    });

    // --- Scanner & Entry Logic ---
    const resultArea = document.getElementById('scanResultArea');
    const errorArea = document.getElementById('scanError');
    const errorMsg = document.getElementById('errorMsg');
    const realQtyInput = document.getElementById('realQtyInput');
    const remarkInput = document.getElementById('remarkInput');
    const btnSaveCount = document.getElementById('btnSaveCount');
    const resPartName = document.getElementById('resPartName');
    const resPartNo = document.getElementById('resPartNo');
    const resUnit = document.getElementById('resUnit');
    const resSystemQty = document.getElementById('resSystemQty');
    const resPrevReal = document.getElementById('resPrevReal');
    const currentHashId = document.getElementById('currentHashId');

    const productSelect = $('#product_detail_id');
    if (productSelect.length) {
        productSelect.select2();
        productSelect.on('change', function() {
            const hashId = $(this).val();
            if (hashId) fetchStoInfo(hashId);
        });
    }

    if (typeof InventoryScanner !== 'undefined') {
        new InventoryScanner({ selectId: '#product_detail_id', scanButtonId: '#btn-scan', qrInputId: null, modalId: '#scannerModal' });
    }

    function fetchStoInfo(hashId) {
        fetch(scanUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ qr_code: hashId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) showResult(data.data);
            else showError(data.message);
        });
    }

    function showResult(data) {
        resultArea.classList.remove('hidden');
        errorArea.classList.add('hidden');
        resPartName.innerText = data.part_name;
        resPartNo.innerText = data.part_no;
        resUnit.innerText = data.unit || 'PCS';
        resSystemQty.innerText = (data.system_qty || 0) + 0;
        resPrevReal.innerText = (data.prev_real_qty || 0) + 0;
        currentHashId.value = data.product_id_hash;
        realQtyInput.value = data.prev_real_qty || '';
        remarkInput.value = '';
        const unitHelper = document.getElementById('unitHelperLabel');
        if (unitHelper) {
            unitHelper.innerText = 'ENTER QTY IN ' + (data.unit || 'UNIT');
            unitHelper.classList.remove('hidden');
        }
        setTimeout(() => realQtyInput.focus(), 100);
    }

    function showError(msg) {
        errorMsg.innerText = msg;
        errorArea.classList.remove('hidden');
        resultArea.classList.add('hidden');
    }

    // --- Actions ---
    function saveCount() {
        const qty = realQtyInput.value;
        const remark = remarkInput.value;
        const hash = currentHashId.value;
        if (qty === '' || !hash) return;

        fetch(saveUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ product_id_hash: hash, real_qty: qty, remark: remark })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.stats && window.updateStatsCard) window.updateStatsCard(data.stats);
                table.ajax.reload();
                resultArea.classList.add('hidden');
                productSelect.val(null).trigger('change');
            }
        });
    }

    if(btnSaveCount) btnSaveCount.addEventListener('click', saveCount);

    // Submit for Check Confirmation
    function confirmSubmitForCheck() {
        Swal.fire({
            title: 'Submit for Verification?',
            text: 'You will not be able to edit count data anymore.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563EB',
            confirmButtonText: 'Yes, Submit Now',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('submitForCheckForm').submit();
            }
        });
    }

    // Verify Confirmation
    function confirmVerify() {
        Swal.fire({
            title: 'Verify STO Data?',
            text: 'Pass this data to final approval stage.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#F59E0B',
            confirmButtonText: 'Yes, Verify It',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('verifyForm').submit();
            }
        });
    }

    const finalizeForm = document.getElementById('finalizeForm');
    if (finalizeForm) {
        finalizeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Finalize STO Event?',
                text: 'This will update stock levels and lock further entries.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0F172A',
                confirmButtonText: 'Yes, Finalize!'
            }).then((result) => { if (result.isConfirmed) finalizeForm.submit(); });
        });
    }

    function confirmReopen() {
        Swal.fire({
            title: 'Reopen Event?',
            text: 'Adjustments will be reversed. Are you sure?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4B5563',
            confirmButtonText: 'Yes, Reopen'
        }).then((result) => { if (result.isConfirmed) document.getElementById('reopenForm').submit(); });
    }

    window.updateStatsCard = function(data) {
        if (!data || !data.stats) return;
        const s = data.stats;
        const n = data.netAdjustment;
        const p = data.progress;
        const fmt = (v) => new Intl.NumberFormat('en-US').format(v);
        
        const set = (i,v) => { let e = document.getElementById(i); if(e) e.innerText = v; };
        set('stat-total-items', s.total_items);
        set('stat-progress', p);
        set('stat-total-matched', s.total_matched);
        set('table-total-matched', s.total_matched);
        set('table-total-diff', s.total_diff);
        set('stat-total-increase-pcs', fmt(s.total_increase_pcs) + ' Pcs');
        set('stat-total-increase', '(' + fmt(s.total_increase) + ' Unit / ' + s.count_increase + ' items)');
        set('stat-total-decrease-pcs', fmt(s.total_decrease_pcs) + ' Pcs');
        set('stat-total-decrease', '(' + fmt(s.total_decrease) + ' Unit / ' + s.count_decrease + ' items)');
        set('stat-net-adjustment-pcs', (n >= 0 ? '+' : '') + fmt(s.net_adjustment_pcs) + ' Pcs');
        set('stat-net-adjustment', '(' + (n >= 0 ? '+' : '') + fmt(n) + ' Unit)');
    };

    window.openRejectModal = () => $('#rejectModal').removeClass('hidden').addClass('flex');
    window.closeRejectModal = () => $('#rejectModal').addClass('hidden').removeClass('flex');

    // Keybindings: Enter and Alt+S
    if (realQtyInput) {
        realQtyInput.addEventListener('keydown', e => { if(e.key === 'Enter') remarkInput.focus(); });
    }
    if (remarkInput) {
        remarkInput.addEventListener('keydown', e => { if(e.key === 'Enter') saveCount(); });
    }
</script>

@endpush
@endsection
