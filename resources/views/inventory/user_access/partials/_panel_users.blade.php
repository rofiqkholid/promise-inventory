<div class="hidden animate-fadeIn" id="users-panel" role="tabpanel">
    <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-600 flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm tracking-wider">Active Permissions</h3>
            <p class="text-xs text-gray-500 font-medium">Currently assigned roles and custom permissions.</p>
        </div>
        <button onclick="openAddUserRoleModal()" class="w-full md:w-auto inline-flex items-center justify-center px-6 py-2 bg-primary-600 text-white text-sm font-semibold rounded-xs transition-all hover:bg-primary-700 gap-2 tracking-wider">
            <i class="fa-solid fa-user-plus text-sm"></i> Grant New Access
        </button>
    </div>
    <x-table id="UserAccessTable">
        <thead>
            <tr>
                <th class="w-16">No</th>
                <th class="text-left">Employee Name</th>
                <th class="text-left">Email Address</th>
                <th class="text-center">Role Assigned</th>
                <th class="w-32 text-center">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

<!-- MODALS -->
<div id="addUserRoleModal" tabindex="-1" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
    <div class="relative w-full max-w-sm">
        <div class="bg-white dark:bg-gray-800 rounded-xs overflow-hidden border border-slate-200 dark:border-gray-600">
            <div class="px-5 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-slate-200 dark:border-gray-600 flex justify-between items-center">
                <h3 id="addUserRoleModalTitle" class="text-lg font-medium text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                    <i class="fa-solid fa-user-shield text-primary-600"></i> Allocation Details
                </h3>
                <button onclick="closeModal('addUserRoleModal')" class="text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="addUserRoleForm" class="p-5">
                @csrf
                <input type="hidden" name="id" id="user_allocation_id">
                <div class="space-y-4">
                    <div>
                        <label class="block mb-1.5 text-sm font-medium text-gray-500 tracking-wider">Employee Account</label>
                        <select name="user_id" id="user_id_select" class="select2-theme w-full" required></select>
                        <input type="hidden" id="edit_mode_user_id">
                    </div>
                    <div>
                        <label class="block mb-3 text-sm font-medium text-gray-500 tracking-wider">Permission Levels (Select Multiple)</label>
                        <div class="grid grid-cols-2 gap-3 p-4 bg-gray-50 dark:bg-gray-700/30 rounded-xs border border-gray-100 dark:border-gray-600">
                            @foreach($roles as $role)
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" class="role-checkbox w-4 h-4 rounded-sm border-gray-300 text-primary-600 focus:ring-0 transition-all cursor-pointer">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-primary-600 transition-colors">{{ $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="button" onclick="closeModal('addUserRoleModal')" class="flex-1 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xs transition-all">Cancel</button>
                    <button type="submit" class="flex-1 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-xs shadow-sm transition-all active:scale-95">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="permissionModal" tabindex="-1" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 p-6 md:p-10">
    <div class="relative w-full max-w-6xl max-h-[85vh] flex flex-col scale-100 transition-transform duration-300">
        <div class="bg-white dark:bg-gray-900 rounded-xs overflow-hidden border border-slate-200 dark:border-gray-700 shadow-xl flex flex-col h-full min-h-0">
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center shrink-0">
                <div>
                    <h3 id="permissionModalTitle" class="text-sm font-black text-gray-900 dark:text-white tracking-widest uppercase">Access Privilege Configuration</h3>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                        <p class="text-[10px] font-bold text-primary-600 dark:text-primary-400 tracking-widest uppercase" id="permissionSubjectName"></p>
                    </div>
                </div>
                <button onclick="closeModal('permissionModal')" class="w-8 h-8 flex items-center justify-center rounded-xs text-gray-400 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-700 dark:hover:text-white transition-all border border-transparent hover:border-slate-200 dark:hover:border-gray-600">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
            
            <form id="permissionForm" class="flex-1 flex flex-col min-h-0">
                @csrf
                <input type="hidden" name="role_id" id="permission_role_id">
                <input type="hidden" name="user_id" id="permission_user_id">
                
                <div class="p-4 overflow-y-auto custom-scrollbar-minimal flex-1">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-4">
                        @include('inventory.user_access.partials._menu_tree_item', ['menus' => $allMenus, 'depth' => 0])
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-slate-200 dark:border-gray-700 flex items-center justify-between shrink-0">
                    <div class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">
                        <i class="fa-solid fa-circle-info mr-1"></i> Changes apply on next interaction.
                    </div>
                    <div class="flex gap-2">
                        <button type="button" onclick="closeModal('permissionModal')" class="px-4 py-2 text-[11px] font-bold text-gray-500 hover:text-gray-900 dark:hover:text-white transition-all rounded-xs border border-transparent hover:border-slate-200 dark:hover:border-gray-700">DISCARD</button>
                        <button type="submit" class="px-6 py-2 text-[11px] font-bold text-white bg-slate-900 dark:bg-primary-600 rounded-xs shadow-sm shadow-slate-900/10 dark:shadow-primary-900/20 transition-all active:scale-95 uppercase tracking-wider">Save Permissions</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        userTable = window.defaultDataTable('#UserAccessTable', {
            processing: true, serverSide: true,
            ajax: "{{ route('inventory.userAccess.data') }}",
            columns: [
                { data: 'DT_RowIndex', className: 'text-center font-medium text-gray-400' },
                { data: 'user_name', className: 'font-medium text-gray-900 dark:text-white' },
                { data: 'user_email', className: 'text-gray-500 text-sm' },
                { data: 'role_badge', className: 'text-center' },
                { data: 'action', className: 'text-center', orderable: false, searchable: false }
            ]
        });

        $('#user_id_select').select2({
            dropdownParent: $('#addUserRoleModal'), width: '100%', placeholder: 'Search...',
            ajax: {
                url: "{{ route('inventory.userAccess.search') }}", dataType: 'json', delay: 250,
                data: params => ({ 
                    term: params.term, 
                    exclude_id: $('#edit_mode_user_id').val(),
                    include_existing: 1
                }),
                processResults: data => ({ results: data.results }), cache: true
            },
            minimumInputLength: 0
        });

        $('#UserAccessTable').on('click', '.edit-user-role-btn', function() {
            const id = $(this).data('id');
            $.get("{{ url('inventory/user-access') }}/" + id, function(data) {
                $('#user_allocation_id').val(data.user_id);
                $('#edit_mode_user_id').val(data.user_id);
                
                $('.role-checkbox').prop('checked', false);
                data.role_ids.forEach(roleId => {
                    $(`.role-checkbox[value="${roleId}"]`).prop('checked', true);
                });

                const option = new Option(data.user_name, data.user_id, true, true);
                $('#user_id_select').append(option).trigger('change').prop('disabled', true);
                $('#addUserRoleModalTitle').html('<i class="fa-solid fa-user-gear text-primary-500"></i> Configure User Roles');
                $('#addUserRoleModal').removeClass('hidden').addClass('flex');
            });
        });

        $('#UserAccessTable').on('click', '.user-permission-btn', function() {
            const id = $(this).data('id'); const name = $(this).data('name');
            $('#permissionSubjectName').text(id ? 'USER: ' + name : '');
            $('#permission_user_id').val(id); $('#permission_role_id').val('');
            $('#permissionModalTitle').text('User Specific Menu');
            $('.menu-checkbox').prop('checked', false);
            $('.perm-checkbox').prop('checked', false);
            $('.role-permission-matrix').removeClass('flex').addClass('hidden');
            $.get("{{ url('inventory/user-menus') }}/" + id, function(data) {
                const menus = Array.isArray(data.active_menus) ? data.active_menus : Object.values(data.active_menus || {});
                menus.forEach(menuId => $(`.menu-checkbox[value="${menuId}"]`).prop('checked', true));
                $('#permissionModal').removeClass('hidden').addClass('flex');
            });
        });

        $('#UserAccessTable').on('click', '.delete-access-btn', function() {
            deleteItem("{{ url('inventory/user-access') }}/" + $(this).data('id'), userTable, 'Revoke Access?', 'Allocation will be removed.');
        });

        $('#addUserRoleForm').on('submit', e => handleFormSubmit(e, "{{ route('inventory.userAccess.store') }}", 'addUserRoleModal', userTable));
        
        // --- RECURSIVE CHECKBOX LOGIC (FIXED) ---
        let isProgrammatic = false;

        $(document).on('change', '.menu-checkbox', function() {
            if (isProgrammatic) return;
            
            const isChecked = $(this).is(':checked');
            const id = $(this).data('id');
            const parentId = $(this).data('parent');

            isProgrammatic = true;

            // 1. If checking/unchecking a parent, handle all descendants (Select All effect)
            checkDescendants(id, isChecked);

            // 2. If checking a child, ensure all ancestors are checked
            if (isChecked && parentId) {
                checkAncestors(parentId);
            }

            isProgrammatic = false;

            // 3. Permission matrix sync:
            syncMatrixCheckboxes();
        });

        $(document).on('change', '.perm-checkbox', function() {
            const isChecked = $(this).is(':checked');
            if (isChecked) {
                const menuId = $(this).closest('.role-permission-matrix').data('menu-id');
                const menuCb = $(`.menu-checkbox[data-id="${menuId}"]`);
                if (!menuCb.is(':checked')) {
                    isProgrammatic = true;
                    menuCb.prop('checked', true);
                    const parentId = menuCb.data('parent');
                    if (parentId) checkAncestors(parentId);
                    isProgrammatic = false;
                }
            }
        });

        $(document).on('click', '.select-all-children', function() {
            const id = $(this).data('id');
            const parentCheckbox = $(`.menu-checkbox[data-id="${id}"]`);
            isProgrammatic = true;
            parentCheckbox.prop('checked', true);
            checkDescendants(id, true);
            checkAncestors(parentCheckbox.data('parent'));
            isProgrammatic = false;
            syncMatrixCheckboxes();
        });

        function checkDescendants(parentId, state) {
            $(`.menu-checkbox[data-parent="${parentId}"]`).each(function() {
                $(this).prop('checked', state);
                checkDescendants($(this).data('id'), state);
            });
        }

        function checkAncestors(id) {
            const current = $(`.menu-checkbox[data-id="${id}"]`);
            if (current.length > 0) {
                current.prop('checked', true);
                const parentId = current.data('parent');
                if (parentId) checkAncestors(parentId);
            }
        }

        function syncMatrixCheckboxes() {
            $('.menu-checkbox').each(function() {
                const id = $(this).data('id');
                const isChecked = $(this).is(':checked');
                const matrixDiv = $(`.role-permission-matrix[data-menu-id="${id}"]`);
                if (isChecked) {
                    if (matrixDiv.find('.perm-checkbox:checked').length === 0) {
                        matrixDiv.find('.can-view-cb').prop('checked', true);
                    }
                } else {
                    matrixDiv.find('.perm-checkbox').prop('checked', false);
                }
            });
        }

        $('#permissionForm').on('submit', function(e) {
            e.preventDefault();
            const userId = $('#permission_user_id').val();
            const url = userId ? "{{ route('inventory.userMenus.update') }}" : "{{ route('inventory.roleMenus.update') }}";
            handleFormSubmit(e, url, 'permissionModal', null);
        });
    });

    function openAddUserRoleModal() { 
        $('#addUserRoleForm')[0].reset(); 
        $('#user_allocation_id, #edit_mode_user_id').val(''); 
        $('.role-checkbox').prop('checked', false);
        $('#user_id_select').prop('disabled', false).val(null).empty().trigger('change'); 
        $('#addUserRoleModalTitle').html('<i class="fa-solid fa-user-plus text-primary-600"></i> New Access Assignment'); 
        $('#addUserRoleModal').removeClass('hidden').addClass('flex'); 
    }
</script>
@endpush
