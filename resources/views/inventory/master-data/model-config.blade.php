@extends('layouts.app')
@section('title', 'Model Configuration')
@section('page_title', 'Model Configuration')
@section('header-title', 'Model Config & Setup')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">Model Configuration</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Batch manage specific configuration / status per model (e.g., Project vs Regular).</p>
        </div>
    </div>

    <!-- Data Table -->
    <x-table id="modelConfigTable">
        <thead>
            <tr>
                <th scope="col" class="px-6 py-3 w-16 text-center text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">No</th>
                <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Customer</th>
                <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 w-full">Model Name</th>
                <th scope="col" class="px-6 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 min-w-[150px]">Project Status</th>
            </tr>
        </thead>
        <tbody>
            <!-- Loaded via DataTables -->
        </tbody>
    </x-table>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        const table = window.defaultDataTable('#modelConfigTable', {
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("inventory.master.modelConfig.data") }}',
                type: 'GET'
            },
            columns: [
                {
                    data: null,
                    className: 'px-4 py-3 text-center text-xs text-gray-500',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'customer_code',
                    className: 'px-4 py-3 text-xs font-semibold'
                },
                {
                    data: 'name',
                    className: 'px-4 py-3 text-sm font-bold text-slate-800 dark:text-gray-200'
                },
                {
                    data: 'project_status',
                    className: 'px-4 py-3 text-center',
                    orderable: false,
                    render: function(data, type, row) {
                        const isProject = data === 'Project';
                        // Switch toggle design for status
                        return `
                            <div class="flex justify-center items-center">
                                <label class="relative inline-flex items-center cursor-pointer group" title="Toggle Project Status">
                                    <input type="checkbox" class="sr-only peer status-toggle" data-id="${row.hash_id}" ${isProject ? 'checked' : ''}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                    <span class="ml-3 text-xs font-bold uppercase tracking-wider ${isProject ? 'text-orange-500 dark:text-orange-400' : 'text-emerald-500 dark:text-emerald-400'} status-text">
                                        ${isProject ? 'Project' : 'Regular'}
                                    </span>
                                </label>
                            </div>
                        `;
                    }
                }
            ],
            order: [[2, 'asc']] // order by model name
        });

        // Handle AJAX Toggle
        $(document).on('change', '.status-toggle', function() {
            const $checkbox = $(this);
            const hashId = $checkbox.data('id');
            const isChecked = $checkbox.is(':checked');
            const newStatus = isChecked ? 'Project' : 'Regular';
            const $textSpan = $checkbox.siblings('.status-text');

            // Optimistic UI update
            $textSpan.text(newStatus)
                .removeClass('text-orange-500 text-emerald-500 dark:text-orange-400 dark:text-emerald-400')
                .addClass(isChecked ? 'text-orange-500 dark:text-orange-400' : 'text-emerald-500 dark:text-emerald-400');

            $.ajax({
                url: '{{ route("inventory.master.modelConfig.updateStatus") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    model_hash_id: hashId,
                    field: 'project_status',
                    value: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        toast('success', 'Updated', 'Configuration updated successfully');
                    } else {
                        // Revert on logic fail
                        $checkbox.prop('checked', !isChecked);
                        toast('error', 'Error', response.message || 'Failed to update configuration');
                    }
                },
                error: function(xhr) {
                    // Revert on error
                    $checkbox.prop('checked', !isChecked);
                    const msg = xhr.responseJSON?.message || 'Failed to update configuration';
                    toast('error', 'Error', msg);
                }
            });
        });
    });
</script>
@endpush
@endsection
