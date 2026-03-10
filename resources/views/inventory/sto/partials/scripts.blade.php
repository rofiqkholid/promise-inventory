@push('scripts')
<script>
    const scanUrl = "{{ route('inventory.sto.scan', $stoEvent->hash_id) }}";
    const saveUrl = "{{ route('inventory.sto.saveCount', $stoEvent->hash_id) }}";
    const csrfToken = "{{ csrf_token() }}";
    
    // Inject reasons for inline dropdowns
    const stoReasons = @json(\App\Models\InventoryModel\StoReason::where('is_active', true)->get());

    // JS Formatter Helpers
    function formatQtyHtml(qty, pcsPerUnit, unitCode, prefix = '') {
        qty = parseFloat(qty || 0);
        let pcs = qty * pcsPerUnit;
        let pcsDisplay = Math.abs(pcs).toLocaleString(undefined, { maximumFractionDigits: 0 });
        
        if (pcsPerUnit == 1) return `<span class='font-bold'>${prefix}${pcsDisplay}</span>`;
        
        let unitDisplay = Math.abs(qty).toLocaleString(undefined, { maximumFractionDigits: 2 });
        return `
            <div class='flex flex-col items-center justify-center'>
                <span class='font-bold'>${prefix}${pcsDisplay}</span>
                <span class='text-[10px] text-gray-400 leading-none mt-0.5'>(${unitDisplay} ${unitCode})</span>
            </div>`;
    }

    function formatCurrencyHtml(val, isDiff = false) {
        val = parseFloat(val || 0);
        if (val == 0) {
            if (isDiff) return '<span class="text-[11px] font-mono font-bold text-green-600">0</span>';
            return '<span class="text-gray-300">-</span>';
        }
        
        let color = 'text-gray-600 dark:text-gray-400';
        let prefix = '';
        if (isDiff) {
            color = 'text-red-600';
            prefix = val > 0 ? '+' : '-';
        }
        
        return `<span class="text-[11px] font-mono font-bold ${color}">${prefix}${Math.abs(val).toLocaleString()}</span>`;
    }

    // --- Modal Handlers ---
    function openRemainingModal() {
        document.getElementById('remainingItemsModal').classList.remove('hidden');
        document.getElementById('remainingItemsModal').classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeRemainingModal() {
        document.getElementById('remainingItemsModal').classList.add('hidden');
        document.getElementById('remainingItemsModal').classList.remove('flex');
        document.body.style.overflow = 'auto';
    }

    function openRejectModal() {
        document.getElementById('rejectModal').classList.remove('hidden');
        document.getElementById('rejectModal').classList.add('flex');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
        document.getElementById('rejectModal').classList.remove('flex');
    }

    // --- Confirmation Handlers ---
    function confirmSubmitForCheck() {
        Swal.fire({
            title: 'Submit for Verification?',
            text: "This will notify the checker to review the data. You won't be able to edit while it's in review.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            confirmButtonText: 'Yes, Submit'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('submitForCheckForm').submit();
        });
    }

    function confirmVerify() {
        Swal.fire({
            title: 'Verify Data?',
            text: "Confirm that all counted data is accurate and ready for final approval.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            confirmButtonText: 'Yes, Verify'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('verifyForm').submit();
        });
    }

    function confirmReopen() {
        Swal.fire({
            title: 'Reopen Event?',
            text: "This will return the event to OPEN status for further editing.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Reopen'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('reopenForm').submit();
        });
    }

    // --- Stats Update Logic ---
    window.updateStatsCard = function(stats) {
        if (!stats) return;
        
        const formatNumber = (num, dec = 0) => parseFloat(num || 0).toLocaleString(undefined, {minimumFractionDigits: dec});
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.innerText = val;
        };

        setVal('stat-total-items', stats.total_items);
        setVal('stat-progress', stats.progress || 0);
        setVal('stat-total-recorded-pcs', formatNumber(stats.total_recorded_pcs));
        setVal('stat-total-missing-items', stats.total_missing_items);
        setVal('stat-total-increase-pcs', formatNumber(stats.total_increase_pcs) + ' Pcs');
        setVal('stat-total-increase', '(' + formatNumber(stats.total_increase) + ' Unit / ' + stats.count_increase + ' items)');
        setVal('stat-total-decrease-pcs', formatNumber(stats.total_decrease_pcs) + ' Pcs');
        setVal('stat-total-decrease', '(' + formatNumber(stats.total_decrease) + ' Unit / ' + stats.count_decrease + ' items)');
        
        const netPcsPrefix = stats.net_adjustment_pcs >= 0 ? '+' : '';
        setVal('stat-net-adjustment-pcs', netPcsPrefix + formatNumber(stats.net_adjustment_pcs) + ' Pcs');
        
        const netUnitPrefix = stats.net_adjustment >= 0 ? '+' : '';
        setVal('stat-net-adjustment', '(' + netUnitPrefix + formatNumber(stats.net_adjustment) + ' Unit)');
        
        const amountPrefix = stats.net_amount_impact > 0 ? '+' : (stats.net_amount_impact < 0 ? '-' : '');
        setVal('stat-net-amount-impact', amountPrefix + formatNumber(Math.abs(stats.net_amount_impact)));
        
        setVal('stat-total-matched', stats.total_matched);
        setVal('table-total-matched', stats.total_matched);
        setVal('table-total-diff', stats.total_diff);

        // Financial Impact Color
        const amountBg = document.getElementById('stat-net-amount-bg');
        const amountText = document.getElementById('stat-net-amount-impact');
        if (amountBg && amountText) {
            if (stats.net_amount_impact >= 0) {
                amountBg.classList.replace('bg-rose-50', 'bg-emerald-50');
                amountBg.classList.replace('text-rose-600', 'text-emerald-600');
                amountText.classList.replace('text-rose-700', 'text-emerald-700');
            } else {
                amountBg.classList.replace('bg-emerald-50', 'bg-rose-50');
                amountBg.classList.replace('text-emerald-600', 'text-rose-600');
                amountText.classList.replace('text-emerald-700', 'text-rose-700');
            }
        }

        // Banner Alert
        const banner = document.getElementById('missing-alert-banner');
        if (banner) {
            if (stats.total_missing_items > 0) {
                banner.classList.remove('hidden');
                setVal('banner-missing-count', stats.total_missing_items);
            } else {
                banner.classList.add('hidden');
            }
        }
    };

    let table;
    $(document).ready(function() {
        if (window.defaultDataTable) {
            table = window.defaultDataTable('#stoDetailsTable', {
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('inventory.sto.detailsData', $stoEvent->hash_id) }}",
                    type: 'GET'
                },
                columns: [
                    { data: 'row_number', className: 'text-center font-bold text-gray-500', orderable: false, searchable: false },
                    { data: 'updated_at', className: 'text-[10px] font-mono font-bold text-gray-500' },
                    { 
                        data: null, 
                        className: 'font-medium',
                        render: function(data) {
                            return `
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200">${data.part_no} - ${data.revision}</span>
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400 leading-tight uppercase">${data.part_name}</span>
                                </div>`;
                        }
                    },
                    { data: 'auditor', className: 'text-xs font-semibold text-primary-600 dark:text-primary-400' },
                    { 
                        data: null, 
                        className: 'text-center font-mono text-sm group-hover:bg-gray-50 dark:group-hover:bg-gray-800',
                        render: function(data) {
                            return formatQtyHtml(data.system_qty, data.pcs_per_unit, data.unit_code);
                        }
                    },
                    { 
                        data: 'system_amount', 
                        className: 'text-right pr-4 bg-gray-50/30 dark:bg-gray-800/20',
                        render: (val) => formatCurrencyHtml(val)
                    },
                    { 
                        data: null, 
                        className: 'text-center bg-primary-50/10 dark:bg-primary-900/5',
                        render: function(data) {
                            if (data.can_edit_inline) {
                                return `
                                    <div class="flex items-center justify-center gap-1">
                                        <input type="number" step="any" 
                                            class="qty-input text-center font-medium text-sm px-2 py-1 border border-slate-200 dark:border-gray-700 rounded-xs focus:ring-0 focus:border-primary-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100" 
                                            style="width: 80px; min-width: 80px;"
                                            data-detail-id="${data.hash_id}" 
                                            data-product-id="${data.product_hash_id}"
                                            value="${data.real_qty_input}" 
                                            placeholder="Qty" />
                                        <span class="text-[9px] font-bold text-gray-400 uppercase">${data.unit_code}</span>
                                    </div>`;
                            }
                            return `<div class="text-primary-600 dark:text-primary-400">${formatQtyHtml(data.real_qty_input, data.pcs_per_unit, data.unit_code)}</div>`;
                        }
                    },
                    { 
                        data: 'real_amount', 
                        className: 'text-right pr-4 bg-primary-50/20 dark:bg-primary-900/10',
                        render: (val) => formatCurrencyHtml(val)
                    },
                    { 
                        data: null, 
                        className: 'text-center font-bold',
                        render: function(data) {
                            if (data.diff_qty > 0) return `<div class="text-red-600 font-medium">${formatQtyHtml(data.diff_qty, data.pcs_per_unit, data.unit_code, '+')}</div>`;
                            if (data.diff_qty < 0) return `<div class="text-red-600 font-medium">${formatQtyHtml(Math.abs(data.diff_qty), data.pcs_per_unit, data.unit_code, '-')}</div>`;
                            return `<span class="text-sm font-medium text-emerald-600">0</span>`;
                        }
                    },
                    { 
                        data: 'diff_amount', 
                        className: 'text-right pr-4 bg-slate-50/30 dark:bg-slate-800/20',
                        render: (val) => formatCurrencyHtml(val, true)
                    },
                    { 
                        data: 'location_name', 
                        className: 'text-center',
                        render: (val) => val || '<span class="text-gray-400 italic">No Location</span>'
                    },
                    { 
                        data: null, 
                        className: 'text-center',
                        render: function(data) {
                            if (data.diff_qty === 0) return '<span class="text-[10px] text-gray-400 italic">No Diff</span>';
                            if (data.can_edit_inline) {
                                let category = data.category; // SHORTAGE or EXCESS
                                let options = stoReasons.filter(r => r.category === category || r.category === 'OTHERS')
                                    .map(r => `<option value="${r.id}" ${data.reason_id == r.id ? 'selected' : ''}>${r.name}</option>`)
                                    .join('');
                                return `<select class="reason-input text-xs pl-2 py-1 border border-slate-200 dark:border-gray-700 rounded-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-primary-500" style="width: 180px; min-width: 180px;" data-detail-id="${data.hash_id}">
                                    <option value="">-- Select Reason --</option>
                                    ${options}
                                </select>`;
                            }
                            return `<span class="text-[10px] text-red-500 font-bold">${data.reason_name || 'Reason Required'}</span>`;
                        }
                    },
                    { 
                        data: null, 
                        className: 'text-xs text-gray-500 italic',
                        render: function(data) {
                            if (data.can_edit_inline) {
                                return `<input type="text" 
                                    class="remark-input text-xs px-2 py-1 border border-slate-200 dark:border-gray-700 rounded-xs focus:ring-0 focus:border-primary-500 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300" 
                                    style="width: 180px; min-width: 180px;"
                                    data-detail-id="${data.hash_id}" 
                                    value="${data.remark || ''}" 
                                    placeholder="Add note..." />`;
                            }
                            return data.remark || '-';
                        }
                    },
                    @if($stoEvent->status === 'OPEN')
                    { 
                        data: null, 
                        className: 'text-center', 
                        orderable: false,
                        render: function(data) {
                            if (data.status !== 'OPEN') return '';
                            return `
                                <div class="flex items-center justify-center">
                                    <button type="button" onclick="deleteItem('${data.hash_id}')" 
                                            class="h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-xs bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors" title="Delete Entry">
                                        <i class="fa-solid fa-trash-can text-sm"></i>
                                    </button>
                                </div>`;
                        }
                    }
                    @endif
                ],
                order: [[1, 'desc']],
                autoWidth: true,
                columnDefs: [
                    { targets: '_all', className: 'whitespace-nowrap px-4 py-3' }
                ]
            });

            // Inline Editing logic (Qty)
            $('#stoDetailsTable').on('blur', '.qty-input', function() {
                const $input = $(this);
                const productId = $input.data('product-id');
                const newQty = $input.val();
                const originalQty = $input.data('original-value');

                if (newQty === originalQty || !newQty || newQty === '') return;

                const $row = $input.closest('tr');
                const existingRemark = $row.find('.remark-input').val();
                const existingReasonId = $row.find('.reason-input').val();

                fetch(saveUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ 
                        product_id_hash: productId, 
                        real_qty: newQty, 
                        remark: existingRemark,
                        reason_id: existingReasonId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        $input.data('original-value', newQty);
                        table.ajax.reload(null, false);
                        if (data.stats && window.updateStatsCard) window.updateStatsCard(data.stats);
                    }
                });
            });

            // Inline Editing logic (Remark)
            $('#stoDetailsTable').on('blur', '.remark-input', function() {
                const $input = $(this);
                const newRemark = $input.val();
                const originalRemark = $input.data('original-value') || '';

                if (newRemark === originalRemark) return;

                const $row = $input.closest('tr');
                const $qtyInput = $row.find('.qty-input');
                const productId = $qtyInput.data('product-id');
                const currentQty = $qtyInput.val();
                const existingReasonId = $row.find('.reason-input').val();

                fetch(saveUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ 
                        product_id_hash: productId, 
                        real_qty: currentQty, 
                        remark: newRemark,
                        reason_id: existingReasonId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        $input.data('original-value', newRemark);
                        if (data.stats && window.updateStatsCard) window.updateStatsCard(data.stats);
                    }
                });
            });

            // Inline Editing logic (Reason)
            $('#stoDetailsTable').on('change', '.reason-input', function() {
                const $select = $(this);
                const reasonId = $select.val();
                const $row = $select.closest('tr');
                const $qtyInput = $row.find('.qty-input');
                const productId = $qtyInput.data('product-id');
                const currentQty = $qtyInput.val();
                const currentRemark = $row.find('.remark-input').val();

                fetch(saveUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ 
                        product_id_hash: productId, 
                        real_qty: currentQty, 
                        remark: currentRemark,
                        reason_id: reasonId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (data.stats && window.updateStatsCard) window.updateStatsCard(data.stats);
                    }
                });
            });
        }
    });

    // --- Scanner & Entry Logic ---
    const resultArea = document.getElementById('scanResultArea');
    const errorArea = document.getElementById('scanError');
    const errorMsg = document.getElementById('errorMsg');
    const realQtyInput = document.getElementById('realQtyInput');
    const remarkInput = document.getElementById('remarkInput');
    const btnSaveCount = document.getElementById('btnSaveCount');
    const resPartName = document.getElementById('resPartName');
    const resPartNo = document.getElementById('resPartNo');
    const resUnit = document.getElementById('resUnit');
    const resSystemQty = document.getElementById('resSystemQty');
    const resPrevReal = document.getElementById('resPrevReal');
    const currentHashId = document.getElementById('currentHashId');

    const productSelect = $('#product_detail_id');
    if (productSelect.length) {
        productSelect.select2({
            templateResult: function(data) {
                if (!data.id) return data.text;
                const $opt = $(data.element);
                const isCounted = $opt.data('counted') === true || $opt.data('counted') === 'true';
                
                if (isCounted) {
                    return $(`
                        <div class="flex items-center justify-between gap-2">
                            <span class="flex items-center gap-2 overflow-hidden">
                                <i class="fa-solid fa-circle-check text-emerald-500 shrink-0"></i>
                                <span class="truncate text-xs">${data.text}</span>
                            </span>
                            <span class="text-[9px] text-emerald-600 font-bold shrink-0">COUNTED</span>
                        </div>
                    `);
                }
                return data.text;
            },
            templateSelection: function(data) {
                if (!data.id) return data.text;
                // Focus on Part Number for selection display
                const partNo = $(data.element).data('partno');
                return $(`<span class="text-xs font-bold text-gray-900 dark:text-white truncate block w-full">${partNo || data.text}</span>`);
            }
        });
        productSelect.on('change', function() {
            const hashId = $(this).val();
            if (hashId) fetchStoInfo(hashId);
        });
    }

    if (typeof InventoryScanner !== 'undefined') {
        new InventoryScanner({ selectId: '#product_detail_id', scanButtonId: '#btn-scan', qrInputId: null, modalId: '#scannerModal' });
    }

    function fetchStoInfo(hashId) {
        fetch(scanUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ qr_code: hashId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) showResult(data.data);
            else showError(data.message);
        });
    }

    window.resetToNewEntry = function() {
        document.getElementById('currentDetailHashId').value = '';
        document.getElementById('editModeIndicator').classList.add('hidden');
        document.getElementById('realQtyInput').value = '';
        document.getElementById('remarkInput').value = '';
        $('#location_id').val('').trigger('change');
        document.getElementById('realQtyInput').focus();
    }

    let currentProductData = null;

    function showResult(data) {
        currentProductData = data;
        const container = document.getElementById('entriesFormContainer');
        container.innerHTML = ''; // Fresh start
        
        if (data.existing_entries && data.existing_entries.length > 0) {
            let entryDetailsHtml = `
                <div class="mb-4 text-xs text-gray-500">This item has been recorded in ${data.existing_entries.length} locations:</div>
                <div class="text-left bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-100 dark:border-gray-800 overflow-hidden shadow-inner">
                    <table class="w-full text-[11px] font-bold">
                        <thead class="bg-gray-100 dark:bg-gray-800 text-gray-400 uppercase tracking-widest text-[9px]">
                            <tr>
                                <th class="px-3 py-2 text-left">Location</th>
                                <th class="px-3 py-2 text-right">Qty Recorded</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">`;
            
            data.existing_entries.forEach(entry => {
                const qtyVal = parseFloat(entry.real_qty || 0).toLocaleString();
                entryDetailsHtml += `
                    <tr>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300">
                            <i class="fa-solid fa-location-dot text-primary-500 mr-1.5 opacity-70"></i>
                            ${entry.location_name || 'No Location'}
                        </td>
                        <td class="px-3 py-2 text-right text-primary-600 dark:text-primary-400 font-mono">
                            ${qtyVal} <span class="text-[9px] text-gray-400 font-normal ml-0.5">${data.unit || 'PCS'}</span>
                        </td>
                    </tr>
                `;
            });
            
            entryDetailsHtml += `</tbody></table></div>`;

            Swal.fire({
                title: 'Count Already Recorded',
                html: entryDetailsHtml,
                icon: 'question',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-pen-to-square mr-1"></i> Edit Existing',
                denyButtonText: '<i class="fa-solid fa-plus mr-1"></i> Add New',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#3b82f6',
                denyButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                customClass: {
                    htmlContainer: 'swal2-html-container-tight'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    processShowResult(data);
                    data.existing_entries.forEach(entry => {
                        createFormRow(entry);
                    });
                } else if (result.isDenied) {
                    processShowResult(data);
                    data.existing_entries.forEach(entry => {
                        createFormRow(entry);
                    });
                    addNewEntryRow(); // Automatically add the blank row
                } else {
                    // Reset selection on cancel
                    $('#product_detail_id').val('').trigger('change.select2');
                    document.getElementById('scanResultArea').classList.add('hidden');
                }
            });
        } else {
            // New item, add one blank row automatically
            processShowResult(data);
            createFormRow();
        }
    }

    function createFormRow(entry = null) {
        const container = document.getElementById('entriesFormContainer');
        const rowId = 'row-' + Math.random().toString(36).substr(2, 9);
        const locations = @json($locations);
        
        let locationOptions = '<option value="">-- No Location --</option>';
        locations.forEach(loc => {
            const selected = (entry && entry.location_id == loc.id) ? 'selected' : '';
            locationOptions += `<option value="${loc.id}" ${selected}>${loc.name}</option>`;
        });

        const rowHtml = `
            <div id="${rowId}" class="flex flex-col sm:flex-row items-end gap-3 p-3 rounded-xs bg-white dark:bg-gray-800 border ${entry ? 'border-primary-100 dark:border-primary-900/10 bg-primary-50/5' : 'border-gray-200 dark:border-gray-700 shadow-sm'} transition-all hover:bg-gray-50 dark:hover:bg-gray-700/30">
                <input type="hidden" class="row-detail-hash" value="${entry ? entry.detail_id_hash : ''}">
                
                <div class="flex-1 w-full">
                    <div class="text-[8px] font-bold text-gray-400 uppercase mb-1">Quantity (${currentProductData.unit || 'PCS'})</div>
                    <input type="number" class="row-qty w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xs h-[40px] text-center font-semibold text-sm focus:border-primary-500 transition-all outline-none" 
                           placeholder="0.00" value="${entry ? entry.real_qty : ''}">
                </div>

                <div class="flex-[1.5] w-full">
                    <div class="text-[8px] font-bold text-gray-400 uppercase mb-1">Location</div>
                    <select class="row-location w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 focus:border-primary-500 rounded-xs h-[40px] text-xs px-3 outline-none transition-all">
                        ${locationOptions}
                    </select>
                </div>

                <div class="flex-[2] w-full">
                    <div class="text-[8px] font-bold text-gray-400 uppercase mb-1">Remark</div>
                    <input type="text" class="row-remark w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 focus:border-primary-500 rounded-xs h-[40px] text-xs px-3 outline-none transition-all" 
                           placeholder="Optional Note..." value="${entry ? entry.remark || '' : ''}">
                </div>

                <button type="button" onclick="saveRowCount('${rowId}')" 
                        class="h-[40px] px-4 rounded-xs font-bold text-[10px] uppercase tracking-widest transition-all bg-primary-600 hover:bg-primary-700 text-white flex items-center justify-center gap-2 active:scale-95">
                    ${entry ? '<i class="fa-solid fa-check"></i> Update' : '<i class="fa-solid fa-plus"></i> Save'}
                </button>
                
                ${entry ? `
                    <button type="button" onclick="deleteItem('${entry.detail_id_hash}')" class="h-[40px] w-[40px] flex items-center justify-center text-red-400 hover:text-red-600 transition-colors">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                ` : `
                    <button type="button" onclick="document.getElementById('${rowId}').remove()" class="h-[40px] w-[40px] flex items-center justify-center text-gray-400 hover:text-red-400">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                `}
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', rowHtml);
        
        // Auto focus the first blank row's qty
        if (!entry) {
            const lastRow = container.lastElementChild;
            lastRow.querySelector('.row-qty').focus();
        }
    }

    window.addNewEntryRow = function() {
        createFormRow();
    }

    window.saveRowCount = function(rowId) {
        const row = document.getElementById(rowId);
        const qty = row.querySelector('.row-qty').value;
        const locId = row.querySelector('.row-location').value;
        const remark = row.querySelector('.row-remark').value;
        const detailHash = row.querySelector('.row-detail-hash').value;
        const productHash = currentHashId.value;

        if (qty === '' || !productHash) return;

        // Visual feedback
        const btn = row.querySelector('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        btn.disabled = true;

        fetch(saveUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ 
                product_id_hash: productHash, 
                detail_id_hash: detailHash, 
                real_qty: qty, 
                remark: remark, 
                location_id: locId 
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500
                });
                
                row.querySelector('.row-detail-hash').value = data.detail_id_hash || '';
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Update';
                btn.className = 'h-[40px] px-4 rounded-xs font-bold text-[10px] uppercase tracking-widest transition-all bg-primary-600 hover:bg-primary-700 text-white flex items-center justify-center gap-2';
                btn.disabled = false;
                row.classList.replace('border-gray-200', 'border-primary-100');
                row.classList.add('bg-primary-50/10');
                
                if (!row.querySelector('.fa-trash')) {
                    const deleteBtnHtml = `
                        <button type="button" onclick="deleteItem('${data.detail_id_hash}')" class="h-[40px] w-[40px] flex items-center justify-center text-red-400 hover:text-red-600 transition-colors">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    `;
                    const xBtn = row.querySelector('.fa-xmark')?.parentElement;
                    if (xBtn) xBtn.remove();
                    row.insertAdjacentHTML('beforeend', deleteBtnHtml);
                }

                table.ajax.reload(null, false);
                if (data.stats && window.updateStatsCard) window.updateStatsCard(data.stats);
            } else {
                Swal.fire('Error', data.message || 'Failed to save.', 'error');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        });
    }

    function deleteItem(detailHash) {
        Swal.fire({
            title: 'Delete Entry?',
            text: "Are you sure you want to remove this record?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const url = `{{ route('inventory.sto.deleteDetail', [$stoEvent->hash_id, ':detailHash']) }}`.replace(':detailHash', detailHash);
                
                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: data.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        
                        // Reload table and update stats
                        if (typeof table !== 'undefined') table.ajax.reload(null, false);
                        if (data.stats && window.updateStatsCard) window.updateStatsCard(data.stats);
                        
                        if (currentHashId && currentHashId.value) {
                            fetchStoInfo(currentHashId.value);
                        }
                    } else {
                        Swal.fire('Error', data.message || 'Failed to delete.', 'error');
                    }
                });
            }
        });
    }

    function processShowResult(data) {
        const area = document.getElementById('scanResultArea');
        area.classList.remove('hidden');
        
        errorArea.classList.add('hidden');
        resPartName.innerText = data.part_name;
        resPartNo.innerText = data.part_no;
        resUnit.innerText = data.unit || 'PCS';
        resSystemQty.innerText = (data.system_qty || 0) + 0;
        
        currentHashId.value = data.product_id_hash;
    }

    function showError(msg) {
        errorMsg.innerText = msg;
        errorArea.classList.remove('hidden');
        resultArea.classList.add('hidden');
    }

    window.editFromTable = function(productHash, detailHash) {
        $('#product_detail_id').val(productHash).trigger('change.select2');
        fetchStoInfo(productHash);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const productHash = urlParams.get('product');
        
        if (productHash && document.getElementById('product_detail_id')) {
            setTimeout(() => {
                console.log("[STO] Auto-selecting product from URL:", productHash);
                $('#product_detail_id').val(productHash).trigger('change.select2');
                fetchStoInfo(productHash);
            }, 500); 
        }
    });
</script>

@endpush
@endsection
