@extends('layouts.app')
@section('title', 'Inventory Product Management')
@section('page_title', 'Product Master')
@section('header-title', 'Inventory Product')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-xl xl:text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tighter leading-none">Inventory Product</h2>
            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 font-normal">Manage inventory product details.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-2">
            <button type="button" id="btnExport" class="inline-flex items-center justify-center gap-2 px-4 h-9 bg-emerald-600 hover:bg-emerald-700 border border-transparent rounded-xs text-xs font-medium text-white active:scale-[0.98] transition-all shadow-sm">
                <i class="fa-solid fa-file-excel"></i>
                Export Excel
            </button>
            <button type="button" id="btnImport" class="inline-flex items-center justify-center gap-2 px-4 h-9 bg-indigo-600 hover:bg-indigo-700 border border-transparent rounded-xs text-xs font-medium text-white active:scale-[0.98] transition-all shadow-sm">
                <i class="fa-solid fa-file-import"></i>
                Import Excel
            </button>
            <button type="button" id="add-button" class="inline-flex items-center justify-center gap-2 px-4 h-9 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-xs font-medium text-white active:scale-[0.98] transition-all shadow-sm">
                <i class="fa-solid fa-plus"></i>
                Add New
            </button>
        </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="mb-4 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-gray-700 bg-slate-50/50 dark:bg-slate-900/30">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fa-solid fa-filter text-primary-600"></i> Product Filter
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 items-end">
                {{-- CUSTOMER --}}
                <div class="w-full">
                    <label class="block mb-2 text-xs font-medium text-gray-900 dark:text-gray-500">Customer</label>
                    <select id="filterCustomer" class="select2-filter w-full">
                        <option value="">All Customers</option>
                        @foreach($filterCustomers as $c)
                            <option value="{{ $c->id }}">{{ $c->code }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- MODEL --}}
                <div class="w-full">
                    <label class="block mb-2 text-xs font-medium text-gray-900 dark:text-gray-500">Model</label>
                    <select id="filterModel" class="select2-filter w-full">
                        <option value="">All Models</option>
                        @foreach($filterModels as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- PART NUMBER (SELECT2 AJAX) --}}
                <div class="w-full lg:col-span-1">
                    <label class="block mb-2 text-xs font-medium text-gray-900 dark:text-gray-500">Part Number</label>
                    <select id="filterPartNo" class="w-full">
                        <option value="">All Part Numbers</option>
                    </select>
                </div>

                {{-- PROJECT STATUS FILTER --}}
                <div class="w-full">
                    <label class="block mb-2 text-xs font-medium text-gray-900 dark:text-gray-500">Project Status</label>
                    <select id="filterProjectStatus" class="select2-filter w-full">
                        <option value="">All Status</option>
                        <option value="project">Project</option>
                        <option value="regular">Regular</option>
                    </select>
                </div>

                {{-- ACTIONS --}}
                <div class="flex items-center w-full lg:col-span-1">
                    <button type="button" id="btnResetFilter" class="h-9 px-4 text-xs font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xs border border-slate-200 dark:border-gray-700 transition-all active:scale-95 shadow-xs">
                        <i class="fa-solid fa-rotate-left mr-1.5"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <x-table id="inventoryProductTable">
        <thead>
            <tr>
                <th class="text-center w-16">No</th>
                <th class="text-left w-48 min-w-[180px]">Part No</th>
                <th class="text-left">Customer</th>
                <th class="text-left">Model</th>
                <th class="text-center">Status</th>
                <th class="text-left">Material</th>
                <th class="text-center">Rank</th>
                <th class="text-left">Dimensions</th>
                <th class="text-center">Weight (Kg)</th>
                <th class="text-center">Pcs/Unit</th>
                <th class="text-center">Unit/Car</th>
                <th class="text-left">Remark</th>
                <th class="text-center whitespace-nowrap">Updated At</th>
                <th class="text-center w-[100px]">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

