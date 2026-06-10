@extends('layouts.app')

@section('title', 'Coil Center Master')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-xl xl:text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tighter leading-none">Coil Center</h2>
            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 font-normal">Manage all industrial coil center configurations.</p>
        </div>
        @if(Auth::user()->hasMenuPermission('inventory.master.coilCenter.index', 'create'))

        <div class="mt-4 sm:mt-0">
            <button type="button" class="add-button inline-flex items-center justify-center gap-2 px-4 h-9 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-xs font-medium text-white active:scale-[0.98] transition-all shadow-sm" data-target="coil-center">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
        @endif
    </div>

    <x-table id="coilCenterTable">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th class="px-6 py-4 w-16 text-center text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">No</th>
                <th class="px-6 py-4 text-left w-32 text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Code</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Name</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Email</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Phone</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Address</th>
                <th class="px-6 py-4 text-center w-[100px] text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

{{-- Modals --}}
<div id="modal-coil-center-add" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50">
    <div class="relative w-full max-w-md transform overflow-hidden rounded-xs bg-white dark:bg-gray-800 transition-all border border-slate-200 dark:border-gray-800 flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white">Add Coil Center</h3>
            <button type="button" class="close-modal text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100 dark:hover:bg-gray-800">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="overflow-y-auto px-6 py-6 custom-scrollbar flex-1">
            <form class="modal-form" data-action="{{ route('inventory.master.coilCenter.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all" placeholder="e.g. CC001">
                    <p class="error-msg hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Name</label>
                    <input type="text" name="name" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                    <p class="error-msg hidden"></p>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Email</label>
                        <input type="email" name="email" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                        <p class="error-msg hidden"></p>
                    </div>
                    <div>
                        <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Phone</label>
                        <input type="text" name="phone" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                        <p class="error-msg hidden"></p>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Address</label>
                    <textarea name="address" rows="3" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all"></textarea>
                    <p class="error-msg hidden"></p>
                </div>
                <div class="border-t border-gray-100 dark:border-gray-800 mt-6 pt-4 flex gap-3">
                    <button type="button" class="close-modal flex-1 px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-xs font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors active:scale-95">Cancel</button>
                    <button type="submit" class="submit-btn flex-1 px-4 py-2.5 bg-primary-600 border border-transparent rounded-xs text-xs font-medium text-white hover:bg-primary-700 transition-all active:scale-95">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modal-coil-center-edit" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50">
    <div class="relative w-full max-w-md transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 transition-all border border-slate-200 dark:border-gray-800 flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white">Edit Coil Center</h3>
            <button type="button" class="close-modal text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100 dark:hover:bg-gray-800">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="overflow-y-auto px-6 py-6 custom-scrollbar flex-1">
            <form class="modal-form">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all" placeholder="e.g. CC001">
                    <p class="error-msg hidden"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Name</label>
                    <input type="text" name="name" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                    <p class="error-msg hidden"></p>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Email</label>
                        <input type="email" name="email" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                        <p class="error-msg hidden"></p>
                    </div>
                    <div>
                        <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Phone</label>
                        <input type="text" name="phone" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                        <p class="error-msg hidden"></p>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Address</label>
                    <textarea name="address" rows="3" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all"></textarea>
                    <p class="error-msg hidden"></p>
                </div>
                <div class="border-t border-gray-100 dark:border-gray-800 mt-6 pt-4 flex gap-3">
                    <button type="button" class="close-modal flex-1 px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-xs font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors active:scale-95">Cancel</button>
                    <button type="submit" class="submit-btn flex-1 px-4 py-2.5 bg-primary-600 border border-transparent rounded-xs text-xs font-medium text-white hover:bg-primary-700 transition-all active:scale-95">Save Changes</button>
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
    width: 100%; height: 100%; background-color: rgb(2 6 23 / 0.6);
}
.modal-container:not(.hidden) { display: flex; }
.error-msg { margin-top: 0.25rem; font-size: 0.75rem; line-height: 1rem; color: rgb(239 68 68); }
</style>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const csrf = $('meta[name="csrf-token"]').attr('content');
        const apiBase = "{{ url('inventory/master/coil-center') }}";
        let deleteUrl = '';

        window.masterTable = window.defaultDataTable('#coilCenterTable', {
            ajax: { url: '{{ route("inventory.master.coilCenter.data") }}', type: 'GET' },
            serverSide: true, processing: true,
            columns: [
                { data: null, orderable: false, searchable: false, render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                { data: 'code' },
                { data: 'name' },
                { data: 'email', defaultContent: '-', render: (d) => d || '-' },
                { data: 'phone', defaultContent: '-', render: (d) => d || '-' },
                { data: 'address', orderable: false },
                {
                    data: null, orderable: false, searchable: false, className: 'text-center', width: '100px',
                    render: (d, t, r) => `
                        <div class="flex items-center justify-center gap-1.5">
                            <button class="edit-btn h-8 w-8 inline-flex items-center justify-center text-primary-600 rounded-xs bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:hover:bg-primary-900/30 transition-colors" data-id="${r.hash_id}" title="Edit">
                                <i class="fa-solid fa-pen-to-square text-sm"></i>
                            </button>
                            <button class="delete-btn h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-xs bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors" data-id="${r.hash_id}" title="Delete">
                                <i class="fa-solid fa-trash-can text-sm"></i>
                            </button>
                        </div>`
                }
                @endif
            ],
            order: [[1, 'asc']]
        });

        const showMdl = (id) => { $('.modal-container').addClass('hidden'); $(`#${id}`).removeClass('hidden'); }
        const hideMdl = (id) => { $(`#${id}`).addClass('hidden'); }
        $('.close-modal').on('click', function() { $(this).closest('.modal-container').addClass('hidden'); });

        $('.add-button').on('click', function() {
            const $form = $('#modal-coil-center-add form');
            $form[0].reset(); $form.find('.error-msg').addClass('hidden');
            showMdl('modal-coil-center-add');
        });

        $(document).on('click', '.edit-btn', function() {
            const id = $(this).data('id');
            $.get(`${apiBase}/${id}`, (data) => {
                const $modal = $('#modal-coil-center-edit'); 
                $modal.find('.error-msg').addClass('hidden');
                Object.keys(data).forEach(key => { $modal.find(`[name="${key}"]`).val(data[key]); });
                $modal.find('form').attr('action', `${apiBase}/${id}`);
                showMdl('modal-coil-center-edit');
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
