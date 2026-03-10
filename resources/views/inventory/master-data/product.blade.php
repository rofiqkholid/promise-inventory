@extends('layouts.app')
@section('title', 'Inventory Product Management')
@section('page_title', 'Product Master')
@section('header-title', 'Inventory Product')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">Inventory Product</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Manage inventory product details.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button type="button" id="add-button" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest active:scale-[0.98] transition-all">
                <i class="fa-solid fa-plus"></i>
                Add New
            </button>
        </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="mb-8 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-gray-700 bg-slate-50/50 dark:bg-slate-900/30">
            <h3 class="text-[10px] font-bold text-gray-900 dark:text-white flex items-center gap-3 uppercase tracking-[0.15em]">
                <i class="fa-solid fa-filter text-primary-600"></i> Product Filter
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-end">
                {{-- CUSTOMER --}}
                <div class="w-full">
                    <label class="block mb-2 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Customer</label>
                    <select id="filterCustomer" class="select2-filter w-full">
                        <option value="">All Customers</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->code }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- MODEL --}}
                <div class="w-full">
                    <label class="block mb-2 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Model</label>
                    <select id="filterModel" class="select2-filter w-full">
                        <option value="">All Models</option>
                        @foreach($models as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- PART NUMBER (SELECT2 AJAX) --}}
                <div class="w-full lg:col-span-1">
                    <label class="block mb-2 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Part Number</label>
                    <select id="filterPartNo" class="w-full">
                        <option value="">All Part Numbers</option>
                    </select>
                </div>

                {{-- ACTIONS --}}
                <div class="flex items-center w-full">
                    <button type="button" id="btnResetFilter" class="w-full h-10 px-4 text-[10px] font-bold text-gray-500 hover:text-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xs border border-slate-100 dark:border-gray-700 transition-all uppercase tracking-widest active:scale-95">
                        <i class="fa-solid fa-rotate-left mr-1"></i> Reset Filter
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
                <th class="text-left">Dimensions</th>
                <th class="text-center">Pcs/Unit</th>
                <th class="text-center">Weight (Kg)</th>
                <th class="text-center">Unit/Car</th>
                <th class="text-center">Rank</th>
                <th class="text-left">Remark</th>
                <th class="text-center whitespace-nowrap">Updated At</th>
                <th class="text-center w-[100px]">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

