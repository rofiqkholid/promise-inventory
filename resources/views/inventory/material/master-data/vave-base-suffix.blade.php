@extends('layouts.app')

@section('title', 'VA/VE Suffix Master')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    {{-- Header Section --}}
    <div class="mb-4">
        <h2 class="text-xl xl:text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tighter leading-none">VA/VE Suffix</h2>
        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 font-normal">Manage suffixes for Engineering Breakdown (EBD) and Sales Quotation (SQ) versions.</p>
    </div>

    {{-- UNIFIED CARD HEADER TOOLBAR --}}
    <div id="vaveSuffixCard" class="mb-0 bg-white dark:bg-gray-800 rounded-t-xs rounded-b-none border border-b-0 border-slate-200 dark:border-gray-700 overflow-hidden shadow-xs">
        <div class="px-5 py-3.5 bg-slate-50/70 dark:bg-slate-900/40 border-b border-slate-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h3 class="text-xs font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2 tracking-tight">
                <i class="fa-solid fa-[#] fa-hashtag text-primary-600"></i> VA/VE Suffix List
            </h3>
            @if(Auth::user()->hasMenuPermission('inventory.master.vave-base-suffix.index', 'create'))
            <button type="button" class="add-button inline-flex items-center justify-center gap-2 px-3.5 h-9 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-xs font-medium text-white active:scale-[0.98] transition-all shadow-xs w-full sm:w-auto">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
            @endif
        </div>
    </div>

    <x-table id="vaveSuffixTable">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th scope="col" class="px-6 py-4 w-16 text-center text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">No</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Suffix Name</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Baseline Type</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Remark</th>
                <th scope="col" class="px-6 py-4 text-center w-24 text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Status</th>
                @if(Auth::user()->hasMenuPermission('inventory.master.vave-base-suffix.index', 'edit') || Auth::user()->hasMenuPermission('inventory.master.vave-base-suffix.index', 'delete'))
                <th scope="col" class="px-6 py-4 text-center w-[100px] text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Action</th>
                @endif
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

{{-- Modals --}}
<div id="modal-vave-suffix" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50">
    <div class="relative w-full max-w-md transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 transition-all border border-slate-200 dark:border-gray-800 flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="modal-title text-sm font-medium text-gray-900 dark:text-white">Add VA/VE Suffix</h3>
            <button type="button" class="close-modal text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100 dark:hover:bg-gray-800">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="overflow-y-auto px-6 py-6 custom-scrollbar flex-1">
            <form id="vaveSuffixForm" class="modal-form">
                @csrf
                <div id="method-field"></div>
                
                <div class="mb-5">
                    <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Baseline Type <span class="text-red-500">*</span></label>
                    <select name="base_type" id="base_type" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all select2-modal">
                        <option value="">-- Select Type --</option>
                        <option value="EBD">EBD (Engineering)</option>
                        <option value="SQ">SQ (Sales Quotation)</option>
                    </select>
                    <p class="error-msg hidden"></p>
                </div>

                <div class="mb-5">
                    <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Suffix Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all placeholder:text-gray-400 placeholder:font-normal" placeholder="e.g. SQ, Tech Review, Final">
                    <p class="error-msg hidden"></p>
                </div>

                <div class="mb-5">
                    <label class="block mb-2 text-[11px] font-medium text-slate-900 dark:text-gray-300">Remark</label>
                    <textarea name="remark" rows="3" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all placeholder:text-gray-400 placeholder:font-normal" placeholder="Optional notes..."></textarea>
                    <p class="error-msg hidden"></p>
                </div>

                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800/50 rounded-xs border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center h-5">
                        <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-primary-600 bg-white border-gray-300 rounded-sm focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <label class="text-[11px] font-medium text-slate-700 dark:text-gray-300">Active Status</label>
                </div>
                <p class="error-msg hidden"></p>
            </form>
        </div>
        <div class="border-t border-gray-200 dark:border-gray-800 px-6 py-5 bg-gray-50/50 dark:bg-gray-800/50 flex gap-3">
            <button type="button" class="close-modal flex-1 px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-xs font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors active:scale-95">Cancel</button>
            <button type="button" id="btnSubmit" class="flex-1 px-4 py-2.5 bg-primary-600 border border-transparent rounded-xs text-xs font-medium text-white hover:bg-primary-700 transition-all active:scale-95">Save</button>
        </div>
    </div>
</div>

<x-inventory.delete-modal />

