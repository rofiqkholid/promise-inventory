@extends('layouts.app')

@section('title', 'Tool Location Master')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">Tool Locations</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Manage storage locations for tool inventory.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button type="button" class="add-button inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest active:scale-[0.98] transition-all">
                <i class="fa-solid fa-plus"></i> Add Location
            </button>
        </div>
    </div>

    <x-table id="locationTable">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th scope="col" class="px-6 py-4 w-16 text-center text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">No</th>
                <th scope="col" class="px-6 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Code</th>
                <th scope="col" class="px-6 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Name</th>
                <th scope="col" class="px-6 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Category</th>
                <th scope="col" class="px-6 py-4 text-left text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Description</th>
                <th scope="col" class="px-6 py-4 text-center w-[100px] text-[10px] font-bold tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

{{-- Modal Form --}}
<div id="modal-location-form" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50">
    <div class="relative w-full max-w-md transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 transition-all border border-slate-200 dark:border-gray-800 flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-widest" id="modal-title">Add Location</h3>
            <button type="button" class="close-modal text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100 dark:hover:bg-gray-800">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="overflow-y-auto px-6 py-6 custom-scrollbar flex-1">
            <form id="locationForm">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <input type="hidden" id="locationId">
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Location Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all placeholder:text-gray-400" placeholder="e.g. WH-A1">
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Location Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all placeholder:text-gray-400" placeholder="e.g. Warehouse Area A1">
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Category <span class="text-red-500">*</span></label>
                    <select name="category" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all">
                        <option value="storage">Storage</option>
                        <option value="machine">Machine</option>
                        <option value="subcont">Subcont</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Description</label>
                    <textarea name="description" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-3 transition-all placeholder:text-gray-400" rows="3"></textarea>
                </div>
            </form>
        </div>
        <div class="border-t border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 flex gap-3">
            <button type="button" class="close-modal flex-1 px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xs text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</button>
            <button type="button" id="saveLocationBtn" class="flex-1 px-4 py-3 bg-primary-600 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest hover:bg-primary-700 transition-all">Save</button>
        </div>
    </div>
</div>

<x-inventory.delete-modal />
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const csrf    = $('meta[name="csrf-token"]').attr('content');
    const apiBase = "{{ route('inventory.tool.location.index') }}";
    let deleteUrl = '';

    window.masterTable = window.defaultDataTable('#locationTable', {
        ajax: { url: apiBase, type: 'GET' },
        columns: [
            { data: null, orderable: false, searchable: false, render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'code', render: d => `<span class="font-mono font-semibold text-primary-600 dark:text-primary-400">${d}</span>` },
            { data: 'name' },
            { 
                data: 'category', 
                render: d => {
                    const colors = {
                        storage: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                        machine: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                        subcont: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
                    };
                    return `<span class="px-2 py-0.5 rounded-full text-[8px] font-bold uppercase tracking-widest ${colors[d] || 'bg-gray-100 text-gray-600'}">${d}</span>`;
                }
            },
            { data: 'description', render: d => d || '-' },
            {
                data: null, orderable: false, searchable: false, className: 'text-center', width: '100px',
                render: (d, t, r) => `
                    <div class="flex items-center justify-center gap-1.5">
                        <button class="edit-btn h-8 w-8 inline-flex items-center justify-center text-primary-600 rounded-xs bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:hover:bg-primary-900/30 transition-colors"
                            data-id="${r.id}" data-code="${r.code}" data-name="${r.name}" data-category="${r.category}" data-description="${r.description || ''}" title="Edit">
                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                        </button>
                        <button class="delete-btn h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-xs bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors"
                            data-id="${r.id}" title="Delete">
                            <i class="fa-solid fa-trash-can text-sm"></i>
                        </button>
                    </div>`
            }
        ],
        order: [[1, 'asc']]
    });

    const showMdl = (id) => { $('.modal-container').addClass('hidden'); $(`#${id}`).removeClass('hidden'); };
    const hideMdl = (id) => { $(`#${id}`).addClass('hidden'); };
    $('.close-modal').on('click', function() { $(this).closest('.modal-container').addClass('hidden'); });

    $('.add-button').on('click', function() {
        $('#locationForm')[0].reset();
        $('#locationId').val('');
        $('input[name="_method"]').val('POST');
        $('#modal-title').text('Add Location');
        showMdl('modal-location-form');
    });

    $(document).on('click', '.edit-btn', function() {
        $('#locationForm')[0].reset();
        $('#locationId').val($(this).data('id'));
        $('input[name="code"]').val($(this).data('code'));
        $('input[name="name"]').val($(this).data('name'));
        $('select[name="category"]').val($(this).data('category'));
        $('textarea[name="description"]').val($(this).data('description'));
        $('input[name="_method"]').val('PUT');
        $('#modal-title').text('Edit Location');
        showMdl('modal-location-form');
    });

    $('#saveLocationBtn').on('click', function() {
        const id  = $('#locationId').val();
        const url = id ? `${apiBase}/${id}` : apiBase;
        $.ajax({
            url, method: 'POST', data: $('#locationForm').serialize(),
            success: (res) => { toast('success', 'Success', res.message); hideMdl('modal-location-form'); masterTable.ajax.reload(); },
            error:   (xhr) => { toast('error',   'Error',   xhr.responseJSON?.message || 'Operation failed'); }
        });
    });

    $(document).on('click', '.delete-btn', function() {
        deleteUrl = `${apiBase}/${$(this).data('id')}`;
        showMdl('modal-delete');
    });

    $('#confirmDelete').on('click', function() {
        $.ajax({
            url: deleteUrl, method: 'POST', data: { _method: 'DELETE', _token: csrf },
            success: (data) => { masterTable.ajax.reload(); hideMdl('modal-delete'); toast('success', 'Success', data.message); },
            error:   (xhr)  => { toast('error', 'Error', xhr.responseJSON?.message || 'Delete failed'); hideMdl('modal-delete'); }
        });
    });
});
</script>
@endpush
