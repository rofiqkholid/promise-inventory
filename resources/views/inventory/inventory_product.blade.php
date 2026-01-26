@extends('layouts.app')
@section('title', 'Inventory Product Management')
@section('page_title', 'Product Master')
@section('header-title', 'Inventory Product')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">Inventory Product</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage inventory product details.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button type="button" id="add-button" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <i class="fa-solid fa-plus"></i>
                Add New
            </button>
        </div>
    </div>

    <x-table id="inventoryProductTable">
        <thead>
            <tr>
                <th scope="col" class="px-4 py-3 w-16 text-center">No</th>
                <th scope="col" class="px-4 py-3 whitespace-nowrap">Part No</th>
                <th scope="col" class="px-4 py-3">Customer</th>
                <th scope="col" class="px-4 py-3">Model</th>
                <th scope="col" class="px-4 py-3 whitespace-nowrap">Material</th>
                <th scope="col" class="px-4 py-3 whitespace-nowrap">Dimensions</th>
                <th scope="col" class="px-4 py-3 text-center">Pcs/Unit</th>
                <th scope="col" class="px-4 py-3 text-center">Weight (Kg)</th>
                <th scope="col" class="px-4 py-3 text-center">Unit/Car</th>
                <th scope="col" class="px-4 py-3 text-center">Rank</th>

                <th scope="col" class="px-4 py-3">Remark</th>
                <th scope="col" class="px-4 py-3 whitespace-nowrap">Updated At</th>
                <th scope="col" class="px-4 py-3 text-center w-[100px]">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

