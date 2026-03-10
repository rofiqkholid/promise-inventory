@extends('layouts.app')

@section('title', 'Revision Master')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">Revision</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Manage product revision codes and their order</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button type="button" class="add-button inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest active:scale-[0.98] transition-all" data-target="revision">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-gray-700 bg-slate-50/50 dark:bg-slate-900/30">
            <h3 class="text-[10px] font-bold text-gray-900 dark:text-white flex items-center gap-3 uppercase tracking-[0.15em]">
                <i class="fa-solid fa-list-ol text-primary-600"></i> Revision Sequence
            </h3>
        </div>
        <div class="p-0 overflow-x-auto">
             <x-table id="revisionTable">
                <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-4 w-16 text-center text-[10px] font-bold uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">No</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">Code</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">Group</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">Order</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">Status</th>
                        <th class="px-6 py-4 text-center w-[120px] text-[10px] font-bold uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700"></tbody>
            </x-table>
        </div>
    </div>
</div>

{{-- Modals --}}
<div id="modal-revision-add" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/60 transition-opacity">
    <div class="relative w-full max-w-md transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 transition-all border border-slate-200 dark:border-gray-800 flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-widest">Add Revision</h3>
            <button type="button" class="close-modal text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100 dark:hover:bg-gray-800">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="overflow-y-auto px-6 py-6 custom-scrollbar flex-1">
            <form class="modal-form" data-action="{{ route('inventory.master.revision.store') }}">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Revision Code <span class="text-red-500">*</span></label>
                        <input type="text" name="code" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all placeholder:text-gray-400" placeholder="e.g. R1">
                        <p class="error-msg hidden text-red-500 text-[10px] mt-1 font-bold"></p>
                    </div>
                    <div class="mb-4">
                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Sort Order <span class="text-red-500">*</span></label>
                        <input type="number" name="sort_order" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all" placeholder="e.g. 10">
                        <p class="error-msg hidden text-red-500 text-[10px] mt-1 font-bold"></p>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Group Name <span class="text-red-500">*</span></label>
                    <select name="group_name" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                        <option value="Standard">Standard</option>
                        <option value="Correction">Correction</option>
                    </select>
                    <p class="error-msg hidden text-red-500 text-[10px] mt-1 font-bold"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Status</label>
                    <select name="is_active" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <p class="error-msg hidden text-red-500 text-[10px] mt-1 font-bold"></p>
                </div>
            </form>
        </div>
        <div class="border-t border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 flex gap-3">
            <button type="button" class="close-modal flex-1 px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</button>
            <button type="submit" class="submit-btn flex-1 px-4 py-3 bg-primary-600 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest hover:bg-primary-700 transition-all font-sans">Save</button>
        </div>
    </div>
</div>

<div id="modal-revision-edit" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/60 transition-opacity">
    <div class="relative w-full max-w-md transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 transition-all border border-slate-200 dark:border-gray-800 flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-widest">Edit Revision</h3>
            <button type="button" class="close-modal text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100 dark:hover:bg-gray-800">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="overflow-y-auto px-6 py-6 custom-scrollbar flex-1">
            <form class="modal-form">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Revision Code <span class="text-red-500">*</span></label>
                        <input type="text" name="code" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                        <p class="error-msg hidden text-red-500 text-[10px] mt-1 font-bold"></p>
                    </div>
                    <div class="mb-4">
                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Sort Order <span class="text-red-500">*</span></label>
                        <input type="number" name="sort_order" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                        <p class="error-msg hidden text-red-500 text-[10px] mt-1 font-bold"></p>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Group Name <span class="text-red-500">*</span></label>
                    <select name="group_name" id="edit_group_name" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                        <option value="Standard">Standard</option>
                        <option value="Correction">Correction</option>
                    </select>
                    <p class="error-msg hidden text-red-500 text-[10px] mt-1 font-bold"></p>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Status</label>
                    <select name="is_active" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <p class="error-msg hidden text-red-500 text-[10px] mt-1 font-bold"></p>
                </div>
            </form>
        </div>
        <div class="border-t border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 flex gap-3">
            <button type="button" class="close-modal flex-1 px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</button>
            <button type="submit" class="submit-btn flex-1 px-4 py-3 bg-primary-600 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest hover:bg-primary-700 transition-all">Save Changes</button>
        </div>
    </div>
</div>

<x-inventory.delete-modal />

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const csrf = $('meta[name="csrf-token"]').attr('content');
        const apiBase = "{{ url('inventory/master/revision') }}";
        let deleteUrl = '';

        window.masterTable = window.defaultDataTable('#revisionTable', {
            ajax: { url: '{{ route("inventory.master.revision.data") }}', type: 'GET' },
            serverSide: true, processing: true,
            columns: [
                { data: null, orderable: false, searchable: false, render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1, className: 'text-center' },
                { data: 'code', className: 'font-bold' },
                { data: 'group_name' },
                { data: 'sort_order', className: 'text-center' },
                { 
                    data: 'is_active', 
                    className: 'text-center',
                    render: (d) => d 
                        ? '<span class="px-2 py-1.5 rounded-xs text-[10px] font-bold uppercase tracking-wide border bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/50">Active</span>' 
                        : '<span class="px-2 py-1.5 rounded-xs text-[10px] font-bold uppercase tracking-wide border bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800/50">Inactive</span>'
                },
                {
                    data: null, orderable: false, searchable: false, className: 'text-center', width: '120px',
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
            ],
            order: [[2, 'asc'], [3, 'asc']]
        });

        const showMdl = (id) => { $('.modal-container').addClass('hidden'); $(`#${id}`).removeClass('hidden'); }
        const hideMdl = (id) => { $(`#${id}`).addClass('hidden'); }
        $('.close-modal').on('click', function() { $(this).closest('.modal-container').addClass('hidden'); });

        $('.add-button').on('click', function() {
            const $form = $('#modal-revision-add form');
            $form[0].reset(); $form.find('.error-msg').addClass('hidden');
            showMdl('modal-revision-add');
        });

        $(document).on('click', '.edit-btn', function() {
            const id = $(this).data('id');
            $.get(`${apiBase}/${id}`, (data) => {
                const $modal = $('#modal-revision-edit'); 
                $modal.find('.error-msg').addClass('hidden');
                $modal.find(`[name="code"]`).val(data.code);
                $modal.find(`[name="group_name"]`).val(data.group_name);
                $modal.find(`[name="sort_order"]`).val(data.sort_order);
                $modal.find(`[name="is_active"]`).val(data.is_active ? "1" : "0");
                $modal.find('form').attr('action', `${apiBase}/${id}`);
                showMdl('modal-revision-edit');
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
                    if (data.success) { masterTable.ajax.reload(); hideMdl('modal-delete'); window.showToast(data.message, 'success'); }
                },
                error: (xhr) => { window.showToast(xhr.responseJSON?.message || 'Delete failed', 'error'); }
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
                    if (data.success) { masterTable.ajax.reload(); $form.closest('.modal-container').addClass('hidden'); window.showToast(data.message, 'success'); }
                },
                error: (xhr) => {
                    const errors = xhr.responseJSON?.errors; $form.find('.error-msg').addClass('hidden');
                    if (errors) { Object.keys(errors).forEach(key => { $form.find(`[name="${key}"]`).next('.error-msg').text(errors[key][0]).removeClass('hidden'); }); }
                    window.showToast(xhr.responseJSON?.message || 'Operation failed', 'error');
                },
                complete: () => { $submitBtn.prop('disabled', false).text(originalText); }
            });
        });
    });
</script>
@endpush