<style>
.error-msg { margin-top: 0.25rem; font-size: 0.75rem; line-height: 1rem; color: rgb(239 68 68); font-weight: 600; }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const csrf = $('meta[name="csrf-token"]').attr('content');
        const apiBase = "{{ route('inventory.master.vave-base-suffix.index') }}";
        let deleteUrl = '';

        window.masterTable = window.defaultDataTable('#vaveSuffixTable', {
            ajax: { url: '{{ route("inventory.master.vave-base-suffix.data") }}', type: 'GET' },
            serverSide: true, processing: true,
            columns: [
                { data: null, orderable: false, searchable: false, className: 'text-center', render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                { data: 'name', className: 'font-medium text-slate-800 dark:text-slate-200' },
                { 
                    data: 'base_type', 
                    render: (d) => d ? `<span class="px-2 py-0.5 ${d === 'EBD' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800' : 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 border-purple-200 dark:border-purple-800'} rounded-px text-[9px] font-bold border uppercase tracking-wider">${d}</span>` : '-' 
                },
                { data: 'remark', render: (d) => d || '-' },
                { 
                    data: 'is_active', className: 'text-center',
                    render: (d) => d 
                        ? '<span class="px-2 py-0.5 bg-emerald-100/50 text-emerald-700 rounded-px text-[9px] font-bold border border-emerald-200 uppercase tracking-wider">Active</span>' 
                        : '<span class="px-2 py-0.5 bg-gray-100/50 text-gray-500 rounded-px text-[9px] font-bold border border-gray-200 uppercase tracking-wider">Inactive</span>'
                },
                @if(Auth::user()->hasMenuPermission('inventory.master.vave-base-suffix.index', 'edit') || Auth::user()->hasMenuPermission('inventory.master.vave-base-suffix.index', 'delete'))
                {
                    data: null, orderable: false, searchable: false, className: 'text-center', width: '100px',
                    render: (d, t, r) => {
                        let buttons = '';
                        @if(Auth::user()->hasMenuPermission('inventory.master.vave-base-suffix.index', 'edit'))
                        buttons += `
                             <button class="edit-btn h-8 w-8 inline-flex items-center justify-center text-primary-600 rounded-xs bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:hover:bg-primary-900/30 transition-colors" data-id="${r.hash_id}" title="Edit">
                                <i class="fa-solid fa-pen-to-square text-sm"></i>
                            </button>
                        `;
                        @endif
                        @if(Auth::user()->hasMenuPermission('inventory.master.vave-base-suffix.index', 'delete'))
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
            const $form = $('#vaveSuffixForm');
            $form[0].reset(); 
            $form.find('#method-field').empty();
            $form.attr('action', apiBase);
            $('.modal-title').text('Add VA/VE Suffix');
            $('#base_type').val('').trigger('change.select2');
            $form.find('.error-msg').addClass('hidden');
            showMdl('modal-vave-suffix');
        });

        $(document).on('click', '.edit-btn', function() {
            const id = $(this).data('id');
            showMdl('modal-vave-suffix'); // Show loading state or clear previous
            $.get(`${apiBase}/${id}`, (data) => {
                const $form = $('#vaveSuffixForm');
                $('.modal-title').text('Edit VA/VE Suffix');
                $form.find('.error-msg').addClass('hidden');
                $form.find('#method-field').html('@method("PUT")');
                
                Object.keys(data).forEach(key => { 
                    const $input = $form.find(`[name="${key}"]`);
                    if ($input.is(':checkbox')) {
                        $input.prop('checked', data[key] == 1);
                    } else if ($input.hasClass('select2-modal')) {
                        $input.val(data[key]).trigger('change.select2');
                    } else {
                        $input.val(data[key]);
                    }
                });
                
                $form.attr('action', `${apiBase}/${id}`);
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

        $('#btnSubmit').on('click', function() { $('#vaveSuffixForm').submit(); });

        $(document).on('submit', '#vaveSuffixForm', function(e) {
            e.preventDefault();
            const $form = $(this); 
            const formData = new FormData(this);
            const $submitBtn = $('#btnSubmit');
            const originalText = $submitBtn.text();

            $submitBtn.prop('disabled', true).html('<i class="fa-solid fa-circle-notch fa-spin"></i> Saving...');
            
            $.ajax({
                url: $form.attr('action'), 
                method: 'POST', 
                headers: { 'X-CSRF-TOKEN': csrf },
                data: formData, 
                processData: false, 
                contentType: false,
                success: (data) => {
                    if (data.success) { 
                        masterTable.ajax.reload(); 
                        hideMdl('modal-vave-suffix'); 
                        toast('success', 'Success', data.message); 
                    }
                },
                error: (xhr) => {
                    const errors = xhr.responseJSON?.errors; 
                    $form.find('.error-msg').addClass('hidden');
                    if (errors) { 
                        Object.keys(errors).forEach(key => { 
                            const $err = $form.find(`[name="${key}"]`).closest('div').find('.error-msg');
                            $err.text(errors[key][0]).removeClass('hidden'); 
                        }); 
                    }
                    toast('error', 'Error', xhr.responseJSON?.message || 'Operation failed');
                },
                complete: () => { $submitBtn.prop('disabled', false).text(originalText); }
            });
        });

        // Initialize Select2 in modal
        $('.select2-modal').select2({
            dropdownParent: $('#modal-vave-suffix'),
            width: '100%'
        });
    });
</script>
@endpush
