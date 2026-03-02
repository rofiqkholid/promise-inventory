@extends('layouts.app')

@section('title', 'STO Event Details')
@section('page_title', 'Stock Opname')

@push('styles')
<style>
    .custom-scrollbar::-webkit-scrollbar {
        height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    .dataTables_scrollBody {
        border-bottom: 0 !important;
    }
    /* Fix Select2 responsive width */
    .select2-container {
        width: 100% !important;
    }
    .select2-selection {
        height: 42px !important;
        display: flex !important;
        align-items: center !important;
        border-color: #d1d5db !important;
        border-radius: 4px !important;
    }
    .dark .select2-selection {
        background-color: #111827 !important;
        border-color: #374151 !important;
    }
    .select2-selection__rendered {
        width: 100% !important;
        padding-right: 30px !important;
    }
</style>
@endpush

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    <!-- Header -->
    <div class="mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-2.5">
                <a href="{{ route('inventory.sto.index') }}" class="group inline-flex items-center gap-1.5 text-xs font-bold text-gray-400 hover:text-blue-600 transition-colors uppercase tracking-widest">
                    <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i> Back to Monitor
                </a>
                <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                <span class="px-2 py-0.5 text-[9px] rounded font-black uppercase tracking-widest border {{ $stoEvent->status === 'OPEN' ? 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-400 dark:border-emerald-800' : 'bg-blue-50 text-blue-600 border-blue-100 dark:bg-blue-900/40 dark:text-blue-400 dark:border-blue-800' }}">
                    {{ str_replace('_', ' ', $stoEvent->status) }}
                </span>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight leading-none">{{ $stoEvent->name }}</h2>
            
            <div class="mt-3 flex flex-wrap items-center gap-4 text-[11px] font-bold">
                <div class="flex items-center gap-1.5 text-gray-400 bg-gray-50 dark:bg-gray-800/50 px-2.5 py-1 rounded border border-gray-100 dark:border-gray-700">
                    <span class="text-[9px] uppercase tracking-wider opacity-60">Code:</span>
                    <span class="font-mono text-gray-700 dark:text-gray-300">#{{ $stoEvent->code }}</span>
                </div>
                <div class="flex items-center gap-1.5 text-gray-400 bg-gray-50 dark:bg-gray-800/50 px-2.5 py-1 rounded border border-gray-100 dark:border-gray-700">
                    <i class="fa-solid fa-calendar-alt opacity-60"></i>
                    <span class="text-[9px] uppercase tracking-wider opacity-60 mr-0.5">Started:</span>
                    <span class="text-gray-700 dark:text-gray-300">{{ $stoEvent->period_start->format('d M Y') }}</span>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('inventory.sto.exportExcel', $stoEvent->hash_id) }}" class="h-10 inline-flex items-center gap-2 px-5 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-black rounded shadow-sm shadow-emerald-100 dark:shadow-none transition-all uppercase tracking-wider">
                <i class="fa-solid fa-file-excel text-sm"></i> Export Result
            </a>

            @php
                 $user = auth()->user();
                 $isPic = $user->hasAppRole('pic') || $user->hasAppRole('admin') || $stoEvent->user_id === $user->id;
                 $isChecker = $user->hasAppRole('checker') || $user->hasAppRole('approver') || $user->hasAppRole('admin');
                 $isApprover = $user->hasAppRole('approver') || $user->hasAppRole('admin');
            @endphp

            @if($stoEvent->status === 'OPEN' && $isPic)
                <form action="{{ route('inventory.sto.submitForCheck', $stoEvent->hash_id) }}" method="POST" id="submitForCheckForm" class="inline">
                    @csrf
                    <button type="button" onclick="confirmSubmitForCheck()" class="h-10 inline-flex items-center gap-2 px-5 bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-black rounded shadow-sm shadow-blue-100 dark:shadow-none transition-all uppercase tracking-wider">
                        <i class="fa-solid fa-paper-plane text-sm"></i> Submit for Check
                    </button>
                </form>
            @endif

            @if($stoEvent->status === 'WAITING CHECK' && $isChecker)
                <form action="{{ route('inventory.sto.verify', $stoEvent->hash_id) }}" method="POST" id="verifyForm" class="inline">
                    @csrf
                    <button type="button" onclick="confirmVerify()" class="h-10 inline-flex items-center gap-2 px-5 bg-amber-500 hover:bg-amber-600 text-white text-[11px] font-black rounded shadow-sm shadow-amber-100 dark:shadow-none transition-all uppercase tracking-wider">
                        <i class="fa-solid fa-check-double text-sm"></i> Verify Data
                    </button>
                </form>
                <button type="button" onclick="openRejectModal()" class="h-10 inline-flex items-center gap-2 px-5 bg-red-500 hover:bg-red-600 text-white text-[11px] font-black rounded transition-all uppercase tracking-wider">
                    <i class="fa-solid fa-xmark text-sm"></i> Reject
                </button>
            @endif

            @if($stoEvent->status === 'WAITING APPROVAL' && $isApprover)
                <form action="{{ route('inventory.sto.finalize', $stoEvent->hash_id) }}" method="POST" id="finalizeForm" class="inline">
                    @csrf
                    <button type="submit" class="h-10 inline-flex items-center gap-3 px-6 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-[11px] font-black rounded hover:bg-slate-800 transition-all shadow-lg uppercase tracking-widest">
                        <i class="fa-solid fa-lock text-sm"></i> Finalize & Adjust
                    </button>
                </form>
                <button type="button" onclick="openRejectModal()" class="h-10 inline-flex items-center gap-2 px-5 bg-red-500 hover:bg-red-600 text-white text-[11px] font-black rounded transition-all uppercase tracking-wider">
                    <i class="fa-solid fa-xmark text-sm"></i> Reject
                </button>
            @endif

            @if(($stoEvent->status === 'CLOSED' || $stoEvent->status === 'WAITING CHECK' || $stoEvent->status === 'WAITING APPROVAL') && $isApprover)
                <form action="{{ route('inventory.sto.reopen', $stoEvent->hash_id) }}" method="POST" id="reopenForm" class="inline">
                    @csrf
                    <button type="button" onclick="confirmReopen()" class="h-10 inline-flex items-center gap-2 px-5 bg-gray-600 hover:bg-gray-700 text-white text-[11px] font-black rounded transition-all uppercase tracking-wider">
                        <i class="fa-solid fa-rotate-left text-sm"></i> Reopen
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Statistics Dashboard -->
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden relative">
        <div class="relative grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 divide-x divide-y md:divide-y-0 divide-gray-100 dark:divide-gray-700">
            <div class="p-4 flex flex-col items-center text-center group">
                <div class="w-10 h-10 mb-2 flex items-center justify-center bg-blue-50 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Scanned</span>
                <div class="flex items-baseline gap-1">
                    <span id="stat-total-items" class="text-xl font-bold text-gray-900 dark:text-white">{{ $stats['total_items'] }}</span>
                    <span class="text-[9px] font-bold text-blue-500 bg-blue-50 dark:bg-blue-900/40 px-1.5 py-0.5 rounded-full"><span id="stat-progress">{{ $progress }}</span>%</span>
                </div>
            </div>

            <!-- Remaining -->
            <div onclick="openRemainingModal()" class="p-4 flex flex-col items-center text-center group cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-all relative overflow-hidden">
                <div class="w-10 h-10 mb-2 flex items-center justify-center bg-amber-50 dark:bg-amber-900/30 rounded-lg text-amber-600 dark:text-amber-400">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Remaining</span>
                <span id="stat-total-unscanned" class="text-xl font-bold text-amber-600 leading-none">
                    {{ $stats['total_unscanned'] ?? ($stats['total_count'] - $stats['total_items']) }}
                </span>
            </div>

            <!-- Increment -->
            <div class="p-4 flex flex-col items-center text-center group">
                <div class="w-10 h-10 mb-2 flex items-center justify-center bg-emerald-50 dark:bg-emerald-900/30 rounded-lg text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-square-plus text-lg"></i>
                </div>
                <span class="text-[9px] font-bold text-emerald-600/70 dark:text-emerald-400 uppercase tracking-widest mb-1">Stock Increment</span>
                <span id="stat-total-increase-pcs" class="text-lg font-bold text-emerald-700 dark:text-emerald-400 leading-none">{{ number_format($stats['total_increase_pcs'], 0) }} Pcs</span>
                <span id="stat-total-increase" class="text-[9px] font-medium text-gray-400 mt-1">({{ number_format($stats['total_increase'], 0) }} Unit / {{ $stats['count_increase'] }} items)</span>
            </div>

            <!-- Decrement -->
            <div class="p-4 flex flex-col items-center text-center group">
                <div class="w-10 h-10 mb-2 flex items-center justify-center bg-red-50 dark:bg-red-900/30 rounded-lg text-red-600 dark:text-red-400 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-square-minus text-lg"></i>
                </div>
                <span class="text-[9px] font-bold text-red-600/70 dark:text-red-400 uppercase tracking-widest mb-1">Stock Decrement</span>
                <span id="stat-total-decrease-pcs" class="text-lg font-bold text-red-700 dark:text-red-400 leading-none">{{ number_format($stats['total_decrease_pcs'], 0) }} Pcs</span>
                <span id="stat-total-decrease" class="text-[9px] font-medium text-gray-400 mt-1">({{ number_format($stats['total_decrease'], 0) }} Unit / {{ $stats['count_decrease'] }} items)</span>
            </div>

            <!-- Net Adjustment -->
            <div class="p-4 flex flex-col items-center text-center group">
                <div class="w-10 h-10 mb-2 flex items-center justify-center bg-purple-50 dark:bg-purple-900/30 rounded-lg text-purple-600 dark:text-purple-400 group-hover:scale-110 transition-transform">
                    <span class="font-black text-xs">NET</span>
                </div>
                <span class="text-[9px] font-bold text-purple-600/70 dark:text-purple-400 uppercase tracking-widest mb-1">Adjustment Impact</span>
                <span id="stat-net-adjustment-pcs" class="text-lg font-bold text-purple-700 dark:text-purple-400 leading-none">{{ ($stats['net_adjustment_pcs'] >= 0 ? '+' : '') . number_format($stats['net_adjustment_pcs'], 0) }} Pcs</span>
                <span id="stat-net-adjustment" class="text-[9px] font-medium text-gray-400 mt-1">({{ ($netAdjustment >= 0 ? '+' : '') . number_format($netAdjustment, 0) }} Unit)</span>
            </div>

            <!-- Financial Impact -->
            <div class="p-4 flex flex-col items-center text-center group">
                <div id="stat-net-amount-bg" class="w-10 h-10 mb-2 flex items-center justify-center rounded-lg group-hover:scale-110 transition-transform {{ $stats['net_amount_impact'] >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
                    <i class="fa-solid fa-coins text-lg"></i>
                </div>
                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">Financial Impact</span>
                <span id="stat-net-amount-impact" class="text-lg font-bold leading-none {{ $stats['net_amount_impact'] >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                    {{ number_format(abs($stats['net_amount_impact'] ?? 0), 0) }}
                </span>
                <span class="text-[9px] font-medium text-gray-400 mt-1 uppercase">Total Currency</span>
            </div>

            <!-- Perfect Match -->
            <div class="p-4 flex flex-col items-center text-center group">
                <div class="w-10 h-10 mb-2 flex items-center justify-center bg-slate-50 dark:bg-slate-900 rounded-lg text-slate-400 group-hover:scale-110 transition-transform border border-slate-100 dark:border-slate-800">
                    <i class="fa-solid fa-circle-check text-lg"></i>
                </div>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Perfect Match</span>
                <span id="stat-total-matched" class="text-lg font-bold text-slate-900 dark:text-white leading-none">{{ $stats['total_matched'] }}</span>
                <span class="text-[9px] font-medium text-slate-400 mt-1 lowercase">items found match</span>
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
                <h3 class="text-[10px] font-bold text-red-800 dark:text-red-300 uppercase tracking-wider mb-1 flex items-center gap-2">
                    Rejection Feedback Received
                </h3>
                <div class="bg-white/50 dark:bg-black/20 p-3 rounded border border-red-100 dark:border-red-800/50 italic font-semibold text-sm text-red-700 dark:text-red-400 leading-relaxed shadow-inner">
                    "{{ $stoEvent->rejection_note }}"
                </div>
                <div class="mt-2 flex items-center gap-1.5 text-[10px] font-bold text-red-500 uppercase tracking-wide">
                   Please rectify reported issues and resubmit for verification.
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($stoEvent->status === 'OPEN')
    <!-- ATTENTION BANNER -->
    <div id="unscanned-alert-banner" class="{{ ($stats['total_unscanned'] ?? 0) > 0 ? '' : 'hidden' }} bg-amber-50 dark:bg-amber-900/20 border-l-4 border-l-amber-500 p-4 mb-6 rounded shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 w-8 h-8 bg-amber-100 dark:bg-amber-900/40 rounded-full flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h4 class="text-[10px] font-bold text-amber-800 dark:text-amber-300 uppercase tracking-widest">Missing Counts</h4>
                    <p class="text-xs text-amber-700 dark:text-amber-400">
                        <span id="banner-unscanned-count" class="font-bold">{{ $stats['total_unscanned'] }}</span> items have not been scanned.
                    </p>
                </div>
            </div>
            <button onclick="openRemainingModal()" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-1.5 rounded text-[10px] font-bold uppercase tracking-wider transition-all">
                VIEW MISSING
            </button>
        </div>
    </div>

    <!-- SCANNER SECTION -->
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-lg p-5 mb-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-900 dark:text-white uppercase tracking-wider text-xs flex items-center gap-2">
                <i class="fa-solid fa-barcode text-blue-600"></i> Count Entry
            </h3>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 min-w-0">
                <select id="product_detail_id" class="select2 w-full" data-placeholder="Select Product via Search or Scanner...">
                    <option value="">Select Product via Search or Scanner...</option>
                    @foreach($allProducts as $product)
                        @php $is_counted = in_array($product->id, $countedIds); @endphp
                        <option value="{{ $product->hash_id }}" data-partno="{{ $product->part_no }}" data-counted="{{ $is_counted ? 'true' : 'false' }}">
                             {{ $product->part_no }} {{ $product->revision ? '- ' . $product->revision : '' }} - {{ $product->part_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button id="btn-scan" class="w-full sm:w-auto flex-shrink-0 bg-gray-50 hover:bg-gray-100 text-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-100 px-5 py-2.5 rounded border border-gray-200 dark:border-gray-600 transition-all shadow-sm flex items-center justify-center gap-2" title="Open Scanner Camera">
                <i class="fa-solid fa-camera text-lg"></i>
                <span class="sm:hidden font-bold text-xs uppercase tracking-wider">Scan QR Code</span>
            </button>
        </div>

        <div class="mt-4 hidden" id="scanResultArea">
             <div class="flex flex-col md:flex-row items-stretch gap-6 p-4 md:p-5 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                 
                 <!-- Product Info Section -->
                 <div class="flex-1 flex flex-col justify-center min-w-0">
                    <div class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-1 truncate" id="resPartNo">-</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white leading-tight mb-4 break-words" id="resPartName">-</div>
                    
                    <div class="grid grid-cols-3 gap-2">
                        <div class="flex flex-col px-3 py-2 bg-white dark:bg-gray-800 rounded border border-gray-100 dark:border-gray-700">
                            <span class="text-[8px] font-bold text-gray-400 uppercase leading-none mb-1">Unit</span>
                            <span class="text-xs font-bold text-gray-900 dark:text-white truncate" id="resUnit">-</span>
                        </div>
                        <div class="flex flex-col px-3 py-2 bg-white dark:bg-gray-800 rounded border border-gray-100 dark:border-gray-700">
                            <span class="text-[8px] font-bold text-blue-400 uppercase leading-none mb-1">System</span>
                            <span class="text-xs font-bold text-blue-600 dark:text-blue-400 truncate" id="resSystemQty">0</span>
                        </div>
                        <div class="flex flex-col px-3 py-2 bg-white dark:bg-gray-800 rounded border border-gray-100 dark:border-gray-700">
                            <span class="text-[8px] font-bold text-purple-400 uppercase leading-none mb-1">Prev</span>
                            <span class="text-xs font-bold text-purple-600 dark:text-purple-400 truncate" id="resPrevReal">0</span>
                        </div>
                    </div>
                 </div>
                 
                 <div class="hidden md:block w-px bg-gray-200 dark:bg-gray-700 my-2"></div>

                 <!-- Entry Form Section -->
                 <div class="flex-1 flex flex-col sm:flex-row md:flex-col lg:flex-row items-stretch sm:items-end md:items-stretch lg:items-end gap-3 lg:w-3/5">
                    <div class="flex-1 flex flex-col">
                        <div id="unitHelperLabel" class="text-[9px] font-bold text-blue-500 uppercase tracking-tighter mb-1.5 hidden">Enter Qty</div>
                        <div class="relative">
                            <input type="number" id="realQtyInput" step="any" 
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded h-[46px] text-center font-bold text-lg focus:border-blue-500 transition-all outline-none" 
                                placeholder="0.00">
                        </div>
                    </div>

                    <div class="flex-1">
                        <div class="sm:hidden md:block lg:hidden text-[9px] font-bold text-gray-400 uppercase mb-1.5">Note / Location</div>
                        <input type="text" id="remarkInput" 
                               class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 focus:border-blue-500 rounded h-[46px] text-sm px-4 outline-none transition-all" 
                               placeholder="Remark / Location...">
                    </div>
                    
                    <button id="btnSaveCount" class="w-full sm:w-auto md:w-full lg:w-auto h-[46px] bg-slate-900 dark:bg-blue-600 text-white px-8 rounded font-bold text-xs uppercase tracking-widest transition-all hover:bg-slate-800 dark:hover:bg-blue-700 flex items-center justify-center gap-2 flex-shrink-0">
                        Commit <i class="fa-solid fa-arrow-right"></i>
                    </button>
                 </div>
             </div>
             <input type="hidden" id="currentHashId">
        </div>
        <div id="scanError" class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 text-red-600 text-xs font-bold rounded-md border border-red-100 dark:border-red-800 hidden items-center gap-2">
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
        
        <div class="overflow-x-auto w-full custom-scrollbar">
            <x-table id="stoDetailsTable" class="w-full">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-center">No</th>
                        <th rowspan="2" class="text-left">Timestamp</th>
                        <th rowspan="2" class="text-left">Material Information</th>
                        <th rowspan="2" class="text-left">Auditor</th>
                        <th colspan="2" class="text-center bg-gray-50/50 dark:bg-gray-700/30">System Status</th>
                        <th colspan="2" class="text-center bg-blue-50/30 dark:bg-blue-900/10">Real Count</th>
                        <th colspan="2" class="text-center bg-slate-50/50 dark:bg-slate-700/30">Variance</th>
                        <th rowspan="2" class="text-left">Reason</th>
                        <th rowspan="2" class="text-left">Remark</th>
                        @if($stoEvent->status === 'OPEN')
                        <th rowspan="2" class="text-center">Action</th>
                        @endif
                    </tr>
                    <tr>
                        <th class="text-center border-t border-gray-100 dark:border-gray-700">Qty</th>
                        <th class="text-center border-t border-gray-100 dark:border-gray-700">Amount</th>
                        <th class="text-center border-t border-gray-100 dark:border-gray-700">Qty</th>
                        <th class="text-center border-t border-gray-100 dark:border-gray-700">Amount</th>
                        <th class="text-center border-t border-gray-100 dark:border-gray-700">Qty</th>
                        <th class="text-center border-t border-gray-100 dark:border-gray-700">Amount</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </x-table>
        </div>
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
                <label for="rejection_note" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-3">Feedback for the PIC</label>
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

<!-- Unscanned Items Modal -->
<div id="remainingItemsModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-slate-900/50 transition-all">
    <div class="bg-white dark:bg-gray-800 rounded shadow-xl w-full max-w-2xl overflow-hidden border border-gray-200 dark:border-gray-700 flex flex-col max-h-[85vh]">
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center shrink-0">
            <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-3 text-sm uppercase tracking-widest">
                <i class="fa-solid fa-clipboard-list text-blue-600"></i> Remaining Products
            </h3>
            <button onclick="closeRemainingModal()" class="text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="p-0 overflow-y-auto flex-1 h-full">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 bg-gray-50 dark:bg-gray-900 text-[10px] font-bold text-gray-500 uppercase tracking-widest border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">Part Name</th>
                        <th class="px-6 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($products as $p)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors group">
                        <td class="px-6 py-3 font-mono font-bold text-xs text-gray-900 dark:text-white">
                            {{ $p->part_no }} {{ $p->revision ? '- ' . $p->revision : '' }}
                        </td>
                        <td class="px-6 py-3 text-xs text-gray-600 dark:text-gray-400">{{ $p->part_name }}</td>
                        <td class="px-6 py-3 text-center">
                            <button onclick="closeRemainingModal(); editFromTable('{{ $p->hash_id }}')" 
                                    class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors" title="Scan Now">
                                <i class="fa-solid fa-camera text-lg"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic text-sm">All products have been scanned.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 shrink-0 text-center">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Remaining: {{ count($products) }} Items</span>
        </div>
    </div>
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
            table = window.defaultDataTable('#stoDetailsTable', {
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
                    { data: 'auditor', className: 'text-xs font-semibold text-blue-600 dark:text-blue-400' },
                    { data: 'system_qty', className: 'text-center font-mono text-sm group-hover:bg-gray-50 dark:group-hover:bg-gray-800' },
                    { data: 'system_amount', className: 'text-right pr-4 bg-gray-50/30 dark:bg-gray-800/20' },
                    { data: 'real_qty', className: 'text-center bg-blue-50/10 dark:bg-blue-900/5' },
                    { data: 'real_amount', className: 'text-right pr-4 bg-blue-50/20 dark:bg-blue-900/10' },
                    { data: 'diff', className: 'text-center font-bold' },
                    { data: 'diff_amount', className: 'text-right pr-4 bg-slate-50/30 dark:bg-slate-800/20' },
                    { data: 'reason', className: 'text-center' },
                    { data: 'remark', className: 'text-xs text-gray-500 italic' },
                    @if($stoEvent->status === 'OPEN')
                    { data: 'action', className: 'text-center', orderable: false }
                    @endif
                ],
                order: [[1, 'desc']],
                autoWidth: true,
                columnDefs: [
                    { targets: '_all', className: 'whitespace-nowrap px-4 py-3' }
                ]
            });

            // Inline Editing logic (Qty)
            $('#stoDetailsTable').on('blur', '.qty-input', function() {
                const $input = $(this);
                const productId = $input.data('product-id');
                const newQty = $input.val();
                const originalQty = $input.data('original-value');

                if (newQty === originalQty || !newQty || newQty === '') return;

                const $row = $input.closest('tr');
                const existingRemark = $row.find('.remark-input').val();
                const existingReasonId = $row.find('.reason-input').val();

                fetch(saveUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ 
                        product_id_hash: productId, 
                        real_qty: newQty, 
                        remark: existingRemark,
                        reason_id: existingReasonId
                    })
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
                const existingReasonId = $row.find('.reason-input').val();

                fetch(saveUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ 
                        product_id_hash: productId, 
                        real_qty: currentQty, 
                        remark: newRemark,
                        reason_id: existingReasonId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        $input.data('original-value', newRemark);
                        if (data.stats && window.updateStatsCard) window.updateStatsCard(data.stats);
                    }
                });
            });

            // Inline Editing logic (Reason)
            $('#stoDetailsTable').on('change', '.reason-input', function() {
                const $select = $(this);
                const reasonId = $select.val();
                const $row = $select.closest('tr');
                const $qtyInput = $row.find('.qty-input');
                const productId = $qtyInput.data('product-id');
                const currentQty = $qtyInput.val();
                const currentRemark = $row.find('.remark-input').val();

                fetch(saveUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ 
                        product_id_hash: productId, 
                        real_qty: currentQty, 
                        remark: currentRemark,
                        reason_id: reasonId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
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
        productSelect.select2({
            templateResult: function(data) {
                if (!data.id) return data.text;
                const $opt = $(data.element);
                const isCounted = $opt.data('counted') === true || $opt.data('counted') === 'true';
                
                if (isCounted) {
                    return $(`
                        <div class="flex items-center justify-between gap-2">
                            <span class="flex items-center gap-2 overflow-hidden">
                                <i class="fa-solid fa-circle-check text-emerald-500 shrink-0"></i>
                                <span class="truncate text-xs">${data.text}</span>
                            </span>
                            <span class="text-[9px] text-emerald-600 font-bold shrink-0">COUNTED</span>
                        </div>
                    `);
                }
                return data.text;
            },
            templateSelection: function(data) {
                if (!data.id) return data.text;
                return $(`<span class="text-xs font-bold truncate block w-full">${data.text}</span>`);
            }
        });
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
        // Use is_new_snapshot to determine if we should alert
        if (data.is_new_snapshot === false) {
            const prevVal = data.prev_real_qty !== null ? data.prev_real_qty : 0;
            Swal.fire({
                title: 'Already Counted!',
                html: `This item has been filled with <b>${prevVal} ${data.unit}</b>.<br><small class="text-gray-500">Do you want to update the existing result?</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-pen-to-square"></i> Yes, Edit This',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                customClass: {
                    confirmButton: 'font-bold uppercase tracking-widest text-xs px-6 py-2.5',
                    cancelButton: 'font-bold uppercase tracking-widest text-xs px-6 py-2.5'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    processShowResult(data);
                } else {
                    resultArea.classList.add('hidden');
                    $('#product_detail_id').val('').trigger('change');
                }
            });
        } else {
            processShowResult(data);
        }
    }

    function processShowResult(data) {
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
        
        // Visual indicator for editing existing
        if (data.prev_real_qty !== null) {
            realQtyInput.classList.add('border-amber-400', 'bg-amber-50/50');
            setTimeout(() => realQtyInput.classList.remove('border-amber-400', 'bg-amber-50/50'), 2000);
        }

        setTimeout(() => realQtyInput.focus(), 100);
    }

    function showError(msg) {
        errorMsg.innerText = msg;
        errorArea.classList.remove('hidden');
        resultArea.classList.add('hidden');
    }

    window.editFromTable = function(hashId) {
        // Scroll to entry area for better UX
        document.getElementById('product_detail_id').scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Force refresh data even if ID is same
        fetchStoInfo(hashId);
        
        // Match Select2 visual
        $('#product_detail_id').val(hashId).trigger('change.select2');
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
                // Mark as counted in dropdown
                const $opt = $(`#product_detail_id option[value="${hash}"]`);
                if ($opt.length) {
                    $opt.data('counted', 'true').attr('data-counted', 'true');
                    // Text change removed to keep option content clean
                }

                // Success feedback
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: 'Count result recorded successfully.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500
                });

                // Clear input area
                resultArea.classList.add('hidden');
                $('#product_detail_id').val('').trigger('change');
                
                // Refresh table and stats
                table.ajax.reload(null, false);
                if (data.stats && window.updateStatsCard) window.updateStatsCard(data.stats);
            } else {
                Swal.fire('Error', data.message || 'Failed to save.', 'error');
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
        set('stat-total-unscanned', s.total_unscanned);
        
        // Dynamic Alert Visibility
        const unscannedCount = parseInt(s.total_unscanned) || 0;
        const alertBanner = document.getElementById('unscanned-alert-banner');
        const alertPulse = document.getElementById('stat-unscanned-pulse');
        const alertBadge = document.getElementById('stat-unscanned-badge');
        const bannerCount = document.getElementById('banner-unscanned-count');

        if (unscannedCount > 0) {
            if (alertBanner) alertBanner.classList.remove('hidden');
            if (alertPulse) alertPulse.classList.remove('hidden');
            if (alertBadge) alertBadge.classList.remove('hidden');
            if (bannerCount) bannerCount.innerText = unscannedCount;
        } else {
            if (alertBanner) alertBanner.classList.add('hidden');
            if (alertPulse) alertPulse.classList.add('hidden');
            if (alertBadge) alertBadge.classList.add('hidden');
        }

        set('stat-total-matched', s.total_matched);
        set('table-total-matched', s.total_matched);
        set('table-total-diff', s.total_diff);
        set('stat-total-increase-pcs', fmt(s.total_increase_pcs) + ' Pcs');
        set('stat-total-increase', '(' + fmt(s.total_increase) + ' Unit / ' + s.count_increase + ' items)');
        set('stat-total-decrease-pcs', fmt(s.total_decrease_pcs) + ' Pcs');
        set('stat-total-decrease', '(' + fmt(s.total_decrease) + ' Unit / ' + s.count_decrease + ' items)');
        set('stat-net-adjustment-pcs', (n >= 0 ? '+' : '') + fmt(s.net_adjustment_pcs) + ' Pcs');
        set('stat-net-adjustment', '(' + (n >= 0 ? '+' : '') + fmt(n) + ' Unit)');
        
        // Net Financial Impact Update
        const amtElem = document.getElementById('stat-net-amount-impact');
        const bgElem = document.getElementById('stat-net-amount-bg');
        if (amtElem) {
            const v = s.net_amount_impact;
            amtElem.innerText = fmt(Math.abs(v));
            if (v >= 0) {
                amtElem.classList.remove('text-red-700');
                amtElem.classList.add('text-emerald-700');
                if (bgElem) {
                    bgElem.classList.remove('bg-red-50', 'border-red-100', 'text-red-600');
                    bgElem.classList.add('bg-emerald-50', 'border-emerald-100', 'text-emerald-600');
                }
            } else {
                amtElem.classList.remove('text-emerald-700');
                amtElem.classList.add('text-red-700');
                if (bgElem) {
                    bgElem.classList.remove('bg-emerald-50', 'border-emerald-100', 'text-emerald-600');
                    bgElem.classList.add('bg-red-50', 'border-red-100', 'text-red-600');
                }
            }
        }
    };

    window.openRemainingModal = () => $('#remainingItemsModal').removeClass('hidden').addClass('flex');
    window.closeRemainingModal = () => $('#remainingItemsModal').addClass('hidden').removeClass('flex');

    window.openRejectModal = () => $('#rejectModal').removeClass('hidden').addClass('flex');
    window.closeRejectModal = () => $('#rejectModal').addClass('hidden').removeClass('flex');

    // Keybindings: Enter and Alt+S
    if (realQtyInput) {
        realQtyInput.addEventListener('keydown', e => { if(e.key === 'Enter') remarkInput.focus(); });
    }
    if (remarkInput) {
        remarkInput.addEventListener('keydown', e => { if(e.key === 'Enter') saveCount(); });
    }

    // Handle initial product selection from URL parameter (Redirect from Scan Info)
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const productHash = urlParams.get('product');
        
        if (productHash && document.getElementById('product_detail_id')) {
            setTimeout(() => {
                console.log("[STO] Auto-selecting product from URL:", productHash);
                $('#product_detail_id').val(productHash).trigger('change.select2');
                fetchStoInfo(productHash);
            }, 500); 
        }
    });
</script>

@endpush
@endsection
