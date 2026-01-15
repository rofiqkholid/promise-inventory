@extends('layouts.app')
@section('title', 'Inventory Product Management')
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
                <th scope="col" class="px-6 py-3 w-16">No</th>
                <th scope="col" class="px-6 py-3">Part No</th>
                <th scope="col" class="px-6 py-3">Customer</th>
                <th scope="col" class="px-6 py-3">Model</th>
                <th scope="col" class="px-6 py-3">Material</th>
                <th scope="col" class="px-6 py-3">Size (TxWxL)</th>
                <th scope="col" class="px-6 py-3">Pcs/Unit</th>
                <th scope="col" class="px-6 py-3">Unit/Car</th>
                <th scope="col" class="px-6 py-3">Rank</th>
                <th scope="col" class="px-6 py-3">Coil Center</th>
                <th scope="col" class="px-6 py-3">Remark</th>
                <th scope="col" class="px-6 py-3 text-center w-[100px]">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

{{-- Add/Edit Modal --}}
<div id="formModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 justify-center items-center w-full h-full bg-black bg-opacity-50 flex">
    <div class="relative p-4 w-full max-w-4xl max-h-[95vh]">
        <div class="relative text-left bg-white rounded-lg shadow dark:bg-gray-800 flex flex-col max-h-[90vh]">
            <div class="flex items-center justify-between p-4 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-medium text-gray-900 dark:text-white" id="modalTitle">Add Inventory Product</h3>
                <button type="button" class="close-modal-button text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                    <i class="fa-solid fa-xmark w-5 h-5"></i>
                </button>
            </div>

            <form id="productForm" method="POST" class="flex flex-col overflow-hidden h-full">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                
                <div class="p-4 overflow-y-auto flex-1 space-y-6">
                    {{-- Product Identity Section --}}
                    <div>
                        <h4 class="text-sm font-semibold text-blue-600 dark:text-blue-400 mb-3 flex items-center gap-2 border-b pb-2">
                            <i class="fa-solid fa-tag text-xs"></i>
                            Product Identity
                        </h4>
                        <div class="grid gap-4 md:grid-cols-3">
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
                        </div>
                    </div>

                    {{-- Material & Source Section --}}
                    <div>
                        <h4 class="text-sm font-semibold text-blue-600 dark:text-blue-400 mb-3 flex items-center gap-2 border-b pb-2">
                            <i class="fa-solid fa-industry text-xs"></i>
                            Material & Source
                        </h4>
                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Material Spec</label>
                                <select name="material_spec_id" id="material_spec_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white select2">
                                    <option value="">Select Material Spec</option>
                                </select>
                                <p id="error-material_spec_id" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Coil Center</label>
                                <select name="coil_center_id" id="coil_center_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white select2">
                                    <option value="">Select Coil Center</option>
                                </select>
                                <p id="error-coil_center_id" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Sub Contractor</label>
                                <select name="subcont_id" id="subcont_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white select2">
                                    <option value="">Select Sub Contractor</option>
                                </select>
                                <p id="error-subcont_id" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Dimensions Section --}}
                    <div>
                        <h4 class="text-sm font-semibold text-blue-600 dark:text-blue-400 mb-3 flex items-center gap-2 border-b pb-2">
                            <i class="fa-solid fa-ruler-combined text-xs"></i>
                            Dimensions
                        </h4>
                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Thickness</label>
                                <input type="number" name="thickness" id="thickness" step="0.01" min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                <p id="error-thickness" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Width</label>
                                <input type="number" name="width" id="width" step="0.01" min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                <p id="error-width" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Length</label>
                                <input type="number" name="length" id="length" step="0.01" min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                <p id="error-length" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>
                            <div id="length2Container">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Length 2 (Trapezoid)</label>
                                <input type="number" name="length_2" id="length_2" step="0.01" min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                <p id="error-length_2" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pitch</label>
                                <input type="number" name="pitch" id="pitch" step="0.01" min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                <p id="error-pitch" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Logistics Section --}}
                    <div>
                        <h4 class="text-sm font-semibold text-blue-600 dark:text-blue-400 mb-3 flex items-center gap-2 border-b pb-2">
                            <i class="fa-solid fa-truck-ramp-box text-xs"></i>
                            Unit & Logistics
                        </h4>
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Unit</label>
                                <select name="unit_id" id="unit_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white select2">
                                    <option value="">Select Unit</option>
                                </select>
                                <p id="error-unit_id" class="text-red-500 text-xs mt-1 hidden"></p>
                            </div>
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
                        </div>
                    </div>

                    {{-- Inventory Control Section --}}
                    <div>
                        <h4 class="text-sm font-semibold text-blue-600 dark:text-blue-400 mb-3 flex items-center gap-2 border-b pb-2">
                            <i class="fa-solid fa-boxes-stacked text-xs"></i>
                            Inventory Control & Others
                        </h4>
                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Min Stock</label>
                                <input type="number" name="min_stock" id="min_stock" min="0" value="0" readonly class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white cursor-not-allowed" title="Auto-calculated (Pcs per Unit x 90)">
                                <p id="error-min_stock" class="text-red-500 text-xs mt-1 hidden"></p>
                                <p class="text-xs text-gray-500 mt-1">Formula: <b>Pcs per Unit x 90</b></p>
                            </div>
                            <div class="md:col-span-2">
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
<div id="deleteModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full bg-black bg-opacity-50">
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

    // Toast helper
    function toast(icon, title, text) {
        const isDark = document.documentElement.classList.contains('dark');
        const theme = isDark ? {
            bg: 'rgba(30, 41, 59, 0.95)', fg: '#E5E7EB',
            icon: { success: '#22c55e', error: '#ef4444' }
        } : {
            bg: 'rgba(255, 255, 255, 0.98)', fg: '#0f172a',
            icon: { success: '#16a34a', error: '#dc2626' }
        };
        Swal.fire({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 2600, timerProgressBar: true,
            icon, title, text,
            iconColor: theme.icon[icon],
            background: theme.bg,
            color: theme.fg
        });
    }

    // Load dropdown data
    $.get('{{ route("inventory.product.dropdownData") }}', function(data) {
        dropdownData = data;
        
        data.coilCenters.forEach(cc => {
            $('#coil_center_id').append(`<option value="${cc.hash_id}">${cc.code} - ${cc.name}</option>`);
        });
        
        data.materialSpecs.forEach(ms => {
            $('#material_spec_id').append(`<option value="${ms.hash_id}">${ms.spec_name}</option>`);
        });
        
        data.units.forEach(u => {
            $('#unit_id').append(`<option value="${u.hash_id}">${u.code} - ${u.name}</option>`);
        });
        
        data.ranks.forEach(r => {
            $('#rank_id').append(`<option value="${r.hash_id}">${r.code}</option>`);
        });

        if (data.subContractors) {
            data.subContractors.forEach(sc => {
                $('#subcont_id').append(`<option value="${sc.hash_id}">${sc.name} (${sc.code})</option>`);
            });
        }
    });




    // Initialize Product Select2
    $('#product_id').select2({
        dropdownParent: $('#formModal'),
        width: '100%',
        placeholder: 'Search Product...',
        allowClear: false,
        minimumInputLength: 1,
        ajax: {
            url: '{{ route("inventory.product.getProducts") }}',
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term, page: params.page || 1 }),
            processResults: data => ({
                results: data.results,
                pagination: { more: data.pagination.more }
            })
        }
    });

    // DataTable
    // DataTable
    const table = window.defaultDataTable('inventoryProductTable', {
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("inventory.product.data") }}',
            type: 'GET',
            data: d => { d.search = d.search.value; }
        },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1 },
            { data: 'part_no' },
            { data: 'customer' },
            { data: 'model' },
            { 
                data: null, 
                render: row => `${row.material_spec} <br> <span class="text-xs text-gray-500">(${row.coating_type || '-'})</span>` 
            },
            { 
                data: null, 
                render: row => {
                    const t = parseFloat(row.thickness) || 0;
                    const w = parseFloat(row.width) || 0;
                    const l = parseFloat(row.length) || 0;
                    const l2 = parseFloat(row.length_2) || 0;
                    const p = parseFloat(row.pitch) || 0;
                    
                    let size = `${t} x ${w} x ${l}`;
                    if (l2 > 0) size += ` x ${l2}`;
                    if (p > 0) size += ` x ${p}`;
                    
                    return size;
                }
            },
            { data: 'pcs_per_unit' },
            { data: 'unit_per_car' },
            { data: 'rank' },
            { data: 'coil_center' },
            { 
                data: 'remark', 
                defaultContent: '-', 
                className: 'text-xs text-gray-500',
                orderable: false,
                render: (d) => d || '-'
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                width: '100px',
                render: row => `
                    <div class="flex items-center justify-center gap-2">
                        <button class="print-button h-8 w-8 inline-flex items-center justify-center text-green-600 rounded-lg bg-green-50 hover:bg-green-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-green-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" data-id="${row.id}" title="Print Label">
                            <i class="fa-solid fa-print text-sm"></i>
                        </button>
                        <button class="edit-button h-8 w-8 inline-flex items-center justify-center text-blue-600 rounded-lg bg-blue-50 hover:bg-blue-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-blue-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" data-id="${row.id}" title="Edit">
                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                        </button>
                        <button class="delete-button h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-lg bg-red-50 hover:bg-red-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-red-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" data-id="${row.id}" title="Delete">
                            <i class="fa-solid fa-trash-can text-sm"></i>
                        </button>
                    </div>`
            }
        ],
        order: [[0, 'desc']]
    });

    // Modal helpers
    function showModal(m) { m.removeClass('hidden').addClass('flex'); }
    function hideModal(m) { m.addClass('hidden').removeClass('flex'); }

    const formModal = $('#formModal');
    const deleteModal = $('#deleteModal');
    let deleteId = null;

    // Add button
    $('#add-button').on('click', function() {
        isEditMode = false;
        $('#modalTitle').text('Add Inventory Product');
        $('#formMethod').val('POST');
        $('#productForm').attr('action', '{{ route("inventory.product.store") }}');
        $('#productForm')[0].reset();
        $('#pcs_per_unit').val(1);
        $('#min_stock').val(90);
        $('#product_id').val(null).trigger('change');
        $('#unit_id').val('').trigger('change');
        $('[id^="error-"]').addClass('hidden').text('');
        toggleLength2();
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
            headers: { 'X-CSRF-TOKEN': csrfToken },
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
        const id = $(this).data('id');
        isEditMode = true;
        $('#modalTitle').text('Edit Inventory Product');
        $('#formMethod').val('PUT');
        $('#productForm').attr('action', `{{ url('inventory/product') }}/${id}`);
        $('[id^="error-"]').addClass('hidden').text('');

        $.get(`{{ url('inventory/product') }}/${id}`, function(data) {
            $('#revision').val(data.revision).trigger('change');
            $('#subcont_id').val(data.subcont_id).trigger('change');
            $('#coil_center_id').val(data.coil_center_id).trigger('change');
            $('#material_spec_id').val(data.material_spec_id).trigger('change');
            $('#thickness').val(data.thickness);
            $('#width').val(data.width);
            $('#length').val(data.length);
            $('#length_2').val(data.length_2);
            $('#pitch').val(data.pitch);
            $('#unit_id').val(data.unit_id).trigger('change');
            $('#rank_id').val(data.rank_id).trigger('change');
            $('#pcs_per_unit').val(data.pcs_per_unit);
            $('#unit_per_car').val(data.unit_per_car);
            $('#min_stock').val(data.min_stock);
            $('#remark').val(data.remark);

            if (data.product) {
                const opt = new Option(`${data.product.part_no} - ${data.product.part_name}`, data.product_id, true, true);
                $('#product_id').append(opt).trigger('change');
            }
            
            toggleLength2();
            showModal(formModal);
        });
    });
    
    // Print button
    $(document).on('click', '.print-button', function() {
        const id = $(this).data('id');
        window.open(`{{ url('inventory/product') }}/${id}/print`, '_blank');
    });


    // Toggle Length 2 visibility
    function toggleLength2() {
        const unitId = $('#unit_id').val();
        if (unitId == '3') {
            $('#length2Container').show();
        } else {
            $('#length2Container').hide();
            if (!isEditMode) $('#length_2').val('');
        }
    }

    $('#unit_id').on('change', function() {
        toggleLength2();
    });

    // Auto-calculate min stock
    $('#pcs_per_unit').on('input change', function() {
        const pcs = parseInt($(this).val()) || 0;
        $('#min_stock').val(pcs * 90);
    });

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
            headers: { 'X-CSRF-TOKEN': csrfToken },
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
