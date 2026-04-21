@extends('layouts.app')

@section('title', 'STO — Fast Moving Tools')

@section('content')
<div class="text-gray-900 dark:text-gray-100">

    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">STO — Fast Moving</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Stock Take for fast moving tools. Physical count vs system stock.</p>
        </div>
        <button type="button" id="btnNewSto" class="mt-4 sm:mt-0 inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest transition-all">
            <i class="fa-solid fa-clipboard-check"></i> New STO
        </button>
    </div>

    <x-table id="stoFastTable">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th class="px-4 py-4 w-12 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">No</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">STO Date</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Tool</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Location</th>
                <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">System Qty</th>
                <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Physical Qty</th>
                <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Adjustment</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Conducted By</th>
                <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Status</th>
                <th class="px-4 py-4 text-center w-[120px] text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

{{-- Modal: New STO --}}
<div id="modal-sto-fast" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50">
    <div class="relative w-full max-w-md transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-clipboard-check text-primary-500"></i> New STO — Fast Moving
            </h3>
            <button class="close-modal text-gray-400 hover:text-gray-500 w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <div class="overflow-y-auto px-6 py-6 flex-1">
            <form id="stoFastForm">
                @csrf
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">STO Date <span class="text-red-500">*</span></label>
                    <input type="date" name="sto_date" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3">
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Tool <span class="text-red-500">*</span></label>
                    <select name="tool_id" id="stoToolId" required class="select2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3">
                        <option value="">-- Select Tool --</option>
                        @foreach($tools as $tool)
                            @if($tool->category && $tool->category->moving_type === 'fast')
                            <option value="{{ $tool->id }}">{{ $tool->name }} — {{ $tool->brand }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Location <span class="text-red-500">*</span></label>
                    <select name="location_id" required class="select2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3">
                        <option value="">-- Select Location --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->code }} — {{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Physical Qty (hasil hitung fisik) <span class="text-red-500">*</span></label>
                    <input type="number" name="physical_qty" id="stoPhysicalQty" min="0" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3" placeholder="0">
                </div>
                <div class="mb-2">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Note</label>
                    <textarea name="note" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3" rows="2" placeholder="Optional notes..."></textarea>
                </div>
            </form>
        </div>
        <div class="border-t border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 flex gap-3">
            <button type="button" class="close-modal flex-1 px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-[10px] font-bold text-gray-600 uppercase tracking-widest hover:bg-gray-50 transition-colors">Cancel</button>
            <button type="button" id="saveStoFastBtn" class="flex-1 px-4 py-3 bg-primary-600 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest hover:bg-primary-700 transition-all">Create Draft STO</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const csrf    = $('meta[name="csrf-token"]').attr('content');
    const apiBase = "{{ route('inventory.tool.sto-fast.index') }}";

    window.stoFastTable = window.defaultDataTable('#stoFastTable', {
        ajax: { url: apiBase, type: 'GET' },
        order: [[1, 'desc']],
        columns: [
            { data: null, orderable: false, searchable: false, render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'sto_date' },
            { data: null, render: (d, t, r) => `<span class="font-semibold text-xs">${r.tool_name}</span><br><span class="text-[10px] text-gray-400">${r.brand}</span>` },
            { data: 'location', render: d => `<span class="text-xs">${d}</span>` },
            { data: 'system_qty', className: 'text-center font-mono text-xs' },
            { data: 'physical_qty', className: 'text-center font-mono text-xs font-semibold' },
            {
                data: 'adjustment_qty', className: 'text-center font-mono font-bold text-xs',
                render: d => {
                    const cls = d > 0 ? 'text-emerald-600' : d < 0 ? 'text-red-600' : 'text-gray-400';
                    const sign = d > 0 ? '+' : '';
                    return `<span class="${cls}">${sign}${d}</span>`;
                }
            },
            { data: 'conducted_by', render: d => `<span class="text-xs">${d}</span>` },
            {
                data: 'status',
                render: d => d === 'approved'
                    ? '<span class="px-2 py-0.5 rounded-xs text-[9px] font-bold uppercase bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Approved</span>'
                    : '<span class="px-2 py-0.5 rounded-xs text-[9px] font-bold uppercase bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Draft</span>'
            },
            {
                data: null, orderable: false, searchable: false, className: 'text-center',
                render: (d, t, r) => {
                    if (r.status === 'draft') return `
                        <div class="flex items-center justify-center gap-1.5">
                            <button class="approve-sto-btn h-8 px-2 inline-flex items-center gap-1 text-emerald-600 rounded-xs bg-emerald-50 hover:bg-emerald-100 text-[9px] font-bold uppercase transition-colors" data-id="${r.id}" title="Approve STO">
                                <i class="fa-solid fa-check text-xs"></i> Approve
                            </button>
                            <button class="delete-sto-btn h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-xs bg-red-50 hover:bg-red-100 transition-colors" data-id="${r.id}">
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

    // Set default date to today
    $('input[name="sto_date"]').val(new Date().toISOString().split('T')[0]);

    $('#btnNewSto').on('click', () => { 
        $('#stoFastForm')[0].reset(); 
        $('input[name="sto_date"]').val(new Date().toISOString().split('T')[0]); 
        $('select.select2').trigger('change');
        showMdl('modal-sto-fast'); 
    });

    $('#saveStoFastBtn').on('click', function() {
        $.ajax({
            url: apiBase, method: 'POST', data: $('#stoFastForm').serialize(),
            success: (res) => { toast('success', 'STO Created', res.message); hideMdl('modal-sto-fast'); stoFastTable.ajax.reload(); },
            error:   (xhr) => { toast('error',   'Error',       xhr.responseJSON?.message || 'Failed'); }
        });
    });

    $(document).on('click', '.approve-sto-btn', function() {
        const id  = $(this).data('id');
        const url = `${apiBase}/${id}/approve`;
        if (!confirm('Approve this STO? Stock will be updated to physical qty.')) return;
        $.ajax({
            url, method: 'POST', data: { _token: csrf },
            success: (res) => { toast('success', 'STO Approved', res.message); stoFastTable.ajax.reload(); },
            error:   (xhr) => { toast('error',   'Error',        xhr.responseJSON?.message || 'Failed'); }
        });
    });

    $(document).on('click', '.delete-sto-btn', function() {
        if (!confirm('Delete this STO draft?')) return;
        $.ajax({
            url: `${apiBase}/${$(this).data('id')}`, method: 'POST', data: { _method: 'DELETE', _token: csrf },
            success: (res) => { toast('success', 'Deleted', res.message); stoFastTable.ajax.reload(); },
            error:   (xhr) => { toast('error',   'Error',   xhr.responseJSON?.message || 'Failed'); }
        });
    });
});
</script>
@endpush
