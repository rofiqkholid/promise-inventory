@extends('layouts.app')

@section('title', 'Location Master')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="mb-4">
        <h2 class="text-xl xl:text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tighter leading-none">Location</h2>
        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 font-normal">Manage all storage locations (racks, warehouses, etc.)</p>
    </div>

    {{-- UNIFIED CARD HEADER TOOLBAR --}}
    <div id="locationCard" class="mb-0 bg-white dark:bg-gray-800 rounded-t-xs rounded-b-none border border-b-0 border-slate-200 dark:border-gray-700 overflow-hidden shadow-xs">
        <div class="px-5 py-3.5 bg-slate-50/70 dark:bg-slate-900/40 border-b border-slate-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h3 class="text-xs font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2 tracking-tight">
                <i class="fa-solid fa-location-dot text-primary-600"></i> Location List
            </h3>
            @if(Auth::user()->hasMenuPermission('inventory.master.location.index', 'create'))
            <button type="button" class="add-button inline-flex items-center justify-center gap-2 px-3.5 h-9 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-xs font-medium text-white active:scale-[0.98] transition-all shadow-xs w-full sm:w-auto" data-target="location">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
            @endif
        </div>
    </div>

    <x-table id="locationTable">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th scope="col" class="px-6 py-4 w-16 text-center text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">No</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Name</th>
                <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Status</th>
                @if(Auth::user()->hasMenuPermission('inventory.master.location.index', 'edit') || Auth::user()->hasMenuPermission('inventory.master.location.index', 'delete'))
                <th scope="col" class="px-6 py-4 text-center w-[120px] text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Action</th>
                @endif
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

{{-- Modals --}}
<div id="modal-location-add" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50">
    <div class="relative w-full max-w-md transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 transition-all border border-slate-200 dark:border-gray-800 flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white">Add Location</h3>
            <button type="button" class="close-modal text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100 dark:hover:bg-gray-800">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="overflow-y-auto px-6 py-6 custom-scrollbar flex-1">
            <form class="modal-form" data-action="{{ route('inventory.master.location.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Location Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all placeholder:text-gray-400" placeholder="e.g. Rack A-1">
                    <p class="error-msg hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Status</label>
                    <select name="is_active" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <p class="error-msg hidden"></p>
                </div>
            </form>
        </div>
        <div class="border-t border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 flex gap-3">
            <button type="button" class="close-modal flex-1 px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-xs font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors active:scale-95">Cancel</button>
            <button type="submit" class="submit-btn flex-1 px-4 py-2.5 bg-primary-600 border border-transparent rounded-xs text-xs font-medium text-white hover:bg-primary-700 transition-all active:scale-95">Save</button>
        </div>
    </div>
</div>

<div id="modal-location-edit" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50">
    <div class="relative w-full max-w-md transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 transition-all border border-slate-200 dark:border-gray-800 flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white">Edit Location</h3>
            <button type="button" class="close-modal text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100 dark:hover:bg-gray-800">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="overflow-y-auto px-6 py-6 custom-scrollbar flex-1">
            <form class="modal-form">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Location Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                    <p class="error-msg hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Status</label>
                    <select name="is_active" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <p class="error-msg hidden"></p>
                </div>
            </form>
        </div>
        <div class="border-t border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 flex gap-3">
            <button type="button" class="close-modal flex-1 px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-xs font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors active:scale-95">Cancel</button>
            <button type="submit" class="submit-btn flex-1 px-4 py-2.5 bg-primary-600 border border-transparent rounded-xs text-xs font-medium text-white hover:bg-primary-700 transition-all active:scale-95">Save Changes</button>
        </div>
    </div>
</div>

<x-inventory.delete-modal />

