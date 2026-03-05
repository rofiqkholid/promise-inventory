@extends('layouts.app')

@section('title', 'Stock Opname Management')
@section('page_title', 'Stock Opname')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    <div class="mb-4">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter leading-none">Stock Opname</h2>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Coordinate and track physical inventory count events.</p>
    </div>

    <div class="mb-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="p-4 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 flex-1 w-full flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <h3 class="font-bold text-slate-800 dark:text-white leading-tight">Active STO Events</h3>
                <p class="text-[11px] text-gray-500 font-medium tracking-wider">List of scheduled and completed inventory counts.</p>
            </div>
            
            <div class="flex items-center gap-2 w-full md:w-auto overflow-x-auto pb-1 md:pb-0">
                @if(auth()->user()->hasAppRole('pic') || auth()->user()->hasAppRole('approver') || auth()->user()->hasAppRole('admin'))
                    <button onclick="window.openCreateModal()" class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest active:scale-[0.98] transition-all whitespace-nowrap">
                        <i class="fa-solid fa-calendar-plus text-sm"></i> 
                        New STO Event
                    </button>
                @endif
            </div>
        </div>

        {{-- No Legend Popover --}}
    </div>

    <!-- Events Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 overflow-hidden">
        <x-table id="stoEventsTable">
            <thead>
                <tr>
                    <th class="w-16 border-b border-slate-200 dark:border-gray-700 text-center font-bold uppercase tracking-wider text-xs">No</th>
                    <th class="text-left w-48 border-b border-slate-200 dark:border-gray-700 font-bold uppercase tracking-wider text-xs">Event Code</th>
                    <th class="text-left border-b border-slate-200 dark:border-gray-700 font-bold uppercase tracking-wider text-xs">Counting Period</th>
                    <th class="text-center w-32 border-b border-slate-200 dark:border-gray-700 font-bold uppercase tracking-wider text-xs">Status</th>
                    <th class="text-left w-40 border-b border-slate-200 dark:border-gray-700 font-bold uppercase tracking-wider text-xs">PIC</th>
                    <th class="text-center w-32 border-b border-slate-200 dark:border-gray-700 font-bold uppercase tracking-wider text-xs">Net Amount</th>
                    <th class="text-center w-32 border-b border-slate-200 dark:border-gray-700 font-bold uppercase tracking-wider text-xs">Net PCS</th>
                    <th class="text-center w-40 border-b border-slate-200 dark:border-gray-700 font-bold uppercase tracking-wider text-xs">STO Control</th>
                    <th class="w-[100px] text-center border-b border-slate-200 dark:border-gray-700 font-bold uppercase tracking-wider text-xs">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </x-table>
    </div>
</div>

