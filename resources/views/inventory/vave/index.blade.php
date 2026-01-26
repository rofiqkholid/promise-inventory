@extends('layouts.app')
@section('title', 'VA/VE Analysis')
@section('page_title', 'Material Efficiency Analysis')
@section('header-title', 'VA/VE Analysis')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">VA/VE Analysis</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Compare baseline RFQ data with production revisions to analyze material efficiency.</p>
        </div>
    </div>

    <x-table id="vaveTable">
        <thead>
            <tr>
                <th scope="col" class="px-4 py-3 w-16 text-center">No</th>
                <th scope="col" class="px-4 py-3">Part No</th>
                <th scope="col" class="px-4 py-3">Part Name</th>
                <th scope="col" class="px-4 py-3 text-center">Customer</th>
                <th scope="col" class="px-4 py-3 text-center">Model</th>
                <th scope="col" class="px-4 py-3 text-center">RFQ Status</th>
                <th scope="col" class="px-4 py-3 text-center">Baseline Weight (Kg)</th>
                <th scope="col" class="px-4 py-3 text-center w-[150px]">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

{{-- RFQ Management Modal --}}
<div id="rfqModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 justify-center items-center w-full h-full bg-slate-900/50 flex">
    <div class="relative p-4 w-full max-w-4xl max-h-[95vh]">
        <div class="relative text-left bg-white rounded-lg shadow dark:bg-gray-800 flex flex-col p-4 sm:p-5">
            <button type="button" class="close-modal-button text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white z-10 transition-colors">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
            </button>
            
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-6" id="rfqModalTitle">Manage Baseline (RFQ)</h3>

            <form id="rfqForm" method="POST">
                @csrf
                <input type="hidden" name="product_id" id="rfq_product_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    {{-- Left Column: Basic Info & Result --}}
                    <div class="space-y-6">
                        <div>
                            <h4 class="text-xs font-semibold text-blue-600 dark:text-blue-400 mb-3 uppercase tracking-wider border-b pb-1">Basic Information</h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Material Spec</label>
                                    <select name="material_spec_id" id="rfq_material_spec_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <option value="">Select Spec</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Density</label>
                                    <input type="number" step="0.001" name="density" id="rfq_density" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.000">
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white font-bold text-blue-600 dark:text-blue-400">Theoretical Weight (Kg)</label>
                                    <input type="number" step="0.001" name="weight_kg" id="rfq_weight_kg" readonly class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white cursor-not-allowed font-bold" placeholder="0.000">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Dimensions --}}
                    <div>
                        <h4 class="text-xs font-semibold text-blue-600 dark:text-blue-400 mb-3 uppercase tracking-wider border-b pb-1">Technical Specification</h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Unit Type</label>
                                <select name="unit_id" id="rfq_unit_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">Select Unit</option>
                                </select>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Thickness (t)</label>
                                <input type="number" step="0.01" name="thickness" id="rfq_thickness" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Width (w)</label>
                                <input type="number" step="0.01" name="width" id="rfq_width" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                            </div>
                            
                            <div id="rfq_length_container">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Length (L)</label>
                                <input type="number" step="0.01" name="length" id="rfq_length" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                            </div>
                            <div id="rfq_length2_container">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Length 2 (L2)</label>
                                <input type="number" step="0.01" name="length_2" id="rfq_length_2" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                            </div>
                            <div id="rfq_pitch_container" class="hidden">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pitch</label>
                                <input type="number" step="0.01" name="pitch" id="rfq_pitch" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 p-4 border-t border-gray-200 dark:border-gray-600">
                    <button type="button" class="close-modal-button text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 w-full sm:w-auto">Cancel</button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-6 py-2.5 text-center w-full sm:w-auto min-w-[100px]">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Comparison Modal --}}
