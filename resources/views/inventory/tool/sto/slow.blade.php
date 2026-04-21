@extends('layouts.app')

@section('title', 'STO — Slow Moving Tools')

@section('content')
<div class="text-gray-900 dark:text-gray-100">

    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">STO — Slow Moving</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Stock Take for slow moving tools. Physical check (OK/NOK) + asset value recalculation per batch.</p>
        </div>
        <button type="button" id="btnNewStoSlow" class="mt-4 sm:mt-0 inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest transition-all">
            <i class="fa-solid fa-clipboard-check"></i> New STO
        </button>
    </div>

    <x-table id="stoSlowTable">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th class="px-4 py-4 w-12 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">No</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">STO Date</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Batch No</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Tool</th>
                <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Physical Check</th>
                <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Qty OK / NOK</th>
                <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Age (yrs)</th>
                <th class="px-4 py-4 text-right text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Asset Value</th>
                <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Status</th>
                <th class="px-4 py-4 text-center w-[120px] text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

{{-- Modal: New STO Slow --}}
<div id="modal-sto-slow" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50">
    <div class="relative w-full max-w-lg transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-clipboard-check text-primary-500"></i> New STO — Slow Moving
            </h3>
            <button class="close-modal text-gray-400 hover:text-gray-500 w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <div class="overflow-y-auto px-6 py-6 flex-1 custom-scrollbar">
            <form id="stoSlowForm">
                @csrf
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">STO Date <span class="text-red-500">*</span></label>
                    <input type="date" name="sto_date" id="stoSlowDate" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3">
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Batch <span class="text-red-500">*</span></label>
                    <select name="batch_id" id="stoBatchId" required class="select2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3">
                        <option value="">-- Select Batch --</option>
                        @foreach($activeBatches as $batch)
                            <option value="{{ $batch->id }}"
                                data-qty="{{ $batch->qty_current }}"
                                data-purchase-date="{{ $batch->purchase_date->format('Y-m-d') }}"
                                data-lifetime="{{ $batch->std_lifetime_yrs }}"
                                data-price="{{ $batch->purchase_price }}">
                                {{ $batch->batch_no }} — {{ $batch->tool?->name ?? '-' }} ({{ $batch->location?->code ?? '-' }}) — Qty: {{ $batch->qty_current }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Physical Check <span class="text-red-500">*</span></label>
                    <div class="flex gap-3">
                        <label class="flex-1 flex items-center gap-2 p-3 border border-gray-200 dark:border-gray-700 rounded-xs cursor-pointer has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 dark:has-[:checked]:bg-emerald-900/20 transition-all">
                            <input type="radio" name="physical_check" value="ok" class="accent-emerald-600"> <span class="text-xs font-semibold text-emerald-700">✓ OK — Masih bisa dipakai</span>
                        </label>
                        <label class="flex-1 flex items-center gap-2 p-3 border border-gray-200 dark:border-gray-700 rounded-xs cursor-pointer has-[:checked]:border-red-500 has-[:checked]:bg-red-50 dark:has-[:checked]:bg-red-900/20 transition-all">
                            <input type="radio" name="physical_check" value="nok" class="accent-red-600"> <span class="text-xs font-semibold text-red-700">✗ NOK — Rusak / tidak layak</span>
                        </label>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3 mb-4">
                    <div>
                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Qty Checked <span class="text-red-500">*</span></label>
                        <input type="number" name="qty_checked" id="stoQtyChecked" min="1" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3" placeholder="0">
                    </div>
                    <div>
                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Qty OK <span class="text-red-500">*</span></label>
                        <input type="number" name="qty_ok" id="stoQtyOk" min="0" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3" placeholder="0">
                    </div>
                    <div>
                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Qty NOK <span class="text-red-500">*</span></label>
                        <input type="number" name="qty_nok" id="stoQtyNok" min="0" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3" placeholder="0">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Note / Alasan NOK</label>
                    <textarea name="note" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3" rows="2"></textarea>
                </div>
                {{-- Asset Value Preview --}}
                <div id="assetPreviewBox" class="hidden p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xs border border-blue-200 dark:border-blue-800">
                    <p class="text-[10px] font-bold uppercase text-blue-700 dark:text-blue-400 tracking-wider mb-3">Asset Value Preview (Straight-Line)</p>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="text-gray-500">Age Terpakai:</div><div class="font-semibold" id="pvAge">—</div>
                        <div class="text-gray-500">Sisa Lifetime:</div><div class="font-semibold" id="pvRemaining">—</div>
                        <div class="text-gray-500">Qty OK:</div><div class="font-semibold" id="pvQtyOk">—</div>
                        <div class="text-gray-500 font-bold text-blue-700">Nilai Aset Terkini:</div>
                        <div class="font-bold text-emerald-600" id="pvValue">—</div>
                    </div>
                </div>
            </form>
        </div>
        <div class="border-t border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 flex gap-3">
            <button type="button" id="previewStoBtn" class="px-4 py-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xs text-[10px] font-bold text-blue-700 dark:text-blue-400 uppercase tracking-widest hover:bg-blue-100 transition-colors">
                <i class="fa-solid fa-calculator mr-1"></i> Preview
            </button>
            <button type="button" class="close-modal flex-1 px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-[10px] font-bold text-gray-600 uppercase tracking-widest hover:bg-gray-50 transition-colors">Cancel</button>
            <button type="button" id="saveStoSlowBtn" class="flex-1 px-4 py-3 bg-primary-600 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest hover:bg-primary-700 transition-all">Create Draft STO</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const csrf       = $('meta[name="csrf-token"]').attr('content');
    const apiBase    = "{{ route('inventory.tool.sto-slow.index') }}";
    const previewUrl = "{{ route('inventory.tool.sto-slow.preview') }}";
    const idr        = (v) => 'Rp ' + parseFloat(v || 0).toLocaleString('id-ID');

    window.stoSlowTable = window.defaultDataTable('#stoSlowTable', {
        ajax: { url: apiBase, type: 'GET' },
        order: [[1, 'desc']],
        columns: [
            { data: null, orderable: false, searchable: false, render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'sto_date' },
            { data: 'batch_no', render: d => `<span class="font-mono text-xs font-semibold text-primary-600 dark:text-primary-400">${d}</span>` },
            { data: null, render: (d, t, r) => `<span class="font-semibold text-xs">${r.tool_name}</span><br><span class="text-[10px] text-gray-400">${r.brand}</span>` },
            {
                data: 'physical_check',
                render: d => d === 'ok'
                    ? '<span class="px-2 py-0.5 rounded-xs text-[9px] font-bold uppercase bg-emerald-100 text-emerald-700">OK</span>'
                    : '<span class="px-2 py-0.5 rounded-xs text-[9px] font-bold uppercase bg-red-100 text-red-700">NOK</span>'
            },
            {
                data: null, className: 'text-center',
                render: (d, t, r) => `<span class="text-xs"><span class="text-emerald-600 font-semibold">${r.qty_ok}</span> / <span class="text-red-500">${r.qty_nok}</span></span>`
            },
            { data: 'age_years', className: 'text-center', render: d => `<span class="text-xs">${d} yrs</span>` },
            { data: 'remaining_value', className: 'text-right font-mono text-xs font-semibold', render: d => idr(d) },
            {
                data: 'status',
                render: d => d === 'approved'
                    ? '<span class="px-2 py-0.5 rounded-xs text-[9px] font-bold uppercase bg-emerald-100 text-emerald-700">Approved</span>'
                    : '<span class="px-2 py-0.5 rounded-xs text-[9px] font-bold uppercase bg-amber-100 text-amber-700">Draft</span>'
            },
            {
                data: null, orderable: false, searchable: false, className: 'text-center',
                render: (d, t, r) => {
                    if (r.status === 'draft') return `
                        <div class="flex items-center justify-center gap-1.5">
                            <button class="approve-slow-btn h-8 px-2 inline-flex items-center gap-1 text-emerald-600 rounded-xs bg-emerald-50 hover:bg-emerald-100 text-[9px] font-bold uppercase transition-colors" data-id="${r.id}">
                                <i class="fa-solid fa-check text-xs"></i> Approve
                            </button>
                            <button class="delete-slow-btn h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-xs bg-red-50 hover:bg-red-100 transition-colors" data-id="${r.id}">
                                <i class="fa-solid fa-trash-can text-sm"></i>
                            </button>
                        </div>`;
                    return '<span class="text-[10px] text-gray-400">—</span>';
                }
            }
        ]
    });

    const showMdl = (id) => { $('.modal-container').addClass('hidden'); $(`#${id}`).removeClass('hidden'); };
    const hideMdl = (id) => { $(`#${id}`).addClass('hidden'); };
    $('.close-modal').on('click', function() { $(this).closest('.modal-container').addClass('hidden'); });

    $('input[name="sto_date"], #stoBatchId').first().val(new Date().toISOString().split('T')[0]);

    $('#btnNewStoSlow').on('click', () => {
        $('#stoSlowForm')[0].reset();
        $('#stoSlowDate').val(new Date().toISOString().split('T')[0]);
        $('#assetPreviewBox').addClass('hidden');
        $('select.select2').trigger('change');
        showMdl('modal-sto-slow');
    });

    // Auto-fill qty_checked & qty_ok from batch selection
    $('#stoBatchId').on('change', function() {
        const qty = $('option:selected', this).data('qty') || 0;
        $('#stoQtyChecked').val(qty);
        $('#stoQtyOk').val(qty);
        $('#stoQtyNok').val(0);
        $('input[name="physical_check"][value="ok"]').prop('checked', true);
        $('#assetPreviewBox').addClass('hidden');
    });

    // Handle Physical Check selection
    $('input[name="physical_check"]').on('change', function() {
        const checked = $('#stoQtyChecked').val() || 0;
        if ($(this).val() === 'ok') {
            $('#stoQtyOk').val(checked);
            $('#stoQtyNok').val(0);
        } else {
            $('#stoQtyOk').val(0);
            $('#stoQtyNok').val(checked);
        }
    });

    // Ensure Qty OK + Qty NOK = Qty Checked
    $('#stoQtyOk').on('input', function() {
        const checked = parseInt($('#stoQtyChecked').val()) || 0;
        const ok = parseInt($(this).val()) || 0;
        if (ok > checked) { $(this).val(checked); $('#stoQtyNok').val(0); }
        else { $('#stoQtyNok').val(checked - ok); }
    });

    $('#stoQtyNok').on('input', function() {
        const checked = parseInt($('#stoQtyChecked').val()) || 0;
        const nok = parseInt($(this).val()) || 0;
        if (nok > checked) { $(this).val(checked); $('#stoQtyOk').val(0); }
        else { $('#stoQtyOk').val(checked - nok); }
    });

    // Preview depreciation
    $('#previewStoBtn').on('click', function() {
        const batchId = $('#stoBatchId').val();
        const stoDate = $('#stoSlowDate').val();
        const qtyOk   = $('#stoQtyOk').val();
        const qtyNok  = $('#stoQtyNok').val();
        if (!batchId || !stoDate || qtyOk === '') { toast('error', 'Error', 'Fill in batch, date, and qty first'); return; }

        $.ajax({
            url: previewUrl, method: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content'), batch_id: batchId, sto_date: stoDate, qty_ok: qtyOk, qty_nok: qtyNok },
            success: (res) => {
                $('#pvAge').text(res.age_years + ' yrs');
                $('#pvRemaining').text(res.remaining_years + ' yrs');
                $('#pvQtyOk').text(res.qty_ok);
                $('#pvValue').text('Rp ' + parseFloat(res.asset_value).toLocaleString('id-ID'));
                $('#assetPreviewBox').removeClass('hidden');
            },
            error: (xhr) => { toast('error', 'Error', xhr.responseJSON?.message || 'Preview failed'); }
        });
    });

    $('#saveStoSlowBtn').on('click', function() {
        $.ajax({
            url: apiBase, method: 'POST', data: $('#stoSlowForm').serialize(),
            success: (res) => { toast('success', 'STO Created', res.message); hideMdl('modal-sto-slow'); stoSlowTable.ajax.reload(); },
            error:   (xhr) => { toast('error',   'Error',       xhr.responseJSON?.message || 'Failed'); }
        });
    });

    $(document).on('click', '.approve-slow-btn', function() {
        const id = $(this).data('id');
        if (!confirm('Approve this STO? Batch asset value will be updated.')) return;
        $.ajax({
            url: `${apiBase}/${id}/approve`, method: 'POST', data: { _token: csrf },
            success: (res) => { toast('success', 'Approved', res.message); stoSlowTable.ajax.reload(); },
            error:   (xhr) => { toast('error',   'Error',    xhr.responseJSON?.message || 'Failed'); }
        });
    });

    $(document).on('click', '.delete-slow-btn', function() {
        if (!confirm('Delete this STO draft?')) return;
        $.ajax({
            url: `${apiBase}/${$(this).data('id')}`, method: 'POST', data: { _method: 'DELETE', _token: csrf },
            success: (res) => { toast('success', 'Deleted', res.message); stoSlowTable.ajax.reload(); },
            error:   (xhr) => { toast('error',   'Error',   xhr.responseJSON?.message || 'Failed'); }
        });
    });
});
</script>
@endpush
