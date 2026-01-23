@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="flex justify-between items-center mb-4 md:mb-6">
        <h1 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white">Stock Opname (STO) Events</h1>
        @auth
            <button onclick="document.getElementById('createEventModal').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 text-sm whitespace-nowrap">
                <i class="fa-solid fa-plus"></i> New Event
            </button>
        @endauth
    </div>

    <!-- Stats or Info could go here -->

    <!-- Events List - Responsive -->
    <x-table id="stoEventsTable">
        <thead>
            <tr>
                <th class="px-4 md:px-6 py-2 md:py-3 w-10 text-center">No</th>
                <th class="px-4 md:px-6 py-2 md:py-3">Code</th>
                <th class="px-4 md:px-6 py-2 md:py-3">Name</th>
                <th class="px-4 md:px-6 py-2 md:py-3">Period</th>
                <th class="px-4 md:px-6 py-2 md:py-3">Status</th>
                <th class="px-4 md:px-6 py-2 md:py-3">PIC</th>
                <th class="px-4 md:px-6 py-2 md:py-3 text-center">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
            {{-- Data populated via AJAX --}}
        </tbody>
    </x-table>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.defaultDataTable) {
            window.defaultDataTable('stoEventsTable', {
                serverSide: true,
                ajax: "{{ route('inventory.sto.index') }}",
                order: [[1, 'desc']],
                columns: [
                    { className: 'px-4 md:px-6 py-3 md:py-4 text-center text-gray-500 font-medium', orderable: false, searchable: false },
                    { className: 'px-4 md:px-6 py-3 md:py-4 font-medium text-gray-800 dark:text-gray-200' },
                    { className: 'px-4 md:px-6 py-3 md:py-4 text-gray-600 dark:text-gray-400' },
                    { className: 'px-4 md:px-6 py-3 md:py-4 text-gray-600 dark:text-gray-400 whitespace-nowrap' },
                    { className: 'px-4 md:px-6 py-3 md:py-4' },
                    { className: 'px-4 md:px-6 py-3 md:py-4 text-gray-600 dark:text-gray-400' },
                    { className: 'px-4 md:px-6 py-3 md:py-4 text-center', orderable: false }
                ]
            });
        }
    });
</script>
@endpush

<!-- Create Modal -->
<div id="createEventModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/50 transition-opacity" aria-hidden="true" onclick="document.getElementById('createEventModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative z-10" onclick="event.stopPropagation()">
            <form action="{{ route('inventory.sto.store') }}" method="POST">
                @csrf
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4" id="modal-title">Create New STO Event</h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Event Name</label>
                        <input type="text" name="name" required class="w-full border rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. STO Semester 1 2026">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                        <input type="date" name="period_start" required class="w-full border rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">PIC (Person In Charge)</label>
                        <select name="user_id" required class="w-full border rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach($pics as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description (Optional)</label>
                        <textarea name="description" rows="3" class="w-full border rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Create Event</button>
                    <button type="button" onclick="document.getElementById('createEventModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 hover:bg-gray-200 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
