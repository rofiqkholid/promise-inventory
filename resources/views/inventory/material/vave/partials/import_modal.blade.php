{{-- Import EBD Modal --}}
<div id="importEbdModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 justify-center items-center w-full h-full bg-slate-900/50 transition-all duration-300">
    <div class="relative p-4 w-full max-w-2xl max-h-screen">
        <div class="relative text-left bg-white rounded-xs border border-slate-200 dark:border-gray-700 dark:bg-gray-800 flex flex-col max-h-[90vh] overflow-hidden">
            <button type="button" class="close-modal-button text-slate-400 absolute top-4 right-4 bg-transparent hover:bg-slate-100 dark:hover:bg-gray-700 rounded-xs text-sm w-9 h-9 flex items-center justify-center z-10 transition-all active:scale-95">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-slate-50/80 dark:bg-slate-900/50">
                <h3 class="text-base font-bold text-slate-900 dark:text-white uppercase tracking-widest">Import EBD Data</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium">Bulk import Engineering Breakdown data from Excel</p>
            </div>
            <div class="flex-1 overflow-y-auto custom-scrollbar">
                <form id="importEbdForm" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
                @csrf
                <div class="space-y-6">
                    <div class="p-4 bg-primary-50/50 dark:bg-primary-900/10 border border-primary-100 dark:border-primary-800 rounded-xs flex items-start gap-4">
                        <i class="fa-solid fa-circle-info text-primary-500 mt-1"></i>
                        <div>
                            <p class="text-xs font-bold text-primary-900 dark:text-primary-300 uppercase tracking-wider">Information</p>
                            <p class="text-[11px] text-primary-700 dark:text-primary-400 mt-1 leading-relaxed">The system will automatically match the **Part Number** from your Excel file with the existing Product Master.</p>
                            <a href="{{ route('inventory.vave.downloadTemplate') }}" class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-primary-200 dark:border-primary-800 rounded-xs text-[10px] font-bold text-primary-600 dark:text-primary-400 uppercase tracking-widest hover:bg-primary-50 transition-all shadow-sm">
                                <i class="fa-solid fa-download"></i> Download Template
                            </a>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-[10px] font-black text-slate-500 dark:text-gray-400 uppercase tracking-[0.2em]">1. Excel File</label>
                        <div class="relative">
                            <input type="file" name="file" id="import_file" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-6 file:rounded-xs file:border-0 file:text-[11px] file:font-black file:uppercase file:tracking-widest file:bg-primary-600 file:text-white hover:file:bg-primary-700 border border-slate-200 dark:border-gray-700 rounded-xs bg-slate-50/50 dark:bg-gray-900 cursor-pointer">
                        </div>
                    </div>
                    
                    <div id="sheetSelectionContainer" class="hidden transition-all duration-300">
                        <label class="block mb-2 text-[10px] font-black text-slate-500 dark:text-gray-400 uppercase tracking-[0.2em]">2. Select Target Sheet</label>
                        <div class="relative">
                            <select name="sheet_name" id="import_sheet_name" required class="select2-import w-full bg-slate-50 dark:bg-gray-900 border border-slate-200 dark:border-gray-700 text-slate-900 dark:text-white text-xs font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block p-3 transition-all">
                                <option value="">Please upload a file first...</option>
                            </select>
                            <div id="sheetLoadingSpinner" class="hidden absolute right-10 top-1/2 -translate-y-1/2">
                                <i class="fa-solid fa-circle-notch fa-spin text-primary-500"></i>
                            </div>
                        </div>
                    </div>

                    <div id="targetProductContainer" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4 transition-all duration-300">
                        <div>
                            <label class="block mb-2 text-[10px] font-black text-slate-500 dark:text-gray-400 uppercase tracking-[0.2em]">3. Target Customer <span class="text-red-500">*</span></label>
                            <select name="modal_customer_id" id="modal_import_customer_id" required class="select2-import w-full bg-slate-50 dark:bg-gray-900 border border-slate-200 dark:border-gray-700 text-slate-900 dark:text-white text-xs font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block p-3 transition-all">
                                <option value="">Select Customer...</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-2 text-[10px] font-black text-slate-500 dark:text-gray-400 uppercase tracking-[0.2em]">4. Target Model <span class="text-red-500">*</span></label>
                            <select name="modal_model_id" id="modal_import_model_id" required disabled class="select2-import w-full bg-slate-50 dark:bg-gray-900 border border-slate-200 dark:border-gray-700 text-slate-900 dark:text-white text-xs font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block p-3 transition-all">
                                <option value="">Select Model...</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end pt-4">
                    <div class="flex gap-2">
                        <button type="button" class="close-modal-button px-6 py-2.5 text-[10px] font-bold text-slate-500 bg-white border border-slate-200 rounded-xs uppercase tracking-widest">Cancel</button>
                        <button type="submit" id="btnSubmitImport" class="px-8 py-2.5 text-[10px] font-bold text-white bg-primary-600 rounded-xs uppercase tracking-widest hover:bg-primary-700 disabled:opacity-50">Start Import</button>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
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
        populateModalCustomers();
        $('#importEbdModal').removeClass('hidden').addClass('flex');
    });

    // Handle Import Customer Change
    $('#modal_import_customer_id').on('change', function() {
        const customerId = $(this).val();
        $('#modal_import_model_id').empty().append('<option value="">Select Model...</option>');
        if (customerId) {
            $('#modal_import_model_id').prop('disabled', false);
            $.get('{{ route("inventory.master.product.getModels") }}', { customer_id: customerId }, function(data) {
                data.forEach(m => {
                    $('#modal_import_model_id').append(`<option value="${m.id}">${m.name}</option>`);
                });
            });
        } else {
            $('#modal_import_model_id').prop('disabled', true);
        }
    });

    $('#import_file').on('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Show loading state for sheet selection
        $('#sheetSelectionContainer').removeClass('hidden');
        $('#targetProductContainer').removeClass('hidden');
        $('#sheetLoadingSpinner').removeClass('hidden');
        $('#import_sheet_name').empty().append('<option value="">Loading sheets...</option>').trigger('change').prop('disabled', true);

        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array', bookSheets: true });
                const sheetNames = workbook.SheetNames;
                
                const $sheet = $('#import_sheet_name');
                $sheet.empty().append('<option value="">-- Select Worksheet --</option>');
                sheetNames.forEach(s => $sheet.append(new Option(s, s)));
                $sheet.prop('disabled', false).trigger('change');
            } catch (err) {
                console.error(err);
                window.showToast('Failed to read excel sheets from browser.', 'error');
            } finally {
                $('#sheetLoadingSpinner').addClass('hidden');
            }
        };
        
        reader.onerror = function() {
            window.showToast('Error reading file.', 'error');
            $('#sheetLoadingSpinner').addClass('hidden');
        };

        reader.readAsArrayBuffer(file);
    });

    $('#importEbdForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#btnSubmitImport');
        const originalHtml = $btn.html();
        
        $btn.prop('disabled', true).html('<i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Processing...');
        
        $('#importResult').removeClass('hidden');
        $('#importStatusBox').attr('class', 'p-4 rounded-xs border mb-4 bg-blue-50 text-blue-700 border-blue-100').html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> Processing data, please wait...');
        $('#importLogs').empty();

        const fileInput = $('#import_file')[0];
        if (!fileInput.files || !fileInput.files[0]) {
            $('#importStatusBox').attr('class', 'p-4 rounded-xs border mb-4 bg-rose-50 text-rose-700 border-rose-100').html('<i class="fa-solid fa-circle-exclamation mr-2"></i> Please select a file.');
            $btn.prop('disabled', false).html(originalHtml);
            return;
        }

        const file = fileInput.files[0];
        const customerId = $('#modal_import_customer_id').val();
        const modelId = $('#modal_import_model_id').val();
        const sheetName = $('#import_sheet_name').val();

        if (!customerId || !modelId || !sheetName) {
            $('#importStatusBox').attr('class', 'p-4 rounded-xs border mb-4 bg-rose-50 text-rose-700 border-rose-100').html('<i class="fa-solid fa-circle-exclamation mr-2"></i> Please complete all required fields.');
            $btn.prop('disabled', false).html(originalHtml);
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const fullBase64 = e.target.result;
            const chunkSize = 64 * 1024;
            const totalChunks = Math.ceil(fullBase64.length / chunkSize);
            const uploadId = Date.now().toString() + Math.floor(Math.random() * 1000);
            
            const uploadChunk = function(index) {
                const chunkData = fullBase64.substring(index * chunkSize, (index + 1) * chunkSize);
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
                $btn.html(`<i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Uploading ${percent}% ...`);

                $.ajax({
                    url: '{{ route("inventory.vave.importExcel") }}',
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: JSON.stringify(payload),
                    contentType: 'application/json',
                    success: function(res) {
                        if (index < totalChunks - 1) {
                            uploadChunk(index + 1);
                        } else {
                            $('#importStatusBox').attr('class', 'p-4 rounded-xs border mb-4 bg-emerald-50 text-emerald-700 border-emerald-100').html(res.message);
                            if (res.log) {
                                if (res.log.created) res.log.created.forEach(l => $('#importLogs').append(`<div class="text-emerald-600 italic text-[11px] font-medium">[CREATED] ${l}</div>`));
                                if (res.log.updated) res.log.updated.forEach(l => $('#importLogs').append(`<div class="text-amber-600 italic text-[11px] font-medium">[UPDATED] ${l}</div>`));
                            }
                            if (typeof table !== 'undefined') table.ajax.reload();
                            $btn.prop('disabled', false).html(originalHtml);
                        }
                    },
                    error: function(xhr) {
                        const res = xhr.responseJSON || {};
                        $('#importStatusBox').attr('class', 'p-4 rounded-xs border mb-4 bg-rose-50 text-rose-700 border-rose-100').html(res.message || 'Error occurred.');
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
});
</script>
@endpush
