@extends('layouts.app')
@section('title', 'Inventory Master Data')
@section('page_title', 'Master Data')
@section('header-title', 'Inventory Master')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Inventory Master Data</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage inventory master data.</p>
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
                    <button data-tab="sub-contractor" role="tab" aria-selected="false" class="tab-button segmented-btn rounded-md py-2 px-4 text-sm font-medium border-transparent text-gray-700 dark:text-gray-300 relative z-10">
                        Sub Contractor
                    </button>
                    <button data-tab="transaction-category" role="tab" aria-selected="false" class="tab-button segmented-btn rounded-md py-2 px-4 text-sm font-medium border-transparent text-gray-700 dark:text-gray-300 relative z-10">
                        Transaction Category
                    </button>
                    <button data-tab="pic" role="tab" aria-selected="false" class="tab-button segmented-btn rounded-md py-2 px-4 text-sm font-medium border-transparent text-gray-700 dark:text-gray-300 relative z-10">
                        PIC
                    </button>
                </div>
            </nav>
        </div>
    </div>

    {{-- Tab Content: Coil Center --}}
    <div id="tab-coil-center" class="tab-content hidden">
        <div class="mb-4 flex justify-end">
            <button type="button" class="add-button inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition ease-in-out duration-150" data-target="coil-center">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
        <x-table id="coilCenterTable">
            <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-400">
                <tr>
                    <th scope="col" class="px-6 py-3 w-16">No</th>
                    <th scope="col" class="px-6 py-3">Code</th>
                    <th scope="col" class="px-6 py-3">Name</th>
                    <th scope="col" class="px-6 py-3">Address</th>
                    <th scope="col" class="px-6 py-3 text-center w-[100px]">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </x-table>
    </div>

    {{-- Tab Content: Material Spec --}}
    <div id="tab-material-spec" class="tab-content hidden">
        <div class="mb-4 flex justify-end">
            <button type="button" class="add-button inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150" data-target="material-spec">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
        <x-table id="materialSpecTable">
            <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-400">
                <tr>
                    <th scope="col" class="px-6 py-3 w-16">No</th>
                    <th scope="col" class="px-6 py-3">Spec Name</th>
                    <th scope="col" class="px-6 py-3">Coating Type</th>
                    <th scope="col" class="px-6 py-3 text-center w-[100px]">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </x-table>
    </div>

    {{-- Tab Content: Unit --}}
    <div id="tab-unit" class="tab-content hidden">
        <div class="mb-4 flex justify-end">
            <button type="button" class="add-button inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150" data-target="unit">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
        <x-table id="unitTable">
            <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-400">
                <tr>
                    <th scope="col" class="px-6 py-3 w-16">No</th>
                    <th scope="col" class="px-6 py-3">Code</th>
                    <th scope="col" class="px-6 py-3">Name</th>
                    <th scope="col" class="px-6 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </x-table>
    </div>

    {{-- Tab Content: Rank --}}
    <div id="tab-rank" class="tab-content hidden">
        <div class="mb-4 flex justify-end">
            <button type="button" class="add-button inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150" data-target="rank">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
        <x-table id="rankTable">
            <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-400">
                <tr>
                    <th scope="col" class="px-6 py-3 w-16">No</th>
                    <th scope="col" class="px-6 py-3">Code</th>
                    <th scope="col" class="px-6 py-3">Limit Value</th>
                    <th scope="col" class="px-6 py-3">Description</th>
                    <th scope="col" class="px-6 py-3 text-center w-[100px]">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </x-table>
    </div>

    {{-- Tab Content: Sub Contractor --}}
    <div id="tab-sub-contractor" class="tab-content hidden">
        <div class="mb-4 flex justify-end">
            <button type="button" class="add-button inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150" data-target="sub-contractor">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
        <x-table id="subContractorTable">
            <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-400">
                <tr>
                    <th scope="col" class="px-6 py-3 w-16">No</th>
                    <th scope="col" class="px-6 py-3">Code</th>
                    <th scope="col" class="px-6 py-3">Name</th>
                    <th scope="col" class="px-6 py-3">Service Type</th>
                    <th scope="col" class="px-6 py-3 text-center w-[100px]">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </x-table>
    </div>

    {{-- Tab Content: Transaction Category --}}
    <div id="tab-transaction-category" class="tab-content hidden">
        <div class="mb-4 flex justify-end">
            <button type="button" class="add-button inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150" data-target="transaction-category">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
        <x-table id="transactionCategoryTable">
            <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-400">
                <tr>
                    <th scope="col" class="px-6 py-3 w-16">No</th>
                    <th scope="col" class="px-6 py-3">Code</th>
                    <th scope="col" class="px-6 py-3">Name</th>
                    <th scope="col" class="px-6 py-3">Effect</th>
                    <th scope="col" class="px-6 py-3 text-center w-[100px]">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </x-table>
    </div>

    {{-- Tab Content: PIC --}}
    <div id="tab-pic" class="tab-content hidden">
        <div class="mb-4 flex justify-end">
            <button type="button" class="add-button inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150" data-target="pic">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
        <x-table id="picTable">
            <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-400">
                <tr>
                    <th scope="col" class="px-6 py-3 w-16">No</th>
                    <th scope="col" class="px-6 py-3">Name</th>
                    <th scope="col" class="px-6 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </x-table>
    </div>
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
        color: #2563eb !important; /* blue-600 */
        font-weight: 600 !important;
    }
    .dark .segmented-tabs .segmented-btn.active {
        color: #60a5fa !important; /* blue-400 */
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
