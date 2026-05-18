@extends('layouts.app')

@section('title', 'Tool Master Specification')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">Tool Specification</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Manage master data for tool specifications.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button type="button" class="add-button inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest active:scale-[0.98] transition-all">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
    </div>

    <x-table id="toolMasterTable">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th scope="col" class="px-6 py-4 w-16 text-center text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">No</th>
                <th scope="col" class="px-6 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Category</th>
                <th scope="col" class="px-6 py-4 text-center w-16 text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Sketch</th>
                <th scope="col" class="px-6 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700" style="min-width: 180px;">Name</th>
                <th scope="col" class="px-6 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Brand</th>
                <th scope="col" class="px-6 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700" style="min-width: 250px;">Spec Code</th>
                <th scope="col" class="px-6 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">UOM</th>
                <th scope="col" class="px-6 py-4 text-right text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Price</th>
                <th scope="col" class="px-6 py-4 text-center text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Min</th>
                <th scope="col" class="px-6 py-4 text-center text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Max</th>
                <th scope="col" class="px-6 py-4 text-center w-[100px] text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

{{-- Modals --}}
<div id="modal-master-form" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50">
    <div class="relative w-full max-w-5xl transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 transition-all border border-slate-200 dark:border-gray-800 flex flex-col h-[650px] max-h-[90vh]">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-widest" id="modal-title">Add Tool</h3>
            <button type="button" class="close-modal text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100 dark:hover:bg-gray-800">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Tab Switcher -->
        <div class="flex border-b border-gray-100 dark:border-gray-800 bg-slate-50/50 dark:bg-gray-800/20 px-6">
            <button type="button" id="tab-specification-btn" class="modal-tab-btn active px-4 py-3 text-xs font-bold uppercase tracking-wider text-primary-600 border-b-2 border-primary-500 transition-all focus:outline-none flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-[10px]"></i> Specification
            </button>
            <button type="button" id="tab-machining-btn" class="modal-tab-btn px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-500 hover:text-slate-700 border-b-2 border-transparent transition-all focus:outline-none flex items-center gap-2">
                <i class="fa-solid fa-screwdriver-wrench text-[10px]"></i> Machining Settings
            </button>
        </div>

        <div class="overflow-y-auto px-6 py-6 custom-scrollbar flex-1">
            <form id="masterForm">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <input type="hidden" id="toolId">
                
                <!-- Tab 1: Tool Specification -->
                <div id="tab-specification" class="modal-tab-content space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Category <span class="text-red-500">*</span></label>
                            <select name="category_id" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" data-moving-type="{{ $category->moving_type }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Tool Sketch</label>
                            <div class="flex gap-2">
                                <select name="sketch_id" id="sketch_id" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all">
                                    <option value="">Select Sketch</option>
                                </select>
                                <div id="sketch-preview-container" class="w-10 h-10 border border-gray-200 dark:border-gray-700 rounded-xs flex-shrink-0 flex items-center justify-center bg-gray-50 dark:bg-gray-800 overflow-hidden cursor-pointer hover:scale-110 transition-all">
                                    <i id="sketch-preview-placeholder" class="fa-solid fa-image text-gray-300 text-xs"></i>
                                    <img id="sketch-preview-img" referrerpolicy="no-referrer" class="w-full h-full object-cover" style="display: none;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all placeholder:text-gray-400">
                        </div>
                        <div>
                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Brand <span class="text-red-500">*</span></label>
                            <input type="text" name="brand" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Spec Code</label>
                            <input type="text" name="spec_code" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                        </div>
                        <div>
                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">UOM <span class="text-red-500">*</span></label>
                            <select name="uom" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                                <option value="PCS">PCS</option>
                                <option value="BOX">BOX</option>
                                <option value="SET">SET</option>
                                <option value="ROLL">ROLL</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-4 gap-4">
                        <div>
                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Dimension (ø / T / Range)</label>
                            <input type="text" name="dimension" placeholder="e.g. 200, 32-R2.5, 0-150" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                        </div>
                        <div>
                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Length</label>
                            <input type="number" step="0.01" name="length" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                        </div>
                        <div>
                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Material Type</label>
                            <input type="text" name="material_type" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                        </div>
                        <div>
                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">HRC</label>
                            <input type="text" name="hrc" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-4 gap-4">
                        <div id="pcsPerUnitGroup" class="col-span-2">
                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Pcs / Unit <span class="text-red-500">*</span></label>
                            <input type="number" name="pcs_per_unit" required value="1" min="1" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs block w-full p-3">
                        </div>
                        <div id="priceGroup" class="col-span-1 hidden">
                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Price (IDR)</label>
                            <input type="number" name="price_per_unit" id="price_per_unit" value="0" min="0" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs block w-full p-3">
                        </div>
                        <div>
                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Qty Min</label>
                            <input type="number" name="qty_min" value="0" min="0" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs block w-full p-3">
                        </div>
                        <div>
                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Qty Max</label>
                            <input type="number" name="qty_max" value="0" min="0" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs block w-full p-3">
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Machining Settings -->
                <div id="tab-machining" class="modal-tab-content hidden space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider">Machining Parameter Settings</h4>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400">Configure spindle speed, table feed, depth of cut (ap), and step over per material category</p>
                        </div>
                        <button type="button" id="addSettingRowBtn" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 rounded-xs text-[10px] font-bold uppercase tracking-wider transition-all">
                            <i class="fa-solid fa-plus text-[9px]"></i> Add Row
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto border border-slate-100 dark:border-gray-800 rounded-xs">
                        <table class="min-w-full divide-y divide-slate-100 dark:divide-gray-800 text-xs text-left" id="machiningSettingsTable">
                            <thead class="bg-slate-50 dark:bg-slate-800/40 text-[9px] font-bold text-slate-500 dark:text-gray-400 tracking-wider">
                                <tr>
                                    <th scope="col" class="px-3 py-2.5 w-44">Material Category</th>
                                    <th scope="col" class="px-3 py-2.5 w-28 text-center">Spindle Speed (n)<br><span class="text-[8px] font-normal lowercase">rev/min</span></th>
                                    <th scope="col" class="px-3 py-2.5 w-28 text-center">Table Feed (Vf)<br><span class="text-[8px] font-normal lowercase">mm/min</span></th>
                                    <th scope="col" class="px-3 py-2.5 w-28 text-center">Depth of Cut (ap)<br><span class="text-[8px] font-normal lowercase">mm</span></th>
                                    <th scope="col" class="px-3 py-2.5 w-24 text-center">Step Over (%)</th>
                                    <th scope="col" class="px-2 py-2.5 w-24 text-center">CNC Small<br><span class="text-[8px] font-normal uppercase">plant b</span></th>
                                    <th scope="col" class="px-2 py-2.5 w-28 text-center">CNC Hartford<br><span class="text-[8px] font-normal uppercase">plant f</span></th>
                                    <th scope="col" class="px-3 py-2.5 w-24">Status</th>
                                    <th scope="col" class="px-2 py-2.5 w-10 text-center"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-gray-800 bg-white dark:bg-gray-900" id="settingRowsContainer">
                                {{-- Rows appended dynamically --}}
                            </tbody>
                        </table>
                    </div>
                </div>
                
            </form>
        </div>
        <div class="border-t border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 flex gap-3">
            <button type="button" class="close-modal flex-1 px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</button>
            <button type="button" id="saveMasterBtn" class="flex-1 px-4 py-3 bg-primary-600 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest hover:bg-primary-700 transition-all">Save</button>
        </div>
    </div>
</div>

<x-inventory.delete-modal />

{{-- Modal: Image Preview --}}
<div id="modal-preview" class="modal-container hidden fixed inset-0 z-[110] flex items-center justify-center bg-slate-900/60 p-4">
    <div class="relative max-w-4xl w-full h-full flex items-center justify-center p-4">
        <img id="img-full" src="" referrerpolicy="no-referrer" class="max-w-full max-h-[90vh] object-contain rounded-xs shadow-2xl transition-all duration-300">
        <button class="close-preview absolute top-4 right-4 text-white text-3xl hover:text-red-400 hover:scale-110 active:scale-95 transition-all drop-shadow-lg" title="Close"><i class="fa-solid fa-xmark"></i></button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const csrf = $('meta[name="csrf-token"]').attr('content');
        const apiBase = "{{ route('inventory.tool.master.index') }}";
        let deleteUrl = '';

        window.masterTable = window.defaultDataTable('#toolMasterTable', {
            ajax: { url: apiBase, type: 'GET' },
            columns: [
                { data: null, orderable: false, searchable: false, render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                { data: 'category_name', render: d => d || '-' },
                { 
                    data: 'sketch_image', 
                    className: 'text-center',
                    render: d => d ? `<img src="${d}" referrerpolicy="no-referrer" class="h-8 w-8 object-cover mx-auto rounded-xs border border-gray-200 cursor-pointer hover:scale-150 transition-all" onclick="window.previewImg('${d}')">` : `<div class="h-8 w-8 flex items-center justify-center mx-auto bg-gray-50 border border-gray-100 text-gray-300 rounded-xs"><i class="fa-solid fa-image text-[8px]"></i></div>`
                },
                { data: 'name' },
                { data: 'brand' },
                { data: 'spec_code', render: d => d || '-' },
                { data: 'uom' },
                { 
                    data: 'price_per_unit', 
                    className: 'text-right font-mono text-xs',
                    render: (d, t, r) => {
                        if (r.moving_type === 'fast' && d > 0) {
                            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(d);
                        }
                        return '-';
                    }
                },
                { data: 'qty_min', className: 'text-center' },
                { data: 'qty_max', className: 'text-center' },
                {
                    data: null, orderable: false, searchable: false, className: 'text-center', width: '100px',
                    render: (d, t, r) => `
                        <div class="flex items-center justify-center gap-1.5">
                             <button class="edit-btn h-8 w-8 inline-flex items-center justify-center text-primary-600 rounded-xs bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:hover:bg-primary-900/30 transition-colors" data-row='${JSON.stringify(r).replace(/'/g, "&apos;")}' title="Edit">
                                <i class="fa-solid fa-pen-to-square text-sm"></i>
                            </button>
                            <button class="delete-btn h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-xs bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors" data-id="${r.id}" title="Delete">
                                <i class="fa-solid fa-trash-can text-sm"></i>
                            </button>
                        </div>`
                }
            ],
            order: [[2, 'asc']]
        });

        const showMdl = (id) => { $('.modal-container').addClass('hidden'); $(`#${id}`).removeClass('hidden'); }
        const hideMdl = (id) => { $(`#${id}`).addClass('hidden'); }
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

        const resetTabs = () => {
            $('.modal-tab-btn').removeClass('text-primary-600 border-primary-500 active').addClass('text-slate-500 hover:text-slate-700 border-transparent');
            $('#tab-specification-btn').addClass('text-primary-600 border-primary-500 active').removeClass('text-slate-500 hover:text-slate-700 border-transparent');
            $('.modal-tab-content').addClass('hidden');
            $('#tab-specification').removeClass('hidden');
        }

        // Tab Switcher click handlers
        $('.modal-tab-btn').on('click', function() {
            const targetId = $(this).attr('id');
            $('.modal-tab-btn').removeClass('text-primary-600 border-primary-500 active').addClass('text-slate-500 hover:text-slate-700 border-transparent');
            $(this).addClass('text-primary-600 border-primary-500 active').removeClass('text-slate-500 hover:text-slate-700 border-transparent');
            
            $('.modal-tab-content').addClass('hidden');
            if (targetId === 'tab-specification-btn') {
                $('#tab-specification').removeClass('hidden');
            } else {
                $('#tab-machining').removeClass('hidden');
            }
        });

        $('.add-button').on('click', function() {
            resetTabs();
            $('#masterForm')[0].reset();
            $('#toolId').val('');
            $('#sketch_id').html('<option value="">Select Sketch</option>');
            $('#sketch-preview-img').hide();
            $('#sketch-preview-placeholder').show();
            $('#settingRowsContainer').empty();
            $('input[name="_method"]').val('POST');
            $('#modal-title').text('Add Tool');
            toggleMovingTypeFields();
            showMdl('modal-master-form');
        });

        function toggleMovingTypeFields() {
            const selectedOpt = $('[name="category_id"] option:selected');
            const movingType = selectedOpt.data('moving-type');
            
            if (movingType === 'fast') {
                $('#pcsPerUnitGroup').removeClass('col-span-2').addClass('col-span-1');
                $('#priceGroup').removeClass('hidden');
            } else {
                $('#pcsPerUnitGroup').removeClass('col-span-1').addClass('col-span-2');
                $('#priceGroup').addClass('hidden');
                $('#price_per_unit').val('0'); // Reset price if not fast moving
            }
        }

        // Fetch Sketches on Category Change
        $('[name="category_id"]').on('change', function() {
            toggleMovingTypeFields();
            const categoryId = $(this).val();
            const sketchSelect = $('#sketch_id');
            sketchSelect.html('<option value="">Loading...</option>');
            
            if (!categoryId) {
                sketchSelect.html('<option value="">Select Sketch</option>').trigger('change');
                return;
            }

            $.get(`{{ url('inventory/tool/sketch/by-category') }}/${categoryId}`, function(res) {
                let html = '<option value="">Select Sketch</option>';
                res.forEach(s => {
                    html += `<option value="${s.id}" data-img="{{ url('storage') }}/${s.image_path}">${s.name}</option>`;
                });
                sketchSelect.html(html);
                
                // If editing, re-select current sketch
                const currentSketchId = $('#masterForm').data('current-sketch-id');
                if (currentSketchId) {
                    sketchSelect.val(currentSketchId).trigger('change');
                    $('#masterForm').data('current-sketch-id', null);
                } else {
                    sketchSelect.trigger('change');
                }
            });
        });

        // Sketch Preview
        $('#sketch_id').on('change', function() {
            const selected = $(this).find(':selected');
            const imgPath = selected.data('img');
            if (imgPath) {
                $('#sketch-preview-img').attr('src', imgPath).show();
                $('#sketch-preview-placeholder').hide();
            } else {
                $('#sketch-preview-img').hide();
                $('#sketch-preview-placeholder').show();
            }
        });

        window.previewImg = (src) => {
            $('#img-full').attr('src', src);
            $('#modal-preview').removeClass('hidden');
        };

        function addSettingRow(data = {}) {
            const rowId = 'r_' + Math.random().toString(36).substr(2, 9);
            const material = data.material_category || '';
            const speed = data.spindle_speed !== undefined && data.spindle_speed !== null ? data.spindle_speed : '';
            const feed = data.table_feed !== undefined && data.table_feed !== null ? data.table_feed : '';
            const doc = data.depth_of_cut !== undefined && data.depth_of_cut !== null ? data.depth_of_cut : '';
            let stepOver = data.step_over || '';
            if (stepOver) {
                stepOver = String(stepOver).replace('%', '');
            }
            const cncSmall = data.cnc_small_plant_b ? 'checked' : '';
            const cncHartford = data.cnc_big_hartford_plant_f ? 'checked' : '';
            const status = data.status || 'USE';

            const html = `
                <tr class="setting-row" id="row-${rowId}">
                    <td class="px-2 py-2">
                        <select name="settings[${rowId}][material_category]" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs block w-full p-1.5 focus:ring-primary-500 focus:border-primary-500 transition-all">
                            <option value="">-- Select --</option>
                            <option value="SS,FC300" ${material === 'SS,FC300' ? 'selected' : ''}>SS,FC300</option>
                            <option value="S45,S50" ${material === 'S45,S50' ? 'selected' : ''}>S45,S50</option>
                            <option value="SKD,FC600" ${material === 'SKD,FC600' ? 'selected' : ''}>SKD,FC600</option>
                        </select>
                    </td>
                    <td class="px-2 py-2">
                        <input type="number" name="settings[${rowId}][spindle_speed]" required value="${speed}" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs block w-full p-1.5 text-center focus:ring-primary-500">
                    </td>
                    <td class="px-2 py-2">
                        <input type="number" name="settings[${rowId}][table_feed]" required value="${feed}" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs block w-full p-1.5 text-center focus:ring-primary-500">
                    </td>
                    <td class="px-2 py-2">
                        <input type="number" step="0.1" name="settings[${rowId}][depth_of_cut]" required value="${doc}" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs block w-full p-1.5 text-center focus:ring-primary-500">
                    </td>
                    <td class="px-2 py-2">
                        <input type="number" min="1" max="100" name="settings[${rowId}][step_over]" required value="${stepOver}" placeholder="70" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs block w-full p-1.5 text-center focus:ring-primary-500">
                    </td>
                    <td class="px-2 py-2 text-center">
                        <input type="checkbox" name="settings[${rowId}][cnc_small_plant_b]" value="1" ${cncSmall} class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    </td>
                    <td class="px-2 py-2 text-center">
                        <input type="checkbox" name="settings[${rowId}][cnc_big_hartford_plant_f]" value="1" ${cncHartford} class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    </td>
                    <td class="px-2 py-2">
                        <select name="settings[${rowId}][status]" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs block w-full p-1.5 focus:ring-primary-500">
                            <option value="USE" ${status === 'USE' ? 'selected' : ''}>USE</option>
                            <option value="NOT USE" ${status === 'NOT USE' ? 'selected' : ''}>NOT USE</option>
                        </select>
                    </td>
                    <td class="px-2 py-2 text-center">
                        <button type="button" class="remove-row-btn text-red-500 hover:text-red-700 transition-colors w-8 h-8 flex items-center justify-center rounded-xs hover:bg-red-50 dark:hover:bg-red-900/10">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#settingRowsContainer').append(html);
        }

        $('#addSettingRowBtn').on('click', function() {
            addSettingRow();
        });

        $(document).on('click', '.remove-row-btn', function() {
            $(this).closest('tr').remove();
        });

        $(document).on('click', '.edit-btn', function() {
            resetTabs();
            $('#masterForm')[0].reset();
            $('#settingRowsContainer').empty();
            
            // Clear sketch preview initially
            $('#sketch-preview-img').hide().attr('src', '');
            $('#sketch-preview-placeholder').show();
            
            const data = $(this).data('row');
            
            // Set image preview immediately if available
            if (data.sketch_image) {
                $('#sketch-preview-img').attr('src', data.sketch_image).show();
                $('#sketch-preview-placeholder').hide();
            }
            
            $('#toolId').val(data.id);
            $('#masterForm').data('current-sketch-id', data.sketch_id);
            
            Object.keys(data).forEach(key => {
                if (key !== 'settings') {
                    const el = $(`#masterForm [name="${key}"]`);
                    if (el.length) el.val(data[key]);
                }
            });

            // Populate settings rows
            if (data.settings && data.settings.length > 0) {
                data.settings.forEach(setting => {
                    addSettingRow(setting);
                });
            }
            
            // Trigger category change to load sketches
            $('[name="category_id"]').trigger('change');
            
            $('input[name="_method"]').val('PUT');
            $('#modal-title').text('Edit Tool');
            showMdl('modal-master-form');
        });

        $('#saveMasterBtn').on('click', function() {
            const id = $('#toolId').val();
            const url = id ? `${apiBase}/${id}` : apiBase;
            const data = $('#masterForm').serialize();
            
            $.ajax({
                url: url,
                method: 'POST',
                data: data,
                success: function(res) {
                    toast('success', 'Success', res.message);
                    hideMdl('modal-master-form');
                    masterTable.ajax.reload();
                },
                error: function(xhr) {
                    toast('error', 'Error', xhr.responseJSON?.message || 'Operation failed');
                }
            });
        });

        $(document).on('click', '.delete-btn', function() {
            deleteUrl = `${apiBase}/${$(this).data('id')}`;
            showMdl('modal-delete');
        });

        $('#confirmDelete').on('click', function() {
            $.ajax({
                url: deleteUrl, 
                method: 'POST', 
                data: { _method: 'DELETE', _token: csrf },
                success: (data) => {
                    masterTable.ajax.reload(); 
                    hideMdl('modal-delete'); 
                    toast('success', 'Success', data.message);
                },
                error: (xhr) => { 
                    toast('error', 'Error', xhr.responseJSON?.message || 'Delete failed'); 
                    hideMdl('modal-delete');
                }
            });
        });
    });
</script>
@endpush