<div id="comparisonModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 flex items-center justify-center w-full h-full bg-slate-900/50 p-4">
    <div class="relative w-full max-w-6xl max-h-[90vh]">
        <div class="relative text-left bg-white rounded-lg shadow-xl dark:bg-gray-800 flex flex-col max-h-[90vh]">
            <button type="button" class="close-modal-button text-gray-400 absolute top-4 right-4 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white z-10 transition-colors">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
            </button>
            
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 pr-12">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white" id="comparisonTitle">VA/VE Material Analysis</h3>
                <p id="comparisonSubtitle" class="text-sm text-gray-500 dark:text-gray-400 mt-1"></p>
            </div>

            <div class="flex-1 overflow-auto p-6">
                <div id="comparisonContainer">
                    {{-- Table will be injected here --}}
                </div>
            </div>
            
            <!-- <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                <button type="button" class="close-modal-button px-5 py-2.5 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600">Close</button>
            </div> -->
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function() {
    const table = window.defaultDataTable('vaveTable', {
        processing: true,
        serverSide: true,
        ajax: '{{ route("inventory.vave.data") }}',
        columns: [
            { data: 'id', className: 'text-center', render: (d, t, r, m) => m.row + 1 },
            { data: 'part_no', className: 'font-medium' },
            { data: 'part_name' },
            { data: 'customer_code', className: 'text-center' },
            { data: 'model_name', className: 'text-center' },
            { 
                data: 'has_rfq', 
                className: 'text-center',
                render: d => d 
                    ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Baseline Ready</span>'
                    : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Not Set</span>'
            },
            { 
                data: 'rfq_weight', 
                className: 'text-center font-mono',
                render: d => d ? parseFloat(d).toFixed(3) : '-'
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: row => `
                    <div class="flex items-center justify-center gap-2">
                        <button class="rfq-button h-8 px-2 inline-flex items-center gap-1.5 text-blue-600 rounded-lg bg-blue-50 hover:bg-blue-100 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-blue-500 transition-all font-medium text-xs" data-id="${row.hash_id}" title="Manage Baseline">
                            <i class="fa-solid fa-file-invoice"></i> RFQ
                        </button>
                        <button class="compare-button h-8 px-2 inline-flex items-center gap-1.5 text-purple-600 rounded-lg bg-purple-50 hover:bg-purple-100 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-purple-500 transition-all font-medium text-xs ${!row.has_rfq ? 'opacity-50 cursor-not-allowed' : ''}" data-id="${row.hash_id}" ${!row.has_rfq ? 'disabled' : ''} title="VA/VE Comparison">
                            <i class="fa-solid fa-chart-line"></i> VA/VE
                        </button>
                    </div>`
            }
        ]
    });

    let vaveDropdownData = {};

    // Populate Dropdown Data for RFQ Form
    function loadRfqDropdowns() {
        $.get('{{ route("inventory.product.dropdownData") }}', function(data) {
            vaveDropdownData = data;
            
            // Clear and populate
            $('#rfq_material_spec_id').empty().append('<option value="">Select Spec</option>');
            $('#rfq_unit_id').empty().append('<option value="">Select Unit</option>');
            
            data.materialSpecs.forEach(ms => {
                $('#rfq_material_spec_id').append(`<option value="${ms.id}">${ms.spec_name}</option>`);
            });
            
            data.units.forEach(u => {
                $('#rfq_unit_id').append(`<option value="${u.hash_id}">${u.code} - ${u.name}</option>`);
            });
        });
    }
    loadRfqDropdowns();

    // Toggle Unit fields visibility for RFQ
    function toggleRfqUnitFields() {
        const unitId = $('#rfq_unit_id').val();
        const selectedUnit = vaveDropdownData.units ? vaveDropdownData.units.find(u => u.hash_id === unitId) : null;
        const unitName = selectedUnit ? selectedUnit.name.toLowerCase() : '';

        // Reset all visibility first
        $('#rfq_length_container').hide();
        $('#rfq_length2_container').hide();
        $('#rfq_pitch_container').hide();

        // Logic Visibility
        if (unitName.includes('sheet')) {
            // Sheet: Show Length, Hide L2 & Pitch
            $('#rfq_length_container').show();
            $('#rfq_length2_container').hide();
            $('#rfq_pitch_container').hide();
        } else if (unitName.includes('trapezoid')) {
            // Trapezoid: Show Length, Length 2. Hide Pitch.
            $('#rfq_length_container').show();
            $('#rfq_length2_container').show();
            $('#rfq_pitch_container').hide();
        } else if (unitName.includes('coil')) {
            // Coil: Show Pitch. Hide Length, Length 2.
            $('#rfq_length_container').hide();
            $('#rfq_length2_container').hide();
            $('#rfq_pitch_container').show();
        } else {
            // Default if unknown or empty: Show Length.
            $('#rfq_length_container').show();
        }

        // Trigger calculation when unit changes
        calculateRfqWeight();
    }

    $('#rfq_unit_id').on('change', function() {
        toggleRfqUnitFields();
    });

    // Auto-calculate RFQ weight
    function calculateRfqWeight() {
        const unitId = $('#rfq_unit_id').val();
        const selectedUnit = vaveDropdownData.units ? vaveDropdownData.units.find(u => u.hash_id === unitId) : null;
        const unitName = selectedUnit ? selectedUnit.name.toLowerCase() : '';

        const t = parseFloat($('#rfq_thickness').val()) || 0;
        const w = parseFloat($('#rfq_width').val()) || 0;
        const density = parseFloat($('#rfq_density').val()) || 0;

        let weight = 0;
        if (unitName.includes('sheet')) {
            const l = parseFloat($('#rfq_length').val()) || 0;
            weight = (t * w * l * density) / 1000000;
        } else if (unitName.includes('coil')) {
            const p = parseFloat($('#rfq_pitch').val()) || 0;
            weight = (t * w * p * density) / 1000000;
        } else if (unitName.includes('trapezoid')) {
            const l = parseFloat($('#rfq_length').val()) || 0;
            const l2 = parseFloat($('#rfq_length_2').val()) || 0;
            const avgL = (l + l2) / 2;
            weight = (t * w * avgL * density) / 1000000;
        } else {
            const l = parseFloat($('#rfq_length').val()) || 0;
            weight = (t * w * l * density) / 1000000;
        }

        $('#rfq_weight_kg').val(weight > 0 ? weight.toFixed(3) : '0.000');
    }

    $('#rfqForm input').on('input change', calculateRfqWeight);

    // Handle RFQ Management
    $(document).on('click', '.rfq-button', function() {
        const id = $(this).data('id');
        $('#rfqForm')[0].reset();
        $('#rfq_product_id').val(id);
        
        $.get(`{{ url('inventory/vave/rfq') }}/${id}`, function(res) {
            $('#rfqModalTitle').text(`Manage Baseline (RFQ) - ${res.product.part_no}`);
            if (res.rfq) {
                $('#rfq_material_spec_id').val(res.rfq.material_spec_id);
                $('#rfq_unit_id').val(res.rfq.unit ? res.rfq.unit.hash_id : '');
                $('#rfq_thickness').val(res.rfq.thickness);
                $('#rfq_width').val(res.rfq.width);
                $('#rfq_length').val(res.rfq.length);
                $('#rfq_length_2').val(res.rfq.length_2);
                $('#rfq_pitch').val(res.rfq.pitch);
                $('#rfq_density').val(res.rfq.density || 7.85);
            } else {
                $('#rfq_density').val(7.85);
            }
            toggleRfqUnitFields();
            $('#rfqModal').removeClass('hidden').addClass('flex');
        });
    });

    // Save RFQ Baseline
    $('#rfqForm').on('submit', function(e) {
        e.preventDefault();
        $.post('{{ route("inventory.vave.storeRfq") }}', $(this).serialize(), function(res) {
            if (res.success) {
                table.ajax.reload();
                $('#rfqModal').addClass('hidden').removeClass('flex');
                Swal.fire({ icon: 'success', title: 'Saved', text: res.message, toast: true, position: 'top-end', timer: 3000, showConfirmButton: false });
            }
        });
    });

    // Handle VA/VE Comparison
    $(document).on('click', '.compare-button', function() {
        const id = $(this).data('id');
        $.get(`{{ url('inventory/vave/comparison') }}/${id}`, function(res) {
            $('#comparisonTitle').text(`VA/VE Material Analysis`);
            $('#comparisonSubtitle').text(`${res.product.part_no} - ${res.product.part_name} (${res.product.customer_code})`);
            
            const rfq = res.rfq;
            const revisions = res.revisions;
            
            // Build table HTML
            let html = `
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-gray-700 dark:text-gray-300 font-semibold">Parameter</th>
                                <th class="px-4 py-3 text-center bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 font-bold border-x border-blue-200 dark:border-blue-800">
                                    Baseline (RFQ)
                                </th>
            `;
            
            // Add revision headers
            revisions.forEach((rev, idx) => {
                html += `
                    <th class="px-4 py-3 text-center text-gray-700 dark:text-gray-300 font-semibold border-l border-gray-200 dark:border-gray-600">
                        ${rev.revision}
                    </th>
                `;
            });
            
            html += `
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            `;
            
            // Material Spec Row
            html += `
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">Material Spec</td>
                    <td class="px-4 py-3 text-center bg-blue-50/30 dark:bg-blue-900/10 border-x border-blue-100 dark:border-blue-900">
                        ${rfq.material_spec ? rfq.material_spec.spec_name : '-'}
                    </td>
            `;
            revisions.forEach(rev => {
                html += `<td class="px-4 py-3 text-center border-l border-gray-200 dark:border-gray-600">${rev.material_spec ? rev.material_spec.spec_name : '-'}</td>`;
            });
            html += `</tr>`;
            
            // Unit Type Row
            html += `
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">Unit Type</td>
                    <td class="px-4 py-3 text-center bg-blue-50/30 dark:bg-blue-900/10 border-x border-blue-100 dark:border-blue-900">
                        ${rfq.unit ? rfq.unit.name : '-'}
                    </td>
            `;
            revisions.forEach(rev => {
                html += `<td class="px-4 py-3 text-center border-l border-gray-200 dark:border-gray-600">${rev.unit ? rev.unit.name : '-'}</td>`;
            });
            html += `</tr>`;
            
            // Thickness Row
            html += `
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">Thickness (t)</td>
                    <td class="px-4 py-3 text-center font-mono bg-blue-50/30 dark:bg-blue-900/10 border-x border-blue-100 dark:border-blue-900">
                        ${parseFloat(rfq.thickness).toFixed(2)}
                    </td>
            `;
            revisions.forEach(rev => {
                const diff = rev.thickness - rfq.thickness;
                const diffClass = diff === 0 ? 'text-gray-600' : (diff > 0 ? 'text-red-600' : 'text-green-600');
                html += `<td class="px-4 py-3 text-center font-mono border-l border-gray-200 dark:border-gray-600">
                    ${parseFloat(rev.thickness).toFixed(2)}
                    ${diff !== 0 ? `<span class="text-xs ${diffClass} ml-1">(${diff > 0 ? '+' : ''}${diff.toFixed(2)})</span>` : ''}
                </td>`;
            });
            html += `</tr>`;
            
            // Width Row
            html += `
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">Width (w)</td>
                    <td class="px-4 py-3 text-center font-mono bg-blue-50/30 dark:bg-blue-900/10 border-x border-blue-100 dark:border-blue-900">
                        ${parseFloat(rfq.width).toFixed(2)}
                    </td>
            `;
            revisions.forEach(rev => {
                const diff = rev.width - rfq.width;
                const diffClass = diff === 0 ? 'text-gray-600' : (diff > 0 ? 'text-red-600' : 'text-green-600');
                html += `<td class="px-4 py-3 text-center font-mono border-l border-gray-200 dark:border-gray-600">
                    ${parseFloat(rev.width).toFixed(2)}
                    ${diff !== 0 ? `<span class="text-xs ${diffClass} ml-1">(${diff > 0 ? '+' : ''}${diff.toFixed(2)})</span>` : ''}
                </td>`;
            });
            html += `</tr>`;
            
            // Length/Pitch Row (dynamic based on unit)
            const rfqUnit = rfq.unit ? rfq.unit.name.toLowerCase() : 'sheet';
            let lengthLabel = 'Length (L)';
            let rfqLengthValue = rfq.length;
            
            if (rfqUnit.includes('coil')) {
                lengthLabel = 'Pitch';
                rfqLengthValue = rfq.pitch;
            } else if (rfqUnit.includes('trapezoid')) {
                lengthLabel = 'Length (L1/L2)';
                rfqLengthValue = `${rfq.length} / ${rfq.length_2}`;
            }
            
            html += `
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">${lengthLabel}</td>
                    <td class="px-4 py-3 text-center font-mono bg-blue-50/30 dark:bg-blue-900/10 border-x border-blue-100 dark:border-blue-900">
                        ${typeof rfqLengthValue === 'number' ? parseFloat(rfqLengthValue).toFixed(2) : rfqLengthValue}
                    </td>
            `;
            revisions.forEach(rev => {
                const revUnit = rev.unit ? rev.unit.name.toLowerCase() : 'sheet';
                let revLengthValue = rev.length;
                if (revUnit.includes('coil')) {
                    revLengthValue = rev.pitch;
                } else if (revUnit.includes('trapezoid')) {
                    revLengthValue = `${rev.length} / ${rev.length_2}`;
                }
                html += `<td class="px-4 py-3 text-center font-mono border-l border-gray-200 dark:border-gray-600">
                    ${typeof revLengthValue === 'number' ? parseFloat(revLengthValue).toFixed(2) : revLengthValue}
                </td>`;
            });
            html += `</tr>`;
            
            // Density Row
            html += `
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">Density</td>
                    <td class="px-4 py-3 text-center font-mono bg-blue-50/30 dark:bg-blue-900/10 border-x border-blue-100 dark:border-blue-900">
                        ${parseFloat(rfq.density).toFixed(3)}
                    </td>
            `;
            revisions.forEach(rev => {
                html += `<td class="px-4 py-3 text-center font-mono border-l border-gray-200 dark:border-gray-600">${parseFloat(rev.density).toFixed(3)}</td>`;
            });
            html += `</tr>`;
            
            // Weight Row (highlighted)
            html += `
                <tr class="bg-gray-100 dark:bg-gray-700 font-semibold">
                    <td class="px-4 py-4 font-bold text-gray-900 dark:text-white">Theoretical Weight (kg)</td>
                    <td class="px-4 py-4 text-center font-mono text-lg bg-blue-100 dark:bg-blue-900/30 border-x border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300">
                        ${parseFloat(rfq.weight_kg).toFixed(3)}
                    </td>
            `;
            revisions.forEach(rev => {
                html += `<td class="px-4 py-4 text-center font-mono text-lg border-l border-gray-300 dark:border-gray-600">${parseFloat(rev.weight_kg).toFixed(3)}</td>`;
            });
            html += `</tr>`;
            
            // Efficiency Row (Saving/Loss)
            html += `
                <tr class="bg-gray-50 dark:bg-gray-800">
                    <td class="px-4 py-4 font-bold text-gray-900 dark:text-white">VA/VE Impact</td>
                    <td class="px-4 py-4 text-center bg-blue-50 dark:bg-blue-900/20 border-x border-blue-200 dark:border-blue-800 text-gray-500 dark:text-gray-400 text-sm">
                        Baseline
                    </td>
            `;
            revisions.forEach(rev => {
                const saving = rfq.weight_kg - rev.weight_kg;
                const savingPct = (saving / rfq.weight_kg) * 100;
                const isEfficient = saving >= 0;
                const bgClass = isEfficient ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20';
                const textClass = isEfficient ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300';
                const icon = isEfficient ? 'fa-arrow-down' : 'fa-arrow-up';
                
                html += `
                    <td class="px-4 py-4 text-center border-l border-gray-300 dark:border-gray-600 ${bgClass}">
                        <div class="flex items-center justify-center gap-2">
                            <i class="fa-solid ${icon} ${textClass}"></i>
                            <span class="font-bold ${textClass} text-lg">${savingPct.toFixed(2)}%</span>
                        </div>
                        <p class="text-xs ${textClass} mt-1">${isEfficient ? 'Saving' : 'Loss'}: ${Math.abs(saving).toFixed(3)} kg</p>
                    </td>
                `;
            });
            html += `</tr>`;
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            $('#comparisonContainer').html(html);
            $('#comparisonModal').removeClass('hidden').addClass('flex');
        });
    });

    function renderDetailItem(label, value) {
        return `
            <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase leading-none mb-1">${label}</p>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">${value}</p>
            </div>
        `;
    }

    function getOrdinal(n) {
        let s = ["th", "st", "nd", "rd"], v = n % 100;
        return s[(v - 20) % 10] || s[v] || s[0];
    }

    $('.close-modal-button').on('click', function() {
        $(this).closest('[tabindex="-1"]').addClass('hidden').removeClass('flex');
    });

});
</script>
<style>
    .font-mono { font-family: 'JetBrains Mono', 'Fira Code', monospace; }
</style>
@endpush