<style>
.error-msg { margin-top: 0.25rem; font-size: 0.75rem; line-height: 1rem; color: rgb(239 68 68); }
</style>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const csrf = $('meta[name="csrf-token"]').attr('content');
        const apiBase = "{{ url('inventory/master/location') }}";
        let deleteUrl = '';

        window.masterTable = window.defaultDataTable('#locationTable', {
            ajax: { url: '{{ route("inventory.master.location.data") }}', type: 'GET' },
            serverSide: true, processing: true,
            columns: [
                { data: null, orderable: false, searchable: false, render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                { data: 'name' },
                { 
                    data: 'is_active', 
                    className: 'text-center',
                    render: (d) => d 
                        ? '<span class="px-2 py-1.5 rounded-xs text-[10px] font-medium uppercase tracking-wide border bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/50">Active</span>' 
                        : '<span class="px-2 py-1.5 rounded-xs text-[10px] font-medium uppercase tracking-wide border bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800/50">Inactive</span>'
                },
                @if(Auth::user()->hasMenuPermission('inventory.master.location.index', 'edit') || Auth::user()->hasMenuPermission('inventory.master.location.index', 'delete'))
                {
                    data: null, orderable: false, searchable: false, className: 'text-center', width: '120px',
                    render: (d, t, r) => {
                        let buttons = '';
                        @if(Auth::user()->hasMenuPermission('inventory.master.location.index', 'edit'))
                        buttons += `
                             <button class="edit-btn h-8 w-8 inline-flex items-center justify-center text-primary-600 rounded-xs bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:hover:bg-primary-900/30 transition-colors" data-id="${r.hash_id}" title="Edit">
                                <i class="fa-solid fa-pen-to-square text-sm"></i>
                            </button>
                        `;
                        @endif
                        @if(Auth::user()->hasMenuPermission('inventory.master.location.index', 'delete'))
                        buttons += `
                            <button class="delete-btn h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-xs bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors" data-id="${r.hash_id}" title="Delete">
                                <i class="fa-solid fa-trash-can text-sm"></i>
                            </button>
                        `;
                        @endif
                        return `<div class="flex items-center justify-center gap-1.5">${buttons}</div>`;
                    }
                }
                @endif
            ],
            order: [[1, 'asc']]
        });

        const showMdl = (id) => { $('.modal-container').addClass('hidden'); $(`#${id}`).removeClass('hidden'); }
        const hideMdl = (id) => { $(`#${id}`).addClass('hidden'); }
        $('.close-modal').on('click', function() { $(this).closest('.modal-container').addClass('hidden'); });

        $('.add-button').on('click', function() {
            const $form = $('#modal-location-add form');
            $form[0].reset(); $form.find('.error-msg').addClass('hidden');
            showMdl('modal-location-add');
        });

        $(document).on('click', '.edit-btn', function() {
            const id = $(this).data('id');
            $.get(`${apiBase}/${id}`, (data) => {
                const $modal = $('#modal-location-edit'); 
                $modal.find('.error-msg').addClass('hidden');
                $modal.find(`[name="name"]`).val(data.name);
                $modal.find(`[name="is_active"]`).val(data.is_active ? "1" : "0");
                $modal.find('form').attr('action', `${apiBase}/${id}`);
                showMdl('modal-location-edit');
            });
        });

        $(document).on('click', '.delete-btn', function() {
            deleteUrl = `${apiBase}/${$(this).data('id')}`;
            showMdl('modal-delete');
        });

        $('#confirmDelete').on('click', function() {
            $.ajax({
                url: deleteUrl, 
                method: 'POST', 
                data: { _method: 'DELETE' },
                headers: { 'X-CSRF-TOKEN': csrf },
                success: (data) => {
                    if (data.success) { masterTable.ajax.reload(); hideMdl('modal-delete'); toast('success', 'Success', data.message); }
                },
                error: (xhr) => { toast('error', 'Error', xhr.responseJSON?.message || 'Delete failed'); }
            });
        });

        $(document).on('click', '.submit-btn', function() { $(this).closest('.modal-container').find('form').submit(); });

        $(document).on('submit', '.modal-form', function(e) {
            e.preventDefault();
            const $form = $(this); const formData = new FormData(this);
            const $submitBtn = $form.closest('.modal-container').find('.submit-btn');
            const originalText = $submitBtn.text();

            $submitBtn.prop('disabled', true).html('<i class="fa-solid fa-circle-notch fa-spin"></i> Saving...');

            $.ajax({
                url: $form.attr('action'), method: 'POST', headers: { 'X-CSRF-TOKEN': csrf },
                data: formData, processData: false, contentType: false,
                success: (data) => {
                    if (data.success) { masterTable.ajax.reload(); $form.closest('.modal-container').addClass('hidden'); toast('success', 'Success', data.message); }
                },
                error: (xhr) => {
                    const errors = xhr.responseJSON?.errors; $form.find('.error-msg').addClass('hidden');
                    if (errors) { Object.keys(errors).forEach(key => { $form.find(`[name="${key}"]`).next('.error-msg').text(errors[key][0]).removeClass('hidden'); }); }
                    toast('error', 'Error', xhr.responseJSON?.message || 'Operation failed');
                },
                complete: () => { $submitBtn.prop('disabled', false).text(originalText); }
            });
        });
    });
</script>
@endpush
