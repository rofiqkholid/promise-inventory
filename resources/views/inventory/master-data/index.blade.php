@extends('layouts.app')
@section('title', 'Inventory Master Data')
@section('page_title', 'Master Data')
@section('header-title', 'Inventory Master')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">Inventory Master Data</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Manage all system master data configurations.</p>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="mb-6">
        <div>
            <nav aria-label="Tabs" class="relative">
                <div class="segmented-tabs inline-flex bg-gray-200 dark:bg-gray-700/50 rounded-lg p-1 relative overflow-x-auto max-w-full no-scrollbar" role="tablist">
                    {{-- Sliding Highlight --}}
                    <div id="tab-highlight" class="absolute bg-white dark:bg-gray-600 rounded-md shadow-sm transition-all duration-300 ease-in-out z-0 border border-gray-100 dark:border-gray-500"></div>
                    
                    <button data-tab="coil-center" role="tab" aria-selected="true" class="tab-button segmented-btn active rounded-md py-2 px-4 text-sm font-medium border-transparent text-gray-700 dark:text-gray-300 relative z-10">
                        Coil Center
                    </button>
                    <button data-tab="material-spec" role="tab" aria-selected="false" class="tab-button segmented-btn rounded-md py-2 px-4 text-sm font-medium border-transparent text-gray-700 dark:text-gray-300 relative z-10">
                        Material Spec
                    </button>
                    <button data-tab="unit" role="tab" aria-selected="false" class="tab-button segmented-btn rounded-md py-2 px-4 text-sm font-medium border-transparent text-gray-700 dark:text-gray-300 relative z-10">
                        Unit
                    </button>
                    <button data-tab="rank" role="tab" aria-selected="false" class="tab-button segmented-btn rounded-md py-2 px-4 text-sm font-medium border-transparent text-gray-700 dark:text-gray-300 relative z-10">
                        Rank
                    </button>
                    <button data-tab="supplier" role="tab" aria-selected="false" class="tab-button segmented-btn rounded-md py-2 px-4 text-sm font-medium border-transparent text-gray-700 dark:text-gray-300 relative z-10">
                        Supplier
                    </button>
                    <!-- <button data-tab="transaction-category" role="tab" aria-selected="false" class="tab-button segmented-btn rounded-md py-2 px-4 text-sm font-medium border-transparent text-gray-700 dark:text-gray-300 relative z-10">
                        Transaction Category
                    </button> -->

                </div>
            </nav>
        </div>
    </div>

    {{-- Tab Content: Coil Center --}}
    <div id="tab-coil-center" class="tab-content hidden">
        <div class="mb-4 flex justify-end">
            <button type="button" class="add-button inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-slate-900 hover:bg-slate-800 border border-transparent rounded-md text-[10px] font-bold text-white uppercase tracking-widest shadow-md active:scale-[0.98] transition-all" data-target="coil-center">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
        <x-table id="coilCenterTable">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 w-16 text-center text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">No</th>
                    <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Code</th>
                    <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Email</th>
                    <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Phone</th>
                    <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Address</th>
                    <th scope="col" class="px-6 py-3 text-center w-[100px] text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </x-table>
    </div>

    {{-- Tab Content: Material Spec --}}
    <div id="tab-material-spec" class="tab-content hidden">
        <div class="mb-4 flex justify-end">
            <button type="button" class="add-button inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-slate-900 hover:bg-slate-800 border border-transparent rounded-md text-[10px] font-bold text-white uppercase tracking-widest shadow-md active:scale-[0.98] transition-all" data-target="material-spec">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
        <x-table id="materialSpecTable">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 w-16 text-center text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">No</th>
                    <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Spec Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Coating Type</th>
                    <th scope="col" class="px-6 py-3 text-center w-[100px] text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </x-table>
    </div>

    {{-- Tab Content: Unit --}}
    <div id="tab-unit" class="tab-content hidden">
        <div class="mb-4 flex justify-end">
            <button type="button" class="add-button inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-slate-900 hover:bg-slate-800 border border-transparent rounded-md text-[10px] font-bold text-white uppercase tracking-widest shadow-md active:scale-[0.98] transition-all" data-target="unit">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
        <x-table id="unitTable">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 w-16 text-center text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">No</th>
                    <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Code</th>
                    <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Name</th>
                    <th scope="col" class="px-6 py-3 text-center w-[100px] text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </x-table>
    </div>

    {{-- Tab Content: Rank --}}
    <div id="tab-rank" class="tab-content hidden">
        <div class="mb-4 flex justify-end">
            <button type="button" class="add-button inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-slate-900 hover:bg-slate-800 border border-transparent rounded-md text-[10px] font-bold text-white uppercase tracking-widest shadow-md active:scale-[0.98] transition-all" data-target="rank">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
        <x-table id="rankTable">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 w-16 text-center text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">No</th>
                    <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Code</th>
                    <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Limit Value</th>
                    <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Description</th>
                    <th scope="col" class="px-6 py-3 text-center w-[100px] text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </x-table>
    </div>

    {{-- Tab Content: Supplier --}}
    <div id="tab-supplier" class="tab-content hidden">
        <div class="mb-4 flex justify-end">
            <button type="button" class="add-button inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-slate-900 hover:bg-slate-800 border border-transparent rounded-md text-[10px] font-bold text-white uppercase tracking-widest shadow-md active:scale-[0.98] transition-all" data-target="supplier">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
        <x-table id="supplierTable">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 w-16 text-center text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">No</th>
                    <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Code</th>
                    <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Email</th>
                    <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Phone</th>
                    <th scope="col" class="px-6 py-3 text-center w-[100px] text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </x-table>
    </div>

    {{-- Tab Content: Transaction Category --}}

    {{-- Tab Content: PIC --}}

</div>

{{-- Modals will be added via separate file for brevity --}}
@include('inventory.master-data.partials.modals')

@endsection

@push('style')
<style>
    .tab-button.active {
        border-color: rgb(59 130 246);
        color: rgb(59 130 246);
    }
    .dark .tab-button.active {
        border-color: rgb(96 165 250);
        color: rgb(96 165 250);
    }
    /* Segmented tabs styling */
    .segmented-tabs { position: relative; }
    .segmented-tabs .segmented-btn {
        background: transparent !important;
        color: #64748b; /* slate-500 */
        transition: color .2s ease-in-out;
        outline: none;
        border: none !important;
        box-shadow: none !important;
    }
    .segmented-tabs .segmented-btn:hover {
        color: #334155; /* slate-700 */
    }
    .dark .segmented-tabs .segmented-btn {
        color: #94a3b8; /* slate-400 */
    }
    .dark .segmented-tabs .segmented-btn:hover {
        color: #e2e8f0; /* slate-200 */
    }
    .segmented-tabs .segmented-btn.active {
        color: #0f172a !important; /* slate-900 */
        font-weight: 700 !important;
    }
    .dark .segmented-tabs .segmented-btn.active {
        color: #f8fafc !important; /* slate-50 */
    }
    
    #tab-highlight {
        pointer-events: none;
        display: none; /* Hidden by default, shown by JS on init */
    }

    /* Mobile scroll optimization */
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@include('inventory.master-data.partials.scripts')
@endpush
