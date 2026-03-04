@extends('layouts.app')

@section('title', 'Location Master')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">Location</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Manage all storage locations (racks, warehouses, etc.)</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button type="button" class="add-button inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-slate-900 hover:bg-slate-800 border border-transparent rounded-md text-[10px] font-bold text-white uppercase tracking-widest shadow-md active:scale-[0.98] transition-all" data-target="location">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
    </div>

    <x-table id="locationTable">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th scope="col" class="px-6 py-3 w-16 text-center text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">No</th>
                <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Name</th>
                <th scope="col" class="px-6 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Status</th>
                <th scope="col" class="px-6 py-3 text-center w-[120px] text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

{{-- Modals --}}
<div id="modal-location-add" class="modal-container hidden">
    <div class="relative p-4 w-full max-w-md">
        <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal text-gray-400 absolute top-2.5 right-2.5 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
            </button>
            <h3 class="mb-4 text-xl text-center font-bold text-slate-900 dark:text-white uppercase tracking-tight">Add Location</h3>
            <form class="modal-form" data-action="{{ route('inventory.master.location.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Location Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="bg-white border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-slate-500 focus:border-slate-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm" placeholder="e.g. Rack A-1">
                    <p class="error-msg hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Status</label>
                    <select name="is_active" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-slate-500 focus:border-slate-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <p class="error-msg hidden"></p>
                </div>
                <div class="flex gap-4 border-t border-gray-100 pt-4 mt-4">
                    <button type="button" class="close-modal flex-1 px-5 py-2.5 bg-white border border-gray-300 rounded-md text-[10px] font-bold text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition-colors">Cancel</button>
                    <button type="submit" class="flex-1 px-5 py-2.5 bg-slate-900 border border-transparent rounded-md text-[10px] font-bold text-white uppercase tracking-widest hover:bg-slate-800 transition-colors shadow-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modal-location-edit" class="modal-container hidden">
    <div class="relative p-4 w-full max-w-md">
        <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal text-gray-400 absolute top-2.5 right-2.5 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
            </button>
            <h3 class="mb-4 text-xl text-center font-bold text-slate-900 dark:text-white uppercase tracking-tight">Edit Location</h3>
            <form class="modal-form">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Location Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="bg-white border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-slate-500 focus:border-slate-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm">
                    <p class="error-msg hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Status</label>
                    <select name="is_active" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-slate-500 focus:border-slate-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <p class="error-msg hidden"></p>
                </div>
                <div class="flex gap-4 border-t border-gray-100 pt-4 mt-4">
                    <button type="button" class="close-modal flex-1 px-5 py-2.5 bg-white border border-gray-300 rounded-md text-[10px] font-bold text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition-colors">Cancel</button>
                    <button type="submit" class="flex-1 px-5 py-2.5 bg-slate-900 border border-transparent rounded-md text-[10px] font-bold text-white uppercase tracking-widest hover:bg-slate-800 transition-colors shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<x-inventory.delete-modal />

<style>
.modal-container {
    position: fixed; top: 0; right: 0; left: 0; bottom: 0; z-index: 50;
    display: none; justify-content: center; align-items: center;
    width: 100%; height: 100%; background-color: rgb(15 23 42 / 0.5); backdrop-filter: blur(4px);
}
.modal-container:not(.hidden) { display: flex; }
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
                        ? '<span class="px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Active</span>' 
                        : '<span class="px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Inactive</span>'
                },
                {
                    data: null, orderable: false, searchable: false, className: 'text-center', width: '120px',
                    render: (d, t, r) => `
                        <div class="flex items-center justify-center gap-2">
                            <button class="edit-btn h-8 w-8 inline-flex items-center justify-center text-blue-600 rounded-lg bg-blue-50 hover:bg-blue-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-blue-400 transition-all duration-200" data-id="${r.hash_id}" title="Edit">
                                <i class="fa-solid fa-pen-to-square text-sm"></i>
                            </button>
                            <button class="delete-btn h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-lg bg-red-50 hover:bg-red-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-red-400 transition-all duration-200" data-id="${r.hash_id}" title="Delete">
                                <i class="fa-solid fa-trash-can text-sm"></i>
                            </button>
                        </div>`
                }
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
                url: deleteUrl, method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf },
                success: (data) => {
                    if (data.success) { masterTable.ajax.reload(); hideMdl('modal-delete'); toast('success', 'Success', data.message); }
                },
                error: (xhr) => { toast('error', 'Error', xhr.responseJSON?.message || 'Delete failed'); }
            });
        });

        $(document).on('submit', '.modal-form', function(e) {
            e.preventDefault();
            const $form = $(this); const formData = new FormData(this);
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
                }
            });
        });
    });
</script>
@endpush
