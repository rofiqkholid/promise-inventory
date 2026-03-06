@extends('layouts.app')

@section('title', 'System Access & User Management')
@section('page_title', 'Access Management')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tight">Access Control Center</h2>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium tracking-wide">Manage Roles, Permissions, User Accounts, and Application Menus.</p>
    </div>

    <!-- TABS NAVIGATION -->
    @include('inventory.user_access.partials._tabs_navigation')

    <!-- TABS CONTENT -->
    <div id="accessTabsContent">
        @include('inventory.user_access.partials._panel_users')
        @include('inventory.user_access.partials._panel_roles')
        @include('inventory.user_access.partials._panel_accounts')
        @include('inventory.user_access.partials._panel_menus')
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Global Variables for DataTables
    let userTable, rolesTable, accountsTable, menusTable;

    $(document).ready(function() {
        initTabs();
    });

    function initTabs() {
        const tabBtns = $('#accessTabs button');
        const panels = $('#accessTabsContent > div');

        tabBtns.on('click', function() {
            const target = $(this).data('tabs-target');
            tabBtns.removeClass('border-primary-600 dark:border-primary-500 text-primary-600 dark:text-white').addClass('border-transparent text-gray-500 dark:text-gray-400');
            $(this).removeClass('border-transparent text-gray-500 dark:text-gray-400').addClass('border-primary-600 dark:border-primary-500 text-primary-600 dark:text-white');
            panels.addClass('hidden');
            $(target).removeClass('hidden');

            // Force DataTables to recalculate columns when tab becomes visible
            if(target === '#roles-panel' && rolesTable) rolesTable.columns.adjust().draw();
            if(target === '#users-panel' && userTable) userTable.columns.adjust().draw();
            if(target === '#accounts-panel' && accountsTable) accountsTable.columns.adjust().draw();
            if(target === '#menus-panel' && menusTable) menusTable.columns.adjust().draw();
        });
        
        // Trigger first tab
        tabBtns.first().click();
    }

    // Shared Form Handler
    function handleFormSubmit(e, url, modalId, dataTable) {
        e.preventDefault();
        $.ajax({
            url: url,
            type: 'POST',
            data: $(e.target).serialize(),
            success: function(res) {
                window.showToast(res.message, 'success');
                if (modalId) closeModal(modalId);
                if (dataTable) dataTable.ajax.reload(null, false);
            },
            error: function(xhr) {
                window.showToast(xhr.responseJSON?.message || 'Something went wrong', 'error');
            }
        });
    }

    // Shared Delete Handler
    function deleteItem(url, table, title, text) {
        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(res) {
                        window.showToast(res.message, 'success');
                        if (table) table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        window.showToast(xhr.responseJSON?.message || 'Failed to delete', 'error');
                    }
                });
            }
        });
    }

    function closeModal(id) {
        $(`#${id}`).addClass('hidden').removeClass('flex');
    }
</script>

<style>
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fadeIn { animation: fadeIn 0.3s ease-out forwards; }
    .custom-scrollbar-minimal::-webkit-scrollbar { width: 3px; }
    .custom-scrollbar-minimal::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar-minimal::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .dark .custom-scrollbar-minimal::-webkit-scrollbar-thumb { background: #334155; }
    
    .select2-container--default .select2-selection--single { height: 38px !important; background-color: white !important; border: 1px solid #d1d5db !important; border-radius: 0.125rem !important; display: flex !important; align-items: center !important; }
    .dark .select2-container--default .select2-selection--single { background-color: #1f2937 !important; border: 1px solid #4b5563 !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { color: #1e293b !important; font-size: 0.75rem !important; font-weight: 600 !important; padding-left: 10px !important; }
    .dark .select2-container--default .select2-selection--single .select2-selection__rendered { color: white !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px !important; top: 0 !important; display: flex !important; align-items: center !important; right: 8px !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow b { margin-top: 0 !important; top: auto !important; }
</style>
@endpush
