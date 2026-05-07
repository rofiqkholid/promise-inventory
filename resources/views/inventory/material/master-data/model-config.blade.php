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
        <div class="mt-4 sm:mt-0 flex gap-2">
            <a href="{{ route('inventory.projectVaveDashboard.index') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xs transition-all shadow-sm active:scale-95">
                <i class="fa-solid fa-chart-line mr-2"></i> Project VAVE
            </a>
            <a href="{{ route('inventory.regularVaveDashboard.index') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xs transition-all shadow-sm active:scale-95">
                <i class="fa-solid fa-chart-line mr-2"></i> Regular VAVE
            </a>
        </div>
    </div>
    <!-- Filter Bar -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Customer Filter -->
            <div>
                <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Customer</label>
                <select id="filterCustomer" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">All Customers</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Project Status</label>
                <select id="filterStatus" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">All Statuses</option>
                    <option value="Project">Project Only</option>
                    <option value="Regular">Regular Only</option>
                </select>
            </div>

            <!-- Validity Filter (Only relevant for Regular/All) -->
            <div id="containerValidity">
                <label class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Lifecycle Validity</label>
                <select id="filterValidity" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">All (Active & Expired)</option>
                    <option value="active">Active (Valid)</option>
                    <option value="expired">Expired Only</option>
                </select>
            </div>
        </div>
    </div>
    <!-- Data Table -->
    <x-table id="modelConfigTable">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th scope="col" class="px-6 py-4 w-16 text-center text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">No</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Customer</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700 w-full">Model Name</th>
                <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700 min-w-[150px]">Regular Start Date</th>
                <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700 min-w-[150px]">Regular Expire Date</th>
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
                type: 'GET',
                data: function(d) {
                    d.filter_customer = $('#filterCustomer').val();
                    d.filter_status = $('#filterStatus').val();
                    d.filter_validity = $('#filterValidity').val();
                }
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
                    data: 'regular_start_date',
                    className: 'px-4 py-3 text-center',
                    orderable: false,
                    render: function(data, type, row) {
                        return `<input type="date" class="date-updater bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" data-id="${row.hash_id}" data-field="regular_start_date" value="${data || ''}">`;
                    }
                },
                {
                    data: 'regular_expired_date',
                    className: 'px-4 py-3 text-center',
                    orderable: false,
                    render: function(data, type, row) {
                        return `<input type="date" class="date-updater bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" data-id="${row.hash_id}" data-field="regular_expired_date" value="${data || ''}">`;
                    }
                },
                {
                    data: 'project_status',
                    className: 'px-4 py-3 text-center',
                    orderable: false,
                    render: function(data, type, row) {
                        // Force "Regular" status display if expired, even if DB says Project
                        const displayAsRegular = row.is_expired ? true : (data === 'Regular');
                        const isDisabled = row.is_expired;
                        
                        // Switch toggle design for status: Regular is ON, Project is OFF
                        return `
                            <div class="flex flex-col justify-center items-center gap-1">
                                ${row.is_expired ? '<span class="px-2 py-0.5 text-[10px] font-bold bg-red-100 text-red-600 rounded dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800 shadow-sm">EXPIRED</span>' : ''}
                                <label class="relative inline-flex items-center ${isDisabled ? 'cursor-not-allowed opacity-70' : 'cursor-pointer'} group" title="${isDisabled ? 'Cannot toggle: Model is Expired' : 'Toggle Project Status'}">
                                    <input type="checkbox" class="sr-only peer status-toggle" data-id="${row.hash_id}" ${displayAsRegular ? 'checked' : ''} ${isDisabled ? 'disabled' : ''}>
                                    <div class="w-10 h-5.5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4.5 after:w-4.5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                                    <span class="ml-3 text-xs font-medium ${displayAsRegular ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400'} status-text">
                                        ${displayAsRegular ? 'Regular' : 'Project'}
                                    </span>
                                </label>
                            </div>
                        `;
                    }
                }
            ],
            order: [[2, 'asc']] // order by model name
        });

        // Trigger table reload on filter change
        $('#filterCustomer, #filterStatus, #filterValidity').on('change', function() {
            // Hide/Show Validity filter based on Status
            if ($('#filterStatus').val() === 'Project') {
                $('#containerValidity').fadeOut('fast');
                $('#filterValidity').val(''); // Reset validity filter if hidden
            } else {
                $('#containerValidity').fadeIn('fast');
            }
            table.ajax.reload();
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

        // Handle Date Changes
        $(document).on('change', '.date-updater', function() {
            const $input = $(this);
            const hashId = $input.data('id');
            const field = $input.data('field');
            const value = $input.val();

            // Auto-fill expiry date (12 months ahead) when start date is set
            if (field === 'regular_start_date' && value) {
                const $expiryInput = $(`input[data-id="${hashId}"][data-field="regular_expired_date"]`);
                if (!$expiryInput.val()) {
                    const startDate = new Date(value);
                    startDate.setFullYear(startDate.getFullYear() + 1);
                    const yyyy = startDate.getFullYear();
                    const mm = String(startDate.getMonth() + 1).padStart(2, '0');
                    const dd = String(startDate.getDate()).padStart(2, '0');
                    $expiryInput.val(`${yyyy}-${mm}-${dd}`).trigger('change');
                }
            }

            $.ajax({
                url: '{{ route("inventory.master.modelConfig.updateStatus") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    model_hash_id: hashId,
                    field: field,
                    value: value
                },
                success: function(response) {
                    if (response.success) {
                        toast('success', 'Updated', 'Date updated successfully');
                        // Always reload to refresh the 'is_expired' badge and status logic
                        table.ajax.reload(null, false);
                    } else {
                        toast('error', 'Error', response.message || 'Failed to update date');
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Failed to update date';
                    toast('error', 'Error', msg);
                }
            });
        });
    });
</script>
@endpush
@endsection