<!-- Create Modal -->
<div id="createEventModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="document.getElementById('createEventModal').classList.add('hidden')"></div>
        
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xs text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full relative z-10 border border-slate-200 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-3 uppercase tracking-widest">
                    <i class="fa-solid fa-calendar-plus text-slate-400"></i> Initialize STO
                </h3>
                <button onclick="document.getElementById('createEventModal').classList.add('hidden')" class="text-slate-400 hover:text-rose-500 transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <form action="{{ route('inventory.sto.store') }}" method="POST" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block mb-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Start Date</label>
                        <input type="date" id="sto_period_start" name="period_start" required class="w-full p-2.5 bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 rounded-xs text-xs font-bold focus:ring-1 focus:ring-primary-500 outline-none" value="{{ date('Y-m-d') }}">
                    </div>
                    
                    <div>
                        <label class="block mb-1 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Event Code Preview</label>
                        <input type="text" id="eventCodePreview" readonly class="w-full p-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-gray-700 rounded-xs text-xs font-mono font-bold text-slate-500 dark:text-gray-400" value="SAI/STO/{{ date('dmY') }}/....">
                    </div>
                    
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-xs border border-slate-100 dark:border-gray-700">
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Assigned PIC</label>
                        <div class="text-xs font-bold text-slate-700 dark:text-gray-300 flex items-center gap-2">
                             <i class="fa-solid fa-user-check text-[10px] text-slate-400"></i>
                             {{ auth()->user()->name }}
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Notes / Scope (Optional)</label>
                        <textarea name="description" rows="3" class="w-full p-2.5 bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 rounded-xs text-xs font-medium focus:ring-1 focus:ring-primary-500 outline-none" placeholder="Enter STO notes or coverage area..."></textarea>
                    </div>
                </div>
                
                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="document.getElementById('createEventModal').classList.add('hidden')" class="flex-1 py-2.5 text-[11px] font-bold uppercase tracking-widest text-slate-500 bg-slate-50 hover:bg-slate-100 rounded-xs transition-all border border-slate-200">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 text-[11px] font-bold uppercase tracking-widest text-white bg-primary-600 hover:bg-primary-700 rounded-xs active:scale-95 transition-all outline-none shadow-sm">Initialize Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editEventModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="document.getElementById('editEventModal').classList.add('hidden')"></div>
        
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xs text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full relative z-10 border border-slate-200 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-3 uppercase tracking-widest">
                    <i class="fa-solid fa-pen-to-square text-primary-500"></i> Edit STO details
                </h3>
                <button onclick="document.getElementById('editEventModal').classList.add('hidden')" class="text-slate-400 hover:text-rose-500 transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <form id="editEventForm" method="POST" class="p-6">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block mb-1 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Event Code</label>
                        <input type="text" id="edit_code" readonly class="w-full p-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-gray-700 rounded-xs text-xs font-mono font-bold text-slate-400">
                    </div>

                    <div>
                        <label class="block mb-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Start Date</label>
                        <input type="date" id="edit_period_start" name="period_start" required class="w-full p-2.5 bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 rounded-xs text-xs font-bold focus:ring-1 focus:ring-primary-500 outline-none">
                    </div>

                    <div>
                        <label class="block mb-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Notes / Scope (Optional)</label>
                        <textarea id="edit_description" name="description" rows="3" class="w-full p-2.5 bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 rounded-xs text-xs font-medium focus:ring-1 focus:ring-primary-500 outline-none" placeholder="Enter STO notes..."></textarea>
                    </div>
                </div>
                
                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="document.getElementById('editEventModal').classList.add('hidden')" class="flex-1 py-2.5 text-[11px] font-bold uppercase tracking-widest text-slate-500 bg-slate-50 hover:bg-slate-100 rounded-xs transition-all border border-slate-200">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 text-[11px] font-bold uppercase tracking-widest text-white bg-primary-600 hover:bg-primary-700 rounded-xs active:scale-95 transition-all outline-none shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        if (window.defaultDataTable) {
            window.stoTable = window.defaultDataTable('#stoEventsTable', {
                serverSide: true,
                ajax: "{{ route('inventory.sto.index') }}",
                order: [[1, 'desc']],
                columns: [
                    { data: 0, className: 'text-center text-slate-400 text-[11px]' },
                    { data: 1, className: 'text-left font-bold text-slate-800 dark:text-white text-xs' },
                    { data: 2, className: 'text-left text-xs text-slate-600 dark:text-gray-400' },
                    { data: 3, className: 'text-center' },
                    { data: 4, className: 'text-left font-medium text-slate-700 dark:text-gray-300 text-xs' },
                    { data: 5, className: 'text-center' },
                    { data: 6, className: 'text-center' },
                    { data: 7, className: 'text-center', orderable: false },
                    { data: 8, className: 'text-center', orderable: false }
                ]
            });
        }

        // Preview Code Logic
        const periodStartInput = document.getElementById('sto_period_start');
        const previewElement = document.getElementById('eventCodePreview');

        function updateCodePreview() {
            const date = periodStartInput.value;
            if (!date) return;

            fetch("{{ route('inventory.sto.previewCode') }}?date=" + date)
                .then(response => response.json())
                .then(data => {
                    if (data.code) {
                        previewElement.value = data.code;
                    }
                })
                .catch(error => console.error('Error fetching preview code:', error));
        }

        if (periodStartInput) {
            periodStartInput.addEventListener('change', updateCodePreview);
            // Initial update when script loads or modal triggers might be better on modal show
        }

        // Trigger update when modal button is clicked
        window.openCreateModal = function() {
            document.getElementById('createEventModal').classList.remove('hidden');
            updateCodePreview();
        };

        // --- Edit Logic ---
        window.editSto = function(hash_id) {
            const modal = document.getElementById('editEventModal');
            const form = document.getElementById('editEventForm');
            
            // Set form action
            form.action = "{{ url('inventory/sto') }}/" + hash_id;
            
            // Show loading or something if needed
            fetch("{{ url('inventory/sto') }}/" + hash_id + "/edit")
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('edit_code').value = data.data.code;
                        document.getElementById('edit_period_start').value = data.data.period_start;
                        document.getElementById('edit_description').value = data.data.description || '';
                        modal.classList.remove('hidden');
                    } else {
                        toastr.error('Failed to fetch STO details.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    toastr.error('An error occurred while fetching details.');
                });
        };

        $('#editEventForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const formData = form.serialize();
            
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: formData,
                success: function(data) {
                    if (data.success) {
                        toastr.success(data.message);
                        document.getElementById('editEventModal').classList.add('hidden');
                        if (window.stoTable) window.stoTable.ajax.reload(null, false);
                    } else {
                        toastr.error(data.message || 'Update failed.');
                    }
                },
                error: function(err) {
                    toastr.error('Failed to update event.');
                }
            });
        });

        // --- Delete Logic ---
        window.deleteSto = function(hash_id, code) {
            Swal.fire({
                title: 'Delete STO Event?',
                html: `This will permanently delete event <span class="font-black text-rose-600">${code}</span> and ALL calculation details. This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Delete Everything!',
                cancelButtonText: 'No, Keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('inventory/sto') }}/" + hash_id,
                        method: 'DELETE',
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(data) {
                            if (data.success) {
                                toastr.success(data.message);
                                if (window.stoTable) window.stoTable.ajax.reload(null, false);
                            } else {
                                toastr.error(data.message || 'Delete failed.');
                            }
                        },
                        error: function(err) {
                            toastr.error('Failed to delete event.');
                        }
                    });
                }
            });
        };
    });
</script>
@endpush
@endsection
