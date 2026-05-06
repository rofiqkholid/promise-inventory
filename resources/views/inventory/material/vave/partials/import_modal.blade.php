{{-- Import EBD Modal --}}
<div id="importEbdModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 justify-center items-center w-full h-full bg-slate-900/50 flex">
    <div class="relative p-4 w-full max-w-lg max-h-[95vh]">
        <div class="relative bg-white rounded-xs shadow-2xl dark:bg-gray-800 flex flex-col max-h-[90vh] overflow-hidden">
            <button type="button" class="close-modal-button text-gray-400 absolute top-3 right-3 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded-xs text-sm p-2 ml-auto inline-flex items-center dark:hover:bg-gray-700 dark:hover:text-white z-10 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-primary-50/80 dark:bg-slate-900/50">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Import EBD Data</h3>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 font-normal">Bulk import Engineering Breakdown data from Excel.</p>
            </div>
            <form id="importEbdForm" method="POST" enctype="multipart/form-data" class="flex flex-col h-full overflow-hidden min-h-0">
                @csrf
                <div class="p-6 overflow-y-auto min-h-0 flex-1 space-y-6 custom-scrollbar">
                    <div class="bg-blue-50/50 dark:bg-blue-900/20 p-4 rounded-xs border border-blue-100 dark:border-blue-800/50">
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
                            <div>
                                <h4 class="text-[11px] font-bold text-blue-800 dark:text-blue-300 mb-1">Information</h4>
                                <p class="text-[10px] text-blue-600/80 dark:text-blue-400/80 font-normal leading-relaxed">The system will automatically match the **Part Number** from your Excel file with the existing Product Master. Please use the official template:</p>
                                <a href="javascript:void(0)" id="btnDownloadTemplate" class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-gray-800 border border-blue-200 dark:border-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/50 rounded-xs text-xs font-bold text-blue-600 dark:text-blue-400 transition-all shadow-sm active:scale-95">
                                    <i class="fa-solid fa-download"></i> Download Template
                                </a>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">1. Select Excel File <span class="text-red-500">*</span></label>
                        <input type="file" name="file" id="import_file" accept=".xlsx, .xls, .csv" required class="block w-full text-xs text-gray-900 border border-slate-200 rounded-xs cursor-pointer bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 focus:outline-none file:mr-4 file:py-2.5 file:px-4 file:border-0 file:text-[10px] file:font-bold file:uppercase file:tracking-widest file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-slate-800 dark:file:text-primary-400 transition-all">
                        <div id="file_loading" class="hidden mt-2 text-[10px] text-primary-600 font-bold animate-pulse"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Detecting worksheets...</div>
                    </div>

                    <div id="import_next_steps" class="hidden space-y-6 animate-fadeIn">
                        {{-- SHEET NAME --}}
                        <div>
                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">2. Select Worksheet <span class="text-red-500">*</span></label>
                            <select name="sheet_name" id="import_sheet_name" required class="select2-import w-full">
                                <option value="">Select Worksheet...</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- CUSTOMER --}}
                            <div>
                                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">3. Target Customer <span class="text-red-500">*</span></label>
                                <select name="modal_customer_id" id="modal_import_customer_id" required class="select2-import w-full">
                                    <option value="">Select Customer...</option>
                                </select>
                            </div>

                            {{-- MODEL --}}
                            <div>
                                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">4. Target Model <span class="text-red-500">*</span></label>
                                <select name="modal_model_id" id="modal_import_model_id" required disabled class="select2-import w-full">
                                    <option value="">Select Model...</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="importResult" class="hidden text-[10px] font-medium p-4 rounded-xs border"></div>
                </div>
                <div class="flex-none flex items-center justify-end gap-3 px-8 py-5 border-t border-gray-100 dark:border-gray-700 bg-primary-50/80 dark:bg-slate-900/50">
                    <button type="button" class="close-modal-button text-gray-700 bg-white hover:bg-gray-50 rounded-xs border border-gray-300 text-xs font-medium px-6 py-2.5 transition-all active:scale-95 shadow-sm">Cancel</button>
                    <button type="submit" id="btnSubmitImport" class="text-white bg-primary-600 hover:bg-primary-700 rounded-xs text-xs font-medium px-6 py-2.5 text-center shadow-lg shadow-primary-500/10 transition-all active:scale-95 flex items-center gap-2">
                        <i class="fa-solid fa-upload"></i> Process Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
        </div>
    </div>