{{-- Add/Edit Modal --}}
<div id="formModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 justify-center items-center w-full h-full bg-slate-950/60 flex">
    <div class="relative p-4 w-full max-w-4xl max-h-[95vh] h-full md:h-auto">
        <div class="relative bg-white rounded-xs shadow-2xl dark:bg-gray-800 flex flex-col max-h-[90vh] overflow-hidden">
            <button type="button" class="close-modal-button text-gray-400 absolute top-3 right-3 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded-xs text-sm p-2 ml-auto inline-flex items-center dark:hover:bg-gray-700 dark:hover:text-white z-10 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-primary-50/80 dark:bg-slate-900/50">
                <h3 class="text-base font-bold text-slate-900 dark:text-white uppercase tracking-widest" id="modalTitle">Add Inventory Product</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium">Enter product details and specifications</p>
            </div>

            <form id="productForm" method="POST" class="flex flex-col h-full overflow-hidden min-h-0">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="p-6 overflow-y-auto min-h-0 flex-1 space-y-8 custom-scrollbar">
                    {{-- Product Information Section --}}
                    <div>
                        <h4 class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-2">
                            <i class="fa-solid fa-circle-info text-primary-500"></i>
                            Product Information
                        </h4>
                        <div class="grid gap-6 md:grid-cols-4">
                            {{-- CUSTOMER --}}
                            <div>
                                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Customer<span class="text-red-500">*</span></label>
                                <select id="customer_id" class="select2 w-full bg-gray-50 border border-slate-200 text-gray-900 text-xs font-semibold rounded-xs focus:ring-slate-500 focus:border-slate-500 block p-2.5 h-10 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                    <option></option>
                                </select>
                            </div>

                            {{-- MODEL --}}
                            <div>
                                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Model<span class="text-red-500">*</span></label>
                                <select name="model_id" id="model_id" class="select2 w-full bg-gray-50 border border-slate-200 text-gray-900 text-xs font-semibold rounded-xs focus:ring-slate-500 focus:border-slate-500 block p-2.5 h-10 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all" disabled>
                                    <option></option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Product Name <span class="text-red-500">*</span></label>
                                <select name="product_id" id="product_id" required class="bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                    <option value="">Search Product...</option>
                                </select>
                                <p id="error-product_id" class="text-red-500 text-[10px] mt-1 hidden font-bold uppercase tracking-wide"><i class="fa-solid fa-circle-exclamation mr-1"></i> Required</p>
                            </div>
                            <div>
                                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Revision <span class="text-red-500">*</span></label>
                                <select name="revision_id" id="revision_id" required class="bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                    <option value="">Select Revision</option>
                                </select>
                                <p id="error-revision_id" class="text-red-500 text-[10px] mt-1 hidden font-bold uppercase tracking-wide"><i class="fa-solid fa-circle-exclamation mr-1"></i> Required</p>
                            </div>

                            <div>
                                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Material Spec</label>
                                 <select name="material_spec_id" id="material_spec_id" class="select2 bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                    <option value="">Select Material Spec</option>
                                </select>
                                <p id="error-material_spec_id" class="text-red-500 text-[10px] mt-1 hidden font-bold uppercase tracking-wide"><i class="fa-solid fa-circle-exclamation mr-1"></i> Check Input</p>
                            </div>



                            {{-- PRODUCT STATUS --}}
                            <div>
                                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Product Status Override</label>
                                <select name="product_status" id="product_status" class="bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                    <option value="">None (Follow Model)</option>
                                    <option value="Allsize OK">Allsize OK</option>
                                    <option value="Allsize NG">Allsize NG</option>
                                </select>
                            </div>

                            {{-- PRODUCT STATUS REMARK --}}
                            <div>
                                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Status Remark</label>
                                <select name="product_status_remark" id="product_status_remark" class="bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                    <option value="">No Remark</option>
                                    <option value="Damage">Damage</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Unit & Dimensions Section --}}
                    <div>
                        <h4 class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-2">
                            <i class="fa-solid fa-ruler-combined text-primary-500"></i>
                            Unit & Dimensions
                        </h4>

                        {{-- Unit Selection (First as requested) --}}
                        <div class="mb-4 max-w-sm">
                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Unit <span class="text-[9px] text-gray-300 font-normal normal-case tracking-normal ml-1">(Determines visible dimensions)</span></label>
                            <select name="unit_id" id="unit_id" class="select2 bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                <option value="">Select Unit</option>
                            </select>
                            <p id="error-unit_id" class="text-red-500 text-[10px] mt-1 hidden font-bold uppercase tracking-wide"><i class="fa-solid fa-circle-exclamation mr-1"></i> Check Input</p>
                        </div>

                        {{-- Dimensions Grid --}}
                        <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-6 bg-primary-50/50 dark:bg-gray-700/30 p-4 rounded-xs border border-slate-100 dark:border-gray-700">
                            <div class="lg:col-span-1">
                                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Thickness</label>
                                <input type="number" name="thickness" id="thickness" step="0.01" min="0" class="bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all" placeholder="0.00">
                                <p id="error-thickness" class="text-red-500 text-[10px] mt-1 hidden font-bold uppercase tracking-wide"></p>
                            </div>
                            <div class="lg:col-span-1">
                                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Width</label>
                                <input type="number" name="width" id="width" step="0.01" min="0" class="bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all" placeholder="0.00">
                                <p id="error-width" class="text-red-500 text-[10px] mt-1 hidden font-bold uppercase tracking-wide"></p>
                            </div>

                            {{-- Dynamic Fields --}}
                            <div id="lengthContainer" class="lg:col-span-1">
                                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Length</label>
                                <input type="number" name="length" id="length" step="0.01" min="0" class="bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all" placeholder="0.00">
                                <p id="error-length" class="text-red-500 text-[10px] mt-1 hidden font-bold uppercase tracking-wide"></p>
                            </div>
                            <div id="length2Container" class="lg:col-span-1">
                                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Length 2</label>
                                <input type="number" name="length_2" id="length_2" step="0.01" min="0" class="bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all" placeholder="0.00">
                                <p id="error-length_2" class="text-red-500 text-[10px] mt-1 hidden font-bold uppercase tracking-wide"></p>
                            </div>
                            <div id="pitchContainer" class="lg:col-span-1">
                                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Pitch</label>
                                <input type="number" name="pitch" id="pitch" step="0.01" min="0" class="bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all" placeholder="0.00">
                                <p id="error-pitch" class="text-red-500 text-[10px] mt-1 hidden font-bold uppercase tracking-wide"></p>
                            </div>

                            <div class="lg:col-span-1">
                                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Density</label>
                                <input type="number" name="density" id="density" step="0.001" min="0" value="7.85" class="bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all" placeholder="7.85">
                                <p id="error-density" class="text-red-500 text-[10px] mt-1 hidden font-bold uppercase tracking-wide"></p>
                            </div>
                            <div class="lg:col-span-1">
                                <label class="block mb-2 text-[10px] font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-wider">Weight (Kg)</label>
                                <input type="number" name="weight_kg" id="weight_kg" step="0.001" min="0" readonly class="bg-primary-50 border border-primary-100 text-primary-600 text-xs font-bold rounded-xs block w-full h-10 px-3 dark:bg-primary-900/20 dark:border-primary-800 dark:text-primary-300 cursor-not-allowed" placeholder="0.000">
                                <p id="error-weight_kg" class="text-red-500 text-[10px] mt-1 hidden font-bold uppercase tracking-wide"></p>
                            </div>
                            <div class="lg:col-span-1">
                                <label class="block mb-2 text-[10px] font-semibold text-purple-700 dark:text-purple-400 uppercase tracking-wider">Net Weight (Kg)</label>
                                <input type="number" name="net_weight" id="net_weight" step="0.001" min="0" class="bg-purple-50/50 border border-purple-200 text-purple-700 text-xs font-bold rounded-xs focus:ring-purple-500 focus:border-purple-500 block w-full h-10 px-3 dark:bg-purple-900/20 dark:border-purple-800 dark:text-purple-300 transition-all" placeholder="0.000">
                                <p id="error-net_weight" class="text-red-500 text-[10px] mt-1 hidden font-bold uppercase tracking-wide"></p>
                            </div>
                            <div class="lg:col-span-1">
                                <label class="block mb-2 text-[10px] font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Material Price</label>
                                <input type="number" name="material_price" id="material_price" step="0.01" min="0" value="20000" class="bg-emerald-50/50 border border-emerald-200 text-emerald-700 text-xs font-bold rounded-xs focus:ring-emerald-500 focus:border-emerald-500 block w-full h-10 px-3 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-300 transition-all" placeholder="0.00">
                                <p id="error-material_price" class="text-red-500 text-[10px] mt-1 hidden font-bold uppercase tracking-wide"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Inventory Control & Logistics Section --}}
                    <div>
                        <h4 class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-2">
                            <i class="fa-solid fa-boxes-stacked text-primary-500"></i>
                            Logistics & Control
                        </h4>
                        <div class="grid gap-6 md:grid-cols-4">
                            <div>
                                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Rank</label>
                                <select name="rank_id" id="rank_id" class="select2 bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                    <option value="">Select Rank</option>
                                </select>
                                <p id="error-rank_id" class="text-red-500 text-[10px] mt-1 hidden font-bold uppercase tracking-wide"></p>
                            </div>
                            <div>
                                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Pcs per Unit</label>
                                <input type="number" name="pcs_per_unit" id="pcs_per_unit" min="1" value="1" class="bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                <p id="error-pcs_per_unit" class="text-red-500 text-[10px] mt-1 hidden font-bold uppercase tracking-wide"></p>
                            </div>
                            <div>
                                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Unit per Car</label>
                                <input type="number" name="unit_per_car" id="unit_per_car" min="1" value="1" class="bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                <p id="error-unit_per_car" class="text-red-500 text-[10px] mt-1 hidden font-bold uppercase tracking-wide"></p>
                            </div>
                            <div>
                                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Min Stock</label>
                                <input type="number" name="min_stock" id="min_stock" min="0" value="0" readonly class="bg-gray-100 border border-slate-200 text-gray-500 text-xs font-bold rounded-xs focus:outline-none block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white cursor-not-allowed">
                                <p id="error-min_stock" class="text-red-500 text-[10px] mt-1 hidden font-bold uppercase tracking-wide"></p>
                            </div>
                            <div class="md:col-span-4">
                                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Remark</label>
                                <textarea name="remark" id="remark" rows="1" class="block w-full text-xs font-medium text-gray-900 bg-white rounded-xs border border-slate-200 focus:ring-slate-500 focus:border-slate-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all placeholder-gray-300 py-3 px-3 min-h-[42px]" placeholder="Optional notes..."></textarea>
                                <p id="error-remark" class="text-red-500 text-[10px] mt-1 hidden font-bold uppercase tracking-wide"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-none flex items-center justify-end gap-3 px-8 py-5 border-t border-gray-100 dark:border-gray-700 bg-primary-50/80 dark:bg-slate-900/50">
                    <button type="button" class="close-modal-button text-gray-700 bg-white hover:bg-gray-50 rounded-xs border border-gray-300 text-[10px] font-bold uppercase tracking-wider px-6 py-3 transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="text-white bg-primary-600 hover:bg-primary-700 rounded-xs text-[10px] uppercase tracking-widest px-6 py-3 text-center shadow-lg shadow-primary-500/10 transition-all">
                        <i class="fa-solid fa-save mr-1.5"></i> Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<x-inventory.delete-modal />
