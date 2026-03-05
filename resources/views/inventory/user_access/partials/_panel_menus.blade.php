<div class="hidden animate-fadeIn" id="menus-panel" role="tabpanel">
    <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-600 flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="font-semibold text-gray-900 dark:text-white text-xs tracking-wider">Application Menus</h3>
            <p class="text-xs text-gray-500 font-medium">Organize sidebar structure and hierarchy.</p>
        </div>
        <button onclick="openAddMenuModal()" class="w-full md:w-auto inline-flex items-center justify-center px-6 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-xs transition-all hover:bg-emerald-700 gap-2 tracking-wider">
            <i class="fa-solid fa-plus text-xs"></i> Add Menu Item
        </button>
    </div>
    <x-table id="MenusTable">
        <thead>
            <tr>
                <th class="w-16">No</th>
                <th class="w-16 text-center">Icon</th>
                <th class="text-left">Menu Title / Route</th>
                <th class="text-left">Parent Menu</th>
                <th class="w-24 text-center">Status</th>
                <th class="w-16 text-center">Order</th>
                <th class="w-32 text-center">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

<!-- MODAL -->
<div id="menuModal" tabindex="-1" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="relative w-full max-w-md">
        <div class="bg-white dark:bg-gray-800 rounded-xs overflow-hidden border border-slate-200 dark:border-gray-600">
            <div class="px-5 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-slate-200 dark:border-gray-600 flex justify-between items-center">
                <h3 id="menuModalTitle" class="text-lg font-medium text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                    <i class="fa-solid fa-bars text-emerald-600"></i> Menu Configuration
                </h3>
                <button onclick="closeModal('menuModal')" class="text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="menuForm" class="p-5">
                @csrf
                <input type="hidden" name="id" id="menu_id_input">
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-1">
                            <label class="block mb-1 text-xs font-medium text-gray-400">Title</label>
                            <input type="text" name="title" id="menu_title_input" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xs text-sm font-medium" required>
                        </div>
                        <div class="col-span-1">
                            <label class="block mb-1 text-xs font-medium text-gray-400">Icon Class</label>
                            <input type="text" name="icon" id="menu_icon_input" placeholder="fa-solid fa-house" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xs text-sm font-mono">
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-400">Route Name</label>
                        <input type="text" name="route" id="menu_route_input" placeholder="dashboard" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xs text-sm font-medium">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-1">
                            <label class="block mb-1 text-xs font-medium text-gray-400">Parent Menu</label>
                            <select name="parent_id" id="menu_parent_id_input" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xs text-sm font-medium">
                                <option value="">No Parent (Root)</option>
                                @foreach(\App\Models\InventoryModel\Menu::whereNull('parent_id')->get() as $p)
                                    <option value="{{ $p->id }}">{{ $p->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-1">
                            <label class="block mb-1 text-xs font-medium text-gray-400">Display Order</label>
                            <input type="number" name="order" id="menu_order_input" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xs text-sm font-medium" required>
                        </div>
                    </div>
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" id="menu_is_active_input" value="1" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-600"></div>
                        </label>
                        <span class="text-sm font-black text-gray-700 dark:text-gray-300 italic">Is Visible / Active</span>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="button" onclick="closeModal('menuModal')" class="flex-1 py-2 text-sm font-medium text-gray-500 bg-gray-100 hover:bg-gray-200 rounded-xs transition-all">Cancel</button>
                    <button type="submit" class="flex-1 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-xs shadow-sm active:scale-95 transition-all">Save Structure</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        menusTable = window.defaultDataTable('#MenusTable', {
            ajax: "{{ route('inventory.menus.data') }}",
            columns: [
                { data: 'DT_RowIndex', className: 'text-center font-medium text-gray-400' },
                { data: 'icon', className: 'text-center' },
                { data: 'title', className: 'font-medium' },
                { data: 'parent', className: 'text-sm font-medium text-emerald-600' },
                { data: 'status', className: 'text-center' },
                { data: 'order', className: 'text-center' },
                { data: 'action', className: 'text-center', orderable: false }
            ]
        });

        $('#MenusTable').on('click', '.edit-menu-btn', function() {
            $.get("{{ url('inventory/menus') }}/" + $(this).data('id'), function(data) {
                $('#menu_id_input').val(data.id);
                $('#menu_title_input').val(data.title);
                $('#menu_icon_input').val(data.icon);
                $('#menu_route_input').val(data.route);
                $('#menu_parent_id_input').val(data.parent_id || '');
                $('#menu_order_input').val(data.order);
                $('#menu_is_active_input').prop('checked', data.is_active == 1);
                $('#menuModalTitle').html('<i class="fa-solid fa-pen-to-square text-emerald-500"></i> Edit Menu Structure');
                $('#menuModal').removeClass('hidden').addClass('flex');
            });
        });

        $('#MenusTable').on('click', '.delete-menu-btn', function() {
            deleteItem("{{ url('inventory/menus') }}/" + $(this).data('id'), menusTable, 'Delete Menu?', 'Ensure no sub-menus exist before deleting.');
        });

        $('#menuForm').on('submit', e => handleFormSubmit(e, "{{ route('inventory.menus.store') }}", 'menuModal', menusTable));
    });

    function openAddMenuModal() { 
        $('#menuForm')[0].reset(); 
        $('#menu_id_input').val(''); 
        $('#menu_is_active_input').prop('checked', true); 
        $('#menuModalTitle').html('<i class="fa-solid fa-plus text-emerald-600"></i> Add Menu Item'); 
        $('#menuModal').removeClass('hidden').addClass('flex'); 
    }
</script>
@endpush
