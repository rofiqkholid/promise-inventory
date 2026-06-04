@extends('layouts.app')

@section('title', 'Slow Moving Asset Register')

@section('content')
@php
    $categories = $tools->map(fn($t) => $t->category)->filter()->unique('id');
@endphp
<div class="text-gray-900 dark:text-gray-100">

    {{-- Header --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">Slow Moving Assets</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Asset register for slow moving tools (Arbor, Collet, Holder). Tracked per purchase batch with depreciation.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-2 relative items-center">
            <button type="button" id="btnToggleFilter" class="h-9 inline-flex items-center justify-center gap-2 px-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-all font-sans" title="Toggle Filters">
                <i class="fa-solid fa-filter"></i> Filters
            </button>
            
            <div class="relative">
                <button type="button" id="toggleLegend" class="h-9 inline-flex items-center justify-center gap-2 px-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-all font-sans" title="Legend & Help">
                    <i class="fa-solid fa-circle-question"></i> Legend
                </button>

                {{-- Legend Popover Content --}}
                <div id="legendPopover" class="hidden absolute right-0 top-full mt-2 w-72 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 p-6 z-50 shadow-xl">
                    <h4 class="font-bold text-gray-900 dark:text-gray-100 text-xs tracking-wider mb-3 uppercase">Condition Status</h4>
                    <div class="space-y-2.5">
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-2 flex-shrink-0"></span>
                            <div class="text-gray-600 dark:text-gray-300 font-medium">OK <span class="text-gray-400 text-[10px] tracking-tighter">(100% Rate)</span></div>
                        </div>
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 mr-2 flex-shrink-0"></span>
                            <div class="text-gray-600 dark:text-gray-300 font-medium">Good <span class="text-gray-400 text-[10px] tracking-tighter">(75% Rate)</span></div>
                        </div>
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 mr-2 flex-shrink-0"></span>
                            <div class="text-gray-600 dark:text-gray-300 font-medium">Still Good <span class="text-gray-400 text-[10px] tracking-tighter">(50% Rate)</span></div>
                        </div>
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-orange-500 mr-2 flex-shrink-0"></span>
                            <div class="text-gray-600 dark:text-gray-300 font-medium">Warning <span class="text-gray-400 text-[10px] tracking-tighter">(25% Rate)</span></div>
                        </div>
                        <div class="flex items-center text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500 mr-2 flex-shrink-0"></span>
                            <div class="text-gray-600 dark:text-gray-300 font-medium">Retired <span class="text-gray-400 text-[10px] tracking-tighter">(0% Rate or Retired)</span></div>
                        </div>
                    </div>
                </div>
            </div>

            @if(Auth::user()->hasMenuPermission('inventory.tool.slow-batch.index', 'can_create'))
            <button type="button" id="btnAddBatch" class="h-9 inline-flex items-center justify-center gap-2 px-6 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest active:scale-[0.98] transition-all font-sans">
                <i class="fa-solid fa-plus"></i> Register Asset
            </button>
            @endif
        </div>
    </div>

    {{-- Collapsible Filter Card --}}
    <div id="filterCard" class="hidden bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 p-4 mb-6 relative">
        <div class="absolute -top-2 right-44 w-0 h-0 border-l-[10px] border-l-transparent border-r-[10px] border-r-transparent border-b-[10px] border-b-slate-200 dark:border-b-gray-700"></div>
        <div class="absolute -top-[7px] right-44 w-0 h-0 border-l-[10px] border-l-transparent border-r-[10px] border-r-transparent border-b-[10px] border-b-white dark:border-b-gray-800"></div>
        
        <div class="flex flex-col xl:flex-row gap-3 xl:items-end">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 flex-1">
                <!-- Category -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Category</label>
                    <div class="w-full">
                        <select id="filter_category" class="select2 w-full">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Condition -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Condition Status</label>
                    <div class="w-full">
                        <select id="filter_condition" class="select2 w-full">
                            <option value="">All Conditions</option>
                            <option value="100">100% — OK</option>
                            <option value="75">75% — GOOD</option>
                            <option value="50">50% — STILL GOOD</option>
                            <option value="25">25% — WARNING</option>
                            <option value="0">0% — RETIRED</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex gap-2 pt-2 xl:pt-0">
                <button type="button" id="reset_filters" class="h-9 px-4 flex items-center justify-center gap-2 bg-white dark:bg-gray-800 text-slate-500 hover:text-primary-600 border border-slate-200 dark:border-gray-700 rounded-xs transition-all text-xs font-medium active:scale-95 shadow-xs">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </button>
            </div>
        </div>
    </div>

    {{-- Expanded Premium KPI Row --}}
    <div class="flex flex-nowrap gap-3 mb-6 overflow-x-auto scrollbar-hide">
        <!-- Total Active Items -->
        <div class="flex-none w-[180px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all hover:bg-slate-50/50 dark:hover:bg-gray-700/50">
            <div class="w-10 h-10 rounded-xs bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 flex items-center justify-center text-slate-500 text-lg">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 dark:text-gray-500 tracking-tight mb-1 uppercase">Active Items</div>
                <div class="text-sm font-bold text-slate-800 dark:text-white leading-none tracking-tighter" id="statTotalBatches">—</div>
            </div>
        </div>

        <!-- Total Asset Value -->
        <div class="flex-none w-[220px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all hover:bg-slate-50/50 dark:hover:bg-gray-700/50">
            <div class="w-10 h-10 rounded-xs bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-lg">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-emerald-600 dark:text-emerald-500 tracking-tight mb-1 uppercase">Asset Value</div>
                <div class="text-sm font-bold text-emerald-700 dark:text-emerald-400 leading-none tracking-tighter" id="statTotalValue">—</div>
            </div>
        </div>

        <!-- OK / Good / Still Good Condition (>= 50%) -->
        <div class="flex-none w-[180px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all hover:bg-slate-50/50 dark:hover:bg-gray-700/50">
            <div class="w-10 h-10 rounded-xs bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/50 flex items-center justify-center text-blue-600 dark:text-blue-400 text-lg">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-blue-600 dark:text-blue-500 tracking-tight mb-1 uppercase">OK/Good/Still Good</div>
                <div class="text-sm font-bold text-slate-800 dark:text-white leading-none tracking-tighter" id="statOkGood">—</div>
            </div>
        </div>

        <!-- Warning (== 25%) -->
        <div class="flex-none w-[180px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all hover:bg-slate-50/50 dark:hover:bg-gray-700/50">
            <div class="w-10 h-10 rounded-xs bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800/50 flex items-center justify-center text-amber-600 dark:text-amber-400 text-lg">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-amber-600 dark:text-amber-500 tracking-tight mb-1 uppercase">Warning (25%)</div>
                <div class="text-sm font-bold text-slate-800 dark:text-white leading-none tracking-tighter" id="statWarning">—</div>
            </div>
        </div>

        <!-- NOK / Retired (== 0% or Status != active) -->
        <div class="flex-none w-[180px] flex-grow bg-white dark:bg-gray-800 p-3.5 rounded-xs border border-slate-200 dark:border-gray-700 flex items-center gap-3 transition-all hover:bg-slate-50/50 dark:hover:bg-gray-700/50">
            <div class="w-10 h-10 rounded-xs bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800/50 flex items-center justify-center text-rose-600 dark:text-rose-400 text-lg">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-rose-600 dark:text-rose-500 tracking-tight mb-1 uppercase">NOK / Retired</div>
                <div class="text-sm font-bold text-slate-800 dark:text-white leading-none tracking-tighter" id="statRetired">—</div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <x-table id="slowBatchTable">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th class="px-4 py-4 w-12 text-center text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">No</th>
                <th scope="col" class="px-6 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700" style="min-width: 120px;">ID Number</th>
                <th scope="col" class="px-6 py-4 text-center w-16 text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Sketch</th>
                <th scope="col" class="px-6 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700" style="min-width: 200px;">Tool Name</th>
                <th scope="col" class="px-6 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Location</th>
                <th scope="col" class="px-6 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Purchase Date</th>
                <th scope="col" class="px-6 py-4 text-center text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700" style="min-width: 80px;">Age / Life</th>
                <th scope="col" class="px-6 py-4 text-center text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Rate (%)</th>
                <th scope="col" class="px-6 py-4 text-center text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Condition Status</th>
                <th scope="col" class="px-6 py-4 text-right text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700" style="min-width: 120px;">Asset Value (IDR)</th>
                <th class="px-4 py-4 text-center text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Status</th>
                <th class="px-4 py-4 text-center w-[90px] text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

{{-- Modal: Register Batch --}}
<div id="modal-batch-form" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50">
    <div class="relative w-full max-w-lg transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-widest" id="batchModalTitle">Register New Asset</h3>
            <button class="close-modal text-gray-400 hover:text-gray-500 w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <div class="overflow-y-auto px-6 py-6 flex-1 custom-scrollbar">
            <form id="batchForm">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <input type="hidden" id="batchId">
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Tool <span class="text-red-500">*</span></label>
                    <select name="tool_id" required class="select2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3">
                        <option value="">-- Select Tool --</option>
                        @foreach($tools as $tool)
                            @if($tool->category && $tool->category->moving_type === 'slow')
                            <option value="{{ $tool->id }}" data-lifetime="{{ $tool->std_lifetime_yrs }}">
                                {{ $tool->name }} — {{ $tool->brand }} ({{ $tool->spec_code ?? 'No Spec' }})
                            </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">ID Number <span class="text-red-500">*</span></label>
                        <input type="text" name="id_number" required readonly class="uppercase bg-slate-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-400 text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 cursor-not-allowed" placeholder="Select a Tool to generate...">
                    </div>
                    <div>
                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Purchase Date <span class="text-red-500">*</span></label>
                        <input type="date" name="purchase_date" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Price (IDR) <span class="text-red-500">*</span></label>
                        <input type="number" name="purchase_price" min="0" step="1000" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3" placeholder="0">
                    </div>
                    <div>
                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Physical Rate (%) <span class="text-red-500">*</span></label>
                        <select name="physical_rate" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3">
                            <option value="100">100% — Ok</option>
                            <option value="75">75% — Good</option>
                            <option value="50">50% — Still good</option>
                            <option value="25">25% — Warning</option>
                            <option value="0">0% — Retired</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Std. Lifetime (Yrs) <span class="text-red-500">*</span></label>
                        <input type="number" name="std_lifetime_yrs" id="batchLifetime" min="1" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3" placeholder="e.g. 5">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Current Location <span class="text-red-500">*</span></label>
                    <select name="location_id" id="batchLocationId" required class="select2-modal bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3">
                        <option value="">-- Select Location --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" data-category="{{ $loc->category }}">{{ $loc->code }} — {{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Status <span class="text-red-500">*</span></label>
                    <select name="status" id="batchStatus" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3">
                        <option value="active">ACTIVE</option>
                        <option value="nok">NON-ACTIVE (NOK)</option>
                        <option value="retired">RETIRED</option>
                    </select>
                </div>
                {{-- Preview asset value --}}
                <div class="mt-5 p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-xs border border-emerald-200 dark:border-emerald-800">
                    <p class="text-[10px] font-bold uppercase text-emerald-700 dark:text-emerald-400 tracking-wider mb-1">Asset Value Preview</p>
                    <p class="text-lg font-bold text-emerald-700 dark:text-emerald-400" id="previewInitialValue">—</p>
                    <p class="text-[10px] text-gray-500 mt-1">= Price × (Physical Rate / 100)</p>
                </div>
            </form>
        </div>
        <div class="border-t border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 flex gap-3">
            <button type="button" class="close-modal flex-1 px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-[10px] font-bold text-gray-600 uppercase tracking-widest hover:bg-gray-50 transition-colors">Cancel</button>
            <button type="button" id="saveBatchBtn" class="flex-1 px-4 py-3 bg-primary-600 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest hover:bg-primary-700 transition-all">Save Asset</button>
        </div>
    </div>
</div>

{{-- Modal: Image Preview --}}
<div id="modal-preview" class="modal-container hidden fixed inset-0 z-[110] flex items-center justify-center bg-slate-900/60 p-4">
    <div class="relative max-w-4xl w-full h-full flex items-center justify-center p-4">
        <img id="img-full" src="" class="max-w-full max-h-[90vh] object-contain rounded-xs shadow-2xl transition-all duration-300">
        <button class="close-preview absolute top-4 right-4 text-white text-3xl hover:text-red-400 hover:scale-110 active:scale-95 transition-all drop-shadow-lg" title="Close"><i class="fa-solid fa-xmark"></i></button>
    </div>
</div>
@endsection

@push('style')
<style>
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    
    /* Select2 Single Selection Styling Overrides for Badges */
    .select2-container .select2-selection--single {
        height: auto !important;
        min-height: 38px !important;
        display: flex !important;
        align-items: center !important;
        padding-top: 4px !important;
        padding-bottom: 4px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: normal !important;
        padding-left: 8px !important;
        padding-right: 20px !important;
        width: 100% !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        display: flex !important;
        align-items: center !important;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Custom Select2 Formatter for segmented modal options
    function formatLocationState(state) {
        if (!state.id) {
            return state.text;
        }
        const element = $(state.element);
        const category = element.data('category') || 'storage';
        
        let badgeColor = 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800/30';
        if (category === 'machine') {
            badgeColor = 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-400 dark:border-purple-800/30';
        } else if (category === 'subcont') {
            badgeColor = 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-900/20 dark:text-orange-400 dark:border-orange-800/30';
        } else if (category === 'scrap' || category === 'lost') {
            badgeColor = 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800/30';
        }

        const badgeText = category.toUpperCase();

        return $(
            `<div class="flex items-center">
                <div class="flex items-center h-5 mr-2">
                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-xs text-[9px] font-bold border ${badgeColor} leading-none">${badgeText}</span>
                </div>
                <span class="text-xs font-medium text-slate-700 dark:text-gray-300 truncate">${state.text}</span>
            </div>`
        );
    }

    // Initialize category & condition Select2 filters
    $('#filter_category, #filter_condition').select2({
        width: '100%'
    });

    // Toggle filters visibility
    $('#btnToggleFilter').on('click', function(e) {
        e.stopPropagation();
        $('#filterCard').toggleClass('hidden');
    });

    // Toggle legend popover visibility
    $('#toggleLegend').on('click', function(e) {
        e.stopPropagation();
        $('#legendPopover').toggleClass('hidden');
    });

    // Close popovers/filters when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#legendPopover, #toggleLegend').length) {
            $('#legendPopover').addClass('hidden');
        }
        if (!$(e.target).closest('#filterCard, #btnToggleFilter, .select2-container').length) {
            $('#filterCard').addClass('hidden');
        }
    });

    // Filter change listeners
    $('#filter_category, #filter_condition').on('change', function() {
        window.slowTable.ajax.reload();
    });

    // Reset filters
    $('#reset_filters').on('click', function() {
        $('#filter_category').val('').trigger('change');
        $('#filter_condition').val('').trigger('change');
    });

    $('select[name="tool_id"]', '#modal-batch-form').select2({
        dropdownParent: $('#modal-batch-form'),
        width: '100%'
    });

    $('.select2-modal', '#modal-batch-form').select2({
        dropdownParent: $('#modal-batch-form'),
        width: '100%',
        templateResult: formatLocationState,
        templateSelection: formatLocationState
    });

    const apiBase = "{{ route('inventory.tool.slow-batch.index') }}";
    const idr = (v) => 'Rp ' + parseFloat(v || 0).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });

    window.previewImg = (src) => {
        $('#img-full').attr('src', src);
        $('#modal-preview').removeClass('hidden');
    };

    let currentStatus = 'active';

    window.slowTable = window.defaultDataTable('#slowBatchTable', {
        serverSide: true,
        ajax: {
            url: apiBase, type: 'GET',
            data: (d) => {
                d.status = currentStatus;
                d.category_id = $('#filter_category').val();
                d.condition = $('#filter_condition').val();
            }
        },
        order: [[1, 'desc']],
        columns: [
            { data: null, orderable: false, searchable: false, render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'id_number', render: d => `<span class="font-mono font-semibold text-primary-600 dark:text-primary-400 text-xs">${d}</span>` },
            { 
                data: 'sketch_image', 
                className: 'text-center',
                render: d => d ? `<img src="${d}" class="h-8 w-8 object-cover mx-auto rounded-xs border border-gray-200 cursor-pointer hover:scale-150 transition-all" onclick="window.previewImg('${d}')">` : `<div class="h-8 w-8 flex items-center justify-center mx-auto bg-gray-50 border border-gray-100 text-gray-300 rounded-xs"><i class="fa-solid fa-image text-[8px]"></i></div>`
            },
            {
                data: null, render: (d, t, r) =>
                    `<div><span class="font-semibold text-xs text-gray-900 dark:text-white">${r.tool_name}</span><br>
                    <span class="text-[10px] text-gray-500 uppercase font-medium tracking-tighter">${r.brand} · ${r.spec_code || 'No Spec'}</span></div>`
            },
            { data: 'location', render: d => `<span class="text-xs font-bold text-gray-700 dark:text-gray-300">${d}</span>` },
            { data: 'purchase_date', className: 'text-center', render: d => `<span class="text-xs">${d}</span>` },
            { 
                data: 'age_years', className: 'text-center', 
                render: (d, t, r) => `<span class="text-xs font-semibold ${d >= r.std_lifetime_yrs ? 'text-orange-600' : 'text-gray-600'}">${d} / ${r.std_lifetime_yrs}</span>` 
            },
            { 
                data: 'physical_rate', className: 'text-center', 
                render: d => `<span class="text-xs font-bold ${d < 50 ? 'text-red-500' : 'text-emerald-600'}">${d}%</span>` 
            },
            {
                data: 'physical_rate', className: 'text-center',
                render: (d) => {
                    const val = Math.round(d);
                    let label = 'UNKNOWN';
                    let cls = 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                    if (val === 100) {
                        label = 'OK';
                        cls = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400';
                    } else if (val === 75) {
                        label = 'GOOD';
                        cls = 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
                    } else if (val === 50) {
                        label = 'STILL GOOD';
                        cls = 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400';
                    } else if (val === 25 || val === 20) {
                        label = 'WARNING';
                        cls = 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400';
                    } else if (val === 0) {
                        label = 'RETIRED';
                        cls = 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
                    }
                    return `<span class="px-2 py-0.5 rounded-xs text-[9px] font-bold uppercase tracking-wider ${cls}">${label}</span>`;
                }
            },
            {
                data: null, className: 'text-right font-mono',
                render: (d, t, r) => {
                    const cls = r.status === 'active' ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-gray-400 line-through';
                    return `<span class="${cls} text-xs">${idr(r.live_asset_value)}</span>`;
                }
            },
            {
                data: 'status', className: 'text-center',
                render: (d) => {
                    const badges = {
                        active:  'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                        nok:     'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                        retired: 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
                    };
                    return `<span class="px-2 py-0.5 rounded-xs text-[9px] font-bold uppercase tracking-wider ${badges[d] || ''}">${d}</span>`;
                }
            },
            { 
                data: null, orderable: false, searchable: false, className: 'text-center',
                render: (d, t, r) => {
                    let actionsHtml = `
                    <div class="flex items-center justify-center gap-1">
                        <button class="print-qr-btn h-8 w-8 inline-flex items-center justify-center text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-xs border border-green-100/50 dark:border-green-800/30 transition-all active:scale-95"
                            data-id="${r.id}" title="Print QR Code">
                            <i class="fa-solid fa-print text-sm"></i>
                        </button>`;
                        
                    @if(Auth::user()->hasMenuPermission('inventory.tool.slow-batch.index', 'can_edit'))
                    actionsHtml += `
                        <button class="edit-batch-btn h-8 w-8 inline-flex items-center justify-center text-primary-600 rounded-xs bg-primary-50 hover:bg-primary-100 transition-colors"
                            data-id="${r.id}" 
                            data-id-number="${r.id_number}"
                            data-tool-id="${r.tool_id}"
                            data-location-id="${r.location_id}"
                            data-purchase-date="${r.purchase_date_raw}" 
                            data-purchase-price="${r.purchase_price}" 
                            data-physical-rate="${r.physical_rate}"
                            data-lifetime="${r.std_lifetime_yrs}" 
                            data-status="${r.status}"
                            title="Edit">
                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                        </button>`;
                    @endif
                    
                    actionsHtml += `</div>`;
                    return actionsHtml;
                }
            }
        ],
        drawCallback: function(settings) {
            const res = settings.json;
            if (res && res.stats) {
                $('#statTotalBatches').text(res.stats.total_active_items);
                $('#statTotalValue').text(idr(res.stats.total_asset_value));
                $('#statOkGood').text(res.stats.ok_good_still_good_count);
                $('#statWarning').text(res.stats.warning_count);
                $('#statRetired').text(res.stats.retired_count);
            } else {
                $('#statTotalBatches').text('0');
                $('#statTotalValue').text(idr(0));
                $('#statOkGood').text('0');
                $('#statWarning').text('0');
                $('#statRetired').text('0');
            }
        }
    });

    const showMdl = (id) => { $('.modal-container').addClass('hidden'); $(`#${id}`).removeClass('hidden'); };
    const hideMdl = (id) => { $(`#${id}`).addClass('hidden'); };
    $('.close-modal').on('click', function() { $(this).closest('.modal-container').addClass('hidden'); });
    $(document).on('click', '#modal-preview', function(e) {
        if ($(e.target).closest('#img-full').length === 0) {
            $('#modal-preview').addClass('hidden');
        }
    });
    $(document).on('click', '#modal-preview .close-preview', function(e) {
        e.stopPropagation();
        $('#modal-preview').addClass('hidden');
    });

    // Preview asset value (Sync with controller logic: Price * Depreciation * Physical Rate)
    function updatePreview() {
        const price = parseFloat($('input[name="purchase_price"]').val()) || 0;
        const rate  = parseFloat($('[name="physical_rate"]').val()) || 0;
        const purchaseDateStr = $('input[name="purchase_date"]').val();
        const lifetime = parseFloat($('input[name="std_lifetime_yrs"]').val()) || 1;

        let depFactor = 1;
        if (purchaseDateStr) {
            const purchaseDate = new Date(purchaseDateStr);
            const today = new Date();
            const ageDays = (today - purchaseDate) / (1000 * 60 * 60 * 24);
            const ageYears = ageDays / 365.25;
            const remainYrs = Math.max(0, lifetime - ageYears);
            depFactor = remainYrs / lifetime;
        }

        const total = price * depFactor * (rate / 100);
        $('#previewInitialValue').text(total > 0 ? idr(total) : (total === 0 && purchaseDateStr ? idr(0) : '—'));
    }
    $('input[name="purchase_price"], select[name="physical_rate"], input[name="purchase_date"], input[name="std_lifetime_yrs"]').on('input change', updatePreview);

    // Auto-fill lifetime and ID prefix from tool selection
    $('select[name="tool_id"]', '#modal-batch-form').on('change', function() {
        const selected = $('option:selected', this);
        const lifetime = selected.data('lifetime');
        if (lifetime) $('#batchLifetime').val(lifetime);

        const toolId = $(this).val();
        const idInput = $('input[name="id_number"]', '#batchForm');
        
        // Only generate ID number for new registrations (where batchId is empty)
        if (toolId && !$('#batchId').val()) {
            idInput.attr('placeholder', 'Generating...');
            $.ajax({
                url: apiBase + '/next-id',
                method: 'GET',
                data: { tool_id: toolId },
                success: function(res) {
                    if (res.next_id) {
                        idInput.val(res.next_id);
                    } else {
                        idInput.val('');
                        idInput.attr('placeholder', 'e.g. TOL-2024-001');
                    }
                },
                error: function() {
                    idInput.val('');
                    idInput.attr('placeholder', 'e.g. TOL-2024-001');
                }
            });
        }
    });

    // Auto-transform ID number to uppercase as typed
    $('input[name="id_number"]', '#batchForm').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });

    // Automatically set status to NOK and disable active option when physical rate is 0%
    $('select[name="physical_rate"]', '#modal-batch-form').on('change', function() {
        const rate = $(this).val();
        const statusSelect = $('select[name="status"]', '#modal-batch-form');
        if (rate === '0') {
            statusSelect.val('nok').trigger('change');
            statusSelect.find('option[value="active"]').attr('disabled', true);
        } else {
            statusSelect.find('option[value="active"]').removeAttr('disabled');
        }
    });

    $('#btnAddBatch').on('click', () => {
        $('#batchForm')[0].reset();
        $('#batchId').val('');
        $('input[name="_method"]').val('POST');
        $('#batchModalTitle').text('Register New Batch');
        $('#previewInitialValue').text('—');
        $('select.select2').trigger('change');
        showMdl('modal-batch-form');
    });

    // Auto-trigger registration modal if tool_id is passed in query string
    const urlParams = new URLSearchParams(window.location.search);
    const preselectedToolId = urlParams.get('tool_id');
    if (preselectedToolId) {
        $('#batchForm')[0].reset();
        $('#batchId').val('');
        $('input[name="_method"]').val('POST');
        $('#batchModalTitle').text('Register New Batch');
        $('#previewInitialValue').text('—');
        $('select[name="tool_id"]', '#batchForm').val(preselectedToolId).trigger('change');
        showMdl('modal-batch-form');
    }

    $(document).on('click', '.edit-batch-btn', function() {
        const btn = $(this);
        $('#batchForm')[0].reset();
        $('#batchId').val(btn.data('id'));
        $('input[name="id_number"]', '#batchForm').val(btn.data('id-number'));
        $('select[name="tool_id"]', '#batchForm').val(btn.data('tool-id')).trigger('change');
        $('input[name="purchase_date"]', '#batchForm').val(btn.data('purchase-date'));
        $('input[name="purchase_price"]', '#batchForm').val(btn.data('purchase-price'));
        $('[name="physical_rate"]', '#batchForm').val(Math.round(btn.data('physical-rate'))).trigger('change');
        $('input[name="std_lifetime_yrs"]', '#batchForm').val(btn.data('lifetime'));
        $('select[name="location_id"]', '#batchForm').val(btn.data('location-id')).trigger('change');
        $('select[name="status"]', '#batchForm').val(btn.data('status')).trigger('change');
        $('input[name="_method"]').val('PUT');
        $('#batchModalTitle').text('Edit Asset');
        updatePreview();
        showMdl('modal-batch-form');
    });

    $('#saveBatchBtn').on('click', function() {
        const id  = $('#batchId').val();
        const url = id ? `${apiBase}/${id}` : apiBase;
        $.ajax({
            url, method: 'POST', data: $('#batchForm').serialize(),
            success: (res) => { toast('success', 'Success', res.message); hideMdl('modal-batch-form'); slowTable.ajax.reload(); },
            error:   (xhr) => { toast('error',   'Error',   xhr.responseJSON?.message || 'Failed'); }
        });
    });

    // Print QR Code from table row
    $(document).on('click', '.print-qr-btn', function() {
        const batchId = $(this).data('id');
        window.open(`{{ url('inventory/tool/slow-batch/print-qr') }}/${batchId}`, '_blank');
    });
});
</script>
@endpush
