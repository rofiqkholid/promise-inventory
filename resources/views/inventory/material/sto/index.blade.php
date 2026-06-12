@extends('layouts.app')

@section('title', 'Stock Opname Management')
@section('page_title', 'Stock Opname')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    <div class="mb-4">
        <h2 class="text-xl xl:text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tighter leading-none">Stock Opname</h2>
        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 font-normal">Coordinate and track physical inventory count events.</p>
    </div>

    <div class="mb-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="p-4 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 flex-1 w-full flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <h3 class="font-bold text-slate-800 dark:text-white leading-tight">Active STO Events</h3>
                <p class="text-[11px] text-gray-500 font-normal">List of scheduled and completed inventory counts.</p>
            </div>
            
            <div class="flex items-center gap-2 w-full md:w-auto overflow-x-auto pb-1 md:pb-0">
                @if(Auth::user()->hasMenuPermission('inventory.sto.index', 'create'))
                    <button onclick="window.openCreateModal()" class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 px-4 h-9 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-xs font-medium text-white active:scale-[0.98] transition-all whitespace-nowrap shadow-sm">
                        <i class="fa-solid fa-calendar-plus text-sm"></i> 
                        New STO Event
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Events Table -->
    <x-table id="stoEventsTable">
        <thead>
            <tr>
                <th class="w-16 text-center text-[10px] font-semibold tracking-wider text-slate-550 dark:text-slate-400 uppercase">No</th>
                <th class="text-left w-48 text-[10px] font-semibold tracking-wider text-slate-550 dark:text-slate-400 uppercase">Event Code</th>
                <th class="text-left text-[10px] font-semibold tracking-wider text-slate-550 dark:text-slate-400 uppercase">Counting Period</th>
                <th class="text-left w-40 text-[10px] font-semibold tracking-wider text-slate-550 dark:text-slate-400 uppercase">PIC</th>
                <th class="text-center w-32 text-[10px] font-semibold tracking-wider text-slate-550 dark:text-slate-400 uppercase">Net Amount (Rp)</th>
                <th class="text-center w-32 text-[10px] font-semibold tracking-wider text-slate-550 dark:text-slate-400 uppercase">Net Qty</th>
                <th class="text-center w-32 text-[10px] font-semibold tracking-wider text-slate-550 dark:text-slate-400 uppercase">Status</th>
                <th class="text-center w-40 text-[10px] font-semibold tracking-wider text-slate-550 dark:text-slate-400 uppercase">STO Control</th>
                <th class="w-[100px] text-center text-[10px] font-semibold tracking-wider text-slate-550 dark:text-slate-400 uppercase">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

