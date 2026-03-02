@extends('layouts.app')

@section('title', 'Stock Opname Management')
@section('page_title', 'Stock Opname')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    <div class="mb-6">
        <h2 class="text-2xl font-black text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">Stock Opname Monitor</h2>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Coordinate and track physical inventory count events.</p>
    </div>

    <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-md border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-gray-900 dark:text-white">Active STO Events</h3>
            <p class="text-xs text-gray-500 font-medium">List of all scheduled and completed inventory counts.</p>
        </div>
        @if(auth()->user()->hasAppRole('pic') || auth()->user()->hasAppRole('approver') || auth()->user()->hasAppRole('admin'))
            <button onclick="window.openCreateModal()" class="w-full md:w-auto inline-flex items-center justify-center px-6 py-2 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-sm font-bold rounded-md transition-all shadow-sm hover:bg-slate-800 gap-2">
                <i class="fa-solid fa-plus text-xs"></i> New Event
            </button>
        @endif
    </div>

    <!-- Events Table -->
    <x-table id="stoEventsTable">
        <thead>
            <tr>
                <th class="w-16">No</th>
                <th class="text-left w-48">Event Code</th>
                <th class="text-left">Counting Period</th>
                <th class="text-center w-32">Status</th>
                <th class="text-left w-40">PIC</th>
                <th class="text-center w-40">Variance</th>
                <th class="w-40 text-center">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

<!-- Create Modal -->
<div id="createEventModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="document.getElementById('createEventModal').classList.add('hidden')"></div>
        
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-md text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full relative z-10 border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <i class="fa-solid fa-calendar-plus text-blue-600"></i> Initialize STO
                </h3>
                <button onclick="document.getElementById('createEventModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <form action="{{ route('inventory.sto.store') }}" method="POST" class="p-5">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block mb-1.5 text-xs font-bold text-gray-400 uppercase tracking-widest">Event Code Preview</label>
                        <input type="text" id="eventCodePreview" readonly class="w-full p-2 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md text-sm font-mono font-bold text-gray-700 dark:text-gray-300" value="SAI/STO/{{ date('dmY') }}/....">
                    </div>

                    <div>
                        <label class="block mb-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Start Date</label>
                        <input type="date" id="sto_period_start" name="period_start" required class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md text-sm font-bold" value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-md border border-blue-100 dark:border-blue-800">
                        <label class="block text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-1.5">Assigned PIC (Auto)</label>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-600 rounded-md flex items-center justify-center text-white text-xs font-black shadow-sm">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-black text-gray-900 dark:text-white">{{ auth()->user()->name }}</span>
                                <span class="text-[9px] text-blue-500 font-bold uppercase tracking-tighter">Event Creator</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Notes / Scope (Optional)</label>
                        <textarea name="description" rows="3" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md text-sm font-medium"></textarea>
                    </div>
                </div>
                
                <div class="mt-6 flex gap-3">
                    <button type="button" onclick="document.getElementById('createEventModal').classList.add('hidden')" class="flex-1 py-2 text-sm font-bold text-gray-500 bg-gray-100 hover:bg-gray-200 rounded-md transition-all">Cancel</button>
                    <button type="submit" class="flex-1 py-2 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-md shadow-sm active:scale-95 transition-all outline-none">Initialize Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        if (window.defaultDataTable) {
            window.defaultDataTable('#stoEventsTable', {
                serverSide: true,
                ajax: "{{ route('inventory.sto.index') }}",
                order: [[1, 'desc']],
                columns: [
                    { data: 0, className: 'text-center font-bold text-gray-400' },
                    { data: 1, className: 'text-left font-mono font-bold text-[13px] text-blue-700 dark:text-blue-400' },
                    { data: 2, className: 'text-sm font-medium text-gray-500' },
                    { data: 3, className: 'text-center' },
                    { data: 4, className: 'text-left font-bold text-blue-600 dark:text-blue-400' },
                    { data: 5, className: 'text-center' },
                    { data: 6, className: 'text-center', orderable: false }
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
    });
</script>
@endpush
@endsection
