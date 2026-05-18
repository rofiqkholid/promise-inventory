@extends('layouts.app')

@section('title', 'Tool Stock Opname')

@section('content')
<div class="text-gray-900 dark:text-gray-100">

    {{-- Header --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">Tool Stock Opname</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Manage stock opname events for both fast and slow moving tools.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button type="button" onclick="showMdl('modal-new-event')" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest active:scale-[0.98] transition-all">
                <i class="fa-solid fa-plus"></i> New STO Event
            </button>
        </div>
    </div>

    {{-- Table --}}
    <x-table id="stoEventTable">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th class="px-4 py-4 w-12 text-center text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">No</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">STO Code</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Event Name</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Period</th>
                <th class="px-4 py-4 text-center text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Status</th>
                <th class="px-4 py-4 text-center text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Total Items</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Created By</th>
                <th class="px-4 py-4 text-center text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

{{-- Modal: New Event --}}
<div id="modal-new-event" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4">
    <div class="relative w-full max-w-md transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 shadow-2xl transition-all">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-widest">Create New STO Event</h3>
            <button class="close-modal text-gray-400 hover:text-gray-500 w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100 dark:hover:bg-gray-800"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="formNewEvent" class="p-6">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block mb-1 text-[11px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider">Event Name</label>
                    <input type="text" name="name" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all" placeholder="e.g. Monthly STO May 2026">
                </div>
                <div>
                    <label class="block mb-1 text-[11px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">Event Code Preview</label>
                    <input type="text" id="eventCodePreview" readonly class="w-full p-2.5 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xs text-xs font-mono font-bold text-gray-400 dark:text-gray-500 cursor-not-allowed" value="SAI/STO-TOOL/{{ date('dmY') }}/....">
                </div>
                <div>
                    <label class="block mb-1 text-[11px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider">Start Date</label>
                    <input type="date" id="period_start_input" name="period_start" value="{{ date('Y-m-d') }}" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all">
                </div>
                <div>
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Description</label>
                    <textarea name="description" rows="3" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all" placeholder="Optional notes..."></textarea>
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-3">
                <button type="button" class="close-modal px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-[10px] font-bold text-white uppercase tracking-widest active:scale-[0.98] transition-all">Create Event</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        const table = window.defaultDataTable('#stoEventTable', {
            ajax: "{{ route('inventory.tool.sto.index') }}",
            columns: [
                { data: 'DT_RowIndex', className: 'text-center' },
                { data: 'code' },
                { data: 'name', render: d => `<span class="font-bold">${d}</span>` },
                { data: 'period' },
                { data: 'status', className: 'text-center' },
                { data: 'items_count', className: 'text-center font-mono text-[11px]' },
                { data: 'creator.name' },
                { data: 'action', className: 'text-center' }
            ]
        });

        $('#formNewEvent').on('submit', function(e) {
            e.preventDefault();
            const btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Processing...');

            $.ajax({
                url: "{{ route('inventory.tool.sto.store') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    window.showToast('success', res.message);
                    window.location.href = res.redirect;
                },
                error: function(err) {
                    window.showToast('error', err.responseJSON?.message || 'Something went wrong');
                    btn.prop('disabled', false).text('Create Event');
                }
            });
        });

        // Dynamic Code Preview Logic
        const periodStartInput = document.getElementById('period_start_input');
        const previewElement = document.getElementById('eventCodePreview');

        function updateCodePreview() {
            const date = periodStartInput.value;
            if (!date) return;

            $.ajax({
                url: "{{ route('inventory.tool.sto.previewCode') }}",
                data: { date: date },
                success: function(res) {
                    if (res.code) previewElement.value = res.code;
                }
            });
        }

        if (periodStartInput) {
            periodStartInput.addEventListener('change', updateCodePreview);
        }

        // Trigger update when opening modal
        window.showMdl = (id) => { 
            $(`#${id}`).removeClass('hidden'); 
            if (id === 'modal-new-event') updateCodePreview();
        };

        $(document).on('click', '.close-modal', function() { $(this).closest('.modal-container').addClass('hidden'); });
    });
</script>
@endpush