</div>

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
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out forwards;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for import modal
    function initImportSelect2() {
        $('.select2-import').select2({
            dropdownParent: $('#importEbdModal'),
            width: '100%',
            placeholder: 'Select...'
        });

        // Initialize Model with AJAX (Lazy Loading)
        $('#modal_import_model_id').select2({
            dropdownParent: $('#importEbdModal'),
            width: '100%',
            placeholder: 'Select Model...',
            ajax: {
                url: '{{ route("inventory.master.product.getModels") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        customer_id: $('#modal_import_customer_id').val(),
                        q: params.term // search term
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

    function populateModalCustomers() {
        if ($('#modal_import_customer_id option').length <= 1) {
            $.get('{{ route("inventory.master.product.getCustomers") }}', function(data) {
                $('#modal_import_customer_id').empty().append('<option value="">Select Customer...</option>');
                data.forEach(c => {
                    $('#modal_import_customer_id').append(`<option value="${c.id}">${c.code}</option>`);
                });
            });
        }
    }

    // Handle Open Import Modal
    $(document).on('click', '[data-modal-target="importEbdModal"]', function() {
        initImportSelect2();
        populateModalCustomers();
        $('#importResult').addClass('hidden').html('');
        $('#import_next_steps').addClass('hidden');
        $('#importEbdForm')[0].reset();
        $('#modal_import_customer_id, #modal_import_model_id, #import_sheet_name').val(null).trigger('change');
        $('#importEbdModal').removeClass('hidden').addClass('flex');
    });

    // Handle Import Customer Change
    $('#modal_import_customer_id').on('change', function() {
        const cid = $(this).val();
        const $model = $('#modal_import_model_id');
        
        // Clear previous selection
        $model.val(null).trigger('change');

        if (cid) {
            // Enable immediately as requested
            $model.prop('disabled', false);
        } else {
            $model.prop('disabled', true);
        }
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
                
                const $sheet = $('#import_sheet_name');
                $sheet.empty().append('<option value="">-- Select Worksheet --</option>');
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

        reader.readAsArrayBuffer(file);
    });

    $('#importEbdForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#btnSubmitImport');
        const originalHtml = $btn.html();
        
        $btn.prop('disabled', true).html('<i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Processing...');
        $('#importResult').addClass('hidden').html('');

        const fileInput = $('#import_file')[0];
        if (!fileInput.files || !fileInput.files[0]) {
            window.showToast('Please select a file.', 'error');
            $btn.prop('disabled', false).html(originalHtml);
            return;
        }

        const file = fileInput.files[0];
        const customerId = $('#modal_import_customer_id').val();
        const modelId = $('#modal_import_model_id').val();
        const sheetName = $('#import_sheet_name').val();

        if (!customerId || !modelId || !sheetName) {
            window.showToast('Please complete all required fields.', 'error');
            $btn.prop('disabled', false).html(originalHtml);
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const fullBase64 = e.target.result;
            const base64Only = fullBase64.split(',')[1];
            
            const chunkSize = 1024 * 1024; // Increased to 1MB for faster uploads
            const totalChunks = Math.ceil(base64Only.length / chunkSize);
            const uploadId = 'UP-VAVE-' + Date.now().toString() + '-' + Math.floor(Math.random() * 1000);
            
            const uploadChunk = function(index) {
                const chunkData = base64Only.substring(index * chunkSize, (index + 1) * chunkSize);
                const payload = {
                    sheet_name: sheetName,
                    customer_id: customerId,
                    model_id: modelId,
                    upload_id: uploadId,
                    chunk_index: index,
                    total_chunks: totalChunks,
                    file_base64_chunk: chunkData,
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                let percent = Math.round((index / totalChunks) * 100);
                if (index === totalChunks - 1) percent = 99;
                $btn.html(`<i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Uploading ${percent}% ...`);

                $.ajax({
                    url: VAVE_CONFIG.route_importExcel,
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: JSON.stringify(payload),
                    contentType: 'application/json',
                    success: function(res) {
                        if (index < totalChunks - 1) {
                            uploadChunk(index + 1);
                        } else {
                            $('#importResult').removeClass('hidden bg-rose-50 text-rose-900 border-rose-100')
                                .addClass('bg-emerald-50 text-emerald-900 border-emerald-100 p-5 rounded-sm')
                                .html(res.message);
                            
                            // Add logs if available
                            if (res.log && (res.log.created?.length || res.log.updated?.length)) {
                                let logHtml = '<div class="mt-3 pt-3 border-t border-emerald-200/50 space-y-1">';
                                if (res.log.created) res.log.created.forEach(l => logHtml += `<div class="text-[10px] text-emerald-600 font-medium"><i class="fa-solid fa-plus-circle mr-1"></i> [CREATED] ${l}</div>`);
                                if (res.log.updated) res.log.updated.forEach(l => logHtml += `<div class="text-[10px] text-amber-600 font-medium"><i class="fa-solid fa-pen-to-square mr-1"></i> [UPDATED] ${l}</div>`);
                                logHtml += '</div>';
                                $('#importResult').append(logHtml);
                            }

                            window.showToast('Import completed successfully', 'success');
                            if (typeof table !== 'undefined') table.ajax.reload();
                            $btn.prop('disabled', false).html(originalHtml);
                        }
                    },
                    error: function(xhr) {
                        let msg = xhr.responseJSON?.message || 'Error occurred.';
                        if (xhr.status) msg = `<span class='font-bold'>[Error ${xhr.status}: ${xhr.statusText}]</span><br>${msg}`;

                        $('#importResult').removeClass('hidden bg-emerald-50 text-emerald-900 border-emerald-100')
                            .addClass('bg-rose-50 text-rose-900 border-rose-100 p-5 rounded-sm')
                            .html(msg);
                        
                        window.showToast('Import failed', 'error');
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            };
            uploadChunk(0);
        };
        reader.readAsDataURL(file);
    });

    $('.close-modal-button').on('click', function() {
        $(this).closest('[tabindex="-1"]').addClass('hidden').removeClass('flex');
    });
    $('#btnDownloadTemplate').on('click', function() {
        window.location.href = VAVE_CONFIG.route_downloadTemplate;
    });
});
</script>
@endpush
