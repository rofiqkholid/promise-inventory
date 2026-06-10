@extends('layouts.app')

@section('title', 'STO Event Details')

@section('content')
<div class="text-gray-900 dark:text-gray-100">

    {{-- High-Density Unified Header Control Bar --}}
    <div class="mb-6 bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 rounded-xs shadow-xs p-4">
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-5">
            
            <!-- Left Section: Title, Status, Metadata -->
            <div class="flex-1 min-w-0">
                <!-- Row 1: Back, Title, Status, Item Count -->
                <div class="flex items-center gap-3.5 mb-2">
                    <a href="{{ route('inventory.tool.sto.index') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full border border-slate-200 dark:border-gray-800 text-slate-500 dark:text-gray-400 hover:text-primary-600 hover:bg-slate-50 dark:hover:bg-gray-800 hover:border-primary-500 dark:hover:border-primary-500 transition-all shadow-2xs" title="Back to Monitor">
                        <i class="fa-solid fa-arrow-left text-[13px]"></i>
                    </a>
                    <h2 class="text-lg lg:text-xl font-black text-gray-900 dark:text-white tracking-tight truncate font-mono">{{ $event->code }}</h2>
                    
                    @php
                        $statusCls = match($event->status) {
                            'draft'     => 'bg-slate-50 text-slate-600 border border-slate-200 dark:bg-slate-900/40 dark:text-slate-400 dark:border-slate-800',
                            'submitted' => 'bg-blue-50 text-blue-600 border border-blue-200 dark:bg-blue-900/40 dark:text-blue-400 dark:border-blue-800',
                            'approved'  => 'bg-emerald-50 text-emerald-600 border border-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-400 dark:border-emerald-800',
                            'rejected'  => 'bg-rose-50 text-rose-600 border border-rose-200 dark:bg-rose-900/40 dark:text-rose-400 dark:border-rose-800',
                            default     => 'bg-slate-50 text-slate-600 border border-slate-200 dark:bg-slate-900/40'
                        };
                    @endphp
                    <span class="px-2 py-0.5 text-[10px] font-medium uppercase rounded-xs tracking-widest {{ $statusCls }}">
                        {{ $event->status }}
                    </span>
                    <span class="px-2 py-0.5 text-[10px] font-medium uppercase rounded-xs bg-slate-50 text-slate-500 border border-slate-200 dark:bg-gray-800 dark:text-slate-400 dark:border-gray-700 tracking-wider hidden sm:inline-block">
                        {{ $event->fastDetails->count() + $event->slowDetails->count() }} Items
                    </span>
                </div>
                
                <!-- Row 2: Metadata / Overview details -->
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 text-xs text-slate-500 dark:text-slate-450">
                    <div class="flex items-center gap-1.5">
                        <span class="uppercase font-medium text-slate-400 dark:text-slate-500 text-[10px] tracking-wider">Auditor:</span>
                        <span class="text-gray-700 dark:text-gray-350 font-medium">{{ $event->creator?->name }}</span>
                    </div>
                    <div class="w-[1px] h-3.5 bg-slate-250 dark:bg-gray-750"></div>
                    <div class="flex items-center gap-1.5">
                        <span class="uppercase font-medium text-slate-400 dark:text-slate-500 text-[10px] tracking-wider">Created:</span>
                        <span class="text-gray-700 dark:text-gray-350 font-mono font-medium">{{ $event->created_at->format('d M Y H:i') }}</span>
                    </div>
                    @if($event->approver)
                    <div class="w-[1px] h-3.5 bg-slate-250 dark:bg-gray-750"></div>
                    <div class="flex items-center gap-1.5">
                        <span class="uppercase font-medium text-slate-400 dark:text-slate-500 text-[10px] tracking-wider">Approver:</span>
                        <span class="text-gray-700 dark:text-gray-350 font-medium">{{ $event->approver->name }}</span>
                    </div>
                    @endif
                    
                    @if($event->description || $event->rejection_note)
                        <div class="w-[1px] h-3.5 bg-slate-250 dark:bg-gray-750 hidden md:block"></div>
                        <div class="flex items-center gap-2 w-full md:w-auto mt-1 md:mt-0">
                            @if($event->description)
                                <span class="italic text-slate-400 dark:text-slate-500 truncate max-w-[200px] text-xs" title="{{ $event->description }}"><i class="fa-solid fa-info-circle mr-1 text-[11px]"></i>{{ $event->description }}</span>
                            @endif
                            @if($event->rejection_note)
                                <span class="text-rose-600 dark:text-rose-400 font-medium truncate max-w-[200px] text-xs" title="{{ $event->rejection_note }}"><i class="fa-solid fa-triangle-exclamation mr-1 text-[11px]"></i>{{ $event->rejection_note }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Middle Section: Stepper -->
            <div class="hidden xl:flex items-center justify-center min-w-[280px] max-w-[320px] px-6 border-x border-slate-100 dark:border-gray-850 self-stretch">
                <div class="flex items-start justify-between w-full relative">
                    @php
                        $step = match($event->status) {
                            'draft' => 1,
                            'submitted' => 2,
                            'approved', 'rejected' => 3,
                            default => 1
                        };
                    @endphp
                    
                    <!-- Line connecting steps (Absolute position) -->
                    <div class="absolute left-[36px] right-[36px] top-[12px] h-[2px] bg-slate-100 dark:bg-gray-800 -z-0">
                        <div class="h-full bg-primary-600 transition-all duration-500" style="width: {{ $step == 1 ? '0%' : ($step == 2 ? '50%' : '100%') }}"></div>
                    </div>

                    <!-- Step 1 -->
                    <div class="flex flex-col items-center z-10 relative">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center border-2 font-medium text-[10px] transition-all duration-300 {{ $step >= 1 ? 'bg-primary-600 border-primary-600 text-white shadow-xs' : 'bg-white dark:bg-gray-900 border-slate-250 dark:border-gray-700 text-slate-400' }}">
                            @if($step > 1) <i class="fa-solid fa-check text-[10px]"></i> @else 1 @endif
                        </div>
                        <span class="text-[10px] uppercase tracking-widest font-medium mt-2 {{ $step >= 1 ? 'text-primary-600' : 'text-slate-400' }}">Draft</span>
                    </div>

                    <!-- Step 2 -->
                    <div class="flex flex-col items-center z-10 relative">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center border-2 font-medium text-[10px] transition-all duration-300 {{ $step >= 2 ? 'bg-primary-600 border-primary-600 text-white shadow-xs' : 'bg-white dark:bg-gray-900 border-slate-250 dark:border-gray-700 text-slate-400' }}">
                            @if($step > 2) <i class="fa-solid fa-check text-[10px]"></i> @else 2 @endif
                        </div>
                        <span class="text-[10px] uppercase tracking-widest font-medium mt-2 {{ $step >= 2 ? 'text-primary-600' : 'text-slate-400' }}">Verify</span>
                    </div>

                    <!-- Step 3 -->
                    <div class="flex flex-col items-center z-10 relative">
                        @if($event->status === 'rejected')
                            <div class="w-6 h-6 rounded-full flex items-center justify-center border-2 font-medium text-[10px] bg-rose-600 border-rose-600 text-white shadow-xs">
                                <i class="fa-solid fa-xmark text-[10px]"></i>
                            </div>
                            <span class="text-[10px] uppercase tracking-widest font-medium mt-2 text-rose-600">Reject</span>
                        @else
                            <div class="w-6 h-6 rounded-full flex items-center justify-center border-2 font-medium text-[10px] transition-all duration-300 {{ $step >= 3 ? 'bg-emerald-600 border-emerald-600 text-white shadow-xs' : 'bg-white dark:bg-gray-900 border-slate-250 dark:border-gray-700 text-slate-400' }}">
                                @if($step == 3) <i class="fa-solid fa-lock text-[10px]"></i> @else 3 @endif
                            </div>
                            <span class="text-[10px] uppercase tracking-widest font-medium mt-2 {{ $step >= 3 ? 'text-emerald-600' : 'text-slate-400' }}">Closed</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Section: Action Buttons -->
            <div class="flex items-center gap-2 flex-shrink-0">
                @if($event->status === 'draft' && ((Auth::user()->hasMenuPermission('inventory.tool.sto.index', 'create') && !Auth::user()->hasMenuPermission('inventory.tool.sto.index', 'delete')) || Auth::user()->hasRole('admin')))
                    <button type="button" id="btnSubmitSTO" class="inline-flex items-center justify-center gap-1.5 px-4 h-9 bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium rounded-xs transition-all active:scale-[0.98] shadow-xs uppercase tracking-wider">
                        <i class="fa-solid fa-paper-plane text-xs"></i> Submit for Approval
                    </button>
                @endif

                @if($event->status === 'submitted' && Auth::user()->hasMenuPermission('inventory.tool.sto.index', 'delete'))
                    <button type="button" id="btnApproveSTO" class="inline-flex items-center justify-center gap-1.5 px-4 h-9 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-xs transition-all active:scale-[0.98] shadow-xs uppercase tracking-wider">
                        <i class="fa-solid fa-check-double text-xs"></i> Approve
                    </button>
                    <button type="button" id="btnRejectSTO" class="inline-flex items-center justify-center gap-1.5 px-4 h-9 bg-rose-650 hover:bg-rose-750 text-white text-xs font-medium rounded-xs transition-all active:scale-[0.98] shadow-xs uppercase tracking-wider">
                        <i class="fa-solid fa-ban text-xs"></i> Reject
                    </button>
                @endif

                @if(in_array($event->status, ['approved', 'submitted']) && Auth::user()->hasMenuPermission('inventory.tool.sto.index', 'delete'))
                    <button type="button" id="btnReopenSTO" class="inline-flex items-center justify-center gap-1.5 px-4 h-9 bg-amber-600 hover:bg-amber-700 text-white text-xs font-medium rounded-xs transition-all active:scale-[0.98] shadow-xs uppercase tracking-wider">
                        <i class="fa-solid fa-rotate-left text-xs"></i> Re-open STO
                    </button>
                @endif
            </div>
            
        </div>
    </div>

    {{-- Main Section: Full Width Details Tables --}}
    <div class="space-y-6 mb-8">
        
        {{-- Tabs Navigation Bar --}}
        <div class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 rounded-xs overflow-hidden shadow-2xs">
            <div class="flex flex-wrap items-center justify-between border-b border-slate-100 dark:border-gray-800">
                <div class="flex flex-1 flex-row">
                    <button type="button" 
                        class="tab-btn flex-1 py-4 px-6 inline-flex items-center justify-center border-b-2 font-medium text-xs uppercase tracking-wider transition-all duration-200 outline-none border-primary-600 text-primary-600 dark:text-primary-400 bg-slate-50/50 dark:bg-gray-800/10"
                        data-target="#tab-content-fast"
                        id="btn-tab-fast">
                        <i class="fa-solid fa-bolt mr-2 text-primary-500"></i>
                        Fast Moving
                        <span class="ml-2 px-2 py-0.5 rounded-full bg-primary-50 text-primary-600 border border-primary-100 dark:bg-primary-950/20 dark:text-primary-400 dark:border-primary-900/40 text-[10px] font-medium">
                            {{ $event->fastDetails->count() }}
                        </span>
                    </button>
                    <button type="button" 
                        class="tab-btn flex-1 py-4 px-6 inline-flex items-center justify-center border-b-2 font-medium text-xs uppercase tracking-wider transition-all duration-200 outline-none border-transparent text-slate-400 dark:text-slate-500 font-medium hover:text-slate-700 dark:hover:text-slate-350 hover:bg-slate-50/20"
                        data-target="#tab-content-slow"
                        id="btn-tab-slow">
                        <i class="fa-solid fa-clock-rotate-left mr-2 text-emerald-500"></i>
                        Slow Moving
                        <span class="ml-2 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/40 text-[10px] font-medium">
                            {{ $event->slowDetails->count() }}
                        </span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Tabs Content Wrapper --}}
        <div class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 rounded-xs shadow-2xs p-6">
            
            {{-- Tab Pane: Fast Moving --}}
            <div id="tab-content-fast" class="tab-pane">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fa-solid fa-bolt text-primary-500"></i> Fast Moving Items
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">List of counted fast-moving tools in this event.</p>
                    </div>
                    @if($event->status === 'draft')
                    <div class="flex-shrink-0">
                        <button type="button" id="btnAddFastAction" onclick="showMdl('modal-add-fast')" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium rounded-xs transition-all active:scale-[0.98] shadow-xs uppercase tracking-wider">
                            <i class="fa-solid fa-plus text-xs"></i> Add Fast Moving
                        </button>
                    </div>
                    @endif
                </div>

                <x-table id="tbl-fast-details">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-gray-800/50 text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="p-4 rounded-tl-xs">Tool Name</th>
                            <th class="p-4">Location</th>
                            <th class="p-4 text-center">Sys Qty</th>
                            <th class="p-4 text-center">Phys Qty</th>
                            <th class="p-4 text-center">Diff</th>
                            <th class="p-4 text-right">Value (IDR)</th>
                            <th class="p-4">Note</th>
                            @if($event->status === 'draft')
                            <th class="p-4 text-right rounded-tr-xs w-[110px]">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-gray-800 text-sm">
                        @foreach($event->fastDetails as $item)
                        <tr class="hover:bg-slate-50/20 dark:hover:bg-gray-800/10 transition-colors">
                            <td class="p-4">
                                <div class="font-medium text-gray-900 dark:text-white text-sm">{{ $item->tool?->name }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-450 mt-0.5">{{ $item->tool?->brand }}</div>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-xs text-xs font-medium bg-slate-50 text-slate-650 border border-slate-200 dark:bg-gray-800/60 dark:text-gray-300 dark:border-gray-700 font-mono">
                                    {{ $item->location?->code }}
                                </span>
                            </td>
                            <td class="p-4 text-center font-medium font-mono text-sm">{{ $item->system_qty }}</td>
                            <td class="p-4 text-center font-medium font-mono text-sm text-primary-600 dark:text-primary-400 bg-primary-50/20 dark:bg-primary-950/10">{{ $item->physical_qty }}</td>
                            <td class="p-4 text-center font-medium font-mono text-sm">
                                @php $diff = $item->physical_qty - $item->system_qty; @endphp
                                @if($diff > 0)
                                    <span class="text-emerald-600 bg-emerald-50 dark:bg-emerald-950/20 px-1.5 py-0.5 rounded-xs">+{{ $diff }}</span>
                                @elseif($diff < 0)
                                    <span class="text-rose-600 bg-rose-50 dark:bg-rose-950/20 px-1.5 py-0.5 rounded-xs">{{ $diff }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="p-4 text-right font-medium font-mono text-xs">
                                <div class="text-gray-900 dark:text-white">Rp {{ number_format($item->tool?->price_per_unit * $item->physical_qty, 0, ',', '.') }}</div>
                                <div class="text-[10px] text-slate-450 dark:text-slate-500 mt-0.5" title="Price per Unit">
                                    Unit: Rp {{ number_format($item->tool?->price_per_unit, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="p-4 text-slate-650 dark:text-slate-350 italic max-w-xs truncate text-xs" title="{{ $item->note }}">{{ $item->note ?: '-' }}</td>
                            @if($event->status === 'draft')
                            <td class="p-4 text-right">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <button type="button" 
                                        class="edit-fast bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-600 dark:text-gray-350 hover:bg-slate-100 dark:hover:bg-gray-700 hover:text-slate-900 dark:hover:text-white w-8 h-8 flex items-center justify-center rounded-xs transition-all shadow-3xs"
                                        data-id="{{ $item->id }}"
                                        data-tool-id="{{ $item->tool_id }}"
                                        data-location-id="{{ $item->location_id }}"
                                        data-physical-qty="{{ $item->physical_qty }}"
                                        data-note="{{ $item->note }}"
                                        title="Edit Item"
                                    >
                                        <i class="fa-solid fa-pencil text-[10px]"></i>
                                    </button>
                                    <button type="button" 
                                        class="delete-fast bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/40 hover:text-rose-700 dark:hover:text-rose-300 w-8 h-8 flex items-center justify-center rounded-xs transition-all shadow-3xs"
                                        data-id="{{ $item->id }}"
                                        title="Hapus Item"
                                    >
                                        <i class="fa-solid fa-trash-can text-[10px]"></i>
                                    </button>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </x-table>
            </div>

            {{-- Tab Pane: Slow Moving --}}
            <div id="tab-content-slow" class="tab-pane hidden">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-emerald-500"></i> Slow Moving Items
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">List of audited slow-moving assets / batches in this event.</p>
                    </div>
                    @if($event->status === 'draft')
                    <div class="flex-shrink-0">
                        <button type="button" id="btnAddSlowAction" onclick="showMdl('modal-add-slow')" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-xs transition-all active:scale-[0.98] shadow-xs uppercase tracking-wider">
                            <i class="fa-solid fa-plus text-xs"></i> Add Slow Moving
                        </button>
                    </div>
                    @endif
                </div>

                <x-table id="tbl-slow-details">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-gray-800/50 text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="p-4 rounded-tl-xs">Tool Name / ID Number</th>
                            <th class="p-4">Brand & Specification</th>
                            <th class="p-4">Location</th>
                            <th class="p-4 text-center">Status STO</th>
                            <th class="p-4 text-center">Rate (%)</th>
                            <th class="p-4 text-right">Value (IDR)</th>
                            <th class="p-4">Note</th>
                            @if($event->status === 'draft')
                            <th class="p-4 text-right rounded-tr-xs w-[110px]">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-gray-800 text-sm">
                        @foreach($event->slowDetails as $item)
                        <tr class="hover:bg-slate-50/20 dark:hover:bg-gray-800/10 transition-colors">
                            <td class="p-4">
                                <div class="font-medium text-gray-900 dark:text-white text-sm">{{ $item->batch?->tool?->name }}</div>
                                <div class="font-mono text-[11px] text-slate-550 dark:text-slate-450 mt-0.5">{{ $item->batch?->id_number }}</div>
                            </td>
                            <td class="p-4">
                                <div class="text-gray-850 dark:text-gray-200 font-medium text-[13px]">{{ $item->batch?->tool?->brand }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-450 mt-0.5">{{ $item->batch?->tool?->specification }}</div>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-xs text-xs font-medium bg-slate-50 text-slate-650 border border-slate-200 dark:bg-gray-800/60 dark:text-gray-300 dark:border-gray-700 font-mono">
                                    {{ $item->batch?->location?->code }}
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                @if($item->physical_check === 'ok')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xs text-xs font-medium uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/40">
                                        <i class="fa-solid fa-circle-check text-xs"></i> OK
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xs text-xs font-medium uppercase bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/20 dark:text-rose-400 dark:border-rose-900/40">
                                        <i class="fa-solid fa-circle-xmark text-xs"></i> NOK
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-center font-medium font-mono text-sm">{{ $item->physical_rate }}%</td>
                            <td class="p-4 text-right font-medium font-mono text-xs">
                                <div class="text-gray-900 dark:text-white">Rp {{ number_format($item->remaining_value, 0, ',', '.') }}</div>
                                <div class="text-[10px] text-slate-450 dark:text-slate-500 mt-0.5" title="Purchase Price">
                                    Base: Rp {{ number_format($item->batch?->purchase_price, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="p-4 text-slate-650 dark:text-slate-350 italic max-w-xs truncate text-xs" title="{{ $item->note }}">{{ $item->note ?: '-' }}</td>
                            @if($event->status === 'draft')
                            <td class="p-4 text-right">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <button type="button" 
                                        class="edit-slow bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-600 dark:text-gray-350 hover:bg-slate-100 dark:hover:bg-gray-700 hover:text-slate-900 dark:hover:text-white w-8 h-8 flex items-center justify-center rounded-xs transition-all shadow-3xs"
                                        data-id="{{ $item->id }}"
                                        data-batch-id="{{ $item->batch_id }}"
                                        data-physical-check="{{ $item->physical_check }}"
                                        data-physical-rate="{{ $item->physical_rate }}"
                                        data-note="{{ $item->note }}"
                                        title="Edit Asset"
                                    >
                                        <i class="fa-solid fa-pencil text-[10px]"></i>
                                    </button>
                                    <button type="button" 
                                        class="delete-slow bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/40 hover:text-rose-700 dark:hover:text-rose-300 w-8 h-8 flex items-center justify-center rounded-xs transition-all shadow-3xs"
                                        data-id="{{ $item->id }}"
                                        title="Hapus Asset"
                                    >
                                        <i class="fa-solid fa-trash-can text-[10px]"></i>
                                    </button>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </x-table>
            </div>

        </div>
    </div>
</div>

{{-- Modal: Add Fast --}}
<div id="modal-add-fast" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4">
    <div class="relative w-full max-w-md transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 shadow-2xl transition-all">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">Add Fast Moving Item</h3>
            <button class="close-modal text-gray-400 hover:text-gray-500 w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100 dark:hover:bg-gray-800"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="formAddFast" class="p-6">
            @csrf
            <input type="hidden" name="item_id" id="fastItemId" value="">
            <div class="space-y-4">
                <div>
                    <label class="block mb-1.5 text-[10px] font-medium text-slate-650 dark:text-gray-300 uppercase tracking-wider">Tool</label>
                    <select name="tool_id" id="fastToolId" required class="select2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all">
                        <option value="">Select Tool</option>
                        @foreach($fastTools as $t)
                            @php
                                $locId = $t->fastStock->first()?->location_id;
                                $key = $t->id . '-' . $locId;
                                $isCounted = in_array($key, $countedFastKeys);
                            @endphp
                            <option value="{{ $t->id }}" data-location-id="{{ $locId }}"
                                @if($isCounted) disabled data-counted="true" @endif>
                                {{ $t->name }} ({{ $t->brand }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-1.5 text-[10px] font-medium text-slate-650 dark:text-gray-300 uppercase tracking-wider">Location</label>
                    <select name="location_id" id="fastLocationId" required disabled class="select2 bg-slate-50 dark:bg-gray-800/80 cursor-not-allowed opacity-75 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all">
                        <option value="">Select Location</option>
                        @foreach($locations as $l)
                            <option value="{{ $l->id }}">{{ $l->code }} — {{ $l->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Live Stock Check & Difference Preview (Horizontal Bar) -->
                <div id="fastStockCheckContainer" class="hidden p-3 bg-slate-50 dark:bg-gray-800/40 border border-slate-200 dark:border-gray-800 rounded-xs text-[10px] flex justify-between items-center gap-4">
                    <div>
                        <span class="font-medium text-slate-400 dark:text-slate-500 uppercase tracking-wider text-[8px]">System Stock Level</span>
                        <span id="fastSystemQty" class="block font-medium text-slate-800 dark:text-slate-200 text-xs">0</span>
                    </div>
                    <div class="text-right">
                        <span class="font-medium text-slate-400 dark:text-slate-500 uppercase tracking-wider text-[8px]">Discrepancy (Diff)</span>
                        <span id="fastDiffPreview" class="block font-medium text-xs">0</span>
                    </div>
                </div>

                <div>
                    <label class="block mb-1.5 text-[10px] font-medium text-slate-650 dark:text-gray-300 uppercase tracking-wider">Physical Quantity</label>
                    <input type="number" name="physical_qty" id="fastPhysicalQty" required min="0" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all">
                </div>
                <div>
                    <label class="block mb-1.5 text-[10px] font-medium text-slate-650 dark:text-gray-300 uppercase tracking-wider">Note</label>
                    <textarea name="note" rows="2" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all" placeholder="Enter STO check notes..."></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" class="close-modal px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-[10px] font-medium text-gray-650 dark:text-gray-400 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-[10px] font-medium text-white uppercase tracking-widest active:scale-[0.98] transition-all">Add to List</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Add Slow --}}
<div id="modal-add-slow" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4">
    <div class="relative w-full max-w-md transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 shadow-2xl transition-all">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-sm font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">Add Slow Moving Batch</h3>
            <button class="close-modal text-gray-400 hover:text-gray-500 w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100 dark:hover:bg-gray-800"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="formAddSlow" class="p-6">
            @csrf
            <input type="hidden" name="item_id" id="slowItemId" value="">
            <div class="space-y-4">
                <div>
                    <label class="block mb-1.5 text-[10px] font-medium text-slate-650 dark:text-gray-300 uppercase tracking-wider">Asset (ID Number)</label>
                    <select name="batch_id" required class="select2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all">
                        <option value="">Select Asset</option>
                        @foreach($slowBatches as $b)
                            @php
                                $isCounted = in_array($b->id, $countedSlowBatchIds);
                            @endphp
                            <option value="{{ $b->id }}" 
                                data-tool-id="{{ $b->tool_id }}"
                                data-rate="{{ $b->physical_rate }}"
                                data-price="{{ $b->purchase_price }}"
                                data-lifetime="{{ $b->std_lifetime_yrs }}"
                                data-purchase-date="{{ $b->purchase_date->format('Y-m-d') }}"
                                data-location="{{ $b->location?->name ?? '-' }}"
                                @if($isCounted) disabled data-counted="true" @endif
                            >
                                {{ $b->id_number }} — {{ $b->tool->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Asset Details Card (Compact 3-Column Grid) -->
                <div id="slowDetailsCard" class="hidden p-3 bg-slate-50 dark:bg-gray-800/40 border border-slate-200 dark:border-gray-800 rounded-xs text-[10px]">
                    <div class="grid grid-cols-3 gap-2.5">
                        <div>
                            <span class="text-slate-400 dark:text-slate-500 block uppercase tracking-wider font-medium text-[8px] mb-0.5">Location</span>
                            <span id="slowLoc" class="font-medium text-slate-800 dark:text-slate-200 text-[10px] block truncate"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 dark:text-slate-500 block uppercase tracking-wider font-medium text-[8px] mb-0.5">Purchase Date</span>
                            <span id="slowPurchaseDate" class="font-medium text-slate-800 dark:text-slate-200 text-[10px] block"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 dark:text-slate-500 block uppercase tracking-wider font-medium text-[8px] mb-0.5">Std. Lifetime</span>
                            <span id="slowLifetime" class="font-medium text-slate-800 dark:text-slate-200 text-[10px] block"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 dark:text-slate-500 block uppercase tracking-wider font-medium text-[8px] mb-0.5">Price</span>
                            <span id="slowPrice" class="font-medium text-slate-800 dark:text-slate-200 text-[10px] block"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 dark:text-slate-500 block uppercase tracking-wider font-medium text-[8px] mb-0.5">Reg. Rate</span>
                            <span id="slowRate" class="font-medium text-slate-800 dark:text-slate-200 text-[10px] block"></span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1.5 text-[10px] font-medium text-slate-650 dark:text-gray-300 uppercase tracking-wider">Check Result</label>
                        <select name="physical_check" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all">
                            <option value="ok">OK</option>
                            <option value="nok">NOK (Retired)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1.5 text-[10px] font-medium text-slate-650 dark:text-gray-300 uppercase tracking-wider">Physical Rate (%)</label>
                        <select name="physical_rate" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all">
                            <option value="100">100% — Ok</option>
                            <option value="75">75% — Good</option>
                            <option value="50">50% — Still good</option>
                            <option value="25">25% — Warning</option>
                            <option value="0">0% — Retired</option>
                        </select>
                    </div>
                </div>

                <!-- Asset Remaining Value Preview (Horizontal Bar) -->
                <div id="slowPreviewCard" class="hidden p-3 bg-emerald-50/40 dark:bg-emerald-950/10 border border-emerald-100 dark:border-emerald-900/40 rounded-xs text-[10px] flex justify-between items-center gap-4">
                    <div class="flex items-center gap-4">
                        <div>
                            <span class="text-emerald-700/60 dark:text-emerald-400/60 block uppercase tracking-wider font-medium text-[8px] mb-0.5">Calculated Age</span>
                            <span id="slowAgePreview" class="font-medium text-emerald-900 dark:text-emerald-200">0 Yrs</span>
                        </div>
                        <div class="border-l border-emerald-100 dark:border-emerald-900/40 pl-4">
                            <span class="text-emerald-700/60 dark:text-emerald-400/60 block uppercase tracking-wider font-medium text-[8px] mb-0.5">Depreciation</span>
                            <span id="slowDepPreview" class="font-medium text-emerald-900 dark:text-emerald-200">100%</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-emerald-700/60 dark:text-emerald-400/60 block uppercase tracking-wider font-medium text-[8px] mb-0.5">New Remaining Value</span>
                        <span id="slowValPreview" class="text-base font-black text-emerald-600 dark:text-emerald-400">Rp 0</span>
                    </div>
                </div>

                <div>
                    <label class="block mb-1.5 text-[10px] font-medium text-slate-650 dark:text-gray-300 uppercase tracking-wider">Note / Reason</label>
                    <textarea name="note" rows="2" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all" placeholder="Enter STO check notes..."></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" class="close-modal px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-[10px] font-medium text-gray-650 dark:text-gray-400 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-[10px] font-medium text-white uppercase tracking-widest active:scale-[0.98] transition-all">Add to List</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(function() {
        const idr = (v) => 'Rp ' + parseFloat(v || 0).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });

        // Custom Select2 template with checkmarks for counted tools & batches
        const select2Options = {
            width: '100%',
            dropdownAutoWidth: true,
            placeholder: 'Select an option',
            allowClear: true,
            templateResult: function(data) {
                if (!data.id) return data.text;
                const $opt = $(data.element);
                const isCounted = $opt.data('counted') === true || $opt.data('counted') === 'true';

                if (isCounted) {
                    return $(`
                        <div class="flex items-center justify-between gap-2 opacity-65">
                            <span class="flex items-center gap-2 overflow-hidden text-gray-400 dark:text-gray-500">
                                <i class="fa-solid fa-circle-check text-emerald-500 shrink-0"></i>
                                <span class="truncate text-xs">${data.text}</span>
                            </span>
                            <span class="text-[8px] bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 px-1.5 py-0.5 rounded-full font-bold uppercase shrink-0">Counted</span>
                        </div>
                    `);
                }
                return data.text;
            }
        };

        // Initialize custom select2 elements with their modals as dropdown parent
        $('#fastToolId').select2($.extend({}, select2Options, { dropdownParent: $('#modal-add-fast') }));
        $('select[name="batch_id"]', '#modal-add-slow').select2($.extend({}, select2Options, { dropdownParent: $('#modal-add-slow') }));

        // --- Fast Moving Tool STO Logic ---
        function updateFastStockInfo() {
            const toolSelect = $('#fastToolId');
            const locSelect = $('#fastLocationId');
            const selectedOpt = toolSelect.find('option:selected');
            const locId = selectedOpt.data('location-id');

            if (locId) {
                locSelect.val(locId).trigger('change');
                locSelect.addClass('bg-slate-50 dark:bg-gray-800/80 cursor-not-allowed opacity-75').prop('disabled', true);
                
                // Fetch dynamic stock level
                const toolId = toolSelect.val();
                $.ajax({
                    url: "{{ route('inventory.tool.sto.getCurrentStock') }}",
                    method: 'GET',
                    data: { tool_id: toolId, location_id: locId },
                    success: function(res) {
                        $('#fastSystemQty').text(res.system_qty);
                        $('#fastStockCheckContainer').removeClass('hidden');
                        calculateFastDiff();
                    },
                    error: function() {
                        $('#fastSystemQty').text('0');
                        $('#fastStockCheckContainer').addClass('hidden');
                    }
                });
            } else {
                locSelect.val('').trigger('change');
                locSelect.addClass('bg-slate-50 dark:bg-gray-800/80 cursor-not-allowed opacity-75').prop('disabled', true);
                $('#fastStockCheckContainer').addClass('hidden');
            }
        }

        function calculateFastDiff() {
            const systemQty = parseFloat($('#fastSystemQty').text()) || 0;
            const physicalQty = parseFloat($('#fastPhysicalQty').val()) || 0;
            const diff = physicalQty - systemQty;
            
            const diffText = (diff > 0 ? '+' : '') + diff;
            const diffElement = $('#fastDiffPreview');
            
            diffElement.text(diffText);
            diffElement.removeClass('text-emerald-600 text-red-600 text-gray-500');
            if (diff > 0) {
                diffElement.addClass('text-emerald-600');
            } else if (diff < 0) {
                diffElement.addClass('text-red-600');
            } else {
                diffElement.addClass('text-gray-500');
            }
        }

        $('#fastToolId').on('change', updateFastStockInfo);
        $('#fastLocationId').on('change', function() {
            const toolId = $('#fastToolId').val();
            const locId = $(this).val();
            if (toolId && locId) {
                $.ajax({
                    url: "{{ route('inventory.tool.sto.getCurrentStock') }}",
                    method: 'GET',
                    data: { tool_id: toolId, location_id: locId },
                    success: function(res) {
                        $('#fastSystemQty').text(res.system_qty);
                        $('#fastStockCheckContainer').removeClass('hidden');
                        calculateFastDiff();
                    }
                });
            } else {
                $('#fastStockCheckContainer').addClass('hidden');
            }
        });
        $('#fastPhysicalQty').on('input', calculateFastDiff);

        // Form Add Fast Submit
        $('#formAddFast').on('submit', function(e) {
            e.preventDefault();
            const selectLocation = $('#fastLocationId');
            const selectTool = $('#fastToolId');
            const wasLocDisabled = selectLocation.prop('disabled');
            const wasToolDisabled = selectTool.prop('disabled');

            if (wasLocDisabled) selectLocation.prop('disabled', false);
            if (wasToolDisabled) selectTool.prop('disabled', false);

            const formData = $(this).serialize();

            if (wasLocDisabled) selectLocation.prop('disabled', true);
            if (wasToolDisabled) selectTool.prop('disabled', true);

            const btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Processing...');

            $.ajax({
                url: "{{ route('inventory.tool.sto.addItemFast', $event->id) }}",
                type: "POST",
                data: formData,
                success: (res) => { window.showToast(res.message, 'success'); window.location.reload(); },
                error: (err) => { 
                    window.showToast(err.responseJSON?.message || 'Error', 'error'); 
                    btn.prop('disabled', false).text($('#fastItemId').val() ? 'Save Changes' : 'Add to List');
                }
            });
        });

        // --- Slow Moving Asset STO Logic ---
        function updateSlowAssetPreview() {
            const selected = $('option:selected', 'select[name="batch_id"]', '#modal-add-slow');
            if (!selected.val()) {
                $('#slowDetailsCard').addClass('hidden');
                $('#slowPreviewCard').addClass('hidden');
                return;
            }
            
            const price = parseFloat(selected.data('price')) || 0;
            const purchaseDateStr = selected.data('purchase-date');
            const lifetime = parseFloat(selected.data('lifetime')) || 1;
            const rate  = parseFloat($('select[name="physical_rate"]', '#modal-add-slow').val()) || 0;

            $('#slowLoc').text(selected.data('location') || '-');
            $('#slowPurchaseDate').text(purchaseDateStr ? new Date(purchaseDateStr).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'}) : '-');
            $('#slowPrice').text(idr(price));
            $('#slowLifetime').text(lifetime + ' Years');
            $('#slowRate').text(selected.data('rate') + '%');
            $('#slowDetailsCard').removeClass('hidden');

            let depFactor = 1;
            let ageYears = 0;
            if (purchaseDateStr) {
                const purchaseDate = new Date(purchaseDateStr);
                const today = new Date();
                const ageDays = (today - purchaseDate) / (1000 * 60 * 60 * 24);
                ageYears = Math.max(0, Math.round((ageDays / 365.25) * 100) / 100);
                const remainYrs = Math.max(0, lifetime - ageYears);
                depFactor = remainYrs / lifetime;
            }

            const total = price * depFactor * (rate / 100);
            
            $('#slowAgePreview').text(ageYears + ' Years');
            $('#slowDepPreview').text(Math.round(depFactor * 100) + '%');
            $('#slowValPreview').text(idr(total));
            $('#slowPreviewCard').removeClass('hidden');
        }

        // Form Add Slow Listeners
        $('select[name="batch_id"]', '#modal-add-slow').on('change', function() {
            const rate = $('option:selected', this).data('rate');
            if (rate !== undefined) {
                $('select[name="physical_rate"]', '#modal-add-slow').val(Math.round(rate)).trigger('change');
            } else {
                updateSlowAssetPreview();
            }
        });

        $('select[name="physical_rate"]', '#modal-add-slow').on('change', function() {
            const rate = $(this).val();
            const checkSelect = $('select[name="physical_check"]', '#modal-add-slow');
            if (rate === '0') {
                checkSelect.val('nok').trigger('change');
                checkSelect.find('option[value="ok"]').attr('disabled', true);
            } else {
                checkSelect.find('option[value="ok"]').removeAttr('disabled');
                checkSelect.val('ok').trigger('change');
            }
            updateSlowAssetPreview();
        });

        $('select[name="physical_check"]', '#modal-add-slow').on('change', updateSlowAssetPreview);

        $('#formAddSlow').on('submit', function(e) {
            e.preventDefault();
            const btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Processing...');

            $.ajax({
                url: "{{ route('inventory.tool.sto.addItemSlow', $event->id) }}",
                type: "POST",
                data: $(this).serialize(),
                success: (res) => { window.showToast(res.message, 'success'); window.location.reload(); },
                error: (err) => { 
                    window.showToast(err.responseJSON?.message || 'Error', 'error'); 
                    btn.prop('disabled', false).text($('#slowItemId').val() ? 'Save Changes' : 'Add to List');
                }
            });
        });

        // --- Edit Fast Button Handler ---
        $(document).on('click', '.edit-fast', function() {
            const btn = $(this);
            const itemId = btn.data('id');
            const toolId = btn.data('tool-id');
            const locationId = btn.data('location-id');
            const physicalQty = btn.data('physical-qty');
            const note = btn.data('note');

            const modal = $('#modal-add-fast');
            modal.find('h3').text('Edit Fast Moving Item');
            modal.find('button[type="submit"]').text('Save Changes');
            modal.find('#fastItemId').val(itemId);
            
            // Temporarily enable option during edit and set as disabled (readonly) for user
            modal.find('#fastToolId').find(`option[value="${toolId}"]`).prop('disabled', false);
            modal.find('#fastToolId').val(toolId).trigger('change');
            modal.find('#fastToolId').prop('disabled', true);
            
            // Keep location select disabled and read-only from database selection
            const locSelect = modal.find('#fastLocationId');
            locSelect.addClass('bg-slate-50 dark:bg-gray-800/80 cursor-not-allowed opacity-75').prop('disabled', true);
            locSelect.val(locationId).trigger('change');

            modal.find('#fastPhysicalQty').val(physicalQty).trigger('input');
            modal.find('textarea[name="note"]').val(note);

            modal.removeClass('hidden');
            $(document).trigger('select2:reinit', [modal]);
        });

        // --- Edit Slow Button Handler ---
        $(document).on('click', '.edit-slow', function() {
            const btn = $(this);
            const itemId = btn.data('id');
            const batchId = btn.data('batch-id');
            const physicalCheck = btn.data('physical-check');
            const physicalRate = btn.data('physical-rate');
            const note = btn.data('note');

            const modal = $('#modal-add-slow');
            modal.find('h3').text('Edit Slow Moving Asset');
            modal.find('button[type="submit"]').text('Save Changes');
            modal.find('#slowItemId').val(itemId);
            
            // Temporarily enable option during edit
            modal.find('select[name="batch_id"]').find(`option[value="${batchId}"]`).prop('disabled', false);
            modal.find('select[name="batch_id"]').val(batchId).trigger('change');
            
            modal.find('select[name="physical_check"]').val(physicalCheck).trigger('change');
            modal.find('select[name="physical_rate"]').val(Math.round(physicalRate)).trigger('change');
            modal.find('textarea[name="note"]').val(note);

            modal.removeClass('hidden');
            $(document).trigger('select2:reinit', [modal]);
        });

        // --- Delete Fast Button Handler ---
        $(document).on('click', '.delete-fast', function() {
            const itemId = $(this).data('id');
            Swal.fire({
                title: 'Hapus Item?',
                text: "Item fast moving akan dihapus dari daftar STO ini.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('/inventory/tool/sto') }}/${"{{ $event->id }}"}/item-fast/${itemId}`,
                        type: 'DELETE',
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(res) {
                            window.showToast(res.message, 'success');
                            window.location.reload();
                        },
                        error: function(err) {
                            window.showToast(err.responseJSON?.message || 'Gagal menghapus item', 'error');
                        }
                    });
                }
            });
        });

        // --- Delete Slow Button Handler ---
        $(document).on('click', '.delete-slow', function() {
            const itemId = $(this).data('id');
            Swal.fire({
                title: 'Hapus Aset?',
                text: "Aset slow moving akan dihapus dari daftar STO ini.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('/inventory/tool/sto') }}/${"{{ $event->id }}"}/item-slow/${itemId}`,
                        type: 'DELETE',
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(res) {
                            window.showToast(res.message, 'success');
                            window.location.reload();
                        },
                        error: function(err) {
                            window.showToast(err.responseJSON?.message || 'Gagal menghapus aset', 'error');
                        }
                    });
                }
            });
        });

        // Submit STO
        $('#btnSubmitSTO').on('click', function() {
            Swal.fire({
                title: 'Submit STO?',
                text: "You won't be able to edit items after submission.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Submit'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post("{{ route('inventory.tool.sto.submit', $event->id) }}", {_token: "{{ csrf_token() }}"}, function(res) {
                        window.showToast(res.message, 'success');
                        window.location.reload();
                    });
                }
            });
        });

        // Approve STO
        $('#btnApproveSTO').on('click', function() {
            Swal.fire({
                title: 'Approve STO?',
                text: "Stock levels will be updated based on physical input.",
                icon: 'success',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Approve'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post("{{ route('inventory.tool.sto.approve', $event->id) }}", {_token: "{{ csrf_token() }}"}, function(res) {
                        window.showToast(res.message, 'success');
                        window.location.reload();
                    });
                }
            });
        });

        // Reject STO
        $('#btnRejectSTO').on('click', function() {
            Swal.fire({
                title: 'Reject STO?',
                input: 'textarea',
                inputLabel: 'Rejection Reason',
                inputPlaceholder: 'Type your reason here...',
                showCancelButton: true,
                confirmButtonText: 'Reject',
                confirmButtonColor: '#ef4444'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post("{{ route('inventory.tool.sto.reject', $event->id) }}", {
                        _token: "{{ csrf_token() }}",
                        note: result.value
                    }, function(res) {
                        window.showToast(res.message, 'success');
                        window.location.reload();
                    });
                }
            });
        });

        // Reopen / Rollback STO
        $('#btnReopenSTO').on('click', function() {
            Swal.fire({
                title: 'Rollback & Re-open STO?',
                text: "Status STO akan dikembalikan ke Draft. Riwayat transaksi penyesuaian akan dihapus, dan jumlah stok serta status aset slow-moving akan dikembalikan ke kondisi awal sebelum disetujui.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d97706',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Rollback',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses Rollback...',
                        text: 'Harap tunggu sebentar.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    $.post("{{ route('inventory.tool.sto.reopen', $event->id) }}", {_token: "{{ csrf_token() }}"}, function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    }).fail(function(err) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: err.responseJSON?.message || 'Terjadi kesalahan saat memproses rollback.'
                        });
                    });
                }
            });
        });

        // Initialize DataTables
        const hasFastActions = $('#tbl-fast-details th:contains("Actions")').length > 0;
        const tblFast = window.defaultDataTable('#tbl-fast-details', {
            serverSide: false,
            processing: false,
            order: [[0, 'asc']],
            columnDefs: hasFastActions ? [{ orderable: false, targets: -1 }] : [],
            language: {
                emptyTable: `
                    <div class="py-16 flex flex-col items-center justify-center text-center w-full">
                        <div>
                            <i class="fa-solid fa-bolt text-3xl text-slate-200 dark:text-gray-650 m-4"></i>
                        </div>
                        <h4 class="text-sm font-medium text-slate-900 dark:text-white uppercase tracking-widest mb-2">No Fast Moving Items</h4>
                        <p class="text-xs text-gray-400 dark:text-gray-500 max-w-xs mx-auto font-medium leading-relaxed">Belum ada item fast moving yang tercatat.</p>
                    </div>
                `
            }
        });

        const hasSlowActions = $('#tbl-slow-details th:contains("Actions")').length > 0;
        const tblSlow = window.defaultDataTable('#tbl-slow-details', {
            serverSide: false,
            processing: false,
            order: [[0, 'asc']],
            columnDefs: hasSlowActions ? [{ orderable: false, targets: -1 }] : [],
            language: {
                emptyTable: `
                    <div class="py-16 flex flex-col items-center justify-center text-center w-full">
                        <div>
                            <i class="fa-solid fa-clock-rotate-left text-3xl text-slate-200 dark:text-gray-650 m-4"></i>
                        </div>
                        <h4 class="text-sm font-medium text-slate-900 dark:text-white uppercase tracking-widest mb-2">No Slow Moving Batches</h4>
                        <p class="text-xs text-gray-400 dark:text-gray-500 max-w-xs mx-auto font-medium leading-relaxed">Belum ada aset slow moving yang tercatat.</p>
                    </div>
                `
            }
        });

        // --- Premium Segmented Tabs Switcher ---
        $(document).on('click', '.tab-btn', function() {
            const target = $(this).data('target');
            
            // Toggle Content Panes
            $('.tab-pane').addClass('hidden');
            $(target).removeClass('hidden');
            
            // Reset all tab buttons to Inactive Style
            $('.tab-btn')
                .removeClass('border-primary-600 text-primary-600 dark:text-primary-400 font-medium bg-slate-50/50 dark:bg-gray-800/10')
                .addClass('border-transparent text-slate-400 dark:text-slate-500 font-medium hover:text-slate-700 dark:hover:text-slate-350 hover:bg-slate-50/20');
            
            // Set active clicked tab button Style
            $(this)
                .removeClass('border-transparent text-slate-400 dark:text-slate-500 font-medium hover:text-slate-700 dark:hover:text-slate-350 hover:bg-slate-50/20')
                .addClass('border-primary-600 text-primary-600 dark:text-primary-400 font-medium bg-slate-50/50 dark:bg-gray-800/10');
                
            // Adjust DataTables column sizing dynamically
            if (target === '#tab-content-slow' && typeof tblSlow !== 'undefined' && tblSlow) {
                tblSlow.columns.adjust().draw();
            } else if (target === '#tab-content-fast' && typeof tblFast !== 'undefined' && tblFast) {
                tblFast.columns.adjust().draw();
            }
                
            // Save state to localStorage
            localStorage.setItem('sto_active_tab', target);
        });

        // Retrieve and restore last active tab from localStorage (called after handler is registered)
        const activeTab = localStorage.getItem('sto_active_tab') || '#tab-content-fast';
        if ($(`.tab-btn[data-target="${activeTab}"]`).length) {
            $(`.tab-btn[data-target="${activeTab}"]`).trigger('click');
        }

        // Global Modal Logic with dynamic form resets and default labels restoration
        window.showMdl = (id) => { 
            const modal = $(`#${id}`);
            modal.removeClass('hidden'); 
            
            // Auto switch tab content for visual cohesion
            if (id === 'modal-add-fast') {
                $(`.tab-btn[data-target="#tab-content-fast"]`).trigger('click');
            } else if (id === 'modal-add-slow') {
                $(`.tab-btn[data-target="#tab-content-slow"]`).trigger('click');
            }
            
            const form = modal.find('form');
            if (form.length) {
                form[0].reset();
                form.find('input[name="item_id"]').val('');
                
                // Re-disable all options that are marked as counted
                form.find('option[data-counted="true"]').prop('disabled', true);
                
                form.find('.select2').trigger('change');
                
                // Restore default titles & buttons
                if (id === 'modal-add-fast') {
                    modal.find('h3').text('Add Fast Moving Item');
                    modal.find('button[type="submit"]').text('Add to List');
                    modal.find('#fastToolId').prop('disabled', false);
                    modal.find('#fastLocationId').addClass('bg-slate-50 dark:bg-gray-800/80 cursor-not-allowed opacity-75').prop('disabled', true);
                    modal.find('#fastStockCheckContainer').addClass('hidden');
                } else {
                    modal.find('h3').text('Add Slow Moving Batch');
                    modal.find('button[type="submit"]').text('Add to List');
                    modal.find('#slowDetailsCard').addClass('hidden');
                    modal.find('#slowPreviewCard').addClass('hidden');
                }
            }
            $(document).trigger('select2:reinit', [modal]); 
        };
        
        $(document).on('click', '.close-modal', function() { $(this).closest('.modal-container').addClass('hidden'); });

        // Auto-trigger STO modal & select tool if tool_id is passed in query parameters
        const urlParams = new URLSearchParams(window.location.search);
        const preselectedToolId = urlParams.get('tool_id');
        if (preselectedToolId) {
            // Check if fast tool option exists
            const fastOption = $(`#fastToolId option[value="${preselectedToolId}"]`);
            if (fastOption.length) {
                // Switch tab to fast
                $(`.tab-btn[data-target="#tab-content-fast"]`).trigger('click');
                // Open add fast modal
                window.showMdl('modal-add-fast');
                // Select tool
                $('#fastToolId').val(preselectedToolId).trigger('change');
            } else {
                // Otherwise, search slow batches options for this tool
                const slowOption = $(`select[name="batch_id"] option[data-tool-id="${preselectedToolId}"]`).first();
                if (slowOption.length) {
                    // Switch tab to slow
                    $(`.tab-btn[data-target="#tab-content-slow"]`).trigger('click');
                    // Open add slow modal
                    window.showMdl('modal-add-slow');
                    // Select batch
                    $('select[name="batch_id"]', '#modal-add-slow').val(slowOption.val()).trigger('change');
                }
            }
        }
    });
</script>
@endpush
