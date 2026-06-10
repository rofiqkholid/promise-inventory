<div class="hidden animate-fadeIn" id="roles-panel" role="tabpanel">
    <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-600 flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="font-semibold text-gray-900 dark:text-white text-xs tracking-wider">Role Definitions</h3>
            <p class="text-xs text-gray-500 font-medium">Inventory roles are managed from the Admin panel. You can add custom roles here.</p>
        </div>
        <button onclick="openAddRoleModal()" class="w-full md:w-auto inline-flex items-center justify-center px-6 py-2 bg-purple-600 text-white text-sm font-semibold rounded-xs transition-all hover:bg-purple-700 gap-2 tracking-wider">
            <i class="fa-solid fa-plus text-xs"></i> Create Role
        </button>
    </div>
    <x-table id="RolesTable">
        <thead>
            <tr>
                <th class="w-16">No</th>
                <th class="text-left">Role Name</th>
                <th class="text-left">Description</th>
                <th class="w-40 text-center">Configuration</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

<!-- MODAL -->
<div id="roleModal" tabindex="-1" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
    <div class="relative w-full max-w-sm">
        <div class="bg-white dark:bg-gray-800 rounded-xs overflow-hidden border border-slate-200 dark:border-gray-600">
            <div class="px-5 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-slate-200 dark:border-gray-600 flex justify-between items-center">
                <h3 id="roleModalTitle" class="text-lg font-medium text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                    <i class="fa-solid fa-shield-halved text-purple-600"></i> Role Details
                </h3>
                <button onclick="closeModal('roleModal')" class="text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="roleForm" class="p-5">
                @csrf
                <input type="hidden" name="id" id="role_id_input">
                <div class="space-y-3">
                    <div>
                        <label class="block mb-1.5 text-xs font-medium text-gray-500 tracking-wider">Role Name</label>
                        <input type="text" name="role_name" id="role_name_input" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md text-sm font-medium" placeholder="e.g. Inv Admin" required>
                        <p class="mt-1 text-[10px] text-gray-400">Prefix with "Inv " for inventory roles (e.g. Inv Admin, Inv Viewer)</p>
                    </div>
                    <div>
                        <label class="block mb-1.5 text-xs font-medium text-gray-500 tracking-wider">Description</label>
                        <textarea name="description" id="role_description_input" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md text-sm font-medium" placeholder="Role description..." rows="2"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="button" onclick="closeModal('roleModal')" class="flex-1 py-2 text-sm font-medium text-gray-500 bg-gray-100 hover:bg-gray-200 rounded-md transition-all">Cancel</button>
                    <button type="submit" class="flex-1 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-md shadow-sm active:scale-95 transition-all">Save Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        rolesTable = window.defaultDataTable('#RolesTable', {
            processing: true, serverSide: true,
            ajax: "{{ route('inventory.roles.data') }}",
            columns: [
                { data: 'DT_RowIndex', className: 'text-center font-medium text-gray-400' },
                { data: 'name', className: 'font-medium text-slate-900 dark:text-slate-100' },
                { data: 'description', className: 'text-gray-500 text-sm' },
                { data: 'action', className: 'text-center', orderable: false }
            ]
        });

        $('#RolesTable').on('click', '.edit-role-btn', function() {
            $.get("{{ url('inventory/roles') }}/" + $(this).data('id'), function(data) {
                $('#role_id_input').val(data.id);
                $('#role_name_input').val(data.role_name);
                $('#role_description_input').val(data.description);
                $('#roleModalTitle').html('<i class="fa-solid fa-pen-to-square text-primary-500"></i> Edit Role');
                $('#roleModal').removeClass('hidden').addClass('flex');
            });
        });

        $('#RolesTable').on('click', '.permission-role-btn', function() {
            const id = $(this).data('id'); const name = $(this).data('name');
            $('#permissionSubjectName').text('ROLE: ' + name);
            $('#permission_role_id').val(id); $('#permission_user_id').val('');
            $('#permissionModalTitle').text('Role Basic Menu');
            $('.menu-checkbox').prop('checked', false);
            $('.perm-checkbox').prop('checked', false);
            $('.role-permission-matrix').removeClass('hidden').addClass('flex');
            $.get("{{ url('inventory/role-menus') }}/" + id, function(data) {
                const menus = Array.isArray(data.active_menus) ? data.active_menus : Object.values(data.active_menus || {});
                menus.forEach(menuId => $(`.menu-checkbox[value="${menuId}"]`).prop('checked', true));
                
                const perms = data.permissions || {};
                Object.keys(perms).forEach(menuId => {
                    const row = perms[menuId];
                    const matrixDiv = $(`.role-permission-matrix[data-menu-id="${menuId}"]`);
                    if (row.view) matrixDiv.find('.can-view-cb').prop('checked', true);
                    if (row.create) matrixDiv.find('.can-create-cb').prop('checked', true);
                    if (row.edit) matrixDiv.find('.can-edit-cb').prop('checked', true);
                    if (row.delete) matrixDiv.find('.can-delete-cb').prop('checked', true);
                });
                
                $('#permissionModal').removeClass('hidden').addClass('flex');
            });
        });

        $('#RolesTable').on('click', '.delete-role-btn', function() {
            deleteItem("{{ url('inventory/roles') }}/" + $(this).data('id'), rolesTable, 'Delete Role?', 'Used roles cannot be deleted.');
        });

        $('#roleForm').on('submit', e => handleFormSubmit(e, "{{ route('inventory.roles.store') }}", 'roleModal', rolesTable));
    });

    function openAddRoleModal() { 
        $('#roleForm')[0].reset(); 
        $('#role_id_input').val(''); 
        $('#role_name_input').val('');
        $('#role_description_input').val('');
        $('#roleModalTitle').html('<i class="fa-solid fa-plus text-purple-600"></i> Add Role'); 
        $('#roleModal').removeClass('hidden').addClass('flex'); 
    }
</script>
@endpush
