@extends('layouts.app')

@section('title', 'Supplier Master')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-xl xl:text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tighter leading-none">Supplier</h2>
            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 font-normal">Manage local and global suppliers.</p>
        </div>
        @if(Auth::user()->hasMenuPermission('inventory.master.supplier.index', 'create'))

        <div class="mt-4 sm:mt-0">
            <button type="button" class="add-button inline-flex items-center justify-center gap-2 px-4 h-9 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-xs font-medium text-white active:scale-[0.98] transition-all shadow-sm" data-target="supplier">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
        @endif
    </div>

    <x-table id="supplierTable">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th scope="col" class="px-6 py-4 w-16 text-center text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">No</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Code</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Name</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Email</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Phone</th>
                @if(Auth::user()->hasMenuPermission('inventory.master.supplier.index', 'edit') || Auth::user()->hasMenuPermission('inventory.master.supplier.index', 'delete'))
                <th scope="col" class="px-6 py-4 text-center w-[100px] text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Action</th>
                @endif
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

{{-- Modals --}}
<div id="modal-supplier-add" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50">
    <div class="relative w-full max-w-2xl transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 transition-all border border-slate-200 dark:border-gray-800 flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white">Add Supplier</h3>
            <button type="button" class="close-modal text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100 dark:hover:bg-gray-800">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="overflow-y-auto px-6 py-6 custom-scrollbar flex-1">
            <form class="modal-form" data-action="{{ route('inventory.master.supplier.store') }}">
                @csrf
                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xs flex items-center justify-center gap-8 border border-gray-100 dark:border-gray-700">
                    <label class="inline-flex items-center gap-3 cursor-pointer group">
                        <input type="radio" name="source_type" value="manual" checked class="hidden peer">
                        <div class="w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded-full flex items-center justify-center peer-checked:border-primary-600 dark:peer-checked:border-primary-500 transition-all group-hover:scale-110">
                            <div class="w-2.5 h-2.5 bg-primary-600 dark:bg-primary-500 rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                        </div>
                        <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400 peer-checked:text-gray-900 dark:peer-checked:text-white">Manual Input</span>
                    </label>
                    <label class="inline-flex items-center gap-3 cursor-pointer group">
                        <input type="radio" name="source_type" value="global" class="hidden peer">
                        <div class="w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded-full flex items-center justify-center peer-checked:border-primary-600 dark:peer-checked:border-primary-500 transition-all group-hover:scale-110">
                            <div class="w-2.5 h-2.5 bg-primary-600 dark:bg-primary-500 rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                        </div>
                        <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400 peer-checked:text-gray-900 dark:peer-checked:text-white">Global Promise</span>
                    </label>
                </div>

                <div id="global-supplier-container" class="hidden mb-6">
                    <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Search Global Supplier</label>
                    <select id="global_supplier_search" class="select2-global-supplier w-full"></select>
                    <input type="hidden" name="promise_supp_id" id="add_promise_supp_id">
                </div>

                <div id="supplier-detail-fields">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Code <span class="text-red-500">*</span></label>
                            <input type="text" name="code" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all placeholder:text-gray-400">
                            <p class="error-msg hidden"></p>
                        </div>
                        <div>
                            <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all placeholder:text-gray-400">
                            <p class="error-msg hidden"></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Email</label>
                            <input type="email" name="email" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all placeholder:text-gray-400" placeholder="e.g. supplier@example.com">
                            <p class="error-msg hidden"></p>
                        </div>
                        <div>
                            <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Phone</label>
                            <input type="text" name="phone" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all placeholder:text-gray-400" placeholder="e.g. 021-123456">
                            <p class="error-msg hidden"></p>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Address</label>
                        <textarea name="address" rows="3" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all placeholder:text-gray-400" placeholder="Enter supplier address..."></textarea>
                        <p class="error-msg hidden"></p>
                    </div>
                    <div class="mb-4">
                        <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Remark</label>
                        <input type="text" name="remark" maxlength="100" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all placeholder:text-gray-400" placeholder="Optional notes...">
                        <p class="error-msg hidden"></p>
                    </div>
                </div>

                <div id="supplier-card-preview" class="hidden mb-6 p-6 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs">
                    <div class="grid grid-cols-2 gap-y-6 gap-x-8">
                        <div>
                            <p class="text-[9px] font-medium text-gray-500 uppercase tracking-wider mb-1">Code</p>
                            <p id="card-code" class="text-sm font-medium text-gray-900 dark:text-white">-</p>
                        </div>
                        <div>
                            <p class="text-[9px] font-medium text-gray-500 uppercase tracking-wider mb-1">Name</p>
                            <p id="card-name" class="text-sm font-medium text-gray-900 dark:text-white">-</p>
                        </div>
                        <div>
                            <p class="text-[9px] font-medium text-gray-500 uppercase tracking-wider mb-1">Email</p>
                            <p id="card-email" class="text-sm font-medium text-gray-900 dark:text-white">-</p>
                        </div>
                        <div>
                            <p class="text-[9px] font-medium text-gray-500 uppercase tracking-wider mb-1">Phone</p>
                            <p id="card-phone" class="text-sm font-medium text-gray-900 dark:text-white">-</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-[9px] font-medium text-gray-500 uppercase tracking-wider mb-1">Address</p>
                            <p id="card-address" class="text-sm font-medium text-gray-900 dark:text-white">-</p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="border-t border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 flex gap-3">
            <button type="button" class="close-modal flex-1 px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-xs font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors active:scale-95">Cancel</button>
            <button type="submit" class="submit-btn flex-1 px-4 py-2.5 bg-primary-600 border border-transparent rounded-xs text-xs font-medium text-white hover:bg-primary-700 transition-all active:scale-95">Save Supplier</button>
        </div>
    </div>