{{-- Add/Edit Modal --}}
<div id="formModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 justify-center items-center w-full h-full bg-slate-900/50 flex">
    <div class="relative p-4 w-full max-w-4xl max-h-[95vh] h-full md:h-auto">
        <div class="relative bg-white rounded-xs shadow-2xl dark:bg-gray-800 flex flex-col max-h-[90vh] overflow-hidden">
            <button type="button" class="close-modal-button text-gray-400 absolute top-3 right-3 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded-xs text-sm p-2 ml-auto inline-flex items-center dark:hover:bg-gray-700 dark:hover:text-white z-10 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-primary-50/80 dark:bg-slate-900/50">
                <h3 class="text-base font-medium text-slate-900 dark:text-white" id="modalTitle">Add Inventory Product</h3>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 font-normal">Enter product details and specifications</p>
            </div>

            <form id="productForm" method="POST" class="flex flex-col h-full overflow-hidden min-h-0">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="p-6 overflow-y-auto min-h-0 flex-1 space-y-8 custom-scrollbar">
                    {{-- Product Information Section --}}
                    <div>
                        <h4 class="text-[10px] font-medium text-slate-900 dark:text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-2">
                            <i class="fa-solid fa-circle-info text-primary-500"></i>
                            Product Information
                        </h4>
                        <div class="grid gap-6 md:grid-cols-4">
                            {{-- CUSTOMER --}}
                            <div>
                                <label class="block mb-2 text-[10px] font-medium text-slate-900 dark:text-gray-300 uppercase tracking-wider">Customer<span class="text-red-500">*</span></label>
                                <select id="customer_id" class="select2 w-full bg-gray-50 border border-slate-200 text-gray-900 text-xs font-medium rounded-xs focus:ring-slate-500 focus:border-slate-500 block p-2.5 h-10 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                    <option></option>
                                </select>
                            </div>

                            {{-- MODEL --}}
                            <div>
                                <label class="block mb-2 text-[10px] font-medium text-slate-900 dark:text-gray-300 uppercase tracking-wider">Model<span class="text-red-500">*</span></label>
                                <select name="model_id" id="model_id" class="select2 w-full bg-gray-50 border border-slate-200 text-gray-900 text-xs font-medium rounded-xs focus:ring-slate-500 focus:border-slate-500 block p-2.5 h-10 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all" disabled>
                                    <option></option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block mb-2 text-[10px] font-medium text-slate-900 dark:text-gray-300 uppercase tracking-wider">Product Name <span class="text-red-500">*</span></label>
                                <select name="product_id" id="product_id" required class="bg-white border border-slate-200 text-gray-900 text-xs font-medium rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                    <option value="">Search Product...</option>
                                </select>
                                <p id="error-product_id" class="text-red-500 text-[10px] mt-1 hidden font-medium uppercase tracking-wide"><i class="fa-solid fa-circle-exclamation mr-1"></i> Required</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block mb-2 text-[10px] font-medium text-slate-900 dark:text-gray-300 uppercase tracking-wider">Unit <span class="text-red-500">*</span></label>
                                <select name="unit_id" id="unit_id" required class="select2 bg-white border border-slate-200 text-gray-900 text-xs font-medium rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                    <option value="">Select Unit</option>
                                </select>
                                <p id="error-unit_id" class="text-red-500 text-[10px] mt-1 hidden font-medium uppercase tracking-wide"><i class="fa-solid fa-circle-exclamation mr-1"></i> Required</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block mb-2 text-[10px] font-medium text-slate-900 dark:text-gray-300 uppercase tracking-wider">Material Spec</label>
                                 <select name="material_spec_id" id="material_spec_id" class="select2 bg-white border border-slate-200 text-gray-900 text-xs font-medium rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                    <option value="">Select Material Spec</option>
                                </select>
                                <p id="error-material_spec_id" class="text-red-500 text-[10px] mt-1 hidden font-medium uppercase tracking-wide"><i class="fa-solid fa-circle-exclamation mr-1"></i> Check Input</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block mb-2 text-[10px] font-medium text-slate-900 dark:text-gray-300 uppercase tracking-wider">Revision <span class="text-red-500">*</span></label>
                                <select name="revision_id" id="revision_id" required class="bg-white border border-slate-200 text-gray-900 text-xs font-medium rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                    <option value="">Select Revision</option>
                                </select>
                                <p id="error-revision_id" class="text-red-500 text-[10px] mt-1 hidden font-medium uppercase tracking-wide"><i class="fa-solid fa-circle-exclamation mr-1"></i> Required</p>
                            </div>
                        </div>
                    </div>

                    {{-- Unit & Dimensions Section --}}
                    <div>
                        <h4 class="text-[10px] font-medium text-slate-900 dark:text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-2">
                            <i class="fa-solid fa-ruler-combined text-primary-500"></i>
                            Unit & Dimensions
                        </h4>

                        {{-- Dimensions Grid --}}
                        <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-6 bg-primary-50/50 dark:bg-gray-700/30 p-4 rounded-xs border border-slate-100 dark:border-gray-700">
                            <div class="lg:col-span-1">
                                <label class="block mb-2 text-[10px] font-medium text-slate-900 dark:text-gray-300 uppercase tracking-wider">Thickness</label>
                                <input type="number" name="thickness" id="thickness" step="0.01" min="0" class="bg-white border border-slate-200 text-gray-900 text-xs font-medium rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all" placeholder="0.00">
                                <p id="error-thickness" class="text-red-500 text-[10px] mt-1 hidden font-medium uppercase tracking-wide"></p>
                            </div>
                            <div class="lg:col-span-1">
                                <label class="block mb-2 text-[10px] font-medium text-slate-900 dark:text-gray-300 uppercase tracking-wider">Width</label>
                                <input type="number" name="width" id="width" step="0.01" min="0" class="bg-white border border-slate-200 text-gray-900 text-xs font-medium rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all" placeholder="0.00">
                                <p id="error-width" class="text-red-500 text-[10px] mt-1 hidden font-medium uppercase tracking-wide"></p>
                            </div>

                            {{-- Dynamic Fields --}}
                            <div id="lengthContainer" class="lg:col-span-1">
                                <label class="block mb-2 text-[10px] font-medium text-slate-900 dark:text-gray-300 uppercase tracking-wider">Length</label>
                                <input type="number" name="length" id="length" step="0.01" min="0" class="bg-white border border-slate-200 text-gray-900 text-xs font-medium rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all" placeholder="0.00">
                                <p id="error-length" class="text-red-500 text-[10px] mt-1 hidden font-medium uppercase tracking-wide"></p>
                            </div>
                            <div id="length2Container" class="lg:col-span-1">
                                <label class="block mb-2 text-[10px] font-medium text-slate-900 dark:text-gray-300 uppercase tracking-wider">Length 2</label>
                                <input type="number" name="length_2" id="length_2" step="0.01" min="0" class="bg-white border border-slate-200 text-gray-900 text-xs font-medium rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all" placeholder="0.00">
                                <p id="error-length_2" class="text-red-500 text-[10px] mt-1 hidden font-medium uppercase tracking-wide"></p>
                            </div>
                            <div id="pitchContainer" class="lg:col-span-1">
                                <label class="block mb-2 text-[10px] font-medium text-slate-900 dark:text-gray-300 uppercase tracking-wider">Pitch</label>
                                <input type="number" name="pitch" id="pitch" step="0.01" min="0" class="bg-white border border-slate-200 text-gray-900 text-xs font-medium rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all" placeholder="0.00">
                                <p id="error-pitch" class="text-red-500 text-[10px] mt-1 hidden font-medium uppercase tracking-wide"></p>
                            </div>

                            <div class="lg:col-span-1">
                                <label class="block mb-2 text-[10px] font-medium text-slate-900 dark:text-gray-300 uppercase tracking-wider">Density</label>
                                <input type="number" name="density" id="density" step="0.001" min="0" value="7.85" class="bg-white border border-slate-200 text-gray-900 text-xs font-medium rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all" placeholder="7.85">
                                <p id="error-density" class="text-red-500 text-[10px] mt-1 hidden font-medium uppercase tracking-wide"></p>
                            </div>
                            <div class="lg:col-span-1">
                                <label class="block mb-2 text-[10px] font-medium text-primary-600 dark:text-primary-400 uppercase tracking-wider">Weight (Kg)</label>
                                <input type="number" name="weight_kg" id="weight_kg" step="0.01" min="0" readonly class="bg-primary-50 border border-primary-100 text-primary-600 text-xs font-medium rounded-xs block w-full h-10 px-3 dark:bg-primary-900/20 dark:border-primary-800 dark:text-primary-300 cursor-not-allowed" placeholder="0.00">
                                <p id="error-weight_kg" class="text-red-500 text-[10px] mt-1 hidden font-medium uppercase tracking-wide"></p>
                            </div>
                            <div class="lg:col-span-1">
                                <label class="block mb-2 text-[10px] font-medium text-purple-700 dark:text-purple-400 uppercase tracking-wider">Net Weight (Kg)</label>
                                <input type="number" name="net_weight" id="net_weight" step="0.01" min="0" class="bg-purple-50/50 border border-purple-200 text-purple-700 text-xs font-medium rounded-xs focus:ring-purple-500 focus:border-purple-500 block w-full h-10 px-3 dark:bg-purple-900/20 dark:border-purple-800 dark:text-purple-300 transition-all" placeholder="0.00">
                                <p id="error-net_weight" class="text-red-500 text-[10px] mt-1 hidden font-medium uppercase tracking-wide"></p>
                            </div>
                            <div class="lg:col-span-1">
                                <label class="block mb-2 text-[10px] font-medium text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Material Price</label>
                                <input type="number" name="material_price" id="material_price" step="0.01" min="0" value="20000" class="bg-emerald-50/50 border border-emerald-200 text-emerald-700 text-xs font-medium rounded-xs focus:ring-emerald-500 focus:border-emerald-500 block w-full h-10 px-3 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-300 transition-all" placeholder="0.00">
                                <p id="error-material_price" class="text-red-500 text-[10px] mt-1 hidden font-medium uppercase tracking-wide"></p>
                            </div>
                        </div>
                        
                        {{-- Coil Specific Weight Details --}}
                        <div id="coilWeightSection" class="hidden">
                            <div class="mt-4 grid gap-4 grid-cols-5 bg-amber-50/50 dark:bg-amber-900/10 p-4 rounded-xs border border-amber-100 dark:border-amber-900/30">
                                <div>
                                    <label class="block mb-2 text-[10px] font-medium text-slate-900 dark:text-gray-300 uppercase tracking-wider">Gross Coil (Kg)</label>
                                    <input type="number" name="gross_coil" id="gross_coil" step="0.01" min="0" class="bg-white border border-slate-200 text-gray-900 text-xs font-medium rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all" placeholder="0.00">
                                    <p id="error-gross_coil" class="text-red-500 text-[10px] mt-1 hidden font-medium uppercase tracking-wide"></p>
                                </div>
                                <div>
                                    <label class="block mb-2 text-[10px] font-medium text-slate-900 dark:text-gray-300 uppercase tracking-wider">Top Coil (mm)</label>
                                    <input type="number" name="top_coil" id="top_coil" step="0.01" min="0" value="500" class="bg-white border border-slate-200 text-gray-900 text-xs font-medium rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all" placeholder="500">
                                    <p id="error-top_coil" class="text-red-500 text-[10px] mt-1 hidden font-medium uppercase tracking-wide"></p>
                                </div>
                                <div>
                                    <label class="block mb-2 text-[10px] font-medium text-slate-900 dark:text-gray-300 uppercase tracking-wider">End Coil (mm)</label>
                                    <input type="number" name="end_coil" id="end_coil" step="0.01" min="0" value="2500" class="bg-white border border-slate-200 text-gray-900 text-xs font-medium rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all" placeholder="2500">
                                    <p id="error-end_coil" class="text-red-500 text-[10px] mt-1 hidden font-medium uppercase tracking-wide"></p>
                                </div>
                                <div>
                                    <label class="block mb-2 text-[10px] font-medium text-amber-700 dark:text-amber-400 uppercase tracking-wider">Net Coil (Kg)</label>
                                    <input type="number" name="net_coil" id="net_coil" step="0.01" min="0" readonly class="bg-amber-100 border border-amber-200 text-amber-700 text-xs font-medium rounded-xs block w-full h-10 px-3 dark:bg-amber-900/30 dark:border-amber-800 dark:text-amber-300 cursor-not-allowed" placeholder="0.00">
                                    <p id="error-net_coil" class="text-red-500 text-[10px] mt-1 hidden font-medium uppercase tracking-wide"></p>
                                </div>
                                <div>
                                    <label class="block mb-2 text-[10px] font-medium text-blue-700 dark:text-blue-400 uppercase tracking-wider">Est. Total Pcs</label>
                                    <input type="number" id="est_pcs" readonly class="bg-blue-100 border border-blue-200 text-blue-700 text-xs font-medium rounded-xs block w-full h-10 px-3 dark:bg-blue-900/30 dark:border-blue-800 dark:text-blue-300 cursor-not-allowed" placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Inventory Control & Logistics Section --}}
                    <div>
                        <h4 class="text-[10px] font-medium text-slate-900 dark:text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-2">
                            <i class="fa-solid fa-boxes-stacked text-primary-500"></i>
                            Logistics & Control
                        </h4>
                        <div class="grid gap-6 md:grid-cols-5">
                            <div>
                                <label class="block mb-2 text-[10px] font-medium text-slate-900 dark:text-gray-300 uppercase tracking-wider">Rank</label>
                                <select name="rank_id" id="rank_id" class="select2 bg-white border border-slate-200 text-gray-900 text-xs font-medium rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                    <option value="">Select Rank</option>
                                </select>
                                <p id="error-rank_id" class="text-red-500 text-[10px] mt-1 hidden font-medium uppercase tracking-wide"></p>
                            </div>
                            <div id="pcsPerPitchContainer">
                                <label class="block mb-2 text-[10px] font-medium text-slate-900 dark:text-gray-300 uppercase tracking-wider">Pcs / Pitch</label>
                                <input type="number" name="pcs_per_pitch" id="pcs_per_pitch" step="1" min="0" class="bg-white border border-slate-200 text-gray-900 text-xs font-medium rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all" placeholder="0">
                                <p id="error-pcs_per_pitch" class="text-red-500 text-[10px] mt-1 hidden font-medium uppercase tracking-wide"></p>
                            </div>
                            <div>
                                <label class="block mb-2 text-[10px] font-medium text-slate-900 dark:text-gray-300 uppercase tracking-wider">Pcs / Unit</label>
                                <input type="number" name="pcs_per_unit" id="pcs_per_unit" min="1" value="1" class="bg-white border border-slate-200 text-gray-900 text-xs font-medium rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                <p id="error-pcs_per_unit" class="text-red-500 text-[10px] mt-1 hidden font-medium uppercase tracking-wide"></p>
                            </div>
                            <div>
                                <label class="block mb-2 text-[10px] font-medium text-slate-900 dark:text-gray-300 uppercase tracking-wider">Unit / Car</label>
                                <input type="number" name="unit_per_car" id="unit_per_car" min="1" value="1" class="bg-white border border-slate-200 text-gray-900 text-xs font-medium rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                <p id="error-unit_per_car" class="text-red-500 text-[10px] mt-1 hidden font-medium uppercase tracking-wide"></p>
                            </div>
                            <div>
                                <label class="block mb-2 text-[10px] font-medium text-slate-900 dark:text-gray-300 uppercase tracking-wider">Min Stock</label>
                                <input type="number" name="min_stock" id="min_stock" min="0" value="0" readonly class="bg-gray-100 border border-slate-200 text-gray-500 text-xs font-medium rounded-xs focus:outline-none block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white cursor-not-allowed">
                                <p id="error-min_stock" class="text-red-500 text-[10px] mt-1 hidden font-medium uppercase tracking-wide"></p>
                            </div>
                            <div class="md:col-span-4">
                                <label class="block mb-2 text-[10px] font-medium text-slate-900 dark:text-gray-300 uppercase tracking-wider">Remark</label>
                                <textarea name="remark" id="remark" rows="1" class="block w-full text-xs font-medium text-gray-900 bg-white rounded-xs border border-slate-200 focus:ring-slate-500 focus:border-slate-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all placeholder-gray-300 py-3 px-3 min-h-[42px]" placeholder="Optional notes..."></textarea>
                                <p id="error-remark" class="text-red-500 text-[10px] mt-1 hidden font-medium uppercase tracking-wide"></p>
                            </div>

                            {{-- PRODUCT STATUS OVERRIDE --}}
                            <div class="md:col-span-2">
                                <label class="block mb-2 text-[10px] font-medium text-slate-900 dark:text-gray-300 uppercase tracking-wider">Product Status Override</label>
                                <select name="product_status" id="product_status" class="bg-white border border-slate-200 text-gray-900 text-xs font-medium rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                    <option value="">None (Follow Model)</option>
                                    <option value="Oldstock OK">Oldstock OK</option>
                                    <option value="Oldstock NG">Oldstock NG</option>
                                </select>
                            </div>

                            {{-- PRODUCT STATUS REMARK --}}
                            <div class="md:col-span-2">
                                <label class="block mb-2 text-[10px] font-medium text-slate-900 dark:text-gray-300 uppercase tracking-wider">Status Remark</label>
                                <select name="product_status_remark" id="product_status_remark" class="bg-white border border-slate-200 text-gray-900 text-xs font-medium rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                    <option value="">No Remark</option>
                                    <option value="Drawing Change">Drawing Change</option>
                                    <option value="Damage">Damage</option>
                                    <!-- <option value="Under">Under</option> -->
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-none flex items-center justify-end gap-3 px-8 py-5 border-t border-gray-100 dark:border-gray-700 bg-primary-50/80 dark:bg-slate-900/50">
                    <button type="button" class="close-modal-button text-gray-700 bg-white hover:bg-gray-50 rounded-xs border border-gray-300 text-xs font-medium px-6 py-2.5 transition-all active:scale-95">
                        Cancel
                    </button>
                    <button type="submit" class="text-white bg-primary-600 hover:bg-primary-700 rounded-xs text-xs font-medium px-6 py-2.5 text-center transition-all active:scale-95">
                        <i class="fa-solid fa-save mr-1.5"></i> Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<x-inventory.delete-modal />

