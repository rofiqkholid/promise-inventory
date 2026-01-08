@extends('layouts.app')
@section('title', 'Inventory Master Data')
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
            <nav aria-label="Tabs">
                <div class="segmented-tabs inline-flex bg-gray-200 dark:bg-gray-700 rounded-lg p-1 space-x-1" role="tablist">
                    <button data-tab="coil-center" role="tab" aria-selected="false" class="tab-button segmented-btn rounded-md py-2 px-4 text-sm font-medium border-transparent text-gray-700 dark:text-gray-300">
                        Coil Center
                    </button>
                    <button data-tab="material-spec" role="tab" aria-selected="false" class="tab-button segmented-btn rounded-md py-2 px-4 text-sm font-medium border-transparent text-gray-700 dark:text-gray-300">
                        Material Spec
                    </button>
                    <button data-tab="unit" role="tab" aria-selected="false" class="tab-button segmented-btn rounded-md py-2 px-4 text-sm font-medium border-transparent text-gray-700 dark:text-gray-300">
                        Unit
                    </button>
                    <button data-tab="rank" role="tab" aria-selected="false" class="tab-button segmented-btn rounded-md py-2 px-4 text-sm font-medium border-transparent text-gray-700 dark:text-gray-300">
                        Rank
                    </button>
                    <button data-tab="sub-contractor" role="tab" aria-selected="false" class="tab-button segmented-btn rounded-md py-2 px-4 text-sm font-medium border-transparent text-gray-700 dark:text-gray-300">
                        Sub Contractor
                    </button>
                    <button data-tab="transaction-category" role="tab" aria-selected="false" class="tab-button segmented-btn rounded-md py-2 px-4 text-sm font-medium border-transparent text-gray-700 dark:text-gray-300">
                        Transaction Category
                    </button>
                    <button data-tab="pic" role="tab" aria-selected="false" class="tab-button segmented-btn rounded-md py-2 px-4 text-sm font-medium border-transparent text-gray-700 dark:text-gray-300">
                        PIC
                    </button>
                </div>
            </nav>
        </div>
    </div>

    {{-- Tab Content: Coil Center --}}
    <div id="tab-coil-center" class="tab-content hidden">
        <div class="mb-4 flex justify-end">
            <button type="button" class="add-button inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150" data-target="coil-center">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
            <div class="p-4 md:p-6 overflow-x-auto">
                <table id="coilCenterTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3 w-16">No</th>
                            <th scope="col" class="px-6 py-3">Code</th>
                            <th scope="col" class="px-6 py-3">Name</th>
                            <th scope="col" class="px-6 py-3">Address</th>
                            <th scope="col" class="px-6 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tab Content: Material Spec --}}
    <div id="tab-material-spec" class="tab-content hidden">
        <div class="mb-4 flex justify-end">
            <button type="button" class="add-button inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150" data-target="material-spec">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
            <div class="p-4 md:p-6 overflow-x-auto">
                <table id="materialSpecTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3 w-16">No</th>
                            <th scope="col" class="px-6 py-3">Spec Name</th>
                            <th scope="col" class="px-6 py-3">Coating Type</th>
                            <th scope="col" class="px-6 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tab Content: Unit --}}
    <div id="tab-unit" class="tab-content hidden">
        <div class="mb-4 flex justify-end">
            <button type="button" class="add-button inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150" data-target="unit">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
            <div class="p-4 md:p-6 overflow-x-auto">
                <table id="unitTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3 w-16">No</th>
                            <th scope="col" class="px-6 py-3">Code</th>
                            <th scope="col" class="px-6 py-3">Name</th>
                            <th scope="col" class="px-6 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tab Content: Rank --}}
    <div id="tab-rank" class="tab-content hidden">
        <div class="mb-4 flex justify-end">
            <button type="button" class="add-button inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150" data-target="rank">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
            <div class="p-4 md:p-6 overflow-x-auto">
                <table id="rankTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3 w-16">No</th>
                            <th scope="col" class="px-6 py-3">Code</th>
                            <th scope="col" class="px-6 py-3">Limit Value</th>
                            <th scope="col" class="px-6 py-3">Description</th>
                            <th scope="col" class="px-6 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tab Content: Sub Contractor --}}
    <div id="tab-sub-contractor" class="tab-content hidden">
        <div class="mb-4 flex justify-end">
            <button type="button" class="add-button inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150" data-target="sub-contractor">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
            <div class="p-4 md:p-6 overflow-x-auto">
                <table id="subContractorTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3 w-16">No</th>
                            <th scope="col" class="px-6 py-3">Code</th>
                            <th scope="col" class="px-6 py-3">Name</th>
                            <th scope="col" class="px-6 py-3">Service Type</th>
                            <th scope="col" class="px-6 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tab Content: Transaction Category --}}
    <div id="tab-transaction-category" class="tab-content hidden">
        <div class="mb-4 flex justify-end">
            <button type="button" class="add-button inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150" data-target="transaction-category">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
            <div class="p-4 md:p-6 overflow-x-auto">
                <table id="transactionCategoryTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3 w-16">No</th>
                            <th scope="col" class="px-6 py-3">Code</th>
                            <th scope="col" class="px-6 py-3">Name</th>
                            <th scope="col" class="px-6 py-3">Effect</th>
                            <th scope="col" class="px-6 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tab Content: PIC --}}
    <div id="tab-pic" class="tab-content hidden">
        <div class="mb-4 flex justify-end">
            <button type="button" class="add-button inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150" data-target="pic">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
            <div class="p-4 md:p-6 overflow-x-auto">
                <table id="picTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3 w-16">No</th>
                            <th scope="col" class="px-6 py-3">Name</th>
                            <th scope="col" class="px-6 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modals will be added via separate file for brevity --}}
