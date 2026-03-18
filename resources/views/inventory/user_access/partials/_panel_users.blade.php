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

<div id="permissionModal" tabindex="-1" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50">
    <div class="relative w-full max-w-xl">
        <div class="bg-white dark:bg-gray-800 rounded-xs overflow-hidden border border-slate-200 dark:border-gray-600">
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-slate-200 dark:border-gray-600 flex justify-between items-center">
                <div>
                    <h3 id="permissionModalTitle" class="text-lg font-medium text-gray-900 dark:text-white tracking-tight">Access Control</h3>
                    <p class="text-xs font-medium text-primary-600 dark:text-primary-400 tracking-widest mt-0.5" id="permissionSubjectName"></p>
                </div>
                <button onclick="closeModal('permissionModal')" class="text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            <form id="permissionForm" class="p-6">
                @csrf
                <input type="hidden" name="role_id" id="permission_role_id">
                <input type="hidden" name="user_id" id="permission_user_id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-[50vh] overflow-y-auto px-1 py-2 custom-scrollbar-minimal">
                    @foreach($allMenus as $menu)
                        <div class="p-3 bg-gray-50/50 dark:bg-gray-900/30 rounded-xs border border-gray-100 dark:border-gray-800">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="menu_ids[]" value="{{ $menu->id }}" class="menu-checkbox w-4 h-4 rounded-sm border-gray-300 text-primary-600 focus:ring-0 transition-all cursor-pointer">
                                <div class="flex items-center gap-2">
                                    <i class="{{ $menu->icon }} text-primary-600 dark:text-primary-400 text-sm"></i>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $menu->title }}</span>
                                </div>
                            </label>
                            
                            @if($menu->children->count() > 0)
                                <div class="mt-2 ml-6 space-y-1.5 border-l border-slate-200 dark:border-slate-700 pl-4">
                                    @foreach($menu->children as $child)
                                        <label class="flex items-center gap-2.5 cursor-pointer group/child">
                                            <input type="checkbox" name="menu_ids[]" value="{{ $child->id }}" class="menu-checkbox w-3.5 h-3.5 rounded-sm border-gray-300 text-primary-500 focus:ring-0 transition-all cursor-pointer">
                                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 group-hover/child:text-primary-600 transition-colors">{{ $child->title }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="closeModal('permissionModal')" class="flex-1 py-2 text-sm font-medium text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all rounded-xs">Cancel</button>
                    <button type="submit" class="flex-1 py-2 text-sm font-medium text-white bg-slate-900 dark:bg-white dark:text-slate-950 rounded-xs shadow-sm transition-all active:scale-95">Sync Permissions</button>
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
                
                // Clear and set checkboxes
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
