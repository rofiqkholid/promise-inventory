@extends('layouts.app')

@section('title', 'STO Event Details')
@section('page_title', 'Stock Opname')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    <!-- Header -->
    <div class="mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-2.5">
                <a href="{{ route('inventory.sto.index') }}" class="h-10 px-3 inline-flex items-center justify-center rounded-xs bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-500 hover:text-primary-600 hover:border-primary-100 hover:bg-primary-50 transition-all" title="Back to STO Monitor">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    <span class="ml-2 text-[10px] font-black uppercase tracking-widest">Back</span>
                </a>
                <span class="w-1 h-1 rounded-xs bg-gray-300 dark:bg-gray-700"></span>
                @php
                    $statusClasses = [
                        'OPEN' => 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-400 dark:border-emerald-800',
                        'WAITING CHECK' => 'bg-amber-50 text-amber-600 border-amber-100 dark:bg-amber-900/40 dark:text-amber-400 dark:border-amber-800',
                        'WAITING APPROVAL' => 'bg-primary-50 text-primary-600 border-primary-100 dark:bg-primary-900/40 dark:text-primary-400 dark:border-primary-800',
                        'CLOSED' => 'bg-slate-50 text-slate-600 border-slate-100 dark:bg-slate-900/40 dark:text-slate-400 dark:border-slate-800'
                    ];
                    $currentStatusClass = $statusClasses[$stoEvent->status] ?? $statusClasses['CLOSED'];
                @endphp
                <span class="px-2 py-1 text-xs rounded-xs font-black uppercase tracking-widest border {{ $currentStatusClass }}">
                    {{ str_replace('_', ' ', $stoEvent->status) }}
                </span>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight leading-none">{{ $stoEvent->name }}</h2>
            
            <div class="mt-3 flex flex-wrap items-center gap-4 text-[11px] font-bold">
                <div class="flex items-center gap-1.5 text-gray-400 bg-gray-50 dark:bg-gray-800/50 px-2.5 py-1 rounded-xs border border-gray-100 dark:border-gray-700">
                    <span class="text-[9px] uppercase tracking-wider opacity-60">Code:</span>
                    <span class="font-mono text-gray-700 dark:text-gray-300">#{{ $stoEvent->code }}</span>
                </div>
                <div class="flex items-center gap-1.5 text-gray-400 bg-gray-50 dark:bg-gray-800/50 px-2.5 py-1 rounded-xs border border-gray-100 dark:border-gray-700">
                    <i class="fa-solid fa-calendar-alt opacity-60"></i>
                    <span class="text-[9px] uppercase tracking-wider opacity-60 mr-0.5">Started:</span>
                    <span class="text-gray-700 dark:text-gray-300">{{ $stoEvent->period_start->format('d M Y') }}</span>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('inventory.sto.exportExcel', $stoEvent->hash_id) }}" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold rounded-xs transition-all uppercase tracking-widest active:scale-[0.98]">
                <i class="fa-solid fa-file-excel text-sm"></i> 
                Export Result
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
                    <button type="button" onclick="confirmSubmitForCheck()" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-[10px] font-bold rounded-xs transition-all uppercase tracking-widest active:scale-[0.98]">
                        <i class="fa-solid fa-paper-plane text-sm"></i> 
                        Submit for Check
                    </button>
                </form>
            @endif

            @if($stoEvent->status === 'WAITING CHECK' && $isChecker)
                <form action="{{ route('inventory.sto.verify', $stoEvent->hash_id) }}" method="POST" id="verifyForm" class="inline">
                    @csrf
                    <button type="button" onclick="confirmVerify()" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-bold rounded-xs transition-all uppercase tracking-widest active:scale-[0.98]">
                        <i class="fa-solid fa-check-double text-sm"></i> 
                        Verify Data
                    </button>
                </form>
                <button type="button" onclick="openRejectModal()" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-rose-500 hover:bg-rose-600 text-white text-[10px] font-bold rounded-xs transition-all uppercase tracking-widest active:scale-[0.98]">
                    <i class="fa-solid fa-xmark text-sm"></i> 
                    Reject
                </button>
            @endif

            @if($stoEvent->status === 'WAITING APPROVAL' && $isApprover)
                <form action="{{ route('inventory.sto.finalize', $stoEvent->hash_id) }}" method="POST" id="finalizeForm" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-[10px] font-bold rounded-xs hover:bg-slate-800 transition-all uppercase tracking-widest active:scale-[0.98]">
                        <i class="fa-solid fa-lock text-sm"></i> 
                        Finalize & Adjust
                    </button>
                </form>
                <button type="button" onclick="openRejectModal()" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-rose-500 hover:bg-rose-600 text-white text-[10px] font-bold rounded-xs transition-all uppercase tracking-widest active:scale-[0.98]">
                    <i class="fa-solid fa-xmark text-sm"></i> 
                    Reject
                </button>
            @endif

            @if(($stoEvent->status === 'CLOSED' || $stoEvent->status === 'WAITING CHECK' || $stoEvent->status === 'WAITING APPROVAL') && $isApprover)
                <form action="{{ route('inventory.sto.reopen', $stoEvent->hash_id) }}" method="POST" id="reopenForm" class="inline">
                    @csrf
                    <button type="button" onclick="confirmReopen()" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-gray-600 hover:bg-gray-700 text-white text-[10px] font-bold rounded-xs transition-all uppercase tracking-widest active:scale-[0.98]">
                        <i class="fa-solid fa-rotate-left text-sm"></i> 
                        Reopen
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Statistics Dashboard -->
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 overflow-hidden relative">
        <div class="relative grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 divide-x divide-y md:divide-y-0 divide-gray-100 dark:divide-gray-700">
            <div class="p-4 flex flex-col items-center text-center group">
                <div class="w-10 h-10 mb-2 flex items-center justify-center bg-primary-50 dark:bg-primary-900/30 rounded-xs text-primary-600 dark:text-primary-400">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Recorded</span>
                <div class="flex items-baseline gap-1">
                    <span id="stat-total-items" class="text-xl font-bold text-gray-900 dark:text-white">{{ $stats['total_items'] }}</span>
                    <span class="text-[9px] font-bold text-primary-500 bg-primary-50 dark:bg-primary-900/40 px-1.5 py-0.5 rounded-xs"><span id="stat-progress">{{ $progress }}</span>%</span>
                </div>
                <span class="text-[9px] font-medium text-gray-400 mt-1 uppercase">Items Recorded</span>
            </div>

            <!-- Total Recorded PCS -->
            <div class="p-4 flex flex-col items-center text-center group">
                <div class="w-10 h-10 mb-2 flex items-center justify-center bg-primary-50 dark:bg-primary-900/30 rounded-xs text-primary-600 dark:text-primary-400">
                    <i class="fa-solid fa-calculator"></i>
                </div>
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Total Qty Counted</span>
                <span id="stat-total-recorded-pcs" class="text-xl font-bold text-primary-700 dark:text-primary-400 leading-none">
                    {{ number_format($stats['total_recorded_pcs'] ?? 0, 0) }} 
                </span>
                <span class="text-[9px] font-medium text-gray-400 mt-1 uppercase">PCS Recorded</span>
            </div>

            <!-- Remaining -->
            <div onclick="openRemainingModal()" class="p-4 flex flex-col items-center text-center group cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-all relative overflow-hidden">
                <div class="w-10 h-10 mb-2 flex items-center justify-center bg-amber-50 dark:bg-amber-900/30 rounded-xs text-amber-600 dark:text-amber-400">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Remaining</span>
                <span id="stat-total-missing-items" class="text-xl font-bold text-amber-600 leading-none">
                    {{ $stats['total_missing_items'] ?? ($stats['total_count'] - $stats['total_items']) }}
                </span>
            </div>

            <!-- Increment -->
            <div class="p-4 flex flex-col items-center text-center group">
                <div class="w-10 h-10 mb-2 flex items-center justify-center bg-emerald-50 dark:bg-emerald-900/30 rounded-xs text-emerald-600 dark:text-emerald-400">
                    <i class="fa-solid fa-square-plus text-lg"></i>
                </div>
                <span class="text-[9px] font-bold text-emerald-600/70 dark:text-emerald-400 uppercase tracking-widest mb-1">Stock Increment</span>
                <span id="stat-total-increase-pcs" class="text-lg font-bold text-emerald-700 dark:text-emerald-400 leading-none">{{ number_format($stats['total_increase_pcs'], 0) }} Pcs</span>
                <span id="stat-total-increase" class="text-[9px] font-medium text-gray-400 mt-1">({{ number_format($stats['total_increase'], 0) }} Unit / {{ $stats['count_increase'] }} items)</span>
            </div>

            <!-- Decrement -->
            <div class="p-4 flex flex-col items-center text-center group">
                <div class="w-10 h-10 mb-2 flex items-center justify-center bg-rose-50 dark:bg-rose-900/30 rounded-xs text-rose-600 dark:text-rose-400">
                    <i class="fa-solid fa-square-minus text-lg"></i>
                </div>
                <span class="text-[9px] font-bold text-rose-600/70 dark:text-rose-400 uppercase tracking-widest mb-1">Stock Decrement</span>
                <span id="stat-total-decrease-pcs" class="text-lg font-bold text-rose-700 dark:text-rose-400 leading-none">{{ number_format($stats['total_decrease_pcs'], 0) }} Pcs</span>
                <span id="stat-total-decrease" class="text-[9px] font-medium text-gray-400 mt-1">({{ number_format($stats['total_decrease'], 0) }} Unit / {{ $stats['count_decrease'] }} items)</span>
            </div>

            <!-- Net Adjustment -->
            <div class="p-4 flex flex-col items-center text-center group">
                <div class="w-10 h-10 mb-2 flex items-center justify-center bg-purple-50 dark:bg-purple-900/30 rounded-xs text-purple-600 dark:text-purple-400">
                    <span class="font-black text-xs">NET</span>
                </div>
                <span class="text-[9px] font-bold text-purple-600/70 dark:text-purple-400 uppercase tracking-widest mb-1">Adjustment Impact</span>
                <span id="stat-net-adjustment-pcs" class="text-lg font-bold text-purple-700 dark:text-purple-400 leading-none">{{ ($stats['net_adjustment_pcs'] >= 0 ? '+' : '') . number_format($stats['net_adjustment_pcs'], 0) }} Pcs</span>
                <span id="stat-net-adjustment" class="text-[9px] font-medium text-gray-400 mt-1">({{ ($netAdjustment >= 0 ? '+' : '') . number_format($netAdjustment, 0) }} Unit)</span>
            </div>

            <!-- Financial Impact -->
            <div class="p-4 flex flex-col items-center text-center group">
                <div id="stat-net-amount-bg" class="w-10 h-10 mb-2 flex items-center justify-center rounded-xs {{ $stats['net_amount_impact'] >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                    <i class="fa-solid fa-coins text-lg"></i>
                </div>
                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">Financial Impact</span>
                <span id="stat-net-amount-impact" class="text-lg font-bold leading-none {{ $stats['net_amount_impact'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                    {{ ($stats['net_amount_impact'] > 0 ? '+' : ($stats['net_amount_impact'] < 0 ? '-' : '')) . number_format(abs($stats['net_amount_impact'] ?? 0), 0) }}
                </span>
                <span class="text-[9px] font-medium text-gray-400 mt-1 uppercase">Total Currency</span>
            </div>

            <!-- Perfect Match -->
            <div class="p-4 flex flex-col items-center text-center group">
                <div class="w-10 h-10 mb-2 flex items-center justify-center bg-slate-50 dark:bg-slate-900 rounded-xs text-slate-400 border border-slate-100 dark:border-slate-800">
                    <i class="fa-solid fa-circle-check text-lg"></i>
                </div>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Perfect Match</span>
                <span id="stat-total-matched" class="text-lg font-bold text-slate-900 dark:text-white leading-none">{{ $stats['total_matched'] }}</span>
                <span class="text-[9px] font-medium text-slate-400 mt-1 lowercase">items found match</span>
            </div>
        </div>
    </div>

    @if($stoEvent->status === 'OPEN' && $stoEvent->rejection_note)
    <div class="bg-rose-50 dark:bg-rose-900/20 border-l-4 border-rose-500 p-4 mb-6 rounded-xs shadow-sm animate-pulse-once">
        <div class="flex items-start gap-4">
            <div class="p-2 bg-rose-100 dark:bg-rose-900/40 rounded-xs text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800">
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
    <div id="missing-alert-banner" class="{{ ($stats['total_missing_items'] ?? 0) > 0 ? '' : 'hidden' }} bg-amber-50 dark:bg-amber-900/20 border-l-4 border-l-amber-500 p-4 mb-6 rounded-xs shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 w-8 h-8 bg-amber-100 dark:bg-amber-900/40 rounded-full flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h4 class="text-[10px] font-bold text-amber-800 dark:text-amber-300 uppercase tracking-widest">Missing Counts</h4>
                    <p class="text-xs text-amber-700 dark:text-amber-400">
                        <span id="banner-missing-count" class="font-bold">{{ $stats['total_missing_items'] }}</span> items have not been recorded yet.
                    </p>
                </div>
            </div>
            <button onclick="openRemainingModal()" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-[10px] font-bold rounded-xs transition-all uppercase tracking-widest active:scale-[0.98]">
                VIEW MISSING
            </button>
        </div>
    </div>

    <!-- SCANNER SECTION -->
    <div class="bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xs p-5 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-900 dark:text-white uppercase tracking-wider text-xs flex items-center gap-2">
                <i class="fa-solid fa-barcode text-primary-600"></i> Count Entry
            </h3>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 min-w-0">
                <select id="product_detail_id" class="select2 w-full" data-placeholder="Pick Product via Search or Scanning...">
                    <option value="">Pick Product via Search or Scanning...</option>
                    @foreach($allProducts as $product)
                        @php $is_counted = in_array($product->id, $countedIds); @endphp
                        <option value="{{ $product->hash_id }}" data-partno="{{ $product->part_no }}{{ $product->revision ? ' - ' . $product->revision : '' }}" data-counted="{{ $is_counted ? 'true' : 'false' }}">
                             {{ $product->part_no }} {{ $product->revision ? '- ' . $product->revision : '' }} - {{ $product->part_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button id="btn-scan" class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xs text-[10px] font-bold text-gray-700 dark:text-gray-100 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-all active:scale-[0.98]" title="Open Scanner Camera">
                <i class="fa-solid fa-camera text-sm"></i>
                <span class="sm:hidden">Scan QR Code</span>
            </button>
        </div>

        <div class="mt-4 hidden" id="scanResultArea">
             <div class="flex flex-col md:flex-row items-stretch gap-6 p-4 md:p-5 rounded-xs border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                 
                 <!-- Product Info Section -->
                 <div class="flex-1 flex flex-col justify-center min-w-0">
                     <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1 opacity-70">Selected Product</span>
                     <div class="text-xl font-semibold text-gray-900 dark:text-white tracking-tighter leading-none mb-1 break-all" id="resPartNo">-</div>
                     <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide truncate" id="resPartName">-</div>
                    
                    <div class="grid grid-cols-3 gap-2">
                        <div class="flex flex-col px-3 py-2 bg-white dark:bg-gray-800 rounded-xs border border-gray-100 dark:border-gray-700">
                            <span class="text-[8px] font-bold text-gray-400 uppercase leading-none mb-1">Unit</span>
                            <span class="text-xs font-bold text-gray-900 dark:text-white truncate" id="resUnit">-</span>
                        </div>
                        <div class="flex flex-col px-3 py-2 bg-white dark:bg-gray-800 rounded-xs border border-gray-100 dark:border-gray-700">
                            <span class="text-[8px] font-bold text-primary-400 uppercase leading-none mb-1">System</span>
                            <span class="text-xs font-bold text-primary-600 dark:text-primary-400 truncate" id="resSystemQty">0</span>
                        </div>
                        <div class="flex flex-col px-3 py-2 bg-white dark:bg-gray-800 rounded-xs border border-gray-100 dark:border-gray-700">
                            <span class="text-[8px] font-bold text-purple-400 uppercase leading-none mb-1">Entries</span>
                            <span class="text-xs font-bold text-purple-600 dark:text-purple-400 truncate" id="resEntriesCount">0</span>
                        </div>
                    </div>
                 </div>
                 
                 <div class="hidden md:block w-px bg-gray-200 dark:bg-gray-700 my-2"></div>

                 <!-- Entry Form Section -->
                  <div class="flex-[2] flex flex-col gap-4" id="entriesFormContainer">
                     <!-- Dynamic Rows -->
                  </div>
               </div>
               <div class="mt-4 flex justify-center">
                  <button type="button" onclick="addNewEntryRow()" class="flex items-center justify-center gap-2 px-6 py-2 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 rounded-full text-[10px] font-bold uppercase tracking-widest transition-all active:scale-[0.98]">
                      <i class="fa-solid fa-plus text-sm"></i> 
                      Add New
                  </button>
               </div>
             <input type="hidden" id="currentHashId">
        </div>
        <div id="scanError" class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 text-red-600 text-xs font-bold rounded-xs border border-red-100 dark:border-red-800 hidden items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation"></i> <span id="errorMsg"></span>
        </div>
    </div>
    @endif
    
    @include('components.scanner-modal')

    <!-- RESULTS TABLE -->
    <div class="bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xs overflow-hidden">
        <div class="p-4 md:p-6 border-b border-gray-50 dark:border-gray-700 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-slate-100 dark:bg-slate-700 rounded-xs">
                   <i class="fa-solid fa-list-check text-slate-600 dark:text-slate-300"></i>
                </div>
                <div>
                   <h3 class="font-bold text-gray-900 dark:text-white uppercase tracking-widest text-sm">Counting Journal</h3>
                   <p class="text-xs text-gray-400 font-medium tracking-tighter">Real-time log of recorded quantities.</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="px-4 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 rounded-xs flex items-center gap-3">
                    <span class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">Matched</span>
                    <span id="table-total-matched" class="text-sm font-bold text-emerald-700 dark:text-emerald-400">{{ $stats['total_matched'] }}</span>
                </div>
                <div class="px-4 py-1.5 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800 rounded-xs flex items-center gap-3">
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
                        <th rowspan="2" class="text-left">Material Information</th>
                        <th rowspan="2" class="text-left">Auditor</th>
                        <th colspan="2" class="text-center bg-gray-50/50 dark:bg-gray-700/30">System Status</th>
                        <th colspan="2" class="text-center bg-primary-50/30 dark:bg-primary-900/10">Real Count</th>
                        <th colspan="2" class="text-center bg-slate-50/50 dark:bg-slate-700/30">Variance</th>
                        <th rowspan="2" class="text-left">Location</th>
                        <th rowspan="2" class="text-left">Reason</th>
                        <th rowspan="2" class="text-left">Remark</th>
                        <th rowspan="2" class="text-left">Timestamp</th>
                        @if($stoEvent->status === 'OPEN')
                        <th rowspan="2" class="w-[60px] text-center">Action</th>
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

<!-- Finalize Modal & Reject Modal UI logic remains the same, but styled consistently -->

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 transition-all">
    <div class="bg-white dark:bg-gray-800 rounded-xs shadow-xl w-full max-w-md overflow-hidden border border-slate-200 dark:border-gray-700">
        <div class="px-6 py-4 bg-rose-50 dark:bg-rose-900/20 border-b border-rose-100 dark:border-rose-800 flex justify-between items-center">
            <h3 class="font-bold text-rose-900 dark:text-rose-400 flex items-center gap-3 text-sm uppercase tracking-widest">
                <i class="fa-solid fa-ban"></i> Reject Submission
            </h3>
            <button onclick="closeRejectModal()" class="text-rose-400 hover:text-rose-900 dark:hover:text-white transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form action="{{ route('inventory.sto.reject', $stoEvent->hash_id) }}" method="POST" class="p-6">
            @csrf
            <div class="mb-5">
                <label for="rejection_note" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-3">Feedback for the PIC</label>
                <textarea name="rejection_note" id="rejection_note" rows="4" required 
                    class="w-full bg-slate-50 dark:bg-gray-900 border-2 border-slate-100 dark:border-gray-700 rounded-xs p-4 text-sm font-bold focus:ring-0 focus:border-rose-500 transition-all dark:text-gray-200 placeholder-slate-300 outline-none"
                    placeholder="Provide clear reasons for rejection..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeRejectModal()" class="flex-1 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-gray-800 rounded-xs transition-all">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-3 bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-bold rounded-xs transition-all active:scale-95 uppercase tracking-widest shadow-lg shadow-rose-100 dark:shadow-none">
                    Confirm Reject
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Unscanned Items Modal -->
<div id="remainingItemsModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-slate-900/50 transition-all">
    <div class="bg-white dark:bg-gray-800 rounded-xs shadow-xl w-full max-w-2xl overflow-hidden border border-slate-200 dark:border-gray-700 flex flex-col max-h-[85vh]">
        <div class="px-6 py-4 bg-slate-50 dark:bg-gray-900 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center shrink-0">
            <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-3 text-sm uppercase tracking-widest">
                <i class="fa-solid fa-clipboard-list text-primary-600"></i> Remaining Products
            </h3>
            <button onclick="closeRemainingModal()" class="text-slate-400 hover:text-rose-500 transition-colors">
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
                            <button onclick="closeRemainingModal(); editFromTable('{{ $p->hash_id }}', null)" 
                                    class="h-8 w-8 inline-flex items-center justify-center text-primary-600 rounded-xs bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:hover:bg-primary-900/30 transition-all" title="Record Now">
                                <i class="fa-solid fa-pen-to-square text-sm"></i>
                            </button>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic text-sm">All products have been recorded.</td>
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
    
    // Inject reasons for inline dropdowns
    const stoReasons = @json(\App\Models\InventoryModel\StoReason::where('is_active', true)->get());

    // JS Formatter Helpers
    function formatQtyHtml(qty, pcsPerUnit, unitCode, weightKg, prefix = '') {
        qty = parseFloat(qty || 0);
        pcsPerUnit = parseFloat(pcsPerUnit || 1);
        weightKg = parseFloat(weightKg || 0);
        unitCode = (unitCode || '').toLowerCase();
        
        let pcs = 0;
        if (unitCode.includes('coil') && weightKg > 0) {
            pcs = Math.floor(qty / weightKg) * pcsPerUnit;
        } else {
            pcs = qty * pcsPerUnit;
        }

        let pcsDisplay = Math.abs(pcs).toLocaleString(undefined, { maximumFractionDigits: 0 });
        
        if (pcsPerUnit == 1 && !unitCode.includes('coil')) return `<span class='font-bold'>${prefix}${pcsDisplay}</span>`;
        
        let unitDisplay = Math.abs(qty).toLocaleString(undefined, { maximumFractionDigits: 2 });
        return `
            <div class='flex flex-col items-center justify-center'>
                <span class='font-bold'>${prefix}${pcsDisplay}</span>
                <span class='text-[10px] text-gray-400 leading-none mt-0.5'>(${unitDisplay} ${unitCode.toUpperCase()})</span>
            </div>`;
    }

    function formatCurrencyHtml(val, isDiff = false) {
        val = parseFloat(val || 0);
        if (val == 0) {
            if (isDiff) return '<span class="text-[11px] font-mono font-bold text-green-600">0</span>';
            return '<span class="text-gray-300">-</span>';
        }
        
        let color = 'text-gray-600 dark:text-gray-400';
        let prefix = '';
        if (isDiff) {
            color = 'text-red-600';
            prefix = val > 0 ? '+' : '-';
        }
        
        return `<span class="text-[11px] font-mono font-bold ${color}">${prefix}${Math.abs(val).toLocaleString()}</span>`;
    }

    // --- Modal Handlers ---
    function openRemainingModal() {
        document.getElementById('remainingItemsModal').classList.remove('hidden');
        document.getElementById('remainingItemsModal').classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeRemainingModal() {
        document.getElementById('remainingItemsModal').classList.add('hidden');
        document.getElementById('remainingItemsModal').classList.remove('flex');
        document.body.style.overflow = 'auto';
    }

    function openRejectModal() {
        document.getElementById('rejectModal').classList.remove('hidden');
        document.getElementById('rejectModal').classList.add('flex');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
        document.getElementById('rejectModal').classList.remove('flex');
    }

    // --- Confirmation Handlers ---
    function confirmSubmitForCheck() {
        Swal.fire({
            title: 'Submit for Verification?',
            text: "This will notify the checker to review the data. You won't be able to edit while it's in review.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            confirmButtonText: 'Yes, Submit'
        }).then((result) => {
            if (result.isConfirmed) {
                // Pre-check for missing reasons in the DOM to avoid page reload if possible
                const missingReasons = document.querySelectorAll('.reason-input.border-red-500');
                if (missingReasons.length > 0) {
                    Swal.fire({
                        title: 'Incomplete Reasons',
                        text: `There are ${missingReasons.length} mismatch items missing a Reason. Please scroll down to the Journal and fill them first.`,
                        icon: 'warning',
                        confirmButtonColor: '#f59e0b'
                    });
                    
                    // Optional: Smooth scroll to the first invalid reason
                    missingReasons[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
                
                document.getElementById('submitForCheckForm').submit();
            }
        });
    }

    function confirmVerify() {
        Swal.fire({
            title: 'Verify Data?',
            text: "Confirm that all counted data is accurate and ready for final approval.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            confirmButtonText: 'Yes, Verify'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('verifyForm').submit();
        });
    }

    function confirmReopen() {
        Swal.fire({
            title: 'Reopen Event?',
            text: "This will return the event to OPEN status for further editing.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Reopen'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('reopenForm').submit();
        });
    }

    // --- Stats Update Logic ---
    window.updateStatsCard = function(stats) {
        if (!stats) return;
        
        const formatNumber = (num, dec = 0) => parseFloat(num || 0).toLocaleString(undefined, {minimumFractionDigits: dec});
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.innerText = (val !== undefined && val !== null) ? val : '0';
        };

        setVal('stat-total-items', stats.total_items || 0);
        setVal('stat-progress', stats.progress || 0);
        setVal('stat-total-recorded-pcs', formatNumber(stats.total_recorded_pcs));
        setVal('stat-total-missing-items', stats.total_missing_items || 0);
        setVal('stat-total-increase-pcs', formatNumber(stats.total_increase_pcs) + ' Pcs');
        setVal('stat-total-increase', '(' + formatNumber(stats.total_increase) + ' Unit / ' + (stats.count_increase || 0) + ' items)');
        setVal('stat-total-decrease-pcs', formatNumber(stats.total_decrease_pcs) + ' Pcs');
        setVal('stat-total-decrease', '(' + formatNumber(stats.total_decrease) + ' Unit / ' + (stats.count_decrease || 0) + ' items)');
        
        const netPcsPrefix = (stats.net_adjustment_pcs || 0) >= 0 ? '+' : '';
        setVal('stat-net-adjustment-pcs', netPcsPrefix + formatNumber(stats.net_adjustment_pcs) + ' Pcs');
        
        const netUnitPrefix = (stats.net_adjustment || 0) >= 0 ? '+' : '';
        setVal('stat-net-adjustment', '(' + netUnitPrefix + formatNumber(stats.net_adjustment) + ' Unit)');
        
        const amountImpact = stats.net_amount_impact || 0;
        const amountPrefix = amountImpact > 0 ? '+' : (amountImpact < 0 ? '-' : '');
        setVal('stat-net-amount-impact', amountPrefix + formatNumber(Math.abs(amountImpact)));
        
        setVal('stat-total-matched', stats.total_matched || 0);
        setVal('table-total-matched', stats.total_matched || 0);
        setVal('table-total-diff', stats.total_diff || 0);

        // Financial Impact Color
        const amountBg = document.getElementById('stat-net-amount-bg');
        const amountText = document.getElementById('stat-net-amount-impact');
        if (amountBg && amountText) {
            if (amountImpact >= 0) {
                amountBg.classList.add('bg-emerald-50', 'text-emerald-600');
                amountBg.classList.remove('bg-rose-50', 'text-rose-600');
                amountText.classList.add('text-emerald-700');
                amountText.classList.remove('text-rose-700');
            } else {
                amountBg.classList.add('bg-rose-50', 'text-rose-600');
                amountBg.classList.remove('bg-emerald-50', 'text-emerald-600');
                amountText.classList.add('text-rose-700');
                amountText.classList.remove('text-emerald-700');
            }
        }

        // Banner Alert
        const banner = document.getElementById('missing-alert-banner');
        if (banner) {
            if (stats.total_missing_items > 0) {
                banner.classList.remove('hidden');
                setVal('banner-missing-count', stats.total_missing_items);
            } else {
                banner.classList.add('hidden');
            }
        }
    };

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
                    { data: 'row_number', className: 'text-center font-bold text-gray-500', orderable: false, searchable: false },
                    { 
                        data: null, 
                        className: 'font-medium',
                        render: function(data) {
                            return `
                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-gray-800 dark:text-gray-200">${data.part_no} - ${data.revision}</span>
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400 leading-tight uppercase">${data.part_name}</span>
                                </div>`;
                        }
                    },
                    { data: 'auditor', className: 'text-xs font-semibold text-primary-600 dark:text-primary-400' },
                    { 
                        data: null, 
                        className: 'text-center font-mono text-sm group-hover:bg-gray-50 dark:group-hover:bg-gray-800 bg-slate-50/20',
                        render: function(data) {
                            return formatQtyHtml(data.system_qty, data.pcs_per_unit, data.unit_code, data.weight_kg);
                        }
                    },
                    { 
                        data: 'system_amount', 
                        className: 'text-right pr-4 bg-gray-50/30 dark:bg-gray-800/20',
                        render: (val) => formatCurrencyHtml(val)
                    },
                    { 
                        data: null, 
                        className: 'text-center bg-primary-50/10 dark:bg-primary-900/5',
                        render: function(data) {
                            if (data.can_edit_inline) {
                                return `
                                    <div class="flex items-center justify-center gap-1">
                                        <input type="number" step="any" 
                                            class="qty-input text-center font-medium text-sm px-2 py-1 border border-slate-200 dark:border-gray-700 rounded-xs focus:ring-0 focus:border-primary-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100" 
                                            style="width: 80px; min-width: 80px;"
                                            data-detail-id="${data.hash_id}" 
                                            data-product-id="${data.product_hash_id}"
                                            value="${data.real_qty_input}" 
                                            placeholder="Qty" />
                                        <span class="text-[9px] font-bold text-gray-400 uppercase">${data.unit_code}</span>
                                    </div>`;
                            }
                            return `<div class="text-primary-600 dark:text-primary-400 font-bold">${formatQtyHtml(data.real_qty_input, data.pcs_per_unit, data.unit_code, data.weight_kg)}</div>`;
                        }
                    },
                    { 
                        data: 'real_amount', 
                        className: 'text-right pr-4 bg-primary-50/20 dark:bg-primary-900/10',
                        render: (val) => formatCurrencyHtml(val)
                    },
                    { 
                        data: null, 
                        className: 'text-center font-bold bg-slate-50/20',
                        render: function(data) {
                            if (data.diff_qty > 0) return `<div class="text-red-600 font-medium">${formatQtyHtml(data.diff_qty, data.pcs_per_unit, data.unit_code, data.weight_kg, '+')}</div>`;
                            if (data.diff_qty < 0) return `<div class="text-red-600 font-medium">${formatQtyHtml(Math.abs(data.diff_qty), data.pcs_per_unit, data.unit_code, data.weight_kg, '-')}</div>`;
                            return `<span class="text-sm font-bold text-emerald-600">0</span>`;
                        }
                    },
                    { 
                        data: 'diff_amount', 
                        className: 'text-right pr-4 bg-slate-50/30 dark:bg-slate-800/20',
                        render: (val) => formatCurrencyHtml(val, true)
                    },
                    { 
                        data: 'location_name', 
                        className: 'text-center text-xs',
                        render: (val) => val || '<span class="text-gray-400 italic">No Location</span>'
                    },
                    { 
                        data: null, 
                        className: 'text-center',
                        render: function(data) {
                            if (data.can_edit_inline) {
                                let category = data.category; // SHORTAGE or EXCESS
                                let options = stoReasons.filter(r => r.category === category || r.category === 'OTHERS')
                                    .map(r => `<option value="${r.id}" ${data.reason_id == r.id ? 'selected' : ''}>${r.name}</option>`)
                                    .join('');
                                
                                let isMismatch = Math.abs(data.total_diff_qty) > 0.0001;
                                let isInvalid = isMismatch && !data.group_has_reason && data.is_primary;
                                let borderClass = isInvalid ? 'border-red-500 bg-red-50/30' : 'border-slate-200 dark:border-gray-700';

                                return `
                                    <div class="flex flex-col items-center gap-1">
                                        <select class="reason-input text-xs pl-2 py-1 border ${borderClass} rounded-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-primary-500" style="width: 180px; min-width: 180px;" data-detail-id="${data.hash_id}">
                                            <option value="">-- Select Reason --</option>
                                            ${options}
                                        </select>
                                        ${isInvalid ? '<span class="text-[9px] font-black text-red-500 uppercase tracking-widest animate-pulse">Required reason</span>' : ''}
                                        ${data.reason_id ? '<span class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest"><i class="fa-solid fa-circle-check"></i> Reason set</span>' : ''}
                                    </div>`;
                            }

                            // Read-only view
                            if (data.reason_name) {
                                return `<span class="text-[10px] text-emerald-600 font-bold uppercase tracking-tight"><i class="fa-solid fa-circle-check"></i> ${data.reason_name}</span>`;
                            }
                            
                            let isMismatch = Math.abs(data.total_diff_qty) > 0.0001;
                            if (isMismatch && !data.group_has_reason && data.is_primary) {
                                return `<span class="text-[10px] text-red-500 font-bold uppercase tracking-widest">Reason Required</span>`;
                            }
                            
                            return `<span class="text-gray-400">-</span>`;
                        }
                    },
                    { 
                        data: null, 
                        className: 'text-xs text-gray-500 italic',
                        render: function(data) {
                            if (data.can_edit_inline) {
                                return `<input type="text" 
                                    class="remark-input text-xs px-2 py-1 border border-slate-200 dark:border-gray-700 rounded-xs focus:ring-0 focus:border-primary-500 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300" 
                                    style="width: 180px; min-width: 180px;"
                                    data-detail-id="${data.hash_id}" 
                                    value="${data.remark || ''}" 
                                    placeholder="Add note..." />`;
                            }
                            return data.remark || '-';
                        }
                    },
                    { data: 'updated_at', className: 'text-[10px] font-mono text-gray-500' },
                    @if($stoEvent->status === 'OPEN')
                    { 
                        data: null, 
                        className: 'text-center', 
                        orderable: false,
                        render: function(data) {
                            if (data.status !== 'OPEN') return '';
                            return `
                                <div class="flex items-center justify-center">
                                    <button type="button" onclick="deleteItem('${data.hash_id}')" 
                                             class="h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-xs bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors" title="Delete Entry">
                                        <i class="fa-solid fa-trash-can text-sm"></i>
                                    </button>
                                </div>`;
                        }
                    }
                    @endif
                ],
                order: [[2, 'asc']], // Order by Part No primarily for grouping
                autoWidth: true,
                columnDefs: [
                    { targets: '_all', className: 'whitespace-nowrap px-4 py-3' }
                ],
                drawCallback: function(settings) {
                    const api = this.api();
                    const rows = api.rows({ page: 'current' }).nodes();
                    let lastProduct = null;
                    let productCounts = {};
                    let startIdx = settings._iDisplayStart;
                    
                    // 1. First pass: Count items per product group on the current page
                    api.column(1, { page: 'current' }).data().each(function(data, i) {
                        const productHash = data.product_hash_id;
                        productCounts[productHash] = (productCounts[productHash] || 0) + 1;
                    });

                    let groupCounter = 1;
                    
                    // 2. Second pass: Apply rowspan and hide redundant cells
                    api.column(1, { page: 'current' }).data().each(function(data, i) {
                        const productHash = data.product_hash_id;
                        const $row = $(rows).eq(i);
                        
                        if (lastProduct !== productHash) {
                            // FIRST ROW of product group
                            const rowCount = productCounts[productHash];
                            const diffQty = data.total_diff_qty;
                            const diffClass = diffQty > 0 ? 'text-rose-600' : (diffQty < 0 ? 'text-rose-600' : 'text-emerald-600');
                            const diffIcon = diffQty > 0 ? '+' : '';

                            // Apply rowspans (No, Material Info, System Status Qty/Amount, Var Qty/Amount)
                            const mergeIndices = [0, 1, 3, 4, 7, 8];
                            mergeIndices.forEach(idx => {
                                const $td = $row.find(`td:eq(${idx})`);
                                $td.attr('rowspan', rowCount).css({
                                    'vertical-align': 'middle',
                                    'background-color': 'inherit'
                                });
                            });

                            // Set Group Data for merged columns
                            $row.find('td:eq(0)').html(startIdx + groupCounter++).addClass('font-black text-slate-900 bg-slate-50/30');
                            
                             $row.find('td:eq(3)').html(formatQtyHtml(data.total_system_qty, data.pcs_per_unit, data.unit_code, data.weight_kg)).addClass('bg-slate-50/50 dark:bg-slate-800/40 border-l border-slate-200');
                            $row.find('td:eq(4)').html(formatCurrencyHtml(data.total_system_amount)).addClass('bg-slate-50/50 dark:bg-slate-800/40');
                            
                            $row.find('td:eq(7)').html(`<div class="${diffClass}">${formatQtyHtml(diffQty, data.pcs_per_unit, data.unit_code, data.weight_kg, diffIcon)}</div>`).addClass('bg-slate-50/50 dark:bg-slate-800/40 border-l border-slate-200 border-r');
                            $row.find('td:eq(8)').html(`<div class="${diffClass}">${formatCurrencyHtml(data.total_diff_amount, true)}</div>`).addClass('bg-slate-50/50 dark:bg-slate-800/40');
                            
                            $row.addClass('border-t-2 border-slate-300 dark:border-slate-600');
                            lastProduct = productHash;
                        } else {
                            // SUBSEQUENT ROWS - hide merged cells
                            const mergeIndices = [0, 1, 3, 4, 7, 8];
                            mergeIndices.forEach(idx => {
                                $row.find(`td:eq(${idx})`).css('display', 'none');
                            });
                        }
                        
                        $row.addClass('hover:bg-primary-50/5 transition-colors');
                    });
                }
            });

            // Inline Editing logic (Qty)
            $('#stoDetailsTable').on('blur', '.qty-input', function() {
                const $input = $(this);
                const productId = $input.data('product-id');
                const detailId = $input.data('detail-id');
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
                        detail_id_hash: detailId,
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
                    } else {
                        Swal.fire('Error', data.message || 'Update failed', 'error');
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
                const detailId = $qtyInput.data('detail-id');
                const currentQty = $qtyInput.val();
                const existingReasonId = $row.find('.reason-input').val();

                fetch(saveUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ 
                        product_id_hash: productId, 
                        detail_id_hash: detailId,
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
                const detailId = $qtyInput.data('detail-id');
                const currentQty = $qtyInput.val();
                const currentRemark = $row.find('.remark-input').val();

                fetch(saveUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ 
                        product_id_hash: productId, 
                        detail_id_hash: detailId,
                        real_qty: currentQty, 
                        remark: currentRemark,
                        reason_id: reasonId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        table.ajax.reload(null, false);
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
                // Focus on Part Number for selection display
                const partNo = $(data.element).data('partno');
                return $(`<span class="text-xs font-bold text-gray-900 dark:text-white truncate block w-full">${partNo || data.text}</span>`);
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

    window.resetToNewEntry = function() {
        document.getElementById('currentDetailHashId').value = '';
        document.getElementById('editModeIndicator').classList.add('hidden');
        document.getElementById('realQtyInput').value = '';
        document.getElementById('remarkInput').value = '';
        $('#location_id').val('').trigger('change');
        document.getElementById('realQtyInput').focus();
    }

    let currentProductData = null;

    function showResult(data) {
        currentProductData = data;
        const container = document.getElementById('entriesFormContainer');
        container.innerHTML = ''; // Fresh start
        
        processShowResult(data);
        
        // Show all existing entries for this product
        if (data.existing_entries && data.existing_entries.length > 0) {
            data.existing_entries.forEach(entry => {
                createFormRow(entry);
            });
        }
        
        if (!data.existing_entries || data.existing_entries.length === 0) {
            createFormRow();
        }
    }

    function createFormRow(entry = null) {
        const container = document.getElementById('entriesFormContainer');
        const rowId = 'row-' + Math.random().toString(36).substr(2, 9);
        const locations = @json($locations);
        
        let locationOptions = '<option value="">-- No Location --</option>';
        locations.forEach(loc => {
            const selected = (entry && entry.location_id == loc.id) ? 'selected' : '';
            locationOptions += `<option value="${loc.id}" ${selected}>${loc.name}</option>`;
        });

        const rowHtml = `
            <div id="${rowId}" class="flex flex-col sm:flex-row items-end gap-3 p-3 rounded-xs bg-white dark:bg-gray-800 border ${entry ? 'border-primary-100 dark:border-primary-900/10 bg-primary-50/5' : 'border-gray-200 dark:border-gray-700'} transition-all hover:bg-gray-50 dark:hover:bg-gray-700/30 relative">
                <input type="hidden" class="row-detail-hash" value="${entry ? entry.detail_id_hash : ''}">
                
                ${entry ? `
                    <div class="absolute -top-2 left-3 px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-[8px] font-bold text-gray-500 rounded-full border border-gray-200 dark:border-gray-600 uppercase tracking-tighter">
                        <i class="fa-solid fa-user-check mr-1 opacity-70"></i> Recorded by: ${entry.auditor_name || 'System'}
                    </div>
                ` : ''}

                <div class="flex-1 w-full mt-2 sm:mt-0">
                    <div class="text-[8px] font-bold text-gray-400 uppercase mb-1">Quantity (${currentProductData.unit || 'PCS'})</div>
                    <input type="number" class="row-qty w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xs h-[40px] text-center font-semibold text-sm focus:border-primary-500 transition-all outline-none" 
                           placeholder="0.00" value="${entry ? entry.real_qty : ''}">
                </div>

                <div class="flex-[1.5] w-full">
                    <div class="text-[8px] font-bold text-gray-400 uppercase mb-1">Location</div>
                    <select class="row-location w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 focus:border-primary-500 rounded-xs h-[40px] text-xs px-3 outline-none transition-all">
                        ${locationOptions}
                    </select>
                </div>

                <div class="flex-[2] w-full">
                    <div class="text-[8px] font-bold text-gray-400 uppercase mb-1">Remark</div>
                    <input type="text" class="row-remark w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 focus:border-primary-500 rounded-xs h-[40px] text-xs px-3 outline-none transition-all" 
                           placeholder="Optional Note..." value="${entry ? entry.remark || '' : ''}">
                </div>

                <button type="button" onclick="saveRowCount('${rowId}')" 
                        class="h-[40px] px-4 rounded-xs font-bold text-[10px] uppercase tracking-widest transition-all bg-primary-600 hover:bg-primary-700 text-white flex items-center justify-center gap-2 active:scale-95">
                    ${entry ? '<i class="fa-solid fa-check"></i> Update' : '<i class="fa-solid fa-plus"></i> Save'}
                </button>
                
                ${entry ? `
                    <button type="button" onclick="deleteItem('${entry.detail_id_hash}')" class="h-[40px] w-[40px] flex items-center justify-center text-red-400 hover:text-red-600 transition-colors">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                ` : `
                    <button type="button" onclick="document.getElementById('${rowId}').remove()" class="h-[40px] w-[40px] flex items-center justify-center text-gray-400 hover:text-red-400">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                `}
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', rowHtml);
        
        // Auto focus the first blank row's qty
        if (!entry) {
            const lastRow = container.lastElementChild;
            lastRow.querySelector('.row-qty').focus();
        }
    }

    window.addNewEntryRow = function() {
        createFormRow();
    }

    window.saveRowCount = function(rowId) {
        const row = document.getElementById(rowId);
        const qty = row.querySelector('.row-qty').value;
        const locId = row.querySelector('.row-location').value;
        const remark = row.querySelector('.row-remark').value;
        const detailHash = row.querySelector('.row-detail-hash').value;
        const productHash = currentHashId.value;

        if (qty === '' || !productHash) return;

        // Visual feedback
        const btn = row.querySelector('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        btn.disabled = true;

        fetch(saveUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ 
                product_id_hash: productHash, 
                detail_id_hash: detailHash, 
                real_qty: qty, 
                remark: remark, 
                location_id: locId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500
                });
                
                row.querySelector('.row-detail-hash').value = data.detail_id_hash || '';
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Update';
                btn.className = 'h-[40px] px-4 rounded-xs font-bold text-[10px] uppercase tracking-widest transition-all bg-primary-600 hover:bg-primary-700 text-white flex items-center justify-center gap-2';
                btn.disabled = false;
                row.classList.replace('border-gray-200', 'border-primary-100');
                row.classList.add('bg-primary-50/10');
                
                if (!row.querySelector('.fa-trash-can')) {
                    const deleteBtnHtml = `
                        <button type="button" onclick="deleteItem('${data.detail_id_hash}')" class="h-[40px] w-[40px] flex items-center justify-center text-red-400 hover:text-red-600 transition-colors">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    `;
                    const xBtn = row.querySelector('.fa-xmark')?.parentElement;
                    if (xBtn) xBtn.remove();
                    row.insertAdjacentHTML('beforeend', deleteBtnHtml);
                }

                table.ajax.reload(null, false);
                if (data.stats && window.updateStatsCard) window.updateStatsCard(data.stats);
            } else {
                Swal.fire('Error', data.message || 'Failed to save.', 'error');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        });
    }

    function deleteItem(detailHash) {
        Swal.fire({
            title: 'Delete Entry?',
            text: "Are you sure you want to remove this record?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const url = `{{ route('inventory.sto.deleteDetail', [$stoEvent->hash_id, ':detailHash']) }}`.replace(':detailHash', detailHash);
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        _method: 'DELETE'
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: data.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        
                        // Reload table and update stats
                        if (typeof table !== 'undefined') table.ajax.reload(null, false);
                        if (data.stats && window.updateStatsCard) window.updateStatsCard(data.stats);
                        
                        if (currentHashId && currentHashId.value) {
                            fetchStoInfo(currentHashId.value);
                        }
                    } else {
                        Swal.fire('Error', data.message || 'Failed to delete.', 'error');
                    }
                });
            }
        });
    }

    function processShowResult(data) {
        const area = document.getElementById('scanResultArea');
        area.classList.remove('hidden');
        
        errorArea.classList.add('hidden');
        resPartName.innerText = data.part_name;
        resPartNo.innerText = data.part_no;
        resUnit.innerText = data.unit || 'PCS';
        resSystemQty.innerText = (data.system_qty || 0).toLocaleString();
        
        // Update entries count display
        const entriesCountEl = document.getElementById('resEntriesCount');
        if (entriesCountEl) {
            entriesCountEl.innerText = data.existing_entries ? data.existing_entries.length : 0;
        }

        currentHashId.value = data.product_id_hash;
    }

    function showError(msg) {
        errorMsg.innerText = msg;
        errorArea.classList.remove('hidden');
        resultArea.classList.add('hidden');
    }

    window.editFromTable = function(productHash, detailHash) {
        $('#product_detail_id').val(productHash).trigger('change.select2');
        fetchStoInfo(productHash);
    }

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

        // Handle Session Alerts
        @if(session('error'))
            if (window.toast) window.toast('error', 'Action Failed', '{{ session('error') }}');
            else if (window.Swal) Swal.fire('Error', '{{ session('error') }}', 'error');
            else alert('{{ session('error') }}');
        @endif

        @if(session('success'))
            if (window.toast) window.toast('success', 'Success', '{{ session('success') }}');
        @endif
    });
</script>

@endpush
@endsection

 
@push('styles')
<style>
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
    .swal2-html-container-tight {
        margin: 1em 0 0 !important;
        padding: 0 1.25em !important;
    }
    .swal2-popup, .swal2-confirm, .swal2-cancel, .swal2-deny {
        border-radius: 0.125rem !important;
    }
</style>
@endpush
