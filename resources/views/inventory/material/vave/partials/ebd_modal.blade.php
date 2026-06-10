@php
    $vaveRouteName = request()->route() ? request()->route()->getName() : 'inventory.vave.index';
    $canCreateVave = Auth::user()->hasMenuPermission($vaveRouteName, 'create');
    $canEditVave = Auth::user()->hasMenuPermission($vaveRouteName, 'edit');
    $canDeleteVave = Auth::user()->hasMenuPermission($vaveRouteName, 'delete');
@endphp
{{-- RFQ Management Modal --}}
<div id="rfqModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 justify-center items-center w-full h-full bg-slate-900/50 transition-all duration-300">
    <div class="relative p-4 w-full max-w-4xl max-h-screen">
        <div class="relative bg-white rounded-xs border border-slate-200 dark:border-gray-700 dark:bg-gray-800 flex flex-col max-h-[90vh] overflow-hidden">
            <button type="button" class="close-modal-button text-slate-400 absolute top-4 right-4 bg-transparent hover:bg-slate-100 dark:hover:bg-gray-700 hover:text-slate-900 dark:hover:text-white rounded-xs text-sm w-9 h-9 flex items-center justify-center z-10 transition-all active:scale-95">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
            
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-slate-50/80 dark:bg-slate-900/50">
                <h3 class="text-base font-bold text-slate-900 dark:text-white" id="rfqModalTitle">Manage EBD (Engineering Breakdown)</h3>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 font-normal">Define EBD parameters for VA/VE analysis</p>
            </div>

            <form id="rfqForm" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <input type="hidden" name="product_id" id="rfq_product_id">
                <input type="hidden" name="base_id" id="base_id">
                
                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <div class="p-8">
                        {{-- Version Management Toolbar --}}
                        <div class="mb-6 p-1 bg-slate-50 dark:bg-gray-800/40 rounded-xs border border-slate-200 dark:border-gray-700 overflow-hidden">
                            {{-- Row 1: Actions & Inputs --}}
                            <div class="p-5 flex flex-col md:flex-row md:items-end gap-6 bg-white dark:bg-gray-800 rounded-xs border-b border-slate-100 dark:border-gray-700">
                                {{-- Version Selection --}}
                                <div class="flex-1">
                                    <label class="block mb-2 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">Select Version</label>
                                    <select id="rfq_history_select" class="select2-rfq w-full bg-slate-50 border border-slate-200 text-slate-900 text-xs font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all h-11">
                                        <option value="">-- Select EBD History --</option>
                                    </select>
                                </div>

                                {{-- Version Action Group --}}
                                <div class="flex-[1.5] flex flex-col md:flex-row items-end gap-3">
                                    <div class="flex-1 w-full">
                                        <label class="block mb-2 text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">EBD Suffix</label>
                                        <select name="vave_base_suffix_id" id="rfq_vave_base_suffix_id" class="select2-rfq w-full bg-white border border-slate-200 text-slate-900 text-xs font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all h-11">
                                            <option value="">No Suffix</option>
                                        </select>
                                    </div>
                                    <div class="flex gap-2 h-11 w-full md:w-auto">
                                        @if($canCreateVave)
                                        <button type="button" id="btn_new_baseline" class="flex-1 md:flex-none px-6 text-[10px] font-black text-white bg-primary-600 border border-primary-600 rounded-xs hover:bg-primary-700 transition-all uppercase tracking-widest active:scale-95 flex items-center justify-center gap-2">
                                            <i class="fa-solid fa-plus"></i> NEW VERSION
                                        </button>
                                        @endif
                                        @if($canDeleteVave)
                                        <button type="button" id="btn_delete_baseline" class="px-4 text-[12px] font-black text-rose-600 bg-rose-50 border border-rose-200 rounded-xs hover:bg-rose-100 transition-all hidden uppercase tracking-widest active:scale-95">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Row 2: Information Display --}}
                            <div class="px-5 py-3 flex flex-col md:flex-row items-center justify-between gap-8 bg-slate-50 dark:bg-gray-800/80">
                                <div class="flex items-center gap-3">
                                    <div id="editing_status_badge" class="flex items-center gap-2.5 px-3 py-1 rounded-xs bg-amber-100 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800">
                                        <i class="fa-solid fa-pen-to-square text-[10px] text-amber-500"></i>
                                        <span id="editing_status_text" class="text-[9px] font-black text-amber-700 dark:text-amber-400 uppercase tracking-widest">Editing</span>
                                    </div>
                                    <span id="display_base_name" class="text-xs font-black text-slate-800 dark:text-gray-200 uppercase tracking-widest">EBD 1</span>
                                </div>

                                <div class="flex items-center gap-3 bg-white dark:bg-gray-900 px-4 py-1.5 rounded-xs border border-slate-200 dark:border-gray-700 min-w-[200px]">
                                    <div class="flex items-center gap-2 pr-3 border-r border-slate-100 dark:border-gray-800">
                                        <i class="fa-solid fa-circle-check text-[10px] text-primary-500"></i>
                                        <span class="text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest">Latest Version</span>
                                    </div>
                                    <div class="flex items-center gap-2 pl-1">
                                        <span id="active_baseline_display" class="text-[10px] font-black text-slate-700 dark:text-gray-300">-</span>
                                        <span id="active_weight_display" class="text-[10px] font-bold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/40 px-2 py-0.5 rounded-xs">0.000 Kg</span>
                                    </div>
                                </div>
                                <div id="effective_year_badge" class="hidden flex items-center gap-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-3 py-1.5 rounded-xs">
                                    <i class="fa-solid fa-calendar-days text-[10px] text-amber-500"></i>
                                    <span class="text-[9px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-widest">Effective:</span>
                                    <span id="effective_year_display" class="text-[10px] font-black text-amber-700 dark:text-amber-300">-</span>
                                </div>
                            </div>
                        </div>

                        {{-- Unified Single-Column Layout --}}
                        <div class="space-y-6">
                            {{-- SECTION 1: IDENTITY & EFFECTIVE DATE --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Identity Card --}}
                                <div class="p-5 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="hidden">
                                            <input type="text" name="base_name" id="base_name" readonly>
                                            <input type="hidden" name="is_active" value="1">
                                        </div>
                                        <div>
                                            <label class="block mb-2 text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider">Material Spec</label>
                                            <select name="material_spec_id" id="rfq_material_spec_id" class="select2-rfq w-full">
                                                <option value="">Select Spec</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block mb-2 text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider">Unit Type</label>
                                            <select name="unit_id" id="rfq_unit_id" class="select2-rfq w-full">
                                                <option value="">Select Unit</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                {{-- Effective Date Card --}}
                                <div class="p-5 bg-amber-50/30 dark:bg-amber-900/10 rounded-xs border border-amber-100 dark:border-amber-900/30">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block mb-2 text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                                                <i class="fa-solid fa-calendar-days"></i> From
                                            </label>
                                            <input type="number" name="effective_from" id="rfq_effective_from" min="2000" max="2099" placeholder="{{ date('Y') }}" class="bg-white border border-amber-200 text-amber-700 text-xs font-bold rounded-xs focus:ring-amber-400 focus:border-amber-400 block w-full h-10 px-3">
                                        </div>
                                        <div>
                                            <label class="block mb-2 text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                                                <i class="fa-solid fa-calendar-xmark"></i> To
                                            </label>
                                            <input type="number" name="effective_to" id="rfq_effective_to" min="2000" max="2099" placeholder="2026" class="bg-white border border-amber-200 text-amber-700 text-xs font-bold rounded-xs focus:ring-amber-400 focus:border-amber-400 block w-full h-10 px-3">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- SECTION 2: DIMENSIONS & SPECIFICATION --}}
                            <div class="p-5 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 relative">
                                <h5 class="text-[9px] font-black text-primary-600 dark:text-primary-400 uppercase tracking-[0.2em] mb-4 flex items-center absolute -top-3 left-4 bg-white dark:bg-gray-800 px-3 py-1 border border-slate-200 dark:border-gray-700 rounded-xs shadow-xs">
                                    <i class="fa-solid fa-ruler-combined mr-2"></i> Dimensions & Specification
                                </h5>
                                
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-2">
                                    <div>
                                        <label class="block mb-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Thickness</label>
                                        <input type="number" step="0.01" name="thickness" id="rfq_thickness" class="bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                    </div>
                                    <div>
                                        <label class="block mb-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Width</label>
                                        <input type="number" step="0.01" name="width" id="rfq_width" class="bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                    </div>
                                    <div id="rfq_length_container">
                                        <label class="block mb-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider" id="label_rfq_length">Length</label>
                                        <input type="number" step="0.01" name="length" id="rfq_length" class="bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                    </div>
                                    <div id="rfq_length2_container" class="hidden">
                                        <label class="block mb-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Length 2 (L2)</label>
                                        <input type="number" step="0.01" name="length_2" id="rfq_length_2" class="bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                    </div>
                                    <div id="rfq_pitch_container" class="hidden">
                                        <label class="block mb-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pitch (mm)</label>
                                        <input type="number" step="0.01" name="pitch" id="rfq_pitch" class="bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                    </div>
                                    <div>
                                        <label class="block mb-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Density</label>
                                        <input type="number" step="0.001" name="density" id="rfq_density" value="7.85" class="bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="7.850">
                                    </div>
                                </div>
                            </div>

                            {{-- SECTION 3: YIELD & COST --}}
                            <div class="p-5 bg-slate-50 dark:bg-gray-900/30 rounded-xs border border-slate-100 dark:border-gray-700">
                                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                                    <div>
                                        <label class="block mb-2 text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider">Pcs / Pitch</label>
                                        <input type="number" step="1" name="pcs_per_pitch" id="rfq_pcs_per_pitch" value="1" class="bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="1">
                                    </div>
                                    <div>
                                        <label class="block mb-2 text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider">Pcs / Unit</label>
                                        <input type="number" step="1" name="pcs_per_unit" id="rfq_pcs_per_unit" value="1" class="bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="1">
                                    </div>
                                    <div>
                                        <label class="block mb-2 text-[10px] font-bold text-primary-600 dark:text-primary-400 uppercase tracking-wider">Gross Weight (Kg)</label>
                                        <input type="number" step="0.001" name="weight_kg" id="rfq_weight_kg" readonly class="bg-primary-50 border border-primary-100 text-primary-600 text-xs font-black rounded-xs block w-full h-10 px-3 dark:bg-primary-900/20 dark:border-primary-800 dark:text-primary-300 cursor-not-allowed" placeholder="0.000">
                                    </div>
                                    <div>
                                        <label class="block mb-2 text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider">Net Weight (Kg)</label>
                                        <input type="number" step="0.001" name="net_weight" id="rfq_net_weight" class="bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.000">
                                    </div>
                                    <div>
                                        <label class="block mb-2 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Price (Rp/Kg)</label>
                                        <input type="number" step="0.01" name="material_price" id="rfq_material_price" value="20000" class="bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-black rounded-xs focus:ring-emerald-500 focus:border-emerald-500 block w-full h-10 px-3 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-200" placeholder="0.00">
                                    </div>
                                </div>
                            </div>

                            {{-- SECTION 4: COIL WEIGHT DETAILS (CONDITIONAL) --}}
                            <div id="rfq_coil_section" class="hidden p-5 bg-amber-50/50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/30 rounded-xs relative">
                                <h5 class="text-[9px] font-black text-amber-600 uppercase tracking-[0.2em] mb-4 flex items-center absolute -top-3 left-4 bg-white dark:bg-gray-800 px-3 py-1 border border-amber-100 dark:border-amber-900/30 rounded-xs shadow-xs">
                                    <i class="fa-solid fa-scroll mr-2"></i> Coil Weight Details
                                </h5>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-2">
                                    <div>
                                        <label class="block mb-1.5 text-[9px] font-bold text-slate-400 uppercase tracking-wider">Gross Coil (Kg)</label>
                                        <input type="number" step="0.001" name="gross_coil" id="rfq_gross_coil" class="bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-xs focus:ring-amber-500 focus:border-amber-500 block w-full h-10 px-3" placeholder="0.000">
                                    </div>
                                    <div>
                                        <label class="block mb-1.5 text-[9px] font-bold text-slate-400 uppercase tracking-wider">Top Coil (mm)</label>
                                        <input type="number" step="1" name="top_coil" id="rfq_top_coil" value="500" class="bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-xs focus:ring-amber-500 focus:border-amber-500 block w-full h-10 px-3" placeholder="500">
                                    </div>
                                    <div>
                                        <label class="block mb-1.5 text-[9px] font-bold text-slate-400 uppercase tracking-wider">End Coil (mm)</label>
                                        <input type="number" step="1" name="end_coil" id="rfq_end_coil" value="2500" class="bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-xs focus:ring-amber-500 focus:border-amber-500 block w-full h-10 px-3" placeholder="2500">
                                    </div>
                                    <div>
                                        <label class="block mb-1.5 text-[9px] font-bold text-amber-600 uppercase tracking-wider">Net Coil (Kg)</label>
                                        <input type="number" step="0.001" name="net_coil" id="rfq_net_coil" readonly class="bg-amber-100/30 border border-amber-200 text-amber-700 text-xs font-black rounded-xs block w-full h-10 px-3 cursor-not-allowed" placeholder="0.000">
                                    </div>
                                </div>
                            </div>

                            {{-- SECTION 5: REMARK --}}
                            <div>
                                <label for="remark" class="block mb-2 text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">Remark / Notes</label>
                                <textarea name="remark" id="remark" rows="2" class="block p-3 w-full text-xs font-medium text-slate-700 bg-white rounded-xs border border-slate-200 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all placeholder-slate-300" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-8 py-6 border-t border-slate-100 dark:border-gray-700 bg-slate-50/50 dark:bg-slate-900/40">
                    <button type="button" class="close-modal-button text-slate-500 bg-white hover:bg-slate-50 focus:outline-none rounded-xs border border-slate-200 text-xs font-medium px-8 py-2.5 transition-all active:scale-95">
                        Cancel
                    </button>
                    @if($canCreateVave || $canEditVave)
                    <button type="submit" class="text-white bg-primary-600 hover:bg-primary-700 focus:outline-none font-medium rounded-xs text-xs px-8 py-2.5 text-center transition-all active:scale-95">
                        <i class="fa-solid fa-save mr-2"></i> Save EBD
                    </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Variables
    const CAN_CREATE_VAVE = @json($canCreateVave);
    const CAN_EDIT_VAVE = @json($canEditVave);
    const CAN_DELETE_VAVE = @json($canDeleteVave);

    window.baseHistory = [];
    window.baseRevisions = [];
    window.latestRevision = null;

    // Helper to set form readonly state
    function setEbdFormReadonly(isReadonly) {
        // Force readonly if user lacks both create and edit permissions
        if (!CAN_CREATE_VAVE && !CAN_EDIT_VAVE) {
            isReadonly = true;
        }

        // Find all fields except the history selector and product ID
        const formFields = $('#rfqForm').find('input, select, textarea').not('#rfq_history_select, #rfq_product_id, [name="_token"]');
        formFields.prop('disabled', isReadonly);
        
        // Handle select2 specifically
        $('#rfq_material_spec_id, #rfq_unit_id, #rfq_vave_base_suffix_id').trigger('change.select2');

        if (isReadonly) {
            $('#rfqForm button[type="submit"]').addClass('hidden');
            $('#btn_delete_baseline').addClass('hidden');
            $('#editing_status_badge').removeClass('bg-amber-100 border-amber-200 bg-primary-100 border-primary-200')
                .addClass('bg-slate-100 border-slate-200 dark:bg-slate-900/40 dark:border-slate-800');
            $('#editing_status_badge i').removeClass('fa-pen-to-square text-amber-500 fa-plus-circle text-primary-500')
                .addClass('fa-lock text-slate-400');
            $('#editing_status_text').text('Readonly History').removeClass('text-amber-700 text-primary-700')
                .addClass('text-slate-600 dark:text-slate-400');
        } else {
            if (CAN_CREATE_VAVE || CAN_EDIT_VAVE) {
                $('#rfqForm button[type="submit"]').removeClass('hidden');
            } else {
                $('#rfqForm button[type="submit"]').addClass('hidden');
            }
            // Badge state will be updated by the caller (Editing/New Create)
        }
    }

    // Reset and autofill EBD form from Product Data
    function resetAndAutoFillEbdForm() {
        if (!window.latestRevision) return;
        const data = window.latestRevision;
        loadEbdToForm(data);
        $('#base_id').val('');
        $('#rfq_length_2').val('');
        $('#rfq_pitch').val('');
        $('#rfq_density').val(7.85); 
        $('#rfq_pcs_per_unit').val(1);
        $('#rfq_pcs_per_pitch').val(1);
        $('#rfq_net_weight').val('');
        $('#remark').val('');
        $('#rfq_effective_from').val('');
        $('#rfq_effective_to').val('');
        $('#effective_year_badge').addClass('hidden').removeClass('flex');
        $('#rfq_material_spec_id').val('').trigger('change');
        $('#rfq_unit_id').val('').trigger('change');
        $('#rfq_material_price').val('20000');
        $('#rfq_gross_coil').val(0);
        $('#rfq_top_coil').val(500);
        $('#rfq_end_coil').val(2500);
        $('#rfq_net_coil').val(0);

        if (window.latestRevision) {
            if (window.latestRevision.material_spec) {
                $('#rfq_material_spec_id').val(window.latestRevision.material_spec.hash_id).trigger('change');
            }
            if (window.latestRevision.unit) {
                $('#rfq_unit_id').val(window.latestRevision.unit.hash_id).trigger('change');
            }
            if (window.latestRevision.pcs_per_unit) {
                $('#rfq_pcs_per_unit').val(parseInt(window.latestRevision.pcs_per_unit));
            }
            if (window.latestRevision.pcs_per_pitch) {
                $('#rfq_pcs_per_pitch').val(parseInt(window.latestRevision.pcs_per_pitch));
            }
        }
        calculateEbdWeight();
    }

    // Load Data to Form
    function loadEbdToForm(data) {
        if (!data) return;
        $('#base_id').val(data.hash_id);
        $('#base_name').val(data.base_name);
        
        let specId = (data.material_spec ? data.material_spec.hash_id : '');
        if (!specId && window.latestRevision && window.latestRevision.material_spec) {
            specId = window.latestRevision.material_spec.hash_id;
        }
        $('#rfq_material_spec_id').val(specId).trigger('change');

        let unitId = (data.unit ? data.unit.hash_id : '');
        if (!unitId && window.latestRevision && window.latestRevision.unit) {
            unitId = window.latestRevision.unit.hash_id;
        }
        $('#rfq_unit_id').val(unitId).trigger('change');

        let vaveBaseSuffixId = '';
        if (data.suffix) vaveBaseSuffixId = data.suffix.hash_id;
        $('#rfq_vave_base_suffix_id').val(vaveBaseSuffixId).trigger('change');
        
        $('#rfq_thickness').val(parseFloat(data.thickness || 0));
        $('#rfq_width').val(parseFloat(data.width || 0));
        $('#rfq_length').val(parseFloat(data.length || 0));
        $('#rfq_length_2').val(parseFloat(data.length_2 || 0));
        $('#rfq_pitch').val(parseFloat(data.pitch || 0));
        $('#rfq_density').val(parseFloat(data.density || 7.85));
        $('#rfq_pcs_per_unit').val(parseInt(data.pcs_per_unit || 1));
        $('#rfq_pcs_per_pitch').val(parseInt(data.pcs_per_pitch || 1));
        $('#rfq_gross_coil').val(parseFloat(data.gross_coil || 0));
        $('#rfq_top_coil').val(parseFloat(data.top_coil || 500));
        $('#rfq_end_coil').val(parseFloat(data.end_coil || 2500));
        $('#rfq_net_coil').val(parseFloat(data.net_coil || 0));
        $('#rfq_net_weight').val(parseFloat(data.net_weight || 0));
        $('#rfq_material_price').val(parseFloat(data.material_price || 20000));
        $('#remark').val(data.remark);

        $('#rfq_effective_from').val(data.effective_from || '');
        $('#rfq_effective_to').val(data.effective_to || '');
        const fromYear = data.effective_from || '-';
        const toYear   = data.effective_to   ? data.effective_to : 'Present';
        if (data.effective_from) {
            $('#effective_year_display').text(`${fromYear} – ${toYear}`);
            $('#effective_year_badge').removeClass('hidden').addClass('flex');
        } else {
            $('#effective_year_badge').addClass('hidden').removeClass('flex');
        }

        calculateEbdWeight();
    }

    // Handle RFQ Management dengan Loading State
    $(document).on('click', '.rfq-button', function() {
        const $btn = $(this);
        const id = $btn.data('id');
        const $icon = $btn.find('.btn-icon');
        const originalIconClass = $icon.attr('class');

        if($btn.prop('disabled')) return;
        $btn.prop('disabled', true).addClass('opacity-70 cursor-wait');
        $icon.attr('class', 'fa-solid fa-circle-notch fa-spin');

        $('#rfqForm')[0].reset();
        $('#rfq_product_id').val(id);
        $('#base_id').val(''); 
        $('#btn_new_baseline').addClass('hidden');
        $('#btn_delete_baseline').addClass('hidden');
        
        $.get(`${VAVE_CONFIG.url_base}/${id}`, function(res) {
            $('#rfqModalTitle').text(`Manage EBD - ${res.product.part_no}`);
            window.baseRevisions = res.revisions;
            window.latestRevision = (res.revisions && res.revisions.length > 0) ? res.revisions[0] : null;

            const suffixSelect = $('#rfq_vave_base_suffix_id').empty().append('<option value="">No Suffix</option>');
            if(res.baseSuffixes) res.baseSuffixes.forEach(s => suffixSelect.append(`<option value="${s.hash_id}">${s.name}</option>`));

            const histSelect = $('#rfq_history_select').empty().append('<option value="">-- Load Existing EBD --</option>');
            if(res.baseHistory) res.baseHistory.forEach((h, index) => {
                const suffix = h.suffix ? ` - ${h.suffix.name}` : '';
                histSelect.append(`<option value="${h.hash_id}" ${index === 0 ? 'selected' : ''}>${h.base_name || 'EBD'}${suffix} - ${parseFloat(h.weight_kg).toFixed(3)}kg${h.is_active ? ' (Active)' : ''}</option>`);
            });
            window.baseHistory = res.baseHistory;

            if (res.base && res.base.base_name.toUpperCase().startsWith('EBD')) {
                loadEbdToForm(res.base);
                $('#display_base_name').text(res.base.base_name);
                if (CAN_CREATE_VAVE) $('#btn_new_baseline').removeClass('hidden'); 
                if (CAN_DELETE_VAVE) $('#btn_delete_baseline').removeClass('hidden');
                setEbdFormReadonly(false);

                // Explicitly set to Editing
                $('#editing_status_badge').removeClass('bg-primary-100 border-primary-200 bg-slate-100 border-slate-200').addClass('bg-amber-100 border-amber-200');
                $('#editing_status_badge i').removeClass('fa-plus-circle text-primary-500 fa-lock text-slate-400').addClass('fa-pen-to-square text-amber-500');
                $('#editing_status_text').text('Editing').removeClass('text-primary-700 text-slate-600').addClass('text-amber-700');
            } else {
                // Check if the current active base is SQ
                if (res.base && res.base.base_name.toUpperCase().startsWith('SQ')) {
                    loadEbdToForm(res.base);
                    $('#display_base_name').text(res.base.base_name);
                    if (CAN_CREATE_VAVE) $('#btn_new_baseline').removeClass('hidden'); 
                    setEbdFormReadonly(true);
                } else {
                    // If no active EBD or SQ, calculate next EBD number
                    const ebdCount = (window.baseHistory || []).filter(h => h.base_name.toUpperCase().startsWith('EBD')).length;
                    const nextEbdName = `EBD ${ebdCount + 1}`;
                    $('#base_name').val(nextEbdName);
                    $('#display_base_name').text(nextEbdName);
                    resetAndAutoFillEbdForm();
                    $('#btn_delete_baseline').addClass('hidden');
                    $('#btn_new_baseline').addClass('hidden'); 
                    setEbdFormReadonly(false);

                    // Explicitly set to New Create
                    $('#editing_status_badge').removeClass('bg-amber-100 border-amber-200 bg-slate-100 border-slate-200').addClass('bg-primary-100 border-primary-200');
                    $('#editing_status_badge i').removeClass('fa-pen-to-square text-amber-500 fa-lock text-slate-400').addClass('fa-plus-circle text-primary-500');
                    $('#editing_status_text').text('New Create').removeClass('text-amber-700 text-slate-600').addClass('text-primary-700');
                }
            }

            if (res.base) {
                $('#active_baseline_display').text(`${res.base.base_name}${res.base.suffix ? ` - ${res.base.suffix.name}` : ''}`);
                $('#active_weight_display').text(`${parseFloat(res.base.weight_kg || 0).toFixed(3)} Kg`);
            } else {
                $('#active_baseline_display').text('No EBD Set');
                $('#active_weight_display').text('-');
            }
            
            if (typeof toggleEbdUnitFields === 'function') toggleEbdUnitFields();
            $('#rfqModal').removeClass('hidden').addClass('flex');
        }).always(function() {
            $btn.prop('disabled', false).removeClass('opacity-70 cursor-wait');
            $icon.attr('class', originalIconClass);
        }).fail(function() {
            window.showToast('Error loading RFQ data', 'error');
        });
    });

    $('#rfq_history_select').on('change', function() {
        const id = $(this).val();
        if(!id || id === 'NEW_CREATE') return;
        const selected = window.baseHistory ? window.baseHistory.find(h => h.hash_id == id) : null;
        if (selected) {
             loadEbdToForm(selected);
             $('#display_base_name').text(selected.base_name);
             
             const isSQ = selected.base_name.toUpperCase().startsWith('SQ');
             if (isSQ) {
                 setEbdFormReadonly(true);
                 if (CAN_CREATE_VAVE) $('#btn_new_baseline').removeClass('hidden');
             } else {
                 setEbdFormReadonly(false);
                 $('#editing_status_badge').removeClass('bg-primary-100 border-primary-200').addClass('bg-amber-100 border-amber-200');
                 $('#editing_status_badge i').removeClass('fa-plus-circle text-primary-500 fa-lock text-slate-400').addClass('fa-pen-to-square text-amber-500');
                 $('#editing_status_text').text('Editing').removeClass('text-primary-700 text-slate-600').addClass('text-amber-700');
                 if (CAN_CREATE_VAVE) $('#btn_new_baseline').removeClass('hidden');
                 if (CAN_DELETE_VAVE) $('#btn_delete_baseline').removeClass('hidden');
             }
             $('#rfq_history_select option[value="NEW_CREATE"]').remove();
        }
    });
    
    $('#btn_new_baseline').on('click', function() {
        const currentData = {
            rfq_material_spec_id: $('#rfq_material_spec_id').val(),
            rfq_unit_id: $('#rfq_unit_id').val(),
            thickness: $('#rfq_thickness').val(),
            width: $('#rfq_width').val(),
            length: $('#rfq_length').val(),
            length_2: $('#rfq_length_2').val(),
            pitch: $('#rfq_pitch').val(),
            density: $('#rfq_density').val(),
            net_weight: $('#rfq_net_weight').val(),
            material_price: $('#rfq_material_price').val()
        };

        $('#btn_new_baseline').addClass('hidden');
        $('#btn_delete_baseline').addClass('hidden');
        
        const count = (window.baseHistory ? window.baseHistory.length : 0) + 1;
        const newName = `EBD ${count}`;

        const histSelect = $('#rfq_history_select');
        if (histSelect.find('option[value="NEW_CREATE"]').length === 0) histSelect.prepend(`<option value="NEW_CREATE">${newName} (New)</option>`);
        histSelect.val('NEW_CREATE').trigger('change.select2');
        
        $('#base_name').val(newName);
        $('#display_base_name').text(newName);
        $('#base_id').val('');

        $('#editing_status_badge').removeClass('bg-amber-100 border-amber-200 bg-slate-100 border-slate-200').addClass('bg-primary-100 border-primary-200');
        $('#editing_status_badge i').removeClass('fa-pen-to-square text-amber-500 fa-lock text-slate-400').addClass('fa-plus-circle text-primary-500');
        $('#editing_status_text').text('New Create').removeClass('text-amber-700 text-slate-600').addClass('text-primary-700');
        
        setEbdFormReadonly(false);

        $('#rfq_material_spec_id').val(currentData.rfq_material_spec_id).trigger('change');
        $('#rfq_unit_id').val(currentData.rfq_unit_id).trigger('change');
        $('#rfq_vave_base_suffix_id').val('').trigger('change');
        $('#rfq_thickness').val(currentData.thickness);
        $('#rfq_width').val(currentData.width);
        $('#rfq_length').val(currentData.length);
        $('#rfq_length_2').val(currentData.length_2);
        $('#rfq_pitch').val(currentData.pitch);
        $('#rfq_density').val(currentData.density);
        $('#rfq_net_weight').val(currentData.net_weight);
        $('#rfq_material_price').val(currentData.material_price);
        calculateEbdWeight();
    });

    $('#btn_delete_baseline').on('click', function() {
        const id = $('#base_id').val();
        if (!id) return;
        Swal.fire({
            title: 'Are you sure?',
            text: "This baseline will be permanently deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${VAVE_CONFIG.url_base}/${id}`,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                    success: function(res) {
                        if (res.success) {
                            if(typeof window.vaveTable !== 'undefined') window.vaveTable.ajax.reload();
                            $('#rfqModal').addClass('hidden').removeClass('flex');
                            window.showToast(res.message, 'success');
                        }
                    }
                });
            }
        });
    });

    $('#rfqForm').on('submit', function(e) {
        e.preventDefault();
        $.post(VAVE_CONFIG.route_storeBase, $(this).serialize(), function(res) {
            if (res.success) {
                if(typeof window.vaveTable !== 'undefined') window.vaveTable.ajax.reload();
                $('#rfqModal').addClass('hidden').removeClass('flex');
                window.showToast(res.message, 'success');
            }
        });
    });

    // Populate Dropdown Data for EBD Form
    function loadEbdDropdowns() {
        $.get('{{ route("inventory.master.product.dropdownData") }}', function(data) {
            window.vaveDropdownData = data;
            $('#rfq_material_spec_id').empty().append('<option value="">Select Spec</option>');
            $('#rfq_unit_id').empty().append('<option value="">Select Unit</option>');
            data.materialSpecs.forEach(ms => {
                $('#rfq_material_spec_id').append(`<option value="${ms.hash_id}">${ms.spec_name}</option>`);
            });
            data.units.forEach(u => {
                $('#rfq_unit_id').append(`<option value="${u.hash_id}">${u.code} - ${u.name}</option>`);
            });
        });
    }
    loadEbdDropdowns();

    // Toggle Unit fields visibility for EBD
    window.toggleEbdUnitFields = function() {
        const unitId = $('#rfq_unit_id').val();
        const selectedUnit = window.vaveDropdownData.units ? window.vaveDropdownData.units.find(u => u.hash_id === unitId) : null;
        const unitName = selectedUnit ? selectedUnit.name.toLowerCase() : '';

        $('#rfq_length_container, #rfq_length2_container, #rfq_pitch_container').hide();

        $('#rfq_pcs_per_unit').prop('readonly', unitName.includes('coil')).toggleClass('bg-slate-50 cursor-not-allowed', unitName.includes('coil'));
        
        $('#rfq_coil_section').hide();

        if (unitName.includes('sheet')) {
            $('#label_rfq_length').text('Length (mm)');
            $('#rfq_length_container, #rfq_pitch_container').show();
        } else if (unitName.includes('trapezoid')) {
            $('#label_rfq_length').text('Length 1 (L1)');
            $('#rfq_length_container, #rfq_length2_container, #rfq_pitch_container').show();
        } else if (unitName.includes('coil')) {
            $('#rfq_pitch_container, #rfq_coil_section').show();
        } else {
            $('#label_rfq_length').text('Length (mm)');
            $('#rfq_length_container').show();
        }
        calculateEbdWeight();
    }

    $('#rfq_unit_id').on('change', function() {
        toggleEbdUnitFields();
    });

    // Auto-calculate EBD weight
    window.calculateEbdWeight = function() {
        const unitId = $('#rfq_unit_id').val();
        const selectedUnit = window.vaveDropdownData.units ? window.vaveDropdownData.units.find(u => u.hash_id === unitId) : null;
        const unitName = selectedUnit ? selectedUnit.name.toLowerCase() : '';

        const t = parseFloat($('#rfq_thickness').val()) || 0;
        const w = parseFloat($('#rfq_width').val()) || 0;
        const density = parseFloat($('#rfq_density').val()) || 0;
        const pcsPerUnit = parseInt($('#rfq_pcs_per_unit').val()) || 1;
        const pcsPerPitch = parseInt($('#rfq_pcs_per_pitch').val()) || 1;

        let weight = 0;
        if (unitName.includes('sheet')) {
            const l = parseFloat($('#rfq_length').val()) || 0;
            weight = ((t * w * l * density) / 1000000) / Math.max(1, pcsPerUnit);
        } else if (unitName.includes('coil')) {
            const p = parseFloat($('#rfq_pitch').val()) || 0;
            const grossKg = parseFloat($('#rfq_gross_coil').val()) || 0;
            const topMm = parseFloat($('#rfq_top_coil').val()) || 0;
            const endMm = parseFloat($('#rfq_end_coil').val()) || 0;

            const weightPerPitch = (t * w * p * density) / 1000000;
            const weightPerMm = (t * w * 1 * density) / 1000000;
            
            const topKg = topMm * weightPerMm;
            const endKg = endMm * weightPerMm;
            const netCoil = Math.max(0, grossKg - (topKg + endKg));

            $('#rfq_net_coil').val(netCoil > 0 ? netCoil.toFixed(3) : '0.000');
            weight = weightPerPitch / Math.max(1, pcsPerPitch);

            if (weightPerPitch > 0) {
                const estPcs = Math.floor(Math.max(0, netCoil) / weightPerPitch) * pcsPerPitch;
                $('#rfq_pcs_per_unit').val(estPcs);
            } else {
                $('#rfq_pcs_per_unit').val(0);
            }
        } else if (unitName.includes('trapezoid')) {
            const l = parseFloat($('#rfq_length').val()) || 0;
            const l2 = parseFloat($('#rfq_length_2').val()) || 0;
            const avgL = (l + l2) / 2;
            weight = ((t * w * avgL * density) / 1000000) / Math.max(1, pcsPerUnit);
        } else {
            const l = parseFloat($('#rfq_length').val()) || 0;
            weight = ((t * w * l * density) / 1000000) / Math.max(1, pcsPerUnit);
        }
        $('#rfq_weight_kg').val(weight > 0 ? weight.toFixed(3) : '0.000');
    }

    $('#rfqForm input').on('input change', calculateEbdWeight);

    $('.select2-rfq').select2({
        dropdownParent: $('#rfqModal'),
        width: '100%'
    });
});
</script>
@endpush