@endsection

@push('scripts')
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
                        d.search = d.search.value;
                        d.customer_id = this.elements.customerFilter.val();
                        d.model_id = this.elements.modelFilter.val();
                        d.part_no = this.elements.partNoFilter.val();
                    }
                },
                columns: [
                    { data: null, className: 'text-center', render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1 },
                    { data: 'part_no', className: 'whitespace-nowrap font-medium text-slate-700 dark:text-gray-200' },
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
                                'Allsize OK': 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/50',
                                'Allsize NG': 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800/50'
                            }[status] || 'bg-gray-50 text-gray-600 border-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700';
                            const override = d ? ' <span class="text-[8px] opacity-50 underline">(O)</span>' : '';
                            return `<span class="px-2 py-1.5 rounded-xs text-[10px] font-bold uppercase tracking-wide border ${colors}">${status}${override}</span>`;
                        }
                    },
                    {
                        data: null,
                        render: r => `<div class="leading-snug">
                            <div class="font-medium text-slate-700 dark:text-slate-300">${r.material_spec}</div>
                            <div class="text-[10px] text-gray-400 tracking-tight">${r.coating_type || '-'}</div>
                        </div>`
                    },
                    {
                        data: null,
                        className: 'whitespace-nowrap',
                        render: r => this.logic.renderDimensions(r)
                    },
                    { data: 'pcs_per_unit', className: 'text-center' },
                    { data: 'weight_kg', className: 'text-center', render: d => d ? parseFloat(d).toFixed(2) : '-' },
                    { data: 'unit_per_car', className: 'text-center' },
                    { data: 'rank', className: 'text-center' },
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
            $('#btnResetFilter').on('click', () => this.resetFilters());
        },

        bindFormEvents: function() {
            $('#add-button').on('click', () => this.showAddModal());
            $('.close-modal-button, .close-modal').on('click', e => this.ui.hideModal($(e.currentTarget).closest('[tabindex="-1"]')));
            
            this.elements.customerSelect.on('change', e => this.handleFormCustomerChange(e.target.value));
            this.elements.productSelect.on('select2:select', e => this.handleProductSelect(e.params.data));
            this.elements.unitSelect.on('change', () => this.ui.toggleUnitFields());
            this.elements.form.on('submit', e => this.handleFormSubmit(e));
            
            // Calculations
            $('#unit_per_car').on('input change', () => this.logic.calculateMinStock());
            $('#thickness, #width, #length, #length_2, #pitch, #density').on('input change', () => this.logic.calculateWeight());
        },

        bindTableEvents: function() {
            $(document).on('click', '.edit-button', e => this.showEditModal($(e.currentTarget).data('id')));
            $(document).on('click', '.duplicate-button', e => this.showDuplicateModal($(e.currentTarget).data('id')));
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

        resetFilters: function() {
            this.elements.customerFilter.val(null).trigger('change.select2');
            this.elements.modelFilter.val(null).trigger('change.select2');
            this.elements.partNoFilter.val(null).trigger('change.select2');
            
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
            this.elements.productSelect.prop('disabled', false);
            this.initProductSelect2();
            this.ui.showModal(this.elements.formModal);
        },

        showEditModal: function(id) {
            this.state.isEditMode = true;
            this.state.isDuplicateMode = false;
            this.ui.resetForm('Edit Inventory Product', 'PUT', `${this.config.routes.base}/${id}`);
            this.loadProductData(id);
        },

        showDuplicateModal: function(id) {
            this.state.isEditMode = false;
            this.state.isDuplicateMode = true;
            this.ui.resetForm('Duplicate Inventory Product', 'POST', this.config.routes.store);
            this.ui.toggleDuplicateMode(true);
            this.loadProductData(id, true);
        },

        loadProductData: function(id, isDuplicate = false) {
            $.get(`${this.config.routes.base}/${id}`, (data) => {
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
                    $('#material_spec_id').val(data.material_spec?.hash_id).trigger('change');
                    $('#thickness').val(parseFloat(data.thickness || 0));
                    $('#width').val(parseFloat(data.width || 0));
                    $('#length').val(parseFloat(data.length || 0));
                    $('#length_2').val(parseFloat(data.length_2 || 0));
                    $('#pitch').val(parseFloat(data.pitch || 0));
                    this.elements.unitSelect.val(data.unit?.hash_id).trigger('change');
                    $('#rank_id').val(data.rank?.hash_id).trigger('change');
                    $('#pcs_per_unit').val(data.pcs_per_unit);
                    $('#unit_per_car').val(data.unit_per_car);
                    $('#min_stock').val(data.min_stock);
                    $('#density').val(parseFloat(data.density || 7.85));
                    $('#net_weight').val(parseFloat(data.net_weight || 0));
                    $('#material_price').val(parseFloat(data.material_price || 20000));
                    $('#remark').val(data.remark);
                    $('#product_status').val(data.product_status).trigger('change');
                    $('#product_status_remark').val(data.product_status_remark).trigger('change');

                    this.ui.toggleUnitFields();
                    this.ui.showModal(this.elements.formModal);
                    if (isDuplicate) window.showToast('Please select a NEW model to finish duplication.', 'info');
                });
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
                if (res.exists) {
                    const d = res.data;
                    $('#revision_id').val(res.next_revision_id).trigger('change');
                    $('#material_spec_id').val(res.material_spec_hash).trigger('change');
                    $('#thickness').val(parseFloat(d.thickness || 0));
                    $('#width').val(parseFloat(d.width || 0));
                    $('#length').val(parseFloat(d.length || 0));
                    $('#length_2').val(parseFloat(d.length_2 || 0));
                    $('#pitch').val(parseFloat(d.pitch || 0));
                    this.elements.unitSelect.val(res.unit_hash).trigger('change');
                    $('#rank_id').val(res.rank_hash).trigger('change');
                    $('#pcs_per_unit').val(d.pcs_per_unit);
                    $('#unit_per_car').val(d.unit_per_car);
                    $('#density').val(parseFloat(d.density || 7.85));
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
                    window.showToast(res.message, 'success');
                    if (wasDuplicate) this.ui.toggleDuplicateMode(false);
                },
                error: (xhr) => this.handleAjaxError(xhr)
            });
        },

        handleDelete: function() {
            if (!this.state.deleteId) return;
            $.ajax({
                url: `${this.config.routes.base}/${this.state.deleteId}`,
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': this.config.csrfToken },
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
                let weight = 0;

                if (name.includes('sheet')) weight = (t * w * (parseFloat($('#length').val()) || 0) * d) / 1000000;
                else if (name.includes('coil')) weight = (t * w * (parseFloat($('#pitch').val()) || 0) * d) / 1000000;
                else if (name.includes('trapezoid')) weight = (t * w * (((parseFloat($('#length').val()) || 0) + (parseFloat($('#length_2').val()) || 0)) / 2) * d) / 1000000;
                else weight = (t * w * (parseFloat($('#length').val()) || 0) * d) / 1000000;

                $('#weight_kg').val(weight > 0 ? weight.toFixed(3) : '');
            },

            calculateMinStock: function() {
                $('#min_stock').val((parseInt($('#unit_per_car').val()) || 0) * 90);
            },

            renderDimensions: function(row) {
                const unit = (row.unit_name || '').toLowerCase();
                const fmt = (l, v) => `<span class="inline-flex items-center gap-x-0.5"><span class="text-gray-500 font-bold">${l}:</span><span class="text-slate-800 font-medium">${v}</span></span>`;
                let items = [fmt('T', parseFloat(row.thickness) || 0), fmt('W', parseFloat(row.width) || 0)];
                if (unit.includes('coil')) items.push(fmt('P', parseFloat(row.pitch) || 0));
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
            $('#revision_id').empty().append('<option value="">Select Revision</option>');
            data.revisions?.forEach(rev => $('#revision_id').append(`<option value="${rev.hash_id}">${rev.code}</option>`));
        },

        /**
         * UI UTILITIES
         */
        ui: {
            showModal: m => m.removeClass('hidden').addClass('flex'),
            hideModal: m => m.addClass('hidden').removeClass('flex'),
            clearErrors: () => $('[id^="error-"]').addClass('hidden').text(''),
            resetForm: (title, method, action) => {
                $('#modalTitle').text(title);
                $('#formMethod').val(method);
                $('#productForm').attr('action', action)[0].reset();
                $('#pcs_per_unit, #unit_per_car').val(1);
                $('#min_stock').val(90);
                $('#density').val('7.85');
                $('#material_price').val('20000');
                $('#weight_kg, #net_weight').val('');
                $('#customer_id, #model_id, #material_spec_id, #unit_id, #rank_id, #revision_id, #product_status, #product_status_remark').val('').trigger('change');
                ProductApp.ui.clearErrors();
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
                $('#lengthContainer, #length2Container, #pitchContainer').hide();
                if (name.includes('sheet')) $('#lengthContainer').show();
                else if (name.includes('trapezoid')) $('#lengthContainer, #length2Container').show();
                else if (name.includes('coil')) $('#pitchContainer').show();
                else $('#lengthContainer').show();
                ProductApp.logic.calculateWeight();
            }
        }
    };

    ProductApp.init();
});
</script>
@endpush