{{-- Import Modal --}}
<div id="importModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 justify-center items-center w-full h-full bg-slate-900/50 flex">
    <div class="relative p-4 w-full max-w-lg max-h-[95vh]">
        <div class="relative bg-white rounded-xs shadow-2xl dark:bg-gray-800 flex flex-col max-h-[90vh] overflow-hidden">
            <button type="button" class="close-modal-button text-gray-400 absolute top-3 right-3 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded-xs text-sm p-2 ml-auto inline-flex items-center dark:hover:bg-gray-700 dark:hover:text-white z-10 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-primary-50/80 dark:bg-slate-900/50">
                <h3 class="text-base font-medium text-slate-900 dark:text-white">Import Inventory Product</h3>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 font-normal">Upload Excel file to bulk import products.</p>
            </div>
            <form id="importForm" method="POST" enctype="multipart/form-data" class="flex flex-col h-full overflow-hidden min-h-0">
                @csrf
                <div class="p-6 overflow-y-auto min-h-0 flex-1 space-y-6 custom-scrollbar">
                    <div class="bg-blue-50/50 dark:bg-blue-900/20 p-4 rounded-xs border border-blue-100 dark:border-blue-800/50">
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
                            <div>
                                <h4 class="text-[11px] font-medium text-blue-800 dark:text-blue-300 mb-1">Standard Template</h4>
                                <p class="text-[10px] text-blue-600/80 dark:text-blue-400/80 font-normal leading-relaxed">Please use the official excel template to ensure all data is correctly mapped. Download the latest version below:</p>
                                <a href="{{ route('inventory.master.product.downloadTemplate') }}" target="_blank" class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-gray-800 border border-blue-200 dark:border-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/50 rounded-xs text-xs font-medium text-blue-600 dark:text-blue-400 transition-all active:scale-95">
                                    <i class="fa-solid fa-download"></i> Download Template
                                </a>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-[10px] font-semibold text-slate-900 dark:text-gray-300 uppercase tracking-wider">1. Select Excel File <span class="text-red-500">*</span></label>
                        <input type="file" name="file" id="import_file" accept=".xlsx, .xls, .csv" required class="block w-full text-xs text-gray-900 border border-slate-200 rounded-xs cursor-pointer bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 focus:outline-none file:mr-4 file:py-2.5 file:px-4 file:border-0 file:text-[10px] file:font-medium file:tracking-widest file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-slate-800 dark:file:text-primary-400 transition-all">
                        <div id="file_loading" class="hidden mt-2 text-[10px] text-primary-600 font-medium animate-pulse"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Detecting worksheets...</div>
                    </div>

                    <div id="import_next_steps" class="hidden space-y-6 animate-fadeIn">
                        {{-- SHEET NAME --}}
                        <div>
                            <label class="block mb-2 text-[10px] font-semibold text-slate-900 dark:text-gray-300 uppercase tracking-wider">2. Select Worksheet <span class="text-red-500">*</span></label>
                            <select name="sheet_name" id="sheet_name" required class="select2-import w-full">
                                <option value="">Select Worksheet...</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- CUSTOMER --}}
                            <div>
                                <label class="block mb-2 text-[10px] font-semibold text-slate-900 dark:text-gray-300 uppercase tracking-wider">3. Target Customer <span class="text-red-500">*</span></label>
                                <select name="customer_id" id="import_customer_id" required class="select2-import w-full">
                                    <option value="">Select Customer...</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}">{{ $c->code }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- MODEL --}}
                            <div>
                                <label class="block mb-2 text-[10px] font-semibold text-slate-900 dark:text-gray-300 uppercase tracking-wider">4. Target Model <span class="text-red-500">*</span></label>
                                <select name="model_id" id="import_model_id" required class="select2-import w-full" disabled>
                                    <option value="">Select Model...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="importResult" class="hidden text-[10px] font-medium p-4 rounded-xs border"></div>
                </div>
                <div class="flex-none flex items-center justify-end gap-3 px-8 py-5 border-t border-gray-100 dark:border-gray-700 bg-primary-50/80 dark:bg-slate-900/50">
                    <button type="button" class="close-modal-button text-gray-700 bg-white hover:bg-gray-50 rounded-xs border border-gray-300 text-xs font-medium px-6 py-2.5 transition-all active:scale-95">Cancel</button>
                    <button type="submit" id="btnSubmitImport" class="text-white bg-primary-600 hover:bg-primary-700 rounded-xs text-xs font-medium px-6 py-2.5 text-center transition-all active:scale-95 flex items-center gap-2">
                        <i class="fa-solid fa-upload"></i> Process Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    
    #importResult {
        max-height: 400px;
        overflow-y: auto;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
