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
                                <select name="revision" id="revision" required class="bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-xs focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                    <option value="">Select Revision</option>
                                    <option value="R">R</option>
                                    <option value="R1">R1</option>
                                    <option value="R2">R2</option>
                                    <option value="R3">R3</option>
                                    <option value="RC">RC</option>
                                    <option value="RC1">RC1</option>
                                    <option value="RC2">RC2</option>
                                </select>
                                <p id="error-revision" class="text-red-500 text-[10px] mt-1 hidden font-bold uppercase tracking-wide"><i class="fa-solid fa-circle-exclamation mr-1"></i> Required</p>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(function() {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        let dropdownData = {};
        let isEditMode = false;
        let isDuplicateMode = false;

        function toggleDuplicateMode(isDuplicate) {
            isDuplicateMode = isDuplicate;
            const fields = $('#productForm').find('input, select, textarea').not('#model_id, [name="_token"], [name="_method"]');
            
            fields.prop('disabled', isDuplicate);
            
            $('#productForm').find('select.select2-hidden-accessible').not('#model_id').each(function() {
                $(this).trigger('change.select2');
            });

            $('#weight_kg').prop('disabled', true);
            $('#min_stock').prop('disabled', true);
            
            if (isDuplicate) {
                $('#model_id').prop('disabled', false).trigger('change.select2');
            }
        }

        function initFormSelect2() {
            $('#customer_id, #model_id, #material_spec_id, #unit_id, #rank_id').select2({
                dropdownParent: $('#formModal'),
                width: '100%',
                placeholder: 'Select...',
            });
        }
        
        const routeCustomers = '{{ route("inventory.master.product.getCustomers") }}';
        const routeModels = '{{ route("inventory.master.product.getModels") }}';

        function populateCustomers() {
            const el = $('#customer_id');
            el.empty().append('<option></option>');
            if (dropdownData.customers) {
                dropdownData.customers.forEach(c => {
                    el.append(new Option(c.code, c.id));
                });
            }
        }

        const modelCache = {};

        let modelLoadPromise = Promise.resolve();
        let isAutoFilling = false;

        function loadModels(customerId) {
            const el = $('#model_id');
            el.prop('disabled', true).empty().append('<option></option>').trigger('change');

            if (!customerId) {
                 return Promise.resolve();
            }

            if (modelCache[customerId]) {
                modelCache[customerId].forEach(m => {
                    el.append(new Option(m.name, m.id));
                });
                el.prop('disabled', false);
                return Promise.resolve();
            }

            return new Promise((resolve, reject) => {
                $.get(routeModels, { customer_id: customerId })
                    .done(function(models) {
                        modelCache[customerId] = models;
                        models.forEach(m => {
                            el.append(new Option(m.name, m.id));
                        });
                        el.prop('disabled', false);
                        resolve();
                    })
                    .fail(reject);
            });
        }

        $('#customer_id').on('change', function() {
            const customerId = this.value;

            // reset product only if not auto-filling
            if (!isAutoFilling) {
                $('#product_id').val(null).trigger('change');
            }

            modelLoadPromise = loadModels(customerId);
        });

        function initProductSelect2() {
            if ($('#product_id').data('select2')) {
                $('#product_id').select2('destroy');
            }

            $('#product_id').select2({
                dropdownParent: $('#formModal'),
                width: '100%',
                minimumInputLength: 0,
                placeholder: 'Search Product...',
                ajax: {
                    url: '{{ route("inventory.master.product.getProducts") }}',
                    dataType: 'json',
                    delay: 250,
                    data: p => ({
                        q: p.term || '',
                        model_id: $('#model_id').val(),
                        customer_id: $('#customer_id').val()
                    }),
                    processResults: d => ({
                        results: d.results,
                        pagination: d.pagination
                    })
                }
            });
        }

        $('#product_id').on('select2:select', function(e) {
            const data = e.params.data;
            const currentCust = $('#customer_id').val();
            
            // Auto-fill customer and model
            if (data.customer_id && data.customer_id != currentCust) {
                isAutoFilling = true;
                $('#customer_id').val(data.customer_id).trigger('change');
                isAutoFilling = false;
                
                // Wait for models to load then set model
                modelLoadPromise.then(() => {
                    if (data.model_id) {
                         $('#model_id').val(data.model_id).trigger('change');
                    }
                });
            } else if (data.model_id) {
                // If customer was already same, just set model if not set
                if (!$('#model_id').val() || $('#model_id').val() != data.model_id) {
                     $('#model_id').val(data.model_id).trigger('change');
                }
            }
        });

        $('#model_id').on('change', function() {
             // Model change logic
        });


        // Toast helper
        function toast(icon, title, text) {
            const isDark = document.documentElement.classList.contains('dark');
            const theme = isDark ? {
                bg: 'rgba(30, 41, 59, 0.95)',
                fg: '#E5E7EB',
                icon: {
                    success: '#22c55e',
                    error: '#ef4444'
                }
            } : {
                bg: 'rgba(255, 255, 255, 0.98)',
                fg: '#0f172a',
                icon: {
                    success: '#16a34a',
                    error: '#dc2626'
                }
            };
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2600,
                timerProgressBar: true,
                icon,
                title,
                text,
                iconColor: theme.icon[icon],
                background: theme.bg,
                color: theme.fg
            });
        }

        // Load dropdown data
        $.get('{{ route("inventory.master.product.dropdownData") }}', function(data) {
            dropdownData = data;
            
            populateCustomers();

            data.materialSpecs.forEach(ms => {
                $('#material_spec_id').append(`<option value="${ms.hash_id}">${ms.spec_name}</option>`);
            });

            data.units.forEach(u => {
                $('#unit_id').append(`<option value="${u.hash_id}">${u.code} - ${u.name}</option>`);
            });

            data.ranks.forEach(r => {
                $('#rank_id').append(`<option value="${r.hash_id}">${r.code}</option>`);
            });
        });







        // DataTable
        const table = window.defaultDataTable('#inventoryProductTable', {
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("inventory.master.product.data") }}',
                type: 'GET',
                data: d => {
                    d.search = d.search.value;
                }
            },
            columns: [{
                    data: null,
                    className: 'text-center',
                    render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1
                },
                {
                    data: 'part_no',
                    className: 'whitespace-nowrap font-medium text-slate-700 dark:text-gray-200'
                },
                {
                    data: 'customer',
                    className: 'text-center'
                },
                {
                    data: 'model',
                    className: 'text-center'
                },
                {
                    data: 'product_status',
                    className: 'text-center',
                    render: (d, t, r) => {
                        const status = d || r.model_project_status || 'Project';
                        const config = {
                            'Project': 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800/50',
                            'Regular': 'bg-primary-50 text-primary-700 border-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:border-primary-800/50',
                            'Allsize OK': 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/50',
                            'Allsize NG': 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800/50'
                        };
                        const colors = config[status] || 'bg-gray-50 text-gray-600 border-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700';

                        const overrideLabel = d ? ' <span class="text-[8px] opacity-50 underline">(O)</span>' : '';
                        return `<span class="px-2 py-1.5 rounded-xs text-[10px] font-bold uppercase tracking-wide border ${colors}">${status}${overrideLabel}</span>`;
                    }
                },
                {
                    data: null,
                    render: row => `
                        <div class="leading-snug">
                            <div class="font-medium text-slate-700 dark:text-slate-300">${row.material_spec}</div>
                            <div class="text-[10px] text-gray-400 uppercase tracking-tight">${row.coating_type || '-'}</div>
                        </div>
                    `
                },
                {
                    data: null,
                    className: 'whitespace-nowrap',
                    render: row => {
                        // Technical Dimension Format with Unit Logic
                        const t = parseFloat(row.thickness) || 0;
                        const w = parseFloat(row.width) || 0;
                        const l = parseFloat(row.length) || 0;
                        const l2 = parseFloat(row.length_2) || 0;
                        const p = parseFloat(row.pitch) || 0;
                        const unit = (row.unit_name || '').toLowerCase();

                        const fmt = (lbl, val) => `
                            <span class="inline-flex items-center gap-x-0.5">
                                <span class="text-gray-500 dark:text-gray-400 font-bold">${lbl}:</span>
                                <span class="text-slate-800 dark:text-gray-200 font-medium">${val}</span>
                            </span>
                        `;
                        
                        let items = [];
                        items.push(fmt('T', t));
                        items.push(fmt('W', w));
                        
                        if (unit.includes('coil')) {
                            items.push(fmt('P', p));
                        } else if (unit.includes('trapezoid')) {
                            items.push(fmt('L', l));
                            items.push(fmt('L2', l2));
                        } else {
                            // Default/Sheet
                            items.push(fmt('L', l));
                        }

                        return `
                            <div class="flex items-center gap-x-3 font-mono text-xs tracking-tight">
                                ${items.join('')}
                            </div>
                        `;
                    }
                },
                { data: 'pcs_per_unit', className: 'text-center' },
                {
                    data: 'weight_kg',
                    className: 'text-center',
                    render: d => d ? parseFloat(d).toFixed(2) : '-'
                },
                { data: 'unit_per_car', className: 'text-center' },
                { data: 'rank', className: 'text-center' },
                {
                    data: 'remark',
                    className: 'text-xs text-gray-500',
                    render: d => d || '-'
                },
                {
                    data: 'updated_at',
                    className: 'whitespace-nowrap text-[10px] text-gray-400',
                    render: d => d || '-'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    width: '100px',
                    render: row => `
                    <div class="flex items-center justify-center gap-1.5">
                        <button class="print-button h-8 w-8 inline-flex items-center justify-center text-green-600 rounded-xs bg-green-50 hover:bg-green-100 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/30 transition-colors" data-id="${row.id}" title="Print">
                            <i class="fa-solid fa-print text-sm"></i>
                        </button>
                        <button class="edit-button h-8 w-8 inline-flex items-center justify-center text-primary-600 rounded-xs bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:hover:bg-primary-900/30 transition-colors" data-id="${row.id}" title="Edit">
                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                        </button>
                        <button class="duplicate-button h-8 w-8 inline-flex items-center justify-center text-amber-600 rounded-xs bg-amber-50 hover:bg-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:hover:bg-amber-900/30 transition-colors" data-id="${row.id}" title="Duplicate">
                            <i class="fa-solid fa-copy text-sm"></i>
                        </button>
                        <button class="delete-button h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-xs bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors" data-id="${row.id}" title="Delete">
                            <i class="fa-solid fa-trash-can text-sm"></i>
                        </button>
                    </div>`
                }
            ],
            order: [
                [0, 'desc']
            ]
        });

        // Modal helpers
        function showModal(m) {
            m.removeClass('hidden').addClass('flex');
        }

        function hideModal(m) {
            m.addClass('hidden').removeClass('flex');
        }

        const formModal = $('#formModal');
        const deleteModal = $('#modal-delete');
        let deleteId = null;

        // Add button
        $('#add-button').on('click', function() {
            initFormSelect2();
            isEditMode = false;
            $('#modalTitle').text('Add Inventory Product');
            $('#formMethod').val('POST');
            $('#productForm').attr('action', '{{ route("inventory.master.product.store") }}');
            $('#productForm')[0].reset();
            $('#pcs_per_unit').val(1);
            $('#unit_per_car').val(1);
            $('#min_stock').val(90);
            $('#density').val('7.85');
            $('#weight_kg').val('');
            $('#net_weight').val('');
            $('#material_price').val('20000');
            if ($('#product_id').data('select2')) {
                $('#product_id').select2('destroy');
            }
            $('#product_id')
                .val(null)
                .prop('disabled', false);
            
            initProductSelect2();
            $('#unit_id').val('').trigger('change');
            $('[id^="error-"]').addClass('hidden').text('');
            toggleDuplicateMode(false);
            toggleUnitFields();
            showModal(formModal);
        });

        // Close modals
        $('.close-modal-button, .close-modal').on('click', function() {
            hideModal($(this).closest('.modal-container, [tabindex="-1"]'));
        });

        // Submit form
        $('#productForm').on('submit', function(e) {
            e.preventDefault();
            $('[id^="error-"]').addClass('hidden').text('');

            // Temporarily enable fields to capture data
            const wasDuplicate = isDuplicateMode;
            if (wasDuplicate) toggleDuplicateMode(false);
            
            const formData = new FormData(this);
            
            if (wasDuplicate) toggleDuplicateMode(true);

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: formData,
                processData: false,
                contentType: false,
                success: (res) => {
                    if (res.success) {
                        table.ajax.reload();
                        hideModal(formModal);
                        toast('success', 'Success', res.message);
                    }
                },
                error: (xhr) => {
                    const errors = xhr.responseJSON?.errors || {};
                    Object.keys(errors).forEach(key => {
                        $(`#error-${key}`).text(errors[key][0]).removeClass('hidden');
                    });
                    toast('error', 'Error', xhr.responseJSON?.message || 'Operation failed');
                }
            });
        });

        // Duplicate button logic
        $(document).on('click', '.duplicate-button', function() {
            initProductSelect2();
            const id = $(this).data('id');
            isEditMode = false; // It's a new record
            $('#modalTitle').text('Duplicate Inventory Product');
            $('#formMethod').val('POST');
            $('#productForm').attr('action', '{{ route("inventory.master.product.store") }}');
            
            toggleDuplicateMode(true);
            $('[id^="error-"]').addClass('hidden').text('');

            $.get(`{{ url('inventory/master/product') }}/${id}`, function(data) {
                let chain = Promise.resolve();

                if (data.product?.customer_id) {
                    $('#customer_id').val(data.product.customer_id).trigger('change');
                    chain = modelLoadPromise;
                }

                chain.then(() => {
                    // We DON'T pre-fill model_id to force user to choose a NEW model
                    $('#model_id').val(null).trigger('change');

                    // Pre-fill Product Select2
                    if (data.product) {
                        const productText = `${data.product.part_no} - ${data.product.part_name}`;
                        const newOption = new Option(productText, data.product.hash_id || data.product_id, true, true);
                        isAutoFilling = true;
                        $('#product_id').append(newOption).trigger('change');
                        isAutoFilling = false;
                    }
                });

                // Fill other specs
                $('#revision').val(data.revision).trigger('change');
                $('#material_spec_id').val(data.material_spec ? data.material_spec.hash_id : '').trigger('change');
                $('#thickness').val(parseFloat(data.thickness || 0));
                $('#width').val(parseFloat(data.width || 0));
                $('#length').val(parseFloat(data.length || 0));
                $('#length_2').val(parseFloat(data.length_2 || 0));
                $('#pitch').val(parseFloat(data.pitch || 0));
                $('#unit_id').val(data.unit ? data.unit.hash_id : '').trigger('change');
                $('#rank_id').val(data.rank ? data.rank.hash_id : '').trigger('change');
                $('#pcs_per_unit').val(data.pcs_per_unit);
                $('#unit_per_car').val(data.unit_per_car);
                $('#min_stock').val(data.min_stock);
                $('#density').val(parseFloat(data.density || 7.85));
                $('#weight_kg').val(parseFloat(data.weight_kg || 0));
                $('#net_weight').val(parseFloat(data.net_weight || 0));
                $('#material_price').val(parseFloat(data.material_price || 20000));
                $('#remark').val(data.remark);

                $('#product_status').val(data.product_status).trigger('change');
                $('#product_status_remark').val(data.product_status_remark).trigger('change');

                toggleUnitFields();
                showModal(formModal);
                
                toast('info', 'Data Copied', 'Please select a NEW model to finish duplication.');
            });
        });

        // Edit button
        $(document).on('click', '.edit-button', function() {
            initProductSelect2(); // Initialize first
            const id = $(this).data('id');
            isEditMode = true;
            $('#modalTitle').text('Edit Inventory Product');
            $('#formMethod').val('PUT');
            $('#productForm').attr('action', `{{ url('inventory/master/product') }}/${id}`);
            
            // Initial state: Product enabled (since we allow auto-fill) but we will set it specially
            if ($('#product_id').data('select2')) {
                $('#product_id').val(null).trigger('change');
            }
            $('[id^="error-"]').addClass('hidden').text('');

            $.get(`{{ url('inventory/master/product') }}/${id}`, function(data) {
                let chain = Promise.resolve();

                if (data.product?.customer_id) {
                    $('#customer_id').val(data.product.customer_id).trigger('change');
                    chain = modelLoadPromise;
                }

                chain.then(() => {
                    if (data.model_id) {
                         $('#model_id').val(data.model_id).trigger('change');
                    }

                    // Pre-fill Product Select2
                    if (data.product) {
                        const productText = `${data.product.part_no} - ${data.product.part_name}`;
                        const newOption = new Option(productText, data.product.hash_id || data.product_id, true, true);
                        isAutoFilling = true;
                        $('#product_id').append(newOption).trigger('change');
                        isAutoFilling = false;
                    }
                });

                $('#revision').val(data.revision).trigger('change');
                $('#material_spec_id').val(data.material_spec ? data.material_spec.hash_id : '').trigger('change');
                $('#thickness').val(parseFloat(data.thickness || 0));
                $('#width').val(parseFloat(data.width || 0));
                $('#length').val(parseFloat(data.length || 0));
                $('#length_2').val(parseFloat(data.length_2 || 0));
                $('#pitch').val(parseFloat(data.pitch || 0));
                $('#unit_id').val(data.unit ? data.unit.hash_id : '').trigger('change');
                $('#rank_id').val(data.rank ? data.rank.hash_id : '').trigger('change');
                $('#pcs_per_unit').val(data.pcs_per_unit);
                $('#unit_per_car').val(data.unit_per_car);
                $('#min_stock').val(data.min_stock);
                $('#density').val(parseFloat(data.density || 7.85));
                $('#weight_kg').val(parseFloat(data.weight_kg || 0));
                $('#net_weight').val(parseFloat(data.net_weight || 0));
                $('#material_price').val(parseFloat(data.material_price || 20000));
                $('#remark').val(data.remark);

                $('#product_status').val(data.product_status).trigger('change');
                $('#product_status_remark').val(data.product_status_remark).trigger('change');

                toggleDuplicateMode(false);
                toggleUnitFields();
                showModal(formModal);
            });
        });

        // Print button
        $(document).on('click', '.print-button', function() {
            const id = $(this).data('id');
            window.open(`{{ url('inventory/master/product') }}/${id}/print`, '_blank');
        });


        // Toggle Unit fields visibility and Visibility of Fields based on Unit Type
        function toggleUnitFields() {
            const unitId = $('#unit_id').val();
            const selectedUnit = dropdownData.units ? dropdownData.units.find(u => u.hash_id === unitId) : null;
            const unitName = selectedUnit ? selectedUnit.name.toLowerCase() : '';

            // Reset all visibility first
            $('#lengthContainer').hide();
            $('#length2Container').hide();
            $('#pitchContainer').hide();

            // Logic Visibility
            if (unitName.includes('sheet')) {
                // Requirement said "Sheet: Hide L2, Pitch. Show Length"
                $('#lengthContainer').show();
                $('#length2Container').hide();
                $('#pitchContainer').hide();
            } else if (unitName.includes('trapezoid')) {
                // Trapezoid: Show Length, Length 2. Hide Pitch.
                $('#lengthContainer').show();
                $('#length2Container').show();
                $('#pitchContainer').hide();
            } else if (unitName.includes('coil')) {
                // Coil: Show Pitch. Hide Length, Length 2.
                $('#lengthContainer').hide();
                $('#length2Container').hide();
                $('#pitchContainer').show();
            } else {
                // Default if unknown or empty: Show Length.
                $('#lengthContainer').show();
            }

            // Trigger calculation when unit changes
            calculateWeight();
        }

        $('#unit_id').on('change', function() {
            toggleUnitFields();
        });

        // Auto-calculate Weight
        // Formula:
        // Sheet: (T x W x L x Density) / 1,000,000
        // Coil: (T x W x Pitch x Density) / 1,000,000
        // Trapezoid: (T x W x ((L + L2) / 2) x Density) / 1,000,000

        const inputsForCalc = ['#thickness', '#width', '#length', '#length_2', '#pitch', '#density'];
        $(inputsForCalc.join(', ')).on('input change', calculateWeight);

        function calculateWeight() {
            const unitId = $('#unit_id').val();
            const selectedUnit = dropdownData.units ? dropdownData.units.find(u => u.hash_id === unitId) : null;
            const unitName = selectedUnit ? selectedUnit.name.toLowerCase() : '';

            const t = parseFloat($('#thickness').val()) || 0;
            const w = parseFloat($('#width').val()) || 0;
            const density = parseFloat($('#density').val()) || 0;

            let weight = 0;

            if (unitName.includes('sheet')) {
                const l = parseFloat($('#length').val()) || 0;
                weight = (t * w * l * density) / 1000000;
            } else if (unitName.includes('coil')) {
                const p = parseFloat($('#pitch').val()) || 0;
                weight = (t * w * p * density) / 1000000;
            } else if (unitName.includes('trapezoid')) {
                const l = parseFloat($('#length').val()) || 0;
                const l2 = parseFloat($('#length_2').val()) || 0;
                const avgL = (l + l2) / 2;
                weight = (t * w * avgL * density) / 1000000;
            } else {
                // Fallback default (Sheet logic) if unit not selected or known
                const l = parseFloat($('#length').val()) || 0;
                weight = (t * w * l * density) / 1000000;
            }

            $('#weight_kg').val(weight > 0 ? weight.toFixed(3) : '');
        }

        // Auto-calculate min stock
        function calculateMinStock() {
            const upc = parseInt($('#unit_per_car').val()) || 0;
            $('#min_stock').val(upc * 90);
        }

        $('#unit_per_car').on('input change', calculateMinStock);

        // Delete button
        $(document).on('click', '.delete-button', function() {
            deleteId = $(this).data('id');
            showModal(deleteModal);
        });

        // Confirm delete
        $('#confirmDelete').on('click', function() {
            if (!deleteId) return;

            $.ajax({
                url: `{{ url('inventory/master/product') }}/${deleteId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: (res) => {
                    if (res.success) {
                        table.ajax.reload();
                        hideModal(deleteModal);
                        deleteId = null;
                        toast('success', 'Success', res.message);
                    }
                },
                error: (xhr) => {
                    toast('error', 'Error', xhr.responseJSON?.message || 'Delete failed');
                }
            });
        });
    });
</script>
@endpush