</div>

<div id="modal-supplier-edit" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50">
    <div class="relative w-full max-w-2xl transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 transition-all border border-slate-200 dark:border-gray-800 flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white">Edit Supplier</h3>
            <button type="button" class="close-modal text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100 dark:hover:bg-gray-800">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="overflow-y-auto px-6 py-6 custom-scrollbar flex-1">
            <form class="modal-form">
                @csrf
                @method('PUT')
                <div id="supplier-edit-detail-fields">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Code <span class="text-red-500">*</span></label>
                            <input type="text" name="code" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                            <p class="error-msg hidden"></p>
                        </div>
                        <div>
                            <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                            <p class="error-msg hidden"></p>
                        </div>
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
                    <div class="mb-4">
                        <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Remark</label>
                        <input type="text" name="remark" maxlength="100" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                        <p class="error-msg hidden"></p>
                    </div>
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
        const apiBase = "{{ url('inventory/master/supplier') }}";
        let deleteUrl = '';

        window.masterTable = window.defaultDataTable('#supplierTable', {
            ajax: { url: '{{ route("inventory.master.supplier.data") }}', type: 'GET' },
            serverSide: true, processing: true,
            columns: [
                { data: null, orderable: false, searchable: false, render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                { data: 'code' },
                {
                    data: 'name',
                    render: (d, t, r) => r.is_linked == 1 ? `<div class="flex items-center gap-2"><span>${d}</span><i class="fa-solid fa-cloud text-primary-500"></i></div>` : d
                },
                { data: 'email' }, { data: 'phone' },
                @if(Auth::user()->hasMenuPermission('inventory.master.supplier.index', 'edit') || Auth::user()->hasMenuPermission('inventory.master.supplier.index', 'delete'))
                {
                    data: null, orderable: false, searchable: false, className: 'text-center', width: '100px',
                    render: (d, t, r) => {
                        let buttons = '';
                        @if(Auth::user()->hasMenuPermission('inventory.master.supplier.index', 'edit'))
                        buttons += `
                             <button class="edit-btn h-8 w-8 inline-flex items-center justify-center text-primary-600 rounded-xs bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:hover:bg-primary-900/30 transition-colors" data-id="${r.hash_id}" title="Edit">
                                <i class="fa-solid fa-pen-to-square text-sm"></i>
                            </button>
                        `;
                        @endif
                        @if(Auth::user()->hasMenuPermission('inventory.master.supplier.index', 'delete'))
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
            const $form = $('#modal-supplier-add form');
            $form[0].reset(); $form.find('.error-msg').addClass('hidden');
            $form.find('input[name="source_type"][value="manual"]').prop('checked', true).trigger('change');
            showMdl('modal-supplier-add');
        });

        $(document).on('change', 'input[name="source_type"]', function() {
            const val = $(this).val();
            if (val === 'global') {
                $('#global-supplier-container').removeClass('hidden'); $('#supplier-detail-fields').addClass('hidden');
                $('.select2-global-supplier').select2({
                    placeholder: 'Select Global Supplier...', allowClear: true, dropdownParent: $('#modal-supplier-add'),
                    ajax: {
                        url: '{{ route("inventory.master.supplier.getGlobal") }}', dataType: 'json', delay: 250,
                        data: p => ({ q: p.term || '', page: p.page || 1 }),
                        processResults: d => ({ results: d.results, pagination: { more: d.pagination.more } })
                    }
                });
            } else {
                $('#global-supplier-container').addClass('hidden'); $('#supplier-detail-fields').removeClass('hidden');
                $('#supplier-card-preview').addClass('hidden');
            }
        });

        $(document).on('change', '#global_supplier_search', function() {
            const id = $(this).val(); if (!id) return;
            $('#add_promise_supp_id').val(id);
            $.get(`{{ url('inventory/master/supplier/global') }}/${id}`, d => {
                if (d) { 
                    $('#card-code').text(d.code); 
                    $('#card-name').text(d.name); 
                    $('#card-email').text(d.email || '-');
                    $('#card-phone').text(d.phone || '-');
                    $('#card-address').text(d.address || '-');
                    $('#supplier-card-preview').removeClass('hidden'); 
                }
            });
        });

        $(document).on('click', '.edit-btn', function() {
            const id = $(this).data('id');
            $.get(`${apiBase}/${id}`, (data) => {
                const $modal = $('#modal-supplier-edit'); 
                $modal.find('.error-msg').addClass('hidden');
                Object.keys(data).forEach(key => { $modal.find(`[name="${key}"]`).val(data[key]); });
                $modal.find('form').attr('action', `${apiBase}/${id}`);
                showMdl('modal-supplier-edit');
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