{{-- Add/Edit Modal --}}
<div id="formModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 justify-center items-center w-full h-full bg-slate-900/50 flex">
    <div class="relative p-4 w-full max-w-4xl max-h-[95vh]">
        <div class="relative text-left bg-white rounded-lg shadow dark:bg-gray-800 flex flex-col max-h-[90vh] p-4 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white z-10">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
            </button>
            <h3 class="text-xl text-center font-medium text-gray-900 dark:text-white mb-4" id="modalTitle">Add Inventory Product</h3>

            <form id="productForm" method="POST" class="flex flex-col overflow-hidden h-full">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="p-4 overflow-y-auto flex-1 space-y-6">
                    {{-- Product Information Section --}}
                    <div>
                        <h4 class="text-sm font-semibold text-blue-600 dark:text-blue-400 mb-3 flex items-center gap-2 border-b pb-2">
                            <i class="fa-solid fa-circle-info text-xs"></i>
                            Product Information
                        </h4>
                        <div class="grid gap-4 md:grid-cols-4">
                            {{-- CUSTOMER --}}
                            <div>
                                <label class="block text-sm font-medium">Customer<span class="text-red-600">*</span></label>
                                <select id="customer_id" class="select2 w-full">
                                    <option></option>
                                </select>
                            </div>

                            {{-- MODEL --}}
                            <div>
                                <label class="block text-sm font-medium">Model<span class="text-red-600">*</span></label>
                                <select id="model_id" class="select2 w-full" disabled>
                                    <option></option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Product <span class="text-red-600">*</span></label>
                                <select name="product_id" id="product_id" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">Search Product...</option>
                                </select>
                                <p id="error-product_id" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Revision <span class="text-red-600">*</span></label>
                                <select name="revision" id="revision" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white select2">
                                    <option value="">Select Revision</option>
                                    <option value="R">R</option>
                                    <option value="R1">R1</option>
                                    <option value="R2">R2</option>
                                    <option value="R3">R3</option>
                                    <option value="RC">RC</option>
                                    <option value="RC1">RC1</option>
                                    <option value="RC2">RC2</option>
                                </select>
                                <p id="error-revision" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Material Spec</label>
                                <select name="material_spec_id" id="material_spec_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white select2">
                                    <option value="">Select Material Spec</option>
                                </select>
                                <p id="error-material_spec_id" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Unit & Dimensions Section --}}
                    <div>
                        <h4 class="text-sm font-semibold text-blue-600 dark:text-blue-400 mb-3 flex items-center gap-2 border-b pb-2">
                            <i class="fa-solid fa-ruler-combined text-xs"></i>
                            Unit & Dimensions
                        </h4>

                        {{-- Unit Selection (First as requested) --}}
                        <div class="mb-4">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Unit <span class="text-xs text-gray-500">(Determines visible dimensions)</span></label>
                            <select name="unit_id" id="unit_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white select2">
                                <option value="">Select Unit</option>
                            </select>
                            <p id="error-unit_id" class="text-red-500 text-xs mt-1 hidden"></p>
                        </div>

                        {{-- Dimensions Grid --}}
                        <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-6">
                            <div class="lg:col-span-1">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Thickness</label>
                                <input type="number" name="thickness" id="thickness" step="0.01" min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                <p id="error-thickness" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>
                            <div class="lg:col-span-1">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Width</label>
                                <input type="number" name="width" id="width" step="0.01" min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                <p id="error-width" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>

                            {{-- Dynamic Fields --}}
                            <div id="lengthContainer" class="lg:col-span-1">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Length</label>
                                <input type="number" name="length" id="length" step="0.01" min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                <p id="error-length" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>
                            <div id="length2Container" class="lg:col-span-1">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Length 2</label>
                                <input type="number" name="length_2" id="length_2" step="0.01" min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                <p id="error-length_2" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>
                            <div id="pitchContainer" class="lg:col-span-1">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pitch</label>
                                <input type="number" name="pitch" id="pitch" step="0.01" min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                <p id="error-pitch" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>

                            <div class="lg:col-span-1">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Density</label>
                                <input type="number" name="density" id="density" step="0.001" min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                <p id="error-density" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>
                            <div class="lg:col-span-1">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Weight (Kg)</label>
                                <input type="number" name="weight_kg" id="weight_kg" step="0.001" min="0" readonly class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white cursor-not-allowed">
                                <p id="error-weight_kg" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Inventory Control & Logistics Section --}}
                    <div>
                        <h4 class="text-sm font-semibold text-blue-600 dark:text-blue-400 mb-3 flex items-center gap-2 border-b pb-2">
                            <i class="fa-solid fa-boxes-stacked text-xs"></i>
                            Logistics & Control
                        </h4>
                        <div class="grid gap-4 md:grid-cols-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Rank</label>
                                <select name="rank_id" id="rank_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white select2">
                                    <option value="">Select Rank</option>
                                </select>
                                <p id="error-rank_id" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pcs per Unit</label>
                                <input type="number" name="pcs_per_unit" id="pcs_per_unit" min="1" value="1" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <p id="error-pcs_per_unit" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Unit per Car</label>
                                <input type="number" name="unit_per_car" id="unit_per_car" min="1" value="1" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <p id="error-unit_per_car" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Min Stock</label>
                                <input type="number" name="min_stock" id="min_stock" min="0" value="0" readonly class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white cursor-not-allowed">
                                <p id="error-min_stock" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>
                            <div class="md:col-span-4">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Remark</label>
                                <textarea name="remark" id="remark" rows="1" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white h-[42px] leading-tight" placeholder="Optional notes..."></textarea>
                                <p id="error-remark" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-4 p-4 border-t border-gray-200 dark:border-gray-600">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full sm:w-auto flex-1">Cancel</button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center w-full sm:w-auto flex-1">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div id="deleteModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-slate-900/50">
    <div class="relative p-4 w-full max-w-md h-full md:h-auto">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
            </button>
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3.5">
                <i class="fa-solid fa-trash-can text-gray-400 dark:text-gray-500 text-4xl"></i>
            </div>
            <p class="mb-4 text-gray-500 dark:text-gray-300">Are you sure you want to delete this inventory product?</p>
            <div class="flex justify-center items-center space-x-4">
                <button type="button" class="close-modal-button py-2 px-3 text-sm font-medium text-gray-500 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-primary-300 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">No, cancel</button>
                <button type="button" id="confirmDeleteButton" class="py-2 px-3 text-sm font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-900">Yes, I'm sure</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(function() {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        let dropdownData = {};
        let isEditMode = false;

        function initCustomerModelSelect2() {
            $('#customer_id, #model_id').select2({
                dropdownParent: $('#formModal'),
                width: '100%',
                placeholder: 'Select...',
                
            });
        }
        const routeCustomers = '{{ route("inventory.product.getCustomers") }}';
        const routeModels = '{{ route("inventory.product.getModels") }}';

        function loadCustomers() {
            const el = $('#customer_id');

            el.empty().append('<option></option>');

            $.get(routeCustomers, function(res) {
                res.forEach(c => {
                    el.append(new Option(c.code, c.id));
                });
            });
        }

        $('#customer_id').on('change', function() {
            const customerId = this.value;

            // reset model
            $('#model_id')
                .prop('disabled', true)
                .empty()
                .append('<option></option>')
                .trigger('change');

            // reset product
            $('#product_id')
                .val(null)
                .trigger('change')
                .prop('disabled', true);

            if (!customerId) return;

            $.get(routeModels, {
                customer_id: customerId
            }, function(models) {
                models.forEach(m => {
                    $('#model_id').append(new Option(m.name, m.id));
                });

                $('#model_id').prop('disabled', false);
            });
        });

        function initProductSelect2(modelId) {
            if ($('#product_id').data('select2')) {
                $('#product_id').select2('destroy');
            }

            $('#product_id').select2({
                dropdownParent: $('#formModal'),
                width: '100%',
                minimumInputLength: 1,
                ajax: {
                    url: '{{ route("inventory.product.getProducts") }}',
                    dataType: 'json',
                    delay: 250,
                    data: p => ({
                        q: p.term,
                        model_id: modelId
                    }),
                    processResults: d => ({
                        results: d.results,
                        pagination: d.pagination
                    })
                }
            });
        }

        $('#model_id').on('change', function() {
            const modelId = this.value;

            $('#product_id').val(null).trigger('change');

            if (!modelId) return;

            $('#product_id').prop('disabled', false);
            initProductSelect2(modelId);
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
        $.get('{{ route("inventory.product.dropdownData") }}', function(data) {
            dropdownData = data;

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
        const table = window.defaultDataTable('inventoryProductTable', {
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("inventory.product.data") }}',
                type: 'GET',
                data: d => {
                    d.search = d.search.value;
                }
            },
            columns: [{
                    data: null,
                    className: 'px-4 py-3 text-center',
                    render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1
                },
                {
                    data: 'part_no',
                    className: 'px-4 py-3 whitespace-nowrap font-medium'
                },
                {
                    data: 'customer',
                    className: 'px-4 py-3 text-center'
                },
                {
                    data: 'model',
                    className: 'px-4 py-3 text-center'
                },
                {
                    data: null,
                    className: 'px-4 py-3 whitespace-nowrap',
                    render: row => `${row.material_spec} <br> <span class="text-xs text-gray-500 dark:text-gray-400">(${row.coating_type || '-'})</span>`
                },
                {
                    data: null,
                    className: 'px-4 py-3 whitespace-nowrap font-mono text-xs',
                    render: row => {
                        const t = parseFloat(row.thickness) || 0;
                        const w = parseFloat(row.width) || 0;
                        const l = parseFloat(row.length) || 0;
                        const l2 = parseFloat(row.length_2) || 0;
                        const p = parseFloat(row.pitch) || 0;
                        const unitName = (row.unit_name || '').toLowerCase();

                        let size = `${t} x ${w} x ${l}`;

                        if (unitName === 'trapezoid' && l2 > 0) {
                            size += ` / ${l2}`;
                        }

                        let html = `<div class="flex flex-col leading-tight"><span>${size}</span>`;
                        if (p > 0) {
                            html += `<span class="text-xs text-blue-500 dark:text-blue-400 font-bold">P: ${p}</span>`;
                        }
                        if (row.weight_kg) {
                            html += `<span class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold mt-0.5">W: ${parseFloat(row.weight_kg).toFixed(3)} kg</span>`;
                        }
                        html += `</div>`;

                        return html;
                    }
                },
                {
                    data: 'pcs_per_unit',
                    className: 'px-4 py-3 text-center'
                },
                {
                    data: 'weight_kg',
                    className: 'px-4 py-3 text-center',
                    render: d => d ? parseFloat(d).toFixed(2) : '-'
                },
                {
                    data: 'unit_per_car',
                    className: 'px-4 py-3 text-center'
                },
                {
                    data: 'rank',
                    className: 'px-4 py-3 text-center'
                },

                {
                    data: 'remark',
                    defaultContent: '-',
                    className: 'px-4 py-3 text-xs text-gray-500 dark:text-gray-400',
                    orderable: false,
                    render: (d) => d || '-'
                },
                {
                    data: 'updated_at',
                    className: 'px-4 py-3 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400',
                    render: (d) => d || '-'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'px-4 py-3 text-center',
                    width: '100px',
                    render: row => `
                    <div class="flex items-center justify-center gap-2">
                        <button class="print-button h-8 w-8 inline-flex items-center justify-center text-green-600 rounded-lg bg-green-50 hover:bg-green-100 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-green-500 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900" data-id="${row.id}" title="Print Label">
                            <i class="fa-solid fa-print text-sm"></i>
                        </button>
                        <button class="edit-button h-8 w-8 inline-flex items-center justify-center text-blue-600 rounded-lg bg-blue-50 hover:bg-blue-100 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-blue-500 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900" data-id="${row.id}" title="Edit">
                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                        </button>
                        <button class="delete-button h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-lg bg-red-50 hover:bg-red-100 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-red-500 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900" data-id="${row.id}" title="Delete">
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
        const deleteModal = $('#deleteModal');
        let deleteId = null;

        // Add button
        $('#add-button').on('click', function() {
            initCustomerModelSelect2();
            loadCustomers();
            isEditMode = false;
            $('#modalTitle').text('Add Inventory Product');
            $('#formMethod').val('POST');
            $('#productForm').attr('action', '{{ route("inventory.product.store") }}');
            $('#productForm')[0].reset();
            $('#pcs_per_unit').val(1);
            $('#unit_per_car').val(1);
            $('#min_stock').val(90);
            $('#density').val('');
            $('#weight_kg').val('');
            if ($('#product_id').data('select2')) {
                $('#product_id').select2('destroy');
            }
            $('#product_id')
                .val(null)
                .prop('disabled', true);
            $('#unit_id').val('').trigger('change');
            $('[id^="error-"]').addClass('hidden').text('');
            toggleUnitFields();
            showModal(formModal);
        });

        // Close modals
        $('.close-modal-button').on('click', function() {
            hideModal($(this).closest('[tabindex="-1"]'));
        });

        // Submit form
        $('#productForm').on('submit', function(e) {
            e.preventDefault();
            $('[id^="error-"]').addClass('hidden').text('');

            const formData = new FormData(this);

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

        // Edit button
        $(document).on('click', '.edit-button', function() {
            initCustomerModelSelect2();
            loadCustomers();
            const id = $(this).data('id');
            isEditMode = true;
            $('#modalTitle').text('Edit Inventory Product');
            $('#formMethod').val('PUT');
            $('#productForm').attr('action', `{{ url('inventory/product') }}/${id}`);
            if ($('#product_id').data('select2')) {
                $('#product_id').select2('destroy');
            }
            $('#product_id').val(null).prop('disabled', true);
            $('[id^="error-"]').addClass('hidden').text('');

            $.get(`{{ url('inventory/product') }}/${id}`, function(data) {
                if (data.product?.customer_id) {
                    $('#customer_id')
                        .val(data.product.customer_id)
                        .trigger('change');
                }
                setTimeout(() => {
                    if (data.product?.model_id) {
                        $('#model_id')
                            .val(data.product.model_id)
                            .trigger('change');
                    }
                    setTimeout(() => {
                        $('#product_id').prop('disabled', false);
                        initProductSelect2(data.product.model_id);

                        const opt = new Option(
                            `${data.product.part_no} - ${data.product.part_name}`,
                            data.product.hash_id || data.product_id,
                            true,
                            true
                        );
                        $('#product_id').append(opt).trigger('change');

                    }, 300);
                }, 300);
                $('#revision').val(data.revision).trigger('change');
                $('#material_spec_id').val(data.material_spec ? data.material_spec.hash_id : '').trigger('change');
                $('#thickness').val(data.thickness);
                $('#width').val(data.width);
                $('#length').val(data.length);
                $('#length_2').val(data.length_2);
                $('#pitch').val(data.pitch);
                $('#unit_id').val(data.unit ? data.unit.hash_id : '').trigger('change');
                $('#rank_id').val(data.rank ? data.rank.hash_id : '').trigger('change');
                $('#pcs_per_unit').val(data.pcs_per_unit);
                $('#pcs_per_unit').val(data.pcs_per_unit);
                $('#pcs_per_unit').val(data.pcs_per_unit);
                $('#unit_per_car').val(data.unit_per_car);
                $('#min_stock').val(data.min_stock);
                $('#density').val(data.density);
                $('#weight_kg').val(data.weight_kg);
                $('#remark').val(data.remark);

                if (data.product) {
                    const opt = new Option(`${data.product.part_no} - ${data.product.part_name}`, data.product.hash_id || data.product_id, true, true);
                    $('#product_id').append(opt).trigger('change');
                }

                toggleUnitFields();
                showModal(formModal);
            });
        });

        // Print button
        $(document).on('click', '.print-button', function() {
            const id = $(this).data('id');
            window.open(`{{ url('inventory/product') }}/${id}/print`, '_blank');
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
                // Sheet: Show Length, Hide L2 & Pitch (unless user wants Pitch, but per req Hide Pitch)
                // User requirement: "Sheet: (T x W x L x Density)" -> Need L.
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
            const pcs = parseInt($('#pcs_per_unit').val()) || 0;
            const upc = parseInt($('#unit_per_car').val()) || 0;
            $('#min_stock').val(pcs * upc * 90);
        }

        $('#pcs_per_unit, #unit_per_car').on('input change', calculateMinStock);

        // Delete button
        $(document).on('click', '.delete-button', function() {
            deleteId = $(this).data('id');
            showModal(deleteModal);
        });

        // Confirm delete
        $('#confirmDeleteButton').on('click', function() {
            if (!deleteId) return;

            $.ajax({
                url: `{{ url('inventory/product') }}/${deleteId}`,
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