$(function() {
    /**
     * ProductApp - Structured Application Logic
     */
    const ProductApp = {
        config: {
            csrfToken: $('meta[name="csrf-token"]').attr('content'),
            routes: {
                data: '{{ route("inventory.master.product.data") }}',
                store: '{{ route("inventory.master.product.store") }}',
                dropdown: '{{ route("inventory.master.product.dropdownData") }}',
                customers: '{{ route("inventory.master.product.getCustomers") }}',
                models: '{{ route("inventory.master.product.getModels") }}',
                products: '{{ route("inventory.master.product.getProducts") }}',
                export: '{{ route("inventory.master.product.exportExcel") }}',
                import: '{{ route("inventory.master.product.importExcel") }}',
                sheetNames: '{{ route("inventory.master.product.getSheetNames") }}',
                base: '{{ url("inventory/master/product") }}'
            }
        },

        state: {
            isEditMode: false,
            isDuplicateMode: false,
            isAutoFilling: false,
            dropdownData: {},
            modelCache: {},
            modelLoadPromise: Promise.resolve(),
            deleteId: null,
            table: null
        },

        elements: {
            form: $('#productForm'),
            formModal: $('#formModal'),
            deleteModal: $('#modal-delete'),
            customerFilter: $('#filterCustomer'),
            modelFilter: $('#filterModel'),
            partNoFilter: $('#filterPartNo'),
            customerSelect: $('#customer_id'),
            modelSelect: $('#model_id'),
            productSelect: $('#product_id'),
            unitSelect: $('#unit_id')
        },

        init: function() {
            this.initTable();
            this.initFilters();
            this.initFormSelect2();
            this.loadInitialData();
            this.bindFilterEvents();
            this.bindFormEvents();
            this.bindTableEvents();
            this.initImportSelect2();
        },

        initImportSelect2: function() {
            $('.select2-import').select2({
                dropdownParent: $('#importModal'),
                width: '100%',
                placeholder: 'Select...'
            });
        },

        /**
         * INITIALIZATION & DATA LOADING
         */
        initTable: function() {
            this.state.table = window.defaultDataTable('#inventoryProductTable', {
                processing: true,
                serverSide: true,
                ajax: {
                    url: this.config.routes.data,
                    type: 'GET',
                    data: d => {
                        d.customer_id = this.elements.customerFilter.val();
                        d.model_id = this.elements.modelFilter.val();
                        d.part_no = this.elements.partNoFilter.val();
                        d.incomplete_only = $('#filterDataStatus').val() === 'incomplete' ? 1 : null;
                    }
                },
                columns: [
                    { data: null, className: 'text-center', render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1 },
                    { 
                        data: 'part_no', 
                        className: 'whitespace-nowrap font-medium text-slate-700 dark:text-gray-200',
                        render: (d, t, r) => {
                            if (r.is_incomplete_coil) {
                                return `<div>
                                    <div class="flex items-center gap-1.5">
                                        ${d}
                                        <i class="fa-solid fa-triangle-exclamation text-rose-600 animate-pulse" title="Incomplete Coil Data"></i>
                                    </div>
                                    <span class="text-[8px] font-medium text-rose-600 uppercase bg-rose-50 px-1 py-0.5 rounded-xs border border-rose-100 mt-0.5 inline-block">Data Incomplete</span>
                                </div>`;
                            }
                            return d;
                        }
                    },

                    { data: 'customer', className: 'text-center' },
                    { data: 'model', className: 'text-center' },
                    {
                        data: 'product_status',
                        className: 'text-center',
                        render: (d, t, r) => {
                            const status = d || r.model_project_status || 'Project';
                            const colors = {
                                'Project': 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800/50',
                                'Regular': 'bg-primary-50 text-primary-700 border-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:border-primary-800/50',
                                'Oldstock OK': 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/50',
                                'Oldstock NG': 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800/50'
                            }[status] || 'bg-gray-50 text-gray-600 border-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700';
                            return `<span class="px-2 py-1.5 rounded-xs text-[10px] font-medium whitespace-nowrap uppercase tracking-wide border ${colors}">${status}</span>`;
                        }
                    },
                    {
                        data: null,
                        render: r => `<div class="leading-snug">
                            <div class="font-medium text-slate-700 dark:text-slate-300">${r.material_spec}</div>
                            <div class="text-[10px] text-gray-400 tracking-tight">${r.coating_type || '-'}</div>
                        </div>`
                    },
                    { data: 'rank', className: 'text-center' },
                    {
                        data: null,
                        className: 'whitespace-nowrap',
                        render: r => this.logic.renderDimensions(r)
                    },
                    { data: 'weight_kg', className: 'text-center', render: d => d ? parseFloat(d).toFixed(2) : '-' },
                    { data: 'pcs_per_unit', className: 'text-center' },
                    { data: 'unit_per_car', className: 'text-center' },
                    { data: 'remark', className: 'text-xs text-gray-500', render: d => d || '-' },
                    { data: 'updated_at', className: 'whitespace-nowrap text-[10px] text-gray-400', render: d => d || '-' },
                    {
                        data: null,
                        orderable: false,
                        className: 'text-center',
                        render: r => `
                            <div class="flex items-center justify-center gap-1.5">
                                <button class="print-button h-8 w-8 inline-flex items-center justify-center text-green-600 rounded-xs bg-green-50 hover:bg-green-100 transition-colors" data-id="${r.id}"><i class="fa-solid fa-print"></i></button>
                                <button class="edit-button h-8 w-8 inline-flex items-center justify-center text-primary-600 rounded-xs bg-primary-50 hover:bg-primary-100 transition-colors" data-id="${r.id}"><i class="fa-solid fa-pen-to-square"></i></button>
                                <button class="duplicate-button h-8 w-8 inline-flex items-center justify-center text-amber-600 rounded-xs bg-amber-50 hover:bg-amber-100 transition-colors" data-id="${r.id}"><i class="fa-solid fa-copy"></i></button>
                                <button class="delete-button h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-xs bg-red-50 hover:bg-red-100 transition-colors" data-id="${r.id}"><i class="fa-solid fa-trash-can"></i></button>
                            </div>`
                    }
                ],
                order: [[0, 'desc']]
            });
        },

        initFilters: function() {
            $('.select2-filter').select2({ width: '100%', placeholder: 'Select...', allowClear: true });
            
            this.elements.partNoFilter.select2({
                width: '100%',
                placeholder: 'All Part Numbers',
                allowClear: true,
                ajax: {
                    url: this.config.routes.products,
                    dataType: 'json',
                    delay: 250,
                    data: p => ({
                        q: p.term || '',
                        customer_id: this.elements.customerFilter.val(),
                        model_id: this.elements.modelFilter.val(),
                        for_filter: 1
                    }),
                    processResults: d => ({
                        results: d.results.map(i => ({ id: i.part_no, text: i.text }))
                    })
                }
            });
        },

        initFormSelect2: function() {
            $('#customer_id, #model_id, #material_spec_id, #unit_id, #rank_id').select2({
                dropdownParent: this.elements.formModal,
                width: '100%',
                placeholder: 'Select...',
            });
            this.initProductSelect2();
        },

        initProductSelect2: function() {
            const el = this.elements.productSelect;
            if (el.data('select2')) el.select2('destroy');
            el.select2({
                dropdownParent: this.elements.formModal,
                width: '100%',
                placeholder: 'Search Product...',
                ajax: {
                    url: this.config.routes.products,
                    dataType: 'json',
                    delay: 250,
                    data: p => ({
                        q: p.term || '',
                        model_id: this.elements.modelSelect.val(),
                        customer_id: this.elements.customerSelect.val()
                    }),
                    processResults: d => ({ results: d.results, pagination: d.pagination })
                }
            });
        },

        loadInitialData: function() {
            $.get(this.config.routes.dropdown, (data) => {
                this.state.dropdownData = data;
                this.populateFormDropdowns(data);
            });
        },

        /**
         * EVENT BINDING CATEGORIES
         */
        bindFilterEvents: function() {
            this.elements.customerFilter.on('change', () => this.handleCustomerFilterChange());
            this.elements.modelFilter.on('change', () => {
                this.elements.partNoFilter.val(null).trigger('change');
                this.state.table.ajax.reload();
            });
            this.elements.partNoFilter.on('change', () => this.state.table.ajax.reload());
            $('#filterDataStatus').on('change', () => this.state.table.ajax.reload());
            $('#btnResetFilter').on('click', () => this.resetFilters());
            $('#btnExport').on('click', () => this.handleExport());
            
            $('#btnImport').on('click', () => {
                $('#importForm')[0].reset();
                $('#import_customer_id, #import_model_id, #sheet_name').val(null).trigger('change');
                $('#import_model_id').prop('disabled', true);
                $('#import_next_steps').addClass('hidden');
                $('#importResult').addClass('hidden').removeClass('bg-red-50 text-red-700 border-red-200 bg-green-50 text-green-700 border-green-200').html('');
                this.ui.showModal($('#importModal'));
            });

            $('#import_file').on('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                $('#file_loading').removeClass('hidden');
                $('#import_next_steps').addClass('hidden');

                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        const data = new Uint8Array(e.target.result);
                        const workbook = XLSX.read(data, { type: 'array', bookSheets: true });
                        const sheetNames = workbook.SheetNames;
                        
                        const $sheet = $('#sheet_name');
                        $sheet.empty().append('<option value="">Select Worksheet...</option>');
                        sheetNames.forEach(s => $sheet.append(new Option(s, s)));
                        $sheet.trigger('change');
                        
                        $('#import_next_steps').removeClass('hidden');
                    } catch (err) {
                        console.error(err);
                        window.showToast('Failed to read excel sheets from browser.', 'error');
                    } finally {
                        $('#file_loading').addClass('hidden');
                    }
                };
                
                reader.onerror = function() {
                    window.showToast('Error reading file.', 'error');
                    $('#file_loading').addClass('hidden');
                };

                // Use readAsArrayBuffer for better performance with large files
                reader.readAsArrayBuffer(file);
            });

            $('#import_customer_id').on('change', function() {
                const cid = $(this).val();
                const $model = $('#import_model_id');
                
                // Clear selection
                $model.val(null).trigger('change');

                if (cid) {
                    // Enable immediately for Lazy Loading
                    $model.prop('disabled', false);
                } else {
                    $model.prop('disabled', true);
                }
            });

            $('#importForm').on('submit', (e) => this.handleImportFormSubmit(e));
        },

        bindFormEvents: function() {
            $('#add-button').on('click', () => this.showAddModal());
            $('.close-modal-button, .close-modal').on('click', e => this.ui.hideModal($(e.currentTarget).closest('[id^="modal-"], [id$="Modal"]')));
            
            this.elements.customerSelect.on('change', e => this.handleFormCustomerChange(e.target.value));
            this.elements.productSelect.on('select2:select', e => this.handleProductSelect(e.params.data));
            this.elements.unitSelect.on('change', () => this.ui.toggleUnitFields());
            this.elements.form.on('submit', e => this.handleFormSubmit(e));
            
            // Calculations
            $('#unit_per_car').on('input change', () => this.logic.calculateMinStock());
            $('#thickness, #width, #length, #length_2, #pitch, #density, #pcs_per_unit, #pcs_per_pitch').on('input change', () => this.logic.calculateWeight());
            $('#thickness, #width, #density, #pitch, #pcs_per_pitch, #gross_coil, #top_coil, #end_coil').on('input change', () => this.logic.calculateNetCoil());
        },

        bindTableEvents: function() {
            $(document).on('click', '.edit-button', e => {
                const btn = $(e.currentTarget);
                const originalIcon = btn.html();
                this.ui.setBtnLoading(btn);
                this.showEditModal(btn.data('id'), btn, originalIcon);
            });

            $(document).on('click', '.duplicate-button', e => {
                const btn = $(e.currentTarget);
                const originalIcon = btn.html();
                this.ui.setBtnLoading(btn);
                this.showDuplicateModal(btn.data('id'), btn, originalIcon);
            });

            $(document).on('click', '.print-button', e => window.open(`${this.config.routes.base}/${$(e.currentTarget).data('id')}/print`, '_blank'));
            $(document).on('click', '.delete-button', e => {
                this.state.deleteId = $(e.currentTarget).data('id');
                this.ui.showModal(this.elements.deleteModal);
            });
            $('#confirmDelete').on('click', () => this.handleDelete());
        },

        /**
         * CORE LOGIC METHODS
         */
        handleCustomerFilterChange: function() {
            const cid = this.elements.customerFilter.val();
            this.elements.modelFilter.empty().append('<option value="">All Models</option>');
            this.elements.partNoFilter.val(null).trigger('change');
            
            $.get(this.config.routes.models, { customer_id: cid, for_filter: 1 }, (data) => {
                data.forEach(m => this.elements.modelFilter.append(`<option value="${m.id}">${m.name}</option>`));
                this.state.table.ajax.reload();
            });
        },

        handleExport: function() {
            const params = {
                customer_id: this.elements.customerFilter.val(),
                model_id: this.elements.modelFilter.val(),
                part_no: this.elements.partNoFilter.val(),
                search: this.state.table.search()
            };
            const queryString = $.param(params);
            window.location.href = `${this.config.routes.export}?${queryString}`;
        },

        resetFilters: function() {
            this.elements.customerFilter.val(null).trigger('change.select2');
            this.elements.modelFilter.val(null).trigger('change.select2');
            this.elements.partNoFilter.val(null).trigger('change.select2');
            $('#filterDataStatus').val(null).trigger('change.select2');
            
            $.get(this.config.routes.models, { for_filter: 1 }, (data) => {
                this.elements.modelFilter.empty().append('<option value="">All Models</option>');
                data.forEach(m => this.elements.modelFilter.append(`<option value="${m.id}">${m.name}</option>`));
                this.state.table.ajax.reload();
            });
        },

        showAddModal: function() {
            this.state.isEditMode = false;
            this.state.isDuplicateMode = false;
            this.ui.resetForm('Add Inventory Product', 'POST', this.config.routes.store);
            this.ui.toggleDuplicateMode(false);
            this.elements.productSelect.prop('disabled', false);
            this.initProductSelect2();
            this.ui.showModal(this.elements.formModal);
        },

        showEditModal: function(id, btn, originalIcon) {
            this.state.isEditMode = true;
            this.state.isDuplicateMode = false;
            this.ui.resetForm('Edit Inventory Product', 'PUT', `${this.config.routes.base}/${id}`);
            this.loadProductData(id, false, btn, originalIcon);
        },

        showDuplicateModal: function(id, btn, originalIcon) {
            this.state.isEditMode = false;
            this.state.isDuplicateMode = true;
            this.ui.resetForm('Duplicate Inventory Product', 'POST', this.config.routes.store);
            this.ui.toggleDuplicateMode(true);
            this.loadProductData(id, true, btn, originalIcon);
        },

        loadProductData: function(id, isDuplicate = false, btn = null, originalIcon = null) {
            $.get(`${this.config.routes.base}/${id}`)
              .done((data) => {
                this.elements.customerSelect.val(data.product.customer_id).trigger('change');
                
                this.state.modelLoadPromise.then(() => {
                    if (!isDuplicate) this.elements.modelSelect.val(data.model_id).trigger('change');
                    
                    if (data.product) {
                        const opt = new Option(`${data.product.part_no} - ${data.product.part_name}`, data.product.hash_id, true, true);
                        this.state.isAutoFilling = true;
                        this.elements.productSelect.append(opt).trigger('change');
                        this.state.isAutoFilling = false;
                    }

                    // Fill other fields
                    $('#revision_id').val(data.revision_id ? data.revision?.hash_id : '').trigger('change');
                    $('#partno_epicor').val(data.partno_epicor);
                    $('#material_spec_id').val(data.material_spec?.hash_id).trigger('change');
                    $('#thickness').val(parseFloat(data.thickness || 0));
                    $('#width').val(parseFloat(data.width || 0));
                    $('#length').val(parseFloat(data.length || 0));
                    $('#length_2').val(parseFloat(data.length_2 || 0));
                    $('#pitch').val(parseFloat(data.pitch || 0));
                    $('#pcs_per_pitch').val(parseInt(data.pcs_per_pitch || 0));
                    this.elements.unitSelect.val(data.unit?.hash_id).trigger('change');
                    $('#rank_id').val(data.rank?.hash_id).trigger('change');
                    $('#pcs_per_unit').val(data.pcs_per_unit);
                    $('#unit_per_car').val(data.unit_per_car);
                    $('#min_stock').val(data.min_stock);
                    $('#density').val(parseFloat(data.density || 7.85));
                    $('#gross_coil').val(parseFloat(data.gross_coil || 0));
                    $('#top_coil').val(parseFloat(data.top_coil || 500));
                    $('#end_coil').val(parseFloat(data.end_coil || 2500));
                    $('#net_coil').val(parseFloat(data.net_coil || 0));
                    $('#net_weight').val(parseFloat(data.net_weight || 0));
                    $('#material_price').val(parseFloat(data.material_price || 20000));
                    $('#remark').val(data.remark);
                    $('#product_status').val(data.product_status).trigger('change');
                    $('#product_status_remark').val(data.product_status_remark).trigger('change');

                    this.ui.toggleUnitFields();
                    this.ui.showModal(this.elements.formModal);
                    if (isDuplicate) window.showToast('Please select a NEW model to finish duplication.', 'info');
                });
              })
              .fail((xhr) => this.handleAjaxError(xhr))
              .always(() => {
                  if (btn) this.ui.unsetBtnLoading(btn, originalIcon);
              });
        },

        handleFormCustomerChange: function(cid) {
            if (!this.state.isAutoFilling) this.elements.productSelect.val(null).trigger('change');
            
            const el = this.elements.modelSelect;
            el.prop('disabled', true).empty().append('<option></option>').trigger('change');

            if (!cid) {
                this.state.modelLoadPromise = Promise.resolve();
                return;
            }

            if (this.state.modelCache[cid]) {
                this.state.modelCache[cid].forEach(m => el.append(new Option(m.name, m.id)));
                el.prop('disabled', false);
                this.state.modelLoadPromise = Promise.resolve();
                return;
            }

            this.state.modelLoadPromise = new Promise((resolve, reject) => {
                $.get(this.config.routes.models, { customer_id: cid })
                    .done(models => {
                        this.state.modelCache[cid] = models;
                        models.forEach(m => el.append(new Option(m.name, m.id)));
                        el.prop('disabled', false);
                        resolve();
                    })
                    .fail(reject);
            });
        },

        handleProductSelect: function(data) {
            if (this.state.isEditMode || this.state.isDuplicateMode || !data.id) return;

            // Auto-fill logic for Add Mode
            if (data.customer_id && data.customer_id != this.elements.customerSelect.val()) {
                this.state.isAutoFilling = true;
                this.elements.customerSelect.val(data.customer_id).trigger('change');
                this.state.isAutoFilling = false;
                this.state.modelLoadPromise.then(() => {
                    if (data.model_id) this.elements.modelSelect.val(data.model_id).trigger('change');
                });
            } else if (data.model_id && this.elements.modelSelect.val() != data.model_id) {
                this.elements.modelSelect.val(data.model_id).trigger('change');
            }

            $.get(`${this.config.routes.base}/latest-revision/${data.id}`, (res) => {
                this.state.isAutoFilling = true;
                if (res.exists) {
                    const d = res.data;
                    $('#revision_id').val(res.next_revision_id).trigger('change');
                    $('#partno_epicor').val(d.partno_epicor);
                    $('#material_spec_id').val(res.material_spec_hash).trigger('change');
                    $('#thickness').val(parseFloat(d.thickness || 0));
                    $('#width').val(parseFloat(d.width || 0));
                    $('#length').val(parseFloat(d.length || 0));
                    $('#length_2').val(parseFloat(d.length_2 || 0));
                    $('#pitch').val(parseFloat(d.pitch || 0));
                    $('#pcs_per_pitch').val(parseInt(d.pcs_per_pitch || 0));
                    this.elements.unitSelect.val(res.unit_hash).trigger('change');
                    $('#rank_id').val(res.rank_hash).trigger('change');
                    $('#pcs_per_unit').val(d.pcs_per_unit);
                    $('#unit_per_car').val(d.unit_per_car);
                    $('#density').val(parseFloat(d.density || 7.85));
                    $('#gross_coil').val(parseFloat(d.gross_coil || 0));
                    $('#top_coil').val(parseFloat(d.top_coil || 500));
                    $('#end_coil').val(parseFloat(d.end_coil || 2500));
                    $('#net_coil').val(parseFloat(d.net_coil || 0));
                    $('#net_weight').val(parseFloat(d.net_weight || 0));
                    $('#material_price').val(parseFloat(d.material_price || 20000));
                    $('#remark').val(d.remark);
                    $('#product_status').val(d.product_status).trigger('change');
                    $('#product_status_remark').val(d.product_status_remark).trigger('change');
                    window.showToast(`Found existing revision. Auto-filled from ${d.revision?.code || '-'}.`, 'info');
                } else {
                    $('#revision_id').val(res.next_revision_id).trigger('change');
                }
                this.ui.toggleUnitFields();
                this.state.isAutoFilling = false;
            });
        },

        handleFormSubmit: function(e) {
            e.preventDefault();
            this.ui.clearErrors();
            
            const wasDuplicate = this.state.isDuplicateMode;
            if (wasDuplicate) this.ui.toggleDuplicateMode(false);
            
            const formData = new FormData(this.elements.form[0]);
            if (wasDuplicate) this.ui.toggleDuplicateMode(true);

            $.ajax({
                url: this.elements.form.attr('action'),
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': this.config.csrfToken },
                data: formData,
                processData: false,
                contentType: false,
                success: (res) => {
                    this.state.table.ajax.reload();
                    this.ui.hideModal(this.elements.formModal);
                    
                    if (res.warning) {
                        Swal.fire({
                            title: 'Incomplete Coil Data',
                            text: res.warning,
                            icon: 'warning',
                            confirmButtonColor: '#3085d6'
                        });
                    } else {
                        window.showToast(res.message, 'success');
                    }
                    
                    if (wasDuplicate) this.ui.toggleDuplicateMode(false);
                },
                error: (xhr) => this.handleAjaxError(xhr)
            });
        },

        handleImportFormSubmit: function(e) {
            e.preventDefault();
            const btn = $('#btnSubmitImport');
            const originalText = btn.html();
            btn.prop('disabled', true).html('<i class="fa-solid fa-circle-notch fa-spin text-white mr-1.5"></i> Processing...');
            $('#importResult').addClass('hidden');

            const fileInput = $('#import_file')[0];
            if (!fileInput.files || !fileInput.files[0]) {
                window.showToast('Please select a file to import.', 'error');
                btn.prop('disabled', false).html(originalText);
                return;
            }

            const file = fileInput.files[0];
            const reader = new FileReader();
            
            reader.onload = (e) => {
                const fullBase64 = e.target.result;
                // Optimization: Strip the prefix (e.g. "data:...;base64,") before chunking 
                // so the server doesn't have to use regex on a massive string.
                const base64Only = fullBase64.split(',')[1]; 
                
                const chunkSize = 1024 * 1024; // Increased to 1MB for faster uploads (safe for most servers)
                const totalChunks = Math.ceil(base64Only.length / chunkSize);
                const uploadId = 'UP-' + Date.now().toString() + '-' + Math.floor(Math.random() * 10000);
                
                const uploadChunk = (index) => {
                    const chunkData = base64Only.substring(index * chunkSize, (index + 1) * chunkSize);
                    
                    const payload = {
                        customer_id: $('#import_customer_id').val(),
                        model_id: $('#import_model_id').val(),
                        sheet_name: $('#sheet_name').val(),
                        upload_id: uploadId,
                        chunk_index: index,
                        total_chunks: totalChunks,
                        file_base64_chunk: chunkData,
                        _token: this.config.csrfToken
                    };

                    let percent = Math.round((index / totalChunks) * 100);
                    if (index === totalChunks - 1) percent = 99; // 99% while waiting for server to process the final file
                    btn.html(`<i class="fa-solid fa-circle-notch fa-spin text-white mr-1.5"></i> Uploading ${percent}% ...`);

                    $.ajax({
                        url: this.config.routes.import,
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': this.config.csrfToken },
                        data: JSON.stringify(payload),
                        contentType: 'application/json',
                        success: (res) => {
                            if (index < totalChunks - 1) {
                                uploadChunk(index + 1); // Next chunk
                            } else {
                                // Done!
                                this.state.table.ajax.reload();
                                $('#importResult').removeClass('hidden bg-red-50 text-red-700 border-red-200')
                                    .addClass('bg-emerald-50 text-emerald-900 border-emerald-100 p-5 rounded-sm')
                                    .html(res.message);
                                window.showToast('Import completed successfully', 'success');
                                btn.prop('disabled', false).html(originalText);
                            }
                        },
                        error: (xhr) => {
                            let msg = xhr.responseJSON?.message || 'Upload failed. WAF or Server might still be blocking it.';
                            
                            // Enhanced error reporting
                            if (xhr.status) {
                                msg = `<span class='font-medium'>[Error ${xhr.status}: ${xhr.statusText}]</span><br>${msg}`;
                            }

                            if (xhr.status === 413) {
                                $('#importResult').removeClass('hidden bg-emerald-50 text-emerald-900 border-emerald-100')
                                    .addClass('bg-rose-50 text-rose-900 border-rose-100 p-5 rounded-sm')
                                    .html('Error 413: File is too large for the server even with chunks. Please try splitting the data.');
                            } else if (xhr.status === 419) {
                                $('#importResult').removeClass('hidden bg-emerald-50 text-emerald-900 border-emerald-100')
                                    .addClass('bg-rose-50 text-rose-900 border-rose-100 p-5 rounded-sm')
                                    .html('Error 419: Session expired. Please refresh the page and try again.');
                            } else {
                                $('#importResult').removeClass('hidden bg-emerald-50 text-emerald-900 border-emerald-100')
                                    .addClass('bg-rose-50 text-rose-900 border-rose-100 p-5 rounded-sm')
                                    .html(msg);
                            }
                            window.showToast('Import failed - check error details', 'error');
                            btn.prop('disabled', false).html(originalText);
                        }
                    });
                };
                
                uploadChunk(0); // Start upload
            };
            
            reader.readAsDataURL(file);
        },

        handleDelete: function() {
            if (!this.state.deleteId) return;
            $.ajax({
                url: `${this.config.routes.base}/${this.state.deleteId}`,
                method: 'POST',
                data: {
                    _token: this.config.csrfToken,
                    _method: 'DELETE'
                },
                success: (res) => {
                    this.state.table.ajax.reload();
                    this.ui.hideModal(this.elements.deleteModal);
                    window.showToast(res.message, 'success');
                },
                error: (xhr) => this.handleAjaxError(xhr)
            });
        },

        handleAjaxError: function(xhr) {
            const errors = xhr.responseJSON?.errors || {};
            Object.keys(errors).forEach(key => $(`#error-${key}`).text(errors[key][0]).removeClass('hidden'));
            window.showToast(xhr.responseJSON?.message || 'Operation failed', 'error');
        },

        /**
         * CALCULATION LOGIC
         */
        logic: {
            calculateWeight: function() {
                const unitId = ProductApp.elements.unitSelect.val();
                const unit = ProductApp.state.dropdownData.units?.find(u => u.hash_id === unitId);
                const name = (unit?.name || '').toLowerCase();
                
                const t = parseFloat($('#thickness').val()) || 0;
                const w = parseFloat($('#width').val()) || 0;
                const d = parseFloat($('#density').val()) || 0;
                const ppu = parseInt($('#pcs_per_unit').val()) || 1;
                const ppp = parseInt($('#pcs_per_pitch').val()) || 1;
                let weight = 0;

                if (name.includes('sheet')) weight = ((t * w * (parseFloat($('#length').val()) || 0) * d) / 1000000) / ppu;
                else if (name.includes('coil')) weight = ((t * w * (parseFloat($('#pitch').val()) || 0) * d) / 1000000) / ppp;
                else if (name.includes('trapezoid')) weight = ((t * w * (((parseFloat($('#length').val()) || 0) + (parseFloat($('#length_2').val()) || 0)) / 2) * d) / 1000000) / ppu;
                else weight = ((t * w * (parseFloat($('#length').val()) || 0) * d) / 1000000) / ppu;

                $('#weight_kg').val(weight > 0 ? weight.toFixed(2) : '');
            },

            calculateMinStock: function() {
                $('#min_stock').val((parseInt($('#unit_per_car').val()) || 0) * 90);
            },

            calculateNetCoil: function() {
                const t = parseFloat($('#thickness').val()) || 0;
                const w = parseFloat($('#width').val()) || 0;
                const d = parseFloat($('#density').val()) || 7.85;
                const pitch = parseFloat($('#pitch').val()) || 0;
                const pcsPerPitch = parseInt($('#pcs_per_pitch').val()) || 1;

                const grossKg = parseFloat($('#gross_coil').val()) || 0;
                const topMm = parseFloat($('#top_coil').val()) || 0;
                const endMm = parseFloat($('#end_coil').val()) || 0;

                // Formula Kg per 1 mm: (T * W * 1 * D) / 1000000
                const weightPerMm = (t * w * 1 * d) / 1000000;
                
                const topKg = topMm * weightPerMm;
                const endKg = endMm * weightPerMm;

                const net = Math.max(0, grossKg - (topKg + endKg));
                $('#net_coil').val(net > 0 ? net.toFixed(2) : '');

                // Total Pcs calculation: (Net weight / Weight per pitch) * pcs per pitch
                const weightPerPitch = (t * w * pitch * d) / 1000000;
                if (net > 0 && weightPerPitch > 0) {
                    const estPcs = Math.floor(net / weightPerPitch) * pcsPerPitch;
                    $('#est_pcs').val(estPcs);
                    
                    // Autofill Pcs/Unit with Est. Pcs if it's a coil
                    const unitId = ProductApp.elements.unitSelect.val();
                    const unit = ProductApp.state.dropdownData.units?.find(u => u.hash_id === unitId);
                    const name = (unit?.name || '').toLowerCase();
                    if (name.includes('coil')) {
                        $('#pcs_per_unit').val(estPcs);
                    }
                } else {
                    $('#est_pcs').val('');
                }
            },

            renderDimensions: function(row) {
                const unit = (row.unit_name || '').toLowerCase();
                const fmt = (l, v) => `<span class="inline-flex items-center gap-x-0.5"><span class="text-gray-500 font-medium">${l}:</span><span class="text-slate-800 font-medium">${v}</span></span>`;
                let items = [fmt('T', parseFloat(row.thickness) || 0), fmt('W', parseFloat(row.width) || 0)];
                if (unit.includes('coil')) {
                    items.push(fmt('P', parseFloat(row.pitch) || 0));
                    if (row.pcs_per_pitch) items.push(fmt('Pcs/P', parseInt(row.pcs_per_pitch)));
                }
                else if (unit.includes('trapezoid')) { items.push(fmt('L', parseFloat(row.length) || 0)); items.push(fmt('L2', parseFloat(row.length_2) || 0)); }
                else items.push(fmt('L', parseFloat(row.length) || 0));
                return `<div class="flex items-center gap-x-3 font-mono text-xs tracking-tight">${items.join('')}</div>`;
            }
        },

        populateFormDropdowns: function(data) {
            const self = this;
            this.elements.customerSelect.empty().append('<option></option>');
            data.customers?.forEach(c => this.elements.customerSelect.append(new Option(c.code, c.id)));
            data.materialSpecs?.forEach(ms => $('#material_spec_id').append(`<option value="${ms.hash_id}">${ms.spec_name}</option>`));
            data.units?.forEach(u => this.elements.unitSelect.append(`<option value="${u.hash_id}">${u.code} - ${u.name}</option>`));
            data.ranks?.forEach(r => $('#rank_id').append(`<option value="${r.hash_id}">${r.code}</option>`));
            this.ui.updateRevisionOptions();
        },

        /**
         * UI UTILITIES
         */
        ui: {
            setBtnLoading: b => b.prop('disabled', true).html('<i class="fa-solid fa-circle-notch fa-spin"></i>'),
            unsetBtnLoading: (b, i) => b.prop('disabled', false).html(i),
            showModal: m => m.removeClass('hidden').addClass('flex').attr('aria-hidden', 'false'),
            hideModal: m => m.addClass('hidden').removeClass('flex').attr('aria-hidden', 'true'),
            clearErrors: () => $('[id^="error-"]').addClass('hidden').text(''),
            resetForm: (title, method, action) => {
                $('#modalTitle').text(title);
                $('#formMethod').val(method);
                $('#productForm').attr('action', action)[0].reset();
                $('#pcs_per_unit, #unit_per_car').val(1);
                $('#min_stock').val(90);
                $('#density').val('7.85');
                $('#material_price').val('20000');
                $('#top_coil').val(500);
                $('#end_coil').val(2500);
                $('#weight_kg, #net_weight, #gross_coil, #net_coil').val('');
                $('#customer_id, #model_id, #material_spec_id, #unit_id, #rank_id, #revision_id, #product_status, #product_status_remark').val('').trigger('change');
                ProductApp.ui.clearErrors();
                ProductApp.ui.toggleDuplicateMode(false);
                ProductApp.ui.toggleUnitFields();
            },
            toggleDuplicateMode: function(isDup) {
                ProductApp.state.isDuplicateMode = isDup;
                const fields = $('#productForm').find('input, select, textarea').not('#model_id, [name="_token"], [name="_method"]');
                fields.prop('disabled', isDup).trigger('change.select2');
                if (isDup) $('#model_id').prop('disabled', false).trigger('change.select2');
            },
            toggleUnitFields: function() {
                const unitId = ProductApp.elements.unitSelect.val();
                const unit = ProductApp.state.dropdownData.units?.find(u => u.hash_id === unitId);
                const name = (unit?.name || '').toLowerCase();
                const isCoil = name.includes('coil');
                
                // NEW: Update Revision Options based on Unit
                const currentRevId = $('#revision_id').val();
                ProductApp.ui.updateRevisionOptions(name, currentRevId);

                $('#lengthContainer, #length2Container, #pitchContainer, #pcsPerPitchContainer, #coilWeightSection').hide();
                $('#pcs_per_unit').prop('readonly', isCoil).toggleClass('bg-gray-100 cursor-not-allowed', isCoil);

                if (name.includes('sheet')) {
                    $('#lengthContainer, #pitchContainer, #pcsPerPitchContainer').show();
                    if (!ProductApp.state.isEditMode && !ProductApp.state.isDuplicateMode) $('#pcs_per_unit').val(1);
                }
                else if (name.includes('trapezoid')) {
                    $('#lengthContainer, #length2Container, #pitchContainer, #pcsPerPitchContainer').show();
                    if (!ProductApp.state.isEditMode && !ProductApp.state.isDuplicateMode) $('#pcs_per_unit').val(1);
                }
                else if (isCoil) {
                    $('#pitchContainer, #pcsPerPitchContainer, #coilWeightSection').show();
                }
                else {
                    $('#lengthContainer').show();
                    if (!ProductApp.state.isEditMode && !ProductApp.state.isDuplicateMode) $('#pcs_per_unit').val(1);
                }
                ProductApp.logic.calculateWeight();
                ProductApp.logic.calculateNetCoil();
            },
            updateRevisionOptions: function(unitName = '', currentSelectedId = null) {
                const $rev = $('#revision_id');
                const revisions = ProductApp.state.dropdownData.revisions || [];
                
                let targetGroup = '';
                if (unitName.includes('coil')) targetGroup = 'RC';
                else if (unitName.includes('sheet') || unitName.includes('trapezoid')) targetGroup = 'R';

                // Find the currently selected revision's basic code (e.g., "1" from "R1" or "RC1")
                let currentCodeBase = '';
                if (currentSelectedId) {
                    const currentRev = revisions.find(r => r.hash_id === currentSelectedId);
                    if (currentRev) {
                        currentCodeBase = currentRev.code.replace(/^(RC|R|C)/, ''); // Extract number/letter after prefix
                    }
                }

                $rev.empty().append('<option value="">Select Revision</option>');
                
                revisions.forEach(rev => {
                    // Filter: Only show if it matches the target group
                    // Exception: Keep current if we are in Edit mode and it was already там (prevents data loss)
                    if (!targetGroup || rev.group_name === targetGroup || (ProductApp.state.isEditMode && rev.hash_id === currentSelectedId)) {
                        $rev.append(`<option value="${rev.hash_id}">${rev.code}</option>`);
                    }
                });

                // Logic to CHANGE revision if it doesn't match the new group
                if (targetGroup) {
                    const currentRevObj = revisions.find(r => r.hash_id === currentSelectedId);
                    
                    // If current revision doesn't belong to the new target group
                    if (!currentRevObj || currentRevObj.group_name !== targetGroup) {
                        // Try to find matching code base in the new group (e.g., R1 -> RC1)
                        let matchedRev = revisions.find(r => r.group_name === targetGroup && r.code.endsWith(currentCodeBase));
                        
                        // If no perfect match, just take the first one in the new group
                        if (!matchedRev) matchedRev = revisions.find(r => r.group_name === targetGroup);
                        
                        if (matchedRev) {
                            $rev.val(matchedRev.hash_id).trigger('change.select2');
                            return;
                        }
                    }
                }

                // Default fallback: Restore previous selection if it's still valid
                if (currentSelectedId) $rev.val(currentSelectedId).trigger('change.select2');
            }
        },

        initImportSelect2: function() {
            $('.select2-import').select2({
                dropdownParent: $('#importModal'),
                width: '100%',
                placeholder: 'Select...'
            });

            // Initialize Model with AJAX (Lazy Loading)
            $('#import_model_id').select2({
                dropdownParent: $('#importModal'),
                width: '100%',
                placeholder: 'Select Model...',
                ajax: {
                    url: this.config.routes.models,
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            customer_id: $('#import_customer_id').val(),
                            q: params.term
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.map(m => ({ id: m.id, text: m.name }))
                        };
                    },
                    cache: true
                }
            });
        }
    };

    ProductApp.init();
});
</script>
@endpush
