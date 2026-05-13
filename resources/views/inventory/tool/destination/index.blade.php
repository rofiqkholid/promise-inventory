@extends('layouts.app')

@section('title', 'Tool Destination Master')

@section('content')
<div class="text-gray-900 dark:text-gray-100">

    {{-- Header --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">Tool Destination Master</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Manage destinations for Tool Out transactions.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button type="button" onclick="openAddModal()" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest active:scale-[0.98] transition-all">
                <i class="fa-solid fa-plus"></i> Add New Destination
            </button>
        </div>
    </div>

    {{-- Table --}}
    <x-table id="destinationTable">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th class="px-4 py-4 w-12 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">No</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Code</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Destination Name</th>
                <th class="px-4 py-4 text-center w-24 text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

{{-- Modal --}}
<div id="modal-destination" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4">
    <div class="relative w-full max-w-md transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 shadow-2xl transition-all">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 id="modal-title" class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-widest">Add Destination</h3>
            <button class="close-modal text-gray-400 hover:text-gray-500 w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100 dark:hover:bg-gray-800"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="formDestination" class="p-6">
            @csrf
            <input type="hidden" name="id" id="dest_id">
            <div class="space-y-4">
                <div>
                    <label class="block mb-1 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Destination Code</label>
                    <input type="text" name="code" id="code" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all" placeholder="e.g. LINE-A">
                </div>
                <div>
                    <label class="block mb-1 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Destination Name</label>
                    <input type="text" name="name" id="name" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all" placeholder="e.g. Production Line A">
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-3">
                <button type="button" class="close-modal px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-[10px] font-bold text-white uppercase tracking-widest active:scale-[0.98] transition-all">Save Destination</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(function() {
        const table = window.defaultDataTable('#destinationTable', {
            ajax: "{{ route('inventory.tool.destination.index') }}",
            columns: [
                { data: 'DT_RowIndex', className: 'text-center' },
                { data: 'code', render: d => `<span class="px-2 py-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-xs font-mono text-[10px]">${d}</span>` },
                { data: 'name', render: d => `<span class="font-bold text-gray-900 dark:text-white">${d}</span>` },
                { data: 'action', className: 'text-center' }
            ]
        });

        $('#formDestination').on('submit', function(e) {
            e.preventDefault();
            const id = $('#dest_id').val();
            const url = id ? "{{ url('inventory/tool/destination') }}/" + id : "{{ route('inventory.tool.destination.store') }}";
            const method = id ? "PUT" : "POST";

            $.ajax({
                url: url,
                type: method,
                data: $(this).serialize(),
                success: function(res) {
                    window.showToast('success', res.message);
                    $('#modal-destination').addClass('hidden');
                    table.ajax.reload(null, false);
                },
                error: function(err) {
                    window.showToast('error', err.responseJSON?.message || 'Something went wrong');
                }
            });
        });

        window.openAddModal = () => {
            $('#formDestination')[0].reset();
            $('#dest_id').val('');
            $('#modal-title').text('Add Destination');
            $('#modal-destination').removeClass('hidden');
        };

        window.editDestination = (id) => {
            $.get("{{ url('inventory/tool/destination') }}/" + id + "/edit", function(res) {
                $('#dest_id').val(res.id);
                $('#code').val(res.code);
                $('#name').val(res.name);
                $('#modal-title').text('Edit Destination');
                $('#modal-destination').removeClass('hidden');
            });
        };

        window.deleteDestination = (id) => {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('inventory/tool/destination') }}/" + id,
                        type: "DELETE",
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(res) {
                            window.showToast('success', res.message);
                            table.ajax.reload(null, false);
                        }
                    });
                }
            });
        };

        $(document).on('click', '.close-modal', function() { $(this).closest('.modal-container').addClass('hidden'); });
    });
</script>
@endpush