<!-- Create Modal -->
<div id="createEventModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <div class="fixed inset-0 bg-slate-900/50" aria-hidden="true" onclick="document.getElementById('createEventModal').classList.add('hidden')"></div>
        
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xs text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full relative z-10 border border-slate-200 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-3">
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
                        <label class="block mb-1 text-[11px] font-bold text-slate-500">Start Date</label>
                        <input type="date" id="sto_period_start" name="period_start" required class="w-full p-2.5 bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 rounded-xs text-xs font-bold focus:ring-1 focus:ring-primary-500 outline-none" value="{{ date('Y-m-d') }}">
                    </div>
                    
                    <div>
                        <label class="block mb-1 text-[11px] font-bold text-slate-400">Event Code Preview</label>
                        <input type="text" id="eventCodePreview" readonly class="w-full p-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-gray-700 rounded-xs text-xs font-mono font-bold text-slate-500 dark:text-gray-400" value="SAI/STO/{{ date('dmY') }}/....">
                    </div>
                    
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-xs border border-slate-100 dark:border-gray-700">
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">Assigned PIC</label>
                        <div class="text-xs font-bold text-slate-700 dark:text-gray-300 flex items-center gap-2">
                             <i class="fa-solid fa-user-check text-[10px] text-slate-400"></i>
                             {{ auth()->user()->name }}
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 text-[11px] font-bold text-slate-500">Notes / Scope (Optional)</label>
                        <textarea name="description" rows="3" class="w-full p-2.5 bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 rounded-xs text-xs font-medium focus:ring-1 focus:ring-primary-500 outline-none" placeholder="Enter STO notes or coverage area..."></textarea>
                    </div>
                </div>
                
                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="document.getElementById('createEventModal').classList.add('hidden')" class="flex-1 py-2.5 text-xs font-medium text-gray-600 bg-white hover:bg-gray-50 rounded-xs transition-all border border-slate-200 shadow-sm active:scale-95">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 text-xs font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-xs active:scale-95 transition-all outline-none shadow-sm">Initialize Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editEventModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <div class="fixed inset-0 bg-slate-900/50" aria-hidden="true" onclick="document.getElementById('editEventModal').classList.add('hidden')"></div>
        
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xs text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full relative z-10 border border-slate-200 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-3">
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
                        <label class="block mb-1 text-[11px] font-bold text-slate-500">Start Date</label>
                        <input type="date" id="edit_period_start" name="period_start" required class="w-full p-2.5 bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 rounded-xs text-xs font-bold focus:ring-1 focus:ring-primary-500 outline-none">
                    </div>
                    
                    <div>
                        <label class="block mb-1 text-[11px] font-bold text-slate-400">Event Code</label>
                        <input type="text" id="edit_code" readonly class="w-full p-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-gray-700 rounded-xs text-xs font-mono font-bold text-slate-400">
                    </div>

                    <div>
                        <label class="block mb-1 text-[11px] font-bold text-slate-500">Notes / Scope (Optional)</label>
                        <textarea id="edit_description" name="description" rows="3" class="w-full p-2.5 bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 rounded-xs text-xs font-medium focus:ring-1 focus:ring-primary-500 outline-none" placeholder="Enter STO notes..."></textarea>
                    </div>
                </div>
                
                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="document.getElementById('editEventModal').classList.add('hidden')" class="flex-1 py-2.5 text-xs font-medium text-gray-600 bg-white hover:bg-gray-50 rounded-xs transition-all border border-slate-200 shadow-sm active:scale-95">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 text-xs font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-xs active:scale-95 transition-all outline-none shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        @if(session('error'))
            if (typeof window.showToast === 'function') {
                window.showToast("{!! addslashes(session('error')) !!}", 'error');
            }
        @endif

        @if(session('success'))
            if (typeof window.showToast === 'function') {
                window.showToast("{!! addslashes(session('success')) !!}", 'success');
            }
        @endif

        if (window.defaultDataTable) {
            window.stoTable = window.defaultDataTable('#stoEventsTable', {
                serverSide: true,
                ajax: "{{ route('inventory.sto.index') }}",
                order: [[1, 'desc']],
                columns: [
                    { data: 'row_no', orderable: false, className: 'text-center text-slate-500 text-[11px]' },
                    { data: 'code', className: 'text-left font-bold text-slate-800 dark:text-white text-xs' },
                    { 
                        data: null, 
                        className: 'text-left text-xs text-slate-600 dark:text-gray-400',
                        render: function(data) {
                            let period = data.period_start;
                            if (data.period_end && data.status === 'CLOSED') {
                                period += ' - ' + data.period_end;
                            }
                            return period;
                        }
                    },
                    { data: 'pic_name', className: 'text-left font-medium text-slate-700 dark:text-gray-300 text-xs' },
                    { 
                        data: 'net_amount', 
                        className: 'text-center',
                        render: function(val) {
                            if (val == 0) return '<span class="text-xs font-medium text-slate-350">0</span>';
                            let prefix = val > 0 ? '+' : '-';
                            return `<span class="text-xs font-medium text-red-600">${prefix}Rp ${Math.abs(val).toLocaleString()}</span>`;
                        }
                    },
                    { 
                        data: null, 
                        className: 'text-center',
                        render: function(data) {
                            const valPcs = data.net_pcs ?? 0;
                            const valQty = data.net_qty ?? 0;
                            if (valPcs == 0 && valQty == 0) return '<span class="text-xs font-medium text-slate-350">0</span>';
                            
                            const prefixPcs = valPcs > 0 ? '+' : (valPcs < 0 ? '-' : '');
                            const prefixQty = valQty > 0 ? '+' : (valQty < 0 ? '-' : '');
                            
                            const formattedPcs = valPcs !== 0 ? `${prefixPcs}${Math.abs(valPcs).toLocaleString()} Pcs` : '0 Pcs';
                            const formattedQty = valQty !== 0 ? `(${prefixQty}${parseFloat(Math.abs(valQty).toFixed(2)).toLocaleString()} Unit)` : '(0 Unit)';
                            
                            return `
                                <div class="flex flex-col items-center">
                                    <span class="text-xs font-semibold text-red-600">${formattedPcs}</span>
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">${formattedQty}</span>
                                </div>
                            `;
                        }
                    },
                    { 
                        data: 'status', 
                        className: 'text-center',
                        render: function(status) {
                            let statusClass = 'bg-slate-50 text-slate-600 border-slate-100 dark:bg-slate-900/40 dark:text-slate-400 dark:border-slate-800';
                            if (status === 'OPEN') statusClass = 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-400 dark:border-emerald-800';
                            else if (status === 'WAITING CHECK') statusClass = 'bg-amber-50 text-amber-600 border-amber-100 dark:bg-amber-900/40 dark:text-amber-400 dark:border-amber-800';
                            else if (status === 'WAITING APPROVAL') statusClass = 'bg-primary-50 text-primary-600 border-primary-100 dark:bg-primary-900/40 dark:text-primary-400 dark:border-primary-800';
                            
                            return `<span class="px-2 py-1 text-[10px] font-bold rounded-xs whitespace-nowrap border ${statusClass}">${status.replace('_', ' ')}</span>`;
                        }
                    },
                    { 
                        data: null, 
                        className: 'text-center', 
                        orderable: false,
                        render: function(data) {
                            const baseUrl = "{{ url('inventory/sto') }}/" + data.hash_id;
                            
                            const getStep = (label, icon, statusTarget, colorKey, isDone, isCurrent, hasPermission) => {
                                let opacity = isDone || isCurrent ? 'opacity-100' : 'opacity-20 grayscale';
                                let cursor = isDone || isCurrent ? '' : 'cursor-not-allowed';
                                
                                const configs = {
                                    emerald: { bg: 'bg-emerald-50', hover: 'hover:bg-emerald-100', text: 'text-emerald-600', border: 'border-emerald-200', dark: 'dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800' },
                                    primary: { bg: 'bg-primary-50', hover: 'hover:bg-primary-100', text: 'text-primary-600', border: 'border-primary-200', dark: 'dark:bg-primary-900/20 dark:text-primary-400 dark:border-primary-800' },
                                    amber: { bg: 'bg-amber-50', hover: 'hover:bg-amber-100', text: 'text-amber-600', border: 'border-amber-200', dark: 'dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800' },
                                    indigo: { bg: 'bg-indigo-50', hover: 'hover:bg-indigo-100', text: 'text-indigo-600', border: 'border-indigo-200', dark: 'dark:bg-indigo-900/20 dark:text-indigo-400 dark:border-indigo-800' },
                                    slate: { bg: 'bg-slate-50', hover: 'hover:bg-slate-100', text: 'text-slate-400', border: 'border-slate-200', dark: 'dark:bg-slate-900/20 dark:text-slate-400 dark:border-slate-800' }
                                };

                                let config = isDone ? configs.emerald : (isCurrent ? configs[colorKey] : configs.slate);
                                let ringClass = isCurrent ? 'ring-2 ring-primary-500/30' : '';
                                
                                let tag = (isDone || isCurrent) ? 'a' : 'div';
                                let href = (isDone || isCurrent) ? ` href="${baseUrl}"` : '';
                                
                                return `<${tag}${href} class="h-10 w-10 inline-flex items-center justify-center ${config.bg} ${config.text} ${config.dark} border ${config.border} ${config.hover} ${opacity} ${cursor} ${ringClass} rounded-full transition-colors shadow-sm" title="${label}">
                                    <i class="fa-solid ${icon} text-sm"></i>
                                </${tag}>`;
                            };

                            let steps = [];
                            steps.push(getStep('Count', 'fa-list-check', 'OPEN', 'primary', data.status !== 'OPEN', data.status === 'OPEN', true));
                            steps.push(getStep('Verify', 'fa-magnifying-glass', 'WAITING CHECK', 'amber', ['WAITING APPROVAL', 'CLOSED'].includes(data.status), data.status === 'WAITING CHECK', data.is_checker));
                            steps.push(getStep('Approve', 'fa-lock', 'WAITING APPROVAL', 'indigo', data.status === 'CLOSED', data.status === 'WAITING APPROVAL', data.is_approver));

                            let html = `<div class="flex items-center justify-center gap-0 py-1">${steps.join('<div class="w-4 h-[2px] bg-slate-200 dark:bg-slate-700"></div>')}</div>`;
                            return html;
                        }
                    },
                    { 
                        data: null, 
                        className: 'text-center', 
                        orderable: false,
                        render: function(data) {
                            if (!data.can_manage) return '';
                            return `
                                <div class="flex items-center justify-center gap-1.5">
                                    <button onclick="editSto('${data.hash_id}')" class="h-8 w-8 inline-flex items-center justify-center text-primary-600 rounded-xs bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:hover:bg-primary-900/30 transition-colors" title="Edit Event Details">
                                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                                    </button>
                                    <button onclick="deleteSto('${data.hash_id}', '${data.code}')" class="h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-xs bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors" title="Delete Event & All Details">
                                        <i class="fa-solid fa-trash-can text-sm"></i>
                                    </button>
                                </div>`;
                        }
                    }
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
                        window.showToast('Failed to fetch STO details.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    window.showToast('An error occurred while fetching details.', 'error');
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
                        window.showToast(data.message, 'success');
                        document.getElementById('editEventModal').classList.add('hidden');
                        if (window.stoTable) window.stoTable.ajax.reload(null, false);
                    } else {
                        window.showToast(data.message || 'Update failed.', 'error');
                    }
                },
                error: function(err) {
                    window.showToast('Failed to update event.', 'error');
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
                        method: 'POST',
                        data: { 
                            _token: "{{ csrf_token() }}",
                            _method: 'DELETE'
                        },
                        success: function(data) {
                            if (data.success) {
                                window.showToast(data.message, 'success');
                                if (window.stoTable) window.stoTable.ajax.reload(null, false);
                            } else {
                                window.showToast(data.message || 'Delete failed.', 'error');
                            }
                        },
                        error: function(err) {
                            window.showToast('Failed to delete event.', 'error');
                        }
                    });
                }
            });
        };
    });
</script>
@endpush
@endsection
