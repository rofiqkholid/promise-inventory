@extends('layouts.app')
@section('title', 'Model Configuration')
@section('page_title', 'Model Configuration')
@section('header-title', 'Model Config & Setup')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h2 class="text-xl xl:text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tighter leading-none">Model Configuration</h2>
            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 font-normal">Batch manage specific configuration / status per model (e.g., Project vs Regular).</p>
        </div>
    </div>

    <!-- Data Table -->
    <x-table id="modelConfigTable">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th scope="col" class="px-6 py-4 w-16 text-center text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">No</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Customer</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700 w-full">Model Name</th>
                <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700 min-w-[150px]">Project Status</th>
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
                    className: 'px-4 py-3 text-xs font-medium'
                },
                {
                    data: 'name',
                    className: 'px-4 py-3 text-sm font-medium text-slate-800 dark:text-gray-200'
                },
                {
                    data: 'project_status',
                    className: 'px-4 py-3 text-center',
                    orderable: false,
                    render: function(data, type, row) {
                        const isRegular = data === 'Regular';
                        // Switch toggle design for status: Regular is ON, Project is OFF
                        return `
                            <div class="flex justify-center items-center">
                                <label class="relative inline-flex items-center cursor-pointer group" title="Toggle Project Status">
                                    <input type="checkbox" class="sr-only peer status-toggle" data-id="${row.hash_id}" ${isRegular ? 'checked' : ''}>
                                    <div class="w-10 h-5.5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4.5 after:w-4.5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                                    <span class="ml-3 text-xs font-medium ${isRegular ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400'} status-text">
                                        ${isRegular ? 'Regular' : 'Project'}
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
            const newStatus = isChecked ? 'Regular' : 'Project';
            const $textSpan = $checkbox.siblings('.status-text');

            // Optimistic UI update
            $textSpan.text(newStatus)
                .removeClass('text-primary-600 text-gray-500 dark:text-primary-400 dark:text-gray-400')
                .addClass(isChecked ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400');

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
