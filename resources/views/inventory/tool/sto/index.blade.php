@extends('layouts.app')

@section('title', 'Tool Stock Opname')

@section('content')
<div class="text-gray-900 dark:text-gray-100">

    {{-- Header --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-black text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">Tool Stock Opname</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Manage stock opname events for both fast and slow moving tools.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button type="button" onclick="showMdl('modal-new-event')" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-[10px] font-medium text-white uppercase tracking-widest active:scale-[0.98] transition-all">
                <i class="fa-solid fa-plus text-xs"></i> New STO Event
            </button>
        </div>
    </div>

    {{-- Table Grid --}}
    <div class="bg-white dark:bg-gray-900 rounded-xs border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-gray-50 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
            <h5 class="text-[10px] font-medium uppercase tracking-widest text-gray-500">Stock Opname Logs</h5>
            <span class="px-2 py-0.5 rounded-xs bg-primary-100 text-primary-700 text-[9px] font-medium">Active Records</span>
        </div>
        <div class="p-2">
            <x-table id="stoEventTable">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-4 w-12 text-center text-[10px] font-medium tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">No</th>
                        <th class="px-4 py-4 text-left text-[10px] font-medium tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">STO Code</th>
                        <th class="px-4 py-4 text-left text-[10px] font-medium tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Period</th>
                        <th class="px-4 py-4 text-center text-[10px] font-medium tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Status</th>
                        <th class="px-4 py-4 text-center text-[10px] font-medium tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Total Items</th>
                        <th class="px-4 py-4 text-left text-[10px] font-medium tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Auditor</th>
                        <th class="px-4 py-4 text-center text-[10px] font-medium tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </x-table>
        </div>
    </div>
</div>

{{-- Modal: New Event --}}
<div id="modal-new-event" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4">
    <div class="relative w-full max-w-md transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 shadow-2xl transition-all">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">Create New STO Event</h3>
            <button class="close-modal text-gray-400 hover:text-gray-500 w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100 dark:hover:bg-gray-800"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="formNewEvent" class="p-6">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block mb-1 text-[11px] font-medium text-slate-450 dark:text-gray-500 uppercase tracking-wider">Event Code Preview</label>
                    <input type="text" id="eventCodePreview" readonly class="w-full p-2.5 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xs text-xs font-mono font-medium text-gray-400 dark:text-gray-500 cursor-not-allowed" value="SAI/STO-TOOL/{{ date('dmY') }}/....">
                </div>
                <div>
                    <label class="block mb-1 text-[11px] font-medium text-slate-500 dark:text-gray-400 uppercase tracking-wider">Start Date</label>
                    <input type="date" id="period_start_input" name="period_start" value="{{ date('Y-m-d') }}" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all">
                </div>
                <div>
                    <label class="block mb-2 text-[10px] font-medium text-slate-600 dark:text-gray-300 uppercase tracking-wider">Description</label>
                    <textarea name="description" rows="3" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all" placeholder="Optional notes..."></textarea>
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-3">
                <button type="button" class="close-modal px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-[10px] font-medium text-gray-600 dark:text-gray-400 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-[10px] font-medium text-white uppercase tracking-widest active:scale-[0.98] transition-all">Create Event</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Edit Event --}}
<div id="modal-edit-event" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4">
    <div class="relative w-full max-w-md transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 shadow-2xl transition-all">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">Edit STO Event</h3>
            <button class="close-modal text-gray-400 hover:text-gray-500 w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100 dark:hover:bg-gray-800"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="formEditEvent" class="p-6">
            @csrf
            @method('PUT')
            <input type="hidden" id="editEventId" name="id">
            <div class="space-y-4">
                <div>
                    <label class="block mb-1 text-[11px] font-medium text-slate-400 dark:text-gray-500 uppercase tracking-wider">Event Code</label>
                    <input type="text" id="editEventCode" readonly class="w-full p-2.5 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xs text-xs font-mono font-medium text-gray-400 dark:text-gray-500 cursor-not-allowed">
                </div>
                <div>
                    <label class="block mb-1 text-[11px] font-medium text-slate-500 dark:text-gray-400 uppercase tracking-wider">Start Date</label>
                    <input type="date" id="editEventPeriodStart" name="period_start" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all">
                </div>
                <div>
                    <label class="block mb-2 text-[10px] font-medium text-slate-600 dark:text-gray-300 uppercase tracking-wider">Description</label>
                    <textarea id="editEventDescription" name="description" rows="3" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all" placeholder="Optional notes..."></textarea>
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-3">
                <button type="button" class="close-modal px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-[10px] font-medium text-gray-600 dark:text-gray-400 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-[10px] font-medium text-white uppercase tracking-widest active:scale-[0.98] transition-all">Save Changes</button>
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
                { data: 'code', render: d => `<span class="font-medium text-gray-900 dark:text-white font-mono">${d}</span>` },
                { data: 'period' },
                { data: 'status', className: 'text-center' },
                { data: 'items_count', className: 'text-center font-mono text-[11px]' },
                { data: 'creator' },
                { data: 'action', className: 'text-center' }
            ],
            createdRow: function(row, data, dataIndex) {
                $(row).addClass('cursor-pointer hover:bg-slate-50/40 dark:hover:bg-gray-800/10 transition-colors');
            }
        });

        // Make entire row clickable to go to details page (except action cells)
        $('#stoEventTable').on('click', 'tbody tr', function(e) {
            if ($(e.target).closest('.action-cell').length || $(e.target).closest('a, button, input').length) {
                return;
            }
            const data = table.row(this).data();
            if (data && data.id) {
                window.location.href = "{{ route('inventory.tool.sto.show', ['id' => ':id']) }}".replace(':id', data.id);
            }
        });

        // Edit STO Event Modal Trigger
        $(document).on('click', '.edit-event', function(e) {
            e.stopPropagation(); // Stop navigation
            const btn = $(this);
            const id = btn.data('id');
            const code = btn.data('code');
            const period = btn.data('period');
            const desc = btn.data('description');

            const modal = $('#modal-edit-event');
            modal.find('#editEventId').val(id);
            modal.find('#editEventCode').val(code);
            modal.find('#editEventPeriodStart').val(period);
            modal.find('#editEventDescription').val(desc);

            modal.removeClass('hidden');
        });

        // Edit STO Event Form Submit
        $('#formEditEvent').on('submit', function(e) {
            e.preventDefault();
            const id = $('#editEventId').val();
            const btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: "{{ route('inventory.tool.sto.updateEvent', ['id' => ':id']) }}".replace(':id', id),
                type: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    window.showToast('success', res.message);
                    $('#modal-edit-event').addClass('hidden');
                    table.ajax.reload(null, false);
                },
                error: function(err) {
                    window.showToast('error', err.responseJSON?.message || 'Something went wrong');
                    btn.prop('disabled', false).text('Save Changes');
                }
            });
        });

        // Delete STO Event Trigger
        $(document).on('click', '.delete-event', function(e) {
            e.stopPropagation(); // Stop navigation
            const id = $(this).data('id');
            
            Swal.fire({
                title: 'Hapus STO Event?',
                text: "Event STO ini beserta seluruh data detailnya akan dihapus permanen dari sistem!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('inventory.tool.sto.deleteEvent', ['id' => ':id']) }}".replace(':id', id),
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            window.showToast('success', res.message);
                            table.ajax.reload(null, false);
                        },
                        error: function(err) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: err.responseJSON?.message || 'Terjadi kesalahan saat menghapus event.'
                            });
                        }
                    });
                }
            });
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
