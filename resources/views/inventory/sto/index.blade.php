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
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs uppercase">
                    <th class="px-4 md:px-6 py-2 md:py-3 border-b dark:border-gray-600">Code</th>
                    <th class="px-4 md:px-6 py-2 md:py-3 border-b dark:border-gray-600">Name</th>
                    <th class="px-4 md:px-6 py-2 md:py-3 border-b dark:border-gray-600">Period</th>
                    <th class="px-4 md:px-6 py-2 md:py-3 border-b dark:border-gray-600">Status</th>
                    <th class="px-4 md:px-6 py-2 md:py-3 border-b dark:border-gray-600">PIC</th>
                    <th class="px-4 md:px-6 py-2 md:py-3 border-b dark:border-gray-600 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                @forelse($events as $event)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <td class="px-4 md:px-6 py-3 md:py-4 font-medium text-gray-800 dark:text-gray-200">{{ $event->code }}</td>
                    <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 dark:text-gray-400">{{ $event->name }}</td>
                    <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                        {{ $event->period_start->format('d M Y') }} 
                        @if($event->period_end && $event->status === 'CLOSED')
                             - {{ $event->period_end->format('d M Y') }}
                        @endif
                    </td>
                    <td class="px-4 md:px-6 py-3 md:py-4">
                        <span class="px-2 py-1 text-xs rounded-full whitespace-nowrap
                            {{ $event->status === 'OPEN' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                            {{ $event->status }}
                        </span>
                    </td>
                    <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 dark:text-gray-400">{{ $event->pic->name ?? '-' }}</td>
                    <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                        <a href="{{ route('inventory.sto.show', $event->hash_id) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-semibold text-sm">
                            {{ $event->status === 'OPEN' ? 'Manage' : 'View' }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 md:px-6 py-6 md:py-8 text-center text-gray-500 dark:text-gray-400">
                        No STO events found. Create one to get started.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="px-6 py-4 border-t dark:border-gray-600">
            {{ $events->links() }}
        </div>
    </div>
</div>

<!-- Create Modal -->
<div id="createEventModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('createEventModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative z-10" onclick="event.stopPropagation()">
            <form action="{{ route('inventory.sto.store') }}" method="POST">
                @csrf
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4" id="modal-title">Create New STO Event</h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Event Name</label>
                        <input type="text" name="name" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. STO Semester 1 2026">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                        <input type="date" name="period_start" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">PIC (Person In Charge)</label>
                        <select name="pic_id" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach($pics as $pic)
                                <option value="{{ $pic->hash_id }}">{{ $pic->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description (Optional)</label>
                        <textarea name="description" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
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