@include('inventory.partials.modals')

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
    div.dataTables_length label { font-size: 0.75rem; }
    div.dataTables_length select { font-size: 0.75rem; line-height: 1rem; padding: 0.25rem 1.25rem 0.25rem 0.5rem; height: 1.875rem; width: 4.5rem; }
    div.dataTables_filter label { font-size: 0.75rem; }
    div.dataTables_filter input[type="search"] { font-size: 0.75rem; line-height: 1rem; padding: 0.25rem 0.5rem; height: 1.875rem; width: 12rem; }
    div.dataTables_info { font-size: 0.75rem; padding-top: 0.8em; }
    div.dataTables_wrapper div.dataTables_scrollBody::-webkit-scrollbar { display: none !important; }
    div.dataTables_wrapper div.dataTables_scrollBody { -ms-overflow-style: none !important; scrollbar-width: none !important; }

    /* Segmented tabs styling */
    .segmented-tabs { }
    .segmented-tabs .segmented-btn {
        background: transparent;
        color: #6b7280; /* gray-500 */
        transition: all .12s ease-in-out;
        outline: none;
    }
    .segmented-tabs .segmented-btn:hover {
        background: rgba(255,255,255,0.08);
        color: #374151; /* gray-700 */
    }
    .segmented-tabs .segmented-btn.active {
        background: #ffffff;
        color: #111827; /* gray-900 */
        font-weight: 600;
        box-shadow: 0 4px 10px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.06);
        transform: translateY(-1px);
    }
    .dark .segmented-tabs .segmented-btn.active {
        background: #0f1724; /* slightly lighter than background */
        color: #E5E7EB;
        border: 1px solid rgba(255,255,255,0.04);
        box-shadow: 0 4px 10px rgba(2,6,23,0.6);
    }

    /* Add a small sliding indicator (optional) */
    .segmented-tabs { position: relative; }
    .segmented-tabs .segmented-btn { position: relative; z-index: 10; }
    .segmented-tabs .segmented-btn::after {
        content: '';
        position: absolute;
        left: 12px;
        right: 12px;
        bottom: 6px;
        height: 3px;
        border-radius: 2px;
        background: transparent;
        transition: all .18s ease-in-out;
    }
    .segmented-tabs .segmented-btn.active::after {
        background: rgba(59,130,246,0.95); /* blue */
        bottom: 6px;
    }
    .dark .segmented-tabs .segmented-btn.active::after {
        background: rgba(96,165,250,0.95);
    }
    .segmented-tabs .segmented-btn:focus {
        box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@include('inventory.partials.scripts')
@endpush
