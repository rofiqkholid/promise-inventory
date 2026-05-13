@extends('layouts.app')

@section('title', 'Tool Sketch Master')

@section('content')
<div class="text-gray-900 dark:text-gray-100">

    {{-- Header --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">Tool Sketch Master</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Manage tool technical sketches and drawings per category.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button type="button" onclick="openAddModal()" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest active:scale-[0.98] transition-all">
                <i class="fa-solid fa-plus"></i> Add New Sketch
            </button>
        </div>
    </div>

    {{-- Table --}}
    <x-table id="sketchTable">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th class="px-4 py-4 w-12 text-center text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">No</th>
                <th class="px-4 py-4 text-center w-20 text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Image</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Sketch Name</th>
                <th class="px-4 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Category</th>
                <th class="px-4 py-4 text-center w-24 text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

{{-- Modal: Sketch Form --}}
<div id="modal-sketch" class="modal-container hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4">
    <div class="relative w-full max-w-md transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 shadow-2xl transition-all">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h3 id="modal-title" class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-widest">Add Tool Sketch</h3>
            <button class="close-modal text-gray-400 hover:text-gray-500 w-8 h-8 flex items-center justify-center rounded-xs hover:bg-gray-100 dark:hover:bg-gray-800"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="formSketch" class="p-6" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" id="sketch_id">
            <div class="space-y-4">
                <div>
                    <label class="block mb-1 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Tool Category</label>
                    <select name="category_id" id="category_id" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all">
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Sketch Name</label>
                    <input type="text" name="name" id="name" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all" placeholder="e.g. Drill Bit Sketch A">
                </div>
                <div>
                    <label class="block mb-1 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Upload Image</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-200 dark:border-gray-700 border-dashed rounded-xs hover:border-primary-500 transition-all cursor-pointer" onclick="document.getElementById('image_input').click()">
                        <div class="space-y-1 text-center">
                            <i id="preview-icon" class="fa-solid fa-image text-3xl text-gray-400 mb-2"></i>
                            <img id="image-preview" class="hidden h-32 w-auto mx-auto object-contain mb-2 rounded-xs">
                            <div class="flex text-xs text-gray-600 dark:text-gray-400">
                                <span class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-xs font-bold text-primary-600 hover:text-primary-500">Upload a file</span>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-[10px] text-gray-500">PNG, JPG, GIF up to 2MB</p>
                        </div>
                        <input id="image_input" name="image" type="file" class="sr-only" onchange="previewFile(this)">
                    </div>
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-3">
                <button type="button" class="close-modal px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-[10px] font-bold text-white uppercase tracking-widest active:scale-[0.98] transition-all">Save Sketch</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Image Preview --}}
<div id="modal-preview" class="modal-container hidden fixed inset-0 z-[110] flex items-center justify-center bg-slate-900/90 p-4" onclick="this.addClass('hidden')">
    <div class="relative max-w-4xl w-full h-full flex items-center justify-center p-4">
        <img id="img-full" src="" class="max-w-full max-h-full object-contain shadow-2xl">
        <button class="absolute top-4 right-4 text-white text-3xl hover:text-gray-300"><i class="fa-solid fa-xmark"></i></button>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(function() {
        const table = window.defaultDataTable('#sketchTable', {
            ajax: "{{ route('inventory.tool.sketch.index') }}",
            columns: [
                { data: 'DT_RowIndex', className: 'text-center' },
                { data: 'image', className: 'text-center' },
                { data: 'name', render: d => `<span class="font-bold text-gray-900 dark:text-white">${d}</span>` },
                { data: 'category' },
                { data: 'action', className: 'text-center' }
            ]
        });

        $('#formSketch').on('submit', function(e) {
            e.preventDefault();
            const id = $('#sketch_id').val();
            const url = id ? "{{ url('inventory/tool/sketch') }}/" + id + "/update" : "{{ route('inventory.tool.sketch.store') }}";
            const formData = new FormData(this);

            const btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    window.showToast('success', res.message);
                    $('#modal-sketch').addClass('hidden');
                    table.ajax.reload(null, false);
                    btn.prop('disabled', false).text('Save Sketch');
                },
                error: function(err) {
                    window.showToast('error', err.responseJSON?.message || 'Something went wrong');
                    btn.prop('disabled', false).text('Save Sketch');
                }
            });
        });

        window.openAddModal = () => {
            $('#formSketch')[0].reset();
            $('#sketch_id').val('');
            $('#modal-title').text('Add Tool Sketch');
            $('#image-preview').addClass('hidden');
            $('#preview-icon').removeClass('hidden');
            $('#modal-sketch').removeClass('hidden');
        };

        window.editSketch = (id) => {
            $.get("{{ url('inventory/tool/sketch') }}/" + id + "/edit", function(res) {
                $('#sketch_id').val(res.id);
                $('#category_id').val(res.category_id);
                $('#name').val(res.name);
                $('#modal-title').text('Edit Tool Sketch');
                
                $('#image-preview').attr('src', '/storage/' + res.image_path).removeClass('hidden');
                $('#preview-icon').addClass('hidden');
                
                $('#modal-sketch').removeClass('hidden');
            });
        };

        window.deleteSketch = (id) => {
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
                        url: "{{ url('inventory/tool/sketch') }}/" + id,
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

        window.previewImg = (src) => {
            $('#img-full').attr('src', src);
            $('#modal-preview').removeClass('hidden');
        };

        window.previewFile = (input) => {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#image-preview').attr('src', e.target.result).removeClass('hidden');
                    $('#preview-icon').addClass('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        };

        $(document).on('click', '.close-modal', function() { $(this).closest('.modal-container').addClass('hidden'); });
        $('#modal-preview').on('click', function() { $(this).addClass('hidden'); });
    });
</script>
@endpush
